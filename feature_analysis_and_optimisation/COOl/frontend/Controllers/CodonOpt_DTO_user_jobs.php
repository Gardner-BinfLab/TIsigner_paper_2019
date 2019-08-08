<?php
class CodonOpt_DTO_user_jobs {
	//Read only Variables and their Getters
	//=====================================
	private $serial;
	public function getSerial() {
		return $this->serial;
	}
	public function setSerial($InputSerial) {
		return $this->serial = intval($InputSerial);
	}
	
	private $example_serial;
	public function getExample_serial() {
		return $this->example_serial;
	}
	public function setExample_serial($InputSerial) {
		return $this->example_serial = $InputSerial;
	}
	
	private $encrypt_id;
	public function getEncrypt_id() {
		return $this->encrypt_id;
	}
	public function setEncrypt_id($InputID) {
		$this->encrypt_id = $InputID;
	}
	
	private $job_end_on;
	public function getJob_end_on() {
		return $this->job_end_on;
	}	
	
	private $error_text;
	public function getError_text() {
		return $this->error_text;
	}
	
	//Read Only, changeable but not writable Variables
	//================================================
	//These cannot be written directly, but instead are modi
	private $submitted_on;
	public function getSubmitted_on() {
		return $this->submitted_on;
	}
	public function submitCurrentJob() {
		if ( isset($submitted_on) ) {				//If job is already submitted
			die("Attempted to submit already submitted job: ".$this->serial." - ".$this->encrypt_id);
		} else {									//otherwise job not yet submitted
			$this->isChanged_submitted_on = true;	//becomes true (will use SQL timestamp)
		}
	}
	private $isChanged_submitted_on = false;		//default false
	public function getIsChanged_submitted_on() {
		return $this->isChanged_submitted_on;
	}
	//Do not allow manual setting of submission time: use SQL timestamp instead
	//public function setSubmitted_on($input) {
	//	$this->submitted_on = $input;			//Cast as boolean and Save Data
	//	$this->isChanged_submitted_on = true;	//becomes true
	//}
	
	private $post_processed_on;
	public function getPost_processed_on() {
		return $this->post_processed_on;
	}	
	public function postProcessCurrentJob() {
		if ( isset($post_processed_on) ) {				//If job is already submitted
			die("Attempted to post process an already post processed job: ".$this->serial." - ".$this->encrypt_id);
		} else {										//otherwise job not yet submitted
			$this->isChanged_post_processed_on = true;	//becomes true (will use SQL timestamp)
			$this->post_processed_on = true;			//Temporary Set to true (so that subsequent checks will see object as post processed)
		}
	}	
	private $isChanged_post_processed_on = false;		//default false
	public function getIsChanged_post_processed_on() {
		return $this->isChanged_post_processed_on;
	}
	
	//Read/Write Variables and their Getters/Setters
	//==============================================
	//Also come with change flags that default to false and become true if setter is ever called
	//Change flags let us shorten the SQL update command
	private $is_input_protein;
	public function getIs_input_protein() {
		return $this->is_input_protein;
	}
	public function setIs_input_protein($input) {
		$this->is_input_protein = (bool)$input;		//Save Data
		$this->isChanged_is_input_protein = true;	//becomes true
	}
	private $isChanged_is_input_protein = false;		//default false
	public function getIsChanged_is_input_protein() {
		return $this->isChanged_is_input_protein;
	}
	
	private $nucleotide_input_translation_rule;
	public function getNucleotide_input_translation_rule() {
		return $this->nucleotide_input_translation_rule;
	}
	public function setNucleotide_input_translation_rule($input) {
		$this->nucleotide_input_translation_rule = intval($input);		//Save Data as integer
		$this->isChanged_nucleotide_input_translation_rule = true;		//becomes true
	}
	private $isChanged_nucleotide_input_translation_rule = false;		//default false
	public function getIsChanged_nucleotide_input_translation_rule() {
		return $this->isChanged_nucleotide_input_translation_rule;
	}

	private $input_sequence;
	public function getInput_sequence() {
		return $this->input_sequence;
	}
	public function setInput_sequence($input) {
		$this->input_sequence = $input;				//Save Data
		$this->isChanged_input_sequence = true;		//becomes true
	}
	private $isChanged_input_sequence = false;		//default false
	public function getIsChanged_input_sequence() {
		return $this->isChanged_input_sequence;
	}
	
	private $title;
	public function getTitle() {
		return $this->title;
	}
	public function setTitle($input) {
		$this->title = $input;				//Save Data
		$this->isChanged_title = true;		//becomes true
	}
	private $isChanged_title = false;		//default false
	public function getIsChanged_title() {
		return $this->isChanged_title;
	}
	
	private $user_email;
	public function getUser_email() {
		return $this->user_email;
	}
	public function setUser_email($input) {
		$this->user_email = $input;				//Save Data
		$this->isChanged_user_email = true;		//becomes true
	}
	private $isChanged_user_email = false;		//default false
	public function getIsChanged_user_email() {
		return $this->isChanged_user_email;
	}
	
	private $exclusion_sequence;
	public function getExclusion_sequence() {
		return $this->exclusion_sequence;
	}
	public function setExclusion_sequence($input) {
		$this->exclusion_sequence = $input;				//Save Data
		$this->isChanged_exclusion_sequence = true;		//becomes true
	}
	private $isChanged_exclusion_sequence = false;		//default false
	public function getIsChanged_exclusion_sequence() {
		return $this->isChanged_exclusion_sequence;
	}
	
	private $repeat_consec_mode;
	public function getRepeat_consec_mode() {
		return $this->repeat_consec_mode;
	}
	public function setRepeat_consec_mode($input) {
		$intput = intval($input);					//Convert to integer
		$this->repeat_consec_mode = $intput;		//Save Data
		$this->isChanged_repeat_consec_mode = true;	//becomes true
	}
	private $isChanged_repeat_consec_mode = false;	//default false
	public function getIsChanged_repeat_consec_mode() {
		return $this->isChanged_repeat_consec_mode;
	}
	
	private $repeat_consec_length;
	public function getRepeat_consec_length() {
		return $this->repeat_consec_length;
	}
	public function setRepeat_consec_length($input) {
		if ( isset($input) ) {							//If data is defined
			$intput = intval($input);					//Convert to integer
			$this->repeat_consec_length = $intput;		//Save Data
		} else {										//Otherwise data not defined
			$this->repeat_consec_length = null;			//Save Null
		}
		$this->isChanged_repeat_consec_length = true;	//becomes true
	}
	private $isChanged_repeat_consec_length = false;	//default false
	public function getIsChanged_repeat_consec_length() {
		return $this->isChanged_repeat_consec_length;
	}
	
	private $repeat_consec_count;
	public function getRepeat_consec_count() {
		return $this->repeat_consec_count;
	}
	public function setRepeat_consec_count($input) {
		if ( isset($input) ) {							//If data is defined
			$intput = intval($input);					//Convert to integer
			$this->repeat_consec_count = $intput;		//Save Data
		} else {										//Otherwise data not defined
			$this->repeat_consec_count = null;			//Save Null
		}
		$this->isChanged_repeat_consec_count = true;	//becomes true
	}
	private $isChanged_repeat_consec_count = false;		//default false
	public function getIsChanged_repeat_consec_count() {
		return $this->isChanged_repeat_consec_count;
	}
	
	private $repeat_allmotif_mode;
	public function getRepeat_allmotif_mode() {
		return $this->repeat_allmotif_mode;
	}
	public function setRepeat_allmotif_mode($input) {
		$intput = intval($input);						//Convert to integer
		$this->repeat_allmotif_mode = $intput;			//Save Data
		$this->isChanged_repeat_allmotif_mode = true;	//becomes true
	}
	private $isChanged_repeat_allmotif_mode = false;	//default false
	public function getIsChanged_repeat_allmotif_mode() {
		return $this->isChanged_repeat_allmotif_mode;
	}
	
	private $repeat_allmotif_length;
	public function getRepeat_allmotif_length() {
		return $this->repeat_allmotif_length;
	}
	public function setRepeat_allmotif_length($input) {
		if ( isset($input) ) {							//If data is defined
			$intput = intval($input);					//Convert to integer
			$this->repeat_allmotif_length = $intput;	//Save Data
		} else {										//Otherwise data not defined
			$this->repeat_allmotif_length = null;		//Save Null
		}
		$this->isChanged_repeat_allmotif_length = true;	//becomes true
	}
	private $isChanged_repeat_allmotif_length = false;	//default false
	public function getIsChanged_repeat_allmotif_length() {
		return $this->isChanged_repeat_allmotif_length;
	}
	
	private $repeat_allmotif_count;
	public function getRepeat_allmotif_count() {
		return $this->repeat_allmotif_count;
	}
	public function setRepeat_allmotif_count($input) {
		if ( isset($input) ) {							//If data is defined
			$intput = intval($input);					//Convert to integer
			$this->repeat_allmotif_count = $intput;		//Save Data
		} else {										//Otherwise data not defined
			$this->repeat_allmotif_count = null;		//Save Null
		}
		$this->isChanged_repeat_allmotif_count = true;	//becomes true
	}
	private $isChanged_repeat_allmotif_count = false;	//default false
	public function getIsChanged_repeat_allmotif_count() {
		return $this->isChanged_repeat_allmotif_count;
	}
	
	private $optimize_ic;
	public function getOptimize_ic() {
		return $this->optimize_ic;
	}
	public function setOptimize_ic($input) {
		$intput = intval($input);				//Convert to integer
		$this->optimize_ic = $intput;			//Save Data
		$this->isChanged_optimize_ic = true;	//becomes true
	}
	private $isChanged_optimize_ic = false;		//default false
	public function getIsChanged_optimize_ic() {
		return $this->isChanged_optimize_ic;
	}
	
	private $optimize_cc;
	public function getOptimize_cc() {
		return $this->optimize_cc;
	}
	public function setOptimize_cc($input) {
		$intput = intval($input);				//Convert to integer
		$this->optimize_cc = $intput;			//Save Data
		$this->isChanged_optimize_cc = true;	//becomes true
	}
	private $isChanged_optimize_cc = false;		//default false
	public function getIsChanged_optimize_cc() {
		return $this->isChanged_optimize_cc;
	}
	
	private $optimize_cai;
	public function getOptimize_cai() {
		return $this->optimize_cai;
	}
	public function setOptimize_cai($input) {
		$intput = intval($input);				//Convert to integer
		$this->optimize_cai = $intput;			//Save Data
		$this->isChanged_optimize_cai = true;	//becomes true
	}
	private $isChanged_optimize_cai = false;		//default false
	public function getIsChanged_optimize_cai() {
		return $this->isChanged_optimize_cai;
	}

	private $optimize_hidden_stop_codon;
	public function getOptimize_hidden_stop_codon() {
		return $this->optimize_hidden_stop_codon;
	}
	public function setOptimize_hidden_stop_codon($input) {
		$intput = intval($input);							//Convert to integer
		$this->optimize_hidden_stop_codon = $intput;		//Save Data
		$this->isChanged_optimize_hidden_stop_codon = true;	//becomes true
	}
	private $isChanged_optimize_hidden_stop_codon = false;	//default false
	public function getIsChanged_optimize_hidden_stop_codon() {
		return $this->isChanged_optimize_hidden_stop_codon;
	}
	
	private $optimize_gc_mode;
	public function getOptimize_gc_mode() {
		return $this->optimize_gc_mode;
	}
	public function setOptimize_gc_mode($input) {
		$intput = intval($input);					//Convert to integer
		$this->optimize_gc_mode = $intput;			//Save Data
		$this->isChanged_optimize_gc_mode = true;	//becomes true
	}
	private $isChanged_optimize_gc_mode = false;	//default false
	public function getIsChanged_optimize_gc_mode() {
		return $this->isChanged_optimize_gc_mode;
	}
	
	private $optimize_gc_target;
	public function getOptimize_gc_target() {
		return $this->optimize_gc_target;
	}
	public function setOptimize_gc_target($input) {
		if ( isset($input) ) {							//If there is content
			$intput = doubleval($input);				//Convert to double
			$this->optimize_gc_target = $intput;		//Save Data
		} else {										//Otherwise no content
			$this->optimize_gc_target = null;			//Set to Null
		}
		$this->isChanged_optimize_gc_target = true;		//becomes true
	}
	private $isChanged_optimize_gc_target = false;		//default false
	public function getIsChanged_optimize_gc_target() {
		return $this->isChanged_optimize_gc_target;
	}
	
	private $inbuilt_species_serial;
	public function getInbuilt_species_serial() {
		return $this->inbuilt_species_serial;
	}
	public function setInbuilt_species_serial($input) {
		$this->inbuilt_species_serial = intval($input);		//Save Data as integer
		$this->isChanged_inbuilt_species_serial = true;		//becomes true
	}
	private $isChanged_inbuilt_species_serial = false;		//default false
	public function getIsChanged_inbuilt_species_serial() {
		return $this->isChanged_inbuilt_species_serial;
	}
	
	private $inbuilt_species_gene_sort;
	public function getInbuilt_species_gene_sort() {
		return $this->inbuilt_species_gene_sort;
	}
	public function setInbuilt_species_gene_sort($input) {
		$this->inbuilt_species_gene_sort = $input;				//Save Data
		$this->isChanged_inbuilt_species_gene_sort = true;		//becomes true
	}
	private $isChanged_inbuilt_species_gene_sort = false;		//default false
	public function getIsChanged_inbuilt_species_gene_sort() {
		return $this->isChanged_inbuilt_species_gene_sort;
	}
	
	private $inbuilt_species_gene_list;
	public function getInbuilt_species_gene_list() {
		return $this->inbuilt_species_gene_list;
	}
	public function setInbuilt_species_gene_list($input) {
		$this->inbuilt_species_gene_list = $input;				//Save Data
		$this->isChanged_inbuilt_species_gene_list = true;		//becomes true
	}
	private $isChanged_inbuilt_species_gene_list = false;		//default false
	public function getIsChanged_inbuilt_species_gene_list() {
		return $this->isChanged_inbuilt_species_gene_list;
	}
	
	private $use_custom_species;
	public function getUse_custom_species() {
		return $this->use_custom_species;
	}
	public function setUse_custom_species($input) {
		$this->use_custom_species = (bool)$input;		//Save Data
		$this->isChanged_use_custom_species = true;	//becomes true
	}
	private $isChanged_use_custom_species = false;		//default false
	public function getIsChanged_use_custom_species() {
		return $this->isChanged_use_custom_species;
	}
	
	private $ic_frequency;
	public function getIc_frequency() {
		return $this->ic_frequency;
	}
	public function setIc_frequency($input) {
		$this->ic_frequency = $input;				//Save Data
		$this->isChanged_ic_frequency = true;		//becomes true
	}
	private $isChanged_ic_frequency = false;		//default false
	public function getIsChanged_ic_frequency() {
		return $this->isChanged_ic_frequency;
	}
	
	private $cc_frequency;
	public function getCc_frequency() {
		return $this->cc_frequency;
	}
	public function setCc_frequency($input) {
		$this->cc_frequency = $input;				//Save Data
		$this->isChanged_cc_frequency = true;		//becomes true
	}
	private $isChanged_cc_frequency = false;		//default false
	public function getIsChanged_cc_frequency() {
		return $this->isChanged_cc_frequency;
	}
	
	private $translationRules_NCBI_code;
	public function getTranslationRules_NCBI_code() {
		return $this->translationRules_NCBI_code;
	}
	public function setTranslationRules_NCBI_code($input) {
		$this->translationRules_NCBI_code = intval($input);		//Save Data as integer
		$this->isChanged_translationRules_NCBI_code = true;		//becomes true
	}
	private $isChanged_translationRules_NCBI_code = false;		//default false
	public function getIsChanged_translationRules_NCBI_code() {
		return $this->isChanged_translationRules_NCBI_code;
	}
	
	
	//Constructor which parses in data from row
	public function CodonOpt_DTO_user_jobs($InputArray) {
		foreach ($InputArray as $tempKey=>$tempValue) {
			switch ($tempKey) {
				case "serial":					//Integer
					$this->serial = intval($tempValue);
					break;
					
				case "example_serial":			//Integer or null
					$this->example_serial = $tempValue;
					break;
					
				case "encrypt_id":				//string
					$this->encrypt_id = $tempValue;
					break;
					
				case "is_input_protein":		//boolean
					$this->is_input_protein = (bool)$tempValue;
					break;
					
				case "nucleotide_input_translation_rule":	//integer
					$this->nucleotide_input_translation_rule = intval($tempValue);
					break;

				case "input_sequence":			//string
					$this->input_sequence = $tempValue;
					break;

				case "submitted_on":			//date time
					$this->submitted_on = $tempValue;
					break;
				
				case "job_end_on":				//date time
					$this->job_end_on = $tempValue;
					break;
					
				case "error_text":				//string
					$this->error_text = $tempValue;
					break;
				
				case "post_processed_on":		//date time	
					$this->post_processed_on = $tempValue;
					break;
				
				case "title":					//string
					$this->title = $tempValue;
					break;
					
				case "exclusion_sequence":		//string
					$this->exclusion_sequence = $tempValue;
					break;

				case "repeat_consec_mode":			//integer
					$this->repeat_consec_mode = intval($tempValue);
					break;

				case "repeat_consec_length":	//integer or null
					$this->setRepeat_consec_length($tempValue);
					$this->isChanged_repeat_consec_length = false;	//Reset Change flag
					break;

				case "repeat_consec_count":	//integer or null
					$this->setRepeat_consec_count($tempValue);
					$this->isChanged_repeat_consec_count = false;	//Reset Change flag
					break;
					
				case "repeat_allmotif_mode":	//integer
					$this->repeat_allmotif_mode = intval($tempValue);
					break;

				case "repeat_allmotif_length":	//integer or null
					$this->setRepeat_allmotif_length($tempValue);
					$this->isChanged_repeat_allmotif_length = false;	//Reset Change flag
					break;

				case "repeat_allmotif_count":	//integer or null
					$this->setRepeat_allmotif_count($tempValue);
					$this->isChanged_repeat_allmotif_count = false;	//Reset Change flag
					break;
					
				case "user_email":				//string
					$this->user_email = $tempValue;
					break;
				
				case "optimize_ic":				//integer
					$this->optimize_ic = intval($tempValue);
					break;
					
				case "optimize_cc":				//integer
					$this->optimize_cc = intval($tempValue);
					break;
					
				case "optimize_cai":			//integer
					$this->optimize_cai = intval($tempValue);
					break;
					
				case "optimize_hidden_stop_codon":	//integer
					$this->optimize_hidden_stop_codon = intval($tempValue);
					break;

				case "optimize_gc_mode":			//integer
					$this->optimize_gc_mode = intval($tempValue);
					break;

				case "optimize_gc_target":			//double or null
					$this->setOptimize_gc_target($tempValue);
					$this->isChanged_optimize_gc_target = false;	//Reset Change flag
					break;
					
				case "inbuilt_species_serial":		//integer
					$this->inbuilt_species_serial = intval($tempValue);				
					break;
					
				case "inbuilt_species_gene_list":	//string
					$this->inbuilt_species_gene_list = $tempValue;				
					break;
					
				case "inbuilt_species_gene_sort":	//string
					$this->inbuilt_species_gene_sort = $tempValue;				
					break;
					
				case "use_custom_species":			//boolean
					$this->use_custom_species = (bool)$tempValue;
					break;
					
				case "ic_frequency":
					$this->ic_frequency = $tempValue;
					break;
					
				case "cc_frequency":
					$this->cc_frequency = $tempValue;
					break;
					
				case "TranslationRules_NCBI_code":	//integer
					$this->translationRules_NCBI_code = intval($tempValue);
					break;
			}
		}
		
		//Make sure this is a valid User Job DTO with the following keys:
		if (! isset($this->serial) ) {
			die ("Error: No valid serial found!");
		} elseif (! isset($this->encrypt_id) ) {
			die ("Error: No valid encrypt_id found!");
		}
	}
}
?>
