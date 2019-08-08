<?php
//Inner Width of Sections
//Originally was 988, but that could not fit into 1024 resolution
//Used in these files:
//	commonstyle-page_header.php
//	commonstyle-page_footer.php
//	commonstyle-section-0-template.php
//	commonstyle-section-1-beforetitle.php
//	commonstyle-section-2-beforecontent.php
//	commonstyle-section-3-aftercontent.php
//
//When you change this width remember to change
//	style-glycan-plus.css: The various column widths and image widths. Including the images in the nested help window.
//	analysis.php: The width of the images

//Width: Narrow is 800, wide is 980
$SectionFormat_InnerWidth = 800;

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- 
NOTE: Need <!DOCTYPE html> at top for popup to work with internet
explorer, but this cause alignment to shift to center. Added on 
various text-align: center in style (for central content cell and 
JSMol Div) to compensate
-->	
<link rel="stylesheet" type="text/css" href="style-glycan-plus.css">
<link rel="stylesheet" type="text/css" href="glycan/glycan.css">
<link rel="stylesheet" type="text/css" href="glycan/ext-all.css">
<link rel="stylesheet" type="text/css" href="glycan/xtheme-gray.css"> 
<link rel="stylesheet" type="text/css" href="glycan/fileuploadfield.css"> 

<link rel="shortcut icon" href="images/favicon.ico">

<!-- Table Sorter Stuff -->
<link rel="stylesheet" type="text/css" href="style-glycan-tablesorter.css">
<script type="text/javascript" src="javascript/jquery-1.12.2.js"></script>	
<script type="text/javascript" src="javascript/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript">
	function TopTabMouseover(inElement)
	{ inElement.className="x-tab-strip-over"; }
	function TopTabMouseout(inElement)
	{ inElement.className=""; }
	
	function showPopUpMessage(InMessage) {
		document.getElementById("popup_message_div_content").innerHTML = InMessage;
		document.getElementById("popup_message_div").style.display = '';
	}
	
	function showGreyPopup(Title,TargetAjaxURL) {
		jQuery('#grey_popup_title').html(Title);
		jQuery('#grey_popup_content').html("");	//Reset content
		var PostData = {};
		jQuery.ajax({
			url: TargetAjaxURL, //If not specified, defaults to current page
			data: PostData,
			type: "post", 		//Versions before 1.9.0 
			//method: ┤"get"-or-"post"-or-"put"├, //Version 1.9.0 and later
			error: function(JqXHR, TextStatus, ErrorThrown){
				//console.log("JqXHR: %o", JqXHR);
				//console.log("TextStatus: "+TextStatus);
				//console.log("ErrorThrown: "+ErrorThrown);
				jQuery('#grey_popup_content').html("An unexpected error has occured. Please contact us for help.");
			},
			success: function(ReturnData, TextStatus, JqXHR){
				//console.log("ReturnData: "+ReturnData);
				//console.log("TextStatus: "+ TextStatus);
				//console.log("JqXHR: %o", JqXHR);
				jQuery('#grey_popup_content').html(ReturnData);
				jQuery('#grey_popup_content .ShowIfJavaScript').css('display','inline');
				jQuery('#grey_popup_content .HideIfJavaScript').css('display','none');
			}
		});
		jQuery('#grey_popup_holder_div').css('display','inline');
	}
</script>
