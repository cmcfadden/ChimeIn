<?php

class api extends CI_Controller {

	function api()
	{
		parent::__construct();
	
		$this->load->model("api_model");
	
		if($this->input->post("apikey")) {
			$apiKey = $this->input->post("apikey");
		}
		else {
			echo "Invalid Key";
			die;
		}


		if(!$this->api_model->checkApiKey($apiKey)) {
			echo "Invalid Key\n";
			die;
		}
		$this->perId=0;
		
		
	}
	
	function createQuestionForCourse($courseId) {
		$this->load->model("course_model");
		$this->load->model("question_model");
		$questionText = $this->input->post("question_text");
		$questionType = $this->input->post("question_type");

		$answers = $this->input->post("answers");

		$questionId = $this->question_model->createQuestionWithParams($courseId, $questionText, $questionType, $answers);
		echo "<question id=\"" . $questionId . "\"></question>\n";
		
		
	}

	function openQuestion($questionId) {
		$this->load->model("question_model");
		$this->question_model->loadQuestionAndAnswers($questionId);
		$this->question_model->openQuestion();
	}

	
	function closeQuestion($questionId) {
		$this->load->model("question_model");
		$this->question_model->loadQuestionAndAnswers($questionId);
		$this->question_model->closeQuestion();
	}

	
	function getQuestionsForCourse($courseId) {
		
		$this->load->model("course_model");
		$this->course_model->loadQuestionsForCourse($courseId);
		$data=array();
		
		$this->load->view("api/questionList", $data);
		
	}
	

	function getAnswersForQuestion($questionId) {
		
		$this->load->model("question_model");
		$this->question_model->loadQuestionAndAnswers($questionId);
		
		$this->load->model("result_model");
		$this->result_model->buildIndexforAnswers($questionId);
		$data['questionIndex'] = $this->result_model->answerArray;
		if($this->question_model->question_type == "TF" || $this->question_model->question_type == "MC" || $this->question_model->question_type == "QA") {
			$this->load->view("api/questionMultipleChoice", $data);			
		}
		else {
			$this->load->view("api/questionFreeResponse", $data);
		}
	}
	
	function getResultsForQuestion($questionId) {
	
		
		$this->load->model("question_model");
		$this->question_model->loadQuestionAndAnswers($questionId);
		
		$this->load->model("result_model");
		$data['resultCount'] =$this->result_model->getResultsCountForQuestion($questionId,$this->question_model->question_type);
		$data['resultData'] = $this->result_model->getResultsForQuestion($questionId, $this->question_model->question_type);
		$data['resultsBinned'] = $this->result_model->getBinnedResultsForQuestion($questionId, $this->question_model->question_type);

		$data['questionIndex'] = $this->result_model->answerArray;
		
		if($this->question_model->question_type == "TF" || $this->question_model->question_type == "MC" || $this->question_model->question_type == "QA") {
			$this->load->view("api/resultMultipleChoice", $data);			
		}
		else {
			$this->load->view("api/resultFreeResponse", $data);
		}
	}
	
	function getResultsForQuestionAsJson($questionId) {
		$this->load->helper('json');
		$this->load->model("question_model");
		$this->question_model->loadQuestionAndAnswers($questionId);
		
		$this->load->model("result_model");
		$data['resultCount'] =$this->result_model->getResultsCountForQuestion($questionId,$this->question_model->question_type);
		$data['resultData'] = $this->result_model->getResultsForQuestion($questionId, $this->question_model->question_type);
		$data['binnedResults'] = $this->result_model->getBinnedResultsForQuestion($questionId, $this->question_model->question_type);
		$data['question_model'] = $this->question_model;
		$data['questionIndex'] = $this->result_model->answerArray;
				

				
		$data['colors'] = array("#A5C6A5", "#F29B53", "#B56F47", "#D9B735", "#EFDB22", "#8BD994", "#3C8576", "#BA5553", "#E19A2E", "#FFC680");
		
		if($this->question_model->question_type == "TF" || $this->question_model->question_type == "MC" || $this->question_model->question_type == "QA") {
			$this->load->view("result_json_views/" . $this->question_model->question_type . "_result_json_stub", $data);
		}
		else {
			echo json_encode($data['resultData']);
		}
	
	}
	
	function addResultForQuestion($questionId) {
		
		$this->load->model('result_model');
		$this->load->model('question_model');
		$this->load->model('course_model');
		$answer = $this->input->post('answer');

		
		$question_model = new Question_model($questionId);

		$isPublic = $this->course_model->isCoursePublic($question_model->course_id);

		$havePerms =false;
		if(!$isPublic) {
			$havePerms = $this->course_model->isEnrolledInCourse($this->perId, $question_model->course_id);
		} 	

		if(!$havePerms && !$isPublic) {
  			redirect(site_url("/errorController/nopermission"));
			die;
  		}

		if($isPublic) {
			$this->perId = "0";
		}
		
		$response = $this->result_model->createResultWithParams($this->perId, $questionId, $answer);
		echo $response;

	}
	
	
}
