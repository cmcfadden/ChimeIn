<?php

function getMinMaxForArray($lemmaWordArray, $countKey="count") {
	$min=-1;
	$max=0;
	foreach($lemmaWordArray as $lemmaArray) {
		
		if($min==-1) {
			$min = $lemmaArray[$countKey];
		}

		if($lemmaArray[$countKey] < $min) {
			$min = $lemmaArray[$countKey];
		}

		if($lemmaArray[$countKey] > $max) {
			$max=  $lemmaArray[$countKey];
		}
		
	}
	
	return array("min"=>$min, "max"=>$max);
}

function multiDimArraySort($value1, $value2) {
	
	return ($value1["count"]>$value2["count"])?-1:+1;
	
}


function highPassFilterArray($sourceArray, $passCount, $subKey=null) {
	
	$highPassArray = array();
	if(count($sourceArray)> $passCount) {
		$total=0;
		foreach($sourceArray as $word=>$subEntry) {

			if($subKey) {
				$count=$subKey["count"];
			}
			else {
				$count=$subEntry;
			}

			if($total>$passCount) {

				if($previousItem != $count) {
					
					break;
				}
			}

			$highPassArray[$word] = $subEntry;
			$previousItem = $count;
			$total++;

		}


		$wordCloudArray = $highPassArray;
	}
	else {
		$wordCloudArray = $sourceArray;
	}
	return $wordCloudArray;
	
	
}

?>