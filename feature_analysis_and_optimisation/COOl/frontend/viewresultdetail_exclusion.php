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
function BreakRows ($input) {
	$RawArray = str_split(			//Split into codons
		$input,50
	);
	return implode("<br/>",$RawArray);
}
?>

<h3>Exclusion Sequence Fitness (Total Exclusion Bases: <?php echo $myController->getTotalExclusionBasesFound(); ?>)</h3> 
<span class="ShowIfJavaScript">
	<a href='#' id='exclusion_fitness_table_holder-show' class='showLink' onclick='showHide("exclusion_fitness_table_holder");return false;'>Show Report.</a>
</span>

<div id="exclusion_fitness_table_holder" class="HideIfJavaScript">
	<!-- Hide buttons both at top and bottom-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("exclusion_fitness_table_holder");return false;'>Hide Report.</a>
	</span>
	<?php 
		{	//If there is a table, show the table
			$ExclusionFitnessArray = $myController->getExclusionFitness();
			if ( isset($ExclusionFitnessArray) ) {
				echo 
		"<table id='exclusion_fitness_table' class='tablesorter'>
			<thead>
				<tr>
					<th>Exclusion Sequence&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
					<th>Frequency&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
				</tr>
			</thead>
			<tbody class='lucida'>";
				foreach ($ExclusionFitnessArray as $tempKey=>$tempValue) {	//For each key/value pair
					echo "<tr>";											//Generate a row
					echo "<td>".BreakRows($tempKey)."</td>";
					echo "<td>".$tempValue."</td>";
					echo "</tr>";
				}
				echo 
			"</tbody>
		</table>";
			}
		}
	?>
	<br/>
	<?php
		{	//Check whether to display highlight sequence
			$OutputNucleotideExclusion = $myController->getOutputNucleotideExclusion();
			if ( isset($OutputNucleotideExclusion) ) {
				echo"<b>Output Sequence with Exclusion Highlight</b>";
				OutputNucleotideAndHighlightCapitalLetters(
					$OutputNucleotideExclusion
				);
			}
		}
	?>

	<!-- Note: Hide Button must be within the Hidden Content to be hidden at the start-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("exclusion_fitness_table_holder");return false;'>Hide Report.</a>
	</span>
</div>
<br/>
<br/>