<div class="question_stub" id='question_id_<?= $question_object->question_id ?>'>
		<?= $question_object->question_text?><br/>

		<form id="question_form_<?=$question_object->question_id?>" method="post">
		<? foreach($question_object->answer_array as $answer) { ?>
			<input type="radio" id="answer_id_<?= $answer->ANSWER_ID ?>" name="answer" value="<?= $answer->ANSWER_ID ?>" /> <label for="answer_id_<?= $answer->ANSWER_ID ?>"><?= $answer->ANSWER_TEXT ?></label><br />
		<? } ?>
			<input type='hidden' name='question_type' value='<?=$question_object->question_type?>' />
			<input type='hidden' name='question_id' value='<?=$question_object->question_id?>' />
			<input id="form_submit" class="submit" type="submit" value="Submit Answer">
		</form>
				<div class="submission_box">
		<div class='success_box' id='success_box_<?=$question_object->question_id?>' style="display:none;">
			Submission Successful
		</div>
				<div class="spinner_pad">
		<div class='ajax_spinner' id="ajax_spinner_<?=$question_object->question_id?>" style="display:none;">
			<?= img("../../assets/css/img/ajax-loader.gif")?>
		</div>
		
		</div>
		</div>
				<div class="anonymous">
		<?if($question_object->anonymous == true){?>
			Note: This question is anonymous.
		<?}else{?>
			Note: This question is not anonymous.
		<? } ?>
	</div>
		<? if($question_object->answered_by_user){ ?>
			<div class='if_answered' id='if_answered_<?= $question_object->question_id ?>'>Note: You have already answered this question.  Submitting will change your previous answer.</div>
		<? }else{ ?>
			<div class='if_answered' id='if_answered_<?= $question_object->question_id ?>' style="display: none;">Note: You have already answered this question.  Submitting will change your previous answer.</div>
		<? } ?>
</div>