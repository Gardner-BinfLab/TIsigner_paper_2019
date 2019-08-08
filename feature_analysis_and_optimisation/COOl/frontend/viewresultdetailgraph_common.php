<?php
if (! isset($ColorPickerObject) ) {	//If there is no ColorPickerObject
	header(							//Go back to index
		"Location: index.php"
	);	
	exit;
}

$HostColor = "red";
$OptSeqColor = "blue";
$MaxWidth = 300;
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>
			<?php echo $PageTitle; ?>
		</title>
		<style type="text/css">
			.courier {
				font-family:courier;
			}
			table, tr, td
			{
				border-collapse:collapse;
				border:none;
				padding:0px;
				margin:0px;
			}
			tr {
				vertical-align:top;
				text-align:left;
			}
			td.barcell {
				border-right:1px solid #000000;
				border-left:1px solid #000000;
			}
		</style>
	</head>
	<body>
		<h2><?php echo $PageTitle; ?></h2>
		<p>Mouseover a bar to see its details. You can save this page as a HTML.</p>
		<table class="courier">
			<tr>
				<td><div style='background-color:<?php echo $HostColor;?>;'>&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
				<td>&nbsp;</td>
				<td>Host Relative Frequency</td>				
			</tr>
			<tr>
				<td><div style='background-color:<?php echo $OptSeqColor;?>;'>&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
				<td>&nbsp;</td>
				<td>Optimized Relative Frequency</td>
			</tr>
		</table>
		<br/>
		
		<table class="courier">
			<tr style='text-align:right;'>
				<td colspan='3'><b>0</b></td>
				<td class='barcell'><div style='width:<?php echo $MaxWidth; ?>px'><b>1</b></div></td>
			</tr>
			<?php 
				{	//Decide whether to generate the color picker
					$AllLines = $ColorPickerObject->getFilteredCodonHash();
					usort(
						$AllLines,
						function($a, $b) {					//Sort according to amino acid
							return strcmp($a->getAAbase(), $b->getAAbase());
						}
					);
					$PreviousBase = null;
					foreach ($AllLines as $tempItem) {
						$AAbase = $tempItem->getAAbase();
						if (	(strlen($AAbase) == 2) &&		//If there are 2 bases (codon context)
								(substr($AAbase,0,1) == "*")	//And it starts with '*' (not valid codon pair)
						) {										//Do nothing
						} else {								//Otherwise it is codon or valid codon pair
							if ($AAbase == $PreviousBase) {		//If same as previous base, do nothing
							} else {							//Otherwise new base, insert line break
								echo "<tr>";
								echo "<td colspan='3'>&nbsp;</td>";
								echo "<td class='barcell'>&nbsp;</td>";
								echo "</tr>";
								$PreviousBase = $AAbase;
							}
							echo "<tr>";
							echo "<td>".$AAbase."</td>";
							echo "<td>&nbsp;</td>";
							echo "<td>".$tempItem->getCodon()."</td>";
							echo "<td class='barcell'>";
							echo "<div";
							echo " title='Host ".$tempItem->getCodon().": ".$tempItem->getHostRelFreqForDisplay()."'";
							echo " style='background-color:".$HostColor.";height:7px;width:".floor($tempItem->getHostRelFreqForDisplay()*$MaxWidth)."px'";
							echo ">&nbsp;</div>";
							echo "<div";
							echo " title='Optimized Sequence ".$tempItem->getCodon().": ".$tempItem->getOptSeqRelFreqForDisplay()."'";
							echo " style='background-color:".$OptSeqColor.";height:7px;width:".floor($tempItem->getOptSeqRelFreqForDisplay()*$MaxWidth)."px'";
							echo ">&nbsp;</div>";						
							echo "</td>";
							echo "</tr>\n";
						}
					}
				}
			?>
		</table>
	</body>
</html>