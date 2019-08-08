<?php
// For this page to work for species with large gene list
// See setup_select_genes.php for more details

require_once "Controllers/CodonOpt_Controller_SetupOptimization.php";

$myController = new CodonOpt_Controller_SetupOptimization();

{	//Go through inputs and save them
	$save_and_continue = false;
	$fasta_upload = false;
	$optimize_gc_mode = 0;	//Assume default value is false (which will be the case if not detected)
	foreach($_POST as $tempKey => $tempValue) {
		switch ($tempKey) {
			case "optimize_ic":
				$myController->setOptimize_Ic($tempValue);
				break;
				
			case "optimize_cc":
				$myController->setOptimize_Cc($tempValue);
				break;

			case "optimize_cai":
				$myController->setOptimize_Cai($tempValue);
				break;
				
			case "optimize_hidden_stop_codon":
				$myController->setOptimize_hidden_stop_codon($tempValue);
				break;

			case "optimize_gc_mode":
				$optimize_gc_mode = $tempValue;
				break;

			case "optimize_gc_target":
				$myController->setOptimize_gc_target($tempValue);
				break;

			case "SpeciesName":
				$myController->setSpeciesSerial($tempValue);
				break;

			case "use_custom_species":
				$myController->setUse_custom_species($tempValue);
				break;	
				
			case "ic_frequency":
				$myController->setIc_frequency($tempValue);
				break;
		
			case "cc_frequency":
				$myController->setCc_frequency($tempValue);
				break;
			
			case "TranslationRules_NCBI_code":
				$myController->setTranslationRules_NCBI_code($tempValue);
				break;
			
			case "save_and_continue":	//Hidden input
				$save_and_continue = true;
				break;
				
			case "fasta_upload":		//Hidden input
				$fasta_upload = true;
				break;				
		}
	}
	if ($save_and_continue) {						//If hidden input found
		$myController->setOptimize_gc_mode($optimize_gc_mode);
		$myController->checkAndSave();				//Check and Save And Continue
	} elseif ($fasta_upload) {						//If this is Fasta Upload
		$myController->setOptimize_gc_mode($optimize_gc_mode);
		$myController->saveValidAndUploadFasta();	//Check and Run	
	}
}	

//This function is meant to check if there is an error, and if there is, place the error_start2 anchor on the first error, but not later ones. It will also echo the Input if there is any. Basically we feed all error messages through this method, and the first error message that has content will also include the page anchor.
$ErrorAnchorAvailable = true;
function CheckErrorAnchor($Input) {
	if ( isset ($Input) ) {								//Check input isset
		if (strlen($Input) >= 1) {						//Check input has minimum length
			global $ErrorAnchorAvailable;
			if ($ErrorAnchorAvailable) {				//If anchor still available
				echo "<a id='error_start2'></a>";		//Print Anchor
				$ErrorAnchorAvailable = false;			//Anchor no longer available
			}
			echo $Input;								//Place Input
		}
	}
}

//This function Generates Radio buttons for below
function GenerateOptimizeMethodRadioButtons($name,$selectedButton) {
	$output = "";
	for ($i=0; $i<3; $i++) {
		$output .= '<td><input type="radio" name="';
		$output .= $name;
		$output .= '" value="';
		$output .= $i;
		$output .= '" id="';
		$output .= $name.$i;
		$output .= '" onclick="recalculateShowHide()"';
		if ($i == $selectedButton) {
			$output .= ' checked ';
		}
		$output .= ' /></td>';
	}
	return $output;
}
?>

<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			<?php echo $myController->getPageTitle(); ?>
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<script type="text/javascript" src="javascript/setup_optimization.js"></script>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<!-- Submit to Error Start to jump to error fields on page reload-->
				<form action="setup_optimization.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>#error_start2" method="Post" name="setupoptimizationform" >
				<!-- hidden submit button to act as default when enter is hit -->
				<div style="width:0px; overflow: hidden;"><input type="submit" name="save_and_continue" value="Save and Continue" /></div>		
				<table>
					<tbody>
						<tr>
							<td colspan="2">
								<div class="SequenceTextWidth"> <!-- Div to force width so that OptimizationMethodErrorMessage does not shift-->
									<h3>Select Optimization Criteria For Current Job:</h3>
								</div>
							</td>
							<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Optimization Settings','help_section.php?section=setup_2_method'); return false;"><?php require "help-icon.php"; ?></a></td>
						</tr>
						<tr>				
							<td colspan="2">
								<table class="tablesorter"><tbody>
									<tr>
										<td></td>
										<td>Ignore</td>
										<td>Maximize</td>
										<td>Minimize</td>
									</tr>
									<tr>
										<td>Individual Codon Usage</td>
										<?php
											echo GenerateOptimizeMethodRadioButtons( "optimize_ic" ,  $myController->getOptimize_ic() );
										?>
									</tr>
									<tr>
										<td>Codon Context</td>
										<?php
											echo GenerateOptimizeMethodRadioButtons( "optimize_cc" ,  $myController->getOptimize_cc() );
										?>
									</tr>
									<tr>
										<td>Codon Adaptation Index</td>
										<?php
											echo GenerateOptimizeMethodRadioButtons( "optimize_cai" ,  $myController->getOptimize_cai() );
										?>
									</tr>	
									<tr>
										<td>Number of Hidden Stop Codons</td>
										<?php
											echo GenerateOptimizeMethodRadioButtons( "optimize_hidden_stop_codon" ,  $myController->getOptimize_hidden_stop_codon() );
										?>
									</tr>								
								</tbody></table>
							</td>
							
							<td><div class="ErrorMessage_RightColumn" id="OptimizationMethodErrorMessage"><?php
								CheckErrorAnchor(
									$myController->getOptimizationMethodErrorMsg()
								);
							?></div></td>
						</tr>

						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td colspan="2">
								<h3>GC Content</h3>
							</td>
							<td></td>
						</tr>
						<tr>
							<td><input type="checkbox" name="optimize_gc_mode" value="1" <?php
								if ( $myController->getOptimize_gc_mode() ) {
									echo "checked";
								}
							?> /></td>
							<td>Optimize GC content with target percentage: 
								<input type="text" name="optimize_gc_target" maxlength="5" size="5" 
									value="<?php echo htmlentities($myController->getOptimize_gc_target()); ?>"
								/>%
								<br/><br/>
							</td>					
							<td><div class="ErrorMessage_RightColumn" id="OptimizationGCErrorMessage"><?php
								CheckErrorAnchor(
									$myController->getOptimizeGCErrorMsg()
								);
							?></div></td>
						</tr>

						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td colspan="2">
								<b>Choose Either To:</b>
							</td>
							<td></td>
						</tr>
						<tr>
							<td>
								<input type="radio" name="use_custom_species" value="0" id="use_custom_species0" onclick="recalculateShowHide()"
									<?php
										if (! $myController->getUse_custom_species() )
										{ echo " checked "; }
									?>
								/>
							</td>
							<td colspan="2">
								<h3>Use Inbuilt Expression Host</h3>						
							</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								<span class="instructions">Selecting an inbuilt expression host will also automatically use the corresponding Translation Rules for that host. You may use all or only a subset of the genes, from your desired target expression host. Selection of which genes you want to use can be done on the next page.</span>
							</td>
						</tr>
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td></td>
							<td colspan="2">
								Target Expression Host: 
								<select name="SpeciesName" >
									<?php
										//This seqment should query the Controller and populate the dropdown box
										$SpecNameList = $myController::getSpeciesNameList();
										foreach ($SpecNameList as $SpeciesDTO) {
											print '<option value="';
											print $SpeciesDTO->getSerial();
											print '" ';
											if ($SpeciesDTO->getSerial() == $myController->getSpeciesSerial()) {
												print 'selected ';
											}
											print '>';
											print $SpeciesDTO->getName();
											print '</option>';	
										}
									?>
								</select>
							</td>
						</tr>
						
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td colspan="2">
								<b>Or:</b>
							</td>
							<td></td>
						</tr>			
						<tr>
							<td>
								<input type="radio" name="use_custom_species" value="1" id="use_custom_species1" onclick="recalculateShowHide()"
									<?php
										if ( $myController->getUse_custom_species() )
										{ echo " checked "; }
									?>
								/>					
							</td>
							<td colspan="2">
								<h3>Input Custom Codon Usage Pattern Values</h3>
							</td>
						</tr>
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
					</tbody>
					
					
					<tbody id="custom_species_translation_rules">
						<tr>
							<td></td>
							<td colspan="2">
								<h3>Select Translation Rules:</h3>
							</td>
						</tr>
						<tr>
							<td></td>
							<td colspan="2">
								<select name="TranslationRules_NCBI_code" title='This translation rules you select here, applies to the expression host, where the protein will be produced. E.g. lets say you are trying to express a Blepharisma nucleotide sequence in Salmonella bongori (which uses Standard translation rules). Then here, you should select "Standard".'>
									<?php
										//This seqment should query the Controller and populate the dropdown box
										$TransRuleList = $myController::getTranslationRulesList();
										foreach ($TransRuleList as $TransRuleDTO) {
											print '<option value="';
											print $TransRuleDTO->getNCBI_code();
											print '" ';
											if ($TransRuleDTO->getNCBI_code() == $myController->getTranslationRules_NCBI_code()) {
												print 'selected ';
											}
											print '>';
											print $TransRuleDTO->getName();
											print '</option>';	
										}
									?>
								</select>
							</td>
						</tr>
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
						<tr>
							<td></td>
							<td colspan="2">
								<input type="submit" name="fasta_upload" value="Convert Fasta Into Codon Usage Pattern Values" class="minimalstylebutton" />
							</td>
						</tr>	
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
					</tbody>
					
					
					<tbody id="custom_species_individual_codon">
						<tr>
							<td></td>
							<td colspan="2">
								<h3><span id="ic_optional">Optional:&nbsp;</span>Individual Codon Usage Pattern <span class="h3normalfont">(<a href="sample-EColi.All.IndividualCodon.txt" target="_blank">Sample Format</a>)</span></h3>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<textarea name="ic_frequency" id="ic_frequency" rows="5" cols="50" onchange="recalculateShowHide()" 
									maxLength="<?php echo CodonOpt_Controller_SetupOptimization::getIc_frequencyMaxLength(); ?>" 
									title="Individual Codon Frequency values take the general form of a list of 3 base nucleotide sequences on seperate lines. Within the same line, each nucleotide sequence is followed by a space or tab, and then an integer stating the frequency of that sequence. Please only use integers, and not fractions or decimals."
								><?php echo htmlentities($myController->getIc_frequency()); ?></textarea>
							</td>
							<td><div class="ErrorMessage_RightColumn" id="ic_frequencyErrorMessage"><?php
								CheckErrorAnchor(
									$myController->getIc_frequencyErrorMsg()
								);
							?></div></td>
						</tr>
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
					</tbody>
					
					
					<tbody id="custom_species_codon_context">
						<tr>
							<td></td>
							<td colspan="2">
								<h3><span id="cc_optional">Optional:&nbsp;</span>Codon Context Usage Pattern <span class="h3normalfont">(<a href="sample-EColi.All.CodonContext.txt" target="_blank">Sample Format</a>)</span></h3>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<textarea name="cc_frequency" id="cc_frequency" rows="5" cols="50" onchange="recalculateShowHide()"
									maxLength="<?php echo CodonOpt_Controller_SetupOptimization::getCc_frequencyMaxLength(); ?>"
									title="Codon Context Frequency values take the general form of a list of 6 base nucleotide sequences on seperate lines. Within the same line, each nucleotide sequence is followed by a space or tab, and then an integer stating the frequency of that sequence. Please only use integers, and not fractions or decimals."
								><?php echo htmlentities($myController->getCc_frequency()); ?></textarea>
							</td>
							<td><div class="ErrorMessage_RightColumn" id="cc_frequencyErrorMessage"><?php
								CheckErrorAnchor(
									$myController->getCc_frequencyErrorMsg()
								);
							?></div></td>
						</tr>				
						<!-- Line Break --><tr><td colspan="3">&nbsp;</td></tr>
					</tbody>
					
					<tbody>
						<tr>
							<td colspan="3">
								<input type="submit" name="save_and_continue" value="Save and Continue" class="minimalstylebutton" />
							</td>
						</tr>
					</tbody>
				</table>
				</form>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>