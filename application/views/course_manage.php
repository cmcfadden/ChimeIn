<script type="text/javascript" src="<?=base_url();?>assets/js/jquery.autocomplete.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/jquery.autocomplete.css" />
	<h1><span class="headingwhite">Manage Course</span></h1>

<h3 class="legendHeader">General</h3>
<fieldset>

	<form method="post" action="<?= site_url("course/delete") ?>">
		<input type="hidden" name="course_id" value='<?=$course_data->COURSE_ID?>' />
		<input type="submit" value="Delete Course" onclick="return confirm('Are you sure you want to delete this course?');"></form>
</fieldset>
<fieldset>		
	Course Settings<br />
	<form method="post" action="<?= site_url("course/editCourseSettings") ?>">

	<input type="hidden" name="course_id" value='<?=$course_data->COURSE_ID?>' />
	    <div class="course_user_node">Public:<input type="checkbox" size="30" maxlength="5" name="public" <?=$course_data->PUBLIC?"checked":""?>>
			</div>
	<div class="course_user_node">Disable Language Parsing:<input type="checkbox" size="30" maxlength="5" 
name="disable_language_parsing" <?=$course_data->DISABLE_LANGUAGE_PARSING?"checked":""?>>
	</div>
	<div class="course_user_node">Show Related Words:<input type="checkbox" size="30" maxlength="5" name="show_related_words" 
<?=$course_data->SHOW_RELATED_WORDS?"checked":""?>>
	</div>


	<input type="submit" value="Save"></form>
		
</fieldset>

<h3 class="legendHeader">Instructors(s)</h3>
<fieldset>
Current Instructors:
<? foreach($instructors as $instructor){ ?>
	<div class='course_user_node'>
		<?= $instructor->name?>
		<form method="post" action="<?= site_url("course/removeInstructor") ?>">
			<input type="hidden" name="per_id" value='<?=$instructor->INSTRUCTOR_ID?>' />
			<input type="hidden" name="course_id" value='<?=$course_data->COURSE_ID?>' />

			<? if($instructor->INSTRUCTOR_ID == $this->perId) { echo "<span class=\"removeYourself\">You Cannot Remove Yourself</span>"; } else { ?><input class="remove_user_button" type="submit" value="Remove Instructor"><? } ?><br/>
		</form>
	</div>
<? } ?>
</fieldset>
<fieldset>
Adding instructors gives them full course control:<br/><br/>
<form method="post" action="<?= site_url("course/addNewInstructor") ?>">
	Instructor: <input id="instructor_auto" name="user" size="30" type="text" />
	<div class="auto_complete" id="user_autocomplete">
	</div>
	<div id="instructor_display">
		<div id="instructor_name_wrapper">
	</div>
	</div>
	<input type="hidden" name="course_id" value='<?=$course_data->COURSE_ID?>' />
	<input type="submit" value="Add Instructor(s) To Course"> 
	</form>


</fieldset>

<h3 class="legendHeader">User(s)</h3>
<fieldset>

	Current Users:<br/>
	
	<? foreach($users as $user){ ?>
		<div class='course_user_node'>
			<?= $user->name?>
			<form method="post" action="<?= site_url("course/removeUser") ?>">
				<input type="hidden" name="per_id" value='<?=$user->PER_ID?>' />
				<input type="hidden" name="course_id" value='<?=$course_data->COURSE_ID?>' />
				<input class="remove_user_button" type="submit" value="Remove User"><br/>
			</form>
		</div>
	<? } ?>
</fieldset>
<fieldset>
	
	<div>
		Adding a user to a course allows them student access.<br/><br/>
		<form method="post" action="<?= site_url("course/addNewUser") ?>">
			User: <input id="user_auto" name="user" size="30" type="text" />
			<div class="auto_complete" id="user_autocomplete">
			</div>
			<div id="user_display">
 				<div id="user_name">
				</div>
			</div>
		<input type="hidden" name="course_id" value='<?=$course_data->COURSE_ID?>' />
		<input type="submit" value="Add User(s) To Course"> 
		</form>
	</div>
</fieldset>




<script type="text/javascript" src="<?=base_url();?>assets/js/userAutocomplete.js"></script>
<script type="text/javascript" src="<?=base_url();?>assets/js/peopleAutocomplete.js"></script>
