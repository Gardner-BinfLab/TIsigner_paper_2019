<?php
require_once "Controllers/CodonOpt_Controller_ViewResultDetail.php";

//Initiate Controller with Translation Rules + Color Picker
$myController = new CodonOpt_Controller_ViewResultDetail(null,null,true);
$ColorPickerObject = $myController->getCc_ColorPicker();
$PageTitle = "CC Comparison Graph for ".$myController->getDetailDisplayName();

if (! isset($ColorPickerObject) ) {	//If there is no ColorPickerObject (valid job but not result)
	header(							//Go back to index
		"viewresult.php?".$myController::getEncryptIDGetKey()."=".$myController->getEncryptID()
	);	
	exit;
}

//Colour to display when no comparison is done
$NoComparisonColor = "black";
//$NoComparisonColor = "white";
$SpacerCell = "<td><div class='table_spacer_div'>&nbsp;</div></td>";
$SpacerRow = "<tr class='microrow'><td><div class='table_spacer_div'>&nbsp;</div></td></tr>";

//Various Possible Display Symbols

//Rectangles and Squares
//$DisplaySymbol = "&#8718;"
//$DisplaySymbol = "&#9607;"; //Can try 9605-9610
$DisplaySymbol = "&#9632;";

//Diamonds
//$DisplaySymbol = "&#9830;";	
//$DisplaySymbol = "&#9670;";

//Circular
//$DisplaySymbol = "&#8226;"
//$DisplaySymbol = "&#9679;";
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>
			<?php echo $PageTitle; ?>
		</title>
		<style type="text/css">
			table, tr, td {
				border-collapse:collapse;
				border:none;
				padding:0px;
				margin:0px;
			}
			tr {
				vertical-align:top;
				text-align:center;
			}
			
			.courier {
				font-family:courier;
				font-size:17px;
			}
			/*Match Courier Font Size Height with Div Width and Height*/
			.optseqpoint_outer {
				width:20px;
				height:20px;
				font-weight:bold;
				overflow:hidden;
				position:relative;
			}
			.optseqpoint_inner {
				/*
				position:absolute;
				top:50%;
				height:40px;
				font-size:40px;
				margin-left:-2px;
				margin-top:-22px;
				*/
			}
			div.table_spacer_div {
				width: 5px;
				height: 5px;
			}
			/*microrow should be smaller than table_spacer_div*/
			tr.microrow {
				font-size:4px;
			}
		</style>
	</head>
	<body>
		<h2><?php echo $PageTitle; ?></h2>
		<p style='width:700px;'>First Codon runs from top to bottom. Second Codon runs from left to right. Mouseover a point to see its details. You can save this page as a HTML.</p>
		<?php 
			echo CodonOpt_ColorPicker_Item::getColorScaleHtml();
		?>
		<br/>
		
		<table style='width:700px;'>
			<tr>
				<td class="courier" style="background-color:rgb(0,255,255)">
					<div class="optseqpoint_outer"><div class="optseqpoint_inner" style="color:white"><?php echo $DisplaySymbol; ?></div></div>
				</td>
				<?php echo $SpacerCell; ?>
				<td class="courier" style="background-color:rgb(255,0,0)">
					<div class="optseqpoint_outer"><div class="optseqpoint_inner" style="color:white"><?php echo $DisplaySymbol; ?></div></div>
				</td>
				<?php echo $SpacerCell; ?>
				<td style='text-align:left;float:left;'>The background colour indicates the Host Relative Frequency.</td>
			</tr>
			<?php echo $SpacerRow; ?>
			<tr>
				<td class="courier">
					<div class="optseqpoint_outer"><div class="optseqpoint_inner" style="color:rgb(0,255,255)"><?php echo $DisplaySymbol; ?></div></div>
				</td>
				<?php echo $SpacerCell; ?>
				<td class="courier">
					<div class="optseqpoint_outer"><div class="optseqpoint_inner" style="color:rgb(255,0,0)"><?php echo $DisplaySymbol; ?></div></div>
				</td>
				<?php echo $SpacerCell; ?>
				<td style='text-align:left;float:left;'>The block colour in the center indicates the Optimized Sequence Relative Frequency.</td>
			</tr>
			<?php echo $SpacerRow; ?>
			<tr>
				<td class="courier" style="background-color:<?php echo $NoComparisonColor; ?>"></td>
				<td style="background-color:<?php echo $NoComparisonColor; ?>"></td>
				<td style="background-color:<?php echo $NoComparisonColor; ?>"></td>
				<?php echo $SpacerCell; ?>
				<td style='text-align:left;float:left;'>This Amino Acid Pair does not occur in the Optimized Sequence. Hence no valid Codon Context comparison can be done, and the cell is <?php echo $NoComparisonColor; ?>.</td>
			</tr>
		</table>
		<br/>
		
		<table class="courier">
		<?php
			$AminoAcidList = $ColorPickerObject->getTranslationRulesHash_AAToCodon();
			{	//Draw Table Header
				$HeadLine1 = "";
				$HeadLine2 = "";
				$HeadLine3 = "";
				$HeadLine4 = "";
				foreach ($AminoAcidList as $TempKeyA => $TempListA) {
					foreach ($TempListA as $TempCodonA) {
						$HeadLine1 .= "<td>".$TempKeyA."</td>";
						$BaseList = str_split($TempCodonA,1);
						$HeadLine2 .= "<td>".$BaseList[0]."</td>";
						$HeadLine3 .= "<td>".$BaseList[1]."</td>";
						$HeadLine4 .= "<td>".$BaseList[2]."</td>";
					}
					//Space after codons for one amino acid
					//Only Line 1 will have
					$HeadLine1 .= $SpacerCell;	
					$HeadLine2 .= "<td></td>";
					$HeadLine3 .= "<td></td>";
					$HeadLine4 .= "<td></td>";
				}
				echo "<tr><td colspan='4'></td>".$HeadLine1."</tr>";
				echo "<tr><td>&nbsp;</td></tr>";
				echo "<tr><td colspan='4'></td>".$HeadLine2."</tr>";
				echo "<tr><td colspan='4'></td>".$HeadLine3."</tr>";
				echo "<tr><td colspan='4'></td>".$HeadLine4."</tr>";
				echo $SpacerRow;
			}
			
			
			//Draw Content
			$CodonHash = $ColorPickerObject->getFilteredCodonHash();
			foreach ($AminoAcidList as $TempKeyA => $TempListA) {
				if ($TempKeyA != "*") {						//Proceed only if first AA is not *
					foreach ($TempListA as $TempCodonA) {
						$HostLine = "";
						$OptSeqLine = "";
						echo "<tr>";
						echo "<td>".$TempKeyA."</td>";
						echo "<td>&nbsp;</td>";
						echo "<td>".$TempCodonA."</td>";
						echo $SpacerCell;
						foreach ($AminoAcidList as $TempKeyB => $TempListB) {
							foreach ($TempListB as $TempCodonB) {
								$CodonPair = $TempCodonA.$TempCodonB;
								$CodonItem = $CodonHash[$CodonPair];
								$HostColor = $CodonItem->getHostColor()->toHtml();
								$OptSeqColor = $CodonItem->getOptSeqColor()->toHtml();
								$TempDisplaySymbol = $DisplaySymbol;
								if ($CodonItem->getOptSeqSynonTotal() == 0) {
									$HostColor = $NoComparisonColor;
									$OptSeqColor = $NoComparisonColor;
									$TempDisplaySymbol = "";
								}
								$HostLine .= "<td";
								$HostLine .= " title='";
								$HostLine .= $CodonPair." | ";
								$HostLine .= "Host : ".$CodonItem->getHostRelFreqForDisplay();
								$HostLine .= " | ";
								$HostLine .= "Optimized Sequence: ".$CodonItem->getOptSeqRelFreqForDisplay();
								$HostLine .= "'";								
								$HostLine .= " style='background-color:".$HostColor."'";
								$HostLine .= " >";
								//Insert Comparison Object
								{	$HostLine .= "<div class='optseqpoint_outer'>";
									$HostLine .= "<div class='optseqpoint_inner' ";
									$HostLine .= "style='color:".$OptSeqColor."' ";
									$HostLine .= ">".$TempDisplaySymbol."</div></div>";
								}
								$HostLine .= "</td>";
							}
							$HostLine .= "<td></td>";	//Space after codons for amino acid
						}
						echo $HostLine;
						echo "</tr>";
					}
					echo $SpacerRow;
				}
				
			}
		?>
		</table>
	</body>
</html>