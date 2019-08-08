<?php
require_once "Controllers/CodonOpt_Controller_ViewResult_Ancestor.php";
$myController = new CodonOpt_Controller_ViewResult_Ancestor(null,false);
$job_end_on = $myController->getCurrentJob()->getJob_end_on();
$error_text = $myController->getCurrentJob()->getError_text();

//Instantiate Appropriate Controller, to get Page Title
if ( isset($job_end_on) ) {					//If job is done	
	if ( isset($error_text) ) {				//If there is error text
		$PageTitle = "An Error Has Occurred";
		$JavaScriptSource = "javascript/viewresult_error.js";
	} else {								//Otherwise there is no error text
		require_once "Controllers/CodonOpt_Controller_ViewResultSummaryAll.php";				
		$myController = new CodonOpt_Controller_ViewResultSummaryAll(
			$myController->getCurrentJob(),	//Reuse Current Job
			false							//Do not include output sequences
		);
		$PageTitle = "Summary of Results for: ".$myController->getJobDisplayTitle();
		$JavaScriptSource = "javascript/viewresult_done.js";
	}
} else {								//Otherwise job not yet done
	$PageTitle = "Still Processing: ".$myController->getJobDisplayTitle();
	$JavaScriptSource = "javascript/viewresult_running.js";
}
?>
<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $PageTitle; ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<link 
			rel="stylesheet" 
			type="text/css"
			href="style-showhide.css"
		/>
		<script type="text/javascript" src="javascript/showhide.js"></script>
		<script type="text/javascript" src="javascript/tablesorter/jquery.tablesorter.js"></script>
		<script type="text/javascript" src="javascript/checkForSVG.js"></script>
		<?php 
			//Old: Script for JavaScript to convert SVG to Flash
			//<script type="text/javascript" src="javascript/svgweb/src/svg.js" data-path="javascript/svgweb/src"></script>
		?>
		<script type="text/javascript" src="<?php echo $JavaScriptSource; ?>"></script>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $PageTitle; ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php //Which Subpage to include is based
					if ( isset($job_end_on) ) {				//If job is done	
						if ( isset($error_text) ) {			//If there is error text
							require "viewresult_error.php";	//Show error page
						} else {							//Otherwise there is no error text
							require "viewresult_done.php";	//show the results page
						}
					} else {								//Otherwise job not yet done
						require "viewresult_running.php";	//show this page
					}
				?>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>
