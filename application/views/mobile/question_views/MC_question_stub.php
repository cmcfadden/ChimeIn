<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>ChimeIn
        </title>
        <link rel="stylesheet" href="<?=base_url();?>assets/css/jquery.mobile-1.1.0.min.css" />
	  <style>
            /* App custom styles */
        </style>
		<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-1.7.2.min.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.mobile-1.1.0.min.js">
        </script>
		<script src="<?=base_url();?>assets/js/jquery-ui-1.8.20.custom.min.js">
        </script>
		<script src="<?=site_url("assets/js/questionFormSubmission.js")?>"></script>
	</head>
    <body>
		<div data-role="page" id="view<?=$question_object->question_id?>" data-theme="c" data-dom-cache="true">
			
				<div data-theme="c" data-role="header">
					<h3></h3>
					<a data-role="button" data-inline="true" data-direction="reverse" data-rel="back" data-transition="slidedown" data-theme="c" href="<?=site_url("course/view/" . $course_data->COURSE_ID)?>" data-icon="arrow-l" data-iconpos="left">
						Back
					</a>
				</div>
				<div data-theme="c" data-role="content">
				<form id="question_form_<?=$question_object->question_id?>" method="post">
					

	                    <fieldset data-role="controlgroup" data-type="vertical">
	                        <legend>
	                            <b><?=$question_object->question_text?></b>
	                        </legend>
							<? foreach($question_object->answer_array as $answer) { ?>
	                        	<input name="answer" id="radio<?= $answer->ANSWER_ID ?>" value="<?= $answer->ANSWER_ID ?>" type="radio" />
	                        	<label for="radio<?= $answer->ANSWER_ID ?>">
	                            	<?= $answer->ANSWER_TEXT ?>
	                        	</label>
	                        <?}?>
	                    </fieldset>

					<input type='hidden' name='question_type' value='<?=$question_object->question_type?>' />
					<input type='hidden' name='question_id' value='<?=$question_object->question_id?>' />
					<div>
							<?if($question_object->anonymous == true){?>
								<b>Note: This question is anonymous.</b>
							<?}else{?>
								<b>Note: This question is not anonymous.</b>
							<? } ?>
					</div>
					<input type="submit" value="Submit" />
					<? if($question_object->answered_by_user){ ?>
						<div class='if_answered' id='if_answered_<?= $question_object->question_id ?>'>Note: You have already answered this question.  Submitting will change your previous answer.</div>
					<? }else{ ?>
						<div class='if_answered' id='if_answered_<?= $question_object->question_id ?>' style="display: none;">Note: You have already answered this question.  Submitting will change your previous answer.</div>
					<? } ?>
				</form>
		</div>
	</body>
</html>
