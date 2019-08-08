jQuery(document).ready(
	function(){
		jQuery(".HideIfJavaScript").css("display","none");
		jQuery(".ShowIfJavaScript").css("display","inline");
		
		//For "viewresultdetail_exclusion.php"
		jQuery("#exclusion_fitness_table").tablesorter();
		
		//For "viewresultdetail_ic_HostVsOptimizedCodonRelFreqComparison.php" 
		jQuery("#ic_HostVsOptimizedCodonRelFreqComparison").tablesorter(); 
		jQuery("#object_nested_error_message").css("display","none");	//Manage the SVG Objects
		var svg_object_nested_span = document.getElementById("svg_object_nested_span");
		var results_svg_div = jQuery("#results_svg_div");				//Get Div using jQuery (so no errors even if Div is not on page)
		if (svg_object_nested_span) {									//If svg_object_nested_span (SVG is ENabled)
			results_svg_div.css("display","inline");					//Show the SVG 
		} else {														//Otherwise SVG is DISabled
			results_svg_div.css("display","none");						//Show the SVG 
		}
		
		//For "viewresultdetail_ic.php"
		jQuery("#ic_frequency_table").tablesorter();
		
		//For "viewresultdetail_cc.php"
		jQuery("#cc_frequency_table").tablesorter(); 
		
		//If no SVG, show error message
		if (! checkForSVG() ) {
			jQuery(".ShowIfNoSVG").css("display","inline");
		}
	}
);