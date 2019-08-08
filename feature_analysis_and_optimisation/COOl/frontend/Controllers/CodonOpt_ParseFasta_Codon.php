<?php
require_once "CodonOpt_ParseFasta_Ancestor.php";

//This class is meant to encapsulate the process by which we assign colours to codons
class CodonOpt_ParseFasta_Codon extends CodonOpt_ParseFasta_Ancestor {
	//Constructor 
	public function CodonOpt_ParseFasta_Codon() {
		$this->CodonHash = $this::getCodonHashTemplate();
	}
	
	//Take Array and convert into hash
	private static function getCodonHashTemplate() {
		$OutputArray = array();
		foreach (CodonOpt_ParseFasta_Codon::$CodonArray as $Codon) {
			$OutputArray[$Codon] = 0;
		}
		return $OutputArray;
	}

	//Parse Query Codon Usage
	public function CountCodonUsageInSequence($InputSeq) {
		$InputSeq = $this::StandardizeNucleotide($InputSeq);
		$RawCodonArray = str_split(				//Split into codons
			$InputSeq,3
		);
		foreach ($RawCodonArray as $tempCodon) {
			if ( strlen($tempCodon) == 3 ) {	//Ensure exactly 3 bases long
				$this->CodonHash[$tempCodon]++;
			}
		}
	}
	
	//Internal values for this object (placed at end so it won't clog up the front)
	private static $CodonArray = array(
		'AAA',
		'AAC',
		'AAG',
		'AAT',
		'ACA',
		'ACC',
		'ACG',
		'ACT',
		'AGA',
		'AGC',
		'AGG',
		'AGT',
		'ATA',
		'ATC',
		'ATG',
		'ATT',
		'CAA',
		'CAC',
		'CAG',
		'CAT',
		'CCA',
		'CCC',
		'CCG',
		'CCT',
		'CGA',
		'CGC',
		'CGG',
		'CGT',
		'CTA',
		'CTC',
		'CTG',
		'CTT',
		'GAA',
		'GAC',
		'GAG',
		'GAT',
		'GCA',
		'GCC',
		'GCG',
		'GCT',
		'GGA',
		'GGC',
		'GGG',
		'GGT',
		'GTA',
		'GTC',
		'GTG',
		'GTT',
		'TAA',
		'TAC',
		'TAG',
		'TAT',
		'TCA',
		'TCC',
		'TCG',
		'TCT',
		'TGA',
		'TGC',
		'TGG',
		'TGT',
		'TTA',
		'TTC',
		'TTG',
		'TTT'
	);
}
?>