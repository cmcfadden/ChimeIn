<?php
require_once('authParent.php');
class Course extends authParent {

	public $isiPhone = false;


	

	function Course()
	{

		parent::authParent();	
	
		$this->load->helper('url');
		$this->load->helper('array');
		$this->load->helper('html');
		$this->load->helper('form');
		$this->load->helper('json');
		$this->load->helper('user');


		if(!$this->isLoggedIn) 
		{
	
			if($this->uri->segment(2) == "view" || $this->uri->segment(2) == "updateViewAjax" ||  $this->uri->segment(2) == "viewQuestion") {

			}
			else {
				$this->verifyLogin();
			}
		}
		
		$this->isiPhone=false;
		
		if(strpos($_SERVER['HTTP_USER_AGENT'],"iPhone")!==false || stripos($_SERVER['HTTP_USER_AGENT'], "Android") !==false) 
		{
			$this->isiPhone=true;
		}

//$this->output->enable_profiler(TRUE);
	}

	
	function index()
	{
		$this->load->model('course_model');
		$this->load->model('user_model');
		$data = array();
		$data['instructed_courses'] = array();
		$data['enrolled_courses'] = array();
		
		$data['instructed_courses'] = $this->course_model->getInstructedCourses($this->perId);
		$data['enrolled_courses'] = $this->course_model->getEnrolledCourses($this->perId);

		$breadcrumb = "Home";
		
		if($this->isiPhone) 
		{
			$this->load->view("mobile/course_page", $data);
			$this->output->enable_profiler(false);
		}	else {
			$this->template->write('breadcrumb', $breadcrumb);
			$this->template->write_view('content', 'course_page', $data);					
			$this->template->render();
		}

	}
	
	function add()
	{
		$data = array();
		
		$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
		$breadcrumb .= " : Add Course";
		
		$this->template->write('breadcrumb', $breadcrumb);
		$this->template->write_view('content', 'course_add', $data);				
		$this->template->render();
	}

	
	function addNew()
	{
		$this->load->model('course_model');
		$this->load->model('question_model');
		
		$validation_response = '';
		$empty_designator = false;
		
		$names = $this->input->post('name');
		$departments = $this->input->post('department');
		$numbers = $this->input->post('number');
		$sections = $this->input->post('section');
		
		$designator_elements = array_merge($names, $departments, $numbers);
		
		if($this->input->post('instructor_user') == '')
		{
			$validation_response .= "<li>Please specify an instructor for this course.</li>";
		}
		
		foreach($designator_elements as $element)
		{
			if($element == '')
			{
				$empty_designator = true;
			}
		}
		
		if($empty_designator)
		{
			$validation_response .= "<li>Please fill in all information for each designator.</li>";
		}
		
		if($validation_response == '')
		{
			$course_id = $this->course_model->createCourse($this->perId);

			$this->question_model->createInstantQuestionsForCourse($course_id);

			redirect('/course/');
		}else{
			$data['validation_response'] = $validation_response;
			$data['instructor_user'] = $this->input->post('instructor_user');
			$data['instructor_name'] = $this->input->post('instructor_name');
			$data['names'] = $names;
			$data['departments'] = $departments;
			$data['numbers'] = $numbers;
			$data['sections'] = $sections;
			
			
			$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
			$breadcrumb .= " : Add Course";

			$this->template->write('breadcrumb', $breadcrumb);
			
			$this->template->write_view('content', 'course_add', $data);				
			$this->template->render();
		}
		
		

	}
	
	function manage($course_id)
	{
		$this->load->model('course_model');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		
		
		$data = array();
		
		$data['course_data'] = $this->course_model->getCourseData($course_id);
		
		$per_ids = $this->course_model->getUsersForCourse($course_id);
		
		$instructors = $this->course_model->getInstructorsForCourse($course_id);
		
		foreach($per_ids as $person)
		{
			$person->name = personIdToName($person->PER_ID);
		}
		
		$data['users'] = $per_ids;
		
		foreach($instructors as $instructor) {
			$instructor->name = personIdToName($instructor->INSTRUCTOR_ID);
		}
		
		$data["instructors"] = $instructors;

		$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
		$breadcrumb .= " > <a href='" . site_url('course/edit/' . $course_id) . "'>Edit Course</a>";
		$breadcrumb .= " : Manage Course";
		
		$this->template->write('breadcrumb', $breadcrumb);
		
		$this->template->write_view('content', 'course_manage', $data);				
		$this->template->render();	
	}
	
	function delete()
	{
		$this->load->model('course_model');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $this->input->post('course_id'));
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		
		$this->course_model->deleteCourse($this->input->post('course_id'));
		
		redirect('/course/');
	}
	
	function addNewUser()
	{
		$this->load->model('course_model');
		
		$course_id = $this->input->post('course_id');
		$per_ids = $this->input->post('user');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		
		foreach($per_ids as $per_id)
		{
			//check if they exist in the course
			$in_course = $this->course_model->checkForUserInCourse($per_id, $course_id);
			
			if(!$in_course)
			{
				$this->course_model->addUserToCourse($per_id, $course_id);
			}
			
		}
		
		redirect('/course/manage/'.$course_id);
	}
	
	function addNewInstructor() {
		
		$this->load->model('course_model');
		
		$course_id = $this->input->post('course_id');
		$per_ids = $this->input->post('instructor_user');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		
		foreach($per_ids as $per_id)
		{
			//check if they exist in the course
			$in_course = $this->course_model->isInstructorForCourse($per_id, $course_id);
			
			if(!$in_course)
			{
				$this->course_model->addInstructorToCourse($per_id, $course_id);
			}
			
		}
		
		redirect('/course/manage/'.$course_id);
		
	}
	
	
	function removeUser()
	{
		$this->load->model('course_model');
		
		$course_id = $this->input->post('course_id');
		$user = $this->input->post('per_id');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		
		$this->course_model->removeUserFromCourse($user, $course_id);
		
		redirect('/course/manage/'.$course_id);
	}
	
	function removeInstructor() {
		
		$this->load->model('course_model');

		$course_id = $this->input->post('course_id');
		$user = $this->input->post('per_id');

		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }


		$this->course_model->removeInstructorFromCourse($user, $course_id);

		redirect('/course/manage/'.$course_id);
		
	}
	
	
	function view($course_id)
	{
		$this->load->model('course_model');
		$this->load->model('question_model');
		

		
		$data = array();
		$data['course_data'] = $this->course_model->getCourseData($course_id);


		if($data['course_data']->PUBLIC == 1) {
		}
		else {
			if(!$this->isLoggedIn) 
			{
				$this->verifyLogin();
			}
			$havePerms = $this->course_model->isEnrolledInCourse($this->perId, $course_id);
		 	if(!$havePerms) {
		  	redirect(site_url("/errorController/nopermission"));
				die;
		  }
		}



		$this->course_model->loadQuestionsForCourse($course_id); 
		
		$data['question_object_array'] = $this->course_model->question_object_array;
		
		$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
		$breadcrumb .= " : View Course";
		
		if($this->isiPhone)
		{
			if($data['course_data']->PUBLIC == 1 && !$this->isLoggedIn) {
				$this->load->view("mobile/course_view", $data);	
			}
			else {
				$this->load->view("mobile/course_view", $data);			
			}
	
			$this->output->enable_profiler(false);
		}else{
			


			$this->template->write('breadcrumb', $breadcrumb);
			$this->template->write_view('content', 'course_view', $data);				
			$this->template->render();
		}
	}
	
	function viewQuestion($course_id, $questionId) {
		
		$this->load->model('course_model');
		$this->load->model('question_model');



		$data = array();
		$data['course_data'] = $this->course_model->getCourseData($course_id);

		if($data['course_data']->PUBLIC == 1) {
		}
		else {
			if(!$this->isLoggedIn) 
			{
				$this->verifyLogin();
			}
			$havePerms = $this->course_model->isEnrolledInCourse($this->perId, $course_id);
		 	if(!$havePerms) {
		  	redirect(site_url("/errorController/nopermission"));
				die;
		  }
		}



		$this->course_model->loadQuestionsForCourse($course_id);
		foreach($this->course_model->question_object_array as $question_object) {
			
			if($question_object->question_id == $questionId) {
				$data['question_object'] = $question_object;
				$this->load->view("mobile/question_views/".$question_object->question_type."_question_stub",$data);
				break;
			}
		}
		
		
		
	}
	
	function editCourseSettings() {
		
		$this->load->model('course_model');	
		$courseId = $this->input->post("course_id");
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $courseId);
	 	if(!$havePerms) {
	  		redirect(site_url("/errorController/nopermission"));
			die;
	  	}
	
		$this->course_model->editCourse($courseId);
		redirect(site_url("course/edit/" . $courseId));
		die;
	
	
	}
	
	
	function edit($course_id)
	{
		$this->load->model('course_model');
		$this->load->model('question_model');
		
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $course_id);
	 	if(!$havePerms) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		
		$data = array();
		$data['course_data'] = $this->course_model->getCourseData($course_id);
		
		$this->course_model->loadQuestionsForCourse($course_id); 

		$data['question_object_array'] = $this->course_model->question_object_array;	
		
		
		$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
		$breadcrumb .= " : Edit Course";
		$this->template->write('breadcrumb', $breadcrumb);
		$this->template->write_view('content', 'course_edit', $data);				
		$this->template->render();
	}
	
	function peopleSearchAutocompleter() 
	{

		parse_str(substr(strrchr($_SERVER['REQUEST_URI'], "?"), 1), $_GET);
		$query = $this->input->get("q");

		$results = $this->db->query("SELECT NAME, UID, OFFICE, PER_ID, MATCH (SEARCH) AGAINST ('$query' IN BOOLEAN MODE) AS score
         FROM CLA_DATA_CENTER.PERSON_SEARCH
        WHERE MATCH (SEARCH) AGAINST ('$query' IN BOOLEAN MODE)
         ORDER BY score DESC LIMIT 10");
		foreach($results->result() as $personEntry) 
		{
			
			echo $personEntry->NAME . 
					 "::" . 
					 $personEntry->UID . 
					 "::" . 
					 $personEntry->PER_ID . 
					 "::" . 
					 $personEntry->OFFICE . 
					 "\n";
		}

		/** DEBUG **/
		$this->output->enable_profiler(false);

//		echo "Colin McFadden :: mcfa0086 :: 2542 :: anderson hall\n";
		
		/** END DEBUG **/

	}
		function peopleSearchAutocompleter2() 
		{
			parse_str(substr(strrchr($_SERVER['REQUEST_URI'], "?"), 1), $_GET);
			$query = $this->input->get("q");
			$this->output->enable_profiler(true);
			$results = $this->db->query("SELECT NAME, UID, OFFICE, PER_ID, MATCH (SEARCH) AGAINST ('$query' IN BOOLEAN MODE) AS score
	         FROM CLA_DATA_CENTER.PERSON_SEARCH
	        WHERE MATCH (SEARCH) AGAINST ('$query' IN BOOLEAN MODE)
	         ORDER BY score DESC LIMIT 10");
		//	$results = $this->db->get("CLA_DATA_CENTER.PERSON_SEARCH");

			foreach($results->result() as $personEntry) 
			{

				echo $personEntry->NAME . 
						 "::" . 
						 $personEntry->UID . 
						 "::" . 
						 $personEntry->PER_ID . 
						 "::" . 
						 $personEntry->OFFICE . 
						 "\n";
			}

			/** DEBUG **/


	//		echo "Colin McFadden :: mcfa0086 :: 2542 :: anderson hall\n";

			/** END DEBUG **/

		}
	
	function isQuestionOpen($questionId) {
		$this->load->model("question_model");
		
		$questionStatus = $this->question_model->isQuestionOpen($questionId);
		echo $questionStatus;
		
	}
	
	
	function updateViewAjax()
	{


		//load models, and disable profiler
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		$this->load->model('course_model');
		

		
		
		
		//get the currently displayed question ids
		$current_question_ids = json_decode($this->input->post('json_data'), true);

		//get the course_id to load its questions
		$course_id = $this->input->post('course_id');
	
	
		$isPublic = $this->course_model->isCoursePublic($course_id);
		$havePerms = false;	
		if(!$isPublic) {		
			$havePerms = $this->course_model->isEnrolledInCourse($this->perId, $course_id);
		}
		if(!$havePerms && !$isPublic) {
	  	redirect(site_url("/errorController/nopermission"));
			die;
	  }
		
		//load questions for course
		$this->course_model->loadQuestionsForCourse($course_id); 
		//get all the question objects in an array
		$question_object_array = $this->course_model->question_object_array;

		//instantiate array to hold the new set of open questions
		$new_question_ids = array();
		//instantiate array to hold the new set of open questions by id
		//this will make it easier to get their view data further down
		//without another loop over the $question_object_array
		$question_objects_by_id = array();
		//increment variable for foreach
		$i = 0;
		//loop and populate the new_id array
		//and populate question_by_id array
		foreach($question_object_array as $question)
		{
			//add data to arrays if question is open
			if($question->question_is_open)
			{
				$new_question_ids[$i] = $question->question_id;
				$question_objects_by_id[$question->question_id] = $question;
				$i += 1;
			}
		}
		
		//get the question ids of questions to be added to the page
		$added_question_ids = array_diff($new_question_ids, $current_question_ids);
		//get the question ids of questions to be removed from the page
		$deleted_question_ids = array_diff($current_question_ids, $new_question_ids);
		
		//put the deleted ids array into the response array
		$response_array['questions_to_delete'] = $deleted_question_ids;
		$response_array['questions_to_add'] = $added_question_ids;
		
		//instantiate string to hold all view data
		$normal_views_to_add_string = " ";
		$instant_views_to_add_string = " ";
		$instant_links_to_add_string = " ";
		$normal_links_to_add_string = " ";
		foreach($added_question_ids as $question_id)
		{
			//separate instant polling questions
			if($question_objects_by_id[$question_id]->question_type == "QR" || $question_objects_by_id[$question_id]->question_type == "QA")
			{
				if($this->isiPhone) 
				{
					$instant_links_to_add_string .= "<li data-icon=\"arrow-r\" id=\"question_id_li_" . 
																					$question_objects_by_id[$question_id]->question_id . 
																					"\" class=\"ui-screen-hidden questionLink\"><a href=\"" .
																					site_url("course/viewQuestion/" . $course_id . "/".$question_objects_by_id[$question_id]->question_id) . "\" data-transition=\"slide\" data-prefetch>" . 
																					$question_objects_by_id[$question_id]->question_text . "</a></li>";
				}	else {
					$instant_views_to_add_string .= $this->load->view("question_views/".$question_objects_by_id[$question_id]->question_type."_question_stub",
					 																						array("question_object"=>$question_objects_by_id[$question_id]),
																											true);
					
				}
			}else{
				if($this->isiPhone) {
					$normal_links_to_add_string .= "<li data-icon=\"arrow-r\" id=\"question_id_li_" . 
																					$question_objects_by_id[$question_id]->question_id . 
																					"\" class=\"ui-screen-hidden questionLink\"><a href=\"" .
																					site_url("course/viewQuestion/" . $course_id . "/".$question_objects_by_id[$question_id]->question_id) . "\" data-transition=\"slide\" data-prefetch>" . 
																					$question_objects_by_id[$question_id]->question_text . "</a></li>";
				
				}	else {
					$normal_views_to_add_string .= $this->load->view("question_views/".$question_objects_by_id[$question_id]->question_type."_question_stub",
				 																						array("question_object"=>$question_objects_by_id[$question_id]),true);
				}																					
			}
		}
		
		$response_array['instant_questions_to_add'] = $instant_views_to_add_string;
		$response_array['normal_questions_to_add'] = $normal_views_to_add_string;
		$response_array['instant_links_to_add_string'] = $instant_links_to_add_string;
		$response_array['normal_links_to_add_string'] = $normal_links_to_add_string;
		$response_array['status'] = "success";
		
		echo json_encode($response_array);
	}
	
	function sendToPhone() 
	{
		$phoneNumber = $this->input->post('phoneNumber');
		
		$smsUsernameArray = array("");		
		
		$cleanedPhone = ltrim(preg_replace('/\D/', '', $phoneNumber), 01);
		$this->load->helper('GoogleVoice');
		$this->gv = new GoogleVoice($smsUsernameArray[rand(0,2)], '');
		$randValue = rand(0,10000);

		$this->gv->sendSMS($cleanedPhone, $randValue);
		$this->output->enable_profiler(FALSE);
		echo $randValue;		
	}
	
	function savePhoneNumber() 
	{
		$phoneNumber = $this->input->post('phoneNumber');

		$cleanedPhone = ltrim(preg_replace('/\D/', '', $phoneNumber), 01);	
		error_log($cleanedPhone);
		$this->load->model('user_model');
		$this->user_model->addPhoneNumberForPerId($this->perId, $cleanedPhone);		
	}
	
	
}
