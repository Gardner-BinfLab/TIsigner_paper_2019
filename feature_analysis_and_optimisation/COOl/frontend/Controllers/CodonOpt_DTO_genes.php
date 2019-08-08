<?php
class CodonOpt_DTO_genes {
	//Read only Variables and their Getters
	private $serial;
	public function getSerial() {
		return $this->serial;
	}
	
	private $species_serial;
	public function getSpecies_serial() {
		return $this->species_serial;
	}

	private $access_id;
	public function getAccess_id() {
		return $this->access_id;
	}
	
	private $name;
	public function getName() {
		return $this->name;
	}
	
	private $description;
	public function getDescription() {
		return $this->description;
	}
	
	private $ic_frequency;
	public function getIc_frequency() {
		return $this->ic_frequency;
	}
	
	private $cc_frequency;
	public function getCc_frequency() {
		return $this->cc_frequency;
	}
	
	//Constructor which parses in data from row
	public function CodonOpt_DTO_genes($InputArray) {
		foreach ($InputArray as $tempKey=>$tempValue) {
			switch ($tempKey) {
				case "serial":						//Integer
					$this->serial = intval($tempValue);
					break;
					
				case "species_serial":				//Integer
					$this->species_serial = intval($tempValue);
					break;

				case "access_id":					//string
					$this->access_id = $tempValue;
					break;

				case "name":						//string
					$this->name = $tempValue;
					break;

				case "description":					//string
					$this->description = $tempValue;
					break;

				case "ic_frequency":				//string
					$this->ic_frequency = $tempValue;
					break;
					
				case "cc_frequency":				//string
					$this->cc_frequency = $tempValue;
					break;					
			}
		}

		//Make sure this is a valid User Job DTO with the following keys:
		if (! isset($this->serial) ) {
			die ("Error: No valid serial found!");
		}
	}
}
?>
