<?php

class Result_model extends CI_Model {
	
	public $answerArray=array();
	public $totalCloudCount=60; // total number of words in the cloud
	private $resultCacheArray = array();
	private $questionId;
	
	public $excludeList = array("a", "about", "above", "accordingly", "after",
	  "again", "against", "ah", "all", "also", "although", "always", "am", "among", "amongst", "an",
	  "and", "any", "anymore", "anyone", "are", "as", "at", "away", "be", "been",
	  "begin", "beginning", "beginnings", "begins", "begone", "begun", "being",
	  "below", "between", "but", "by", "ca", "can", "cannot", "come", "could",
	  "did", "do", "doing", "during", "each", "either", "else", "end", "et",
	  "etc", "even", "ever", "far", "ff", "following", "for", "from", "further", "furthermore",
	  "get", "go", "goes", "going", "got", "had", "has", "have", "he", "her",
	  "hers", "herself", "him", "himself", "his", "how", "i", "if", "in", "into",
	  "is", "it", "its", "itself", "last", "lastly", "less", "many", "may", "me",
	  "might", "more", "must", "my", "myself", "near", "nearly", "never", "new",
	  "next", "no", "not", "now", "o", "of", "off", "often", "oh", "on", "only",
	  "or", "other", "otherwise", "our", "ourselves", "out", "over", "perhaps",
	  "put", "puts", "quite", "s", "said", "saw", "say", "see", "seen", "shall",
	  "she", "should", "since", "so", "some", "such", "t", "than", "that", "the",
	  "their", "them", "themselves", "then", "there", "therefore", "these", "they",
	  "this", "those", "though", "throughout", "thus", "to", "too",
	  "toward", "unless", "until", "up", "upon", "us", "ve", "very", "was", "we",
	  "were", "what", "whatever", "when", "where", "which", "while", "who",
	  "whom", "whomever", "whose", "why", "with", "within", "without", "would",
	  "yes", "you", "your", "yours", "yourself", "yourselves", " ", "-");
	
	
	function Result_model()
	{
		parent::__construct();
	}
	
	function createResult($perId)
	{
		
		$result_data = array();
		
		$result_data['QUESTION_ID'] = $this->input->post('question_id');
		$result_data['RESULT_CONTENT'] = $this->input->post('answer');
		$result_data['PER_ID'] = $perId;
		$result_data['CREATED_AT'] = date( 'Y-m-d H:i:s', time() );
		
		$this->db->insert('Result', $result_data);
		
		return "success";
	}
	
	
	
	function checkForResultToQuestionByUser($result, $question_id, $per_id)
	{
		if($per_id == 0) {
			return false;
		}
		else {
		$this->db->where('RESULT_CONTENT', $result);
		$this->db->where('QUESTION_ID', $question_id);
		$this->db->where('PER_ID', $per_id);
		$query = $this->db->get('Result');
		
		if($query->num_rows() > 0)
		{
			return true;
		}else{
			return false;
		}
		}
	}
	
	
	function createResultWithParams($perId, $questionId, $answerBody) {
		
		$response = "";
		
		$result_data = array();
		
		$result_data['QUESTION_ID'] = $questionId;
		$result_data['RESULT_CONTENT'] = $answerBody;
		$result_data['PER_ID'] = $perId;
		$result_data['CREATED_AT'] = date( 'Y-m-d H:i:s', time() );
		
		$this->db->insert('Result', $result_data);
		
		return "success";
		
		
	}
	
	function getResultsForQuestion($question_id, $question_type)
	{
		$this->db->select('*');
		$this->db->from('Result');
		
		if($question_type != "FR" && $question_type != "QR")
		{
			$this->db->join('Answer', 'Result.RESULT_CONTENT = Answer.ANSWER_ID');
		}
		
		$this->db->where('Result.QUESTION_ID', $question_id);
		$this->db->order_by('Result.RESULT_ID', 'desc');
		$query = $this->db->get();
		
		return $query->result();
	}
	
	function getResultsCountForQuestion($question_id)
	{
		$this->db->select('*');
		$this->db->from('Result');
		$this->db->where('QUESTION_ID', $question_id);
		$query = $this->db->get();
		
		return $query->num_rows();
	}
	
	function hasResultForUser($per_id)
	{
		$this->db->where('QUESTION_ID', $this->input->post('question_id'));
		$this->db->where('PER_ID', $per_id);
		$query = $this->db->get('Result');
		
		if($query->num_rows() > 0)
		{
			return true;
		}else{
			return false;
		}
	}
	
	function hasResultForUserWithParams($per_id, $questionId)
	{
		$this->db->where('QUESTION_ID', $questionId);
		$this->db->where('PER_ID', $per_id);
		$query = $this->db->get('Result');
		
		if($query->num_rows() > 0)
		{
			return true;
		}else{
			return false;
		}
	}
	
	function updateResult($per_id)
	{
		
		$result_data = array();
		
		$result_data['RESULT_CONTENT'] = $this->input->post('answer');
		
		$this->db->where('PER_ID', $per_id);
		$this->db->where('QUESTION_ID', $this->input->post('question_id'));
		$this->db->update('Result', $result_data);
		
		return "success";
	}
	
	function updateResultWithParams($per_id, $questionId, $answerBody)
	{
		$response = "";
		
		$result_data = array();
		
		$result_data['RESULT_CONTENT'] = $answerBody;
		
		$this->db->where('PER_ID', $per_id);
		$this->db->where('QUESTION_ID', $questionId);
		$this->db->update('Result', $result_data);
		
		return $response;
	}
	
	
	function buildIndexForAnswers($question_id) {

 		$ci=&get_instance();		
		$ci->load->model('question_model', 'qm');
		$ci->qm->loadAnswersForQuestion($question_id);

		$i=65;

		foreach($ci->qm->answer_array as $answerEntry) {

			$answerArray[$answerEntry->ANSWER_ID] = chr($i);
			$i++;			
		}
		if(isset($answerArray) && is_array($answerArray)) {
			$this->answerArray = $answerArray;			
		}

	}
	

	function getBinnedResultsForQuestion($question_id, $question_type, $filterNumbers=false) {
		$this->questionId = $question_id;
		$questionResults =$this->getResultsForQuestion($question_id, $question_type);

		if(count($questionResults) ==0) {
			return array();
		}
		
		if($question_type == "FR" || $question_type == "QR") {
			$wordCloudArray = array();

			foreach($questionResults as $result) {
				$resultCacheArray[] = $result->RESULT_CONTENT;
				$wordArray = str_word_count($result->RESULT_CONTENT,1, "0123456789");
				foreach($wordArray as $word) {

					if(!in_array(strtolower($word), $this->excludeList) && (!is_numeric($word) || !$filterNumbers)) {

//					if(!in_array(strtolower($word), $this->excludeList)) {
						if(array_key_exists(strtolower($word), $wordCloudArray)) {
							$wordCloudArray[htmlspecialchars(strtolower($word), ENT_QUOTES)]++;
						}
						else {
							$wordCloudArray[htmlspecialchars(strtolower($word), ENT_QUOTES)] = 1;
						}	
					}
				}
			}

			arsort($wordCloudArray);
			
			$this->load->helper("array_helper");
			
			$wordCloudArray= highPassFilterArray($wordCloudArray,$this->totalCloudCount);
			
			// word cloud is now max of $totalCloudCount and is sorted most to least
			
			$this->resultCacheArray = $resultCacheArray;

			return $wordCloudArray;
			
		}
		else {
			$this->buildIndexForAnswers($question_id);

			$binnedArray = array();

			foreach($this->answerArray as $answerEntry) {
				$binnedArray[$answerEntry] = "0.0001";
			}

			foreach($questionResults as $questionEntry) {

				if(isset($this->answerArray[$questionEntry->ANSWER_ID]) && isset($binnedArray[$this->answerArray[$questionEntry->ANSWER_ID]]) && $binnedArray[$this->answerArray[$questionEntry->ANSWER_ID]] >= 1) {
					$binnedArray[$this->answerArray[$questionEntry->ANSWER_ID]]++;
				}
				else {
					if(isset($this->answerArray[$questionEntry->ANSWER_ID])) {
						$binnedArray[$this->answerArray[$questionEntry->ANSWER_ID]] = 1;						
					}

				}

			}
			
			
		}

		return $binnedArray;
		
	}

	function reverseMapArray($lemmaArray) {
		
		$reversedLemmaArray = array();
		
		foreach($lemmaArray as $key=>$entry) {
			foreach($entry["sourceWords"] as $word) {
				$reversedLemmaArray[$word] = $key;		
			}
		}
		
		return $reversedLemmaArray;
		
	}


	function findRelatedTerms($lemmaArray) {
		
		$reversedLemmaArray = $this->reverseMapArray($lemmaArray);

		foreach($this->resultCacheArray as $resultPhrase) {
			
			$wordArray = str_word_count($resultPhrase,1, "0123456789");
			for($i=0; $i<count($wordArray); $i++) {
				$toLower = strtolower($wordArray[$i]);
				if(array_key_exists($toLower, $reversedLemmaArray)) {
					$firstOrder = array();
					$secondOrder = array();

					if($i>=1 && array_key_exists(strtolower($wordArray[$i-1]), $reversedLemmaArray)) {
						$firstOrder[] = $reversedLemmaArray[strtolower($wordArray[$i-1])];
					}
					if(count($wordArray) > ($i+1) && array_key_exists(strtolower($wordArray[$i+1]), $reversedLemmaArray)) {
						$firstOrder[] = $reversedLemmaArray[strtolower($wordArray[$i+1])];
					}
					
					if($i>=2 && array_key_exists(strtolower($wordArray[$i-2]), $reversedLemmaArray)) {
						$secondOrder[] = $reversedLemmaArray[strtolower($wordArray[$i-2])];
					}
					if(count($wordArray) > ($i+2) && array_key_exists(strtolower($wordArray[$i+2]), $reversedLemmaArray)) {
						$secondOrder[] = $reversedLemmaArray[strtolower($wordArray[$i+2])];
					}
					if(count($firstOrder)>0) {
						if(array_key_exists("firstOrder", $lemmaArray[$reversedLemmaArray[$toLower]])) {
							$lemmaArray[$reversedLemmaArray[$toLower]]["firstOrder"]= array_unique(array_merge($lemmaArray[$reversedLemmaArray[$toLower]]["firstOrder"], $firstOrder));

						}
						else {
							$lemmaArray[$reversedLemmaArray[$toLower]]["firstOrder"] = $firstOrder;
						}
						

					}

					if(count($secondOrder)>0) {
						if(array_key_exists("secondOrder",$lemmaArray[$reversedLemmaArray[$toLower]])) {
							$lemmaArray[$reversedLemmaArray[$toLower]]["secondOrder"]= array_unique(array_merge($lemmaArray[$reversedLemmaArray[$toLower]]["secondOrder"], $secondOrder));
						}
						else {
							$lemmaArray[$reversedLemmaArray[$toLower]]["secondOrder"] = $secondOrder;
						}
						
					}

					
				}
			}
		}
		
		return $lemmaArray;
		
	}


	function addExcludeArray($excludeArray) {
		
		$this->excludeList = array_merge($this->excludeList, $excludeArray);
		
	}


/**
 * Groups code
 */

  // I feel like I must have written this function before?
	


	function addGroupsToArray($sortedArray) {
		$this->db->where("QUESTION_ID", $this->questionId);
		$result = $this->db->get("Result_group");
		
		foreach($result->result() as $resultGroup) {
			
			$this->db->where("GROUP_ID", $resultGroup->GROUP_ID);
			$resultItems = $this->db->get("Result_group_items");
			
			$cleanedGroupName = $resultGroup->CLEANED_GROUP_NAME;
			$groupWords = array();
			$groupCount = 0;
			foreach($resultItems->result() as $resultItem) {
				
				if(array_key_exists($resultItem->GROUP_ITEM, $sortedArray)) {
					
					$groupWords = array_merge($groupWords, $sortedArray[$resultItem->GROUP_ITEM]["sourceWords"]);
					$groupCount += $sortedArray[$resultItem->GROUP_ITEM]["count"];
					unset($sortedArray[$resultItem->GROUP_ITEM]);
				}
			}
			
			if($groupCount>0) {
				$sortedArray["g_".$cleanedGroupName]["count"] = $groupCount;
				$sortedArray["g_".$cleanedGroupName]["type"] = "group";
				$sortedArray["g_".$cleanedGroupName]["sourceWords"] = $groupWords;
				$sortedArray["g_".$cleanedGroupName]["groupTitle"] = $resultGroup->GROUP_NAME;		
			}
			
			
		}


		// need to reorder array based on group count
		
		function sortWordcloudArray($a, $b) {

			if($a["count"] > $b["count"]) {
				return -1;
			}
			elseif($a["count"] < $b["count"]) {
				return 1;
			}
			else {
				return 0;
			}

		}
		
		// really wish we were on php 5.3, this would be an anon func
		uasort($sortedArray, "sortWordcloudArray");

		


		return $sortedArray;

	}


	function createGroup($groupName, $questionId) {
	
		$cleanedGroupName = $this->cleanGroupName($groupName);	
		
		$this->db->set("QUESTION_ID", $questionId);
		$this->db->set("GROUP_NAME", $groupName);
		$this->db->set("CLEANED_GROUP_NAME", $cleanedGroupName);
		$this->db->insert("Result_group");
		
		return $cleanedGroupName;
	}
	
	function cleanGroupName($groupName) {
		$patterns[0] = '/\s+/';            //any string of 1 or more ' '
		$patterns[1] = '/[^a-zA-Z0-9_.]/'; //any non-alphanum, non '_' char
		$replacements[0] = '_'; //whitepace gets underbars
		$replacements[1] = '_'; //special chars get dashes
		ksort($patterns);     //make sure the keys are ordered appropriately
		ksort($replacements); //so that the preg_replace doesn't get subs wrong
		return preg_replace($patterns, $replacements, $groupName);
		
	}
	
	function destroyGroup($groupName, $questionId) {


		$cleanedGroupName = $this->cleanGroupName($groupName);


		$this->db->where("CLEANED_GROUP_NAME", $cleanedGroupName);
		$this->db->where("QUESTION_ID", $questionId);
		$groupId = $this->db->get("Result_group")->row()->GROUP_ID;
	
		$this->db->where("GROUP_ID", $groupId);
		$this->db->delete("Result_group");
		$this->db->where("GROUP_ID", $groupId);
		$this->db->delete("Result_group_items");

		
		return true;
	}

	function removeWordFromGroup($groupName, $wordName, $questionId) {
		
		$cleanedGroupName = $this->cleanGroupName($groupName);
		

		$stemmedLemmaWord = $this->languageprocessing->stemArray($this->languageprocessing->lemmatizeArray(array($wordName=>$wordName)));

		$this->db->where("CLEANED_GROUP_NAME", $cleanedGroupName);
		$this->db->where("QUESTION_ID", $questionId);
		$groupId = $this->db->get("Result_group")->row()->GROUP_ID;

		$this->db->where("GROUP_ID", $groupId);
		$this->db->where("GROUP_ITEM", $stemmedLemmaWord[0]);
		$this->db->delete("Result_group_items");
			
	}
	
	
	function addItemToGroup($groupName, $itemToAdd, $questionId) {
		
		$cleanedGroupName = $this->cleanGroupName($groupName);	

		$this->db->where("CLEANED_GROUP_NAME", $cleanedGroupName);
		$this->db->where("QUESTION_ID", $questionId);
		$groupId = $this->db->get("Result_group")->row()->GROUP_ID;
		
		if(!isset($groupId) || !is_numeric($groupId)) {
			return false;
		}
		
		$this->db->set("GROUP_ID", $groupId);
		$this->db->set("GROUP_ITEM", $itemToAdd);
		$this->db->insert("Result_group_items");
		return true;
		
		
	}

}

?>
