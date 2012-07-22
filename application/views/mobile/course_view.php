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
		<script type="text/javascript" src="<?=base_url();?>assets/js/json2.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.mobile-1.1.0.min.js">
        </script>
		<script src="<?=base_url();?>assets/js/jquery-ui-1.8.20.custom.min.js">
        </script>
		<script src="<?=site_url("assets/js/questionFormSubmission.js")?>"></script>
	</head>
    <body>
		<div data-role="page" id="courseView<?=$course_data->COURSE_ID?>" class="courseQuestionList" data-theme="c">
				<div data-theme="c" data-role="header">
					<h3>
						Viewing <?= $course_data->DEPARTMENT ?><?= $course_data->NUMBER?>
					</h3>
					<a data-role="button" data-inline="true" data-direction="reverse" data-rel="back" data-transition="slidedown" data-theme="c" href="<?=site_url("/course/")?>" data-icon="arrow-l" data-iconpos="left">
						Back
					</a>
				</div>
			<div data-theme="c" data-role="content">
				<ul data-role="listview" data-divider-theme="c" data-inset="true" id="instant_question_list">
	<? 	$questionArray = array();
	if(count($question_object_array) > 0){ 
			$no_questions = true;
			foreach($question_object_array as $question) 
			{ 
				if($question->question_is_open == true)
				{
					if(strpos($question->question_type, "Q") ===0)
					{?>
					<li class="questionLink" id="question_id_li_<?=$question->question_id?>" data-icon="arrow-r"><a href="<?=site_url("course/viewQuestion/" . $course_data->COURSE_ID . "/".$question->question_id)?>" data-transition="slide" data-prefetch><?=$question->question_text?></a></li>
					
					<?
						$questionArray[] = $question;
						$no_questions = false;
					}
				}
		 	}
		 	if($no_questions){ ?>
				<li id="instant_no_questions_text"> This course does not have any open instant questions at this time </li>
			<? } else { ?>
				<li id="instant_no_questions_text" class="ui-screen-hidden">This course does not have instant any questions at this time </li>
			<? }		
 } ?>
	</ul>
	
	<ul id="normal_question_list" data-divider-theme="c" data-inset="true" data-role="listview">
		<? if(count($question_object_array) > 0){ 
				$no_questions = true;

				foreach($question_object_array as $question) 
				{ 
					if($question->question_is_open == true)
					{
						if(strpos($question->question_type, "Q") === false)
						{?>
						<li class="questionLink" id="question_id_li_<?=$question->question_id?>" data-icon="arrow-r"><a href="<?=site_url("course/viewQuestion/" . $course_data->COURSE_ID . "/".$question->question_id)?>" data-transition="slide" data-prefetch><?=$question->question_text?></a></li>
						
						<?
							$questionArray[] = $question;
							$no_questions = false;
						}
					}
			 	}
			 	if($no_questions){ ?>
					<li id="no_questions_text"> This course does not have any questions at this time </li>
				<? } else { ?>
					<li id="no_questions_text" class="ui-screen-hidden">This course does not have any questions at this time </li>
				<? }		
	 } ?>	
	</ul>


					</div>
						<script>

						var globalTimerForUpdate;
						var updateTimer;

						$("#courseView<?=$course_data->COURSE_ID?>").live('pageshow', function() {
							clearInterval(updateTimer);
							updateTimer = null;
							updateTimer = setInterval("updateCourseView();", 5000);

						});

						$("#courseView<?=$course_data->COURSE_ID?>").live('pagehide', function() {
							clearInterval(updateTimer);
							updateTimer=null;
							clearInterval(globalTimerForUpdate);
							globalTimerForUpdate=null;
						})

						function numOrdA(a, b){ return (a-b); }

						function getCurrentQuestionArray() {

							var question_array = new Array();
							$.each($(".questionLink"), function(i,v){
								num_str = $(v).attr('id');
								num_str = num_str.replace("question_id_li_", "");
								num_str = parseInt(num_str);
								question_array[i] = num_str;
							});
							return question_array;
						}


						function updateCourseView()
						{
							var question_array = getCurrentQuestionArray();
							var sortedQuestionArray = question_array.sort(numOrdA);
							if(sortedQuestionArray.length == 0) {
								var openQuestionString ="none";
							}
							else {
								var openQuestionString = sortedQuestionArray.join("_");

							}

							globalTimerForUpdate = setTimeout("updateCourseViewCacheMiss()", 2000);
							var randNum =Math.random(1,10000);

							course_id = <?=$course_data->COURSE_ID?>;

							var cacheUrl = "<?=str_replace("https://","http://",site_url('cache/questionCache/'));?>" + "/" +<?=$course_data->COURSE_ID?> + "/" + openQuestionString + "?" + randNum;

							$.ajax({
								type:'GET',
								dataType:'jsonp',
								url: cacheUrl,
								error: function(request,status,error) {


								},
								success: function(response) {

								}

							});
						}

						function updateCourseViewCacheMiss() {

							var question_array = getCurrentQuestionArray();

							question_array = JSON.stringify(question_array);

							// TODO IS THIS WHERE THIS TIMER SHOULD BE?
							clearInterval(updateTimer);
							updateTimer=null;
							var currentPage = location.hash;

							course_id = <?=$course_data->COURSE_ID?>;

							$.ajax({
											type: 'POST',
											url: "<?= site_url('course/updateViewAjax')?>",
											data: {json_data : question_array, course_id : course_id},
											error: function(request, status,error) {
												//TODO
													var errorAlertDialog = "<div data-role=\"page\" id=\"errorRefreshing\" data-theme=\"c\"> \
														<div data-theme=\"c\" data-role=\"content\"> \
															<h2>There was an error refreshing this page</h2> \
															<p>Please reload and try again.</p> \
															<a href=\"#\" data-role=\"button\" data-rel=\"back\">OK</a> \
														</div> \
													</div>";
													$("body").append(errorAlertDialog);
													$.mobile.changePage( $('#errorRefreshing'), { role: 'dialog', transition: 'pop'} );
											},
											success: function (response) 
															{
																	var response_array = $.parseJSON(response);

																	if(typeof(response_array)!='object' || response_array['status'] != "success")
																	{
																	//TODO
																	alert("An error occurred updating the page.  Please manually reload and try again.");
																}
																else {
																		if(response_array['instant_questions_to_add'] != "")
																		{
																			$("#instant_question_list").prepend(response_array['instant_links_to_add_string']);
																		}
																		if(response_array['normal_questions_to_add'] != "")
																		{
																			$("#normal_question_list").prepend(response_array['normal_links_to_add_string']);
																		}
																		$('#instant_question_list').listview('refresh');	
																		$('#normal_question_list').listview('refresh');	
																		for(add_question in response_array['questions_to_add'])
																		{

																			element_to_add = "#question_id_li_"+response_array['questions_to_add'][add_question];
																			$(element_to_add).slideDown('slow', function() {
																				$('#instant_question_list').listview('refresh');	
																				$('#normal_question_list').listview('refresh');															
																				$(element_to_add).effect("highlight", {}, 300);
																			});


																		}

																		var instantQuestions = $("#instant_question_list li").size();
																		var normalQuestions = $("#normal_question_list li").size();

																		for(delete_question in response_array['questions_to_delete'])
																		{
																			element_to_remove = "#question_id_li_"+response_array['questions_to_delete'][delete_question];
																			if($(element_to_remove).parent().attr("id") == "instant_question_list") {
																				instantQuestions--;
																			}
																			else if($(element_to_remove).parent().attr("id") == "normal_question_list") {
																				normalQuestions--;
																			}
																			


																			$(element_to_remove).slideUp('slow', function() {
																				$(this).remove();
																				$("#view"+response_array['questions_to_delete'][delete_question]).remove();
																				$('#instant_question_list').listview('refresh');	
																				$('#normal_question_list').listview('refresh');															
																			});

																		}


																		var instantQuestionNoneText = "#instant_no_questions_text";
																		var normalQuestionNoneText = "#no_questions_text";
																		if(instantQuestions == 1 && $(instantQuestionNoneText).is(":hidden")) {
																			$(instantQuestionNoneText).slideDown('slow', function() {
																				$('#instant_question_list').listview('refresh');	
																				$(instantQuestionNoneText).effect("highlight", {}, 300);
																			});
																		}
																		else if(instantQuestions > 1 && $(instantQuestionNoneText).is(":visible")) {
																			$(instantQuestionNoneText).slideUp('slow', function() {
																				$(instantQuestionNoneText).addClass("ui-screen-hidden");
																				$('#instant_question_list').listview('refresh');													
																			});
																		}
																		
																		if(normalQuestions == 1 && $(normalQuestionNoneText).is(":hidden")) {
																			$(normalQuestionNoneText).slideDown('slow', function() {
																				$('#normal_question_list').listview('refresh');	
																				$(normalQuestionNoneText).effect("highlight", {}, 300);
																			});
																		}
																		else if(normalQuestions > 1 && $(normalQuestionNoneText).is(":visible")) {
																			$(normalQuestionNoneText).slideUp('slow', function() {
																				$(normalQuestionNoneText).addClass("ui-screen-hidden");
																				$('#normal_question_list').listview('refresh');													
																			});
																		}																				 	
																																				 													
																		clearInterval(updateTimer);
																		updateTimer = null;
																		updateTimer = setInterval("updateCourseView();", 5000);

																}

															},
											async: true
										});
						}



						</script>
				</div>
				
		
	</body>
	</html>