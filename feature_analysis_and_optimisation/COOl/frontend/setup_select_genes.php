<?php
// This page has a huge amount of data being moved from server to browser and back
// For this page to work for species with large gene list
// Ensure that mysql/my.cnf OR mysql/bin/my.ini has sufficient max_allowed_packet, and log size. E.g.:
//	max_allowed_packet = 16M
//	innodb_log_file_size = 16M
//
// And that php has sufficient max_execution_time and memory_limit
	ini_set('max_execution_time', 300);
	ini_set('memory_limit', '512M');
// If the commands above do not work, PHP might be running in safe mode
// To get them to work, either disable safemode, or change the values in php/php.ini
//
// Additionally, in order to handle large numbers of genes in a hash, you need to raise the max_input_vars for PHP
// Put the following values inside ".htaccess" or php.ini
//	php_value max_input_vars 100000
//	php_value suhosin.get.max_vars 100000
//	php_value suhosin.post.max_vars 100000
//	php_value suhosin.request.max_vars 100000
//
// (Note that for .htaccess to work, you need the following in Apache Configuration, for the correct directory)
//	AllowOverride All

require_once "Controllers/CodonOpt_Controller_SetupSelectGenes.php";

$myController = new CodonOpt_Controller_SetupSelectGenes();
$update_gene_list = false;
$gene_sort_box = "";
$all_genes_list = array();
$posted = false;

foreach($_POST as $tempKey => $tempValue) {
	switch ($tempKey) {
		case "gene_sort_box":
			$gene_sort_box = $tempValue;
			break;

		case "all_genes":		
			$all_genes_list = $tempValue;
			break;

		case "update_gene_list":	//Hidden input
			$update_gene_list = true;
			break;
			
		case "posted":				//Hidden input
			$posted = true;
			break;
	}
}
;;;;;;if ($update_gene_list) {	//If Update Gene Sort List
	$myController->setGene_sort_box($gene_sort_box);
	$myController->CheckAndUpdateGeneSort();	//Check And Save
} elseif ($posted) {			//If hidden input found: Check and Run
	$myController->setGene_select_hash($all_genes_list);
	$myController->CheckAndSaveGeneSelection();	//Check And Save
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
			href="style-showhide.css"
		/>
		<script type="text/javascript" src="javascript/showhide.js"></script> 
		<?php require_once "setup_select_genes-javascript.php"; ?>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				<?php echo $myController->getPageTitle(); ?>
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<?php echo $myController->getHeaderCode(); ?>
				<br/>
				<!-- Submit to Form Entry Start to jump to error fields on page reload-->
				<table style='width:99%;'>
					<tr>
						<td>
							<h3>Selecting Genes for: <?php echo $myController->getSpeciesName(); ?></h3>
						</td>
						<td class="right"><a href="help.php#blast" target="_blank" onclick="showGreyPopup('Select Genes','help_section.php?section=setup_3_select_gene'); return false;"><?php require "help-icon.php"; ?></a></td>
					</tr>
					<tr>
						<td colspan="2">
							You can change the selected expression host in the <a href="setup_optimization.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>">previous page</a>.
						</td>
					</tr>
				</table>
				<br/>
				
				
				<!-- Quick Selection List Form -->
				<form action="setup_select_genes.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>#gene_sort_header" method="Post" name="sort_genes_form" >
					<input type="hidden" name="update_gene_list" value="true"/>		
					<table>
						<tbody>
							<tr class="bottom">
								<td>
									<h3 id="gene_sort_header">Optional: Input Quick Gene Selection List</h3>
								</td>
								<td class="right">
									<span class="HideIfJavaScript"><a target="_blank" href="<?php echo $myController->get_heg_link(); ?>">Sample List</a></span>	
									<span class="ShowIfJavaScript"><input type='button' value='Load Sample List' name='load_heg_button' class='minimalstylebutton' onclick='load_heg_into_box()' title='WARNING: This will overwrite your current list, if any.' />	</span>					
								</td>					
							</tr>
							<tr>
								<td colspan="2" class="instructions">
									<div class="GeneralInputPlusErrorTotalWidth">You may enter a list of Locus Tags, and this website will automatically select those which it recognizes, and deselect everything else that is not on the list. Locus Tags which are not recognized will be included, and will be reported in the <i>Unrecognized Gene ID List</i> section below. Tip: You can copy and paste selected cells from excel, into this textbox.</div>
								</td>
							</tr>
							<tr>
								<td>
									<textarea name="gene_sort_box" id="gene_sort_box" maxlength="<?php echo CodonOpt_Controller_SetupSelectGenes::getGeneSortMaxLength(); ?>" rows="5" class="GeneralInputWidth"><?php echo htmlentities($myController->getGene_sort_box()); ?></textarea>
								</td>
								<td><div class="ErrorMessage_RightColumn" id="GeneSortBoxErrorMessage"><?php echo $myController->getGeneSortBoxErrorMsg(); ?></div></td>
							</tr>			
							<tr>
								<td class="right">
									<div id="GeneSortList_SubmitButtonDiv" ><input type="submit" value="Select Genes from Text Input" class='minimalstylebutton' /></div>
								</td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</form>
				<br/>
				

				<!-- Selection Form -->
				<form action="setup_select_genes.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>#gene_select_title" method="Post" name="select_genes_form" >
					<input type="hidden" name="posted" value="true"/>
					<select multiple size="20" name="all_genes[]" id="all_genes" style="display:none" >
						<?php
							//Lists that will be used later
							$SelectedOptionsList = array();
							$AvailableOptionsList = array();

							//Function to format Gene as option for visible input (only used in non-JavaScript)
							function ConvertToNonJavaScriptOption($InputGene,$Selected) {
								$output = "<option ";
								if ($Selected) {
									$output .= "selected ";
								}
								$output .= "value='".$InputGene->getAccess_id()."' ";
								$output .= "title='".$InputGene->getDescription()."' ";
								$output .= " >";
								$output .= GenerateDisplayName($InputGene);
								$output .= "</option>";
								return $output;
							}
							
							//Function to derive Displayed Gene Name within select list (to standardize for hidden and visible inputs)
							function GenerateDisplayName($InputGene) {
								return $InputGene->getAccess_id().": ".$InputGene->getName();
							}

							//Sorted Gene List
							$OptionCount = 0;
							$SelectedHash = $myController->getGene_select_hash();	//Get hash of selected genes
							foreach ($myController->getSorted_gene_list() as $gene) {
								$IsSelected = array_key_exists						//Whether it is selected or not
									($gene->getAccess_id(),$SelectedHash);
								$tempStr = ConvertToNonJavaScriptOption($gene,$IsSelected);
								if ($IsSelected) {
									array_push( $SelectedOptionsList,$tempStr );
								} else {
									array_push( $AvailableOptionsList,$tempStr );
								}
								echo $tempStr;
							}
							//Unsorted genes list
							foreach ($myController->getUnsorted_gene_list() as $gene) {
								$IsSelected = array_key_exists						//Whether it is selected or not
									($gene->getAccess_id(),$SelectedHash);
								$tempStr = ConvertToNonJavaScriptOption($gene,$IsSelected);
								if ($IsSelected) {
									array_push( $SelectedOptionsList,$tempStr );
								} else {
									array_push( $AvailableOptionsList,$tempStr );
								}
								echo $tempStr;
							}
						?>				
					</select>
					
					<!-- Selected List -->
					<div>
						<table>
							<tr>
								<td><h3 id="gene_select_title">Selected Genes <span class="h3normalfont">(Total: <span id="selected_genes_count_span"><?php echo count($SelectedOptionsList); ?></span>)</span></h3>
								</td>
								<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
								<td><div class="ErrorMessage"><?php echo $myController->getGeneSelectErrorMsg(); ?>&nbsp;</div>
								</td>					
							</tr>
						</table>
						<div class="instructions ShowIfJavaScript">You can select multiple genes at once by holding the 'ctrl' key and clicking on each gene.</div>
						<select multiple size="10" name="selected_gene_id" id="selected_gene_id" class="GeneralInputPlusErrorTotalWidth">
						<?php
							//Generate Default PHP values to show when there is no JavaScript
							echo implode("",$SelectedOptionsList);
						?>
						</select>
						<span class="ShowIfJavaScript"><input type='button' value='&dArr; Remove Chosen Genes From Selected List &dArr;' name='move_to_available' class='minimalstylebutton' onclick="SetSelectedOption(false)" /></span>
					</div>
					<br/>
					
					
					<!-- Available Genes -->
					<div>
						<h3>Genes Available for Selection <span class="h3normalfont">(Total: <span id="available_genes_count_span"><?php echo count($AvailableOptionsList); ?></span>)</span></h3>
						<span class="ShowIfJavaScript"><a href='#' id='available_gene_id_holder-show' class='showLink' onclick='showHide("available_gene_id_holder");return false;'>Show List</a></span>
						<div id="available_gene_id_holder" class="HideIfJavaScript">
							<!-- Hide buttons both at top and bottom-->
							<span class="ShowIfJavaScript"><a href='#' class='hideLink' onclick='showHide("available_gene_id_holder");return false;'>Hide List</a></span>
							<div class="instructions ShowIfJavaScript">You can select multiple genes at once by holding the 'ctrl' key and clicking on each gene.<br/></div>
							<div class="ShowIfJavaScript"><input type='button' value='&uArr; Add Chosen Genes to Selected List &uArr;' name='move_to_selected' class='minimalstylebutton' onclick="SetSelectedOption(true)" /></div>
							<select multiple size="10" name="available_gene_id" id="available_gene_id" class="GeneralInputPlusErrorTotalWidth">
							<?php
								//Generate Default PHP values to show when there is no JavaScript
								echo implode("",$AvailableOptionsList);
							?>	
							</select>
							<!-- Note: Hide Button must be within the Hidden Content to be hidden at the start-->
							<span class="ShowIfJavaScript"><a href='#' class='hideLink' onclick='showHide("available_gene_id_holder");return false;'>Hide List</a></span>
						</div>
					</div>
					<br/>
					
					
					<!-- Unrecognized Genes -->
					<div>
						<h3>Unrecognized Gene ID List <span class="h3normalfont">(Total: <?php echo count( $myController->getUnrecognized_gene_list() ); ?>)</span></h3>
						<span class="ShowIfJavaScript"><a href='#' id='unrecognized_gene_id_holder-show' class='showLink' onclick='showHide("unrecognized_gene_id_holder");return false;'>Show List</a></span>
						<div id="unrecognized_gene_id_holder" class="HideIfJavaScript">
							<!-- Hide buttons both at top and bottom-->
							<span class="ShowIfJavaScript"><a href='#' class='hideLink' onclick='showHide("unrecognized_gene_id_holder");return false;'>Hide List</a></span>
							<select size="10" name="unrecognized_gene_id" id="unrecognized_gene_id" class="GeneralInputPlusErrorTotalWidth">
								<?php 
									foreach($myController->getUnrecognized_gene_list() as $geneid) {
									echo "<option>".$geneid."</option>"; 
									}
								?>
							</select>
							<!-- Note: Hide Button must be within the Hidden Content to be hidden at the start-->
							<span class="ShowIfJavaScript"><a href='#' class='hideLink' onclick='showHide("unrecognized_gene_id_holder");return false;'>Hide List</a></span>
						</div>
					</div>
					<br/>
					<input type="submit" value="Save and Continue" class='minimalstylebutton' />
					<br/>
					<br/>
				</form>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>