<?php
class CodonOpt_DTO_user_results {
	//Read only Variables and their Getters
	//=====================================
	private $user_job_serial;
	public function getUser_job_serial() {
		return $this->user_job_serial;
	}
	
	private $user_insert;
	public function getUser_insert() {
		return $this->user_insert;
	}

	private $output_sequence;
	public function getOutput_sequence() {
		return $this->output_sequence;
	}
	
	private $trigger_count_upcase_outseq_exclusion;
	public function getTrigger_count_upcase_outseq_exclusion() {
		return $this->trigger_count_upcase_outseq_exclusion;
	}
	
	private $trigger_count_upcase_outseq_repeat_consec;
	public function getTrigger_count_upcase_outseq_repeat_consec() {
		return $this->trigger_count_upcase_outseq_repeat_consec;
	}
	
	private $trigger_count_upcase_outseq_repeat_allmotif;
	public function getTrigger_count_upcase_outseq_repeat_allmotif() {
		return $this->trigger_count_upcase_outseq_repeat_allmotif;
	}
	
	private $trigger_count_upcase_outseq_hidden_stop_codon;
	public function getTrigger_count_upcase_outseq_hidden_stop_codon() {
		return $this->trigger_count_upcase_outseq_hidden_stop_codon;
	}
	
	private $trigger_count_gc_content_outseq;
	public function getTrigger_count_gc_content_outseq() {
		return $this->trigger_count_gc_content_outseq;
	}
	
	//Read/Write Variables and their getters/setters
	//==============================================
	private $display_id;
	public function getDisplay_id() {
		return $this->display_id;
	}
	public function setDisplay_id($input) {
		$this->display_id = intval($input);
	}
	
	private $user_given_title;
	public function getUser_given_title() {
		return $this->user_given_title;
	}
	public function setUser_given_title($input) {
		$this->user_given_title = $input;
	}
	
	private $output_sequence_exclusion;
	public function getOutput_sequence_exclusion() {
		return $this->output_sequence_exclusion;
	}
	public function setOutput_sequence_exclusion($input) {
		$this->output_sequence_exclusion = $input;				//Save Data
		$this->isChanged_output_sequence_exclusion = true;		//becomes true
	}
	private $isChanged_output_sequence_exclusion = false;		//default false
	public function getIsChanged_output_sequence_exclusion() {
		return $this->isChanged_output_sequence_exclusion;
	}
	
	private $output_sequence_repeat_consec;
	public function getOutput_sequence_repeat_consec() {
		return $this->output_sequence_repeat_consec;
	}
	public function setOutput_sequence_repeat_consec($input) {
		$this->output_sequence_repeat_consec = $input;				//Save Data
		$this->isChanged_output_sequence_repeat_consec = true;		//becomes true
	}
	private $isChanged_output_sequence_repeat_consec = false;		//default false
	public function getIsChanged_output_sequence_repeat_consec() {
		return $this->isChanged_output_sequence_repeat_consec;
	}

	private $output_sequence_repeat_allmotif;
	public function getOutput_sequence_repeat_allmotif() {
		return $this->output_sequence_repeat_allmotif;
	}
	public function setOutput_sequence_repeat_allmotif($input) {
		$this->output_sequence_repeat_allmotif = $input;			//Save Data
		$this->isChanged_output_sequence_repeat_allmotif = true;	//becomes true
	}
	private $isChanged_output_sequence_repeat_allmotif = false;		//default false
	public function getIsChanged_output_sequence_repeat_allmotif() {
		return $this->isChanged_output_sequence_repeat_allmotif;
	}
	
	private $output_sequence_hidden_stop_codon;
	public function getOutput_sequence_hidden_stop_codon() {
		return $this->output_sequence_hidden_stop_codon;
	}
	public function setOutput_sequence_hidden_stop_codon($input) {
		$this->output_sequence_hidden_stop_codon = $input;			//Save Data
		$this->isChanged_output_sequence_hidden_stop_codon = true;	//becomes true
	}
	private $isChanged_output_sequence_hidden_stop_codon = false;	//default false
	public function getIsChanged_output_sequence_hidden_stop_codon() {
		return $this->isChanged_output_sequence_hidden_stop_codon;
	}
	
	private $ic_fitness;
	public function getIc_fitness() {
		return $this->ic_fitness;
	}
	public function setIc_fitness($input) {
		$this->ic_fitness = $input;							//Save Data
		$this->isChanged_ic_fitness = true;					//becomes true
	}
	private $isChanged_ic_fitness = false;					//default false
	public function getIsChanged_ic_fitness() {
		return $this->isChanged_ic_fitness;
	}
	
	private $cc_fitness;
	public function getCc_fitness() {
		return $this->cc_fitness;
	}
	public function setCc_fitness($input) {
		$this->cc_fitness = $input;							//Save Data
		$this->isChanged_cc_fitness = true;					//becomes true
	}
	private $isChanged_cc_fitness = false;					//default false
	public function getIsChanged_cc_fitness() {
		return $this->isChanged_cc_fitness;
	}
	
	private $cai_fitness;
	public function getCai_fitness() {
		return $this->cai_fitness;
	}
	public function setCai_fitness($input) {
		$this->cai_fitness = $input;							//Save Data
		$this->isChanged_cai_fitness = true;					//becomes true
	}
	private $isChanged_cai_fitness = false;					//default false
	public function getIsChanged_cai_fitness() {
		return $this->isChanged_cai_fitness;
	}
	
	private $gc_content_fitness;
	public function getGc_content_fitness() {
		return $this->gc_content_fitness;
	}
	public function setGc_content_fitness($input) {
		$this->gc_content_fitness = $input;							//Save Data
		$this->isChanged_gc_content_fitness = true;					//becomes true
	}
	private $isChanged_gc_content_fitness = false;					//default false
	public function getIsChanged_gc_content_fitness() {
		return $this->isChanged_gc_content_fitness;
	}
	
	private $exclusion_fitness;
	public function getExclusion_fitness() {
		return $this->exclusion_fitness;
	}
	public function setExclusion_fitness($input) {
		$this->exclusion_fitness = $input;				//Save Data
		$this->isChanged_exclusion_fitness = true;		//becomes true
	}
	private $isChanged_exclusion_fitness = false;			//default false
	public function getIsChanged_exclusion_fitness() {
		return $this->isChanged_exclusion_fitness;
	}
	
	private $number_of_stop_codon_motifs;
	public function getNumber_of_stop_codon_motifs() {
		return $this->number_of_stop_codon_motifs;
	}
	public function setNumber_of_stop_codon_motifs($input) {
		$this->number_of_stop_codon_motifs = $input;				//Save Data
		$this->isChanged_number_of_stop_codon_motifs = true;		//becomes true
	}
	private $isChanged_number_of_stop_codon_motifs = false;			//default false
	public function getIsChanged_number_of_stop_codon_motifs() {
		return $this->isChanged_number_of_stop_codon_motifs;
	}
	
	//Constructor which takes in compulsory details
	public static function CreateNewUserResultObject(
		$input_job_serial , $input_user_insert , $input_given_title, $output_sequence
	) {
		$tempArray = array(
			"user_job_serial"	=> $input_job_serial,
			"user_insert"		=> $input_user_insert,
			"user_given_title"	=> $input_given_title,
			"output_sequence"	=> $output_sequence
		);
		$instance = new self($tempArray);
		return $instance;
	}
	
	//Constructor which parses in data from row
	public function CodonOpt_DTO_user_results($InputArray) {
		foreach ($InputArray as $tempKey=>$tempValue) {
			switch ($tempKey) {
				case "user_job_serial":									//Integer
					$this->user_job_serial = intval($tempValue);
					break;
					
				case "user_insert":										//boolean
					$this->user_insert = (bool)$tempValue;
					break;

				case "display_id":										//number
					$this->display_id = intval($tempValue);
					break;
					
				case "user_given_title":								//string
					$this->user_given_title	= $tempValue;
					break;

				case "output_sequence":									//string
					$this->output_sequence = $tempValue;
					break;
					
				case "output_sequence_exclusion":						//string
					$this->output_sequence_exclusion = $tempValue;
					break;
					
				case "output_sequence_repeat_consec":					//string
					$this->output_sequence_repeat_consec = $tempValue;
					break;

				case "output_sequence_repeat_allmotif":					//string
					$this->output_sequence_repeat_allmotif = $tempValue;
					break;

				case "output_sequence_hidden_stop_codon":				//string
					$this->output_sequence_hidden_stop_codon = $tempValue;
					break;

				case "ic_fitness":										//double
					$this->ic_fitness = doubleval($tempValue);
					break;
				
				case "cc_fitness":										//double
					$this->cc_fitness = doubleval($tempValue);
					break;
					
				case "cai_fitness":										//double
					$this->cai_fitness = doubleval($tempValue);
					break;

				case "gc_content_fitness":								//double
					$this->gc_content_fitness = doubleval($tempValue);
					break;

				case "exclusion_fitness":								//string
					$this->exclusion_fitness = $tempValue;
					break;
					
				case "number_of_stop_codon_motifs":						//integer
					$this->number_of_stop_codon_motifs = intval($tempValue);
					break;

				case "trigger_count_upcase_outseq_exclusion":			//integer
					$this->trigger_count_upcase_outseq_exclusion = intval($tempValue);
					break;
					
				case "trigger_count_upcase_outseq_repeat_consec":		//integer
					$this->trigger_count_upcase_outseq_repeat_consec = intval($tempValue);
					break;

				case "trigger_count_upcase_outseq_repeat_allmotif":		//integer
					$this->trigger_count_upcase_outseq_repeat_allmotif = intval($tempValue);
					break;

				case "trigger_count_upcase_outseq_hidden_stop_codon":	//integer
					$this->trigger_count_upcase_outseq_hidden_stop_codon = intval($tempValue);
					break;

				case "trigger_count_gc_content_outseq":					//double
					$this->trigger_count_gc_content_outseq = doubleval($tempValue);
					break;
			}
		}
		
		//Make sure this is a valid User Job DTO with the following keys:
		if (! isset($this->user_job_serial) ) {
			die ("Error: No valid serial found!");
		} elseif (! isset($this->user_insert) ) {
			die ("Error: No valid user_insert found!");			
		}
	}
}
?>
