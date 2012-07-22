<?php
require_once('authParent.php');
class Question extends authParent {

	function Question()
	{
		parent::authParent();	
	
		$this->load->helper('url');
		$this->load->helper('array');
		$this->load->helper('html');
		$this->load->helper('form');
		$this->load->helper('json');
		if(!$this->isLoggedIn) 
		{
			$this->verifyLogin();
		}
		
//		$this->output->enable_profiler(TRUE);
	}
	
	function index()
	{
		
	}
	
	function edit($question_id)
	{
		$this->load->model('question_model');
		
		$this->load->model('course_model');
		$data = array();
	
		$question_object = new Question_model($question_id);
		
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_object->course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }

	
		
		$data['question_object'] = $question_object;
		
		$data['mc_option'] = "<span class='mc_option'><input type='text' name='answers[]'/>".
												 "<input type='button' class='remove_answer' value='Remove' onClick='removeAnswer(this);'/><br /></span>";
		
		
		$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
		$breadcrumb .= " > <a href='".site_url('course/edit/'.$question_object->course_id)."'>Edit Course</a>";
		$breadcrumb .= " : Edit Question";

		$this->template->write('breadcrumb', $breadcrumb);
											
		$this->template->write_view('content', 'question_edit', $data);				
		$this->template->render();
	}
	
	function submitEdit($question_id)
	{
		$this->load->model('question_model');
		
		//TODO FORM VALIDATE
		$this->question_model->updateQuestionTextForQuestion($question_id);
		$this->question_model->toggleAnonymousForQuestion($question_id);
		
		if($this->input->post('question_type') == "MC")
		{
			$this->question_model->removeDeletedAnswers();
			$this->question_model->addNewAnswersToQuestion($question_id);
		}
	

	  redirect('/course/edit/'.$this->input->post('course_id'));
	}
	
	function add($course_id)
	{
		//TODO: What if course id is not supplied? Aka brute force to question/add/ url
		//TODO: security, prevent brute force to question/add/#
		$this->load->model('course_model');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }

		
		
		$data = array();
		
		$data['course_id'] = $course_id;
		
		$data['mc_option_add'] = "<input type='button' class='add_answer' value='Add Answer' onClick='addAnswer();'/><br />";
		
		$data['mc_option'] = "<span class='mc_option'><input type='text' name='answers[]'/>".
												 "<input type='button' class='remove_answer' value='Remove' onClick='removeAnswer(this);'/><br /></span>";
												
		$data['t_option'] = "<span class='mc_option'><input type='text' name='answers[]' value='True' readonly='readonly'/><br />";
		
		$data['f_option'] = "<span class='mc_option'><input type='text' name='answers[]' value='False' readonly='readonly'/><br />";
					
					
		$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
		$breadcrumb .= " > <a href='".site_url('course/edit/'.$course_id)."'>Edit Course</a>";
		$breadcrumb .= " : Add Question";

		$this->template->write('breadcrumb', $breadcrumb);							
		$this->template->write_view('content', 'question_add', $data);				
		$this->template->render();
	}
	
	function addNew($course_id)
	{
		$this->load->model('question_model');
		$this->load->model('course_model');
		
			$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
		 	if(!$havePerms) {
		  	redirect(site_url("/errorController/nopermission"));
				die;
		  }
		
		$validation_response = '';
		if($this->input->post('question_text') == "Type your question here..." || $this->input->post('question_text') == "")
		{
			$validation_response .= '<li>Please enter some question text.</li>';
		}
		if($this->input->post('question_type') == "None")
		{
			$validation_response .= '<li>Please select a question type.</li>';
		}
		
		$answers = $this->input->post('answers');
		if(!is_array($answers)) {
			$answers=null;
		}
		$empty_answers = false;
		if($answers != false)
		{
			foreach($answers as $answer)
			{
				if($answer == '')
				{
					$empty_answers = true;
				}
			}
			
			if($empty_answers)
			{
				$validation_response .= '<li>Please fill in all answers.</li>';
			}
		}

		
		if($validation_response == '')
		{
			$this->question_model->createQuestion();

			redirect('/course/edit/'.$this->input->post('course_id'));
		}else{
			$data = array();

			$data['validation_response'] = $validation_response;
			
			$data['course_id'] = $course_id;
			
			$data['answers'] = $answers;
			$data['questionText'] = $this->input->post('question_text');
			$data['question_type'] = $this->input->post('question_type');

			$data['mc_option_add'] = "<input type='button' class='add_answer' value='Add Answer' onClick='addAnswer();'></input><br />";

			$data['mc_option'] = "<span class='mc_option'><input type='text' name='answers[]'></input>".
													 "<input type='button' class='remove_answer' value='Remove' onClick='removeAnswer(this);'></input><br /></span>";

			$data['t_option'] = "<span class='mc_option'><input type='text' name='answers[]' value='True' readonly='readonly'></input><br />";

			$data['f_option'] = "<span class='mc_option'><input type='text' name='answers[]' value='False' readonly='readonly'></input><br />";
			
			$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
			$breadcrumb .= " > <a href='".site_url('course/edit/'.$course_id)."'>Edit Course</a>";
			$breadcrumb .= " : Edit Question";

			$this->template->write('breadcrumb', $breadcrumb);
			$this->template->write_view('content', 'question_add', $data);				
			$this->template->render();
		}
		

	}
	
	function toggleStatus()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		$this->question_model->toggleQuestionStatus();
		$this->load->model("course_model");
		
		$this->question_model->updateCache();
		
		echo "success";
	}
	
	function toggleQAStatus()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		
		$opening_question = $this->input->post('question_is_open');
		
		if($opening_question)
		{
			//check if there are answers associated with the question, if so, we're reopening
			//if not, we're opening a fresh question
			$count = $this->question_model->getTotalAnswersForQuestion($this->input->post('question_id'));
			
			if($count > 0)
			{
				//just reopen the question
				$this->question_model->toggleQuestionStatus();
			}else{
				//build answers for question and open question
				$this->question_model->buildQuickAskAnswers();
				$this->question_model->toggleQuestionStatus();
			}		
		}else{
			$this->question_model->toggleQuestionStatus();
		}
		$this->question_model->updateCache();
		echo "success";
	}
	
	function toggleQRStatus()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		
		$this->question_model->toggleQuestionStatus();
		$this->question_model->updateCache();
		echo "success";
	
	}
	
	function resetQA()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		
		$this->question_model->destroyQuickAskAnswers();
		
		echo "success";
	}
	
	function resetQR()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		
		$this->question_model->destroyQuickResponseResults();
		
		echo "success";
	}
	
	function delete()
	{
		$this->load->model('question_model');
		$this->question_model->deleteQuestionAndContent();
		
		redirect('/course/edit/'.$this->input->post('course_id'));
	}
	
	function updateResultCountsAjax()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		
		$results = $this->question_model->getResultsForCourseQuestionsFromAjax();
		$results['status'] = "success";
		echo json_encode($results);
		
	}
	
	
	function setSortOrder() 
	{
		$this->load->model("question_model");
		$order=1;
		foreach($this->input->post("question_id") as $questionId) {
			$this->question_model->setSortOrder($questionId, $order);
			$order++;
			
		}
		
		
	}
	
	function getExpandedQuestionDisplay()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		$this->load->model('result_model');
		$this->load->model('course_model');
		
		$data = array();
		
		$question = new Question_model($this->input->post('question_id'));

		$this->result_model->buildIndexforAnswers($this->input->post('question_id'));
		$data['questionIndex'] = $this->result_model->answerArray;

		$data['courseData']= $this->course_model->getCourseData($question->course_id);
		$data['question_model'] = $question;
		
		$view = $this->load->view("question_views/".$question->question_type."_expand_stub", $data, true);
		
		echo $view;
	}
	
}