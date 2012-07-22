
j("#user_auto").autocomplete("/course/peopleSearchAutocompleter/", {

	formatItem: function(resultdata, i, totals, term) {

		var entityParts = resultdata[0].split('::');
	  var entityType  = entityParts[0];
	  var entityId    = entityParts[1];
	  var per_id 	  = entityParts[2];
	  var office      = entityParts[3];
		var parts = resultdata[0].split(')');
	  var display_name = parts[0] + ')';
		return entityType + " (" + entityId + ")<br /> " + office;
	},
	formatResult: function(data, position, total) {
		var entityParts = data[0].split('::');
	  var entityName  = entityParts[0].split(',');
		var name = entityName[1] + " " + entityName[0];
		return name;
	},
	multiple: true,
});

j("#user_auto").result(function(event,data,formatted) {

    var entityParts = data[0].split('::');
  var entityType  = entityParts[0];
  var entityId    = entityParts[1];
  var per_id 	    = entityParts[2];
  var office      = entityParts[3];
	var parts = data[0].split(')');
  var display_name = parts[0] + ')';
	
	var innerContents = "<span class=\"new_user_span\" id='new_user_"+per_id+"'>" + entityType + 
											" (" + entityId + ") <input type='button' onclick=\"clearAdminForm('new_user_"+per_id+"');\" value='X' />" + 
											"<input type='hidden' name='user[]' value='" + per_id + "' />" +
											"<br/></span>";
	
	contents = 	j("#user_name").html();
	j("#user_name").html(contents + " " + innerContents);
	j("#user_auto").val("");

});
	
function clearAdminForm(element) {

	 j("#"+element).remove();
	
}


function auto_complete_on_select(element, selectedElement)
{
  var entityParts = selectedElement.id.split('::');
  var entityType  = entityParts[0];
  var entityId    = entityParts[1];
  var per_id 	  = entityParts[2];
  var office      = entityParts[3];
  //messy, but seems to be neccessary
  //comes in as Name (uid)Office
  var parts = element.value.split(')');
  var display_name = parts[0] + ')';

  document.getElementById(entityType).value = per_id;
  document.getElementById(entityType+'_name').innerHTML = display_name +
       "<a href=\"#\" onclick=\"remove_cla_person('"+entityType+"');\">[x]</a>";
  if(office != '') {
    document.getElementById(entityType+'_name').innerHTML += '<br /><span class="office">' + office + '</span>'	
  }
  element.value = '';
}

function multiple_auto_complete_on_select(element, selectedElement)
{
  var entityParts = selectedElement.id.split('::');
  var entityType  = entityParts[0];
  var entityId    = entityParts[1];
  var per_id 	  = entityParts[2];

  //messy but seems neccessary
  //comes in as Name (uid)Office
  var parts = element.value.split(')');
  var display_name = parts[0] + ')';

//  alert(entityType + '/' + entityId + '/' + per_id)
  options = document.getElementById(entityType).innerHTML;
  options = options += '<option value="'+ per_id +'">'+display_name+'</option>';
  document.getElementById(entityType).innerHTML = options;
  element.value = '';
}