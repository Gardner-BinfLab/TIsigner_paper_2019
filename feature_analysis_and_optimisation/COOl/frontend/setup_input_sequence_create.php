<?php
/*	//Readme
	========
This page handlesthe first submission of protein sequence. It is the only page which is not job specific. After submission, a Database entry is created for that sequence, and all subsequent pages involve changing settings for that job, within the database.

*/

//Instantiate Controller and Fill variables
require_once "Controllers/CodonOpt_Controller_Setup_SequenceSubmit.php";
require_once "Controllers/CodonOpt_DAO_submit_new_job_visitor_log.php";

//NOTE: This MUST be called myController as that is that "setup_input_sequence-common_form.php" will look for
$myController = new CodonOpt_Controller_Setup_SequenceSubmit();

//Current Page which form will Post to
$myPage = $myController->getCurrentPage();
require_once "setup_input_sequence-common_preload.php";

if ($posted) {		//If hidden input found (page is posted)
} else {			//Otherwise page is NOT posted (first load)
	CodonOpt_DAO_submit_new_job_visitor_log::insertNewVisitor();
	//Add 1 to visitor counter
}
?>
<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<?php require "commonstyle-page_scripts.php"; ?>
		<?php require_once "setup_input_sequence-common_head.php"; ?>
		<title>
			Submit New Sequence
		</title>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<?php require "setup_input_sequence-common_form.php" ?>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>
