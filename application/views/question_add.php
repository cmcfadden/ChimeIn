	<h1><span class="headingwhite">Add a Question</span></h1>
<script>
	var j = jQuery.noConflict();
	total_answers = 2;
	
	j(document).ready(function(){
		
		j('#answer_option').change(function(){
			var option = j(this).val();
			switch(option)
			{
				case 'MC':
					//multiple choice
					j('#answer_field').html("<?=$mc_option_add?>" + "<?=$mc_option?>"+"<?=$mc_option?>");
					break;
				case 'TF':
					//true false
					j('#answer_field').html("<?=$t_option?>"+"<?=$f_option?>");
					break;
				case 'FR':
					//free response
					j('#answer_field').html("");
					break;
			}
		});
		
	});
	
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
		
		total_answers += 1;
		
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
		total_answers -= 1;
		j(object).parent().remove();
	}

</script>

<? if(isset($validation_response)){?>
	<ul><?=$validation_response?></ul>
<? } ?>

<div>
	<fieldset><form method="post" action="<?= site_url("question/addNew/". $course_id) ?>">
		<div class="question_stub">
        Question: 
        <div class="textarea_container">
        <textarea name="question_text"><? if(isset($questionText)) { echo $questionText; } else {?>Type your question here...<? } ?></textarea>
        </div>
        </div>
        
        <div class="question_stub">
		Question Type: 
		
		<? if(isset($question_type)){?>
			<select id="answer_option" name="question_type">
				<option value="None">Please Select...</option>
				<option value="MC" <? if($question_type == "MC"){echo "selected";}?>>Multiple Choice</option>
				<option value="TF" <? if($question_type == "TF"){echo "selected";}?>>True/False</option>
				<option value="FR" <? if($question_type == "FR"){echo "selected";}?>>Free Response</option>
			</select><br />
		<?}else{?>
			<select id="answer_option" name="question_type">
				<option value="None">Please Select...</option>
				<option value="MC">Multiple Choice</option>
				<option value="TF">True/False</option>
				<option value="FR">Free Response</option>
			</select><br />
		<? } ?>

		
		<? if(isset($answers)){?>
			<div id="answer_field">
				<? if($question_type == "MC"){?>
					<input type='button' class='add_answer' value='Add Answer' onClick='addAnswer();'></input><br />
				<?}?>
				<?foreach($answers as $answer){?>
					<span class='mc_option'><input type='text' name='answers[]' value='<?=$answer?>' <? if($question_type == "TF"){ echo "readonly='readonly'";} ?> ></input></span><br />
				<?}?>
			</div>
		<? }else{?>
			<div id="answer_field">

			</div>
		<? } ?>
</div>
		<div class="question_stub">

		
		<input type="checkbox" name="anonymous" id="anonymous" value="1"/> <label for="anonymous"> Anonymous</label><br />


		<input type="hidden" name="course_id" value="<?=$course_id?>"/>
		<input type="hidden" name="question_is_open" value="0"/>
		<input type="submit" value="Save Question">
        </div>
	</form>
    </fieldset>
</div>