<?php
require_once('class.xhttp.php');
	
class GoogleVoice
{
	
	public $data;
	public $rnrse;
	// Our credentials
	private $_login;
	private $_pass;
	private $_rnr_se; // some crazy google thing that we need later

	// Our private curl handle
	private $_ch;

	// The location of our cookies
	private $_cookieFile;

	// Are we logged in already?
	private $_loggedIn = FALSE;

	public function __construct($login, $pass)
	{


		// Set account login info
		$this->data = array();
		$this->data['post'] = array(
		  'accountType' => 'GOOGLE',
		  'Email'       => $login,
		  'Passwd'      => $pass,
		  'service'     => 'grandcentral',
		  'source'      => 'sudocode.net-example-1.0' // Application's name, e.g. companyName-applicationName-versionID
		);
		
	}
	
	private function _logIn()
	{
	
	}

	

	public function sendSMS($number, $message)
	{
		
		$response = xhttp::fetch('https://www.google.com/accounts/ClientLogin', $this->data);

		if(!$response['successful']) {
		    echo 'response: '; print_r($response);
		    die();
		}

		// Extract Auth
		preg_match('/Auth=(.+)/', $response['body'], $matches);
		$auth = $matches[1];
		// You can also cache this auth value for at least 5+ minutes

		// Erase POST variables used on the previous xhttp call
		$this->data['post'] = null;

		// Set Authorization for authentication
		// There is no official documentation and this might change without notice
		$this->data['headers'] = array(
		    'Authorization' => 'GoogleLogin auth='.$auth
		);

		$response = xhttp::fetch('https://www.google.com/voice/b/0', $this->data);

		if(!$response['successful']) {
		    echo 'response: '; print_r($response);
		    die();
		}

		// Extract _rnr_se | This value does not change* Cache this value
		preg_match("/'_rnr_se': '([^']+)'/", $response['body'], $matches);
		$this->rnrse = $matches[1];

		// $data['headers'] still contains Auth for authentication
		
		
		$this->data['post'] = array (
		    '_rnr_se'     => $this->rnrse,
		    'phoneNumber' => "1".$number, // country code + area code + phone number (international notation)
		    'text'        => $message,
		    'id'          => ''  // thread ID of message, GVoice's way of threading the messages like GMail
		);

		// Send the SMS
		$response = xhttp::fetch('https://www.google.com/voice/sms/send/', $this->data);

		// Evaluate the response
		$value = json_decode($response['body']);

		if($value->ok) {
//		    echo "SMS message sent! ({$this->data["post"]["phoneNumber"]}: {$data["post"]["text"]})";
		} else {
//		    error_log("Unable to send SMS! Error Code ({$value->data->code})\n\n");
		    error_log(print_r($response,true));
		}
	}
	


}

?>
