<?php

class authParent Extends CI_Controller {
	
	public $x500;
	public $perId;
	public $isEmployed;
	public $isLoggedIn=false;
	public $phone;
	public $userModel;
	
	function authParent()
	{
		parent::__construct();
		
		$this->load->helper("url");

		if(!isset($_SERVER['HTTPS'])) {

		}
		else {
			$this->load->model("user_model");
			$this->userModel = new User_model();
			
			if($this->userModel->lazyLogin()) {
				$this->loadUserData();
			}
		}

	}
	

	function verifyLogin() {
		$this->load->model("user_model");
		$this->userModel = new User_model();
		$this->userModel->login();
		$this->loadUserData();
		

	}
	

	function loadUserData() {
		
		if(isset($this->userModel)) {
			$this->phone = $this->userModel->phone;
			$this->x500 = $this->userModel->x500;
			$this->perId = $this->userModel->perId;
			$this->isEmployed = $this->userModel->isEmployed();
			$this->isLoggedIn = true;
			
		}
		
	}

	function login() {
		$currentURL = split("login/", $this->uri->uri_string());
		$secureURL = base_url() . $currentURL[1];
		$this->verifyLogin();
		header("Location: " . $secureURL);
	}
	
	function logout() {
		
		$currentURL = base_url() . $this->uri->uri_string();
		$targetURL = str_replace("login/", "", $currentURL);
		$secureURL = str_replace("http://", "https://", $targetURL);
		
		$this->userModel->logout("https://idp-test.shib.umn.edu/idp/LogoutUMN?return=" . site_url("/"));

	}
	
	function switchUsers() {
		session_destroy();
		$this->userModel->logout("https://idp-test.shib.umn.edu/idp/LogoutUMN?return=" . site_url("course"));
	}
	
	
}
