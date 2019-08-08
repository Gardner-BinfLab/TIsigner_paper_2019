<?php
class CodonOpt_DTO_translationrules {
	//Read only Variables and their Getters
	private $NCBI_code;
	public function getNCBI_code() {
		return $this->NCBI_code;
	}
	
	private $name;
	public function getName() {
		return $this->name;
	}

	private $codonHash;
	public function getCodonHash() {
		return $this->codonHash;
	}
	
	//Constructor which parses in data from row
	public function CodonOpt_DTO_translationrules($InputArray) {
		foreach ($InputArray as $tempKey=>$tempValue) {
			switch ($tempKey) {
				case "NCBI_code":		//Integer
					$this->NCBI_code = intval($tempValue);
					break;
					
				case "name":			//string
					$this->name = $tempValue;
					break;
				
				//For all the codons, store in hash
				case 'AAA':
				case 'AAC':
				case 'AAG':
				case 'AAU':
				case 'ACA':
				case 'ACC':
				case 'ACG':
				case 'ACU':
				case 'AGA':
				case 'AGC':
				case 'AGG':
				case 'AGU':
				case 'AUA':
				case 'AUC':
				case 'AUG':
				case 'AUU':
				case 'CAA':
				case 'CAC':
				case 'CAG':
				case 'CAU':
				case 'CCA':
				case 'CCC':
				case 'CCG':
				case 'CCU':
				case 'CGA':
				case 'CGC':
				case 'CGG':
				case 'CGU':
				case 'CUA':
				case 'CUC':
				case 'CUG':
				case 'CUU':
				case 'GAA':
				case 'GAC':
				case 'GAG':
				case 'GAU':
				case 'GCA':
				case 'GCC':
				case 'GCG':
				case 'GCU':
				case 'GGA':
				case 'GGC':
				case 'GGG':
				case 'GGU':
				case 'GUA':
				case 'GUC':
				case 'GUG':
				case 'GUU':
				case 'UAA':
				case 'UAC':
				case 'UAG':
				case 'UAU':
				case 'UCA':
				case 'UCC':
				case 'UCG':
				case 'UCU':
				case 'UGA':
				case 'UGC':
				case 'UGG':
				case 'UGU':
				case 'UUA':
				case 'UUC':
				case 'UUG':
				case 'UUU':
					$this->codonHash[$tempKey] = $tempValue;
					break;
			}
		}
		
		//Make sure this is a valid User Job DTO with the following keys:
		if (! isset($this->NCBI_code) ) {
			die ("Error: No valid NCBI_code found!");
		} elseif (! isset($this->name) ) {
			die ("Error: No valid name found!");
		}
	}
}
?>
