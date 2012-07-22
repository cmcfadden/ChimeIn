<h1><span class="headingwhite">Home</span></h1>
	<? if($this->isEmployed) { ?>
<h3 class="legendHeader">My Courses</h3>
	<fieldset>
		<?=  anchor("course/add", "Add New Course"); ?>
		<? if(count($instructed_courses) > 0){?>
			<ul>
			<? foreach ($instructed_courses as $course){?>
				<li>
					<?= anchor("course/edit/".$course->COURSE_ID, join($course->designatorStringArray, "/"))?> : 
					<?=join($course->friendlyTitleArray, "/")?>
				</li>
			<? } ?>
			</ul>
	<? }else{ ?>
		<div>You are not currently instructing any courses. Click 'Add New Course' above to create one.</div>
	<? } ?>
</fieldset>
<? } ?>

<h3 class="legendHeader">Courses I Am Enrolled In</h3>
	<fieldset>

		<? if(count($enrolled_courses) > 0 && $enrolled_courses != false){?>
			<ul>
			<? foreach ($enrolled_courses as $course){ ?>
				<li><?= anchor("course/view/".$course->COURSE_ID, $course->DEPARTMENT.$course->NUMBER)?> : <?=$course->FRIENDLY_TITLE?></li>
			<? } ?>
			</ul>
		<? }else{ ?>
			<div>You are not currently enrolled in any courses.</div>
		<? } ?>
	</fieldset>

	<p></p>


