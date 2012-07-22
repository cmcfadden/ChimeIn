<div id='question_id_<?= $question_object->question_id ?>'>
<h3 class="legendHeader"><?= $question_object->question_text?></h3>
	<fieldset>
		<ol type='A'>
		<? foreach($question_object->answer_array as $answer) { ?>
			<li> <?= $answer->ANSWER_TEXT ?></li><br />
		<? } ?>
		</ol>
	</fieldset>
</div>