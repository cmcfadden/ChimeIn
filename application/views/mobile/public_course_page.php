
<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-1.4.2.min.js"></script>
<script language="javascript" type="text/javascript" src="<?=base_url()?>assets/js/json2.js"></script>
<script src="<?=base_url()?>assets/js/jqtouch.min.js" type="application/x-javascript" charset="utf-8"></script>
<style type="text/css" media="screen">@import "<?=base_url()?>assets/css/jqtouch.min.css";</style>
<style type="text/css" media="screen">@import "<?=base_url()?>assets/css/theme.min.css";</style>
<script>
$.jQTouch({
        icon: 'jqtouch.png',
        statusBar: 'black-translucent',
        preloadImages: [
            '<?=base_url()?>assets/css/img/back_button_clicked.png',
            '<?=base_url()?>assets/css/img/button_clicked.png'
            ]
    });


</script>	

<body id="mobile_body">
<div id="home" class="current">

	<div class="toolbar">
		<h1>Viewing <?= $course_data->DEPARTMENT ?><?= $course_data->NUMBER?></h1>
		<a class="button slideup" id="infoButton" href="#about">About</a>
	</div>
	<ul id="instant_question_list" class="rounded">
	<? 	$questionArray = array();
	if(count($question_object_array) > 0){ 
			$no_questions = true;
			foreach($question_object_array as $question) 
			{ 
				if($question->question_is_open == true)
				{
					if(strpos($question->question_type, "Q") ===0)
					{?>
					<li id="question_id_li_<?=$question->question_id?>" class="arrow"><a href="#question_id_<?=$question->question_id?>"><?=$question->question_text?></a></li>
					
					<?
						$questionArray[] = $question;
						$no_questions = false;
					}
				}
		 	}
		 	if($no_questions){ ?>
				<li id="instant_no_questions_text"> This course does not have any open instant questions at this time </li>
			<? } else { ?>
				<li id="instant_no_questions_text" style="display:none">This course does not have instant any questions at this time </li>
			<? }		
 } ?>
	</ul>
	
	<ul id="normal_question_list" class="rounded">
		<? if(count($question_object_array) > 0){ 
				$no_questions = true;

				foreach($question_object_array as $question) 
				{ 
					if($question->question_is_open == true)
					{
						if(strpos($question->question_type, "Q") === false)
						{?>
						<li id="question_id_li_<?=$question->question_id?>" class="arrow"><a href="#question_id_<?=$question->question_id?>"><?=$question->question_text?></a></li>
						
						<?
							$questionArray[] = $question;
							$no_questions = false;
						}
					}
			 	}
			 	if($no_questions){ ?>
					<li id="no_questions_text"> This course does not have any questions at this time </li>
				<? } else { ?>
					<li id="no_questions_text" style="display:none">This course does not have any questions at this time </li>
				<? }		
	 } ?>	
	</ul>

	
</div>

</body>



<? foreach($questionArray as $question) {

	$this->load->view("mobile/question_views/".$question->question_type."_question_stub",array("question_object"=>$question));
	
}?>

<div id="fakeStuff">
	<script>
			var updateTimer;
			$(document).ready(function() {
				updateTimer = setInterval("updateCourseView();", 5000);
 				$("form").live("submit", function(e) {
					e.preventDefault();
					var form_id = $(this).attr('id');
					num_str = form_id.replace("question_form_", "");
					num_str = parseInt(num_str);
					form_id = "#"+form_id+" :input";
					var inputs = $(form_id).serialize();
					$("#ajax_spinner_"+num_str).fadeIn();

					$.ajax({
						type: 'POST',
						url: "<?= site_url('result/addNew')?>",
						data: inputs,
						error: function(request, status, error) {
							alert("An error occurred while submitting your response.  Please reload the page manually and try again.");
						},
						success: function(response) {
							
							if(response != "success") {
								alert("There was an error submitting your response.  Please reload the page and try again.");
							}
							else {
					
								
								$("#success_box_"+num_str).fadeIn();
								setTimeout( function(){ $("#success_box_"+num_str).fadeOut(); }, 1000);
								$("#ajax_spinner_"+num_str).fadeOut();
						   
							}
						}
						
						
					})
				});
			});
			
			
			function numOrdA(a, b){ return (a-b); }
			
			var globalTimerForUpdate;
			
			function updateCourseView()
			{
				var question_array = new Array();
				var total_questions = 0;

				$.each($(".question_stub"), function(i,v){
					num_str = $(v).attr('id');
					num_str = num_str.replace("question_id_", "");
					num_str = parseInt(num_str);
					question_array[i] = num_str;
					question_type = $(this).find(":input[type=hidden]:first").val();

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
				globalTimerForUpdate = setTimeout("updateCourseViewCacheMiss()", 2000);
var randNum =Math.random(1,10000);
				$.ajax({
					type:'GET',
					dataType:'jsonp',
					url: "<?=str_replace("https://","http://",site_url('cache/questionCache/' . $course_data->COURSE_ID . '/'))?>/" +openQuestionString + "?" + randNum,
					error: function(request,status,error) {


					},
					success: function(response) {

					}

				});
			}
			
			
			function updateCourseViewCacheMiss() {

				var question_array = {};
				var total_questions = 0;
				var total_instant_questions = 0;
				$.each($(".question_stub"), function(i,v){
					num_str = $(v).attr('id');

					num_str = num_str.replace("question_id_", "");
					num_str = parseInt(num_str);
					question_array[i] = num_str;
					question_type = $(this).find(":input[type=hidden]:first").val();
					if( question_type != "QA" && question_type != "QR")
					{
						total_questions += 1;
					}	
					
					if(question_type == "QA" || question_type == "QR") {
						total_instant_questions +=1;

					}
				});
		
				question_array = JSON.stringify(question_array);
				
				// TODO IS THIS WHERE THIS TIMER SHOULD BE?
				clearInterval(updateTimer);
				$.ajax({
								type: 'POST',
								url: "<?= site_url('course/updateViewAjax')?>",
								data: {json_data : question_array, course_id : "<?= $course_data->COURSE_ID ?>"},
								error: function(request, status,error) {
									alert("There was an error refreshing the page.  Please manually reload and try again.");
								},
								success: function (response) 
												{
													
														var response_array = $.parseJSON(response);

														if(typeof(response_array)!='object' || response_array['status'] != "success")
														{
														alert("An error occurred updating the page.  Please manually reload and try again.");
													}
													else {
															if(response_array['instant_questions_to_add'] != "")
															{
																$("#instant_question_list").prepend(response_array['instant_links_to_add_string']);
																$("#mobile_body").prepend(response_array['instant_questions_to_add']);
															}
															if(response_array['normal_questions_to_add'] != "")
															{

																$("#normal_question_list").prepend(response_array['normal_links_to_add_string']);

																$("#mobile_body").prepend(response_array['normal_questions_to_add']);
															}

															for(add_question in response_array['questions_to_add'])
															{
																element_to_add = "#question_id_li_"+response_array['questions_to_add'][add_question];
																if($(element_to_add).parent().attr('id') == "instant_question_list") {
																	total_instant_questions++;
																}
																else {
																	total_questions++;
																}

																$(element_to_add).slideDown('slow');
															}
															for(delete_question in response_array['questions_to_delete'])
															{
																element_to_remove = "#question_id_li_"+response_array['questions_to_delete'][delete_question];
																second_element_to_remove = "#question_id_"+response_array['questions_to_delete'][delete_question];
																if(self.location.href.match('#question_id_' + +response_array['questions_to_delete'][delete_question])) {
																	self.location.href='#home';
																}
																$(second_element_to_remove).remove();
																$(element_to_remove).slideUp('slow', function() {

																	$(this).remove();															
																});
																if($(element_to_remove).parent().attr('id') == "instant_question_list") {
																	total_instant_questions--;
																}
																else {
																	total_questions--;
																}



															}



															if(total_questions > 0)
															{
																$("#no_questions_text").slideUp('slow');
															}else{
																$("#no_questions_text").slideDown('slow');
															}
															if(total_instant_questions > 0) {
																$("#instant_no_questions_text").slideUp('slow');
															}
															else {
																$("#instant_no_questions_text").slideDown('slow');
															}

															updateTimer = setInterval("updateCourseView();", 5000);
													}
												
												},
								async: true
							});
			}
			
			
			
			
			
	</script>



	</div>


<div id="about">
<div class="toolbar">
	<a class="back" href="#">BACK</a>
</div>
<ul class="edit rounded">
<li>ChimeIn is a powerful and fun web-based student response tool. No need to manage dozens or hundreds of expensive clickers - ChimeIn leverages the digital devices students already carry to provide a rich user experience. Instructors manage questions via a simple web interface, and students can respond via web browsers, smart phones, or text messages.</li>

<li>
We’ve created a short video to provide a ChimeIn overview. Our “Help” also includes a series of introductory documents.</li>
<li>If you’re staff or faculty at the University of Minnesota, you should already have access. Click “Log In” above to get started. If you’re a student enrolled in a course that uses ChimeIn, click “Log In” and then select your course.</li>

<li>We strongly recommend using a modern webbrowser when using ChimeIn. These include Safari 4+, Chrome 5+, FireFox 3.6+, or Internet Explorer 8+.</li>

<li>ChimeIn grew out of Dr. Kay Reyerson's "Twitter project" for her CLA Course Transformation grant in spring 2010.</li>

<li>If you have any questions, please <a href="mailto:4help@umn.edu">let us know</a>.</li>
</ul>
</div>
<div id="placeholderDiv">

</div>

