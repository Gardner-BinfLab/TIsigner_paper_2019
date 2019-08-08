<?php
require_once "CodonOpt_StandardizeNucleotide_Ancestor.php";

//This class serves as a template for standardizing nucleotide sequences
class CodonOpt_StandardizeNucleotide_DNA extends CodonOpt_StandardizeNucleotide_Ancestor {
	
	//Converts U in RNA bases to T for DNA bases
	public function standardize($InputSeq) {
		return str_ireplace(
			"U","T",
			strtoupper($InputSeq)
		);
	}
}
?>