<div class="expand_stub" id='expand_id_<?= $question_model->question_id ?>'>
	
	<?=$this->load->view("cellphone_submission")?>

			<div class='expand_stub_title'><?= $question_model->question_text?></div><br/>

		<? foreach($question_model->answer_array as $answer) { ?>
							<div class='expand_stub_answer'><?=$questionIndex[$answer->ANSWER_ID]?>) <?= $answer->ANSWER_TEXT ?></div>
							<br />			
		<? } ?>
		<?if($question_model->anonymous == true){?>
			Note: This question is anonymous.
		<?}else{?>
			Note: This question is not anonymous.
		<? } ?>
		</br>
</div>