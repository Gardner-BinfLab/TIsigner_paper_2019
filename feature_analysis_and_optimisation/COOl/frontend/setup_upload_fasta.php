<?php
require_once "Controllers/CodonOpt_Controller_SetupUploadFasta.php";
$myController = new CodonOpt_Controller_SetupUploadFasta();

$posted = false;
foreach($_POST as $tempKey => $tempValue) {
	switch ($tempKey) {
		case "posted":	//Hidden input
			$posted = true;
			break;
	}
}

if ($posted) {		//If hidden input found: Check and Run
	$myController->ParseFastaUpload($_FILES["fileupload"]);
}

//Explanation of how File Upload Works
//If JavaScript is enabled:
//	The ".ShowIfJavaScript" elements are shown (fileName_display and fileInputButton)
//	The "file_input_div" element is given the "file_input_div" class (giving it relative position)
//	The "fileupload" element is given the fileupload style.
//	This makes it Large and Transparent, and absolute position
//	The absolute position of "fileupload", combined with the relative position of "file_input_div", puts "fileupload" on top of the "fileInputButton"
//	Hence when you click the "fileInputButton", you are in fact clicking "fileupload" which brings up the file selection interface

?>

<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $myController->getPageTitle(); ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(
				function(){
					jQuery(".ShowIfJavaScript").css("display","inline");
					jQuery("#fileupload").addClass("fileupload");			//Hide file upload
					jQuery("#file_input_div").addClass("file_input_div");	//Behind button
				}
			);
		</script>
		<style type="text/css">
			.file_input_textbox {
				width:250px;
				font-size: 17px;
				float:left;
			}
			.file_input_div {
				position: relative;
				width:100px;
				height:30px;
				overflow: hidden;
			}
			.fileupload {
				font-size:45px;						/*Large*/
				position:absolute;
				right:0px;top:0px;
				cursor:pointer;
				opacity:0;filter:alpha(opacity=0);	/*Transparent*/
				-ms-filter:"alpha(opacity=0)";
				-khtml-opacity:0;
				-moz-opacity:0;
			}
		</style>		
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<br/>
				<!-- Submit to Form Entry Start to jump to error fields on page reload-->
				<p><a href="setup_optimization.php?<?php echo $myController->getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>">&lt;&lt;&lt; Click here to return to Optimization Settings</a></p>
				<br/>
				<h3>Upload Fasta File and convert into Codon Usage Pattern</h3>
				<div>Here, you may upload a fasta sequence file, and we will convert the sequences in the file into Codon Usage Pattern values. The calculated values will appear in the "Individual Codon Usage Pattern" and "Codon Context Usage Pattern" textboxes, and you may copy and save the text for future use. The size of the fasta sequence file should not exceed <?php echo $myController::getFileSizeLimitInMB();?> megabytes.</div>

				<form action="setup_upload_fasta.php?<?php echo $myController->getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>" method="post" enctype="multipart/form-data">	
					<table>
						<tr>
							<td>
							<!-- default style is hidden -->
							<span class="ShowIfJavaScript"><input type="text" id="fileName_display" class="file_input_textbox" readonly="readonly"></span>
							</td> 
							<td>
								<div id="file_input_div">
									<span class="ShowIfJavaScript"><input id="fileInputButton" type="button" value="Select File" class="minimalstylebutton"/></span>
									<input type="file" id="fileupload" name="fileupload"
										onchange="javascript: jQuery('#fileName_display').val( this.value.split('\\').pop() );"
									/>
								</div>
							</td>
							<td><div class="ErrorMessage"><?php echo $myController->getErrorMessage() ?></div></td>
						</tr>
						<tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td colspan="3">
								<input type="submit" name="posted" value="Upload" class="minimalstylebutton"/>
							</td>
						</tr>
					</table>
				</form>
				<br/>

				<div><?php
					/* //Error Check: See how well data was parsed
					foreach ($myController->getParseFasta_Codon()->getCodonHash() as $codon=>$tempCount) {
						if ($tempCount >= 1) {
							echo $codon.": ".$tempCount."<br/>";
						}
					}
					foreach ($myController->getParseFasta_CodonPair()->getCodonHash() as $codon=>$tempCount) {
						if ($tempCount >= 1) {
							echo $codon.": ".$tempCount."<br/>";
						}
					}
					*/
				?></div>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>