<?php
require_once "Controllers/CodonOpt_SVGmaker_Pareto.php";

if ( isset($myController) ) {		//Check Controller
	if ( get_class ($myController) == "CodonOpt_Controller_ViewResultSummaryAll" ) {
	} else {
		die ("Error! Input Job is not CodonOpt_Controller_ViewResultSummaryAll class");
	}
} else {
	die("No valid Controller was given!");
}

//Initiate SVG Controller
$SVGmaker = new CodonOpt_SVGmaker_Pareto($myController);
?>

<table style="width:100%;">
	<tr>
		<td>&bull;&nbsp;</td>
		<td><a href="viewresult_done_userdefseq.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>">Add/Remove</a> User Defined Sequences from the list of results.</td>
		<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Summary Graph and Table','help_section.php?section=intepret_results_summary'); return false;"><?php require "help-icon.php"; ?></a></td>
	</tr>
	<tr>
		<td>&bull;</td>
		<td><a href="viewresult_done_txt.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>">Export</a> all current results as a tab seperated file (this might take a while).</td>
		<td></td>
	</tr>
</table>
<br/>


<?php
	if ( count( $SVGmaker->getAxisList() ) >= 2 ) {		//If there are 2 or more X/Y axis
		require "viewresult_done_svg.php";				//Generate SVG graph
	}
?>
<h3>Expression Host: <span class="fontweightnormal"><?php echo $myController->getSpeciesName(); ?></span></h3>
<br/>
If the Name starts with "UDS", that indicates that it is a User Defined Sequence. <span class="ShowIfJavaScript">You can sort by a particular column, by clicking on the column header. Click once to sort ascending, and twice to sort descending. You can sort by multiple columns at once, by holding down 'shift' while clicking on the desired headers in order of sort priority.</span>
<br/>
<br/>
<table class="middle"><tbody><tr><td>
	<table id="result_summary_table" class="tablesorter">
		<thead>
			<tr>
				<?php 
					//echo "<th>No&nbsp;&nbsp;&nbsp;</th>";
					//Print Name Column
					echo "<th>";
					echo $myController->getTextCodeColumn()->getTableHeaderTitle();
					echo "</th>";
					$RowCount = $myController->getCurrentRowCount();
					//Print All Other Columns
					foreach ($myController->getTableColumns() as $HashName=>$ColumnObject) {
						echo "<th>";
						echo $ColumnObject->getTableHeaderTitle();
						echo "</th>";
						if ( $RowCount != $ColumnObject->CountDataPoints() ) {
							die ("Row Count Does Not Match across Columns: ".$RowCount." and ".$ColumnObject->CountDataPoints()." for ".$HashName);
						}
					}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				for ($num=0; $num<$RowCount; $num++) {
					echo "<tr>";
					//echo "<td>".($num+1)."</td>";
					//Print Name Column
					echo "<td>";
					$tempLinkKey = $myController->getLinkCodeColumn()->getDataPoint($num);
					$tempDispText = $myController->getTextCodeColumn()->getDataPoint($num);
					$tempStrHtml = "<a ";
					$tempStrHtml .= "target='_blank' ";
					$tempStrHtml .= "href='viewresultdetail.php?".$tempLinkKey."'>";
					$tempStrHtml .= $tempDispText;				//Display This Text
					$tempStrHtml .= "</a>";
					$tempStrHtml .= " (";
					$tempStrHtml .= "<a ";
					$tempStrHtml .= "target='_blank' ";
					$tempStrHtml .= "href='viewresultdetail_pdf.php?".$tempLinkKey."'>pdf</a>";
					$tempStrHtml .= ")";
					echo $tempStrHtml;
					echo "</td>";
					//Print All Other Columns			
					foreach ($myController->getTableColumns() as $HashName=>$ColumnObject) {
						echo "<td>";
						echo $ColumnObject->getDataPoint($num);
						echo "</td>";
					}
					echo "</tr>";
				}
			?>
		</tbody>
	</table>
</td></tr></tbody></table>
<br/>
<div class="middle">
	<?php
		//Decide whether this is an example
		$ExampleSerial = $myController->getCurrentJob()->getExample_serial();
		if (isset( $ExampleSerial ) ) {
			echo "<b>Important:</b> Examples will be deleted 1 day after generation. Please save your results as TXT or PDF.";
		} else {
			echo "<b>Important:</b> Results will be deleted 1 month after submission. Please export your results as TXT or PDF.";
		}
	?>
</div>