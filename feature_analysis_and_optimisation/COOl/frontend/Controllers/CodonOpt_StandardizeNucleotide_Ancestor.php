<?php
//This class serves as a template for standardizing nucleotide sequences
abstract class CodonOpt_StandardizeNucleotide_Ancestor {
	//Standardize to DNA (U to T) or to RNA (T to U) depending on child class
	public abstract function standardize($InputSeq);
}
?>