<?php

require_once("authParent.php");

class ErrorController extends authParent {

	function ErrorController()
	{
		parent::authParent();	
		
		$this->load->helper('url');
		$this->load->helper('array');
		$this->load->helper('html');
		$this->load->helper('form');


		if(!$this->isLoggedIn) 
		{
			$this->verifyLogin();
		}
//		$this->output->enable_profiler(TRUE);
	}
	

	function nopermission()
	{
		
		$data = "I'm sorry, but you do not have permission to view this page.";
		
		$this->template->write('content', $data);				
		$this->template->render();
		
	}
}

