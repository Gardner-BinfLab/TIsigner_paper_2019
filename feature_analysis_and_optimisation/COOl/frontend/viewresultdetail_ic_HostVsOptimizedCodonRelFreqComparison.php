<?php
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";
require_once "Controllers/CodonOpt_SVGmaker_HostVsOptimizedCodonRelFreqComparison.php";
require_once "Controllers/CodonOpt_SVGmaker_RadarGeneric.php";

if (! isset($Ic_ColorPicker) ) {	//If there is no current controller
	header( "Location: ".CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage() );
	exit;							//Go back to Submit New Job
}

$BarSVGmaker;
$RadarSVGmaker;

{	//Subfunction to instantiate SVGmakers
	$SplitLines = $myController->SplitHostVsOptimizedCodonRelFreqLines();
	$SortedLines = $myController->SortHostVsOptimizedCodonRelFreqLines();
	$BarSVGmaker = new CodonOpt_SVGmaker_HostVsOptimizedCodonRelFreqComparison($SplitLines);
	//Extract Host and Optimized, and store it in Radar
	$HostRadarData = new CodonOpt_SVGmaker_RadarData("Host","Red");
	$OptRadarData = new CodonOpt_SVGmaker_RadarData("Optimized","Blue");
	foreach ($SortedLines as $tempItem) {
		$tempKey = $tempItem->getAAbase().":".$tempItem->getCodon(); 
		$HostRadarData->AddDataPoint( $tempKey , $tempItem->getHostRelFreqForDisplay() );
		$OptRadarData->AddDataPoint( $tempKey , $tempItem->getOptSeqRelFreqForDisplay() );
	}
	$RadarSVGmaker = new CodonOpt_SVGmaker_RadarGeneric("Host vs Optimized Individual Codon Usage","Mouseover a codon for details",1);
	$RadarSVGmaker->AddData($HostRadarData);
	$RadarSVGmaker->AddData($OptRadarData);
} 
?>

<h3>Host Vs Optimized Codon Relative Frequency Radar Plot</h3> 
	<span class="ShowIfJavaScript">
		<a href='#' id='ic_HostVsOptimizedCodonRelFreqComparison_holder-show' class='showLink' onclick='showHide("ic_HostVsOptimizedCodonRelFreqComparison_holder");return false;'>Show Chart.</a>
	</span>

<div id="ic_HostVsOptimizedCodonRelFreqComparison_holder" class="HideIfJavaScript">
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("ic_HostVsOptimizedCodonRelFreqComparison_holder");return false;'>Hide Chart.</a>
	</span>
	<br/>
	<span class="ShowIfNoSVG"><div class="ErrorMessage">Your browser does not support SVG. To view the Host Vs Optimized Codon Relative Frequency Chart, please switch to a browser with SVG.</div></span>
	<?php 
		//This uses native SVG and should work for everything else.
		echo $RadarSVGmaker->GenerateSVGForAminoAcidRadar();
	?>
	<br/>
	<!-- Note: Hide Button must be within the Hidden Content to be hidden at the start-->
	<span class="ShowIfJavaScript">
		<a href='#' class='hideLink' onclick='showHide("ic_HostVsOptimizedCodonRelFreqComparison_holder");return false;'>Hide Chart.</a>
	</span>
</div>
<br/>
<br/>
<?php 
/*	//Original Bar Chart Plot. Currently Disabled
<table>
	<tr>
		<td style="background-color:rgb(255,0,0);">&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Host</td>
	</tr>
	<tr>
		<td style="background-color:rgb(0,0,255);">&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Optimized</td>
	</tr>
</table>

<object data='blank.svg' height="0" width="0" id="object_nested_SVGweb_flash_bar">
	<span id="svg_object_nested_span_bar">&nbsp;</span><br/>
	<script type="image/svg+xml">
		<?php 
			//This one uses SVGweb to convert SVG to flash, and should work for IE 8 and older
			echo $BarSVGmaker->GenerateSVG();
		?>
	</script>
	<br/>
</object>

<div id="results_svg_div_bar">
	<?php 
		//This uses native SVG and should work for everything else.
		echo $BarSVGmaker->GenerateSVG();
	?>		
</div>	
*/ ?>

<?php
/*	//Old Version: Flash Fallback if no SVG
Explanation of display cascade:
-If there is SVG and JavaScript (Chrome with JavaScript enabled)
	-results_svg_div is shown
	-results_form_div is shown
	-blank.svg hidden by object
	
-If there is SVG with no JavaScript (Chrome with JavaScript disabled)
	-results_svg_div is shown
	-results_form_div is shown
	-blank.svg hidden by object
	
-If there is no SVG but JavaScript (e.g. IE8 with JavaScript)
	-results_svg_div will be hidden
	-results_form_div is shown
	-blank.svg shown by object
		-object_nested_error_message is hidden
		-SVGweb converts SVG into Flash
	
If there is no SVG AND no JavaScript (e.g. IE8 with JavaScript disabled)
	-results_svg_div will be shown
	-results_form_div is shown
	-blank.svg shown by object
		-object_nested_error_message remains unchanged (is shown)
		-SVGweb not run and therefore hidden
*/

/*	//Old Version: Flash Fallback if no SVG
//Unfortunately, this caused the page to jump to the middle/end in Chrome
	<object data='blank.svg' height="0" width="0" id="object_nested_SVGweb_flash_radar">
		<span id="svg_object_nested_span_radar">&nbsp;</span><br/>
		<script type="image/svg+xml">
			<?php 
				//This one uses SVGweb to convert SVG to flash, and should work for IE 8 and older
				echo $RadarSVGmaker->GenerateSVGForAminoAcidRadar();
			?>
		</script>
		<br/>
	</object>
	
	<object data='blank.svg' height="0" width="0" id="object_nested_error_message">
		<?php //This must be placed AFTER the SVGweb Flash converter to work for some unknown reason ?>
		<div class="ErrorMessage">Your browser does not support both SVG and JavaScript. To view the Host Vs Optimized Codon Relative Frequency Chart, please switch to a browser with either SVG or JavaScript.</div>
		<br/>
	</object>
	
	<div id="results_svg_div_radar">
		<?php 
			//This uses native SVG and should work for everything else.
			echo $RadarSVGmaker->GenerateSVGForAminoAcidRadar();
		?>		
	</div>
*/
?>
