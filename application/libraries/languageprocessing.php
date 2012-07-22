<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class LanguageProcessing {

/*
	function lemmatizeArray($sourceArray) {
		$wordArray = array_keys($sourceArray);
		$singlePhrase = join($wordArray, " ");
//		exec("echo \"" . $singlePhrase. "\" | binary/morpha -ucf binary/verbstem.list", $outputArray);
//		$lemmatizedArray = explode(" ",join($outputArray, ""));
		$lemmatizedArray = explode(" ",$singlePhrase);
		return $lemmatizedArray;
		
	}
*/

	function lemmatizeArray($sourceArray) {
		// stub, doesn't do anything
			$wordArray = array_keys($sourceArray);
			$lemmatizedArray = $wordArray;
			return $lemmatizedArray;
		
	}


/*	function lemmatizeArray($sourceArray) {
		include "morpha.php";
		$morpha_tool = new morpha();
		error_reporting(0);
		return $morpha_tool->lemmatize_inputs($sourceArray);
		
		
	}
*/
	function stemArray($sourceArray) {
		require_once("class.stemmer.inc");
		
		$stemmer = new Stemmer;
		$stemmedArray = $stemmer->stem_list($sourceArray);
		return $stemmedArray;
	}
	
	
	function generateLemmaSourceMapping($lemmaArray, $sourceArray) {
		
		$wordArray = array_keys($sourceArray);
		
		$mergedArray = array();

		for($i=0; $i<count($lemmaArray); $i++) {

			if(!isset($mergedArray[$lemmaArray[$i]])) {
						$mergedArray[$lemmaArray[$i]]["count"] = 0;
						$mergedArray[$lemmaArray[$i]]["sourceWords"] = array();
			}

			$countForSourceWord = @$sourceArray[$wordArray[$i]];
			$mergedArray[$lemmaArray[$i]]["count"] += $countForSourceWord;
			$mergedArray[$lemmaArray[$i]]["sourceWords"][] = @strtolower($wordArray[$i]);
			$mergedArray[$lemmaArray[$i]]["type"] = "word";

		}

		return($mergedArray);
		
	}
	
	
	function generateMultidimensionalArray($sourceArray) {
		$mergedArray=array();

		foreach($sourceArray as $key=>$sourceCount) {
			
			$mergedArray[$key]["count"] = $sourceCount;
			$mergedArray[$key]["sourceWords"][] = strtolower($key);
			$mergedArray[$key]["type"] = "word";
			
		}
		return $mergedArray;
	}
	
	

}
