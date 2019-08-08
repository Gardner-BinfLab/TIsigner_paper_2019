<?php
require_once "Controllers/CodonOpt_Controller_SetupExclusion.php"; 

$myController = new CodonOpt_Controller_SetupExclusion();
{	//Check Submission
	$posted = false;
	$repeat_consec_mode = 0;
	$repeat_allmotif_mode = 0;
	foreach($_POST as $tempKey => $tempValue) {
		switch ($tempKey) {
			case "ExclusionSequence":
				$myController->setExclusionSequence($tempValue);
				break;
			
			case "repeat_consec_mode":
				$repeat_consec_mode = $tempValue;
				break;
				
			case "repeat_consec_length":
				$myController->setRepeat_consec_length($tempValue);
				break;
				
			case "repeat_consec_count":
				$myController->setRepeat_consec_count($tempValue);
				break;
				
			case "repeat_allmotif_mode":
				$repeat_allmotif_mode = $tempValue;
				break;
				
			case "repeat_allmotif_length":
				$myController->setRepeat_allmotif_length($tempValue);
				break;
				
			case "repeat_allmotif_count":
				$myController->setRepeat_allmotif_count($tempValue);
				break;	
				
			case "posted":	//Hidden input
				$posted = true;
				break;
		}
	}
	if ($posted) {			//If hidden input found: Check and Run
		$myController->setRepeat_consec_mode($repeat_consec_mode);
		$myController->setRepeat_allmotif_mode($repeat_allmotif_mode);
		$myController->checkAndSave();
	}
}

//This function is meant to check if there is an error, and if there is, place the error_start4 anchor on the first error, but not later ones. It will also echo the Input if there is any. Basically we feed all error messages through this method, and the first error message that has content will also include the page anchor.
$ErrorAnchorAvailable = true;
function CheckErrorAnchor($Input) {
	if ( isset ($Input) ) {								//Check input isset
		if (strlen($Input) >= 1) {						//Check input has minimum length
			global $ErrorAnchorAvailable;
			if ($ErrorAnchorAvailable) {				//If anchor still available
				echo "<a id='error_start4'></a>";		//Print Anchor
				$ErrorAnchorAvailable = false;			//Anchor no longer available
			}
			echo $Input;								//Place Input
		}
	}
}
?>

<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $myController->getPageTitle(); ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<link 
			rel="stylesheet" 
			type="text/css"
			href="style-dropdown-menu.css"
		/>
		<script type="text/javascript" src="javascript/setup_exclusion.js"></script>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<br/>
				<!-- Submit to Form Entry Start to jump to error fields on page reload-->
				<form action="setup_exclusion.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>#error_start4" method="Post" name="sequenceexclusionform" >
				<input type="hidden" name="posted" value="true"/>
				<table>
					<tr>
						<td>
							<h3>Optional: Exclusion Sequences</h3>
						</td>
						<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Motif Settings','help_section.php?section=setup_4_exclusion'); return false;"><?php require "help-icon.php"; ?></a></td>
					</tr>
					<tr>
						<td colspan="2">					
							Enter a comma separated list of nucleotide sequences that should be excluded from the final output (e.g. Restriction Sites). Each exclusion sequence within the list should contain at least 4 unambiguous bases (ACGTU). Exclusion sequences may also contain ambiguous bases Y,R,W,S,K,M,D,V,H,B,N.
							<br/><br/>
							Note that sequences entered here will only be excluded on the forward strand and not the complementary strand, unless the sequence is palindromic (forward and complement are the same). E.g. if you enter ATGATG, the algorithm will not automatically exclude the complementary sequence CATCAT. In order to exclude both ATGATG and CATCAT, enter each one seperately. <span class="ShowIfJavaScript">You can select common Restriction Enzyme Sites and Translation Initiation sites (e.g. Kozak), from the menu below. Clicking on a site name, will add its sequence to the textbox.</span>
							<br/><br/>
							<b>Warning:</b> a sequence with too few unambiguous bases (ACGTU) will result in too many matches and thus will reduce the codon optimization algorithm's effectiveness. <span class="ShowIfJavaScript"><br/>&nbsp;</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<a id="1"></a>
							<div class="ShowIfJavaScript"><div id="dropdown_menu">
<ul><li><a href='#1'>AatII-AvrII</a><ul><li><a href='#1' onclick='AddExclusionSequence("GACGTC")'>AatII</a></li><li><a href='#1' onclick='AddExclusionSequence("GGTACC")'>Acc65I</a></li><li><a href='#1' onclick='AddExclusionSequence("GTATAC,GTCGAC")'>AccI</a></li><li><a href='#1' onclick='AddExclusionSequence("AACGTT")'>AclI</a></li><li><a href='#1' onclick='AddExclusionSequence("AGCGCT")'>AfeI</a></li><li><a href='#1' onclick='AddExclusionSequence("CTTAAG")'>AflII</a></li><li><a href='#1' onclick='AddExclusionSequence("ACCGGT")'>AgeI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGGCCC")'>ApaI</a></li><li><a href='#1' onclick='AddExclusionSequence("GTGCAC")'>ApaLI</a></li><li><a href='#1' onclick='AddExclusionSequence("AAATTT,GAATTC")'>ApoI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGCGCGCC")'>AscI</a></li><li><a href='#1' onclick='AddExclusionSequence("ATTAAT")'>AseI</a></li><li><a href='#1' onclick='AddExclusionSequence("GCGATCGC")'>AsiSI</a></li><li><a href='#1' onclick='AddExclusionSequence("CCTAGG")'>AvrII</a></li></ul></li>
<li><a href='#1'>BamHI-BtgI</a><ul><li><a href='#1' onclick='AddExclusionSequence("GGATCC")'>BamHI</a></li><li><a href='#1' onclick='AddExclusionSequence("TGATCA")'>BclI</a></li><li><a href='#1' onclick='AddExclusionSequence("AGATCT")'>BglII</a></li><li><a href='#1' onclick='AddExclusionSequence("GTGCAC,GGGCCC")'>Bme1580I</a></li><li><a href='#1' onclick='AddExclusionSequence("GCTAGC")'>BmtI</a></li><li><a href='#1' onclick='AddExclusionSequence("GACGTC,GGCGCC")'>BsaHI</a></li><li><a href='#1' onclick='AddExclusionSequence("CGATCG,CGGCCG")'>BsiEI</a></li><li><a href='#1' onclick='AddExclusionSequence("CGTACG")'>BsiWI</a></li><li><a href='#1' onclick='AddExclusionSequence("TCCGGA")'>BspEI</a></li><li><a href='#1' onclick='AddExclusionSequence("TCATGA")'>BspHI</a></li><li><a href='#1' onclick='AddExclusionSequence("TGTACA")'>BsrGI</a></li><li><a href='#1' onclick='AddExclusionSequence("GCGCGC")'>BssHII</a></li><li><a href='#1' onclick='AddExclusionSequence("TTCGAA")'>BstBI</a></li><li><a href='#1' onclick='AddExclusionSequence("GTATAC")'>BstZ17I</a></li><li><a href='#1' onclick='AddExclusionSequence("CCATGG,CCGCGG")'>BtgI</a></li></ul></li>
<li><a href='#1'>ClaI-FspI</a><ul><li><a href='#1' onclick='AddExclusionSequence("ATCGAT")'>ClaI</a></li><li><a href='#1' onclick='AddExclusionSequence("TTTAAA")'>DraI</a></li><li><a href='#1' onclick='AddExclusionSequence("TGGCCA,CGGCCG")'>EaeI</a></li><li><a href='#1' onclick='AddExclusionSequence("CGGCCG")'>EagI</a></li><li><a href='#1' onclick='AddExclusionSequence("GAATTC")'>EcoRI</a></li><li><a href='#1' onclick='AddExclusionSequence("GATATC")'>EcoRV</a></li><li><a href='#1' onclick='AddExclusionSequence("GGCCGGCC")'>FseI</a></li><li><a href='#1' onclick='AddExclusionSequence("TGCGCA")'>FspI</a></li></ul></li>
<li><a href='#1'>HaeII-MspA1I</a><ul><li><a href='#1' onclick='AddExclusionSequence("AGCGCT,GGCGCC")'>HaeII</a></li><li><a href='#1' onclick='AddExclusionSequence("GTTAAC,GTCGAC")'>HincII</a></li><li><a href='#1' onclick='AddExclusionSequence("AAGCTT")'>HindIII</a></li><li><a href='#1' onclick='AddExclusionSequence("GTTAAC")'>HpaI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGCGCC")'>KasI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGTACC")'>KpnI</a></li><li><a href='#1' onclick='AddExclusionSequence("CAATTG")'>MfeI</a></li><li><a href='#1' onclick='AddExclusionSequence("ACGCGT")'>MluI</a></li><li><a href='#1' onclick='AddExclusionSequence("TGGCCA")'>MscI</a></li><li><a href='#1' onclick='AddExclusionSequence("CAGCTG,CCGCGG")'>MspA1I</a></li></ul></li>
<li><a href='#1'>NaeI-NspI</a><ul><li><a href='#1' onclick='AddExclusionSequence("GCCGGC")'>NaeI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGCGCC")'>NarI</a></li><li><a href='#1' onclick='AddExclusionSequence("CCATGG")'>NcoI</a></li><li><a href='#1' onclick='AddExclusionSequence("CATATG")'>NdeI</a></li><li><a href='#1' onclick='AddExclusionSequence("GCCGGC")'>NgoMIV</a></li><li><a href='#1' onclick='AddExclusionSequence("GCTAGC")'>NheI</a></li><li><a href='#1' onclick='AddExclusionSequence("GCGGCCGC")'>NotI</a></li><li><a href='#1' onclick='AddExclusionSequence("TCGCGA")'>NruI</a></li><li><a href='#1' onclick='AddExclusionSequence("ATGCAT")'>NsiI</a></li><li><a href='#1' onclick='AddExclusionSequence("ACATGT,GCATGC")'>NspI</a></li></ul></li>
<li><a href='#1'>PacI-PvuII</a><ul><li><a href='#1' onclick='AddExclusionSequence("TTAATTAA")'>PacI</a></li><li><a href='#1' onclick='AddExclusionSequence("ACATGT")'>PciI</a></li><li><a href='#1' onclick='AddExclusionSequence("GTTTAAAC")'>PmeI</a></li><li><a href='#1' onclick='AddExclusionSequence("CACGTG")'>PmlI</a></li><li><a href='#1' onclick='AddExclusionSequence("TTATAA")'>PsiI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGGCCC")'>PspOMI</a></li><li><a href='#1' onclick='AddExclusionSequence("CTGCAG")'>PstI</a></li><li><a href='#1' onclick='AddExclusionSequence("CGATCG")'>PvuI</a></li><li><a href='#1' onclick='AddExclusionSequence("CAGCTG")'>PvuII</a></li></ul></li>
<li><a href='#1'>SacI-SwaI</a><ul><li><a href='#1' onclick='AddExclusionSequence("GAGCTC")'>SacI</a></li><li><a href='#1' onclick='AddExclusionSequence("CCGCGG")'>SacII</a></li><li><a href='#1' onclick='AddExclusionSequence("GTCGAC")'>SalI</a></li><li><a href='#1' onclick='AddExclusionSequence("CCTGCAGG")'>SbfI</a></li><li><a href='#1' onclick='AddExclusionSequence("AGTACT")'>ScaI</a></li><li><a href='#1' onclick='AddExclusionSequence("CTATAG,CTGCAG")'>SfcI</a></li><li><a href='#1' onclick='AddExclusionSequence("GGCGCC")'>SfoI</a></li><li><a href='#1' onclick='AddExclusionSequence("CACCGGTG,CGCCGGCG")'>SgrAI</a></li><li><a href='#1' onclick='AddExclusionSequence("CCCGGG")'>SmaI</a></li><li><a href='#1' onclick='AddExclusionSequence("CTTAAG,CTCGAG")'>SmlI</a></li><li><a href='#1' onclick='AddExclusionSequence("TACGTA")'>SnaBI</a></li><li><a href='#1' onclick='AddExclusionSequence("ACTAGT")'>SpeI</a></li><li><a href='#1' onclick='AddExclusionSequence("GCATGC")'>SphI</a></li><li><a href='#1' onclick='AddExclusionSequence("AATATT")'>SspI</a></li><li><a href='#1' onclick='AddExclusionSequence("AGGCCT")'>StuI</a></li><li><a href='#1' onclick='AddExclusionSequence("ATTTAAAT")'>SwaI</a></li></ul></li>
<li><a href='#1'>XbaI-XmaI</a><ul><li><a href='#1' onclick='AddExclusionSequence("TCTAGA")'>XbaI</a></li><li><a href='#1' onclick='AddExclusionSequence("CTCGAG")'>XhoI</a></li><li><a href='#1' onclick='AddExclusionSequence("CCCGGG")'>XmaI</a></li></ul></li>
<li><a href='#1'>Translation Initiation</a><ul><li><a href='#1' onclick='AddExclusionSequence("GGRGG")'>Shine-Dalgarno mRNA</a></li><li><a href='#1' onclick='AddExclusionSequence("VGNVATG,VANVATG")'>Kozak (Vertebrate)</a></li><li><a href='#1' onclick='AddExclusionSequence("HAHMATGD")'>Kozak (S. cerevisiae)</a></li><li><a href='#1' onclick='AddExclusionSequence("MAHVATG")'>Kozak (Drosophila spp.)</a></li></ul></li></ul>
								
							</div></div>
						</td>
					</tr>	
					<tr>
						<td>
							<textarea name="ExclusionSequence" id="ExclusionSequence" maxlength="<?php echo CodonOpt_Controller_SetupExclusion::getMaxExclusionTextLength() ?>" rows="10" class="GeneralInputWidth"><?php echo htmlentities($myController->getExclusionSequence()); ?></textarea>
						</td>
						<td><div class="ErrorMessage_RightColumn" id="ExclusionSequenceErrorMessage">
							<?php CheckErrorAnchor( $myController->getExclusionSequenceErrorMsg() ); ?>
						</div></td>
					</tr>
				</table>	
				<br/>

				<table>
					<tr>
						<td colspan="4">
							<h3>Optional: Remove Motifs Which are Repeated Consecutively</h3>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="repeat_consec_mode" value="1" <?php
								if ( $myController->getRepeat_consec_mode() ) {
									echo "checked";
								}
							?> />
						</td>
						<td colspan="3"><b>Enable Consecutive Repeat Removal:</b></td>
					</tr>
					<tr>
						<td></td>
						<td>Length of Nucleotide Motif</td>
						<td>
							<input type="text" name="repeat_consec_length" maxlength="2" size="1" 
								value="<?php echo htmlentities($myController->getRepeat_consec_length()); ?>"
							/>
						</td>
						<td><div class="ErrorMessage"  style="width:420px;">
							<?php CheckErrorAnchor( $myController->getRepeat_consec_lengthErrorMsg() ); ?>
						</div></td>
					</tr>
					<tr>
						<td></td>
						<td>Minimum Number of Instances before removal</td>
						<td> 
							<input type="text" name="repeat_consec_count" maxlength="2" size="1" 
								value="<?php echo htmlentities($myController->getRepeat_consec_count()); ?>"
							/> 
						</td>
						<td><div class="ErrorMessage">
							<?php CheckErrorAnchor( $myController->getRepeat_consec_countErrorMsg() ); ?>
						</div></td>
					</tr>
					<!-- Line Break --><tr><td colspan="4">&nbsp;</td></tr>	

					<tr>
						<td colspan="4">
							<h3>Optional: Remove Motifs Which Occur Repeatedly Regardless of Location </h3>
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="repeat_allmotif_mode" value="1" <?php
								if ( $myController->getRepeat_allmotif_mode() ) {
									echo "checked";
								}
							?> />
						</td>
						<td colspan="3"><b>Enable Repeated Motif Removal:</b></td>
					</tr>
					<tr>
						<td></td>
						<td colspan="3">
							<b>Warning:</b> Enabling this option will substantially increase the computation time (50%-80%) required to complete the optimization run. The "Length of Nucleotide Motif" has a minimum value of 7.
						</td>
					</tr>
					<tr>
						<td></td>
						<td>Length of Nucleotide Motif</td>
						<td>
							<input type="text" name="repeat_allmotif_length" maxlength="2" size="1" 
								value="<?php echo htmlentities($myController->getRepeat_allmotif_length()); ?>"
							/>
						</td>
						<td><div class="ErrorMessage">
							<?php CheckErrorAnchor( $myController->getRepeat_allmotif_lengthErrorMsg() ); ?>
						</div></td>
					</tr>
					<tr>
						<td></td>
						<td>Minimum Number of Instances before removal</td>
						<td> 
							<input type="text" name="repeat_allmotif_count" maxlength="2" size="1" 
								value="<?php echo htmlentities($myController->getRepeat_allmotif_count()); ?>"
							/> 
						</td>
						<td><div class="ErrorMessage">
							<?php CheckErrorAnchor( $myController->getRepeat_allmotif_countErrorMsg() ); ?>
						</div></td>
					</tr>
					<!-- Line Break --><tr><td colspan="4">&nbsp;</td></tr>

					<tr>
						<td colspan="2">
							<input type="submit" value="Save and Continue" class='minimalstylebutton' />
						</td>
					</tr>
				</table>
				</form>
				<br/>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>

	</body>
</html>

