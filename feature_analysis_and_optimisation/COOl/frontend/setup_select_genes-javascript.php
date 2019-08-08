<?php
//This is the javascript for the "setup_select_genes.php" page
//It is located here as a "require_once", instad of inside the javascript directory, as it needs access to the controller
if ( isset($myController) ) {		//Check Controller
	if ( get_class ($myController) == "CodonOpt_Controller_SetupSelectGenes" ) {
	} else {
		die ("Error! Input Job is not CodonOpt_Controller_SetupSelectGenes class");
	}
} else {
	die("No valid Controller was given!");
}
?>
<script language="javascript" type="text/javascript">
	jQuery(document).ready(			//When document is ready
		function(){
			jQuery(".HideIfJavaScript").css("display","none");
			jQuery(".ShowIfJavaScript").css("display","inline");
			
			jQuery("#selected_gene_id").html( jQuery("#all_genes").html() );	//Overwrite Selected Genes
			jQuery("#available_gene_id").html( jQuery("#all_genes").html() );	//Overwrite Available Genese
			hide_option_string("#selected_gene_id option:not(:selected)");		//For selected, hide unselected options
			hide_option_string("#available_gene_id option:selected");			//For available, hide selected options
			jQuery("#selected_gene_id option").prop("selected",false);			//Deselect all genes
			jQuery("#available_gene_id option").prop("selected",false);			//Deselect all genes
			//RepopulateAvailableSelectedGenes();								//Populate Selected Genes
		}
	);
	
	//Re-Populate available_genes and selected_genes list
	function RepopulateAvailableSelectedGenes() {
		window.selected_genes_count = 0;
		window.available_genes_count = 0;
		jQuery("#all_genes option").each(				//Go through each options in hidden list
			function() {
				var tempObj = jQuery(this);				//Convert to jQuery Object (this is the raw option element)
				var tempVal = tempObj.val();			//Extract Value
				if ( tempObj.prop("selected") ) {		//If this is selected
					hide_select_option("available_gene_id",tempVal);
					show_select_option("selected_gene_id",tempVal);
					window.selected_genes_count++;
				} else {								//Otherwise this is not selected
					hide_select_option("selected_gene_id",tempVal);
					show_select_option("available_gene_id",tempVal);
					window.available_genes_count++;
				}
			}
		);
		//Update Selected/Available Count
		jQuery("#available_genes_count_span").html(window.available_genes_count);
		jQuery("#selected_genes_count_span").html(window.selected_genes_count);
	}
	
	//When 'Load Sample List' button is clicked, load HEG list into box
	function load_heg_into_box() {
		document.getElementById("gene_sort_box").value = "<?php
			{	//Extract contents from file
				$linebreaks = array("\n","\r");
				$FileContents = file_get_contents( $myController->get_heg_link() );
				$FileContents = str_ireplace(
					$linebreaks," ",$FileContents
				);									
				echo $FileContents;
			}
		?>";
		document.sort_genes_form.submit();	//Submit
	}
	
	//Function to change which genes are selected
	function SetSelectedOption(TrueFalse) {
		var ListName = "#selected_gene_id";		//If false (i.e. moving from selected to available), check selected list
		if (TrueFalse) {						//If true (i.e. moving from available to selected)
			ListName = "#available_gene_id";	//Check available list
		}
		var tempList = jQuery(ListName).val();
		var countA = 0;
		if (tempList == null) {					//If returned object is null, then countA remains at zero
		} else {								//Otherwise returned object is not null
			countA = tempList.length;			//Extract Length
		}
		
		var available_genes_count = parseInt( jQuery("#available_genes_count_span").html() );
		var selected_genes_count = parseInt( jQuery("#selected_genes_count_span").html() );
		if (countA >= 1) {
			for (var numA=0; numA<countA; numA++) {		//Go through each value
				var tempStr = tempList[numA];			//Update whether that option is selected
				var tempObj = jQuery("#all_genes option[value='"+tempStr+"']");
				tempObj.prop("selected",TrueFalse);
				if (TrueFalse) {	//If true (i.e. moving from available to selected)
					hide_select_option("available_gene_id",tempStr);
					show_select_option("selected_gene_id",tempStr);
					available_genes_count--;
					selected_genes_count++;
				} else {			//If false (i.e. moving from selected to available), check selected list
					hide_select_option("selected_gene_id",tempStr);
					show_select_option("available_gene_id",tempStr);
					selected_genes_count--;
					available_genes_count++;
				}
			}
		}
		jQuery("#available_genes_count_span").html(available_genes_count);
		jQuery("#selected_genes_count_span").html(selected_genes_count);
	}
	
	//Function to Hide the select option
	function hide_select_option(SelectID,OptionVal) {
		hide_option_string("#"+SelectID+" > option[value='"+OptionVal+"']");
	}
	function hide_option_string(InString) {
		jQuery(InString).wrap('<span>').hide();
	}
	
	
	//Function to Show the select option
	function show_select_option(SelectID,OptionVal) {
		show_option_string("#"+SelectID+" > span > option[value='"+OptionVal+"']");
	}
	function show_option_string(InString) {
		var tempOpt = jQuery(InString).show();
		var tempSpan = tempOpt.parent();
		tempSpan.replaceWith(tempOpt);
	}
</script>