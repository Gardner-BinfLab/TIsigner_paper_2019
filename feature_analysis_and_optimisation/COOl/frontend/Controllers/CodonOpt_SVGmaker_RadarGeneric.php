<?php
require_once "CodonOpt_SVGmaker_RadarData.php";
require_once "CodonOpt_Utility.php";

//This class is meant to generate the text for amino acid radar plot SVG
class CodonOpt_SVGmaker_RadarGeneric {
	//Internal Variables
	private $DataList = array();	//Life of Data
	public function getDataList() {
		return $this->DataList;
	}
	private $RadarPlotTitle;		//Title of Radar Plot
	private $RadarPlotSubTitle;		//SubTitle of Radar Plot
	private $RotateLabels;			//Whether to rotate labels

	//Add a Column to List of Columns
	public function AddData($InputData) {
		if ( isset($InputData) ) {
			if ( $InputData instanceof CodonOpt_SVGmaker_RadarData ) {
				array_push($this->DataList,$InputData);
			} else {
				die ("Error! Input Data is not CodonOpt_SVGmaker_RadarData class");
			}
		} else {
			die("No valid Input Data was given!");
		}
	}
	
	//Constructor: Accepts SVG options
	//If using labels wider than 1 char, it is probably a good idea to rotate labels
	public function CodonOpt_SVGmaker_RadarGeneric($RadarPlotTitle,$RadarPlotSubTitle,$RotateLabels) {
		$this->RadarPlotTitle = $RadarPlotTitle;
		$this->RadarPlotSubTitle = $RadarPlotSubTitle;
		$this->RotateLabels = $RotateLabels;
	}

	//Static Variables
	private static $StandardFontSize = 14;	//MUST use one font size, as Internet Explorer, Firefox and Opera cannot parse font size properly
	private static $LineWidth_Axis = 1;
	private static $LineColor_Axis_Normal = "rgb(192,192,192)";
	private static $LineColor_Axis_Mouseover = "black";
	private static $NormalTextColor = "black";			//Color for most Text
	//private static $LabelColor_Mouseover = "grey";	//Use Bold instead
	private static $LineWidth_Plot = 2;
	private static $TitleHeight = 40;
	private static $ChartPadding = 60;		//Chart Padding must include space for font
	private static $RadarEdgeToLabel = 10;	//Distance from edge of radar to Label (must be less than $ChartPadding)
	private static $RadarRadius = 250;
	private static $LegendHeight = 40;
	private static $LegendLabelWidth = 160;	//How wide the Legend Label is (determines where the Legend Value starts)

	public static function getTotalWidth() {
		return (
			CodonOpt_SVGmaker_RadarGeneric::$ChartPadding +
			CodonOpt_SVGmaker_RadarGeneric::$RadarRadius +
			CodonOpt_SVGmaker_RadarGeneric::$RadarRadius +
			CodonOpt_SVGmaker_RadarGeneric::$ChartPadding
		);
	}
	public static function getTotalHeight() {
		return (
			CodonOpt_SVGmaker_RadarGeneric::getTotalWidth() +
			CodonOpt_SVGmaker_RadarGeneric::$TitleHeight +
			CodonOpt_SVGmaker_RadarGeneric::$LegendHeight
			
		);
	}
	
	
	//Create SVG chart
	public function GenerateSVGForAminoAcidRadar() {
		//First Find Max/Min values
		$MaxValue;
		$MinValue;
		{	//Find min and max values
			$tempArray = $this->findRadarMinMax();
			$MinValue = $tempArray[0];
			$MaxValue = $tempArray[1];
		}
		
		$NumberOfRadarPoints = $this->DataList[0]->CountDataPoints();
		$NumberOfPlots = count($this->DataList);
		$CentrePointX = CodonOpt_SVGmaker_RadarGeneric::$ChartPadding + CodonOpt_SVGmaker_RadarGeneric::$RadarRadius;
		$CentrePointY = CodonOpt_SVGmaker_RadarGeneric::$TitleHeight + $CentrePointX;
		$DisplaceX_Hash = array();	//Hash containing X displacement values for a particular Radar Key
		$DisplaceY_Hash = array();	//Hash containing Y displacement values for a particular Radar Key
		
		//Seed the Output Arrays
		$OutArray_Header = array();					//Header <svg> and background rectangle
		$OutArray_HLAxisLines = array();			//For Highlightable Axis Lines
		$OutArray_MaxAxisLines = array();			//For Max Axis Lines (will not be highlighted)
		$OutArray_AxisLabels_AppearText = array();	//For Axis Labels, to store Appear text
		$OutArray_AxisLabels_Title = array();		//For Axis Labels, to store titles
		$OutArray_AxisLabels_Output = array();		//Combines AxisLabels_Text and AxisLabels_Title for final element
		$OutArray_ValueLabels = array();			//Labels for Values (currently just minimum and maximum)
		$OutArray_Legend_Labels = array();			//Labels in the Legend Area
		$OutArray_Legend_AppearText = array();		//Text that appears in the Legend Area (normally hidden)
		$OutArray_RadarPoints = array();			//Array for Data points
		$OutArray_Footer = array();					//Footer </svg>
		
		//Fill out the header
		$TempStr = 						//SVG Header
			"<svg width='".( CodonOpt_SVGmaker_RadarGeneric::getTotalWidth() )."px' height='".( CodonOpt_SVGmaker_RadarGeneric::getTotalHeight() )."px' xlink='http://www.w3.org/1999/xlink' space='preserve' xmlns='http://www.w3.org/2000/svg' version='1.1'>";
		array_push($OutArray_Header,$TempStr);
		$TempStr =						//The CSS Style Sheet
"<defs>
	<style type='text/css'><![CDATA[
		text { font-family: tahoma, arial, helvetica, sans-serif; fill: ".CodonOpt_SVGmaker_RadarGeneric::$NormalTextColor."; font-size: ".CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize."px; }
		.radar_plot_main_label:hover {
			font-weight: bold
		}
		.radar_plot_main_label:hover ~ .radar_plot_appear_text {
			visibility: visible
		}
		.radar_plot_main_label:hover ~ .radar_plot_axis_line {
			stroke: ".CodonOpt_SVGmaker_RadarGeneric::$LineColor_Axis_Mouseover.";
		}
	]]></style>
</defs>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//Background Rectangle
			"<rect id='background_rectangle' width='".( CodonOpt_SVGmaker_RadarGeneric::getTotalWidth() )."' height='".( CodonOpt_SVGmaker_RadarGeneric::getTotalHeight() )."' fill='white' stroke-width='0' stroke='none' x='0' y='0'/>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//Graph Title
			"<text id='graph_title' x='0' y='".CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize."' font-weight='bold' >".$this->RadarPlotTitle."</text>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//Graph Title
			"<text id='graph_subtitle' x='0' y='".(20+CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize)."' >".$this->RadarPlotSubTitle."</text>";
		array_push($OutArray_Header,$TempStr);
		

		{	//Fill out the Axis and labels
			//Tangent = Opposite / Adjacent
			//Cosine = Adjacent / Hypothenuse
			//Sine = Opposite / Hypotenuse
			$MaxPlotX_Hash = array();	//As DisplaceX_Hash, but stored in numerical array instead of keyed hash
			$MaxPlotY_Hash = array();	//As DisplaceY_Hash, but stored in numerical array instead of keyed hash
			
			$LabelShiftMultiplier = (CodonOpt_SVGmaker_RadarGeneric::$RadarRadius+CodonOpt_SVGmaker_RadarGeneric::$RadarEdgeToLabel) / CodonOpt_SVGmaker_RadarGeneric::$RadarRadius;
			$numA = 0;
			foreach ($this->DataList[0]->GetDataList() as $tempKey => $tempValue ) {
				$Angle = ( $numA / $NumberOfRadarPoints * deg2rad(360) ) - deg2rad(90);
				$DisplaceX = CodonOpt_SVGmaker_RadarGeneric::$RadarRadius * cos($Angle);
				$DisplaceY = CodonOpt_SVGmaker_RadarGeneric::$RadarRadius * sin($Angle);
				$DisplaceX_Hash[$tempKey] = $DisplaceX;
				$DisplaceY_Hash[$tempKey] = $DisplaceY;
				array_push($MaxPlotX_Hash,$DisplaceX);
				array_push($MaxPlotY_Hash,$DisplaceY);
				$TempStr = 	//Add Radar Line
					"<line id='axisline_".$numA."' x1='".$CentrePointX."' y1='".$CentrePointY."' x2='".($CentrePointX+$DisplaceX)."' y2='".($CentrePointY+$DisplaceY)."' fill='none' stroke='".CodonOpt_SVGmaker_RadarGeneric::$LineColor_Axis_Normal."' stroke-width='".CodonOpt_SVGmaker_RadarGeneric::$LineWidth_Axis."pt' class='radar_plot_axis_line'>";
				$TempStr .= "</line>";
				$OutArray_HLAxisLines[$tempKey] = $TempStr;
				
				{	//This section Generates the Radar Labels
					$TempXCoord = $CentrePointX+($DisplaceX*$LabelShiftMultiplier);
					$TempYCoord = $CentrePointY+($DisplaceY*$LabelShiftMultiplier);
					if ($this->RotateLabels) {	//If you want to rotate labels
						//Displace Coords, so that centre of label lines up with axis line
						$TempXCoord += CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*0.4*cos(deg2rad(90)+$Angle);	
						$TempYCoord += CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*0.4*sin(deg2rad(90)+$Angle);
						$TempStr = 				//Line Title (Note that dominant-baseline='central' does not work in IE and hence is not used)
							"<text id='axisline_".$numA."_title' transform='translate(".$TempXCoord.",".$TempYCoord.")rotate(".rad2deg($Angle).")' class='radar_plot_main_label'  >";
					} else {					//Otherwise, DO NOT rotate labels
						//No need to change X coord as Label is centre aligned (text-anchor='middle')
						//$TempXCoord -= CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*0.4;
						$TempYCoord += CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*0.4;
						$TempStr = 				//Line Title (Note that dominant-baseline='central' does not work in IE and hence is not used)
							"<text text-anchor='middle' id='axisline_".$numA."_title' x='".$TempXCoord."' y='".$TempYCoord."' class='radar_plot_main_label' >";
					}
					$TempStr .= $tempKey;		//Label Text
					$OutArray_AxisLabels_AppearText[$tempKey] = $TempStr;	//Store as key without closing Text (more will be added later)
				}
				$numA++;
			}
			
			for ($numB=0; $numB<$NumberOfRadarPoints; $numB++) {
				$CoordX1 = $MaxPlotX_Hash[$numB];
				$CoordY1 = $MaxPlotY_Hash[$numB];
				$CoordX2;
				$CoordY2;
				//Extract next set of coords
				if ($numB == ($NumberOfRadarPoints-1) ) {	//If this is the last set of coords
					$CoordX2 = $MaxPlotX_Hash[0];			//Next set of coords is first set
					$CoordY2 = $MaxPlotY_Hash[0];
				} else {									//Otherwise this is NOT last set of coords
					$CoordX2 = $MaxPlotX_Hash[$numB+1];		//Next set of coords is next set
					$CoordY2 = $MaxPlotY_Hash[$numB+1];
				}
				$TempStr = 	//Add Maximum Radar Line
					"<line id='maxaxisline_".$numB."' x1='".($CentrePointX+$CoordX1)."' y1='".($CentrePointY+$CoordY1)."' x2='".($CentrePointX+$CoordX2)."' y2='".($CentrePointY+$CoordY2)."' fill='none' stroke='".CodonOpt_SVGmaker_RadarGeneric::$LineColor_Axis_Normal."' stroke-width='".CodonOpt_SVGmaker_RadarGeneric::$LineWidth_Axis."pt'></line>";
				array_push($OutArray_ValueLabels,$TempStr);
			}
		}
		
		{	//Value Labels (currently just Minimum and Maximum)
			$TempStr = 		//Maximum Value Title
				"<text id='maxaxisline_title' fill='".CodonOpt_SVGmaker_RadarGeneric::$LineColor_Axis_Normal."' x='".(CodonOpt_SVGmaker_RadarGeneric::$RadarRadius+CodonOpt_SVGmaker_RadarGeneric::$ChartPadding)."' y='".(CodonOpt_SVGmaker_RadarGeneric::$TitleHeight+CodonOpt_SVGmaker_RadarGeneric::$ChartPadding+(CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize))."' >".$MaxValue."</text>";
			array_push($OutArray_MaxAxisLines,$TempStr);
			$TempStr = 		//Minimum Value Title
				"<text id='minaxisline_title' fill='".CodonOpt_SVGmaker_RadarGeneric::$LineColor_Axis_Normal."' x='".($CentrePointX)."' y='".($CentrePointY)."' >".$MinValue."</text>";
			array_push($OutArray_ValueLabels,$TempStr);
		}
		
		
		//Fill out Radar Plot Points
		foreach ($this->DataList as $tempPointCount => $TempRadarData) {
			//First convert the absolute values into a list of coords
			//We need all coords in order to calculate the polyline structure
			$CoordHashX = array();
			$CoordHashY = array();
			$OutArray_Legend_AppearText[$tempPointCount] = array();	//One element in AppearText for each element in DataList
			$LegendBaseYCoord = //Y coord for the legend
				CodonOpt_SVGmaker_RadarGeneric::$TitleHeight+CodonOpt_SVGmaker_RadarGeneric::$ChartPadding+CodonOpt_SVGmaker_RadarGeneric::$RadarRadius+CodonOpt_SVGmaker_RadarGeneric::$RadarRadius+CodonOpt_SVGmaker_RadarGeneric::$ChartPadding;
			{	//First Create numerical array wih coords of each point
				$numA = 0;		//Counter to keep track of current number
				foreach ($TempRadarData->GetDataList() as $tempKey => $tempValue) {
					$FractionMultiplier = ($tempValue - $MinValue) / ($MaxValue - $MinValue);
					array_push( $CoordHashX ,
						($CentrePointX + ($DisplaceX_Hash[$tempKey] * $FractionMultiplier) )
					);
					array_push( $CoordHashY ,
						($CentrePointY + ($DisplaceY_Hash[$tempKey] * $FractionMultiplier) )
					);
					if ( isset($OutArray_AxisLabels_Title[$tempKey]) ) {	//If current key is set
					} else {												//Otherwise current key is not set
						$OutArray_AxisLabels_Title[$tempKey] = "";			//Set key
					}														//Add Value
					$OutArray_AxisLabels_Title[$tempKey] .= $TempRadarData->getChartTitle()." : ".CodonOpt_SVGmaker_RadarGeneric::RoundForDisplay($tempValue)." \n";
					$TempStr = 	//Mouseover Text
						"<text id='".$tempPointCount."_".$numA."_popuptext' visibility='hidden' x='".CodonOpt_SVGmaker_RadarGeneric::$LegendLabelWidth."' y='".( $LegendBaseYCoord+($tempPointCount*20)+CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize )."' class='radar_plot_appear_text' >";
					$TempStr .= CodonOpt_SVGmaker_RadarGeneric::RoundForDisplay($tempValue);
					$TempStr .= "</text>";
					$OutArray_Legend_AppearText[$tempPointCount][$tempKey] = $TempStr;
					$numA++;
				}
			}
			
			//For each point, generate as polyline linking previous and next point
			for ($numA=0; $numA<$NumberOfRadarPoints; $numA++) {
				$CoordX0;
				$CoordY0;
				$CoordX1 = $CoordHashX[$numA];
				$CoordY1 = $CoordHashY[$numA];
				$CoordX2;
				$CoordY2;
				//Extract next set of coords
				if ($numA == ($NumberOfRadarPoints-1) ) {	//If this is the last set of coords
					$CoordX2 = $CoordHashX[0];				//Next set of coords is first set
					$CoordY2 = $CoordHashY[0];
				} else {									//Otherwise this is NOT last set of coords
					$CoordX2 = $CoordHashX[$numA+1];		//Next set of coords is next set
					$CoordY2 = $CoordHashY[$numA+1];
				}
				//Extract previous set of coords
				if ($numA == 0 ) {	//If this is the first set of coords
					$CoordX0 = $CoordHashX[$NumberOfRadarPoints-1];		//Previous set of coords is last set
					$CoordY0 = $CoordHashY[$NumberOfRadarPoints-1];
				} else {									//Otherwise this is NOT first set of coords
					$CoordX0 = $CoordHashX[$numA-1];		//Next set of coords is next set
					$CoordY0 = $CoordHashY[$numA-1];
				}
				$polylinecoord =
					(($CoordX0+$CoordX1)/2).",".(($CoordY0+$CoordY1)/2)." ".
					$CoordX1.",".$CoordY1." ".
					(($CoordX1+$CoordX2)/2).",".(($CoordY1+$CoordY2)/2);
				$TempStr = 	//Add Radar Line
					"<polyline points='".$polylinecoord."' stroke='".$TempRadarData->getPlotColor()."' stroke-width='".CodonOpt_SVGmaker_RadarGeneric::$LineWidth_Plot."' fill='none' id='".$TempRadarData->getPlotColor()."_".$numA."'>";
				$TempStr .= "</polyline>";	
				array_push($OutArray_RadarPoints,$TempStr);
			}
			
			//Add Legend
			$TempStr = 	//Color Rectangle
				"<rect id='".$tempPointCount."_color' width='".CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize."' height='".CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize."' fill='".$TempRadarData->getPlotColor()."' stroke-width='0' stroke='none' x='0' y='".( $LegendBaseYCoord+($tempPointCount*20) )."'/>";
			array_push($OutArray_Legend_Labels,$TempStr);
			$TempStr = 	//Rectangle Title
				"<text id='".$tempPointCount."_legend' x='20' y='".( $LegendBaseYCoord+($tempPointCount*20)+CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize )."' >".$TempRadarData->getChartTitle()."</text>";
			array_push($OutArray_Legend_Labels,$TempStr);
		}
		
		//Convert $OutArray_AxisLabels_AppearText and $OutArray_AxisLabels_Title to $OutArray_AxisLabels_Output
		foreach ($OutArray_AxisLabels_AppearText as $tempKey => $tempValue) {
			$tempStr = "<g>\n";			//Open as a group
			$tempStr .= $tempValue;		//Open with Text
			$tempStr .= 				//Add title
				"\n<title>".$OutArray_AxisLabels_Title[$tempKey]."</title>\n";
			$tempStr .= "</text>\n";	//Close Text
			$tempStr .= $OutArray_HLAxisLines[$tempKey]."\n";	//Add Highlightable Axis Line
			foreach ($OutArray_Legend_AppearText as $tempPointCount => $tempAppearText) {
				$tempStr .= $tempAppearText[$tempKey]."\n";		//For each element: Add the AppearText from this element
			}
			$tempStr .= "</g>";			//Close Group
			array_push($OutArray_AxisLabels_Output,$tempStr);
		}

		array_push($OutArray_Footer,"</svg>");		//Close the SVG
		return (
			implode ("\n",$OutArray_Header).		//Background Rectangle
			implode ("\n",$OutArray_MaxAxisLines).
			implode ("\n",$OutArray_AxisLabels_Output).
			implode ("\n",$OutArray_RadarPoints).
			implode ("\n",$OutArray_ValueLabels).
			implode ("\n",$OutArray_Legend_Labels).
			implode ("\n",$OutArray_Footer)
		);
	}
	
	//====================================
	//Generate the same Radar Plot for PDF
	//Becuase PDF pixel width is different from HTML, we need to multiply it by this Scale Factor
	private static $PDFScaleFactor = 0.25;
	public function GeneratePDFAminoAcidRadar($InputPDF) {
		//First Find Max/Min values
		$MaxValue;
		$MinValue;
		{	//Find min and max values
			$tempArray = $this->findRadarMinMax();
			$MinValue = $tempArray[0];
			$MaxValue = $tempArray[1];
		}
		$DisplaceX_Hash = array();	//Hash containing X displacement values for a particular Radar Key
		$DisplaceY_Hash = array();	//Hash containing Y displacement values for a particular Radar Key
		$NumberOfRadarPoints = $this->DataList[0]->CountDataPoints();
		$NumberOfPlots = count($this->DataList);
		
		//Write Title
		$InputPDF->SetFont("Arial", "B", CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize);	//Change Font
		$InputPDF->Write(7,$this->RadarPlotTitle);												//Write Title
		$InputPDF->Ln();																		//Linebreak
		
		$AnchorX = $InputPDF->GetX();	//Find Anchor Points on PDF page
		$AnchorY = $InputPDF->GetY();
		$InputPDF->SetLineWidth(		//Set Line Width
			CodonOpt_SVGmaker_RadarGeneric::$LineWidth_Axis * CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor
		);
		$TempColArray = CodonOpt_SVGmaker_RadarGeneric::ConvertRGBtoPDFColorArray( CodonOpt_SVGmaker_RadarGeneric::$LineColor_Axis_Normal );
		$InputPDF->SetDrawColor($TempColArray[0],$TempColArray[1],$TempColArray[2]);		//Set Line Color
		$CentrePointX = $AnchorX +  				//Derive Center Point
			((CodonOpt_SVGmaker_RadarGeneric::$ChartPadding + CodonOpt_SVGmaker_RadarGeneric::$RadarRadius)*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor);
		$CentrePointY = $AnchorY +  				//Derive Center Point
			((CodonOpt_SVGmaker_RadarGeneric::$ChartPadding + CodonOpt_SVGmaker_RadarGeneric::$RadarRadius + CodonOpt_SVGmaker_RadarGeneric::$TitleHeight)*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor);
		
		
		//Fill out the Axis and labels
		//Tangent = Opposite / Adjacent
		//Cosine = Adjacent / Hypothenuse
		//Sine = Opposite / Hypotenuse
		$MaxPlotX_Hash = array();	//As DisplaceX_Hash, but stored in numerical array instead of keyed hash
		$MaxPlotY_Hash = array();	//As DisplaceY_Hash, but stored in numerical array instead of keyed hash
		$InputPDF->SetFont("Arial", "", CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize);	//Change Font (remove bold)
		$LabelShiftMultiplier = (CodonOpt_SVGmaker_RadarGeneric::$RadarRadius+CodonOpt_SVGmaker_RadarGeneric::$RadarEdgeToLabel) / CodonOpt_SVGmaker_RadarGeneric::$RadarRadius;
		$numA = 0;
		
		//Draw Radar Axis Lines
		foreach ($this->DataList[0]->GetDataList() as $tempKey => $tempValue ) {
			$Angle = ( $numA / $NumberOfRadarPoints * deg2rad(360) ) - deg2rad(90);
			$DisplaceX = (CodonOpt_SVGmaker_RadarGeneric::$RadarRadius*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor) * cos($Angle);
			$DisplaceY = (CodonOpt_SVGmaker_RadarGeneric::$RadarRadius*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor) * sin($Angle);
			$DisplaceX_Hash[$tempKey] = $DisplaceX;
			$DisplaceY_Hash[$tempKey] = $DisplaceY;
			array_push($MaxPlotX_Hash,$DisplaceX);
			array_push($MaxPlotY_Hash,$DisplaceY);
			$InputPDF->Line($CentrePointX, $CentrePointY, ($CentrePointX+$DisplaceX), ($CentrePointY+$DisplaceY));
			{	//This section Generates the Radar Labels
				$TempXCoord = $CentrePointX+($DisplaceX*$LabelShiftMultiplier);
				$TempYCoord = $CentrePointY+($DisplaceY*$LabelShiftMultiplier);
				if ($this->RotateLabels) {	//If you want to rotate labels
					//Displace Coords, so that centre of label lines up with axis line
					$TempXCoord += CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor*0.5*cos(deg2rad(90)+$Angle);	
					$TempYCoord += CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor*0.5*sin(deg2rad(90)+$Angle);
					$InputPDF->RotatedText($TempXCoord,$TempYCoord,$tempKey,(0-rad2deg($Angle)) );
				} else {					//Otherwise, DO NOT rotate labels
					$Shift = (CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor*0.5);
					$TempXCoord -= $Shift;
					$TempYCoord += $Shift;
					$InputPDF->Text($TempXCoord,$TempYCoord,$tempKey);
				}
			}
			$numA++;
		}
		
		//Draw Outer Radar Line
		for ($numB=0; $numB<$NumberOfRadarPoints; $numB++) {
			$CoordX1 = $MaxPlotX_Hash[$numB];
			$CoordY1 = $MaxPlotY_Hash[$numB];
			$CoordX2;
			$CoordY2;
			//Extract next set of coords
			if ($numB == ($NumberOfRadarPoints-1) ) {	//If this is the last set of coords
				$CoordX2 = $MaxPlotX_Hash[0];			//Next set of coords is first set
				$CoordY2 = $MaxPlotY_Hash[0];
			} else {									//Otherwise this is NOT last set of coords
				$CoordX2 = $MaxPlotX_Hash[$numB+1];		//Next set of coords is next set
				$CoordY2 = $MaxPlotY_Hash[$numB+1];
			}
			$InputPDF->Line(($CentrePointX+$CoordX1), ($CentrePointY+$CoordY1), ($CentrePointX+$CoordX2), ($CentrePointY+$CoordY2));
		}
		
		//Fill out Radar Plot Points
		foreach ($this->DataList as $tempPointCount => $TempRadarData) {
			//First convert the absolute values into a list of coords
			//We need all coords in order to calculate the polyline structure
			$CoordHashX = array();
			$CoordHashY = array();
			{	//First Create numerical array wih coords of each point
				$numA = 0;		//Counter to keep track of current number
				foreach ($TempRadarData->GetDataList() as $tempKey => $tempValue) {
					$FractionMultiplier = ($tempValue - $MinValue) / ($MaxValue - $MinValue);
					array_push( $CoordHashX ,
						($CentrePointX + ($DisplaceX_Hash[$tempKey] * $FractionMultiplier) )
					);
					array_push( $CoordHashY ,
						($CentrePointY + ($DisplaceY_Hash[$tempKey] * $FractionMultiplier) )
					);
					$numA++;
				}
			}
			//For each point, join it to the next point
			$InputPDF->SetLineWidth(		//Set Line Width
				CodonOpt_SVGmaker_RadarGeneric::$LineWidth_Plot * CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor
			);
			$TempColArray = CodonOpt_SVGmaker_RadarGeneric::ConvertRGBtoPDFColorArray( $TempRadarData->getPlotColor() );
			$InputPDF->SetDrawColor(		//Set Line Color
				$TempColArray[0],$TempColArray[1],$TempColArray[2]
			);
			for ($numB=0; $numB<$NumberOfRadarPoints; $numB++) {
				$CoordX1 = $CoordHashX[$numB];
				$CoordY1 = $CoordHashY[$numB];
				$CoordX2;
				$CoordY2;
				//Extract next set of coords
				if ($numB == ($NumberOfRadarPoints-1) ) {	//If this is the last set of coords
					$CoordX2 = $CoordHashX[0];				//Next set of coords is first set
					$CoordY2 = $CoordHashY[0];
				} else {									//Otherwise this is NOT last set of coords
					$CoordX2 = $CoordHashX[$numB+1];		//Next set of coords is next set
					$CoordY2 = $CoordHashY[$numB+1];
				}
				$InputPDF->Line($CoordX1, $CoordY1, $CoordX2, $CoordY2);
			}
		}
		
		{	//Add Min and Max Labels
			$tempY = $CentrePointY - (CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor);
			$InputPDF->Text($CentrePointX,$CentrePointY,$MinValue);
			$tempY = ($CentrePointY-((CodonOpt_SVGmaker_RadarGeneric::$RadarRadius-CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize)*CodonOpt_SVGmaker_RadarGeneric::$PDFScaleFactor));
			$InputPDF->Text($CentrePointX,$tempY,$MaxValue);
		}
		
		{	//Linebreak past Radar Plot
			$LineBreakCount = 		//Number of Linebreaks to Add
				ceil(0.5*$this::getTotalHeight()/CodonOpt_SVGmaker_RadarGeneric::$StandardFontSize);
			for ($numB=0; $numB<$LineBreakCount; $numB++) {
				$InputPDF->Ln();
			}
		}
		
		//Add Legend at bottom
		foreach ($this->DataList as $tempPointCount => $TempRadarData) {
			$TempColArray = CodonOpt_SVGmaker_RadarGeneric::ConvertRGBtoPDFColorArray( $TempRadarData->getPlotColor() );
			$InputPDF->SetFillColor($TempColArray[0],$TempColArray[1],$TempColArray[2]);
			$InputPDF->Cell(5, 5, "", 0, 0, 'L', true );								//Create Filled Cell
			$InputPDF->Cell(5, 5, $TempRadarData->getChartTitle(), 0, 0, 'L', false );	//Label
			$InputPDF->Ln();															//Linebreak
		}
	}
	
	//===============
	//Other Functions
	//===============
	public function findRadarMinMax() {
		$MaxValue = -1;
		$MinValue = 0;
		foreach ($this->DataList as $TempRadarData) {
			foreach ($TempRadarData->GetDataList() as $tempKey => $tempValue) {
				if ($tempValue > $MaxValue) {
					$MaxValue = $tempValue;
				}
			}
		}
		{	//Round up MaxValue to the nearest 0.1
			$TempMax = $MaxValue*10;
			$TempMax = ceil($TempMax);
			$MaxValue = $TempMax/10;
		}
		return array($MinValue,$MaxValue);
	}
		
	public static function ConvertRGBtoPDFColorArray($input) {
		$tempArray = explode(",",$input);
		$ColorR = 127;
		$ColorG = 127;
		$ColorB = 127;
		if ( count($tempArray) >= 1 ) { $ColorR = $tempArray[0]  ; }
		if ( count($tempArray) >= 2 ) { $ColorG = $tempArray[1]+0; }
		if ( count($tempArray) >= 3 ) { $ColorB = $tempArray[2]  ; }
		$tempArray = explode("(",$ColorR);
		if ( count($tempArray) >= 2 ) { $ColorR = $tempArray[1]+0; }
		$tempArray = explode(")",$ColorB);
		if ( count($tempArray) >= 1 ) { $ColorB = $tempArray[0]+0; }
		return array($ColorR,$ColorG,$ColorB);
	}
		
	public static function RoundForDisplay($input) {
		return CodonOpt_Utility::RoundToSignificantFigure($input,4);
	}
}
?>