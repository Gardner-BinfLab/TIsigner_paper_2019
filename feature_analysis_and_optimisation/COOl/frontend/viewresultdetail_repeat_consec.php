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

<h3>Consecutive Repeat Fitness (Total Consecutive Repeat Bases: <?php echo $myController->getOutputBaseCount_repeat_consec(); ?>)</h3> 
<span class="ShowIfJavaScript">
	<a href='#' id='repeat_consec_fitness_report_holder-show' class='showLink' onclick='showHide("repeat_consec_fitness_report_holder");return false;'>Show Report.</a>
</span>

<div id="repeat_consec_fitness_report_holder" class="HideIfJavaScript">
	<!-- Hide buttons both at top and bottom-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("repeat_consec_fitness_report_holder");return false;'>Hide Report.</a>
	</span>
	<br/>
	
	<!-- <b>Repeat Settings</b> -->
	<ul>
	<?php
		if ( $myController->getRepeat_consec_mode() ) {
			echo "<li>Remove Consecutive Repeats with motif length of ";
			echo $myController->getRepeat_consec_length();
			echo " repeated ";
			echo $myController->getRepeat_consec_count();
			echo " or more times</li>";
		}
	?>
	</ul>
	<br/>
	
	
	<?php
		{	//Decide whether to show repeat sequence
			$RepeatFitness = $myController->getOutput_sequence_repeat_consec();
			if ( isset($RepeatFitness) ) {
				echo "<b>Output Sequence with Consecutive Repeat Highlight</b>";
				OutputNucleotideAndHighlightCapitalLetters($RepeatFitness);
			}
		}
	?>	
	
	<!-- Note: Hide Button must be within the Hidden Content to be hidden at the start-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("repeat_consec_fitness_report_holder");return false;'>Hide Report.</a>
	</span>
</div>
<br/>
<br/>