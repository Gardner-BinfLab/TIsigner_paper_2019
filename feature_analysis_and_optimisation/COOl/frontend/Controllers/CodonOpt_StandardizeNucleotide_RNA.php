<?php
require_once "CodonOpt_StandardizeNucleotide_Ancestor.php";

//This class serves as a template for standardizing nucleotide sequences
class CodonOpt_StandardizeNucleotide_RNA extends CodonOpt_StandardizeNucleotide_Ancestor {
	
	//Converts T for DNA bases to U in RNA bases
	public function standardize($InputSeq) {
		return str_ireplace(
			"T","U",
			strtoupper($InputSeq)
		);
	}
}
?>