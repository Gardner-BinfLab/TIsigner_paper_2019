<?php
require_once "CodonOpt_Controller_ViewResult_Ancestor.php";

abstract class CodonOpt_ParseFasta_Ancestor {
	//Hash which stores codons (populated by child)
	protected $CodonHash;
	public function getCodonHash() {
		return $this->CodonHash;
	}
	abstract public function CountCodonUsageInSequence($InputSeq);
	
	//Variable which stores Nucleotide Standardizer
	public static function StandardizeNucleotide($inputseq) {
		return (
			CodonOpt_Controller_ViewResult_Ancestor::getNucleotideStandardizer()->standardize(
				$inputseq
			)
		);
	}
}
?>