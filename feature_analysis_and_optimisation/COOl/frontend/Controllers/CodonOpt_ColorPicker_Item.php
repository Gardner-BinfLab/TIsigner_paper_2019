<?php
require_once "CodonOpt_DTO_color.php";
require_once "CodonOpt_Utility.php";

//This class is meant to hold details on each Codon (Pair)
class CodonOpt_ColorPicker_Item {
	//Attributes and their getters/setters
	private $codon;
	public function getCodon() {	//Read only
		return $this->codon;
	}								//Set during constructor
	public function CodonOpt_ColorPicker_Item($InputCodon) {
		if ( isset($InputCodon) ) {
			$this->codon = $InputCodon;
		} else {
			die("Error: Input Codon not defined!");
		}
	}
	
	//Amino Acid
	private $aabase;
	public function getAAbase() {
		return $this->aabase;
	}
	public function setAAbase($InputAAbase) {
		if ( isset($InputAAbase) ) {
			return $this->aabase = $InputAAbase;
		} else {
			die("Error: Input AAbase not defined!");
		}
	}
	
	//Expression Host 
	//Raw Usage Count
	private $hostRawCount = 0;
	public function getHostRawCount() {
		return $this->hostRawCount;
	}
	public function setHostRawCount($InputRawCount) {
		if ( isset($InputRawCount) ) {
			return $this->hostRawCount = intval($InputRawCount);
		} else {
			die("Error: Input RawCount not defined!");
		}	
	}
	
	//Synonymous Total
	private $hostSynonTotal;
	public function getHostSynonTotal() {
		return $this->hostSynonTotal;
	}
	public function setHostSynonTotal($InputSynonTotal) {
		if ( isset($InputSynonTotal) ) {
			return $this->hostSynonTotal = intval($InputSynonTotal);
		} else {
			die("Error: Input hostSynonTotal not defined!");
		}	
	}
	
	//Relative Frequency (derived value)
	public function getHostRelFreqForDisplay() {
		return CodonOpt_Utility::RoundToSignificantFigure($this->getHostRelFreq(),4);
	}
	
	public function getHostRelFreq() {
		if (
			($this->hostRawCount == 0)	//If raw count is zero
		) {								//To prevent divide by zero errors
			return 0;					//return 0
		} else {						//Otherwise attempt division
			return ($this->hostRawCount/$this->hostSynonTotal);
		}
	}
	
	//Query Usage Count
	private $querySeqUsageCount = 0;
	public function getQuerySeqUsageCount() { return $this->querySeqUsageCount; }
	public function addQuerySeqUsageCount() { $this->querySeqUsageCount++; }
	
	//Optimized Sequence:
	//Raw Usage Count
	private $optSeqRawCount = 0;
	public function getOptSeqRawCount() { return $this->optSeqRawCount; }
	public function addOptSeqRawCount() { $this->optSeqRawCount++; }
	
	//Synonymoues Total
	private $optSeqSynonTotal;
	public function getOptSeqSynonTotal() {
		return $this->optSeqSynonTotal;
	}
	public function setOptSeqSynonTotal($InputSynonTotal) {
		if ( isset($InputSynonTotal) ) {
			return $this->optSeqSynonTotal = intval($InputSynonTotal);
		} else {
			die("Error: Input optSeqSynonTotal not defined!");
		}	
	}	
	
	//Relative Frequency (derived value)
	public function getOptSeqRelFreqForDisplay() {
		return CodonOpt_Utility::RoundToSignificantFigure($this->getOptSeqRelFreq(),4);
	}
	
	public function getOptSeqRelFreq() {
		if (
			($this->optSeqRawCount == 0)	//If raw count is zero
		) {								//To prevent divide by zero errors
			return 0;					//return 0
		} else {						//Otherwise attempt division
			return ($this->optSeqRawCount/$this->optSeqSynonTotal);
		}
	}
	
	//Color (derived value)
	public function getHostColor() {
		return $this::decimalToColor( $this->getHostRelFreq() );
	}
	public function getOptSeqColor() {
		return $this::decimalToColor( $this->getOptSeqRelFreq() );
	}
	
	protected static function decimalToColor($Input) {
		$tempNum = $Input;
		if ($Input>0.49) {		//If from 0.49 (0.7 ^ 2) to 1
			//Rescale to 0.7 to 1
			$tempNum = sqrt($tempNum);
		} else {					//Otherwise from 0 to 0.49
			//Rescale from 0 to 0.7
			$tempNum = $tempNum * 70/49;
		}
		//Convert Decimal into 0-255 integer
		$tempInt = round(
			$tempNum	//Quadruple-root input to make colors scale better
		*255 );
		return CodonOpt_ColorPicker_Item::integerToColor($tempInt);
	}
	
	//Color Centralize conversion of number to oclors
	protected static function integerToColor($Input) {
		$Intput = intval($Input);
		if ($Input < 0) {
			die ("Input was smaller than 0!");
		}
		if ($Input > 255) {
			die ("Input was larger than 255: ".$Input);
		}
		$highcolor = $Intput;			//If input is high, this makes it a high color
		$lowcolor = 255 - $highcolor;	//If input is low, this makes it a low color
		return new CodonOpt_DTO_color ($highcolor,$lowcolor,$lowcolor);
	}
	
	//This generates a color scale.
	public static function getColorScale() {
		$output = array();
		for ($num=0; $num<256; $num ++) {	//For each possible value
			array_push(						//Add that color to array
				$output,
				CodonOpt_ColorPicker_Item::integerToColor($num)
			);
		}
		return $output;
	}
	
	public static function getColorScaleHtml() {
		$output = "<table>";
		$output .= "<tr>";
		$output .= "<td colspan='128'><span style='text-align:left;float:left;'><b>Rarely Used (0)</b></span></td>";
		$output .= "<td colspan='128'><span style='text-align:right;float:right;'><b>Frequently Used (1)</b></span></td>";
		$output .= "</tr>";
		$output .= "<tr class='microrow'><td>&nbsp;</td></tr>";
		$output .= "<tr class='microrow'>";
		$ColorScale = CodonOpt_ColorPicker_Item::getColorScale();
		foreach ($ColorScale as $Color) {
			$output.= "<td style='background-color:".$Color->tohtml().";'>&nbsp;</td>";
		}
		$output .= "</tr></table>";
		return $output;
	}
}
?>