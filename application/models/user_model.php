<?php

class User_model extends CI_Model {
		
	public $isLoggedIn;
	public $x500;
	public $perId;
	public $name;
	public $phone;
	public $as;
	public $db_link;
	
	function User_model()
	{
		parent::__construct();
	}
	
	function login() {
			if(!isset($_SERVER['HTTPS'])) {
				$currentURL = str_replace("http://","https://", base_url() .$this->uri->uri_string());
				redirect($currentURL);
				exit;
			}
			
			if(!isset($this->as)) {
				@require_once('/web/chimein.cla.umn.edu/simplesamlphp-1.8.2/lib/_autoload.php');

				$this->as = new SimpleSAML_Auth_Simple('default-sp');
			}

			require_once('/web/chimein.cla.umn.edu/cla_php_includes/cla_people_tools/db_connect.php');
			require_once('/web/chimein.cla.umn.edu/cla_php_includes/cla_people_tools/package.ClaPeopleTools.php');

			//authenticate the user, might redirect if nessessary 

			$this->as->requireAuth();

			$userAttributes = $this->as->getAttributes();

			$this->load->helper('url');
			$currentURL = base_url() .$this->uri->uri_string();


			//create a user object from the CLA_DATA_CENTER
			$x500 = $userAttributes["https://www.umn.edu/shibboleth/attributes/uid"][0];


			if(!(@$_SESSION['cla_user'] instanceof ClaUser)) { 

			  $_SESSION['cla_user'] = new ClaUser($x500,$this->db_link);

			}


			$this->perId = mysql_real_escape_string($_SESSION['cla_user']->perId);
			$this->x500 = $x500;

			$this->load->helper("user");
			$this->phone = phoneNumberFormatter($this->loadPhoneForPerId($this->perId));
	
	}
	
	function lazyLogin() {
		
		/**
		 * Check if the user is already logged in (has a cookie set) .. if so, we can probably load their login info without
		 * them doing anything.  Most important with single sign on
		 */

		
		require_once('/web/chimein.cla.umn.edu/simplesamlphp-1.8.2/lib/_autoload.php');

		$this->as = new SimpleSAML_Auth_Simple('default-sp');

		$this->config->set_item("base_url", str_replace("http://", "https://", $this->config->item("base_url")));
		require_once('/web/chimein.cla.umn.edu/cla_php_includes/cla_people_tools/db_connect.php');
		$this->db_link = $db_link;

		// if the user is already authed to shib or chimein, finish building the auth session automatically

		// TODO : check if this actually confirms them when they're authed via shib or just simplesaml.  

		if($this->as->isAuthenticated()) {

			$this->login(); // might still redirect if cookie is expired
			$_SESSION['chimein_user'] = true;
			if($this->hasChimeInSession()) {
				return true;

			}

		}
	}
	
	function logout($destination) {
		
		$_SESSION['chimein_user'] = null;
		$_SESSION["cla_user"] = null;
		$this->as->logout($destination);
		
	}
	
	
	
	
	function hasChimeInSession() {
		if(isset($_SESSION['chimein_user'])) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function isEmployed() {
		
			$this->db->where("PER_ID", $this->perId);
			if($this->db->get("CLA_DATA_CENTER.PERSON_JOBCODE")->num_rows()>0) {
				return true;
			}
			else {
				$this->db->where("INSTRUCTOR_ID", $this->perId);

				if($this->db->get("Instructors")->num_rows()>0) {

					return true;
				}
				else {
					$this->db->where("PER_ID", $this->perId);
					$this->db->where("EMPLID >", "90000000");
					if($this->db->get("CLA_DATA_CENTER.PERSON")->num_rows()>0) {
						return true;
					}
					else {
						return false;				
					}

				}

			}
		
	}
	
	function loadUser($perId) {
		$this->db->where("PER_ID", $perId);
		$result = $this->db->get("CLA_DATA_CENTER.PERSON");
		if($result->num_rows() >0) {
			$loadeduser = $result->row();
			$this->x500 = $loadeduser->UID;
			$this->perId = $perId;
			$this->name = (isset($person->PER_F_NAME)?$person->PER_F_NAME:null) . " " . (isset($person->PER_L_NAME)?$person->PER_L_NAME:null);
			
			
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function loadPhoneForPerId($perId) {
		$this->db->where("PER_ID", $perId);
		$query = $this->db->get("User_Settings");
		if($query->num_rows() == 0) {
			return false;
		}
		else {
			return $query->row()->PHONE_NUMBER;
		}
	}
	
	function loadPerIdForPhone($phoneNumber){ 
		$this->db->where("PHONE_NUMBER", $phoneNumber);
		$query = $this->db->get("User_Settings");
		if($query->num_rows() == 0) {
			return false;
		}
		else {
			return $query->row()->PER_ID;
		}
		
	}

	function addPhoneNumberForPerId($perId, $phoneNumber) {
		
		if($this->loadPhoneForPerId($perId)) {
			$this->db->where("PER_ID", $perId);
			$this->db->set("PHONE_NUMBER", $phoneNumber);
			$this->db->update("User_Settings");
		}
		else {
			$this->db->set("PER_ID", $perId);
			$this->db->set("PHONE_NUMBER", $phoneNumber);
			$this->db->insert("User_Settings");
		}
		return true;
		
	}


}

?>