
function showCellPhone() 
{
	j('#cellPhoneSettings').slideDown('slow');
	
	return false;
}

function submitPhoneNumber() 
{
	var phoneNumber = j('#cellNumber').val();
	
	if(phoneNumber == "")
	{
		j("#cell_error").fadeIn();
		j.fancybox.resize();
		return;
	}
	
	j.fancybox.showActivity();
	
	j.ajax({
	   type: "POST",
	   url: "/course/sendToPhone",
	   data: "phoneNumber=" + phoneNumber,
	   success: function(msg)
							{
								j.fancybox(j("#cell_confirm"));
								j('#hiddenNumber').val(msg);
	   					}
	 });
	
}

function confirmNumber() {
	if(j('#hiddenNumber').val() == j('#secretNumber').val()) 
	{
		var phoneNumber = j('#cellNumber').val();
		j.ajax({
		   type: "POST",
		   url: "/course/savePhoneNumber",
		   data: "phoneNumber=" + phoneNumber,
		   success: function(msg)
								{
									j.fancybox.close();
									window.location.reload();
		   					}
		 });

	}else{
		j.fancybox("Secret Code Incorrect.  Try Again.");
	}
	
	
}

var j = jQuery.noConflict();
j(document).ready(function() {
	j("#cell_phone_edit").fancybox({ 'transitionIn'	:	'fade',
	  'transitionOut'	:	'fade',
		'easingIn'      : 'easeOutBack',
		'easingOut'     : 'easeInBack',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	true,
		'onClosed'		: function() 
										{
			    						j("#cell_error").hide();
										}
		});

});