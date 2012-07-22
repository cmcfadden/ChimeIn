<script type="text/javascript" src="<?=base_url();?>assets/js/jquery.autocomplete.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/jquery.autocomplete.css" />
<style type="text/css">table {width: 60%;} td{text-align:left;} #container td input, #container td input:hover {margin-left:0;}</style>
<script>

function addDesignator()
{
	designator_html = j(".designator").html();
	j("#designator_wrapper").append("<fieldset>" +
																	"<div class='course_user_node'>Course Name: <input type='text' size='30' maxlength='100' name='name[]'> </div> "+
																	"<div class='course_user_node'>Department: <input type='text' size='30' maxlength='4' name='department[]'> </div> "+
																	"<div class='course_user_node'>Course Number: <input type='text' size='30' maxlength='5' name='number[]'> </div> "+
																	"<div class='course_user_node'>Section: <input type='text' size='30' maxlength='5' name='section[]'> </div></fieldset>");
}

</script>

<? if(isset($validation_response)){?>
	<ul><?=$validation_response?></ul>
<? } ?>

<div>

<h1><span class="headingwhite">Add a Course</span></h1>


<form method="post" action="<?= site_url("course/addNew") ?>">
		
			<div id="designator_wrapper">
			<? if(isset($names)){?>
				<?for($i = 0; $i < count($names); $i++){?>
						
							<h3 class='legendHeader'>Designator</h3>
              <fieldset>
              
              <div class="course_user_node">Course Name: <input type="text" size="30" maxlength="100" name="name[]" value='<?=$names[$i]?>'></div>
							<div class="course_user_node">Department: <input type="text" size="30" maxlength="4" name="department[]" value='<?=$departments[$i]?>'></div>
							<div class="course_user_node">Course Number: <input type="text" size="30" maxlength="5" name="number[]" value='<?=$numbers[$i]?>'></div>
							<div class="course_user_node">Section: <input type="text" size="30" maxlength="5" name="section[]" value='<?=$sections[$i]?>'></div>
							
							</fieldset>
				<? } ?>
                
               
			<?}else{?>
				<h3 class='legendHeader'>Designator</h3>
					<fieldset>
	        <div class="course_user_node">Course Name: <input type="text" size="30" maxlength="100" name="name[]"></div>
					<div class="course_user_node">Department: <input type="text" size="30" maxlength="4" name="department[]"></div>
					<div class="course_user_node">Course Number: <input type="text" size="30" maxlength="5" name="number[]"></div>
					<div class="course_user_node">Section: <input type="text" size="30" maxlength="5" name="section[]"></div>
					
					</fieldset>
						
					
			<? } ?>
        </div>
 				<div><input type='button' value='Add Additional Designator' onclick='addDesignator()'/></div>
		
		
		<fieldset>
        	<div class="course_user_node">Semester: <select name="semester">
			<option value='Spring'>Spring</option>
			<option value='Summer'>Summer</option>
			<option value='Fall'>Fall</option>
		</select>
        </div>
        <div class="course_user_node">Year:
		<select name="year">
			<option value='<?= date("Y") ?>'><?= date("Y") ?></option>
			<option value='<?= date("Y") + 1?>'><?= date("Y") + 1?></option>
			<option value='<?= date("Y") + 2?>'><?= date("Y") + 2?></option>
		</select></div>
			    <div class="course_user_node">Public:<input type="checkbox" size="30" maxlength="5" name="public">
		</div>
		<div class="course_user_node">Disable Language Parsing:<input type="checkbox" size="30" maxlength="5" name="disable_language_parsing">
</div>
		<div class="course_user_node">Show Related Words:<input type="checkbox" size="30" maxlength="5" name="show_related_words" CHECKED>	</div>
		
		
        <div class="course_user_node">Instructor: 
        <input id="instructor_auto" name="instructor" size="30" type="text" />
        </div>
        
	<div class="auto_complete" id="instructor_autocomplete">
	</div>

		<?if(isset($instructor_user) && $instructor_user>0){?>
			<div id="instructor_display">
			  <div id="instructor_name_wrapper">
					<?= $instructor_name ?> <input type='button' value='X' onclick='clearAdminForm();'/>
				</div>
			</div>
		<?}else{?>
			<div id="instructor_display">
			 	<div id="instructor_name_wrapper">
				</div>
				
			</div>
		<? } ?>
		<input type="submit" value="Save Course">
        </fieldset>
	</form>
</div>
<script type="text/javascript" src="<?=base_url();?>assets/js/peopleAutocomplete.js"></script>