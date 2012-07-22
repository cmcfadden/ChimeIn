<? foreach($this->course_model->question_object_array as $question) {?>
<question id="<?=$question->question_id?>">
	<questionText><?=$question->question_text?></questionText>
	<questionIsOpen><?=$question->question_is_open?"true":"false"?></questionIsOpen>
	<questionType><?=$question->question_type?></questionType>
</question>
<? } ?>