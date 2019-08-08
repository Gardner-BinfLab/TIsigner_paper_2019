<?php
require_once "Controllers/CodonOpt_Controller_ViewResultSummaryUser.php";				

//Instantiate Appropriate Controller, to get Page Title
$myController = new CodonOpt_Controller_ViewResultSummaryUser();
$PageTitle = $myController->getJobDisplayTitle();

//Extract form data and feed it to the controller
$posted = false;
$save_and_return = false;
foreach($_POST as $tempKey => $tempValue) {
	switch ($tempKey) {
		case "InputSequence":
			$myController->setInputSequence($tempValue);
			break;
		case "Title":
			$myController->setTitle($tempValue);
			break;
		case "posted":	//Hidden input
			$posted = true;
			break;
		case "save_and_return":	//Hidden input
			$save_and_return = true;
			break;
	}
}
if ($posted) {					//If hidden input found: Check and Run
	$myController->checkAndSave(false);		//DO NOT return to summary
} elseif ($save_and_return) {	//If this hidden input found: Check and run
	$myController->checkAndSave(true);		//Return to summary
}
?>
<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			Add/Remove User Sequence for: <?php echo $myController->getJobDisplayTitle(); ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<link 
			rel="stylesheet" 
			type="text/css"
			href="style-showhide.css"
		/>
		<script type="text/javascript" src="javascript/tablesorter/jquery.tablesorter.js"></script>
		<script type="text/javascript">
			//Add sorting instructions
			$(document).ready(
				function() { 
					jQuery(".HideIfJavaScript").css("display","none");
					jQuery(".ShowIfJavaScript").css("display","inline");
					$("#result_summary_table").tablesorter(); 
				}
			); 
		</script>
	</head>
	<body>
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				Add/Remove User Defined Sequences for: <?php echo $myController->getJobDisplayTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<table style="width:99%;">
					<tr>
						<td><a href='viewresult.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>#entry_start1'>&lt;&lt; Go Back to Results Summary</a></td>
						<td class="middle"></td>
						<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Add/Remove User Defined Sequence','help_section.php?section=intepret_results_userdefseq'); return false;"><?php require "help-icon.php"; ?></a></td>
					</tr>
				</table>
				<br/>
				<?php
					if ( $myController->allowNewSequence() ) {			//If allowing new sequeces
						require "viewresult_done_userdefseq_add.php";	//Show Form
					} else {											//Otherwise not allowing
						echo "<div class='ErrorMessage'>You have already hit the limit of ".$myController::getMaxUserDefinedSeqCount()." User Defined Sequences. Please delete some before adding more.</div>";
					}
				?>
				<br/>
				<h3>Delete Existing User Defined Sequences</h3>
				<?php 
					$RowCount = $myController->getCurrentRowCount();
					if ($RowCount >= 1) {		//If at least one row
						echo "<div class='fixedwidth'>By default, this is sorted with the most recently added sequences first. <span class='ShowIfJavaScript'>You can sort by a particular column, by clicking on the column header. Click once to sort ascending, and twice to sort descending. You can sort by multiple columns at once, by holding down 'shift' while clicking on the desired headers in order of sort priority.</span></div>";
						echo "</div><br/>";		//end PageContentHolder
						echo "<table id='result_summary_table' class='tablesorter'>";
						echo "<thead>";
						echo "<tr>";
						echo "<th>No</th>";
						//Print Name Column
						echo "<th>";
						echo $myController->getTextCodeColumn()->getTableHeaderTitle();
						echo "</th>";
						
						//Print All Other Columns
						foreach ($myController->getTableColumns() as $HashName=>$ColumnObject) {
							echo "<th>";
							echo $ColumnObject->getTableHeaderTitle();
							echo "</th>";
							if ( $RowCount != $ColumnObject->CountDataPoints() ) {
								die ("Row Count Does Not Match across Columns: ".$RowCount." and ".$ColumnObject->CountDataPoints()." for ".$HashName);
							}
						}
						echo "<th>Delete</th>";
						echo "</tr>";		//Close Header How
						echo "</thead>";	//Close Header
						echo "<tbody>";		//Start Body

						for ($num=($RowCount-1); $num>=0; $num--) {
							echo "<tr>";
							echo "<td>".($num+1)."</td>";
							//Print Name Column
							$tempLinkKey = $myController->getLinkCodeColumn()->getDataPoint($num);
							$tempDispText = $myController->getTextCodeColumn()->getDataPoint($num);
							$tempStrHtml = "<a href='viewresultdetail.php?".$tempLinkKey."'>";
							$tempStrHtml .= $tempDispText;				//Display This Text
							$tempStrHtml .= "</a>";
							$tempStrHtml .= " (";
							$tempStrHtml .= "<a href='viewresultdetail_pdf.php?".$tempLinkKey."'>pdf</a>";
							$tempStrHtml .= ")";
							echo "<td>".$tempStrHtml."</td>";
							//Print All Other Columns			
							foreach ($myController->getTableColumns() as $HashName=>$ColumnObject) {
								echo "<td>";
								echo $ColumnObject->getDataPoint($num);
								echo "</td>";
							}
							$tempStrHtml = "<a href='viewresult_done_userdefseq_delete.php?".$tempLinkKey."'>Delete</a>";
							echo "<td>".$tempStrHtml."</td>";
							echo "</tr>";
						}
						echo "</tbody>";
						echo "</table>";
						echo "<div class='PageContentHolder'>";
					} else {		//Otherwise there are no rows
						echo "<div class='fixedwidth'>There are currently no User Defined Sequences</div>";
					}
				?>
				<br/>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>

<?php 
//Scrap: Old Display format
/*
<style type="text/css">
	tr.uds_summary_block0	{ background:#FFFFFF; }
	tr.uds_summary_block1	{ background:#F0F0F0; }
	div.uds_seq_holder		{ font-family:courier; width:450px; word-wrap:break-word; }
</style>
$RowCount = $myController->getTextCodeColumn()->CountDataPoints();
if ($RowCount >= 1) {	//If there is at least one row
	echo "<table>"; 											//open table
	for ($numA=0; $numA<$RowCount; $numA++) {
		$EvenOrOdd = $numA%2;									//Find whether even or odd
		echo 													//Print Name
			"<tr class='uds_summary_block".$EvenOrOdd."'><td>Name:</td>";
		echo "<td>&nbsp;&nbsp;</td>";							//Print Spacer
		$tempLinkKey = $myController->getLinkCodeColumn()->getDataPoint($numA);
		$tempDispText = $myController->getTextCodeColumn()->getDataPoint($numA);
		$tempStrHtml = "<a href='viewresultdetail.php?".$tempLinkKey."'>";
		$tempStrHtml .= $tempDispText;							//Display This Text
		$tempStrHtml .= "</a>";
		echo "<td colspan='3'>".$tempStrHtml."</td>";
		echo "</tr>";
		
		//Print All Other Columns
		$DataCount = count($myController->getTableColumns());	//Height of output sequence
		$OutputSeq = 											//Get Output Sequence
			$myController->getTableColumns()[($DataCount-1)]->getDataPoint($numA);
		for ($numB=0; $numB<($DataCount-1); $numB++) {
			$ColumnObject = 									//Extract Column Object
				$myController->getTableColumns()[$numB];
			echo "<tr class='uds_summary_block".$EvenOrOdd."'>";//Open Row
			echo "<td>".$ColumnObject->getCSVHeaderTitle()."</td>";
			echo "<td></td>";									//Print Spacer Cell 
			echo "<td>".$ColumnObject->getDataPoint($numA)."</td>";
			echo "<td></td>";					//Spacer (also gives background color)
			if ($numB == 0) {									//For first row, Print output seqeunce
				echo "<td rowspan='".($DataCount+1)."'><div class='uds_seq_holder'>".$OutputSeq."</div></td>";
				$IsOutputSeqPrinted = true;
			}
			echo "</tr>";										//Close row
		}
		echo													//Linebreak
			"<tr class='uds_summary_block".$EvenOrOdd."'><td colspan='4'>&nbsp;</td></tr>";						
		echo													//Delete Button
			"<tr class='uds_summary_block".$EvenOrOdd."'><td colspan='4'>"."<a href='".$tempLinkKey."'>Delete This Sequence</a>"."</td></tr>";
		echo "<tr><td>&nbsp;</td></tr>";						//Linebreak
	}
	echo "</table>";											//Close table
} else {
	echo "There are currently no User Defined Sequences.";
}
*/
?>
