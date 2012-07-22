<h3 class="legendHeader">Legend</h3>
	<h3 id="download"><?=anchor("result/view/". $question_model->question_id . "/false/true/false", "[View Results Table]")?> <?=anchor("result/view/". $question_model->question_id . "/false/false/true", "[Download Results CSV]")?></h3>

<fieldset>
	<table id='result_key_table' border='1'>
		<tr>
			<th>Answer</th>
			<th>Label</th>
			<th>Color Representation</th>
			<th>Result Count</th>
		</tr>
	<? $charInt = 65;?>
	<? foreach($question_model->answer_array as $answer) { ?>
		<tr>
			<td> <?= $answer->ANSWER_TEXT ?></td>
			<td> <?= chr($charInt) ?></td>
			<td bgcolor="<?= $colors[$charInt - 65]; ?>"></td>
			<td id="answer_<?=chr($charInt)?>_count"><?= ($binnedResults[chr($charInt)] == 0.0001)?0:$binnedResults[chr($charInt)]; ?></td>
		</tr>
		<? $charInt += 1; ?>
	<? } ?>
	</table>
</fieldset>