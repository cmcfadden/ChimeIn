<script>
	var j = jQuery.noConflict();
	var updateTimer;
	
	j(document).ready(function() {
		
		updateTimer = setInterval("updateCourseView();", 5000);
		
 		j("form").live("submit", function(e) {
 			e.preventDefault();
			var form_id = j(this).attr('id');
			
			num_str = form_id.replace("question_form_", "");
			num_str = parseInt(num_str);
			
			form_id = "#"+form_id+" :input";
			var inputs = j(form_id).serializeArray();
			
			j("#ajax_spinner_"+num_str).fadeIn();
			
			j.ajax({
					type: 'POST',
					url: "<?= site_url('result/addNew')?>",
					data: inputs,
					error: function (request, status, error)
								 {
									  j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
																	{ 'transitionIn'	:	'fade',
																	  'transitionOut'	:	'fade',
																		'speedIn'		:	600, 
																		'speedOut'		:	200, 
																		'overlayShow'	:	true});
								 },
					success: function (response)
								   {
											switch(response)
											{
											case "success":
											  function wrapper(num) 
												{
													return function() 
																{
																	setTimeout( function(){ j("#success_box_"+num).hide('slide', {direction:'left'},1000); }, 1500);
																}
												}

												j("#success_box_"+num_str).show('slide', {direction: 'left'}, 1000, wrapper(num_str));

												j("#ajax_spinner_"+num_str).fadeOut();
										    j('#if_answered_'+num_str).fadeIn('slow');
											  break;
											case "fail":
											  j.fancybox("Please supply an answer before submitting.", 
																			{ 'transitionIn'	:	'fade',
																			  'transitionOut'	:	'fade',
																				'speedIn'		:	600, 
																				'speedOut'		:	200, 
																				'overlayShow'	:	true
																			}
																	);
												j("#ajax_spinner_"+num_str).fadeOut();
											  break;
											case "duplicate":
												j.fancybox("This is a duplicate answer and has not been submitted", 
																			{ 'transitionIn'	:	'fade',
																			  'transitionOut'	:	'fade',
																				'speedIn'		:	600, 
																				'speedOut'		:	200, 
																				'overlayShow'	:	true
																			}
																	);
												j("#ajax_spinner_"+num_str).fadeOut();
												break;
											default:
											  j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
																			{ 'transitionIn'	:	'fade',
																			  'transitionOut'	:	'fade',
																				'speedIn'		:	600, 
																				'speedOut'		:	200, 
																				'overlayShow'	:	true
																			}
																	);
												j("#ajax_spinner_"+num_str).fadeOut();
											}
											
											
								   }
			});


		});

 	});

	function numOrdA(a, b){ return (a-b); }

	function updateCourseViewCacheMiss() {
		// called when the cache on the server has changed
			var question_array = new Array();
			var total_questions = 0;

			j.each(j(".question_stub"), function(i,v){
				num_str = j(v).attr('id');
				num_str = num_str.replace("question_id_", "");
				num_str = parseInt(num_str);
				question_array[i] = num_str;
				question_type = j(this).find(":input[type=hidden]:first").val();

				if( question_type != "QA" && question_type != "QR")
				{
					total_questions += 1;
				}	
			});
		
				var question_array_as_string = JSON.stringify(question_array);

				j.ajax({
								type: 'POST',
								url: "<?= site_url('course/updateViewAjax')?>",
								data: {json_data : question_array_as_string, course_id : "<?= $course_data->COURSE_ID ?>"},
								error: function (request, status, error)
											 {
												  j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
																				{ 'transitionIn'	:	'fade',
																				  'transitionOut'	:	'fade',
																					'speedIn'		:	600, 
																					'speedOut'		:	200, 
																					'overlayShow'	:	true});
											 },
								success: function (response) 
												{
													clearInterval(updateTimer);
													var response_array = j.parseJSON(response);


													if(typeof(response_array)!='object' || response_array['status'] != "success")
													{
														j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
																					{ 'transitionIn'	:	'fade',
																					  'transitionOut'	:	'fade',
																						'speedIn'		:	600, 
																						'speedOut'		:	200, 
																						'overlayShow'	:	true
																					}
																			);
													}else{
														var total_questions = 0;
														var total_instants = 0;

														if(response_array['instant_questions_to_add'] != "")
														{
															j("#instant_polling_field").prepend(response_array['instant_questions_to_add']);
														}

														if(response_array['normal_questions_to_add'] != "")
														{
															j("#normal_question_field").prepend(response_array['normal_questions_to_add']);
														}

														for(add_question in response_array['questions_to_add'])
														{
															element_to_add = "#question_id_"+response_array['questions_to_add'][add_question];
															j(element_to_add).hide();
															var questionType = j(element_to_add).find(":input[type=hidden]:first").val();
															if(questionType == "QA" || questionType == "QR") {
																total_instants++;
															}
															else {
																total_questions++;
															}
														}

														for(add_question in response_array['questions_to_add'])
														{
															element_to_add = "#question_id_"+response_array['questions_to_add'][add_question];
															j(element_to_add).slideDown('slow', function(){
																j(this).effect("highlight", {color : "#C2D9C2"}, 1000);
															});
														}

														for(delete_question in response_array['questions_to_delete'])
														{
															element_to_remove = "#question_id_"+response_array['questions_to_delete'][delete_question];

															var questionType = j(element_to_remove).find(":input[type=hidden]:first").val();
															if(questionType == "QA" || questionType == "QR") {
																total_instants--;
															}
															else {
																total_questions--;
															}

															j(element_to_remove).effect("highlight", {color: "#F9A2A7"}, 1000, function(){
																j(this).slideUp('slow', function(){
																	j(this).remove();
																});
															})
														}

															j.each(j(".question_stub"), function(i,v){
															question_type = j(this).find(":input[type=hidden]:first").val();
															if( question_type != "QA" && question_type != "QR")
															{
																total_questions += 1;
															}else if(question_type == "QA" || question_type == "QR"){
																total_instants += 1;
															}	
														});
														if(total_questions > 0)
														{
															j("#no_questions_text").fadeOut('slow');
														}else{
															j("#no_questions_text").fadeIn('slow');
														}

														if(total_instants > 0)
														{
															j("#no_instant_questions_text").fadeOut('slow');
														}else{
															j("#no_instant_questions_text").fadeIn('slow');
														}

														updateTimer = setInterval("updateCourseView();", 5000);
													}

												},
								async: true
							});
		
	}


	var globalTimerForUpdate;

	function updateCourseView()
	{
		var question_array = new Array();
		var total_questions = 0;
		
		j.each(j(".question_stub"), function(i,v){
			num_str = j(v).attr('id');
			num_str = num_str.replace("question_id_", "");
			num_str = parseInt(num_str);
			question_array[i] = num_str;
			question_type = j(this).find(":input[type=hidden]:first").val();
			
			if( question_type != "QA" && question_type != "QR")
			{
				total_questions += 1;
			}	
		});
	
		
		var sortedQuestionArray = question_array.sort(numOrdA);
		if(sortedQuestionArray.length == 0) {
			var openQuestionString ="none";
		}
		else {
			var openQuestionString = sortedQuestionArray.join("_");
			
		}
		var randNum = Math.random(0,10000);
		globalTimerForUpdate = setTimeout("updateCourseViewCacheMiss()", 2000);
		j.ajax({
			type:'GET',
			dataType: 'jsonp',
			url: "<?=str_replace("https://","http://",site_url('cache/questionCache/' . $course_data->COURSE_ID . '/'))?>/" +openQuestionString + "?" + randNum,
			error: function(request,status,error) {

			},
			success: function(response,status,request) {

			}
			
		});


	}

</script>

<h1><span class="headingwhite">Viewing <?= $course_data->DEPARTMENT ?><?= $course_data->NUMBER?></span></h1>

<h3 class="legendHeader">Instant Polling</h3>
<fieldset>

	<div id="instant_polling_field">
	<? $have_open_instant = false;
		foreach($question_object_array as $question) 
		{ 
			if($question->question_is_open == true)
			{
				if(strpos($question->question_type, "QA") !== false || strpos($question->question_type, "QR") !== false)
				{
					$this->load->view("question_views/".$question->question_type."_question_stub", array("question_object"=>$question));
					$have_open_instant = true;
				}
			}
 		} ?>
		<? if(!$have_open_instant){?>
			<div id="no_instant_questions_text" > This course does not have any open polls at this time. </div>
		<?}else{?>
			<div id="no_instant_questions_text" style="display: none;"> This course does not have any open polls at this time. </div>
		<?}?>
		</div>
</fieldset>

<h3 class="legendHeader">Questions</h3>
<fieldset>

	<div id="normal_question_field">
	<? if(count($question_object_array) > 0){ 
			$no_questions = true;
			foreach($question_object_array as $question) 
			{ 
				if($question->question_is_open == true)
				{
					if(strpos($question->question_type, "Q") === false)
					{
						$this->load->view("question_views/".$question->question_type."_question_stub", array("question_object"=>$question));
						$no_questions = false;
					}
				}
		 	}
		 	if($no_questions){ ?>
				<div id="no_questions_text"> This course does not have any questions at this time. </div>
			<? }else{ ?>
				<div id="no_questions_text" style="display: none;"> This course does not have any questions at this time. </div>
			<? } 		
 } ?>

	</div>
</fieldset>