<?php
class CodonOpt_DTO_species {
	//Read only Variables and their Getters
	private $serial;
	public function getSerial() {
		return $this->serial;
	}
	
	private $name;
	public function getName() {
		return $this->name;
	}
	
	private $translationRules_NCBI_Code;
	public function getTranslationRules_NCBI_Code() {
		return $this->translationRules_NCBI_Code;
	}

	//Constructor which parses in data from row
	public function CodonOpt_DTO_species($InputArray) {
		foreach ($InputArray as $tempKey=>$tempValue) {
			switch ($tempKey) {
				case "serial":						//Integer
					$this->serial = intval($tempValue);
					break;
					
				case "name":						//string
					$this->name = $tempValue;
					break;
				
				case "TranslationRules_NCBI_Code":	//string
					$this->translationRules_NCBI_Code = $tempValue;
					break;
			}
		}
		
		//Make sure this is a valid User Job DTO with the following keys:
		if (! isset($this->serial) ) {
			die ("Error: No valid serial found!");
		} elseif (! isset($this->name) ) {
			die ("Error: No valid name found!");
		}
	}
}
?>
