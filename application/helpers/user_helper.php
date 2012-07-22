<?

// take personId, return x500

function personIdtoX500($perId) {
	$CI =& get_instance();
	
	$db2=$CI->load->database('userInfo',TRUE);

	$db2->where('PER_ID',$perId);
	$person=$db2->get('PERSON')->row();


	// reset default DB
	$db1 = $CI->load->database('default',TRUE);
	
	return $person->UID;
	
}


// take x500, return personId

function x500toPersonId($x500) {
	$CI =& get_instance();
	
	$db2=$CI->load->database('userInfo',TRUE);
	
	$db2->where('UID',$x500);
	$person=$db2->get('PERSON')->row();

	// reset default DB
	$db1 = $CI->load->database('default',TRUE);
	
	return $person->PER_ID;	
	
	
	
}

function personIdToName($perId) {
	global $personNameCache;
	if(isset($personNameCache[$perId])) {
		return $personNameCache[$perId];
	}
	else {
	
		if($perId == 0) {
			return "Anonymous";
		}
		$CI =& get_instance();
		$CI->db->where("PER_ID", $perId);
		$personQuery = $CI->db->get("CLA_DATA_CENTER.PERSON");

		if($personQuery->num_rows() > 0) {
			$person = $personQuery->row();
			$personNameCache[$perId] = (isset($person->PER_F_NAME)?$person->PER_F_NAME:null) . " " . (isset($person->PER_L_NAME)?$person->PER_L_NAME:null);


			return (isset($person->PER_F_NAME)?$person->PER_F_NAME:null) . " " . (isset($person->PER_L_NAME)?$person->PER_L_NAME:null);
			
		}
		else {
			return false;
		}
	}
}

function personIdToNameArray($perId) {
	$CI =& get_instance();
	
	$db2=$CI->load->database('userInfo',TRUE);

	$db2->where('PER_ID',$perId);
	$person=$db2->get('PERSON')->row();


	// reset default DB
	$db1 = $CI->load->database('default',TRUE);
	
	return array("firstName"=>$person->PER_F_NAME, "lastName" =>$person->PER_L_NAME);
	
}


function personIdToEmail($perId) {
	
	$CI =& get_instance();
	
	$db2=$CI->load->database('userInfo',TRUE);

	$db2->where('PER_ID',$perId);
	$person=$db2->get('PERSON')->row();


	// reset default DB
	$db1 = $CI->load->database('default',TRUE);
	
	return $person->PER_EMAIL;	
	
}



function phoneNumberFormatter($phoneNumber) {
	    $num = preg_replace('[^0-9]', '', $phoneNumber); 

	    $len = strlen($num); 
	    if($len == 7) 
	        $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num); 
	    elseif($len == 10) 
	        $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num); 

	    return $num; 

	
}