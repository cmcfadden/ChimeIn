var questionClosedTimer;

$(document).live("pageshow", function(event) {

	var targetElement = event.target.id;
	

	if(targetElement.indexOf("course") == -1 && targetElement.indexOf("view")>=0) {
		// loading a question page
		var questionId = targetElement.replace("view","");
		questionClosedTimer = setInterval(function(){ checkQuestion(questionId);}, 15000);
	}
});

$(document).live("pagehide", function(event) {

	clearInterval(questionClosedTimer);

});

function checkQuestion(questionId) {
	//TODO
	$.get('/course/isQuestionOpen/'+questionId, function(data) {
		if(data == "true") {

		}
		else {
			var courseId = data;
			$.mobile.changePage( "/course/view/"+courseId, { transition: "slide", reverse:true} );
			updateCourseViewCacheMiss()
		}
	});
	
}


$(document).live("pageinit", function(event) {

	$(document).unbind('submit');
	$(document).submit(function(event) {
		event.preventDefault();
		var question_id = $("#"+event.target.id + " input[name=question_id]").val();
		var question_type = $("#"+event.target.id + " input[name=question_type]").val();
		
		if(question_type == "FR" || question_type == "QR") {
			var answer = $("#"+event.target.id + " textarea[name=answer]").val();			
		}
		else {
			var answer = $("#"+event.target.id + " input[name=answer]:checked").val();			
		}

		
		
		var errorAlertDialog = "<div data-role=\"page\" id=\"errorSubmitting\" data-theme=\"c\"> \
			<div data-theme=\"c\" data-role=\"content\"> \
				<h2>There was an error submitting this page</h2> \
				<p>Please reload and try again.</p> \
				<a href=\"#\" data-role=\"button\" data-rel=\"back\">OK</a> \
			</div> \
		</div>";
		
		var duplicateAlertDialog = "<div data-role=\"page\" id=\"duplicateResponse\" data-theme=\"c\"> \
			<div data-theme=\"c\" data-role=\"content\"> \
				<h2>Duplicate Response</h2> \
				<p>You may not submit the same free-text answer multiple times.</p> \
				<a href=\"#\" data-role=\"button\"  data-rel=\"back\">OK</a> \
			</div> \
		</div>";
		
		var successfulSubmission = "<div data-role=\"page\" id=\"successfulSubmission\" data-theme=\"c\"> \
			<div data-theme=\"c\" data-role=\"content\"> \
				<h2>Submission Successful</h2> \
				<a href=\"#\" data-role=\"button\" data-rel=\"back\">OK</a> \
			</div> \
		</div>";
		

		$("body").append(errorAlertDialog);
		$("body").append(duplicateAlertDialog);
		$("body").append(successfulSubmission);				
		
		
		
		$.ajax({
			type: 'POST',
			url: "/result/addNew",
			data: "question_id="+question_id+"&answer="+answer+"&question_type="+question_type,
			error: function(request, status, error) {
					$.mobile.changePage( $('#errorSubmitting'+question_id), { role: 'dialog', transition: 'pop'} );
			},
			success: function(response) {

				if(response != "success" && response != "duplicate") {
					$.mobile.changePage( $('#errorSubmitting'), { role: 'dialog', transition: 'pop'} );
				}
				else if(response == "duplicate") {
					$.mobile.changePage( $('#duplicateResponse'), { role: 'dialog', transition: 'pop'} );
				}
				else {
					$.mobile.changePage( $('#successfulSubmission'), { role: 'dialog', transition: 'pop'} );
					if(question_type != "QR" && question_type != "FR") {
						$("#if_answered_"+question_id).fadeIn();
					}

				}
			}


		})
		
	})
});