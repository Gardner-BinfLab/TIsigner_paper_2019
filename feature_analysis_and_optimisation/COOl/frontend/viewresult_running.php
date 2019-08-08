<?php
if ( isset($myController) ) {		//Check Controller
	if ( get_class ($myController) == "CodonOpt_Controller_ViewResult_Ancestor" ) {
	} else {
		die ("Error! Input Job is not CodonOpt_Controller_ViewResult_Ancestor class");
	}
} else {
	die("No valid Controller was given!");
}
?>

<span class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Waiting for Results','help_section.php?section=wait_for_results'); return false;"><?php require "help-icon.php"; ?></a></span>
<h3>Please Wait...</h3>
Your sequence is still being processed. You can bookmark this page or note down the URL to check back again later.
<br/>
<br/>
<h3 class="ShowIfJavaScript">If you leave this page open, it will refresh every minute until your sequence is ready</h3>