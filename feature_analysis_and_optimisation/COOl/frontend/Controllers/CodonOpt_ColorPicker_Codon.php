<?php
require_once "CodonOpt_ColorPicker_Ancestor.php";
require_once "CodonOpt_Utility.php";

//This class is meant to encapsulate the process by which we assign colours to codons
class CodonOpt_ColorPicker_Codon extends CodonOpt_ColorPicker_Ancestor {
	//Constructor parses string into properties
	public function CodonOpt_ColorPicker_Codon($InputString, $TranslationRules) {
		$this->CodonHash = $this::getCodonHashTemplate();
		parent::__construct($InputString);
		$TRCodonHash = $TranslationRules->getCodonHash();
		foreach ($TRCodonHash as $tempKey=>$tempValue) {
			$modKey = $this::StandardizeNucleotide($tempKey);	//Convert U to T
			$tempItem = $this->CodonHash[$modKey];				//Extract item for this codon
			if ( isset($tempItem) ) {							//If there is an item for this codon
				$tempItem->setAAbase($tempValue);				//Store the amino acid
			} else {
				die ("Error: No Item for this Codon: ".$modKey);
			}
		}
		$this->recalculateHostSynonymousTotal();				//Recalculate the Synonymous Total
	}
	
	//Take Array and convert into hash
	private static function getCodonHashTemplate() {
		$OutputArray = array();
		foreach (CodonOpt_ColorPicker_Codon::$CodonArray as $CodonPair) {
			$ConvertPair = CodonOpt_ColorPicker_Ancestor::StandardizeNucleotide($CodonPair);
			$OutputArray[$CodonPair] = new CodonOpt_ColorPicker_Item($CodonPair);
		}
		return $OutputArray;
	}
	
	//Parse Optimized Codon Usage
	public function CountOptSeqCodonUsage($InputSeq) {
		$InputSeq = $this::StandardizeNucleotide($InputSeq);
		$RawCodonArray = str_split(			//Split into codons
			$InputSeq,3
		);
		foreach ($RawCodonArray as $tempCodon) {
			$this->CodonHash[$tempCodon]->addOptSeqRawCount();
		}
		$this->recalculateOptSeqSynonymousTotal();
	}
	
	//Parse Query Codon Usage
	public function CountQuerySeqCodonUsage($InputSeq) {
		$InputSeq = $this::StandardizeNucleotide($InputSeq);
		$RawCodonArray = str_split(				//Split into codons
			$InputSeq,3
		);
		foreach ($RawCodonArray as $tempCodon) {
			if ( strlen($tempCodon) == 3 ) {	//Ensure exactly 3 bases long
				$this->CodonHash[$tempCodon]->addQuerySeqUsageCount();
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