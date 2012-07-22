<div id='question_id_<?= $question_object->question_id ?>'>
	<fieldset>
		<legend><?= $question_object->question_text?> : <?= $question_object->created_at?></legend>
		<ol type='A'>
		<? foreach($question_object->answer_array as $answer) { ?>
			<li> <?= $answer->ANSWER_TEXT ?></li><br />
		<? } ?>
		</ol>
	</fieldset>
</div>