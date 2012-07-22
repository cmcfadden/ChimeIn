<question id="<?=$this->question_model->question_id?>">
	<questionText><?=$this->question_model->question_text?></questionText>
	<questionIsOpen><?=$this->question_model->question_is_open?"true":"false"?></questionIsOpen>
	<questionType><?=$this->question_model->question_type?></questionType>
	<results count="<?=$resultCount?>">
<?foreach($resultData as $result) { ?>
		<result id="<?=$result->RESULT_ID?>"><?=$result->RESULT_CONTENT?></result>
<? } ?>

</question>
