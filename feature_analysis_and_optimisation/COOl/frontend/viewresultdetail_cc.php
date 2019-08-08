<?php
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";
if (! isset($Cc_ColorPicker) ) {	//If there is no current controller
	header( "Location: ".CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage() );
	exit;							//Redirect back to submit new job
}
?>
<h3>Codon Context Frequency and Colour Table</h3> <span class="ShowIfJavaScript"><a href='#' id='cc_frequency_table_holder-show' class='showLink' onclick='showHide("cc_frequency_table_holder");return false;'>Show Table.</a></span>

<div id="cc_frequency_table_holder" class="HideIfJavaScript">
	<span class="ShowIfJavaScript"><a href='#' class='hideLink' onclick='showHide("cc_frequency_table_holder");return false;'>Hide Table.</a></span>
	<span class="ShowIfJavaScript"><br/><br/>You can sort by multiple columns at once, by holding down 'shift' while clicking on the headers.</span>
	<table id="cc_frequency_table" class="tablesorter">
		<thead>
			<tr>
				<th>Amino<br/>Acid&nbsp;&nbsp;&nbsp;&nbsp;</th>
				<th>Codon<br/>Pair&nbsp;&nbsp;&nbsp;&nbsp;</th>
				<!-- <th>Host<br/>Usage<br/>Count&nbsp;&nbsp;&nbsp;&nbsp;</th> -->
				<!-- <th>Synonymous<br/>Total&nbsp;&nbsp;&nbsp;&nbsp;</th> -->
				<th>Host<br/>Relative<br/>Frequency&nbsp;&nbsp;&nbsp;&nbsp;</th>
				<?php
					if ( $myController->isInputSeqNucelotide() ) {			//If show comparison
						echo "<th>Query<br/>Usage<br/>Count&nbsp;&nbsp;&nbsp;&nbsp;</th>";
					}		
				?>
				<th>Optimized<br/>Usage<br/>Count&nbsp;&nbsp;&nbsp;&nbsp;</th>
				<th>Optimized<br/>Synonymous&nbsp;&nbsp;&nbsp;&nbsp;<br/>Total</th>
				<th>Optimized<br/>Relative<br/>Frequency&nbsp;&nbsp;&nbsp;&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				{	//Decide whether to generate the color picker
					$AllLines = $Cc_ColorPicker->getFilteredCodonHash();
					usort(
						$AllLines,
						function($a, $b) {					//Sort according to amino acid
							return strcmp($a->getAAbase(), $b->getAAbase());
						}
					);
					foreach ($AllLines as $tempItem) {
						echo "<tr>";
						echo "<td>".$tempItem->getAAbase()."</td>";
						echo "<td>".$tempItem->getCodon()."</td>";
						//echo "<td>".$tempItem->getHostRawCount()."</td>";
						//echo "<td>".$tempItem->getHostSynonTotal()."</td>";
						echo "<td";
						echo " style='background-color:".$tempItem->getHostColor()->tohtml().";color:white;'";
						echo ">".$tempItem->getHostRelFreqForDisplay()."</td>";
						if ( $myController->isInputSeqNucelotide() ) {			//If show comparison
							echo "<td>".$tempItem->getQuerySeqUsageCount()."</td>";
						}
						echo "<td>".$tempItem->getOptSeqRawCount()."</td>";
						echo "<td>".$tempItem->getOptSeqSynonTotal()."</td>";
						echo "<td";
						echo " style='background-color:".$tempItem->getOptSeqColor()->tohtml().";color:white;'";
						echo ">".$tempItem->getOptSeqRelFreqForDisplay()."</td>";
						echo "</tr>";
					}
				}
			?>
		</tbody>
	</table>
	<span class="ShowIfJavaScript"><a href='#' class='hideLink' onclick='showHide("cc_frequency_table_holder");return false;'>Hide Table.</a></span>
</div>
<br/>
<br/>