<?php
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";
if ( isset($myController) ) { 			//If controller is set
	if ( 								//If controller is NOT View Results
		get_class ($myController) != "CodonOpt_Controller_ViewResultDetail" 
	) {
		header( "Location: ".CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage() );
		exit;							//Redirect back to submit new job
	}
} else {								//Otherwise controller not set
	header( "Location: ".CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage() );
	exit;								//Redirect back to submit new job
}
?>

<h3>Hidden Stop Codons (<?php echo $myController->getOptimizationParameterType( $myController->getOptimize_hidden_stop_codon() ); ?>imize. Total Found: <?php echo $myController->getStop_codon_motifs(); ?>)</h3> 
<span class="ShowIfJavaScript">
	<a href='#' id='hidden_stop_codon_report_holder-show' class='showLink' onclick='showHide("hidden_stop_codon_report_holder");return false;'>Show Report.</a>
</span>

<div id="hidden_stop_codon_report_holder" class="HideIfJavaScript">
	<!-- Hide buttons both at top and bottom-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("hidden_stop_codon_report_holder");return false;'>Hide Report.</a>
	</span>
	<br/>
	<?php
		{	//Check whether to display highlight sequence
			$OutputNucleotideHiddenStopCodon = $myController->getOutputNucleotideHiddenStopCodon();
			if ( isset($OutputNucleotideHiddenStopCodon) ) {
				echo "<b>Output Sequence with Hidden Stop Codon Highlight</b>";
				OutputNucleotideAndHighlightCapitalLetters(
					$OutputNucleotideHiddenStopCodon
				);
			} else {
				echo "<b>No Hidden Stop Codons found.</b>";
			}
		}
	?>
	<br/>
	
	<!-- Note: Hide Button must be within the Hidden Content to be hidden at the start-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("hidden_stop_codon_report_holder");return false;'>Hide Report.</a>
	</span>
</div>
<br/>
<br/>