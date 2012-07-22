<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 4.01//EN" 
		"http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>ChimeIn : University of Minnesota</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/css/screen.css" />
	<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-1.4.4.js"></script>
	<script type="text/javascript" src="<?=base_url();?>assets/js/jquery-ui-1.8.9.custom.min.js"></script>
	<script type="text/javascript" src="<?=base_url()?>/assets/js/cellphone.js"></script>
	
	<script type="text/javascript" src="../../assets/fancybox/jquery.fancybox-1.3.1.pack.js"></script>
	<script type="text/javascript" src="../../assets/fancybox/jquery.easing-1.3.pack.js"></script>
	<link rel="stylesheet" href="../../assets/fancybox/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />

	<script>
	
		var j = jQuery.noConflict();
		
		j(document).ready(function(){
			j("input[type=text], textarea").focus(function(){
				value = this.value;
				if(value == "Type your question here...")
				{
					this.value = "";
				}
			});
		});
	</script>
	
</head>

<body id="section">

	<div id="templatecontainer">
	
		<div id="campus_links" class="campus_links">
			<p class="jump"><a href="#mainnav"><img src="https://assets.cla.umn.edu/common/images/trans.gif" alt="Jump to main navigation." width="10" height="5" /></a><a href="#maincontent"><img src="https://assets.cla.umn.edu/common/images/trans.gif" alt="Jump to main content." width="10" height="5" /></a></p>
			<ul>
				<li class="campus">Campuses :</li>
				<li><a href="http://umn.edu/">Twin Cities</a></li>
				<li><a href="http://www.crk.umn.edu/">Crookston</a></li>
				<li><a href="http://www.d.umn.edu/">Duluth</a></li>
				<li><a href="http://morris.umn.edu/">Morris</a></li>
				<li><a href="http://r.umn.edu/">Rochester</a></li>
				<li><a href="http://umn.edu/campuses.php">Other Locations</a></li>
			</ul>
		</div>
	
		<div class="leftprint">
			<img src="https://assets.cla.umn.edu/common/images/smMwdmk.gif" alt="University of Minnesota" width="216" height="55" hspace="10" align="left" />
		</div>
		<div class="rightprint">
        <strong>CLA-OIT Software &amp; Web Development</strong><br />
			4help@cla.umn.edu<br />
			612-625-3479
		</div>
		
		<div class="grid_7" id="header">
			<a href="http://umn.edu/"><img src="https://assets.cla.umn.edu/common/images/logo_uofm_D2D.gif" alt="Go to the U of M home page." width="320" height="62" /></a>
		</div>
		
		<div class="grid_5" id="search_area">
			<div id="search_nav">
				<a href="http://myu.umn.edu/" id="btn_myu">myU</a>
				<a href="http://onestop.umn.edu/" id="btn_onestop">OneStop</a>
			</div>
			<br class="clearabove" />
			<div class="search">
				<form action="http://search.umn.edu/tc/" method="get" name="gsearch" id="gsearch" title="Search Websites and People">
					<input class="right" type="text" id="search_field" name="q" value="Search Websites and People" title="Search text" onfocus="if (this.value == 'Search Websites and People') { this.value = ''; }" />
					<input type="image" class="search_btn" value="Search" alt="Submit Search" src="https://assets.cla.umn.edu/common/images/search_button.gif" />
				</form>			
			</div>
			<br class="clearabove" />
		</div>

	<div id="banner">
	<a href="<?= base_url() ?>"><img src="/assets/img/chimeinlogo.png" alt="ChimeIn" id="chimeInLogo" /></a>
	<div id="rightFloatBox">
	<div id="breadcrumb"><?=$breadcrumb ?></div>
	<div id="helpDiv"><a id="helpLink" href="https://sites.google.com/a/umn.edu/cla-support/applications/chimein">Help</a></div>
	<? if($this->isLoggedIn) { ?>
	<div id="userInfo">
		<p id="personName"><?=personIdToName($this->perId)?> <a href="<?=site_url("course/switchUsers")?>"><img src="/assets/img/switchUsers.png" alt="Switch Users" title="Switch Users" /></a> <a href="<?=site_url("course/logout")?>"><img src="/assets/img/logout.png" alt="Logout" title="Logout" /></a></p>
		<p id="cellPhone"><?=$this->phone?$this->phone:"No Cell Phone"?> <a id="cell_phone_edit" href="#cell_entry">[edit]</a></p>
	</div>
	<? } ?>
	</div>
    </div>
	<div id="container">
		<?= $content ?>	
		
		<div id="cell_entry_wrapper" style="display:none;">
			<div id='cell_entry'>
				<p id="cell_error" style="display:none;">Please, enter data.</p>
				<? if($this->phone) { ?>
					<div id="cell_update">Your account is currently tied to phone number <?=$this->phone?>. <br/> If you'd like to update it, enter a new number.</div>
				<? } else {?>
				<? } ?>
				<p>We'll send a message to your phone to confirm the number.</p>
				<p>Phone Number: <input name=cellNumber id=cellNumber></p>
				<p><input type=button name="submit" value="Submit" onClick="submitPhoneNumber()"></p>
			</div>
		</div>
		<div id='cell_confirm_wrapper' style="display:none;">
			<div id="cell_confirm">
				<div>We've sent a message to your phone.  <br/> Enter the number you receive in the box below and click confirm.</div>
				<p><input type=hidden name="hiddenNumber" id="hiddenNumber"></p>
				<p><input name="secretNumber" id="secretNumber"></p>
				<p><input type=button value="Confirm" onClick="confirmNumber()"></p>
			</div>
		</div>
		
		
			<br class="clearabove" />
		</div>
			<div class="clearabove"></div>

		<div class="grid_12" id="unit_footer">
			<a href="http://cla.umn.edu/" class="clalogo"><img src="https://assets.cla.umn.edu/common/images/logo_cla.gif" alt="Go to the CLA home page." /></a>
			<ul class="unit_footer_links">
				<li>Address: 110 <a href="http://www1.umn.edu/twincities/maps/AndH/">Anderson Hall</a>, 257 19th <acronym class="acronym_border" title="Avenue South">Ave S</acronym>, Minneapolis, MN 55455</li>
				<li>Email: <a href="mailto:4help@cla.umn.edu">4help@cla.umn.edu</a></li>
			</ul>
		</div>
		<div class="clearabove"></div>
		<div class="grid_7 alpha" id="footer_inner">
			<ul class="copyright">
				<li>&copy; 2012 Regents of the University of Minnesota. All rights reserved.</li>
				<li>The University of Minnesota is an equal opportunity educator and employer</li>
				<li>Last modified on <!-- #BeginDate format:Am1 -->August 10, 2010<!-- #EndDate --></li>
			</ul>
		</div>
		<div class="grid_5 omega" id="footer_right">
			<ul class="footer_links">
				<li>Twin Cities Campus:</li>
				<li><a href="http://umn.edu/pts/">Parking &amp; Transportation</a></li>
				<li><a href="http://umn.edu/twincities/maps/">Maps &amp; Directions</a></li>
			</ul>
			<br class="clearabove" />
			<ul class="footer_links">
				<li><a href="http://www.directory.umn.edu/">Directories</a></li>
				<li><a href="http://umn.edu/twincities/contact/">Contact U of M</a></li>
				<li><a href="http://privacy.umn.edu/">Privacy</a></li>
			</ul>
			<br class="clearabove" />
		</div>
	
	</div>

</body>

</html>
