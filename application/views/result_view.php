<script>
	var j = jQuery.noConflict();
	var updateTimer;
	
	function startResultCheckTimer()
	{		
		updateTimer = setInterval("checkForResults();", 5000);
	}
	
	function checkForResults()
	{
			j.ajax({
							type: 'POST',
							url: "<?= site_url('result/checkForResultsAjax')?>",
							data: {question_id: <?= $question_model->question_id ?>},
							success: function (response) 
											{
												if(response == 1)
												{
													location.reload(true);
												}
											},
							async: true
						}); 

	}
</script>


<h1><span class="headingwhite">Question #<?=$question_model->question_id?></span></h1>

<div id='question_id_<?= $question_model->question_id ?>'>
<h3 class="resultQuestionText"><?=$question_model->question_text?></h3>

</div>

<h2><span class="headingwhite">Results</span></h2> 

<? if(count($result_data) > 0){ ?>
			
			<?=$this->load->view("result_views/".$question_model->question_type."_result_stub"); ?>
			
<? }else{ ?>
	<div>This question does not have any results at this time.</div>
	<script> startResultCheckTimer(); </script>
<? } ?>