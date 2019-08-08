<?php
require_once "Controllers/CodonOpt_Controller_SetupStartRun.php"; 

$myController = new CodonOpt_Controller_SetupStartRun();
$posted = false;
foreach($_POST as $tempKey => $tempValue) {
	switch ($tempKey) {
		case "EmailAddress": 
			$myController->setEmailAddress($tempValue);	
			break;
		case "posted":	//Hidden input
			$posted = true;
			break;
	}
}
if ($posted) {			//If hidden input found: Check and Run
	$myController->checkAndStartNewRun();
}
//var_dump($CurrJob);
//echo "<br/>";
//$CurrJob->setUser_email("hello world!");
//var_dump($CurrJob);
//echo "<br/>";
?>

<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $myController->getPageTitle(); ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<br/>
				<!-- Submit to Form Entry Start to jump to error fields on page reload-->
				<form action="setup_start_run.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>" method="Post" name="start_run_form" >
				<input type="hidden" name="posted" value="true"/>
				<table>
					<tr>
						<td>
							<h3>Optional: Enter your e-mail</h3>
						</td>
						<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Submit Job','help_section.php?section=setup_5_start'); return false;"><?php require "help-icon.php"; ?></a></td>
					</tr>
					<tr>
						<td colspan="2">
							If you wish, you may enter your e-mail, and we will send you a notification when results are ready. Note that sometimes our notification e-mail is placed inside the junk folder, so remember to check there.
						</td>
					</tr>			
					<tr>
						<td>
							<input type="text" name="EmailAddress" class="GeneralInputWidth" maxlength="<?php echo CodonOpt_Controller_SetupStartRun::getMaxEmailLength(); ?>" value="<?php echo htmlentities($myController->getEmailAddress()); ?>"/>
						</td>
						<td><div class="ErrorMessage_RightColumn" id="EmailErrorMessage"><?php echo $myController->getEmailAddressErrorMsg(); ?></div></td>
					</tr>
					
					<!-- Line Break --><tr><td colspan="2">&nbsp;</td></tr>	
					<tr><td colspan="2"><b>Important:</b> Once you start running a job, you cannot make any further changes to its setup options.</td></tr>
					<!-- Line Break --><tr><td colspan="2">&nbsp;</td></tr>				
					<tr>
						<td colspan="2">
							<input type="submit" value="Save and Submit Job" class="minimalstylebutton" />
						</td>
					</tr>
				</table>
				</form>
			<br/>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>