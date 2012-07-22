<script>
	var j = jQuery.noConflict();
	total_answers = Number(<?=$question_object->answer_count?>);

	function addAnswer()
	{
		
		if(total_answers == 10)
		{
			j.fancybox("You may not add more than 10 multiple choice options.", 
										{ 'transitionIn'	:	'fade',
										  'transitionOut'	:	'fade',
											'speedIn'		:	600, 
											'speedOut'		:	200, 
											'overlayShow'	:	true});
			
			return;
		}
		
		total_answers = total_answers + 1;
		
		j('#answer_field').append("<?=$mc_option?>");
		
		//TODO: limit number of answers??
	}
	
	function removeAnswer(object)
	{
		
		if(total_answers == 2)
		{
			j.fancybox("You may not have fewer than 2 multiple choice options.", 
										{ 'transitionIn'	:	'fade',
										  'transitionOut'	:	'fade',
											'speedIn'		:	600, 
											'speedOut'		:	200, 
											'overlayShow'	:	true});
			
			return;
		}
		
		total_answers = total_answers - 1;
		//TODO: do not allow removal if only 2
		j(object).parent().remove();
	}
	
	function removeExistingAnswer(object)
	{
		
		if(total_answers == 2)
		{
			j.fancybox("You may not have fewer than 2 multiple choice options.", 
										{ 'transitionIn'	:	'fade',
										  'transitionOut'	:	'fade',
											'speedIn'		:	600, 
											'speedOut'		:	200, 
											'overlayShow'	:	true});
			
			return;
		}
		
		total_answers = total_answers - 1;
		
		answer_id = j(object).attr('id');
		answer_id = answer_id.replace("remove_answer_id_", "");
		answer_id = parseInt(answer_id);
		field = "<input type='hidden' name='answers_to_delete[]' value='";
		field += answer_id + "'/>";
		
		j("#question_edit_form").append(field);
		j(object).parent().remove();
	}

</script>
	<h1><span class="headingwhite">Edit Question</span></h1>

<? if(isset($validation_response)){?>
	<ul><?=$validation_response?></ul>
<? } ?>

<div>
	<fieldset>
    <form id="question_edit_form" method="post" action="<?= site_url("question/submitEdit/". $question_object->question_id) ?>">
				<div class="question_stub">
				Question: 
                <div class="textarea_container">
				<textarea name="question_text"><?= $question_object->question_text ?></textarea>
                </div>
                </div>
				

		
		
			<? if($question_object->question_type == "MC"){?>
				<div class="question_stub">
				<div id="answer_field">
				<input type='button' class='add_answer' value='Add Answer' onClick='addAnswer();'/><br />
				<?foreach($question_object->answer_array as $answer){?>
					<span class='mc_option'>
					<input type='text' id="answer_id_<?=$answer->ANSWER_ID?>" value='<?=htmlspecialchars($answer->ANSWER_TEXT, ENT_QUOTES)?>' readonly='readonly'/>
						<input type='button' id="remove_answer_id_<?=$answer->ANSWER_ID?>"class='remove_answer' value='Remove' onClick='removeExistingAnswer(this);'/>
						<br />
					</span>
				<?}?>
				</div>
				</div>
			<?}?>
			
			
		
		
		
    <div class="question_stub">

		<? if($question_object->anonymous){?>
			<input type="checkbox" name="anonymous" id="anonymous" value="1" checked/> <label for="anonymous"> Anonymous</label><br />
		<? }else{?>
			<input type="checkbox" name="anonymous" id="anonymous" value="1" /> <label for="anonymous"> Anonymous</label><br />
		<? } ?>				
		
		<input type="hidden" name='anonymous_before' value="<?=$question_object->anonymous?>"/>
		<input type="hidden" name="course_id" value="<?=$question_object->course_id?>"/>
		<input type='hidden' name='question_type' value="<?= $question_object->question_type?>"/>
		<input type="hidden" name="question_is_open" value="0"/>
		<input type="submit" value="Update Question">
        </div>
	</form>
    </fieldset>
</div>