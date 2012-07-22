<?php


// return the current semester as a name
function getCurrentSemesterName() {
$month=date('n');

switch($month)
{	
	case 1:
	case 2:
	case 3:
	case 4:
	case 5:   return 'Spring';
			   break;
	case 6:
	case 7:
	case 8:    return 'Summer';
				break;
	case 9:
	case 10:
	case 11:
	case 12: 	return 'Fall';	
				break;		
}
}

// return the current semester as an int
function getCurrentSemesterInt() {
	$month=date('n');




	switch($month)
	{	
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:   return 0;
				   break;
		case 6:
		case 7:
		case 8:   
			return 1;
			break;
		case 9:
		case 10:
		case 11:
		case 12:    return 2;	
					break;		
	}
	
}

// Takes semester as an int, returns the english name
function getSemesterName($semesterInt) {
	switch ($semesterInt) {
		case 2:
			return "Fall";
			break;
		case 0:
			return "Spring";
			break;
		case 1:
			return "Summer";
			break;
		default:
			return "Unknown";
			break;
	}
}

function getSemesterInt($semesterName) {
	switch (strtoupper($semesterName)) {
		case 'FALL':
			return 2;
			break;
		case 'SPRING':
			return 0;
			break;
		case 'SUMMER':
			return 1;
			break;
		default:
			return "Unknown";
			break;
	}		
}

?>