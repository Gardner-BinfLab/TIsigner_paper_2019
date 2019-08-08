<?php
require_once "fpdf-library/fpdf.php";	//Get PDF generator

class PDFrotate extends FPDF {
	var $angle=0;

	public function Rotate($angle,$x=-1,$y=-1) {
		if($x==-1) {
			$x=$this->x;
		}
		if($y==-1) {
			$y=$this->y;
		}
		if($this->angle!=0) {
			$this->_out('Q');
		}
		$this->angle=$angle;
		if($angle!=0) {
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}

	//Text rotated around its origin
	public function RotatedText($x,$y,$txt,$angle) {
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}

	//Call this function at end of page, reset angle to 0
	public function _endpage() {
		if($this->angle!=0)
		{
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}
}
?>