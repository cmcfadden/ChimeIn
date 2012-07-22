<?php

class Question_model extends CI_Model {
	
	public $question_text;
	public $created_at;
	public $question_is_open;
	public $question_type;
	public $question_id;
	public $answer_count;
	public $result_count;
	public $answered_by_user;
	public $anonymous;
	public $answer_array = array();
	public $course_id;
	
	function Question_model($questionId = null)
	{
	
		parent::__construct();
		if($questionId != null) {
			$this->loadQuestionAndAnswers($questionId);
		}
	}
	
	function loadQuestionAndAnswers($question_id) 
	{
		$this->db->where("QUESTION_ID", $question_id);
		$question_result = $this->db->get("Question")->row();
		
		$this->loadAnswersForQuestion($question_id);
		
		$this->loadTotalResultsForQuestion($question_id);
		
		$this->loadIfAnsweredByUser($question_id, $this->perId);
		
		$this->question_text = $question_result->QUESTION_TEXT;
		$this->created_at = $question_result->CREATED_AT;
		$this->question_is_open = $question_result->QUESTION_IS_OPEN;
		$this->question_type = $question_result->QUESTION_TYPE;
		$this->question_id = $question_result->QUESTION_ID;
		$this->anonymous = $question_result->ANONYMOUS;
		$this->course_id = $question_result->COURSE_ID;
	}
	
	function isQuestionOpen($question_id) {
		$this->db->select("QUESTION_IS_OPEN");		
		$this->db->select("COURSE_ID");
		$this->db->where("QUESTION_ID", $question_id);
		$result= $this->db->get("Question")->row();
		//total hack
		if(!$result->QUESTION_IS_OPEN) {
			return $result->COURSE_ID;
		}
		else {
			return "true";
		}
		
	}
	
	function loadAnswersForQuestion($questionId) 
	{
		
		$this->db->where("QUESTION_ID", $questionId);
		$this->db->order_by("ANSWER_ID", "asc");
		$answer_results = $this->db->get("Answer");
		
		$this->answer_count = $answer_results->num_rows();

		$this->answer_array = $answer_results->result();
	}
	
	function loadIfAnsweredByUser($question_id, $per_id)
	{
		$this->db->where('QUESTION_ID', $question_id);
		$this->db->where('PER_ID', $per_id);
		$query = $this->db->get('Result');
		
		if($query->num_rows() > 0)
		{
			$this->answered_by_user = true;
		}else{
			$this->answered_by_user = false;
		}
		
	}
	
	
	function createQuestion()
	{
		$question_data = array();
		$answer_data = array();
		
		$question_data['COURSE_ID'] = $this->input->post('course_id');
		$question_data['QUESTION_TEXT'] = $this->input->post('question_text');
		$question_data['QUESTION_TYPE'] = $this->input->post('question_type');
		$question_data['QUESTION_IS_OPEN'] = $this->input->post('question_is_open');
		$question_data['CREATED_AT'] = date( 'Y-m-d H:i:s', time() );
		
		$question_data['ANONYMOUS'] = $this->input->post('anonymous')?1:0;		
		
		
		$this->db->insert('Question', $question_data);
		$question_id = $this->db->insert_id();
		
		$answer_data['QUESTION_ID'] = $question_id;
		
		$answers_array = $this->input->post('answers');

		if(is_array($answers_array))
		{
			foreach($answers_array as $answer)
			{
				$answer_data['ANSWER_TEXT'] = $answer;
				$this->db->insert('Answer', $answer_data);
			}
		}

	}
	
	function createQuestionWithParams($courseId, $questionText, $questionType, $answersArray) {
		
		$question_data = array();
		$answer_data = array();
		
		$question_data['COURSE_ID'] = $courseId;
		$question_data['QUESTION_TEXT'] = $questionText;
		$question_data['QUESTION_TYPE'] = $questionType;
		$question_data['QUESTION_IS_OPEN'] = 1;
		$question_data['CREATED_AT'] = date( 'Y-m-d H:i:s', time() );
		
		$question_data['ANONYMOUS'] = 1;		
		
		
		$this->db->insert('Question', $question_data);
		$question_id = $this->db->insert_id();
		
		$answer_data['QUESTION_ID'] = $question_id;
		
		$answers_array = $answersArray;

		if(is_array($answers_array))
		{
			foreach($answers_array as $answer)
			{
				$answer_data['ANSWER_TEXT'] = $answer;
				$this->db->insert('Answer', $answer_data);
			}
		}
		return $question_id;
		
	}
	
	function getCourseForQuestion($questionId) {
		$this->db->where("QUESTION_ID", $questionId);
		$result = $this->db->get("Question")->row();
		return $result->COURSE_ID;
	}
	
	
	
	function toggleQuestionStatus()
	{
		$update_data['QUESTION_IS_OPEN'] = $this->input->post('question_is_open');
		
		$this->db->where('QUESTION_ID', $this->input->post("question_id"));
		$this->db->update('Question', $update_data);
	}
	
	function openQuestion() {
		$this->db->where("QUESTION_ID", $this->question_id);
		$this->db->set("QUESTION_IS_OPEN", 1);
		$this->db->update("Question");		
	}
	
	
	function closeQuestion() {

		$this->db->where("QUESTION_ID", $this->question_id);
		$this->db->set("QUESTION_IS_OPEN", 0);
		$this->db->update("Question");
	}
	
	function deleteQuestionAndContent()
	{
		$question_id = $this->input->post('question_id');
		
		$this->db->where('QUESTION_ID', $question_id);
		$this->db->delete('Question');
		
		$this->db->where('QUESTION_ID', $question_id);
		$this->db->delete('Answer');
		
		$this->db->where('QUESTION_ID', $question_id);
		$this->db->delete('Result');
		
	}
	
	function createInstantQuestionsForCourse($course_id)
	{
		//create quick ask
		$question_data = array();
		
		$question_data['COURSE_ID'] = $course_id;
		$question_data['QUESTION_TEXT'] = "Quick Ask";
		$question_data['QUESTION_TYPE'] = "QA";
		$question_data['QUESTION_IS_OPEN'] = 0;
		$question_data['CREATED_AT'] = date( 'Y-m-d H:i:s', time() );
		$question_data['ANONYMOUS'] = 0;		
		
		$this->db->insert('Question', $question_data);
		
		//create quick free response
		
		$question_data['COURSE_ID'] = $course_id;
		$question_data['QUESTION_TEXT'] = "Quick Response";
		$question_data['QUESTION_TYPE'] = "QR";
		$question_data['QUESTION_IS_OPEN'] = 0;
		$question_data['CREATED_AT'] = date( 'Y-m-d H:i:s', time() );
		$question_data['ANONYMOUS'] = 0;		
		
		$this->db->insert('Question', $question_data);
	}
	
	function getTotalAnswersForQuestion($question_id)
	{
		$this->db->where('QUESTION_ID', $question_id);
		$query = $this->db->get('Answer');
		
		$this->answer_count = $query->num_rows();
		
		return $this->answer_count;
	}
	
	function loadTotalResultsForQuestion($question_id)
	{
		$this->db->select('*');
		$this->db->from('Result');
		$this->db->where('QUESTION_ID', $question_id);
		$query = $this->db->get();
		
		$this->result_count = $query->num_rows();
	}
	
	function buildQuickAskAnswers()
	{
		$answer_data = array();
		 
		$answer_data['QUESTION_ID'] = $this->input->post('question_id');
		
		$num_answers = $this->input->post('answer_count');
		
		$charIndex = 65;
		for($i = 0; $i < $num_answers; $i++)
		{
			$answer_data['ANSWER_TEXT'] = "Answer ".chr($charIndex);
			$this->db->insert('Answer', $answer_data);
			$charIndex += 1;
		}
	}
	
	function destroyQuickResponseResults()
	{
		$this->db->where('QUESTION_ID', $this->input->post('question_id'));
		$this->db->delete('Result');
	}
	
	function destroyQuickAskAnswers()
	{
		$this->db->where('QUESTION_ID', $this->input->post('question_id'));
		$this->db->delete('Answer');
		
		$this->db->where('QUESTION_ID', $this->input->post('question_id'));
		$this->db->delete('Result');
	}
	
	function getResultsForCourseQuestionsFromAjax()
	{
		$course_id = $this->input->post('course_id');
		
		$this->db->select('COUNT(Result.RESULT_ID) AS count');
		$this->db->select('Result.QUESTION_ID AS question_id');
		$this->db->from('Result');
		$this->db->from('Question');
		$this->db->where('Question.COURSE_ID', $course_id);
		$this->db->where('Question.QUESTION_ID', 'Result.QUESTION_ID', false);
		$this->db->group_by('Result.QUESTION_ID');
		$query = $this->db->get();
		
		return $query->result();		
	}
	
	function updateQuestionTextForQuestion($question_id)
	{
		$update_data = array('QUESTION_TEXT' => $this->input->post('question_text'));
		$this->db->where('QUESTION_ID', $question_id);
		$this->db->update('Question', $update_data);
	}
	
	function toggleAnonymousForQuestion($question_id)
	{
		$anonymous = $this->input->post('anonymous');
		$anonymous_before = $this->input->post('anonymous_before');
		
		if($anonymous != $anonymous_before)
		{
			if(!$anonymous)
			{
				$update_data = array('ANONYMOUS' => $anonymous);
				$this->db->where('QUESTION_ID', $question_id);
				$this->db->update('Question', $update_data);
				
				//strip out per_id data
				$update_data = array('PER_ID' => "0");
				$this->db->where('QUESTION_ID', $question_id);
				$this->db->update('Result', $update_data);
				
			}else{
				//just toggle to anonymous
				$update_data = array('ANONYMOUS' => $anonymous);
				$this->db->where('QUESTION_ID', $question_id);
				$this->db->update('Question', $update_data);
			}
		}
	}
	
	function removeDeletedAnswers()
	{
		$answers_to_delete = $this->input->post('answers_to_delete');
		
		if(count($answers_to_delete) > 0)
		{
			//delete answers by id
			foreach($answers_to_delete as $answer)
			{
				$this->db->where('ANSWER_ID', $answer);
				$this->db->delete('Answer');
				
				$this->db->where('RESULT_CONTENT', $answer);
				$this->db->delete('Result');
			}

		}
	}
	
	function addNewAnswersToQuestion($question_id)
	{
		$answer_data = array();
		
		$answer_data['QUESTION_ID'] = $question_id;
		
		$answers_array = $this->input->post('answers');

		if(is_array($answers_array))
		{
			foreach($answers_array as $answer)
			{
				$answer_data['ANSWER_TEXT'] = $answer;
				$this->db->insert('Answer', $answer_data);
			}
		}
	}

	function updateCache() {

		$courseId = $this->getCourseForQuestion($this->input->post("question_id"));

		if(!file_exists("cache/questionCache/" . $courseId)) {
			mkdir("cache/questionCache/" . $courseId);
		}

		$cacheDir = opendir("cache/questionCache/" . $courseId);


		while (($file = readdir($cacheDir)) !== false) {

			if( $file != "." && $file != ".." ) {
				unlink("cache/questionCache/" . $courseId . "/" . $file);
			}
		}
		$this->db->where("COURSE_ID", $courseId);
		$this->db->where("QUESTION_IS_OPEN", 1);
		$result = $this->db->get("Question");
		$cacheArray = array();
		foreach($result->result() as $entry) {
			$cacheArray[] = $entry->QUESTION_ID;
			
		}
		
		if(count($cacheArray) > 0) {
			sort($cacheArray);
		
			$cacheString = join($cacheArray, "_");
			$fp= fopen("cache/questionCache/".$courseId."/".$cacheString, "w");
			fwrite($fp, "clearTimeout(globalTimerForUpdate);\n");
			fclose($fp);
		}
		else {
			$fp= fopen("cache/questionCache/".$courseId."/"."none", "w");
			fwrite($fp, "clearTimeout(globalTimerForUpdate);\n");
			fclose($fp);
		}
		
	}
	
	
	function setSortOrder($questionId, $order) {
		$this->db->set("SORT_ORDER", $order);
		$this->db->where("QUESTION_ID", $questionId);
		$this->db->update("Question");
	}
	

	

}

?>