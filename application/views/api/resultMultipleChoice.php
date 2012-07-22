<question id="<?=$this->question_model->question_id?>">
	<questionText><?=$this->question_model->question_text?></questionText>
	<questionIsOpen><?=$this->question_model->question_is_open?"true":"false"?></questionIsOpen>
	<questionType><?=$this->question_model->question_type?></questionType>
	<results count="<?=$resultCount?>">
<?foreach($this->question_model->answer_array as $answer) { ?>
		<result id="<?=$answer->ANSWER_ID?>" letter="<?=$questionIndex[$answer->ANSWER_ID]?>" count="<?=round($resultsBinned[$questionIndex[$answer->ANSWER_ID]])?>"><?=$answer->ANSWER_TEXT?></result>
<? } ?>

</question>
