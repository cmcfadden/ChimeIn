<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>ChimeIn
        </title>
        <link rel="stylesheet" href="<?=base_url();?>assets/css/jquery.mobile-1.1.0.min.css" />
	  <style>
            /* App custom styles */
        </style>
		<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-1.7.2.min.js"></script>
        <script src="<?=base_url();?>assets/js/jquery.mobile-1.1.0.min.js">
        </script>
		<script src="<?=base_url();?>assets/js/jquery-ui-1.8.20.custom.min.js">
        </script>
		<script src="<?=site_url("assets/js/questionFormSubmission.js")?>"></script>
	</head>
    <body>
		<div data-role="page" id="page1" data-theme="c">
			
				<div data-theme="c" data-role="header">
					<h3>
						ChimeIn
					</h3>
					<a data-role="button" data-inline="true" data-transition="slideup" data-theme="c" href="<?=site_url("/welcome/about")?>" class="ui-btn-right" data-icon="info" data-iconpos="left">
							About
					</a>
				</div>
				<div data-role="content">

				<ul data-role="listview">
			<? if(count($enrolled_courses) > 0){?>
				<? foreach ($enrolled_courses as $course){?>
					<li data-icon="arrow-r"><a href="<?=base_url()?>course/view/<?=$course->COURSE_ID?>" data-transition="slide"> <?=$course->DEPARTMENT.$course->NUMBER?> : <?=$course->FRIENDLY_TITLE?></a></li>
				<? } ?>
			<? }else{ ?>
				<li>You are not currently enrolled in any courses.</li>
			<? } ?>

				</ul>
			</div>
		</div>

</body>
</html>