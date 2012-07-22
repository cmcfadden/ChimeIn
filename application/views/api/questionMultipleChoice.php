<question id="<?=$this->question_model->question_id?>">
	<questionText><?=$this->question_model->question_text?></questionText>
	<questionIsOpen><?=$this->question_model->question_is_open?"true":"false"?></questionIsOpen>
	<questionType><?=$this->question_model->question_type?></questionType>
	<answers count="<?=$this->question_model->answer_count?>">
<?foreach($this->question_model->answer_array as $answer) { ?>
		<answer id="<?=$answer->ANSWER_ID?>" letter="<?=$questionIndex[$answer->ANSWER_ID]?>"><?=$answer->ANSWER_TEXT?></answer>
<? } ?>
	</answers>
</question>
