<?php

class Course_model extends CI_Model {
	
	
	function Course_model()
	{
		parent::__construct();
		
		$this->load->helper('user');
	}

	
	function createCourse($perId)
	{
		$course_data = array();
		$designator_data = array();
		
		$course_data['YEAR'] = $this->input->post('year');
		$course_data['SEMESTER'] = $this->input->post('semester');
//		$course_data['INSTRUCTOR_ID'] = $this->input->post('instructor_user');
//		$course_data['CREATOR_ID'] = $perId;

		$course_data['PUBLIC'] = ($this->input->post('public')=="on")?1:0;
		$course_data['DISABLE_LANGUAGE_PARSING'] = ($this->input->post('disable_language_parsing')=="on")?1:0;
		$course_data['SHOW_RELATED_WORDS'] = ($this->input->post('show_related_words')=="on")?1:0;
		$this->db->insert('Course', $course_data);
		$course_id = $this->db->insert_id();
		
		$names = $this->input->post('name');
		$departments = $this->input->post('department');
		$numbers = $this->input->post('number');
		$sections = $this->input->post('section');

		$instructors = $this->input->post("instructor_user");
		
		$designators = count($names);
		
		$i = 0;
		for($i; $i < $designators; $i++)
		{
			$designator_data['FRIENDLY_TITLE'] = $names[$i];
			$designator_data['DEPARTMENT'] = $departments[$i];
			$designator_data['NUMBER'] = $numbers[$i];
			$designator_data['SECTION'] = $sections[$i];

			if($designator_data['SECTION'] == 0) 
			{
				$designator_data['SECTION'] = 1;
			}

			$designator_data['COURSE_ID'] = $course_id;
			$this->db->insert('Designator', $designator_data);
		}
		
		$i=0;
		$instructorIsCreator =false;
		for($i; $i<count($instructors); $i++) {
			$this->db->set("INSTRUCTOR_ID", $instructors[$i]);
			$this->db->set("COURSE_ID", $course_id);
			$this->db->insert("Instructors");
			if($instructors[$i] == $perId) {
				$instructorIsCreator = true;
			}
		}
		
		if(!$instructorIsCreator) {
			$this->db->set("INSTRUCTOR_ID", $perId);
			$this->db->set("COURSE_ID", $course_id);
			$this->db->insert("Instructors");
		}
		
		mkdir("cache/questionCache/" . $course_id, 0777);
		chmod("cache/questionCache/" . $course_id, 0777);
		$fp= fopen("cache/questionCache/".$course_id."/"."none", "w");
                fwrite($fp, "clearTimeout(globalTimerForUpdate);\n");
                fclose($fp);		
		mkdir("cache/answerCache/" . $course_id, 0777);
		chmod("cache/answerCache/" . $course_id, 0777);
		return $course_id;
	}	
	

	function getInstructedCourses($perId)
	{
		$this->db->select('*');
		$this->db->from('Instructors');
		//$this->db->join('Designator', 'Course.COURSE_ID = Designator.COURSE_ID');
		$this->db->where('INSTRUCTOR_ID', $perId);
		
		$query = $this->db->get();
		
		$courseArray = array();
		
		foreach($query->result() as $courseEntry) 
		{
			$this->db->where("COURSE_ID", $courseEntry->COURSE_ID);
			$designatorQuery = $this->db->get("Designator");
			$designatorStringArray = array();
			$friendlyTitleArray = array();
			
			foreach($designatorQuery->result() as $designatorEntry) 
			{
				$designatorStringArray[] = $designatorEntry->DEPARTMENT . 
																	 $designatorEntry->NUMBER .
																	 (($designatorEntry->SECTION>1)?(".".str_pad($designatorEntry->SECTION, 3, 0, STR_PAD_LEFT)):null);
																	
				$friendlyTitleArray[] = $designatorEntry->FRIENDLY_TITLE;
			}
			
			$courseEntry->designatorStringArray = $designatorStringArray;
			$courseEntry->friendlyTitleArray = $friendlyTitleArray;
			$courseArray[] = $courseEntry;
			
		}
		
		return $courseArray;
	}
	
	
	function isInstructorForCourse($perId, $courseId) {
		
		$courseArray = $this->getInstructedCourses($perId);
		
		foreach($courseArray as $courseEntry) {
			if($courseEntry->COURSE_ID == $courseId) {
				return true;
			}
		}
		
		return false;
	}

	
	
	function getEnrolledCourses($perId)
	{
		// apologies for writing the SQL	
		$sql = "SELECT * FROM Course,Course_User, Designator WHERE Course.COURSE_ID = Designator.COURSE_ID AND Course.COURSE_ID = Course_User.COURSE_ID AND Course_User.PER_ID = " . $perId . " GROUP BY Course.COURSE_ID";
		$query = $this->db->query($sql);
		
		if($query->num_rows() > 0) 
		{
			$manualQuery = $query->result();
		}
		
		$sql = "SELECT * FROM CLA_DATA_CENTER.PERSON,CLA_DATA_CENTER.STUDENT_ENROLLMENT, CLA_DATA_CENTER.STUDENT_ENROLLMENT_CURRENT_TERM WHERE  CLA_DATA_CENTER.STUDENT_ENROLLMENT.EMPLID = CLA_DATA_CENTER.PERSON.EMPLID AND CLA_DATA_CENTER.PERSON.PER_ID = " . $perId;
		$query = $this->db->query($sql);
		$enrolledQuery = array();
		if($query->num_rows() > 0) { // we have some sort of enrollment record, see if they're actually in courses
			
			foreach($query->result() as $courseEntry) {

				if(is_numeric($courseEntry->CLASS_SECTION)) {
					$sql = "SELECT * from Course,Designator where Course.COURSE_ID = Designator.COURSE_ID and Designator.DEPARTMENT =\"" . $courseEntry->SUBJECT . "\" AND Designator.NUMBER = \"" . $courseEntry->CATALOG_NBR . "\" AND (Designator.SECTION = " . $courseEntry->CLASS_SECTION . " OR Designator.SECTION=\"1\") AND Course.SEMESTER =\"" . $courseEntry->term . "\" AND Course.YEAR = \"" . $courseEntry->year . "\"";
				}
				else {
					$sql = "SELECT * from Course,Designator where Course.COURSE_ID = Designator.COURSE_ID and Designator.DEPARTMENT =\"" . $courseEntry->SUBJECT . "\" AND Designator.NUMBER = \"" . $courseEntry->CATALOG_NBR . "\" AND (Designator.SECTION = \"" . ltrim($courseEntry->CLASS_SECTION, 0) . "\" OR Designator.SECTION=1) AND Course.SEMESTER =\"" . $courseEntry->term . "\" AND Course.YEAR = \"" . $courseEntry->year . "\"";	
				}

				$courseResult = $this->db->query($sql);
				if($courseResult->num_rows() > 0) {
					$enrolledQuery = array_merge($courseResult->result(), $enrolledQuery);
				}
			}
		}
		
			
		if(isset($manualQuery) && count($enrolledQuery)>0) 
		{
			$myObject = array_merge($manualQuery, $enrolledQuery);
			return $myObject;
		}elseif(isset($manualQuery)) 
		{
			return $manualQuery;
		}elseif(isset($enrolledQuery)) 
		{
			return $enrolledQuery;
		}else 
		{
			return false;
		}
		

	}
	
	
	function editCourse($courseId) {
		$course_data['PUBLIC'] = ($this->input->post('public')=="on")?1:0;
		$course_data['DISABLE_LANGUAGE_PARSING'] = ($this->input->post('disable_language_parsing')=="on")?1:0;
		$course_data['SHOW_RELATED_WORDS'] = ($this->input->post('show_related_words')=="on")?1:0;
		$this->db->where("COURSE_ID", $courseId);
		$this->db->set($course_data);
		$this->db->update("Course");

		return true;
		
		
	}
	
	
	function isEnrolledInCourse($perId, $courseId) {
		$courseArray = $this->getEnrolledCourses($perId);

		if($courseArray != false) {
		if(count($courseArray)>0) {
			foreach($courseArray as $courseEntry) {
				if($courseEntry->COURSE_ID == $courseId) {
					return true;
				}
			}
		}
		}
		return false;
		
	}
	
	
	function getCourseData($course_id)
	{
		$this->db->select('*');
		$this->db->from('Course');
		$this->db->join('Designator', 'Course.COURSE_ID = Designator.COURSE_ID');
		$this->db->where('Course.COURSE_ID', $course_id);
		
		$query = $this->db->get();
		$result = $query->result();
		return $result[0];
	}
	
	
	function loadQuestionsForCourse($course_id)
	{
		$this->db->select('*');
		$this->db->from('Question');
		$this->db->where('COURSE_ID', $course_id);
		$this->db->order_by('SORT_ORDER', 'CREATED_AT', 'desc');
		
		$query = $this->db->get();
		
		$return_val = false;
		$this->question_object_array = array();
		foreach($query->result() as $courseEntry) 
		{
			$this->load->model('question_model');
			$this->question_object_array[] = new Question_model($courseEntry->QUESTION_ID);
			$return_val = true;
		}
		
		return $return_val;
	}
	
	function addUserToCourse($per_id, $course_id)
	{
		$data['PER_ID'] = $per_id;
		$data['COURSE_ID'] = $course_id;

		$this->db->insert('Course_User', $data);
	}
	
	function addInstructorToCourse($per_id, $course_id)
	{
		$data['INSTRUCTOR_ID'] = $per_id;
		$data['COURSE_ID'] = $course_id;

		$this->db->insert('Instructors', $data);
	}

	
	function getUsersForCourse($course_id)
	{
		$this->db->where("COURSE_ID", $course_id);
		$query = $this->db->get('Course_User');
		
		return $query->result();
	}

	function getInstructorsForCourse($course_id)
	{
		$this->db->where("COURSE_ID", $course_id);
		$query = $this->db->get('Instructors');
		
		return $query->result();
	}

	
	function checkForUserInCourse($per_id, $course_id)
	{
		$this->db->where("COURSE_ID", $course_id);
		$this->db->where("PER_ID", $per_id);
		$query = $this->db->get('Course_User');
		
		if($query->num_rows() > 0)
		{
			return true;
		}else{
			return false;
		}
	}
	
	function removeUserFromCourse($per_id, $course_id)
	{
		$this->db->where('COURSE_ID', $course_id);
		$this->db->where('PER_ID', $per_id);
		$this->db->delete('Course_User');

	}
	
	function removeInstructorFromCourse($per_id, $course_id)
	{
		$this->db->where('COURSE_ID', $course_id);
		$this->db->where('INSTRUCTOR_ID', $per_id);
		$this->db->limit(1);
		$this->db->delete('Instructors');

	}
	
	function deleteCourse($course_id)
	{
		//TODO: DOES NOT DELETE RESULTS/ANSWERS
		$this->db->where('COURSE_ID', $course_id);
		$this->db->delete('Question');
		
		$this->db->where('COURSE_ID', $course_id);
		$this->db->delete('Designator');
		
		$this->db->where('COURSE_ID', $course_id);
		$this->db->delete('Course_User');
		
		$this->db->where('COURSE_ID', $course_id);
		$this->db->delete('Course');
		$this->db->where('COURSE_ID', $course_id);
		$this->db->delete('Instructors');
		
	}


	function isCoursePublic($courseId) {
		
		$this->db->select("PUBLIC");
		$this->db->where("COURSE_ID", $courseId);
		$result = $this->db->get("Course")->row();
		if($result->PUBLIC == 1) {
			return true;
		}		
		else {
			return false;
		}
	}
	
	
	function disableLanagueProcessingForCourse($courseId){ 
		
		$this->db->select("DISABLE_LANGUAGE_PARSING");
		$this->db->where("COURSE_ID", $courseId);
		$result = $this->db->get("Course")->row();
		if($result->DISABLE_LANGUAGE_PARSING == 1) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function showRelatedWordsForCourse($courseId){ 
		
		$this->db->select("SHOW_RELATED_WORDS");
		$this->db->where("COURSE_ID", $courseId);
		$result = $this->db->get("Course")->row();
		if($result->SHOW_RELATED_WORDS == 1) {
			return true;
		}
		else {
			return false;
		}
		
		
	}
	

}

?>
