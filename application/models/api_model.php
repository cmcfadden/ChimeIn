<?php

class api_model extends CI_Model {
	
	
	function Api_model()
	{
		parent::__construct();
		
	}

	function checkApiKey($key) {


		$this->db->where("api_key", $key);
		$count = $this->db->get("Api_User")->num_rows();

		if($count>0) {
			return true;
		}
		else {
			return false;
		}
	}
	
	
}