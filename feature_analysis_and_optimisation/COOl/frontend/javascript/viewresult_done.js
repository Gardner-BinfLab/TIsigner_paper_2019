jQuery(document).ready(
	function() { 
		jQuery(".HideIfJavaScript").css("display","none");
		jQuery(".ShowIfJavaScript").css("display","inline");
		jQuery("#result_summary_table").tablesorter(); 
		
		//These Scripts are for "viewresult_done_svg.php"
		jQuery("#object_nested_error_message").css("display","none");	//If JavaScript is working: Hide the Error Message
		var svg_object_nested_span = document.getElementById("svg_object_nested_span");
		var results_svg_div = jQuery("#results_svg_div");				//Get Div using jQuery (so no errors even if Div is not on page)
		if (svg_object_nested_span) {									//If svg_object_nested_span (SVG is Enabled)
			results_svg_div.css("display","inline");					//Show the SVG div
		} else {														//Otherwise SVG is DISabled
			results_svg_div.css("display","none");						//Hide the SVG div
		}
		
		//If no SVG, show error message
		if (! checkForSVG() ) {
			jQuery(".ShowIfNoSVG").css("display","inline");
		}
	}
); 