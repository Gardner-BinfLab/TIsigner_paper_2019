<?php
//This class is meant to generate the text for SVGs
class CodonOpt_SVGmaker_Pareto {
	//Internal Variables
	private $EncryptID;
	private $ColumnList = array();
	public function getColumnList() {
		return $this->ColumnList;
	}

	//Add a Column to List of Columns
	public function AddColumn($InputColumn) {
		if ( isset($InputColumn) ) {
			if ( get_class ($InputColumn) == "CodonOpt_ViewResultSummary_Column" ) {
				array_push($this->ColumnList,$InputColumn);
				if ($InputColumn->getPlot_XY_Axis()) {	//If input column can be plotted on axis
					array_push(							//Add it to Axis List
						$this->AxisList,
						(count($this->ColumnList)-1)	//The number of the column
					);
				}
			} else {
				die ("Error! Input Column is not CodonOpt_ViewResultSummary_Column class");
			}
		} else {
			die("No valid input Column was given!");
		}
	}
	
	//Code Column is handled seperately
	private $CodeColumn = null;
	public function SetCodeColumn($InputColumn) {
		if ( isset($InputColumn) ) {
			if ( get_class ($InputColumn) == "CodonOpt_ViewResultSummary_Column" ) {
				$this->CodeColumn = $InputColumn;
			} else {
				die ("Error! Input Column is not CodonOpt_ViewResultSummary_Column class");
			}
		} else {
			die("No valid input Column was given!");
		}
	}
	
	//Link Column handled seperately
	private $LinkColumn = null;
	public function SetLinkColumn($InputColumn) {
		if ( isset($InputColumn) ) {
			if ( get_class ($InputColumn) == "CodonOpt_ViewResultSummary_Column" ) {
				$this->LinkColumn = $InputColumn;
			} else {
				die ("Error! Input Column is not CodonOpt_ViewResultSummary_Column class");
			}
		} else {
			die("No valid input Column was given!");
		}
	}
	
	//List of Columns that can be plotted on axis
	private $AxisList = array();
	public function getAxisList() {
		return $this->AxisList;
	}
	
	//Function to get the Default Color Code
	public function getDefaultColorCode() {
		if ( count($this->AxisList) >= 3 ) {	//If there are at least 3 plottable axis
			return $this->AxisList[2];			//Return the 3rd axis
		} else {								//Otherwise only 2 plottable axis
			//Go through the columns
			foreach ($this->ColumnList as $Num=>$ColumnObject) {
				if (! $ColumnObject->getPlot_XY_Axis() ) {	//If there are any non-plottable axis
					return $Num;							//Use this axis as color code
				}
			}
		}
		return null;
	}
	
	//Constructor
	public function CodonOpt_SVGmaker_Pareto($ViewResultSummary) {
		if ( isset($ViewResultSummary) ) {
			if ( get_class ($ViewResultSummary) == "CodonOpt_Controller_ViewResultSummaryAll" ) {
				//Do Nothing
			} else {
				die ("Error! Input Column is not CodonOpt_Controller_ViewResultSummaryAll class");
			}
		} else {
			die("No valid ViewResultSummary Controller was given!");
		}	
		
		//Transfer Data into SVG Maker
		$this->EncryptID = 									//Save Input Variables
			$ViewResultSummary->getEncrypt_Id();
		$this->SetCodeColumn(								//Save Code Column
			$ViewResultSummary->getTextCodeColumn()
		);
		$this->SetLinkColumn(								//Save Link Column
			$ViewResultSummary->getLinkCodeColumn()
		);
		$TableColumns = $ViewResultSummary->getTableColumns();
		foreach ($TableColumns as $Num=>$ColumnObject) {	//Transfer Data Columns into SVG Maker
			$this->AddColumn($ColumnObject);
		}
	}

	//Variables
	private static $CirclePointRadiusSize = 5;
	private static $PolylinePointRadiusSize = 8;
	private static $PointWidth_Normal = 1;
	private static $PointWidth_Mouseover = 3;
	private static $StatTablePadding = 5;
	//Old Values for 3 columns
	//private static $StatTableColumnCount = 3;
	//private static $StatTableLabel_X_Width = 160;
	//private static $StatTableValue_X_Width = 90;
	private static $StatTableColumnCount = 2;
	private static $StatTableLabel_X_Width = 280;
	private static $StatTableValue_X_Width = 90;
	
	//Create SVG chart
	public function GenerateSVGForHeaderIntegers($Xaxis, $Yaxis, $ColorAxis=null, $ShowHideOriginal=false) {
		//Control Variables for this Function
		//Refer to the associated powerpoint on each parameter
		/* How much space to give the Code/Name cell in the Display Label. Set it to 1 to give Code/Name cell the normal amount of space. Set it to CodonOpt_SVGmaker_Pareto::$StatTableColumnCount, to give the Code/Name cell an entire row to itself (e.g. for long names). */
		$DisplayLabelCodeOffset = CodonOpt_SVGmaker_Pareto::$StatTableColumnCount;	
		$PaddingTop = 20 + $this::GetDataDescriptorCoord_Y( count($this->ColumnList) + $DisplayLabelCodeOffset - 1 );
		$PaddingLeft = 80;
		$PaddingRight = 40;
		$PaddingBottom = 25;				//Space for X axis labels
		$PaddingColorAxis = 55;				//Space for Color Axis label
		$ChartWidth = 640;
		$ChartHeight = 350;
		$ChartInnerPadding = 15;
		
		$StrokeColor_Default = "black";		//Default Stroke colour
		$StrokeColor_Mouseover = "#777777";	//Color on Mouseover
		$FillColor_Default = "none";		//Default Fill colour (without golour gradient)
		
		//Axis and Label Settings
		$LabelSize = 5;
		$TextColor_Default = "black";
		$TextSize_Default = 16;				//MUST use one font size, as Internet Explorer, Firefox and Opera cannot parse font size properly
		$LineColor_Axis = "silver";
		$LineColor_StatTable = "silver";
		$Label_Text_Title_Space = 5;
		$Xaxis_Label_Text_Offset = 20;		//Offset for X axis labels (shift left)
		//$Yaxis_Label_Text_Offset = 20;	//Offset for Y axis labels (shift down)-Disabled for horizontal labels
		$Additional_Title_Offset = 35;		//Addtional offset for titles
		
		//Convert to Integer and run error checks
		$XaxisInt = intval($Xaxis);
		$YaxisInt = intval($Yaxis);
		if ( $XaxisInt > count($this->ColumnList) ) {
			die ( "XaxisInt exceeds Data Points: ".$XaxisInt." and ".count($this->ColumnList) );
		}
		if ( $YaxisInt > count($this->ColumnList) ) {
			die ( "YaxisInt exceeds Data Points: ".$YaxisInt." and ".count($this->ColumnList) );
		}
		$ColorAxisInt = null;						//default is null
		if ( isset($ColorAxis) ) {					//If Color Axis is set
			$ColorAxisInt = intval($ColorAxis);		//Convert into Integer
			if ( $ColorAxisInt > count($this->ColumnList) ) {
				die ( "ColorAxisInt exceeds Data Points: ".$ColorAxisInt." and ".count($this->ColumnList) );
			}
		}		

		//Find and Check Number of Rows
		$RowCount = null;
		foreach ($this->ColumnList as $Num=>$ColumnObject) {
			if ( isset($RowCount) ) {	//If Row count defined, perform error checks
				if ( $RowCount != $ColumnObject->CountDataPoints() ) {
					die ("Row Count Does Not Match across Columns: ".$RowCount." and ".$ColumnObject->CountDataPoints()." for ".$Num);
				}
			} else {					//Otherwise Row Count not yet defined, define it
				$RowCount = $ColumnObject->CountDataPoints();
			}
		}
		
		//First Find Max/MinX and Max/MinY first
		$MaxX = -100000;
		$MinX = 100000;
		$MaxY = -100000;
		$MinY = 100000;
		$MaxColor = -100000;
		$MinColor = 100000;
		for ($num=0; $num<$RowCount; $num++) {
			if (! $ShowHideOriginal) {					//If we want to hide original (ShowHide = false)
				if ( $this->CheckIfOriginal($num) ) {	//If this is original
					continue;							//Skip
				}
			}
		
			$XDataPoint = $this->ColumnList[$XaxisInt]->GetDataPoint($num);
			$YDataPoint = $this->ColumnList[$YaxisInt]->GetDataPoint($num);
			
			if ($MaxX < $XDataPoint) {
				$MaxX = $XDataPoint;
			}
			if ($MinX > $XDataPoint) {
				$MinX = $XDataPoint;
			}
			if ($MaxY < $YDataPoint) {
				$MaxY = $YDataPoint;
			}
			if ($MinY > $YDataPoint) {
				$MinY = $YDataPoint;
			}
			if ( isset($ColorAxisInt) ) {
				$ColorDataPoint = $this->ColumnList[$ColorAxisInt]->GetDataPoint($num);
				if ($MaxColor < $ColorDataPoint) {
					$MaxColor = $ColorDataPoint;
				}
				if ($MinColor > $ColorDataPoint) {
					$MinColor = $ColorDataPoint;
				}				
			}
		}
		
		//Seed the Output Arrays
		$OutArray_Header = array();		//Header <svg>
		$OutArray_AxisLabels = array();	//For Axis Labels
		$OutArray_Legend = array();
		$OutArray_Highlight = array();	//Highlights for User Defined Sequences
		$OutArray_Points = array();		//Array for Data points and Appear Text
		$OutArray_Footer = array();		//Footer </svg>
		$TempStr = 						//SVG Header
	"<svg width='".($PaddingLeft+$ChartWidth+$PaddingRight).
	"px' height='".($PaddingTop+$ChartHeight+$PaddingBottom+$PaddingColorAxis)."px' xmlns='http://www.w3.org/2000/svg' version='1.1'>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//CSS Style Sheet
"<defs>
	<style type='text/css'><![CDATA[
		text { font-family: tahoma, arial, helvetica, sans-serif; fill: ".$TextColor_Default."; font-size: ".$TextSize_Default."px; }
		.scatter_plot_point {
			stroke-width: ".CodonOpt_SVGmaker_Pareto::$PointWidth_Normal.";
			stroke: ".$StrokeColor_Default.";
		}
		.scatter_plot_point:hover {
			stroke-width: ".CodonOpt_SVGmaker_Pareto::$PointWidth_Mouseover.";
			stroke: ".$StrokeColor_Mouseover.";
		}
		.scatter_plot_point:hover ~ .scatter_plot_appear_text {
			visibility: visible
		}
	]]></style>
</defs>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//Background Rectangle
	"<rect id='background_rectangle' width='".($PaddingLeft+$ChartWidth+$PaddingRight)."' height='".($PaddingTop+$ChartHeight+$PaddingBottom+$PaddingColorAxis)."' fill='white' stroke='none' stroke-width='0' x='0' y='0'/>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//Graph Title
	"<text id='graph_title' x='0' y='20' font-weight='bold' >Pareto Plot</text>";
		array_push($OutArray_Header,$TempStr);
		$TempStr = 						//Graph Subtext
	"<text id='graph_subtitle' x='0' y='40' >(Mouseover a point to view its statistics in the table below. Click on a point to go to its detailed results)</text>";
		array_push($OutArray_Header,$TempStr);
		
		{	//Add the X Axis and Labels
			$Label_y_coord = $PaddingTop+$ChartHeight;
			$Text_y_coord = $PaddingTop+$ChartHeight+$LabelSize+$TextSize_Default+$Label_Text_Title_Space;
			$TempStr = "<line id='xaxis_line' fill='none' stroke='".$LineColor_Axis."' stroke-width='1pt' x1='".$PaddingLeft."' y1='".($PaddingTop+$ChartHeight)."' x2='".($PaddingLeft+$ChartWidth)."' y2='".($PaddingTop+$ChartHeight)."'></line>";
			array_push($OutArray_AxisLabels,$TempStr);
			$TempStr = "<text id='x_axis_title' font-weight='bold' x='".($PaddingLeft+($ChartWidth/2)-$Xaxis_Label_Text_Offset-$Additional_Title_Offset)."' y='".$Text_y_coord."' >".($this->ColumnList[$XaxisInt]->getChartTitle())."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
			
			$TempStr = "<line id='xlabel_min_bar' x1='".($PaddingLeft+$ChartInnerPadding)."' y1='".$Label_y_coord."' x2='".($PaddingLeft+$ChartInnerPadding)."' y2='".($Label_y_coord+$LabelSize)."' fill='none' stroke='".$LineColor_Axis."' stroke-width='1pt'></line>";
			array_push($OutArray_AxisLabels,$TempStr);
			$TempStr = "<text id='xlabel_min_text' x='".($PaddingLeft+$ChartInnerPadding-$Xaxis_Label_Text_Offset)."' y='".$Text_y_coord."' >".$MinX."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
			
			$DisplayMaxX = $MaxX;
			if (($MaxX-$MinX) == 0) {
				$DisplayMaxX = $MinX + 1;
			}
			$TempStr = "<line id='xlabel_max_bar' x1='".($PaddingLeft+$ChartWidth-$ChartInnerPadding)."' y1='".$Label_y_coord."' x2='".($PaddingLeft+$ChartWidth-$ChartInnerPadding)."' y2='".($Label_y_coord+$LabelSize)."' fill='none' stroke='".$LineColor_Axis."' stroke-width='1pt'></line>";
			array_push($OutArray_AxisLabels,$TempStr);
			$TempStr = "<text id='xlabel_max_text' x='".($PaddingLeft+$ChartWidth-$ChartInnerPadding-$Xaxis_Label_Text_Offset)."' y='".$Text_y_coord."' >".$DisplayMaxX."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
		}
		
		
		{	//Add the Y Axis and Labels
			$Text_x_coord = $PaddingLeft-$LabelSize-$TextSize_Default-$Label_Text_Title_Space;		
			$TempStr = "<line id='yaxis_line' fill='none' stroke='".$LineColor_Axis."' stroke-width='1pt' x1='".$PaddingLeft."' y1='".$PaddingTop."' x2='".$PaddingLeft."' y2='".($PaddingTop+$ChartHeight)."'></line>";
			array_push($OutArray_AxisLabels,$TempStr);
			//When using Transform rotation, flip X and Y, and make new X (which is flipped Y) negative
			$TempStr = "<text id='y_axis_title' font-weight='bold' x='-".($PaddingTop+($ChartHeight/2)+$Additional_Title_Offset)."' y='".($PaddingLeft-20)."' transform='rotate(270)'>".($this->ColumnList[$YaxisInt]->getChartTitle())."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
			
			//array_push($OutArray_AxisLabels,"<text x='-80' y='80' transform='rotate(270)'>Hello1</text>");
			//array_push($OutArray_AxisLabels,"<text x='100' y='100' transform='translate(100,100)rotate(270)'>Hello2</text>");
			//array_push($OutArray_AxisLabels,"<text x='120' y='120' transform='rotate(270 120,120)'>Hello3</text>");
			
			$DisplayMaxY = $MaxY;
			if (($MaxY-$MinY) == 0) {
				$DisplayMaxY = $MinY + 1;
			}			
			$TempStr = "<line id='ylabel_max_bar' x1='".$PaddingLeft."' y1='".($PaddingTop+$ChartInnerPadding)."' x2='".($PaddingLeft-$LabelSize)."' y2='".($PaddingTop+$ChartInnerPadding)."' fill='none' stroke='".$LineColor_Axis."' stroke-width='1pt'></line>";
			array_push($OutArray_AxisLabels,$TempStr);
			$TempStr = "<text id='ylabel_max_text' x='".(0)."' y='".($PaddingTop+$ChartInnerPadding+($TextSize_Default/2))."' >".$DisplayMaxY."</text>";
			//Original: Rotate 270
			//$TempStr = "<text id='ylabel_max_text' transform='translate(".($Text_x_coord+$TextSize_Default).",".($PaddingTop+$ChartInnerPadding+$Yaxis_Label_Text_Offset).")rotate(270)' >".$DisplayMaxY."</text>";
			array_push($OutArray_AxisLabels,$TempStr);

			$TempStr = "<line id='ylabel_min_bar' x1='".$PaddingLeft."' y1='".($PaddingTop+$ChartHeight-$ChartInnerPadding)."' x2='".($PaddingLeft-$LabelSize)."' y2='".($PaddingTop+$ChartHeight-$ChartInnerPadding)."' fill='none' stroke='".$LineColor_Axis."' stroke-width='1pt'></line>";
			array_push($OutArray_AxisLabels,$TempStr);
			$TempStr = "<text id='ylabel_min_text' x='".(0)."' y='".($PaddingTop+$ChartHeight-$ChartInnerPadding+($TextSize_Default/2))."' >".$MinY."</text>";
			//Original: Rotate 270
			//$TempStr = "<text id='ylabel_min_text' transform='translate(".($Text_x_coord+$TextSize_Default).",".($PaddingTop+$ChartHeight-$ChartInnerPadding+$Yaxis_Label_Text_Offset).")rotate(270)' >".$MinY."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
		}

		if ( isset($ColorAxisInt) ) {			//If there is a Colour Axis
			//Add Colour Axis and Labels
			
			$CentreAnchorX = $PaddingLeft+$ChartWidth-$ChartInnerPadding-$Xaxis_Label_Text_Offset - 127 - $LabelSize;
			$Scale_y_coord = $PaddingTop+$ChartHeight+$PaddingBottom+$LabelSize+$TextSize_Default+7;

			$TempStr = "<text id='colorlabel_title' x='".($CentreAnchorX)."' y='".($Scale_y_coord)."' text-anchor='middle'>".($this->ColumnList[$ColorAxisInt]->getChartTitle())." Color Gradient</text>";
			array_push($OutArray_AxisLabels,$TempStr);
			//Colour Code Scale
			for ($num=0; $num<256; $num++) {	//Go from 0-255
				$TempStr = "<line x1='".($CentreAnchorX-127+$num)."' y1='".($Scale_y_coord+$LabelSize)."' x2='".($CentreAnchorX-127+$num)."' y2='".($Scale_y_coord+$TextSize_Default+$LabelSize)."' fill='none' stroke='".$this::GetColorForIntegerBetween_0_255($num)."' stroke-width='1pt'></line>";
				array_push($OutArray_AxisLabels,$TempStr);
			}
			
			$TempStr = "<text id='colorlabel_min_text' x='".($CentreAnchorX-127-$LabelSize)."' y='".($Scale_y_coord+$TextSize_Default+$LabelSize)."' text-anchor='end'>".$MinColor."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
			
			$DisplayMaxColor = $MaxColor;
			if (($MaxColor-$MinColor) == 0) {
				$DisplayMaxColor = $MinColor + 1;
			}
			$TempStr = "<text id='colorlabel_max_text' x='".($CentreAnchorX+127+$LabelSize)."' y='".($Scale_y_coord+$TextSize_Default+$LabelSize)."' text-anchor='start'>".$DisplayMaxColor."</text>";
			array_push($OutArray_AxisLabels,$TempStr);
		}
		
		//Add the Statistics Display Table
		{	//Add the Label Names	
			$TempStr = 												//First Code Column
				"<text id='datapointdetail_serial' x='".$this::GetDataDescriptorCoord_X(0)."' y='".$this::GetDataDescriptorCoord_Y(0)."' >Name</text>";
			array_push($OutArray_Legend,$TempStr);		
			for ($num=0; $num<count($this->ColumnList); $num++) {	//Then all the other columns
				$TempStr = "<text id='datapointdetail_".$num."' x='".$this::GetDataDescriptorCoord_X($num+$DisplayLabelCodeOffset)."' y='".$this::GetDataDescriptorCoord_Y($num+$DisplayLabelCodeOffset)."' >".($this->ColumnList[$num]->getChartTitle())."</text>";
				array_push($OutArray_Legend,$TempStr);
			}
		
			//Add the Table Lines around Display Table
			$AdditionalValueXLineShift = -5;
			$TopYCoord = 10000;
			$MiddleYCoord = 0;
			$BottomYCoord = -10000;
			$FarRightX = 								//This is the X-coord for the right edge of the table
				$this::GetDataDescriptorCoord_X(CodonOpt_SVGmaker_Pareto::$StatTableColumnCount-1) + CodonOpt_SVGmaker_Pareto::$StatTableLabel_X_Width + CodonOpt_SVGmaker_Pareto::$StatTableValue_X_Width;
			$StatTableRowCount = ceil(					//Number of Rows in table (not including Name)
				count($this->ColumnList) /
				CodonOpt_SVGmaker_Pareto::$StatTableColumnCount
			);		
			for ($num=-1; $num<($StatTableRowCount+1); $num++) {			//Start from -1 for top line, end at +1 for bottom line
				$yCoord = 
					$this::GetDataDescriptorCoord_Y($num*CodonOpt_SVGmaker_Pareto::$StatTableColumnCount) + CodonOpt_SVGmaker_Pareto::$StatTablePadding;
				if ($TopYCoord > $yCoord) { $TopYCoord = $yCoord; }			//Save top Y coord
				if ($num == 0) { $MiddleYCoord = $yCoord; }					//Save Middle Y coord
				if ($BottomYCoord < $yCoord) { $BottomYCoord = $yCoord; }	//Save bottom Y coord
				$TempStr = "<line id='stattablerowline".$num."' x1='".(1)."' y1='".$yCoord."' x2='".$FarRightX."' y2='".$yCoord."' fill='none' stroke='".$LineColor_StatTable."' stroke-width='1pt'></line>";
				array_push($OutArray_Legend,$TempStr);
			}
			for ($numA=0; $numA<CodonOpt_SVGmaker_Pareto::$StatTableColumnCount; $numA++) {
				$yCoord = $MiddleYCoord;					//Default use middle Y coord
				if ($numA == 0) { $yCoord = $TopYCoord; }	//Except for first cell
				$xCoord = $this::GetDataDescriptorCoord_X($numA) - CodonOpt_SVGmaker_Pareto::$StatTablePadding + 1;
				$TempStr = "<line id='stattablecolline_main_".$numA."' x1='".$xCoord."' y1='".$yCoord."' x2='".$xCoord."' y2='".$BottomYCoord."' fill='none' stroke='".$LineColor_StatTable."' stroke-width='1pt'></line>";
				array_push($OutArray_Legend,$TempStr);	
				$TempStr = "<line id='stattablecolline_sub_".$numA."' x1='".($xCoord + $AdditionalValueXLineShift + CodonOpt_SVGmaker_Pareto::$StatTableLabel_X_Width)."' y1='".$yCoord."' x2='".($xCoord + $AdditionalValueXLineShift + CodonOpt_SVGmaker_Pareto::$StatTableLabel_X_Width)."' y2='".$BottomYCoord."' fill='none' stroke='".$LineColor_StatTable."' stroke-width='1pt'></line>";
				array_push($OutArray_Legend,$TempStr);
			}
			$TempStr = "<line id='stattablecolline_right' x1='".($FarRightX-1)."' y1='".$TopYCoord."' x2='".($FarRightX-1)."' y2='".$BottomYCoord."' fill='none' stroke='".$LineColor_StatTable."' stroke-width='1pt'></line>";
			array_push($OutArray_Legend,$TempStr);
		}
		
		{	//Add Datapoint Symbol Legend
			$SymbolLeftPadding = 17;
			$SymbolWidth = 2;
			$Scale_y_coord = $PaddingTop+$ChartHeight+$PaddingBottom+$LabelSize+$TextSize_Default+7;
			$TempStr = "<text id='Optimized_Sequence_label' x='".($SymbolLeftPadding+$SymbolWidth)."' y='".($Scale_y_coord)."' >Optimized Sequence</text>";
			array_push($OutArray_Legend,$TempStr);
			$TempStr = $this::GenerateInbuiltPoint (
				($SymbolLeftPadding - 								//Xcoord
					max(CodonOpt_SVGmaker_Pareto::$CirclePointRadiusSize,CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize)
				),
				($Scale_y_coord - 									//$Ycoord
					max(CodonOpt_SVGmaker_Pareto::$CirclePointRadiusSize,CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize)
				),
				$FillColor_Default,									//FillColor
				"Optimized_Sequence_point",							//DataPointID
				"Optimized Sequence"								//$DataPointTooltip
			);
			array_push($OutArray_Legend,$TempStr);
			$TempStr = "<text id='User_Defined_Sequence_label' x='".($SymbolLeftPadding+$SymbolWidth)."' y='".($Scale_y_coord+$TextSize_Default+$LabelSize)."' >User Defined Sequence</text>";
			array_push($OutArray_Legend,$TempStr);
			$TempArray = $this::GenerateUserDefinedXML_Point_Highlight (
				($SymbolLeftPadding - 								//Xcoord
					max(CodonOpt_SVGmaker_Pareto::$CirclePointRadiusSize,CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize)
				),
				($Scale_y_coord+$TextSize_Default+$LabelSize - 		//$Ycoord
					max(CodonOpt_SVGmaker_Pareto::$CirclePointRadiusSize,CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize)
				),	
				$FillColor_Default,									//FillColor
				"User_Defined_Sequence_point",						//DataPointID
				"User Defined Sequence"								//$DataPointTooltip
			);
			array_push( $OutArray_Legend, $TempArray[0] );			//XML point
			array_push( $OutArray_Highlight, $TempArray[1] );		//Highlight (Disable)
		}

		//Echo Empty Link (For some unknown reason, when the link around each datapoint is removed, then this Empty Link with '&' in the target, at this location, is required for Chrome and Firefox to work )		
		//$TempStr = "<a xlink:href='&'></a>";
		//array_push($OutArray_Points,$TempStr);
		
		//Add The Data Points
		for ($numA=0; $numA<$RowCount; $numA++) {
			$PointDataArray = array();					//Array to hold data for this point
			if (! $ShowHideOriginal) {					//If we want to hide original (ShowHide = false)
				if ( $this->CheckIfOriginal($numA) ) {	//If this is original
					continue;							//Skip
				}
			}
			$DataPointID = "DataPointMarker".$numA;		//Internal Datapoint ID (based on independent serial)
			$DisplayCodeText = $this->CodeColumn->getDataPoint($numA);
			$BaseFillColor = $FillColor_Default;			//Default colour
			if ( isset($ColorAxisInt) ) {				//If using Colour Axis
				$Fraction = $this::DeriveCoordFraction(
					$MinColor, $MaxColor, $this->ColumnList[$ColorAxisInt]->GetDataPoint($numA)
				);
				$TempInt = floor($Fraction*255);
				$BaseFillColor = $this::GetColorForIntegerBetween_0_255($TempInt);
			}
			//Generate Tooltop String to display when mouse over
			$DataPointTooltip = "Name: ".$DisplayCodeText." \n";
			foreach ($this->ColumnList as $numB=>$ColumnObject) {
				$DataPointTooltip .= $ColumnObject->getChartTitle().": ".$ColumnObject->GetDataPoint($numA)." \n";
			}
		
			$Xcoord = $PaddingLeft + $ChartInnerPadding + 
				(	$this::DeriveCoordFraction(
						$MinX, $MaxX, $this->ColumnList[$XaxisInt]->GetDataPoint($numA)
					) * 
					($ChartWidth - $ChartInnerPadding - $ChartInnerPadding)
				);
			$Ycoord = $PaddingTop + $ChartHeight - $ChartInnerPadding - 
				(	$this::DeriveCoordFraction(
						$MinY, $MaxY, $this->ColumnList[$YaxisInt]->GetDataPoint($numA)
					) *
					($ChartHeight - $ChartInnerPadding - $ChartInnerPadding)
				);
			
			//Generate Point
			$TempStr = "<a class='scatter_plot_point' ";
			$TempStr .= "target='_blank' ";
			$TempStr .= "xlink:href='viewresultdetail.php?".$this->LinkColumn->getDataPoint($numA)."'>";
			array_push($PointDataArray,$TempStr);
			if ( preg_match('/^UDS/',$DisplayCodeText) ) {	//If this is user sequence
				$TempArray = 								//Draw a User Defined
					$this::GenerateUserDefinedXML_Point_Highlight ($Xcoord,$Ycoord,$BaseFillColor,$DataPointID,$DataPointTooltip);
				$TempStr = $TempArray[0];
				array_push($OutArray_Highlight,$TempArray[1]);
			} else {										//Otherwise this is our optimized sequence
				$TempStr = $this::GenerateInbuiltPoint(		//Draw Point
					$Xcoord,$Ycoord,$BaseFillColor,$DataPointID,$DataPointTooltip
				);
			}
			array_push($PointDataArray,$TempStr);
			$TempStr = "</a>";
			array_push($PointDataArray,$TempStr);
			
			//Generate Mouseover Text
			/*X_Width_OffSet: For the details on mouseover segment, This controls how far the actual numerical values are from the detail name. */
			//Popup Text for Serial Number
			$TempStr = "<text visibility='hidden' id='popuptext_code_".$DataPointID."' x='".($this::GetDataDescriptorCoord_X(0)+CodonOpt_SVGmaker_Pareto::$StatTableLabel_X_Width)."' y='".$this::GetDataDescriptorCoord_Y(0)."' class='scatter_plot_appear_text' >";
			$TempStr .= $DisplayCodeText;
			//$TempStr .= "<set attributeName='visibility' to='visible' begin='".$DataPointID.".mouseover' end='".$DataPointID.".mouseout;background_rectangle.mouseover'/>";
			$TempStr .= "</text>";
			array_push($PointDataArray,$TempStr);
			//Popup Text for all the other Attributes
			for ($numB=0; $numB<count($this->ColumnList); $numB++) {
				$TempStr = "<text visibility='hidden' id='popuptext_".$numB."_".$DataPointID."' x='".($this::GetDataDescriptorCoord_X($numB+$DisplayLabelCodeOffset)+CodonOpt_SVGmaker_Pareto::$StatTableLabel_X_Width)."' y='".$this::GetDataDescriptorCoord_Y($numB+$DisplayLabelCodeOffset)."' class='scatter_plot_appear_text' >";
				$TempStr .= $this->ColumnList[$numB]->getDataPoint($numA);
				//$TempStr .= "<set attributeName='visibility' to='visible' begin='".$DataPointID.".mouseover' end='".$DataPointID.".mouseout;background_rectangle.mouseover'/>";
				$TempStr .= "</text>";
				array_push($PointDataArray,$TempStr);
			}
			array_push( $OutArray_Points,(		//
				"<g>\n".implode("\n",$PointDataArray)."</g>"
			) );
		}

		array_push($OutArray_Footer,"</svg>");		//Close the SVG
		return (
			implode ("\n",$OutArray_Header).		//Background Rectangle
			implode ("\n",$OutArray_Highlight).		//Rear highlights
			implode ("\n",$OutArray_AxisLabels).
			implode ("\n",$OutArray_Legend).
			implode ("\n",$OutArray_Points).
			implode ("\n",$OutArray_Footer)
		);
	}
	//===============
	//Other Functions
	private static function GenerateInbuiltPoint (
		$Xcoord,$Ycoord,$FillColor,$DataPointID,$DataPointTooltip
	) {
		return CodonOpt_SVGmaker_Pareto::GenerateCircleXML (
			$Xcoord,$Ycoord,$FillColor,$DataPointID,$DataPointTooltip
		);
	}	
	private static function GenerateUserDefinedXML_Point_Highlight (
		$Xcoord,$Ycoord,$FillColor,$DataPointID,$DataPointTooltip
	) {
		$TempPointStr = CodonOpt_SVGmaker_Pareto::GeneratePolygonXML (
			$Xcoord,$Ycoord,$FillColor,$DataPointID,$DataPointTooltip
		);
		$TempHighlightStr = 	//Highlight should be behind
			"<circle cx='".$Xcoord."' cy='".$Ycoord."' r='".CodonOpt_SVGmaker_Pareto::$CirclePointRadiusSize."' stroke='#00ff00' stroke-width='8' fill='none' id='".$DataPointID."_highlight' />";
		return array($TempPointStr,"");						//Return Point BUT NOT highlight
		//return array($TempPointStr,$TempHighlightStr);	//Return Point and Highlight in array
	}
	//Generate Polyline Shape
	private static function GeneratePolygonXML (
		$Xcoord,$Ycoord,$FillColor,$DataPointID,$DataPointTooltip
	) {
		$polylinecoord = "";
		{	//Generate coordinates for polyline
			$LeftX = $Xcoord - CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize;
			$RightX = $Xcoord + CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize;
			$HighY = $Ycoord - CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize;
			$LowY = $Ycoord + CodonOpt_SVGmaker_Pareto::$PolylinePointRadiusSize;
			//Normal Triangle
			$polylinecoord = (
				$Xcoord.",".$HighY." ".
				$LeftX.",".$LowY." ".
				$RightX.",".$LowY
			);
			/*//Inverted Triangle
			$polylinecoord = (
				$Xcoord.",".$LowY." ".
				$LeftX.",".$HighY." ".
				$RightX.",".$HighY
			);
			*/
		}
		$TempStr = "<polygon points='".$polylinecoord."' class='scatter_plot_point' fill='".$FillColor."' id='".$DataPointID."'>";
		$TempStr .= 
			CodonOpt_SVGmaker_Pareto::GenerateDataPointInnerXML ($DataPointTooltip,$DataPointID);
		$TempStr .= "</polygon>";
		return $TempStr;
	}
	//Generate Circle Shape
	private static function GenerateCircleXML (
		$Xcoord,$Ycoord,$FillColor,$DataPointID,$DataPointTooltip
	) {
		$TempStr = "<circle cx='".$Xcoord."' cy='".$Ycoord."' r='".(CodonOpt_SVGmaker_Pareto::$CirclePointRadiusSize)."' class='scatter_plot_point' fill='".$FillColor."' id='".$DataPointID."'>";
		$TempStr .= 
			CodonOpt_SVGmaker_Pareto::GenerateDataPointInnerXML ($DataPointTooltip,$DataPointID);
		$TempStr .= "</circle>";
		return $TempStr;
	}
	
	//Generates Title and Mouseover functions for both polyline and circle points
	private static function GenerateDataPointInnerXML ($DataPointTooltip,$DataPointID) {
		$TempStr = "<title>".$DataPointTooltip."</title>";
		return $TempStr;
	}
	
	private static function DeriveCoordFraction($InMin,$InMax,$InValue) {
		if (($InMax-$InMin) == 0) {	#If the denominator is zero
			return 0;				#Return zero, to prevent divide by zero error
		} else {					#Otherwise the denominator is non-zero
			return ( ($InValue-$InMin)/($InMax-$InMin) );
		}
	}
	
	private static function GetDataDescriptorCoord_X($InNum) {
		return (
			CodonOpt_SVGmaker_Pareto::$StatTablePadding+(
				($InNum%CodonOpt_SVGmaker_Pareto::$StatTableColumnCount) * 
				(CodonOpt_SVGmaker_Pareto::$StatTableLabel_X_Width + CodonOpt_SVGmaker_Pareto::$StatTableValue_X_Width)
			)
		);
	}
	private static function GetDataDescriptorCoord_Y($InNum) {
		return (60 + CodonOpt_SVGmaker_Pareto::$StatTablePadding + 
			(
				(17+CodonOpt_SVGmaker_Pareto::$StatTablePadding)*
				( floor($InNum/CodonOpt_SVGmaker_Pareto::$StatTableColumnCount) )
			) 
		);
	}
	
	private static function GetColorForIntegerBetween_0_255($Input) {
		$TempInt = intval($Input);
		;;;;;;if ($TempInt < 0) {
			die("Error: GetColorForIntegerBetween_0_255: TempInt is less than 0: ".$TempInt);
		} elseif ($TempInt > 255) {
			die("Error: GetColorForIntegerBetween_0_255: TempInt is greater than 255: ".$TempInt);
		}
		return 
			"rgb(".
			($TempInt).",".
			(255-$TempInt).",".
			//"00,".
			(255-$TempInt).")"
		;
		
	}
	
	//This function checks if the is the Original Nucleotide Input Sequence
	private function CheckIfOriginal($InputNum) {
		$DisplayCodeText = $this->CodeColumn->getDataPoint($InputNum);
		if ( preg_match('/^0/',$DisplayCodeText) ) {	//If it starts with 0
			return true;								//This should be Original Nucleotide Input Sequence
		} else {
			return false;
		}		
	}
}
?>