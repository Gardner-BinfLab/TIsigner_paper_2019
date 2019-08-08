<?php
//This object is meant to encapsulate Color Data

class CodonOpt_DTO_color {
	//Read only Variables and their Getters
	private $red;
	public function getRed() {
		return $this->red;
	}
	
	private $green;
	public function getGreen() {
		return $this->green;
	}
	
	private $blue;
	public function getBlue() {
		return $this->blue;
	}
	
	public function toHtml() {
		return "rgb(".$this->red.",".$this->green.",".$this->blue.")";
	}
	
	//Constructor Accepts Colors
	public function CodonOpt_DTO_color ($inRed, $inGreen, $inBlue) {
		$this->red = $inRed;
		$this->green = $inGreen;
		$this->blue = $inBlue;
	}
}
?>
