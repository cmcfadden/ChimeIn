
<script>
	var j = jQuery.noConflict();
	var updateTimer;
	
	j(document).ready(function() {		
		j("a#inline").fancybox({
				'hideOnContentClick': false
			});
		updateTimer = setInterval("updateResultCounts();", 5000);
		j("#sortable").sortable({
			 stop: function(event, ui) { 
				j.post("<?=site_url('question/setSortOrder')?>",
				
					j("#sortable").sortable('serialize')
									
				);
			 },
		});

		j("#sortable").disableSelection();
	});
	
	
	function submitStatusChange(id)
	{
		var new_val;
		var status;
		
		if(j("#status_button_"+id).val() == 'Close')
		{
			new_val = 'Open';
			status = '0';
		}else{
			new_val = 'Close';
			status = '1';
		}
		
		j.post("<?= site_url('question/toggleStatus')?>", 
					{ question_is_open: status, question_id : id}, 
					function (response) 
					{
						if(response != "success")
						{
							j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
														{ 'transitionIn'	:	'fade',
														  'transitionOut'	:	'fade',
															'speedIn'		:	600, 
															'speedOut'		:	200, 
															'overlayShow'	:	true});
						}
					}
		);
		
		j("#status_button_"+id).val(new_val);
		
		if(new_val == 'Open')
		{
			j("#edit_question_"+id).attr('disabled', '');
			j("#reset_button_"+id).attr('disabled', '');
		}else{
			j("#edit_question_"+id).attr('disabled', 'disabled');
			j("#reset_button_"+id).attr('disabled', 'disabled');
		}
		
	}
	
	function submitQAStatusChange(id)
	{
		var new_val;
		var status;
		
		if(j("#QA_status_button_"+id).val() == 'Close')
		{
			new_val = 'Open';
			status = '0';
		}else{
			new_val = 'Close';
			status = '1';
		}
		
		var count = j("#QA_answer_count").val();
		
		j.post("<?= site_url('question/toggleQAStatus')?>", 
					{ question_is_open: status, question_id : id, answer_count: count}, 
					function (response) 
					{
						if(response != "success")
						{
							j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
														{ 'transitionIn'	:	'fade',
														  'transitionOut'	:	'fade',
															'speedIn'		:	600, 
															'speedOut'		:	200, 
															'overlayShow'	:	true});
						}
					}
		);
		
		//swap Open/Close text
		j("#QA_status_button_"+id).val(new_val);
		//grey out other options
		if(new_val == 'Open')
		{
			j("#QA_reset_button").attr('disabled', '');
		}else{
			j("#QA_answer_count").attr('disabled', 'disabled');
			j("#QA_reset_button").attr('disabled', 'disabled');
		}
	}
	
	function submitQAReset(id)
	{
		j("#QA_answer_count").attr('disabled', '');
		
		j.post("<?= site_url('question/resetQA')?>", 
					{ question_id : id}, 
					function (response) 
					{
						if(response != "success")
						{
							j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
														{ 'transitionIn'	:	'fade',
														  'transitionOut'	:	'fade',
															'speedIn'		:	600, 
															'speedOut'		:	200, 
															'overlayShow'	:	true});
						}
					}
		);
		
		j("#result_count_"+id).text(0);
	}
	
	function submitQRStatusChange(id)
	{
		var new_val;
		var status;
		
		if(j("#QR_status_button_"+id).val() == 'Close')
		{
			new_val = 'Open';
			status = '0';
		}else{
			new_val = 'Close';
			status = '1';
		}
		
		j.post("<?= site_url('question/toggleQRStatus')?>", 
					{ question_is_open: status, question_id : id}, 
					function (response) 
					{
						if(response != "success")
						{
							j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
														{ 'transitionIn'	:	'fade',
														  'transitionOut'	:	'fade',
															'speedIn'		:	600, 
															'speedOut'		:	200, 
															'overlayShow'	:	true});
						}
					}
		);
		
		//swap Open/Close text
		j("#QR_status_button_"+id).val(new_val);
		//grey out other options
		if(new_val == 'Open')
		{
			j("#QR_reset_button").attr('disabled', '');
		}else{
			j("#QR_reset_button").attr('disabled', 'disabled');
		}
	}
	
	function submitQRReset(id)
	{
		j.post("<?= site_url('question/resetQR')?>", 
					{ question_id : id}, 
					function (response) 
					{
						if(response != "success")
						{
							j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
														{ 'transitionIn'	:	'fade',
														  'transitionOut'	:	'fade',
															'speedIn'		:	600, 
															'speedOut'		:	200, 
															'overlayShow'	:	true});
						}
					}
		);
		
		j("#result_count_"+id).text(0);
	}
	
	function updateResultCounts()
	{
		j.ajax({
						type: 'POST',
						url: "<?= site_url('question/updateResultCountsAjax')?>",
						data: {course_id : "<?= $course_data->COURSE_ID ?>"},
						success: function (response) 
										{
											var response_array = eval("(" + response + ")");
											if(response_array['status'] != "success")
											{
												j.fancybox("There has been an error. <br/> Please refresh the page and try again.", 
																			{ 'transitionIn'	:	'fade',
																			  'transitionOut'	:	'fade',
																				'speedIn'		:	600, 
																				'speedOut'		:	200, 
																				'overlayShow'	:	true});
											}else{
												clearInterval(updateTimer);
												

												for(element in response_array)
												{
													element_to_edit = "#result_count_"+response_array[element]['question_id'];
													new_count = response_array[element]['count'];
													if( j(element_to_edit).text() != Number(new_count))
													{
														function wrapper(count) {
															return function() {
																	j(this).text(count);
																	j(this).fadeIn('fast');
															}
														}
														j(element_to_edit).fadeOut('fast', wrapper(new_count));
													}
												}

												updateTimer = setInterval("updateResultCounts();", 5000);
											}
												
										},
						async: true
					});
	}
	

	
	
	function showQuestionInFancyBox(q_id)
	{
		j.fancybox.showActivity();
		j.ajax({
						type: 'POST',
						url: "<?= site_url('question/getExpandedQuestionDisplay')?>",
						data: {question_id : q_id},
						success: function (response) 
										{

											j("#expand_span").width(j(window).width()*.79);
											j("#expand_span").html(response);
											j(".expand_stub").attr('style', "font-size: 40px;");
											
											
											height = j("#expand_span").height();
											box_height = j(window).height() * .79;
											default_size = 40;
											
											//check height of div
											if(height > box_height)
											{
												//if greater, loop through lowering until it fits
												while(j("#expand_span").height() > box_height)
												{
													default_size -= 2;
													j(".expand_stub").attr('style', "font-size: "+default_size+"px;");
													j("#cellphoneSubmission").attr('style', "font-size: "+(default_size-10)+"px;");


												}												
											}else if(height < box_height)
											{
												//if less, loop through until larger, then scale back one
												while(j("#expand_span").height() < box_height)
												{
													default_size += 2;
													j(".expand_stub").attr('style', "font-size: "+default_size+"px;");
																										j("#cellphoneSubmission").attr('style', "font-size: "+(default_size-10)+"px;");

												}
												default_size -= 2;
												j(".expand_stub").attr('style', "font-size: "+default_size+"px;");
																									j("#cellphoneSubmission").attr('style', "font-size: "+(default_size-10)+"px;");

											}else{
												//equal to
												//drop down 1 size to be safe?
												default_size -= 2;
												j(".expand_stub").attr('style', "font-size: "+default_size+"px;");
																									j("#cellphoneSubmission").attr('style', "font-size: "+(default_size-10)+"px;");

											}
											
											//show the fancybox											
											j.fancybox(j("#expand_span").html(), { 'transitionIn'	:	'elastic',
											  'transitionOut'	:	'elastic',
												'hideOnOverlayClick':true,
												'easingIn'      : 'easeOutBack',
												'easingOut'     : 'easeInBack',
												'width': '85%',
												'height': '85%',
												'speedIn'		:	600, 
												'speedOut'		:	400, 
												'overlayShow'	:	true,
												'autoDimensions': false,
												'orig': j("#expand_bullet_"+q_id)});
										},
						async: true
					});
	}

</script>

<h1><span class="headingwhite">Editing <?= $course_data->DEPARTMENT ?><?= $course_data->NUMBER?> <?=$course_data->PUBLIC?"(<a href=\"#publicLink\" id=\"inline\">Public</a>)":""?></span></h1>

<h3 id="manageCourse">						<?= anchor("course/manage/".$course_data->COURSE_ID, "Manage Course")?></h3>

<h3 class="legendHeader">Instant Polling</h3>
<fieldset>

	
		<? foreach ($question_object_array as $question){ ?>
			<? if(strpos($question->question_type, "QA") !== false) { ?>
				<div class='question_bullet'>
						<div class='result_counter' id='result_count_<?=$question->question_id?>'> 
						<p><?= $question->result_count ?></p>
						</div>
					<div class="questiontextCourseEditDiv"><p class="questionTextcourEditP">
					<?= anchor("result/view/".$question->question_id, $question->question_text); ?> 
					<? if($question->answer_count > 0) {?>
						<select id="QA_answer_count" disabled="disabled">
					<?}else{?>
						<select id="QA_answer_count">
					<? } ?>
						  <option value="2">2</option>
						  <option value="3">3</option>
						  <option value="4">4</option>
						  <option value="5">5</option>
						  <option value="6">6</option>
						  <option value="7">7</option>
						  <option value="8">8</option>
						  <option value="9">9</option>
						  <option value="10">10</option>
						</select>
								Answers
								</p></div>
								<div class="buttonContainer">
					<? if($question->question_is_open == true) {?>
						<input type="button" class='QA_status_button' id='QA_status_button_<?=$question->question_id?>' value="Close" onClick="submitQAStatusChange(<?=$question->question_id?>)"/>
						<input type='button' id='QA_reset_button' value='Reset' disabled='disabled' onClick="submitQAReset(<?=$question->question_id?>)"/>
					<? }else{ ?>
						<input type="button" class='QA_status_button' id='QA_status_button_<?=$question->question_id?>' value="Open" onClick="submitQAStatusChange(<?=$question->question_id?>)"/>
						<input type='button' id='QA_reset_button' value='Reset' onClick="submitQAReset(<?=$question->question_id?>)"/>
					<? } ?>
					
						<input type='button' class='expand_bullet' id='expand_bullet_<?=$question->question_id?>' value='Expand' onclick="showQuestionInFancyBox('<?=$question->question_id?>')" />
						</div>
				</div>
				<? } ?>
				
				<? if(strpos($question->question_type, "QR") !== false){ ?>
						<div class='question_bullet'>
								<div class='result_counter' id='result_count_<?=$question->question_id?>'> 
								<p><?= $question->result_count ?></p>
								</div>
							<div class="questiontextCourseEditDiv"><p class="questionTextcourEditP"><?= anchor("result/view/".$question->question_id, $question->question_text); ?> </p></div>
						<div class="buttonContainer">
							<? if($question->question_is_open == true) {?>
								<input type='button' class='QR_status_button' id='QR_status_button_<?=$question->question_id?>' value='Close' onClick="submitQRStatusChange(<?=$question->question_id?>)"/>
								<input type='button' id='QR_reset_button' value='Reset' disabled='disabled' onClick="submitQRReset(<?=$question->question_id?>)"/>
							<? }else{ ?>
								<input type='button' class='QR_status_button' id='QR_status_button_<?=$question->question_id?>' value='Open' onClick="submitQRStatusChange(<?=$question->question_id?>)"/>
								<input type='button' id='QR_reset_button' value='Reset' onClick="submitQRReset(<?=$question->question_id?>)"/>
							<? } ?>
							
							<input type='button' class='expand_bullet' id='expand_bullet_<?=$question->question_id?>' value='Expand' onclick="showQuestionInFancyBox('<?=$question->question_id?>')" />
							</div>
						</div>
				<? } ?>
				
		<? } ?>
		
		
		
</fieldset>

<h3 class="legendHeader">Questions</h3>
<fieldset>

	<?=  anchor("question/add/".$course_data->COURSE_ID, "Add New Question"); ?><br />

	<div id="sortable" class="droppableList">
	
	<? if(count($question_object_array) > 0){ ?>
			<? $no_questions = true; ?>
			<? foreach ($question_object_array as $question){
					
				 	if(strpos($question->question_type, "Q") === false){
						$no_questions = false; ?>
						<div class='question_bullet' id="question_id_<?=$question->question_id?>"> 
							<div class='result_counter' id='result_count_<?=$question->question_id?>'> 
							<p><?= $question->result_count ?></p>
							</div>
							<div class="questiontextCourseEditDiv"><p class="questionTextcourEditP"><?= anchor("result/view/".$question->question_id, $question->question_text); ?> </p></div>
							<div class="buttonContainer">
							<? if($question->question_is_open == true) {?>
								<input type="button" class='status_button' id='status_button_<?=$question->question_id?>' value="Close" onClick="submitStatusChange(<?=$question->question_id?>)"/>
															<input type='button' class='edit_question' id='edit_question_<?=$question->question_id?>' value='Edit' onclick="window.location = '<?= base_url().'question/edit/'.$question->question_id?>';" disabled='disabled'/>
															<input type='button' id='reset_button_<?=$question->question_id?>' value='Reset' disabled='disabled' onClick="submitQRReset(<?=$question->question_id?>)"/>
							<? }else{ ?>
								<input type="button" class='status_button' id='status_button_<?=$question->question_id?>' value="Open" onClick="submitStatusChange(<?=$question->question_id?>)"/>
															<input type='button' class='edit_question' id='edit_question_<?=$question->question_id?>' value='Edit' onclick="window.location = '<?= base_url().'question/edit/'.$question->question_id?>';" />
															<input type='button' id='reset_button_<?=$question->question_id?>' value='Reset' onClick="submitQRReset(<?=$question->question_id?>)"/>

							<? } ?>
							
							<form method="post" action="<?= site_url("question/delete") ?>">
								<input type='hidden' name='question_id' value='<?=$question->question_id?>'/>
								<input type='hidden' name='course_id' value='<?=$course_data->COURSE_ID?>'/>
								<input type="submit" value="Delete">
							</form>

							<input type='button' class='expand_bullet' id='expand_bullet_<?=$question->question_id?>' value='Expand' onclick="showQuestionInFancyBox('<?=$question->question_id?>')" />
							</div>
						</div>
					<? } ?>
			<? } ?>
			<? if($no_questions){ ?>
				<div>This course does not have any questions at this time.</div>
			<?}
	 } ?>

		</div>
</fieldset>

<div id='expand_span' style="position: fixed; left:-5000px; top:-5000px">

</div>

<div style="display:none"><div id="publicLink" style="font-size:2.5em; width:900px">Public URL for Course:<br /><?=site_url("course/view/" . $course_data->COURSE_ID);?></div></div>