<?php

class SMSEmail extends CI_Controller {

	public $gv = "";
	public $perId;
	
	public $smsUsernameArray = array("","","");
	
	function SMSEmail()
	{


		

		parent::__construct();	
		$this->load->helper('json');
		$this->load->helper('GoogleVoice');
//		$this->output->enable_profiler(TRUE);
	}

	function index()
	{

	}

	function getNewSMSViaEmail() {

		$pop = "ssl://pop.gmail.com";
		$socketPOP = fsockopen($pop, 995,$errno,$errstr,10);
		stream_set_timeout($socketPOP,10); 
		fgets($socketPOP);
		fwrite($socketPOP, "USER clachimein@gmail.com\n");

		fgets($socketPOP);
		fwrite($socketPOP, "PASS \n");
		fgets($socketPOP);
		fwrite($socketPOP, "LIST\n");
		$messageCount = fgets($socketPOP);

		$messageCountArray = mb_split(" ", $messageCount);
		$count = $messageCountArray[1];

		$messageListText = "";
		for($i=1; $i<=$count; $i++) {
			$messageListText = fgets($socketPOP);
			$sizeSplit = mb_split(" ", $messageListText);
			$messageSizeArray[$i] = $sizeSplit[1];
			
		}

		
		$messageArray= array();
		for($i=1; $i<=$count; $i++) {
			fwrite($socketPOP, "RETR " . $i . "\n");
  		$messageText = "";
			$tempMessageText ="";
			$tempMessageText = fgets($socketPOP);	
			while(1) {
				$tempMessageText = fgets($socketPOP);	
				if(trim($tempMessageText) == ".") {
					break;
				}
				else {
					$messageText .= $tempMessageText;
				}
			}


			fwrite($socketPOP, "DELE " . $i . "\n");
			fgets($socketPOP);

			$messageArray[] = $messageText;
			
		}
		


		fwrite($socketPOP, "QUIT\n");
		fclose($socketPOP);

		$cleanedMessageArray = array();		
		foreach($messageArray as $message) {

			$splitMessage = mb_split("\n", $message);
			$contentTypeLine = "";
			foreach($splitMessage as $line) {
				if(stristr($line, "subject")) {
					$splitSubject = mb_split("from", $line);
					if(count($splitSubject)>1) {
						$phone = trim($splitSubject[1]);						
					}
					else {
						$phone= false;
					}
				}
				if(stristr($line, "Content-Type")) {
					$contentTypeLine = $line;
				}
			}

			$bodyArrayTemp = mb_split($contentTypeLine, $message);
			$bodyArrayTemp = mb_split("--\r\n", $bodyArrayTemp[1]);
			$messageText = trim($bodyArrayTemp[0]);
			
			$cleanedMessageArray[] = array("phoneNumber"=>$phone, "messageText"=>$messageText);
			
			
		}


		
		$this->load->model("course_model");
		$this->load->model("result_model");
		$this->load->model("question_model");
		$this->load->model("user_model");
	
	
		$this->gv = new GoogleVoice($this->smsUsernameArray[rand(0,2)], '');


		foreach($cleanedMessageArray as $message) {
			$perId = "";
			$cleanedPhone = ltrim(preg_replace('/\D/', '', $message["phoneNumber"]), 01);
//			echo $cleanedPhone;

		
			
			$questionId = strtok($message["messageText"], " ");
			if(!is_numeric($questionId)) {
				$this->gv->sendSMS($cleanedPhone, "ChimeIn: Your answer must begin with the question number.");
				continue;
			}
			
			$answerBody = trim(str_replace($questionId, "", $message["messageText"]));
			
			

			
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
