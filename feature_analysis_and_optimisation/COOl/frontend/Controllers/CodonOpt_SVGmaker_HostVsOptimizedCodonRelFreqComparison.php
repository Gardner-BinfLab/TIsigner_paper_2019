<?php
//This class makes SVG for the Host Vs Optimized Codon Relative Frequency Chart
class CodonOpt_SVGmaker_HostVsOptimizedCodonRelFreqComparison {
	private $inputLines;
	public function CodonOpt_SVGmaker_HostVsOptimizedCodonRelFreqComparison(
		$inputLines
	) {
		$this->inputLines = $inputLines;	//Save Input Lines
	}

	//Internal Variables
	private static $PerLineTopPadding = 10;
	private static $ChartHeight = 200;
	private static $PerLineBottomPadding = 70;
	private static $LeftPadding = 50;
	private static $BarWidth = 13;
	private static $SpacerWidth = 7;
	private static $TotalWidth = 745;
	private static $XFontSize = 15;
	private static $YFontSize = 15;
	private static $TickSize = 5;
	
	//Generate SVG
	public function GenerateSVG() {
		$NumberOfLines = count($this->inputLines);
		$TotalChartHeight = ($this::$PerLineTopPadding+$this::$ChartHeight+$this::$PerLineBottomPadding) * $NumberOfLines;
		
		$OutputArray = array();
		$TempStr = 
			"<svg width='".($this::$TotalWidth)."px' height='".($TotalChartHeight)."px' xmlns='http://www.w3.org/2000/svg' version='1.1'>";
		array_push($OutputArray,$TempStr);
			"<rect id='background_rectangle' width='".($this::$TotalWidth)."' height='".($TotalChartHeight)."' fill='white' stroke-width='0' stroke='none' x='0' y='0' />";
		array_push($OutputArray,$TempStr);
		
		//Generate SVG for each Line
		for ($num=0; $num<$NumberOfLines; $num++) {
			$tempArray = $this->GenerateSVGForLine($num);
			$OutputArray = array_merge($OutputArray,$tempArray);
		}
		
		//Close SVG
		$TempStr = "</svg>";
		array_push($OutputArray,$TempStr);
		return implode ("\n",$OutputArray);
	}
	
	//Generate the SVG for just one particular line
	public function GenerateSVGForLine($LineNum) {
		$MinYOffset = ($this::$PerLineTopPadding+$this::$ChartHeight+$this::$PerLineBottomPadding) * $LineNum;
		$OutputArray = array();
		$tempLine = $this->inputLines[$LineNum];
		$TempStr = "";
		
		//Generate Y axis
		$TempStr = "<line id='y_axis_line_".$LineNum."' x1='".($this::$LeftPadding)."' y1='".($MinYOffset+$this::$PerLineTopPadding)."' x2='".($this::$LeftPadding)."' y2='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight)."' fill='none' stroke='black' stroke-width='1pt'></line>";
		array_push($OutputArray,$TempStr);	//Y axis Line
		$TempStr = "<line id='y_axis_tick10_".$LineNum."' x1='".($this::$LeftPadding)."' y1='".($MinYOffset+$this::$PerLineTopPadding)."' x2='".($this::$LeftPadding-$this::$TickSize)."' y2='".($MinYOffset+$this::$PerLineTopPadding)."' fill='none' stroke='black' stroke-width='1pt'></line>";	
		array_push($OutputArray,$TempStr);	//1.0 tick mark
		$TempStr = "<text id='y_axis_label10_".$LineNum."' fill='black' x='".(0)."' y='".($MinYOffset+$this::$PerLineTopPadding+($this::$YFontSize/3))."' font-family='courier' font-size='".$this::$YFontSize."' >1.0</text>";
		array_push($OutputArray,$TempStr);	//1.0 label
		$TempStr = "<line id='y_axis_tick05_".$LineNum."' x1='".($this::$LeftPadding)."' y1='".($MinYOffset+$this::$PerLineTopPadding+($this::$ChartHeight/2))."' x2='".($this::$LeftPadding-$this::$TickSize)."' y2='".($MinYOffset+$this::$PerLineTopPadding+($this::$ChartHeight/2))."' fill='none' stroke='black' stroke-width='1pt'></line>";	
		array_push($OutputArray,$TempStr);	//0.5 tick mark	
		$TempStr = "<text id='y_axis_label05_".$LineNum."' fill='black' x='".(0)."' y='".($MinYOffset+$this::$PerLineTopPadding+($this::$ChartHeight/2)+($this::$YFontSize/3))."' font-family='courier' font-size='".$this::$YFontSize."' >0.5</text>";
		array_push($OutputArray,$TempStr);	//0.5 label
		$TempStr = "<line id='y_axis_tick00_".$LineNum."' x1='".($this::$LeftPadding)."' y1='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight)."' x2='".($this::$LeftPadding-$this::$TickSize)."' y2='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight)."' fill='none' stroke='black' stroke-width='1pt'></line>";	
		array_push($OutputArray,$TempStr);	//0.0 tick mark	
		$TempStr = "<text id='y_axis_label00_".$LineNum."' fill='black' x='".(0)."' y='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight+($this::$YFontSize/3))."' font-family='courier' font-size='".$this::$YFontSize."' >0.0</text>";
		array_push($OutputArray,$TempStr);	//0.0 label		
		
		//Generate Charts
		$TotalItemNum = count($tempLine);
		for ($ItemNum=0; $ItemNum<$TotalItemNum; $ItemNum++) {
			$tempItem = $tempLine[$ItemNum];
			$MinXOffset = 									//Calculate Minimum X offset
				$this::$LeftPadding + $this::$SpacerWidth + ($ItemNum*($this::$SpacerWidth+(2*$this::$BarWidth)));
			$HostRelFreqHeight =							//Find Bar Height
				$tempItem->getHostRelFreqForDisplay() * $this::$ChartHeight;	
			$TempStr = "<rect id='HostBar_Line".$LineNum."_Item".$ItemNum."' width='".($this::$BarWidth)."' height='".($HostRelFreqHeight)."' fill='red' stroke-width='0' stroke='none' x='".($MinXOffset)."' y='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight-$HostRelFreqHeight)."' />";
			array_push($OutputArray,$TempStr);				//Host Bar
			$OptSeqRelFreqHeight = 							//Find Bar Height
				$tempItem->getOptSeqRelFreqForDisplay() * $this::$ChartHeight;	
			$TempStr = "<rect id='OptSeqBar_Line".$LineNum."_Item".$ItemNum."' width='".($this::$BarWidth)."' height='".($OptSeqRelFreqHeight)."' fill='blue' stroke-width='0' stroke='none' x='".($MinXOffset+$this::$BarWidth)."' y='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight-$OptSeqRelFreqHeight)."' />";
			array_push($OutputArray,$TempStr);				//Host Bar
		}
		
		//Generate First Row of Labels (Codon
		for ($ItemNum=0; $ItemNum<$TotalItemNum; $ItemNum++) {
			$tempItem = $tempLine[$ItemNum];
			$MinXOffset = 									//Calculate Minimum X offset
				$this::$LeftPadding + $this::$SpacerWidth + ($ItemNum*($this::$SpacerWidth+(2*$this::$BarWidth)));
			$TempStr = "<text id='x_axis_codonlabel_".$tempItem->getCodon()."' fill='black' x='".($MinXOffset)."' y='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight+$this::$XFontSize)."' font-family='courier' font-size='".$this::$XFontSize."' >".$tempItem->getCodon()."</text>";
			array_push($OutputArray,$TempStr);				//Codon label						
		}
		
		//Generate Second Row of Labels
		$PreviousAA = null;
		$CodonCount = 0;
		$MinXOffset = $this::$LeftPadding + $this::$SpacerWidth;
		for ($ItemNum=0; $ItemNum<$TotalItemNum; $ItemNum++) {
			$tempItem = $tempLine[$ItemNum];
			$TripletCode = CodonOpt_Utility::convertAA1to3($tempItem->getAAbase());
			if ($PreviousAA == $TripletCode) {	//If this is the same AA\
				$CodonCount++;					//Add to codon count
			} else {
				if ( isset($PreviousAA) ) {		//If this is not first codon of line
					$this->GenerateHostVsOptimizedCodonRelFreqComparison_PrintAA
						($OutputArray,$CodonCount,$PreviousAA,$MinXOffset,$MinYOffset,$ItemNum);
					$PreviousAA = $TripletCode;	//Reset values
				} else {						//Otherwise this is first codon of line
					$PreviousAA = $TripletCode;	//Save AA
				}
			}
		}
		$this->GenerateHostVsOptimizedCodonRelFreqComparison_PrintAA
			($OutputArray,$CodonCount,$PreviousAA,$MinXOffset,$MinYOffset,$ItemNum);
			
		//Add X Axis
		$TempStr = "<line id='x_axis_line_".$LineNum."' x1='".($this::$LeftPadding)."' y1='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight)."' x2='".($this::$TotalWidth)."' y2='".($MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight)."' fill='none' stroke='black' stroke-width='1pt'></line>";
		array_push($OutputArray,$TempStr);	//Y axis Line
		return $OutputArray;	//Return output for this line
	}
	
	private function GenerateHostVsOptimizedCodonRelFreqComparison_PrintAA(
		&$OutputArray,&$CodonCount,$PreviousAA,&$MinXOffset,$MinYOffset,$ItemNum
	) {
		$tempWidth = 							//Calculate Width
			(($CodonCount+1) * $this::$BarWidth * 2) + ($CodonCount * $this::$SpacerWidth);
		$tempYcoord = $MinYOffset+$this::$PerLineTopPadding+$this::$ChartHeight+$this::$YFontSize+5;
		$TempStr = "<rect id='AABox_".$this::ReplaceAsterisk($PreviousAA)."' width='".($tempWidth)."' height='".($this::$YFontSize+5)."' fill='none' stroke-width='1' stroke='grey' x='".($MinXOffset)."' y='".($tempYcoord)."' />";
		array_push($OutputArray,$TempStr);		//Surrounding Text
		$TempStr = "<text id='x_axis_aalabel_".$this::ReplaceAsterisk($PreviousAA)."' fill='black' x='".($MinXOffset+($tempWidth/2)-($this::$XFontSize)+1)."' y='".($tempYcoord+$this::$XFontSize)."' font-family='courier' font-size='".$this::$XFontSize."' >".$PreviousAA."</text>";
		array_push($OutputArray,$TempStr);		//AA label
		
		//Reset Values
		$CodonCount = 0;
		$MinXOffset = 				//Calculate New Minimum X offset
			$this::$LeftPadding + $this::$SpacerWidth + ($ItemNum*($this::$SpacerWidth+(2*$this::$BarWidth)));
	}
	
	//The Asterisk in the Stop Codon is incompatible when used as element IDs
	private static function ReplaceAsterisk($input) {
		return str_ireplace("*","_AST_",$input);;
	}
}


?>

