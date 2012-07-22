<h1><span class="headingwhite">Results Table</span></h1>
<table id="resultsTable" border=1>

<tr>
	<th>Student</th>
	<th>Answer</th>
	<th>Time</th>
</tr>

<?foreach( $result_data as $response){?>
	<tr>
		<td class="tableStudent"><?if(!$question_model->anonymous) { echo personIdToName($response->PER_ID); }?></td>
		<td class="tableAnswer"><?
		if($question_model->question_type == "QR" || $question_model->question_type == "FR") {
			echo $response->RESULT_CONTENT;
		}
		else {
			echo $answerArray[$response->RESULT_CONTENT];
		}
		?></td>
		<td class="tableTime"><?=$response->CREATED_AT?></td>
	</tr>
<? } ?>

</table>