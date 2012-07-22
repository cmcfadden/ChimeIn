<?php
require_once('authParent.php');
class Result extends authParent {


	public $filterSize = 45;

	function Result()
	{
		parent::authParent();	

		$this->load->helper('url');
		$this->load->helper('array');
		$this->load->helper('html');
		$this->load->helper('form');
		$this->load->helper('json');
		$this->load->helper('user');
		//		$this->output->enable_profiler(TRUE);

		if(!$this->isLoggedIn && $this->uri->segment(2) != "addNew") 
		{
			$this->verifyLogin();
		}


	}

	function index()
	{

	}

	function testController() {

		$this->load->library("languageProcessing");
		$this->load->helper("array_helper");



		$lemmatizedArray = $this->languageprocessing->lemmatizeArray(array("relationships"=>1));
		$stemmedArray = $this->languageprocessing->stemArray($lemmatizedArray);
		print_r($stemmedArray);


	}

	function view($question_id, $asJson = false, $asTable=false, $asCSV=false)
	{

		$this->load->model('course_model');
		$this->load->model('result_model');
		$this->load->model('question_model');

		$question_model = new Question_model($question_id);
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}


		$data = array();
		$data['result_data'] = $this->result_model->getResultsForQuestion($question_id, $question_model->question_type);

		$data['binnedResults'] = $this->result_model->getBinnedResultsForQuestion($question_id, $question_model->question_type);


		if($question_model->question_type == "FR" || $question_model->question_type == "QR") {

			$this->load->library("languageProcessing");
			$this->load->helper("array_helper");

			if($this->course_model->disableLanagueProcessingForCourse($question_model->course_id)) {

				$sortedArray = $this->languageprocessing->generateMultidimensionalArray($data['binnedResults']);
				$arrayWithGroups = $this->result_model->addGroupsToArray($sortedArray);
				$highPassFilteredArray = highPassFilterArray($arrayWithGroups, $this->filterSize);
				if($this->course_model->showRelatedWordsForCourse($question_model->course_id)) {
					$filteredArray = $this->result_model->findRelatedTerms($highPassFilteredArray);					
				}
				else {
					$filteredArray= $highPassFilteredArray;
				}


			}
			else {

				$lemmatizedArray = $this->languageprocessing->lemmatizeArray($data['binnedResults']);

				$stemmedArray = $this->languageprocessing->stemArray($lemmatizedArray);

				$sortedArray = $this->languageprocessing->generateLemmaSourceMapping($stemmedArray, $data['binnedResults']);


				$arrayWithGroups = $this->result_model->addGroupsToArray($sortedArray);
				$highPassFilteredArray = highPassFilterArray($arrayWithGroups,  $this->filterSize);

				// yes, this variable should be named somethign else.  sorry.
				if($this->course_model->showRelatedWordsForCourse($question_model->course_id)) {
					$filteredArray = $this->result_model->findRelatedTerms($highPassFilteredArray);					
				}
				else {
					$filteredArray= $highPassFilteredArray;
				}


			}

			$minMaxArray = getMinMaxForArray($filteredArray, "count");
			$filteredArray['minIgnore'] = $minMaxArray['min'];
			$filteredArray['maxIgnore'] = $minMaxArray['max'];

			// need to shuffle

			$arrayKeys = array_keys($filteredArray);
			shuffle($arrayKeys);
			foreach($arrayKeys as $sourceKey) {
				$shuffledArray[$sourceKey] = $filteredArray[$sourceKey];
			}



			$data['sortedArray'] = $shuffledArray;


		}


		$data['question_model'] = $question_model;
		$data['answerArray'] = $this->result_model->answerArray;

		$data['colors'] = array("#A5C6A5", "#F29B53", "#B56F47", "#D9B735", "#EFDB22", "#8BD994", "#3C8576", "#BA5553", "#E19A2E", "#FFC680");

		if($asJson == "true") 
		{
			$this->output->enable_profiler(false);
			$this->load->view("result_json_views/" . $question_model->question_type . "_result_json_stub", $data);

		}
		elseif($asTable == "true") {
			$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
			$breadcrumb .= " > <a href='".site_url('course/edit/'.$question_model->course_id)."'>Edit Course</a>";
			$breadcrumb .= " > <a href='".site_url('result/view/'.$question_id)."'>View Results</a>";
			$breadcrumb .= " : View Results Table";

			$this->template->write('breadcrumb', $breadcrumb);
			$this->template->write_view("content", "resultsTable", $data);
			$this->template->render();

		}
		elseif($asCSV == "true") {
			$this->output->enable_profiler(FALSE);
			$this->load->view("resultCSV", $data);

		}
		else{

			$breadcrumb = "<a href='".site_url('course/')."'>Home</a>";
			$breadcrumb .= " > <a href='".site_url('course/edit/'.$question_model->course_id)."'>Edit Course</a>";
			$breadcrumb .= " : View Results";

			$this->template->write('breadcrumb', $breadcrumb);
			$this->template->write_view('content', 'result_view', $data);				
			$this->template->render();
		}
	}

	function addNew()
	{

		$this->output->enable_profiler(false);
		$this->load->model('result_model');
		$this->load->model('question_model');
		$this->load->model('course_model');





		$question_type = $this->input->post('question_type');
		$answer = $this->input->post('answer');
		$question_id = $this->input->post('question_id');


		$question_model = new Question_model($question_id);
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


		if(!$answer)
		{
			//didn't submit an answer
			echo "fail";
			return;
		}
		if($question_type == "FR" || $question_type == "QR")
		{
			$duplicate = $this->result_model->checkForResultToQuestionByUser($answer, $question_id, $this->perId);
			if($duplicate)
			{
				echo "duplicate";
				return;
			}
		}

		if($question_type == "QA" || $question_type == "MC" || $question_type == "TF")
		{
			
			$this->result_model->buildIndexForAnswers($question_id);
			$answerId=null;
			if(count($this->result_model->answerArray)>0) {
				foreach($this->result_model->answerArray as $key=>$character) {
					if(strtolower($key) == strtolower($answer)) {
						$answerId = $key;
					}
				}
			}

			if($answerId == null) {
				echo "fail";
				return;
			}
			else {
				//check if already answered
				if($this->result_model->hasResultForUser($this->perId) && !$isPublic)
				{
					$response = $this->result_model->updateResult($this->perId);
					echo $response;
				}else{
					//create new
					$response = $this->result_model->createResult($this->perId);
					echo $response;
				}
			}
			

		}else{
			$response = $this->result_model->createResult($this->perId);
			echo $response;
		}


	}

	function checkForResultsAjax()
	{
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		$this->load->model('result_model');

		$question_id = $this->input->post('question_id');

		$num_results = $this->result_model->getResultsCountForQuestion($question_id);

		if($num_results > 0)
		{
			echo 1;
		}else{
			echo 0;
		}
	}


	function updateResultForFRAjax()
	{
		//load models, and disable profiler
		$this->output->enable_profiler(false);
		$this->load->model('question_model');
		$this->load->model('result_model');
		$this->load->model("course_model");
		$question_model = new Question_model($this->input->post('question_id'));

		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}

		
		$filterNumbers = ($this->input->post("filterNumbers")=="true")?true:false;
		
		//get the currently displayed result ids
		$current_result_ids = json_decode($this->input->post('json_data'), true);
		$exclude_list = json_decode($this->input->post('exclude_list'), true);

		for($i=0; $i<count($exclude_list); $i++) {
			$exclude_list[$i] = htmlspecialchars_decode($exclude_list[$i], ENT_QUOTES);
		}

		if(isset($exclude_list) and count($exclude_list)>0) {
			$this->result_model->addExcludeArray($exclude_list);			
		}


		$word_filter = $this->input->post('selected_word');

		//get the question_id to load its results
		$question_id = $this->input->post('question_id');
		//load results for question
		$results_array = $this->result_model->getResultsForQuestion($question_id, "QR");

		// build binned array for word cloud

		$binnedArray = $this->result_model->getBinnedResultsForQuestion($question_id, "QR", $filterNumbers);


		$this->load->library("languageProcessing");
		$this->load->helper("array_helper");





		if($this->course_model->disableLanagueProcessingForCourse($question_model->course_id)) {

			$sortedArray = $this->languageprocessing->generateMultidimensionalArray($binnedArray);
			$arrayWithGroups = $this->result_model->addGroupsToArray($sortedArray);
			$highPassFilteredArray = highPassFilterArray($arrayWithGroups, $this->filterSize);
			if($this->course_model->showRelatedWordsForCourse($question_model->course_id)) {
				$filteredArray = $this->result_model->findRelatedTerms($highPassFilteredArray);					
			}
			else {
				$filteredArray= $highPassFilteredArray;
			}


		}
		else {

			$lemmatizedArray = $this->languageprocessing->lemmatizeArray($binnedArray);
			$stemmedArray = $this->languageprocessing->stemArray($lemmatizedArray);
			$sortedArray = $this->languageprocessing->generateLemmaSourceMapping($stemmedArray, $binnedArray);

			$arrayWithGroups = $this->result_model->addGroupsToArray($sortedArray);

			$highPassFilteredArray = highPassFilterArray($arrayWithGroups, $this->filterSize);

			// yes, this variable should be named somethign else.  sorry.
			if($this->course_model->showRelatedWordsForCourse($question_model->course_id)) {
				$filteredArray = $this->result_model->findRelatedTerms($highPassFilteredArray);					
			}
			else {
				$filteredArray= $highPassFilteredArray;
			}

		}


		$minMaxArray = getMinMaxForArray($filteredArray, "count");
		$filteredArray['minIgnore'] = $minMaxArray['min'];
		$filteredArray['maxIgnore'] = $minMaxArray['max'];


		$new_result_ids = array();
		$results_by_id = array();

		$i = 0;
		foreach($results_array as $result)
		{
			$new_result_ids[$i] = $result->RESULT_ID;
			$results_by_id[$result->RESULT_ID] = $result;
			$i += 1;		
		}

		//get the question ids of questions to be added to the page
		$added_result_ids = array_diff($new_result_ids, $current_result_ids);

		$response_array['results_to_add'] = $added_result_ids;

		function sortByLength($a,$b){
			if(strlen($a) == strlen($b)) return 0;
			return (strlen($a) > strlen($b) ? -1 : 1);
		}


		//instantiate string to hold all view data
		$results_to_add_string = " ";

		if(isset($word_filter) && isset($filteredArray[$word_filter])) {
			$arrayOfPossibleWords = $filteredArray[$word_filter]["sourceWords"];
		}
		else {
			$arrayOfPossibleWords =array();
		}

		foreach($added_result_ids as $result_id)
		{
			if($word_filter != "")
			{


				$wordArray = str_word_count(strtolower($results_by_id[$result_id]->RESULT_CONTENT), 1, "0123456789");


				if(count(array_intersect($arrayOfPossibleWords, $wordArray))>0)
				{

					$tokenized = str_word_count(strip_tags($results_by_id[$result_id]->RESULT_CONTENT), 1, "0123456789");
					usort($tokenized, "sortByLength");
					error_log(print_r($arrayOfPossibleWords,true));
					$highlightedPhrase = strip_tags($results_by_id[$result_id]->RESULT_CONTENT);
					foreach($tokenized as $token) 
					{

						if(in_array(strtolower($token), $arrayOfPossibleWords)) 
						{


							$replacementWord = $token;

							$highlightedPhrase = str_ireplace($replacementWord, "<span class='highlighted'>" . $replacementWord . "</span>", $highlightedPhrase);

						}
					}






					if($question_model->anonymous) 
					{
						$results_to_add_string .= "<li class='result_node' id='result_id".$results_by_id[$result_id]->RESULT_ID."' >"
						. "<span class=\"responseTime\">" . $results_by_id[$result_id]->CREATED_AT . " </span>" 
						. "<span class=\"responseContent\">" . $highlightedPhrase . "</span>"
						. "</li>";

					}	else {
						$results_to_add_string .= "<li class='result_node' id='result_id".$results_by_id[$result_id]->RESULT_ID."' >"
						. "<span class=\"responsePerson\">" . personIdToName($results_by_id[$result_id]->PER_ID) . " wrote: </span>" 
						. "<span class=\"responseContent\">" . $highlightedPhrase . "</span>"
						. "</li>";

					}
				}

			}else{
				if($question_model->anonymous) {
					$results_to_add_string .= "<li class='result_node' id='result_id".$results_by_id[$result_id]->RESULT_ID."' >"
					. "<span class=\"responseTime\">" . $results_by_id[$result_id]->CREATED_AT . " </span>" 
					. "<span class=\"responseContent\">" . strip_tags($results_by_id[$result_id]->RESULT_CONTENT) . "</span>"
					. "</li>";
				}
				else {
					$results_to_add_string .= "<li class='result_node' id='result_id".$results_by_id[$result_id]->RESULT_ID."' >"
					. "<span class=\"responsePerson\">" . personIdToName($results_by_id[$result_id]->PER_ID) . " wrote: </span>" 
					. "<span class=\"responseContent\">" . strip_tags($results_by_id[$result_id]->RESULT_CONTENT) . "</span>"
					. "</li>";					
				}

			}
		}

		$response_array['results_to_add_string'] = $results_to_add_string;
		$response_array['sortedArray'] = $filteredArray;



		echo json_encode($response_array);

	}


	function createGroup() {
		
		
		
		$groupName = $this->input->post("groupName");
		$questionId = $this->input->post("question_id");
		$firstItem = $this->input->post("groupItem1");
		$secondItem = $this->input->post("groupItem2");

		$this->load->model('question_model');
		$this->load->model("course_model");
		$question_model = new Question_model($questionId);
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}


		$this->load->model('result_model');		

		$result_model = new Result_model();
		
		$cleanedGroupName = $result_model->createGroup(htmlspecialchars_decode($groupName, ENT_QUOTES), $questionId);
		
		$result_model->addItemToGroup($cleanedGroupName, $firstItem, $questionId);
		$result_model->addItemToGroup($cleanedGroupName, $secondItem, $questionId);
		
		
		
		
	}


	function updateGroup() {
		
		$groupName = $this->input->post("groupName");
		$questionId = $this->input->post("question_id");
		$newItem = $this->input->post("newItem");
		$this->load->model('result_model');		
		$this->load->model('question_model');
		$this->load->model("course_model");
		$question_model = new Question_model($questionId);
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}


		$result_model = new Result_model();
		$result_model->addItemToGroup($groupName, $newItem, $questionId);

		
	}


	function removeWordFromGroup() {
		$groupName = $this->input->post("groupName");
		$wordName = $this->input->post("wordName");
		
		$questionId = $this->input->post("question_id");

		$this->load->model('result_model');		
		$this->load->model('question_model');
		$this->load->model("course_model");
		$question_model = new Question_model($questionId);
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}
		$this->load->library("languageProcessing");

		$result_model = new Result_model();
		$result_model->removeWordFromGroup($groupName, $wordName, $questionId);
	}


	function destroyGroup() {
		
		$groupName = $this->input->post("groupName");
		$questionId = $this->input->post("question_id");

		$this->load->model('result_model');		
		$this->load->model('question_model');
		$this->load->model("course_model");
		$question_model = new Question_model($questionId);
		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}


		$result_model = new Result_model();
		$result_model->destroyGroup($groupName, $questionId);

		
	}


	function getResultsWithWordAjax()
	{
		$this->output->enable_profiler(false);
		$this->load->model('result_model');
		$this->load->model('question_model');
		$this->load->model('course_model');

		$question_model = new Question_model($this->input->post('question_id'));



		$havePerms = $this->course_model->isInstructorForCourse($this->perId, $question_model->course_id);
		if(!$havePerms) {
			redirect(site_url("/errorController/nopermission"));
			die;
		}


		$exclude_list = json_decode($this->input->post('exclude_list'), true);

		for($i=0; $i<count($exclude_list); $i++) {
			$exclude_list[$i] = htmlspecialchars_decode($exclude_list[0], ENT_QUOTES);
		}

		if(isset($exclude_list) && count($exclude_list)>0) {
			$this->result_model->addExcludeArray($exclude_list);	
		}
		$results = $this->result_model->getResultsForQuestion($this->input->post('question_id'), $question_model->question_type);


		$binnedArray = $this->result_model->getBinnedResultsForQuestion($this->input->post('question_id'), "QR");


		$this->load->library("languageProcessing");
		$this->load->helper("array_helper");


		if($this->course_model->disableLanagueProcessingForCourse($question_model->course_id)) {

			$sortedArray = $this->languageprocessing->generateMultidimensionalArray($binnedArray);
			$arrayWithGroups = $this->result_model->addGroupsToArray($sortedArray);
			$highPassFilteredArray = highPassFilterArray($arrayWithGroups, $this->filterSize);
			if($this->course_model->showRelatedWordsForCourse($question_model->course_id)) {
				$filteredArray = $this->result_model->findRelatedTerms($highPassFilteredArray);					
			}
			else {
				$filteredArray= $highPassFilteredArray;
			}


		}
		else {
			$lemmatizedArray = $this->languageprocessing->lemmatizeArray($binnedArray);
			$stemmedArray = $this->languageprocessing->stemArray($lemmatizedArray);
			$sortedArray = $this->languageprocessing->generateLemmaSourceMapping($stemmedArray, $binnedArray);
			$arrayWithGroups = $this->result_model->addGroupsToArray($sortedArray);
			$highPassFilteredArray = highPassFilterArray($arrayWithGroups, $this->filterSize, "count");

			// yes, this variable should be named somethign else.  sorry.
			if($this->course_model->showRelatedWordsForCourse($question_model->course_id)) {
				$filteredArray = $this->result_model->findRelatedTerms($highPassFilteredArray);					
			}
			else {
				$filteredArray= $highPassFilteredArray;
			}
		}







		function sortByLength($a,$b){
			if(strlen($a) == strlen($b)) return 0;
			return (strlen($a) > strlen($b) ? -1 : 1);
		}


		$word = $this->input->post('selected_word');

		$arrayOfPossibleWords=array();
		if(strlen($word)>0) {
			if(array_key_exists($word,$filteredArray)) {
				$arrayOfPossibleWords = $filteredArray[$word]["sourceWords"];							
			}

		}



		$filtered_results = "";
		foreach($results as $result)
		{
			if(count($arrayOfPossibleWords)==0)
			{

				if($question_model->anonymous) 
				{
					$filtered_results .= "<li class='result_node' id='result_id".$result->RESULT_ID."' >"
					. "<span class=\"responseTime\">" . $result->CREATED_AT . " </span>" 
					. "<span class=\"responseContent\">" . strip_tags($result->RESULT_CONTENT) . "</span>"
					. "</li>";

				}	else {
					$filtered_results .= "<li class='result_node' id='result_id".$result->RESULT_ID."' >"
					. "<span class=\"responsePerson\">" . personIdToName($result->PER_ID) . " wrote: </span>" 
					. "<span class=\"responseContent\">" . strip_tags($result->RESULT_CONTENT) . "</span>"
					. "</li>";

				}
			}else{

				$wordArray = str_word_count(strtolower($result->RESULT_CONTENT), 1, "0123456789");


				if(count(array_intersect($arrayOfPossibleWords, $wordArray))>0)
				{
					$tokenized = str_word_count(strip_tags($result->RESULT_CONTENT), 1, "0123456789");
					usort($tokenized, "sortByLength");

					$highlightedPhrase = strip_tags($result->RESULT_CONTENT);
					foreach($tokenized as $token) 
					{

						if(in_array(strtolower($token), $arrayOfPossibleWords)) 
						{


							$replacementWord = $token;

							$highlightedPhrase = str_ireplace($replacementWord, "<span class='highlighted'>" . $replacementWord . "</span>", $highlightedPhrase);

						}
					}






					if($question_model->anonymous) 
					{
						$filtered_results .= "<li class='result_node' id='result_id".$result->RESULT_ID."' >"
						. "<span class=\"responseTime\">" . $result->CREATED_AT . " </span>" 
						. "<span class=\"responseContent\">" . $highlightedPhrase . "</span>"
						. "</li>";

					}	else {
						$filtered_results .= "<li class='result_node' id='result_id".$result->RESULT_ID."' >"
						. "<span class=\"responsePerson\">" . personIdToName($result->PER_ID) . " wrote: </span>" 
						. "<span class=\"responseContent\">" . $highlightedPhrase . "</span>"
						. "</li>";

					}
				}
			}

		}

		echo json_encode($filtered_results);
	}



}
