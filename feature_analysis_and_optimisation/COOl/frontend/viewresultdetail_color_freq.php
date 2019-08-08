<?php 
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";
if ( isset($Ic_ColorPicker) or isset($Cc_ColorPicker) ) {	//If Color Pickers are set, do nothing
} else {													//Otherwise Both Color Pickers absent
	header( "Location: ".CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage() );
	exit;													//Redirect back to submit new job
}
?>

<h3>Output Sequence Relative Frequency Distribution</h3>
<div><?php
//Final Point
echo "Note: ";
if ( isset($Ic_ColorPicker) ) {
	echo "Colour of codons indicates the frequency for that codon, with respect to the host. ";
}
if ( isset($Cc_ColorPicker) ) {
	echo "Block colour between codons indicates the frequency of the adjacent codon pairs (I.e. the codons to the left and right), with respect to the host.";
}
?></div>
<br/>
<table>
	<tr>
		<td><b>Rarely Used (0)</b></td>
		<td class='right'><b>Frequently Used (1)</b></td>
	</tr>
	<tr>
		<td colspan="2"><img src="images/LowToHighFrequency.png" alt="Blue to Red Gradient"/></td>
	</tr>
</table>
<br/>

<!-- The codons will be in courier. The Table headers will be overridden by viewresult_header class -->
<table class="codon_color_frequency_table">
	<tr class='codon_color_frequency_table_header'>
		<td>Number:&nbsp;</td>
		<td>1&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>2&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>3&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>4&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>5&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>6&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>7&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>8&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>9&nbsp;&nbsp;</td> <td>&nbsp;</td>
		<td>10</td> <td></td>
	</tr>
	<?php 
		{	//Pre-Generate Colored Codon Distribution
			$RawCodonArray = str_split(			//Split into codons
				$myController->getOutputNucleotide(),3
			);
			$CodonList = array();
			$ContextList = array();
			$PreviousCodon = "";
			foreach ($RawCodonArray as $Codon) {	//For each codon
				//Determine what Codon will be
				$codonSpan="";	//Reset with each cycle
				if ( isset($Ic_ColorPicker) ) {
					$codonSpan .= "<td style='color:";
					$codonSpan .= $Ic_ColorPicker->getHostColorForCodon($Codon)->toHtml();
					$codonSpan .= ";' ";
					$codonSpan .= "title='";
					$codonSpan .= $Ic_ColorPicker->getTitleForCodon($Codon);
					$codonSpan .= "' ";
					$codonSpan .= ">";
					$codonSpan .= $Codon."</td>";
				} else {
					$codonSpan = "<td>".$Codon."</td>";
				}
				
				//Determine what Codon Context will be
				$contextSpan="";	//Reset with each cycle
				if ( isset($Cc_ColorPicker) ) {
					$context = $PreviousCodon.$Codon;
					if (strlen($context) == 6) {
						$contextSpan .= "<td style='background-color:";
						$contextSpan .= $Cc_ColorPicker->getHostColorForCodon($context)->toHtml();
						$contextSpan .= ";' ";
						$contextSpan .= "title='";
						$contextSpan .= $Cc_ColorPicker->getTitleForCodon($context);
						$contextSpan .= "' ";						
						$contextSpan .= ">";
						$contextSpan .= "&nbsp;</td>";
					} else {
						$contextSpan = "<td></td>";
					}
				} else {
					$contextSpan = "<td></td>";
				}
				//Store current codon as previous codon for next cycle
				$PreviousCodon = $Codon;
				
				//Store output in array
				array_push($CodonList,$codonSpan);
				array_push($ContextList,$contextSpan);
			}
			array_push($ContextList,"");	//Place 1 last empty cell to suppress warnings
			
			//Place Pre-generated arrays into tables
			//This lets us "see" the next codon context before the next codon is available
			$MaxSize = count($CodonList);
			$RowCount = 0;
			$CellCount = 0;
			echo "<tr>";	//open row
			echo "<td class='codon_color_frequency_table_header'>".(($RowCount*10)+1)."</td>";
			for ($num=0; $num<$MaxSize; $num++) {
				//Put everything together into a table
				echo $CodonList[$num].$ContextList[$num+1];
				$CellCount++;
				if ($CellCount == 10) {	//When cell count is 10
					$RowCount++;		//add 1 to row
					$CellCount = 0;
					echo "</tr><tr>";	//Add new row 
					echo "<td class='codon_color_frequency_table_header'>".(($RowCount*10)+1)."</td>";
				}
			};
			echo "</tr>";	//close row
		}
	?>
</table>
<br/>