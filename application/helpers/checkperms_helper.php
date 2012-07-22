<?
// check perms
//0=private, 1=x500 only, 2=course enroll, 3=public


function haveCourseEditPerms($courseId, $perId) {

	$CI =& get_instance();
	
	$CI->db->where('OWNER_ID', $perId);
	$CI->db->where('COURSE_ID', $courseId);
	$query = $CI->db->get('COURSE_OWNERS');

	if($query->num_rows() > 0) {
		return true;
	}
	else {
		return false;
	}

}


function haveSessionEditPerms($sessionId, $perId) {

	$CI =& get_instance();
	
	$CI->db->where("SESSION_ID", $sessionId);
	$query = $CI->db->get("SESSION_LIST");
	
	$courseId = $query->row()->COURSE_ID;
	
	if($courseId) {
		return haveCourseEditPerms($courseId, $perId);
	}
	else {
		return false;
	}
	
	
}

function haveViewCoursePerms($courseId) {
	
	$CI =& get_instance();
	
	
	$query = $CI->db->query('SELECT PERMISSIONS FROM COURSE_LIST WHERE COURSE_ID = ' . $courseId);
	$coursePermissions = $query->row()->PERMISSIONS;




	switch ($coursePermissions) {
		case 0: //private

			require_once("system/application/config/x500load.php");

			$query = $CI->db->query('SELECT COURSE_ID FROM COURSE_OWNERS WHERE OWNER_ID = ' . $perId . ' AND COURSE_ID = ' . $courseId);
			if($query->num_rows() > 0) {
				return true;
			}
			else {
				return false;
			}
			break;
		case 1: // x500 only
			require_once("system/application/config/x500load.php");
			return true;
			break;
		case 2: // enrolled in course?
			
			// get course number
			$query = $CI->db->query('SELECT DEPARTMENT, COURSE_NUMBER FROM COURSE_LIST WHERE COURSE_ID = ' . $courseId);
			$subject = $query->row()->DEPARTMENT;
			$courseNumber = $query->row()->COURSE_NUMBER;
			require_once("system/application/config/x500load.php");
			
			// look up course enrollment
			
			$peopleDB=$CI->load->database('userInfo',TRUE);
					
			$sql = "SELECT * FROM STUDENT_ENROLLMENT, PERSON WHERE STUDENT_ENROLLMENT.SUBJECT = ? AND STUDENT_ENROLLMENT.CATALOG_NBR = ? AND STUDENT_ENROLLMENT.EMPLID = PERSON.EMPLID AND PERSON.PER_ID = ?";

			$result = $peopleDB->query($sql, array($subject, $courseNumber, $perId));
	
			
			if($result->num_rows() > 0) {
				$db1 = $CI->load->database('default',TRUE);
				return true;
			}
			else {
				$db1 = $CI->load->database('default',TRUE);
				if(haveCourseEditPerms($courseId, $perId)) {
					return true;
				}
				else {
					return false;
				}
	
			}
			break;
		case 3:
			return true;
			break;
	}

	
	
}


function haveViewSessionPerms($sessionId) {
	
	$CI =& get_instance();

	$CI->db->where("SESSION_ID", $sessionId);
	$query = $CI->db->get("SESSION_LIST");
	
	$courseId = $query->row()->COURSE_ID;
	
	if($courseId) {
		return haveViewCoursePerms($courseId);
	}
	else {
		return false;
	}
	
	
}



?>
