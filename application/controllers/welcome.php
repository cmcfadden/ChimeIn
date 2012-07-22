<?php

require_once("authParent.php");

class Welcome extends authParent {

	function Welcome()
	{
		parent::authParent();	
		
		$this->load->helper('url');
		$this->load->helper('array');
		$this->load->helper('html');
		$this->load->helper('form');
		
//		$this->output->enable_profiler(TRUE);
	}
	
	function index()
	{
		$data = array();
		//$this->template->write_view('sideNav', 'sideNav');					
		$this->isiPhone=false;
		
		if(strpos($_SERVER['HTTP_USER_AGENT'],"iPhone")!==false || stripos($_SERVER['HTTP_USER_AGENT'], "Android") !==false) 
		{
			$this->load->view("mobile/home_page", $data);
			$this->output->enable_profiler(false);
		}
		else {
			$this->template->write_view('content', 'home_page', $data);				
					$this->template->render();
		}

	}
	
	function about() {
		
		$this->load->view("mobile/about");
		
	}
	
	
	
}

