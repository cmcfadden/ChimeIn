<div id='question_id_<?= $question_object->question_id ?>'>
<fieldset>
	<legend><?= $question_object->question_text?> : <?= $question_object->created_at?></legend>
	<form id="question_form_<?=$question_object->question_id?>" method="post">
		<textarea name='answer'></textarea><br \>
		<input type='hidden' name='question_id' value='<?=$question_object->question_id?>' />
		<input type='hidden' name='question_type' value='<?=$question_object->question_type?>' />
		<input id="form_submit" type="submit" value="Submit Answer">
	</form>
</fieldset>
</div>