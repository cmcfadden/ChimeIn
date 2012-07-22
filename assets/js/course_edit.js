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
	}else{
		j("#edit_question_"+id).attr('disabled', 'disabled');
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
					async: false
				});
}

function showQuestionInFancyBox(q_id)
{
	j.ajax({
					type: 'POST',
					url: "<?= site_url('question/getExpandedQuestionDisplay')?>",
					data: {question_id : q_id},
					success: function (response) 
									{
										j.fancybox(response, { 'transitionIn'	:	'elastic',
										  'transitionOut'	:	'elastic',
											'hideOnOverlayClick':true,
											'easingIn'      : 'easeOutBack',
											'easingOut'     : 'easeInBack',
											'width': '75%',
											'height': '75%',
											'speedIn'		:	600, 
											'speedOut'		:	400, 
											'overlayShow'	:	true,
											'autoDimensions': false,
											'orig': j("#expand_bullet_"+q_id)});
									},
					async: false
				});
}
