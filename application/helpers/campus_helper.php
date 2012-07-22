<?

function getCampusNameById($campusId) {
	$CI =& get_instance();
	$CI->db->where("CAMPUS_ID", $campusId);
	$campusInfo = $CI->db->get("CAMPUS_LIST")->row();

	return $campusInfo->CAMPUS_NAME;
	
	
}