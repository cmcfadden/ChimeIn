<div class="expand_stub" id='expand_id_<?= $question_model->question_id ?>'>

<?=$this->load->view("cellphone_submission")?>
			<div class='expand_stub_title'><?= $question_model->question_text?></div><br/>
		<br />
	<?if($question_model->anonymous == true){?>
		Note: This question is anonymous.
	<?}else{?>
		Note: This question is not anonymous.
	<? } ?>
</div>