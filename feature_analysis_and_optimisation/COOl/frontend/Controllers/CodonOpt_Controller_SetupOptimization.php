<?php
require_once("CodonOpt_Controller_Setup_Ancestor.php");
require_once("CodonOpt_DAO_species.php");
require_once("CodonOpt_DAO_translationrules.php");
require_once("CodonOpt_Utility.php");

//This Class is meant to control the Setup Optimization page
class CodonOpt_Controller_SetupOptimization extends CodonOpt_Controller_Setup_Ancestor {
	//Internal Variables without getters
	private static $ACGTU = array("A","T","U","C","G");
	private static $TabLineBreaks = array(" ","\t","\r","\n");
	private static $MinCodon = 30;
	private static $MinCodonPair = 2000;
	private static $MaxUserInputErrorLength = 17;
	//This function Trims input error, so that it ca fit within the error message column
	private static function TrimUserErrorInput($input) {
		$output = $input;
		if (strlen($input) > CodonOpt_Controller_SetupOptimization::$MaxUserInputErrorLength) {
			$output = substr($input,0,CodonOpt_Controller_SetupOptimization::$MaxUserInputErrorLength);
			$output .= "...";
		}
		return $output;
	}
	
	
	//Internal Variables with Getters
	private static $ic_frequencyMaxLength = 10000;
	public static function getIc_frequencyMaxLength()
	{ return CodonOpt_Controller_SetupOptimization::$ic_frequencyMaxLength; }
	
	private static $cc_frequencyMaxLength = 100000;
	public static function getCc_frequencyMaxLength()
	{ return CodonOpt_Controller_SetupOptimization::$cc_frequencyMaxLength; }
	
	
	//Static Lists and their Getters (no setters)
	private static $SpeciesNameList;
	public static function getSpeciesNameList() {
		if (! isset(CodonOpt_Controller_SetupOptimization::$SpeciesNameList) ) {	//If list is not yet set
			CodonOpt_Controller_SetupOptimization::$SpeciesNameList					//retrieve from database
				= CodonOpt_DAO_species::selectAllSpecies();
		}
		return CodonOpt_Controller_SetupOptimization::$SpeciesNameList;
	}

	private static $TranslationRulesList;
	public static function getTranslationRulesList() {
		if (! isset(CodonOpt_Controller_SetupOptimization::$TranslationRulesList) ) {	//If list is not yet set
			CodonOpt_Controller_SetupOptimization::$TranslationRulesList					//retrieve from database
				= CodonOpt_DAO_translationrules::selectAllTranslationRules();
		}
		return CodonOpt_Controller_SetupOptimization::$TranslationRulesList;
	}

	
	//This function Validates the input for the optimize_X series
	private static function ValidateOptimizeInput($input) {
		if ( isset($input) ) {			//Ensure input variable is set
			$intput = intval($input);	//convert to integer
			if ($intput>= 0) {			//Ensure 0 or larger
				if ($intput <= 2) {		//Ensure 2 or smaller
					return true;		//Input is valid
				}		
			}
		}	
		return false;					//Default, input not valid
	}

	
	//Form Data, and Getters and Setters
	//Setters include validation before saving data	
	private $optimize_ic;
	public function getOptimize_ic() {
		return $this->optimize_ic;
	}
	public function setOptimize_ic($input) {
		if ( isset($input) ) {
			if ( $this::ValidateOptimizeInput($input) ) {
				$this->optimize_ic = intval($input);
			}
		}			
	}
	
	private $optimize_cc;
	public function getOptimize_cc() {
		return $this->optimize_cc;
	}
	public function setOptimize_cc($input) {
		if ( isset($input) ) {
			if ( $this::ValidateOptimizeInput($input) ) {
				$this->optimize_cc = intval($input);
			}
		}			
	}
	
	private $optimize_cai;
	public function getOptimize_cai() {
		return $this->optimize_cai;
	}
	public function setOptimize_cai($input) {
		if ( isset($input) ) {
			if ( $this::ValidateOptimizeInput($input) ) {
				$this->optimize_cai = intval($input);
			}
		}			
	}

	private $optimize_hidden_stop_codon;
	public function getOptimize_hidden_stop_codon() {
		return $this->optimize_hidden_stop_codon;
	}
	public function setOptimize_hidden_stop_codon($input) {
		if ( isset($input) ) {
			if ( $this::ValidateOptimizeInput($input) ) {
				$this->optimize_hidden_stop_codon = intval($input);
			}
		}			
	}
	
	private $optimize_gc_mode;
	public function getOptimize_gc_mode() {
		return $this->optimize_gc_mode;
	}
	public function setOptimize_gc_mode($input) {
		if ( isset($input) ) {
			if ( $this::ValidateOptimizeInput($input) ) {
				$this->optimize_gc_mode = intval($input);
			}
		}			
	}
	
	private $optimize_gc_target;		//The raw target as input by the user
	private $clean_optimize_gc_target;	//The cleaned target parsed from the raw target	
	public function getOptimize_gc_target() {
		return $this->optimize_gc_target;
	}
	public function setOptimize_gc_target($input) {
		$this->optimize_gc_target = trim($input);			
	}

	private $speciesSerial;
	public function getSpeciesSerial() {
		return $this->speciesSerial;					//Return code if defined
	}	
	public function setSpeciesSerial($input) {
		if ( isset($input) ) {
			$int_input = intval($input);				//Convert to integer
			$tempList = CodonOpt_Controller_SetupOptimization::getSpeciesNameList();
			$FoundSerial = null;
			foreach ($tempList as $DTO) {				//Go through each item on list
				if ($DTO->getSerial() == $int_input) {	//if input matches that item serial
					$FoundSerial = $int_input;			//valid serial was found
				}
			}
			if ( isset ($FoundSerial) ) {				//If valid serial was found
				$this->speciesSerial = $int_input;		//save serial to code
			}
		}	
	}
	
	private $use_custom_species;
	public function getUse_custom_species() {
		return $this->use_custom_species;
	}
	public function setUse_custom_species($input) {
		if ( isset($input) ) {
			$this->use_custom_species = (bool)$input;
		}			
	}
	
	private $ic_frequency;
	private $cleanIc_frequency;
	public function getIc_frequency() {
		return $this->ic_frequency;
	}
	public function setIc_frequency($input) {
		if ( isset($input) ) {
			$this->ic_frequency = $input;
		}			
	}
	
	private $cc_frequency;
	private $cleanCc_frequency;
	public function getCc_frequency() {
		return $this->cc_frequency;
	}
	public function setCc_frequency($input) {
		if ( isset($input) ) {
			$this->cc_frequency = $input;
		}			
	}
	
	private $translationRules_NCBI_code;
	public function getTranslationRules_NCBI_code() {
		return $this->translationRules_NCBI_code;
	}
	public function setTranslationRules_NCBI_code($input) {
		if ( isset($input) ) {
			$int_input = intval($input);					//Convert to integer
			$tempList = CodonOpt_Controller_SetupOptimization::getTranslationRulesList();
			$FoundNCBI_code = null;
			foreach ($tempList as $DTO) {					//Go through each item on list
				if ($DTO->getNCBI_code() == $int_input) {	//if input matches that item serial
					$FoundNCBI_code = $int_input;			//valid serial was found
				}
			}
			if ( isset ($FoundNCBI_code) ) {				//If valid serial was found
				$this->translationRules_NCBI_code = $int_input;	//save serial to code
			}
		}
	}
	
	//Error Messages
	private $OptimizationMethodErrorMsg = null;
	private $OptimizeGCErrorMsg = null;
	private $ic_frequencyErrorMsg = null;
	private $cc_frequencyErrorMsg = null;

	//Error Messages have Getters only
	public function getOptimizationMethodErrorMsg() {
		return $this->OptimizationMethodErrorMsg;
	}
	public function getOptimizeGCErrorMsg() {
		return $this->OptimizeGCErrorMsg;
	}
	public function getIc_frequencyErrorMsg() {
		return $this->ic_frequencyErrorMsg;
	}
	public function getCc_frequencyErrorMsg() {
		return $this->cc_frequencyErrorMsg;
	}
	
	
	//Constructor that accepts current job
	public function CodonOpt_Controller_SetupOptimization() {
		parent::__construct();	//Parent extracts job
		$this->optimize_ic = $this->getCurrentJob()->getOptimize_ic();
		$this->optimize_cc = $this->getCurrentJob()->getOptimize_cc();
		$this->optimize_cai = $this->getCurrentJob()->getOptimize_cai();
		$this->optimize_hidden_stop_codon = $this->getCurrentJob()->getOptimize_hidden_stop_codon();
		$this->optimize_gc_mode = $this->getCurrentJob()->getOptimize_gc_mode();
		$this->optimize_gc_target = $this->getCurrentJob()->getOptimize_gc_target();
		$this->clean_optimize_gc_target = $this->getCurrentJob()->getOptimize_gc_target();
		$this->speciesSerial = $this->getCurrentJob()->getInbuilt_species_serial();
		$this->use_custom_species = $this->getCurrentJob()->getUse_custom_species();
		$this->ic_frequency = $this->unCleanFrequency( $this->getCurrentJob()->getIc_frequency() );
		$this->cc_frequency = $this->unCleanFrequency( $this->getCurrentJob()->getCc_frequency() );
		$this->translationRules_NCBI_code = $this->getCurrentJob()->getTranslationRules_NCBI_code();
	}
	
	
	//Check the inputs are correct, and if they are, submit them
	public function checkAndSave() {
		$FailCount = 0;		//Count number of validation failures

		//The other checks are in self-contained subfunctions
		if (! $this->IsValidOptimizationMethod()	) {$FailCount++;}
		if (! $this->IsValidOptimizeGC()			) {$FailCount++;}		
		if (! $this->IsValidIc_frequency()			) {$FailCount++;}
		if (! $this->IsValidCc_frequency()			) {$FailCount++;}
		if (! $this->IsValidCustomHost()			) {$FailCount++;}
		//Before proceeding, all CheckList must be true
		if ($FailCount == 0) {	//If there are no false, commence submission
			$this->saveAndContinue();
		}
	}
	
	
	//Check there is valid input sequence
	private function IsValidOptimizationMethod() {
		$OptimizationMethodCount = 0;
		if ( $this->getOptimize_ic() >= 1 )			//If IC selected
		{ $OptimizationMethodCount++; }				//Add 1 to count
		if ( $this->getOptimize_cc() >= 1 )			//If CC selected
		{ $OptimizationMethodCount++; }				//Add 1 to count
		if ( $this->getOptimize_cai() >= 1 )		//If CAI selected
		{ $OptimizationMethodCount++; }				//Add 1 to count
		if ( $this->getOptimize_hidden_stop_codon() >= 1 )		//If CAI selected
		{ $OptimizationMethodCount++; }				//Add 1 to count			
		if ( $OptimizationMethodCount == 0 ) {		//If no methods selected
			$this->OptimizationMethodErrorMsg = "Please choose to Maximize or Minimize at least 1 Optimization Criteria.";
			return false;							//Then return false
		} else {									//Otherwise at least 1 method selected
			return true;							//Return true
		}		
	}
	
	
	//Check there is valid ic_frequency
	private function IsValidIc_frequency() {
		if ( $this->FindIllegalCharsInFrequency(
			$this->ic_frequency
		) ) {
			$this->ic_frequencyErrorMsg = "Frequency Count can only contain ACGTU, integers, spaces/tabs and linebreaks.";
			return false;
		}
		if ( $this->FindBoth_TU_AreInFrequency(
			$this->ic_frequency
		) ) {
			$this->ic_frequencyErrorMsg = "Both T and U bases detected! Please use just one or the other.";
			return false;
		}
		
		//Generate Collapsed sequence and check length
		$CollapseSeq = $this->CollapseFrequency( $this->ic_frequency );
		if ( strlen($CollapseSeq) > CodonOpt_Controller_SetupOptimization::$ic_frequencyMaxLength ) {
			$this->cc_frequencyErrorMsg = "Individual Codon Frequency has a character limit of ".CodonOpt_Controller_SetupOptimization::$ic_frequencyMaxLength." characters";
			return false;			
		}
		
		//If there is content, check input
		if ( $CollapseSeq != "" ) {				//Do detailed checks only if text is not empty
			$AllLines = explode("\n",$CollapseSeq);
			$AllBases = array();
			foreach ($AllLines as $CurrLine) {			//go through each line
				$tempArray = explode(" ",$CurrLine,2);	//Split into 2 segments
				$CurrBase = $tempArray[0];				//Extract current bases
				$CurrNumber = "";						//extract current number (force empty string if null)
				if ( isset($tempArray[1]) ) {
					$CurrNumber = $tempArray[1];
				}
				
				//Check Base
				$EmptyBase = str_ireplace(CodonOpt_Controller_SetupOptimization::$ACGTU,"",$CurrBase);
				if ($EmptyBase != "") {					//Check that bases are valid
					$this->ic_frequencyErrorMsg = "Invalid Codon found. Please check your input: ".$this::TrimUserErrorInput($CurrBase);
					return false;
				}
				if ( strlen ($CurrBase) != 3 ) {
					$this->ic_frequencyErrorMsg = "Codon should have 3 bases. Please check your input: ".$this::TrimUserErrorInput($CurrBase);
					return false;			
				}			
				
				//Check Number
				if ( ($CurrNumber."") == "" ) {			//Ensure Number is not empty
					$this->ic_frequencyErrorMsg = "A Codon has no associated frequency number: ".$CurrBase;
					return false;			
				} elseif (								//Check if number is valid
					! CodonOpt_Utility::CheckValidPositiveInteger($CurrNumber)
				) {	
					$this->ic_frequencyErrorMsg = "Invalid integer found. Please check your input: ".$this::TrimUserErrorInput($CurrNumber);
					return false;				
				}
				
				//Check for duplicates
				if ( isset($AllBases[$CurrBase]) ) {
					$this->ic_frequencyErrorMsg = "Duplicate Codon Found. Please check your input: ".$CurrBase;
					return false;		
				} else {
					$AllBases[$CurrBase] = true;;
				}
			}
			
			//Check that number of codons meets minimum
			if ( count($AllBases) <= CodonOpt_Controller_SetupOptimization::$MinCodon ) {
				$this->ic_frequencyErrorMsg = "Only ".count($AllBases)." codons were found. There should be a minimum of ".CodonOpt_Controller_SetupOptimization::$MinCodon;
				return false;
			}
		}
		
		//Save cleaned sequence
		$CleanSeq = $CollapseSeq;
		$CleanSeq = str_ireplace("\n",";",$CleanSeq);	//Replace line breaks with semicolons
		$CleanSeq = str_ireplace(" ",":",$CleanSeq);	//Replace spaces with colons
		$this->cleanIc_frequency = $CleanSeq;
		return true;	//If it came this far without returning false, it should be OK
	}
	
	
	//Check there is valid cc_frequency
	private function IsValidCc_frequency() {
		if ( $this->FindIllegalCharsInFrequency(
			$this->cc_frequency
		) ) {
			$this->cc_frequencyErrorMsg = "Frequency Count can only contain ACGTU, integers, spaces/tabs and linebreaks.";
			return false;
		}
		if ( $this->FindBoth_TU_AreInFrequency(
			$this->cc_frequency
		) ) {
			$this->cc_frequencyErrorMsg = "Both T and U bases detected! Please use just one or the other.";
			return false;
		}

		//Generate Collapsed sequence and check length
		$CollapseSeq = $this->CollapseFrequency( $this->cc_frequency );
		if ( strlen($CollapseSeq) > CodonOpt_Controller_SetupOptimization::$cc_frequencyMaxLength ) {
			$this->cc_frequencyErrorMsg = "Codon Context Frequency has a character limit of ".CodonOpt_Controller_SetupOptimization::$cc_frequencyMaxLength." characters";
			return false;			
		}
		
		//If there is content, check input
		if ( $CollapseSeq != "" ) {				//Do detailed checks only if text is not empty
			$AllLines = explode("\n",$CollapseSeq);
			$AllBases = array();
			foreach ($AllLines as $CurrLine) {			//go through each line
				$tempArray = explode(" ",$CurrLine,2);	//Split into 2 segments
				$CurrBase = $tempArray[0];				//Extract current bases
				$CurrNumber = "";						//extract current number (force empty string if null)
				if ( isset($tempArray[1]) ) {
					$CurrNumber = $tempArray[1];
				}
				
				//Check Base
				$EmptyBase = str_ireplace(CodonOpt_Controller_SetupOptimization::$ACGTU,"",$CurrBase);
				if ($EmptyBase != "") {					//Check that bases are valid
					$this->cc_frequencyErrorMsg = "Invalid Codon Pair found. Please check your input: ".$this::TrimUserErrorInput($CurrBase);
					return false;
				}
				if ( strlen ($CurrBase) != 6 ) {
					$this->cc_frequencyErrorMsg = "Codon Pair should have 6 bases. Please check your input: ".$this::TrimUserErrorInput($CurrBase);
					return false;			
				}			
				
				//Check Number
				if ( ($CurrNumber."") == "" ) {
					$this->cc_frequencyErrorMsg = "A Codon Pair has no associated frequency number: ".$CurrBase;
					return false;
				} elseif (
					! CodonOpt_Utility::CheckValidPositiveInteger($CurrNumber)
				) {
					$this->cc_frequencyErrorMsg = "Invalid integer found. Please check your input: ".$this::TrimUserErrorInput($CurrNumber);
					return false;
				}
				
				//Check for duplicates
				if ( isset($AllBases[$CurrBase]) ) {
					$this->cc_frequencyErrorMsg = "Duplicate Codon Pair Found. Please check your input: ".$CurrBase;
					return false;		
				} else {
					$AllBases[$CurrBase] = true;;
				}
			}
			
			//Check that number of codons meets minimum
			if ( count($AllBases) <= CodonOpt_Controller_SetupOptimization::$MinCodonPair ) {
				$this->cc_frequencyErrorMsg = "Only ".count($AllBases)." codon pairs were found. There should be a minimum of ".CodonOpt_Controller_SetupOptimization::$MinCodonPair;
				return false;
			}
		}
		
		//Save cleaned sequence
		$CleanSeq = $CollapseSeq;
		$CleanSeq = str_ireplace("\n",";",$CleanSeq);	//Replace line breaks with semicolons
		$CleanSeq = str_ireplace(" ",":",$CleanSeq);	//Replace spaces with colons
		$this->cleanCc_frequency = $CleanSeq;
		return true;	//If it came this far without returning false, it should be OK
	}

	
	//Check whether Optimize GC content Target is valid
	private static $OptimizeGCTarget_ValidChars = array("0","1","2","3","4","5","6","7","8","9",".");
	private function IsValidOptimizeGC() {
		if ($this->optimize_gc_target == "") {			//If it is empty string
			$this->clean_optimize_gc_target = null;		//Set to null
			if ($this->optimize_gc_mode) {				//If Optimizing GC
				$this->OptimizeGCErrorMsg = "Please enter target GC content value";
				return false;
			}
		} else {										//Otherwise note empty string
			$CheckStr = str_ireplace(					//Remove Valid Chars
				$this::$OptimizeGCTarget_ValidChars,
				"",$this->optimize_gc_target
			);
			if ($CheckStr == "") {						//If String only has Valid Characters
				$CheckStr = str_ireplace(				//Remove Digits
					CodonOpt_Utility::getDigits(),"",$this->optimize_gc_target
				);		
				if ( strlen($CheckStr)<=1 ) {			//If 1 or fewer decimal point
					$tempDouble 						//Convert to double
						= doubleval( $this->optimize_gc_target );	
					if ($tempDouble <= 100) {			//Check Value
						$this->clean_optimize_gc_target = $tempDouble;
						return true;					//Value is OK
					} else {							//Otherwise value exceeds 100
						$this->OptimizeGCErrorMsg = "Percentage cannot exceed 100%";
						return false;
					}
				} else {								//Otherwise 2 or more decimal points
					$this->OptimizeGCErrorMsg = "Please use only one decimal point";
					return false;			
				}
			} else {									//Otherwise invalid chars detected
				$this->OptimizeGCErrorMsg = "Only numbers and one decimal point are allowed.";
				return false;
			}
		}
		return true;	//If it comes this far without error
	}

	
	//Check there is valid Custom Host
	//Must call this after IsValidCc_frequency/IsValidIc_frequency as it checks cleanIc_frequency/cleanCc_frequency
	private function IsValidCustomHost() {
		//If Using Custom Species
		if ($this->use_custom_species) {
			//If using Individual Codon, make sure text area has content
			if ( $this->getOptimize_ic() >= 1 ) {
				if ( strlen($this->cleanIc_frequency) >= 1 ) {			//If there is valid content (do nothing)
				} elseif( strlen($this->ic_frequencyErrorMsg) >= 1 ) {	//If there is an error message (do nothing)
				} else {												//Otherwise no content and no error message
					$this->ic_frequencyErrorMsg = "Since Individual Codon Method has been selected, please enter a frequency count.";
					return false;
				}
			}
			
			//If using Codon Adaptation Index, make sure text area has content
			if ( $this->getOptimize_cai() >= 1 ) {
				if ( strlen($this->cleanIc_frequency) >= 1 ) {			//If there is valid content (do nothing)
				} elseif( strlen($this->ic_frequencyErrorMsg) >= 1 ) {	//If there is an error message (do nothing)
				} else {												//Otherwise no content and no error message
					$this->ic_frequencyErrorMsg = "Since Codon Adaptation Index Method has been selected, please enter a frequency count.";
					return false;
				}
			}
			
			//If using Codon Context, make sure text area has content
			if ( $this->getOptimize_cc() >= 1 ) {
				if ( strlen($this->cleanCc_frequency) >= 1 ) {			//If there is valid content (do nothing)
				} elseif( strlen($this->cc_frequencyErrorMsg) >= 1 ) {	//If there is an error message (do nothing)
				} else {												//Otherwise no content and no error message
					$this->cc_frequencyErrorMsg = "Since Codon Context Method has been selected, please enter a frequency count.";
					return false;
				}
			}
		} 
		return true;	//If it came this far without returning false, it should be OK
	}
	
	
	//This is meant to check frequency for illegal characters
	//It returns true if any are found
	private function FindIllegalCharsInFrequency($input) {
		$tempCopy = $input;
		$tempCopy = str_ireplace(CodonOpt_Controller_SetupOptimization::$ACGTU,"",$tempCopy);
		$tempCopy = str_ireplace(CodonOpt_Utility::getDigits(),"",$tempCopy);
		$tempCopy = str_ireplace(CodonOpt_Utility::GetTabSpacesLineBreaks(),"",$tempCopy);
		if ($tempCopy == "") {	//If string is now empty
			return false;		//no illegal characters
		} else {				//Otherwise string not empty
			return true;		//Illegal characters found
		}
	}
	
	
	//This is meant to check if BOTH U and T were used
	private function FindBoth_TU_AreInFrequency($input) {
		$BaseCopy = $input;
		$BaseCopy = str_ireplace(CodonOpt_Utility::getDigits(),"",$BaseCopy);
		$BaseCopy = str_ireplace(CodonOpt_Utility::GetTabSpacesLineBreaks(),"",$BaseCopy);
		$ATCG = array("A","T","C","G");
		$AUCG = array("A","U","C","G");
		$TCopy = str_ireplace($ATCG,"",$BaseCopy);
		$UCopy = str_ireplace($AUCG,"",$BaseCopy);
		if ($TCopy == "") { return false; }	//If this is empty, then T is in use
		if ($UCopy == "") { return false; }	//If this is empty, then U is in use
		return true;						//Otherwise both T and U are in use
	}
	
	
	//Collapses the frequency text
	//removes excess linebreaks and spaces
	private function CollapseFrequency($input) {
		$outputLines = array();
		$tempLines = explode( "\n",strtoupper($input) );	//Expand to individual lines
		foreach ($tempLines as $currLine) {					//For each line
			$cleanLine = trim($currLine);					//Remove white space on either end
			$cleanLine = str_replace("\r","",$cleanLine);	//remove any other linebreaks
			$cleanLine = str_replace("\t"," ",$cleanLine);	//replace tabs with spaces
			
			//Collapse multiple spaces to just one
			$oldLine;
			do {
				$oldLine = $cleanLine;						//store old line
				$cleanLine 									//collapse old line
					= str_replace("  "," ",$oldLine);
			}
			while ( $oldLine != $cleanLine );				//Repeat until old and new line are the same
			
			if ( $cleanLine == "" ) {						//If line is empty
				//do nothing
			} elseif ( $cleanLine == " " ) {				//Otherwise line is just a space
				//do nothing
			} else {										//Otherwise line has content
				array_push($outputLines,$cleanLine);
			}
		}
		return implode("\n",$outputLines)."";				//Implode lines and return (force empty string if null)
	}
	
	
	//Reverses the cleaning process, for displaying after extraction from DB
	private function unCleanFrequency($input) {
		$output = $input;
		$output = str_replace(";","\r",$output);	
		$output = str_replace(":"," ",$output);
		return $output;
	}
	
	
	//This function carries out submission of data for continuation
	private function saveAndContinue() {
		//
		$this->getCurrentJob()->setOptimize_ic( $this->optimize_ic );
		$this->getCurrentJob()->setOptimize_cc( $this->optimize_cc );
		$this->getCurrentJob()->setOptimize_cai( $this->optimize_cai );
		$this->getCurrentJob()->setOptimize_hidden_stop_codon( $this->optimize_hidden_stop_codon );
		
		//Update GC content
		$this->getCurrentJob()->setOptimize_gc_mode( $this->optimize_gc_mode );
		$this->getCurrentJob()->setOptimize_gc_target( $this->clean_optimize_gc_target );
		
		//Update Inbuilt Species
		$this->updateInbuiltSpeciesSerialToJob();
		
		//Use Custom Species and IC/CC textboxes
		$this->getCurrentJob()->setUse_custom_species( $this->use_custom_species );
		{	//If no IC frequency save null instead of empty string
			$nullableSequence = $this->cleanIc_frequency;	//Use Nullable sequence
			if ($nullableSequence == "") 					//If input is empty
			{ $nullableSequence = null; }					//Just save null instead
			$this->getCurrentJob()->setIc_frequency( $nullableSequence );
		}
		{	//If no CC frequency save null instead of empty string
			$nullableSequence = $this->cleanCc_frequency;	//Use Nullable sequence
			if ($nullableSequence == "") 					//If input is empty
			{ $nullableSequence = null; }					//Just save null instead
			$this->getCurrentJob()->setCc_frequency( $nullableSequence );
		}		
		$this->getCurrentJob()->setTranslationRules_NCBI_code($this->translationRules_NCBI_code);
		CodonOpt_DAO_user_jobs::update($this->getCurrentJob());
		$Success = CodonOpt_DAO_user_jobs::update($this->getCurrentJob());	//Update Job
		
		//When finished submitting, redirect to view results
		if ($Success) {	//If successful
			if (		//If using custom species
				$this->getCurrentJob()->getUse_custom_species()
			) {
				header("Location: setup_exclusion.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
				exit;	//Skip select genes and go to Exclusion
			} else {	//Otherwise using inbuilt species
				header("Location: setup_select_genes.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
				exit;	//Go to gene selection
			}
		} else {		//Otherwise not successful
			$this->OptimizationMethodErrorMsg = "Internal Database Error. Please contact us for help and include a link to this page.";
		}
	}
	
	
	//Update Inbuilt species serial to job
	private function updateInbuiltSpeciesSerialToJob() {
		if ( 														//If new serial is NOT the same
			$this->getCurrentJob()->getInbuilt_species_serial() != $this->speciesSerial
		) {															//Reset the Fields
			$this->getCurrentJob()->setInbuilt_species_serial 		//Change Species
				( $this->speciesSerial );							//Clear Current Sort Gene List
			$this->getCurrentJob()->setInbuilt_species_gene_sort( null );
			$GeneListArray = CodonOpt_DAO_genes::generateGeneListForSpeciesSerial($this->speciesSerial);
			$GeneListString = implode(
				CodonOpt_DAO_user_jobs::getInbuilt_species_gene_list_delimiter(),
				$GeneListArray
			);
			$this->getCurrentJob()->setInbuilt_species_gene_list(	//Default to all genes for this species
				$GeneListString
			);
		}	
	}
	
	
	//Save Valid Fields, and go to Upload Fasta Page
	public function saveValidAndUploadFasta() {
		//If optimization method is valid, save it
		if ($this->IsValidOptimizationMethod() ) {
			$this->getCurrentJob()->setOptimize_ic( $this->optimize_ic );
			$this->getCurrentJob()->setOptimize_cc( $this->optimize_cc );
			$this->getCurrentJob()->setOptimize_cai( $this->optimize_cai );
			$this->getCurrentJob()->setOptimize_hidden_stop_codon( $this->optimize_hidden_stop_codon );	
		}
		
		//IF GC content state + value is valid, save it
		if ($this->IsValidOptimizeGC() ) {
			$this->getCurrentJob()->setOptimize_gc_mode( $this->optimize_gc_mode );
			$this->getCurrentJob()->setOptimize_gc_target( $this->clean_optimize_gc_target );
		}
		
		//Always Update Inbuilt Species
		$this->updateInbuiltSpeciesSerialToJob();
		
		//DO NOT save use custom species here
		//Instead this will be updated AFTER fasta has been uploaded
		//Similiarly, do not adjust the IC/CC textboxes
		//So that the Use Custom Species / IC textbox / CC textbox will be in a consistent state
		
		
		//Always update Translation Rules NCBI code
		$this->getCurrentJob()->setTranslationRules_NCBI_code($this->translationRules_NCBI_code);
		$Success = CodonOpt_DAO_user_jobs::update($this->getCurrentJob());	//Update Job
		if ($Success) {	//If successfuly
			header("Location: setup_upload_fasta.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
			exit;		//When done, go to Fasta Upload page
		} else {		//Otherwise not successful
			$this->OptimizationMethodErrorMsg = "Internal Database Error. Please contact us for help and include a link to this page.";
		}				//Show error message
	}
}
?>