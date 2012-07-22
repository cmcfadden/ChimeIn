<div class="question_stub" id='question_id_<?= $question_object->question_id ?>'>
	<?= $question_object->question_text?><br />
	<form id="question_form_<?=$question_object->question_id?>" method="post">
		<div class='textarea_container'><textarea id='text_area_<?= $question_object->question_id ?>' name='answer'></textarea></div><br />
		<input type='button' value='Clear Field' onclick="j('#text_area_<?= $question_object->question_id ?>').val('');"/><br/>
		<input type='hidden' name='question_type' value='<?=$question_object->question_type?>' />
		<input type='hidden' name='question_id' value='<?=$question_object->question_id?>' />
		<input id="form_submit" type="submit" class="submit"  value="Submit Answer">
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
	<br/>
	
			<div class="anonymous">
	<?if($question_object->anonymous == true){?>
		Note: This question is anonymous.
	<?}else{?>
		Note: This question is not anonymous.
	<? } ?>
	</div>
</div>