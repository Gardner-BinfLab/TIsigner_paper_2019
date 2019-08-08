<?php
require_once "Controllers/CodonOpt_Controller_Setup_SequenceEdit.php";

//NOTE: This MUST be called myController as that is that "setup_input_sequence-common_form.php" will look for
$myController = new CodonOpt_Controller_Setup_SequenceEdit();
$myPage = $myController->getCurrentPage()."?".CodonOpt_Controller_Ancestor_User_Job::getEncryptIDGetKey()."=".$myController->getEncryptID();
require_once "setup_input_sequence-common_preload.php";
?>

<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $myController->getPageTitle(); ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<?php require_once "setup_input_sequence-common_head.php"; ?>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<?php require "setup_input_sequence-common_form.php"; ?>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>