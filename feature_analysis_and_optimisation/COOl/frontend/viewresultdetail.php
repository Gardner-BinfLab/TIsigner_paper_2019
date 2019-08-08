<?php
require_once "Controllers/CodonOpt_Controller_ViewResultDetail.php";

//Initiate Controller with Translation Rules + Color Picker
$myController = new CodonOpt_Controller_ViewResultDetail(null,null,true);
$Ic_ColorPicker = $myController->getIc_ColorPicker();
$Cc_ColorPicker = $myController->getCc_ColorPicker();

$PrintTitle = "".$myController->getDetailDisplayName();

//This function highlights capital letters and is used for output sequence with: exclusion, repeats and hidden stop codons
function OutputNucleotideAndHighlightCapitalLetters($InputSeq) {	//Convert into Table
	echo "<table class='lucida'>";
	$NucPerLine = 30;	//Nucleotides Per Line
	$LineCounter = 0;
	$tempLinesArray = str_split($InputSeq,$NucPerLine);
	foreach ($tempLinesArray as $tempLine) {
		echo "<tr>";
		echo "<td>".($LineCounter*$NucPerLine+1)."&nbsp;</td>";
		echo "<td>";
		$tempBaseArray = str_split( $tempLine , 1 );
		foreach ($tempBaseArray as $tempBase) {
			if ( ctype_upper($tempBase) ) {
				echo "<b class='highlight'>".$tempBase."</b>";
			} else {
				echo $tempBase;
			}
		}
		echo "</td>";
		echo "</tr>";
		$LineCounter++;
	}
	echo "</table>";
}

?>
<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $PrintTitle;?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<link 
			rel="stylesheet" 
			type="text/css"
			href="style-showhide.css"
		/>
		<script type="text/javascript" src="javascript/viewresultdetail.js"></script> 
		<script type="text/javascript" src="javascript/showhide.js"></script> 
		<script type="text/javascript" src="javascript/tablesorter/jquery.tablesorter.js"></script>
		<script type="text/javascript" src="javascript/checkForSVG.js"></script>
		<?php 
			//Old: Script for JavaScript to convert SVG to Flash
			//<script type="text/javascript" src="javascript/svgweb/src/svg.js" data-path="javascript/svgweb/src"></script>
		?>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $PrintTitle; ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<table style="width:100%;">
					<tr>
						<td><a href="viewresult.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>">&lt;&lt;Back to Results List</a></td>
						<td style="text-align:center;"></td>
						<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Detailed Results','help_section.php?section=intepret_results_details'); return false;"><?php require "help-icon.php"; ?></a></td>
					</tr>
				</table>
				<br/>

				<h3>Summary Report&nbsp;&nbsp;<span class="h3normalfont">(<a target="_blank" href="viewresultdetail_pdf.php?<?php echo $myController->getLinkKey(); ?>">Download this result as a PDF.</a>)</span></h3>
				<table class="tablesorter">
				<?php
				{	//Show fitness values
					if ( $myController->isInputSeqNucelotide() ) {			//If show comparison
						echo "<tr>";
						echo "<td>Input Sequence GC content</td>";
						echo "<td>".$myController->getInputSequenceGCpercent()."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td>&nbsp;</td>";
						echo "</tr>";
					}
					if ( $myController->showGC_target() ) {					//If GC Target
						echo "<tr>";
						echo "<td>User Specified Target GC content</td>";
						echo "<td>".$myController->getGC_target()."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td>&nbsp;</td>";
						echo "</tr>";
					}	
					
					echo "<tr>";
					echo "<td>Output Sequence GC content</td>";
					echo "<td>".$myController->getOuputNucleotideGCpercent()."</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "</tr>";
					
					if ( $myController->showGC_target() ) {					//If GC Target
						echo "<tr>";
						echo "<td>Derived GC content fitness</td>";
						echo "<td>".$myController->getGc_content_fitness()."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td>&nbsp;</td>";
						echo "</tr>";
					}
					
					if ( $myController->showOptimizationParameter( $myController->getOptimize_ic() ) ) {
						echo "<tr>";
						echo "<td>Individual Codon Fitness (".$myController->getOptimizationParameterType( $myController->getOptimize_ic() ).")</td>";
						echo "<td>".$myController->getIc_fitness()."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td>";
						echo "</td>";
						echo "</tr>";
					}
					
					if ( $myController->showOptimizationParameter( $myController->getOptimize_cc() ) ) {
						echo "<tr>";
						echo "<td>Codon Context Fitness (".$myController->getOptimizationParameterType( $myController->getOptimize_cc() ).")</td>";
						echo "<td>".$myController->getCc_fitness()."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td><a target='_blank' href='viewresultdetailgraph_cc.php?".$myController->getLinkKey()."'>Open CC Comparison Graph in new window</a></td>";
						echo "</tr>";
					}
					
					if ( $myController->showOptimizationParameter( $myController->getOptimize_cai() ) ) {
						echo "<tr>";
						echo "<td>Codon Adaptation Index (".$myController->getOptimizationParameterType( $myController->getOptimize_cai() ).")</td>";
						echo "<td>".$myController->getCai_fitness()."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td>&nbsp;</td>";
						echo "</tr>";
					}
				}
				?>
				</table>
				<br/>

				<h3>Input Sequence As Protein&nbsp;&nbsp;<span class="h3normalfont">(<span class="HideIfJavaScript">Click on textbox and press "Ctrl-a" to select all</span><span class="ShowIfJavaScript">Click on textbox to select all</span>)</span></h3>
				<textarea rows="5" cols="90" id="raw_output_protein" onfocus="this.select()" onMouseUp="return false" readonly><?php echo $myController->getOutputAsProtein(); ?></textarea>
				<br/>
				<br/>

				<h3>Optimized Nucleotide Sequence&nbsp;&nbsp;<span class="h3normalfont">(<span class="HideIfJavaScript">Click on textbox and press "Ctrl-a" to select all</span><span class="ShowIfJavaScript">Click on textbox to select all</span>)</span></h3>
				<textarea rows="5" cols="90" id="raw_output_nucleotide" onfocus="this.select()" onMouseUp="return false" readonly><?php echo $myController->getOutputNucleotide(); ?></textarea>
				<br/>
				<br/>

				<?php
					//Decide whether to generate the IC color picker table
					if ( isset($Ic_ColorPicker) or isset($Cc_ColorPicker) ) {
						require "viewresultdetail_color_freq.php";
					}

					if ( $myController->showExclusionReport() ) {
						require "viewresultdetail_exclusion.php";
					}
					
					//Decide whether to generate Consecutive Repeat Report
					if ( $myController->showRepeat_consec_report() ) {
						require "viewresultdetail_repeat_consec.php";
					}
					
					//Decide whether to generate Repeated Motif Report
					if ( $myController->showRepeat_allmotif_report() ) {
						require "viewresultdetail_repeat_allmotif.php";
					}
					
					//Decide whether to generate Stop_codon_motifs
					if ( $myController->showOptimizationParameter( $myController->getOptimize_hidden_stop_codon() ) ) {
						require "viewresultdetail_hidden_stop_codon.php";
					}
					
					//Decide whether to show Host Vs Optimized Codon Relative Frequency Comparison
					if ( isset($Ic_ColorPicker) ) {
						require "viewresultdetail_ic_HostVsOptimizedCodonRelFreqComparison.php";
					}
					
					//Decide whether to include in/out Nucleotide Comparison
					if ( $myController->isInputSeqNucelotide() ) {			//If show comparison
						require "viewresultdetail_in_out_nucleotide_comparison.php";	//Show in/out Nucleotide comparison
					}
				?>
				
				<!-- Details about the species -->
				<h3>Expression Host: <span class="h3normalfont"><?php echo $myController->getSpeciesName(); ?></span></h3>
				<br/>

				<?php
					//Decide whether to generate the IC color picker table
					if ( isset($Ic_ColorPicker) ) {
						require "viewresultdetail_ic.php";
					}
					
					//Decide whether to generate the CC color picker table
					if ( isset($Cc_ColorPicker) ) {
						require "viewresultdetail_cc.php";
					}
					
					//Decide whether this is an example
					$ExampleSerial = $myController->getCurrentJob()->getExample_serial();
					if (isset( $ExampleSerial ) ) {
						echo "<b>Important:</b> Examples will be deleted 1 day after generation. Please save your results as a PDF.";
					} else {
						echo "<b>Important:</b> Results will be deleted 1 month after submission. Please save your results as a PDF.";
					}
				?>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>