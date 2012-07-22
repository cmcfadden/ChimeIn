<?php

class SMS extends CI_Controller {

	public $gv = "";
	public $perId;
	
	public $smsUsernameArray = array("");
	
	function SMS()
	{


		

		parent::__construct();
		$this->load->helper('json');
		$this->load->helper('GoogleVoice');
		$this->gv = new GoogleVoice('', '');
//		$this->output->enable_profiler(TRUE);
	}

	function index()
	{

	}

	function getNewSMS() {

		$sms = $this->gv->getNewSMS();
		$msgIDs = array();
		$smsArray = array();
//		var_dump($sms);
		foreach( $sms as $s )
		{

			$phoneNumber = $s['phoneNumber'];
			$messageText = $s['message'];

			if(is_numeric(ltrim(preg_replace('/\D/', '', $phoneNumber), 01))) {
				$smsArray[] = array("phoneNumber"=>$phoneNumber, "messageText"=>$messageText);				
			}

			
			if( !in_array($s['msgID'], $msgIDs) )
			{
				// mark the conversation as "read" in google voice
				$this->gv->deleteSMSMessage($s['msgID']);
				$msgIDs[] = $s['msgID'];
			}
		}

		// ok, we have everything from SMS now, let's get it into the DB, if possible
		
		$this->load->model("course_model");
		$this->load->model("result_model");
		$this->load->model("question_model");
		$this->load->model("user_model");
	
	
	
	
	
		$this->gv = new 
GoogleVoice($this->smsUsernameArray[rand(0,2)], '');
	
		foreach($smsArray as $sms) {
			$perId = "";
			$cleanedPhone = ltrim(preg_replace('/\D/', '', $sms["phoneNumber"]), 01);
//			echo $cleanedPhone;

		
			
			$questionId = strtok($sms["messageText"], " ");
			if(!is_numeric($questionId)) {
				$this->gv->sendSMS($cleanedPhone, "ChimeIn: Your answer must begin with the question number.");
				continue;
			}
			
			$answerBody = trim(str_replace($questionId, "", $sms["messageText"]));
			
			

			
			$this->question_model->loadQuestionAndAnswers($questionId);
			
			$perId = $this->user_model->loadPerIdForPhone($cleanedPhone);

			$this->question_model->loadIfAnsweredByUser($questionId, $perId);

			$courseIsPublic = $this->course_model->isCoursePublic($this->question_model->course_id);
			$courseForQuestion = $this->question_model->getCourseForQuestion($questionId);

			if(!$courseIsPublic) {

				if(!$perId) {
					$this->gv->sendSMS($cleanedPhone, "ChimeIn: We couldn't find your account in ChimeIn. Visit chimein.cla.umn.edu to add this number.");
					continue;
				}



				$this->perId = $perId;
				
			
				$enrolledCourses = $this->course_model->getEnrolledCourses($perId);
			
				$isEnrolled=false;
				foreach($enrolledCourses as $courseEntry) {
					if($courseEntry->COURSE_ID == $courseForQuestion) {
						$isEnrolled=true;
					
					}
				}
			
				if(!$isEnrolled) {
					$this->gv->sendSMS($cleanedPhone, "ChimeIn: You don't appear to be enrolled in the course for which you're trying to submit an answer.");
					continue;	
				}
			}
			else {
				$perId = 0;
				$this->perId = 0;
			}

			$this->result_model->buildIndexForAnswers($questionId);
			if(count($this->result_model->answerArray)>0) {
				foreach($this->result_model->answerArray as $key=>$character) {
					if(strtolower($character) == strtolower($answerBody)) {
						$answerId = $key;
					}
			
				}
				if(isset($answerId)) {
					if(!$this->question_model->question_is_open) {
						$this->gv->sendSMS($cleanedPhone, "ChimeIn: This question is already closed.");
						continue;
					}
					if(($this->question_model->question_type == "QA" || $this->question_model->question_type == "MC" || $this->question_model->question_type == "TF") && $this->question_model->answered_by_user && !$courseIsPublic) {
						$this->result_model->updateResultWithParams($perId, $questionId, $answerId);
					}
					else {
						$this->result_model->createResultWithParams($perId, $questionId, $answerId);											
					}
				}
				else {
					$this->gv->sendSMS($cleanedPhone, "ChimeIn:We could not figure out which answer you were attempting to submit.");
					continue;
				}
				
			}
			else {
				if($this->question_model->question_is_open) {
					$this->result_model->createResultWithParams($perId, $questionId, $answerBody);
				}
			}

		}
		


	}


}
