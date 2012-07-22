<? header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=results" . $question_model->question_id . ".csv");
header("Pragma: no-cache");
header("Expires: 0"); ?>
Name,Answer,Time
<?foreach( $result_data as $response){?>
<?if(!$question_model->anonymous) { echo personIdToName($response->PER_ID); }?>,<?
		if($question_model->question_type == "QA" || $question_model->question_type == "FR") {
			echo '"' . str_replace("\n", "", str_replace('"', '""', $response->RESULT_CONTENT)) . '"';
		}
		else {
			echo $answerArray[$response->RESULT_CONTENT];
		}?>,<?=$response->CREATED_AT?>

<? } ?>