<?php
require_once "CodonOpt_Controller_Setup_Ancestor.php";

//This Class is meant to control the Exclusion Sequence page
//KNOWN ISSUES: An exclusion sequence that meets the minimum unambiguous length requirement, but which has too many 10 or more 'N's (e.g. ATCGNNNNNNNNNN) will crash the PHP process when we try to convert it to unambiguous bases.
class CodonOpt_Controller_SetupExclusion extends CodonOpt_Controller_Setup_Ancestor {
	//Internal Variables 
	private static $ACGTU = array("A","C","G","T","U");	//List of Unambiguous bases
	private static $AllowedAmbiguousBases = array("Y","R","W","S","K","M","D","V","H","B","N");	//List of allowed ambiguous bases
	private static $MinExclusionSequenceLength=4;
	private static $MaxExclusionTextLength=10000;
	public static function getMaxExclusionTextLength() 
	{ return CodonOpt_Controller_SetupExclusion::$MaxExclusionTextLength; }
	
	//Form Data, and Getters and Setters
	//Setters include validation before saving data
	//Getters should return some default if property is not defined
	private $ExclusionSequence = null;
	private $CleanedExclusion = null;	//Cleaned Exclusion convert to upper case and without spaces/tabs/linebreaks/underscores etc
	public function setExclusionSequence($input) {
		if ( isset($input) ) {
			$this->ExclusionSequence = $input;
		}
	}
	public function getExclusionSequence() {
		return $this->ExclusionSequence;
	}
	
	//Consecutive Repeats
	private $repeat_consec_mode = null;
	public function setRepeat_consec_mode($input) {
		if ( isset($input) ) {
			$this->repeat_consec_mode = intval($input);	//Auto convert to integer
		}
	}
	public function getRepeat_consec_mode() {
		return $this->repeat_consec_mode;
	}
	private $repeat_consec_length = null;
	private $repeat_consec_length_clean = null;
	public function setRepeat_consec_length($input) {
		if ( isset($input) ) {
			$this->repeat_consec_length = trim($input);
		}
	}
	public function getRepeat_consec_length() {
		return $this->repeat_consec_length;
	}
	private $repeat_consec_count = null;
	private $repeat_consec_count_clean = null;
	public function setRepeat_consec_count($input) {
		if ( isset($input) ) {
			$this->repeat_consec_count = trim($input);
		}
	}
	public function getRepeat_consec_count() {
		return $this->repeat_consec_count;
	}
	
	//Allmotif Repeats
	private $repeat_allmotif_mode = null;
	public function setRepeat_allmotif_mode($input) {
		if ( isset($input) ) {
			$this->repeat_allmotif_mode = intval($input);	//Auto convert to integer
		}
	}
	public function getRepeat_allmotif_mode() {
		return $this->repeat_allmotif_mode;
	}
	private $repeat_allmotif_length = null;
	private $repeat_allmotif_length_clean = null;
	public function setRepeat_allmotif_length($input) {
		if ( isset($input) ) {
			$this->repeat_allmotif_length = trim($input);
		}
	}
	public function getRepeat_allmotif_length() {
		return $this->repeat_allmotif_length;
	}
	private $repeat_allmotif_count = null;
	private $repeat_allmotif_count_clean = null;
	public function setRepeat_allmotif_count($input) {
		if ( isset($input) ) {
			$this->repeat_allmotif_count = trim($input);
		}
	}
	public function getRepeat_allmotif_count() {
		return $this->repeat_allmotif_count;
	}
	
	//Error Messages
	private $ExclusionSequenceErrorMsg = null;
	private $repeat_consec_lengthErrorMsg = null;
	private $repeat_consec_countErrorMsg = null;
	private $repeat_allmotif_lengthErrorMsg = null;
	private $repeat_allmotif_countErrorMsg = null;
	//Error Messages have Getters only
	public function getExclusionSequenceErrorMsg() {
		return $this->ExclusionSequenceErrorMsg;
	}
	public function getRepeat_consec_lengthErrorMsg() {
		return $this->repeat_consec_lengthErrorMsg;
	}
	public function getRepeat_consec_countErrorMsg() {
		return $this->repeat_consec_countErrorMsg;
	}
	public function getRepeat_allmotif_lengthErrorMsg() {
		return $this->repeat_allmotif_lengthErrorMsg;
	}
	public function getRepeat_allmotif_countErrorMsg() {
		return $this->repeat_allmotif_countErrorMsg;
	}
	
	//Constructor takes in a DTO
	public function CodonOpt_Controller_SetupExclusion() {
		parent::__construct();	//Parent extracts job 
		$this->ExclusionSequence = $this->getCurrentJob()->getExclusion_sequence();
		$this->repeat_consec_mode = $this->getCurrentJob()->getRepeat_consec_mode();
		$this->repeat_consec_length = $this->getCurrentJob()->getRepeat_consec_length();
		$this->repeat_consec_length_clean = $this->getCurrentJob()->getRepeat_consec_length();
		$this->repeat_consec_count = $this->getCurrentJob()->getRepeat_consec_count();
		$this->repeat_consec_count_clean = $this->getCurrentJob()->getRepeat_consec_count();
		$this->repeat_allmotif_mode = $this->getCurrentJob()->getRepeat_allmotif_mode();
		$this->repeat_allmotif_length = $this->getCurrentJob()->getRepeat_allmotif_length();
		$this->repeat_allmotif_length_clean = $this->getCurrentJob()->getRepeat_allmotif_length();
		$this->repeat_allmotif_count = $this->getCurrentJob()->getRepeat_allmotif_count();
		$this->repeat_allmotif_count_clean = $this->getCurrentJob()->getRepeat_allmotif_count();
	}
	
	//Check the inputs are correct, and if they are, submit them
	public function checkAndSave() {
		$FailCount = 0;	//Count number of validation failures
		
		//The other checks are in self-contained subfunctions
		if (! $this->IsValidExclusionSequenceList()		) {$FailCount++;}
		if (! $this->IsValidRepeat_consec_removal()		) {$FailCount++;}
		if (! $this->IsValidRepeat_allmotif_removal()	) {$FailCount++;}
		
		//Before proceeding, all CheckList must be true
		if ($FailCount == 0) {	//If there are no false, commence submission
			$this->saveAndContinue();
		}
	}
	
	//Check there is valid List of Exclusion sequences
	private $ExclusionRepeatCheck;					//Array
	private function IsValidExclusionSequenceList() {
		if ( isset($this->ExclusionSequence) ) {				//If sequence is set
			if ($this->ExclusionSequence == "") {				//If there is NO exclusion sequence
				return true;									//Return true as exclusion sequence is optional
			} else {											//Otherwise there is exclusion sequence
				$this->CleanedExclusion 						//Clean Sequence
					= CodonOpt_Utility::CleanSequence($this->ExclusionSequence);	
				if ( strlen($this->CleanedExclusion)			//If it is within length limit
					<= CodonOpt_Controller_SetupExclusion::$MaxExclusionTextLength
				) {
					$tempArrayA 								//Break up into Comma Seperated List
						= explode(",",$this->CleanedExclusion);
					$tempArrayB = array();
					foreach ($tempArrayA as $ExclusionItem) {	//For each item
						if ($ExclusionItem != "") {				//If item is NOT empty
							array_push($tempArrayB,$ExclusionItem);	//Store it to Array B 
						}										//This removes "empty" sequences caused by accidental double commas ",," and extra comma after the last sequence
					}
					$this->CleanedExclusion = implode(",",$tempArrayB);
					$this->ExclusionRepeatCheck = array();		//Reset ExclusionRepeatCheck
					foreach ($tempArrayB as $ExclusionItem) {	//Go through each item
						if (! 									//If it fails the subfunction check
							$this->CheckExclusionSequence($ExclusionItem)
						) {	
							return false;						//Exit
						}
					}				
					return true;								//If it manages to reach this point sequence is OK
				} else {										//Otherwise it exceeds length
					$this->ExclusionSequenceErrorMsg = "Total Exclusion Sequence Text should not be more than ".CodonOpt_Controller_SetupExclusion::$MaxExclusionTextLength." characters long";
					return false;								//Give this error message and return false	
				}
			}
		} else {												//Otherwise sequence not set (first load)
			return false;										//return false
		}
	}

	//Check each exclusion sequence
	//Maybe later on can institute length limits (between 4 to 10 bases??)
	private function CheckExclusionSequence($Input) {
		$tempCopy = $Input;				//Make a temporary copy
		$tempCopy = str_ireplace(		//Remove unambiguous bases
			$this::$ACGTU,
			"",$tempCopy
		);
		$tempCopy = str_ireplace(		//Remove allowed ambiguous bases
			$this::$AllowedAmbiguousBases,
			"",$tempCopy
		);
		if ($tempCopy == "") {			//If nothing is left, the only allowed characters are inside
			$tempCopy = $Input;			//Make a temporary copy
			$tempCopy = str_ireplace(	//Remove allowed ambiguous bases
				$this::$AllowedAmbiguousBases,
				"",$tempCopy
			);
			if ( strlen($tempCopy) >=	//Check that it meets minimum length
				CodonOpt_Controller_SetupExclusion::$MinExclusionSequenceLength
			) {	
				$tempCopy = $Input;
				$tempCopy = 			//Replace U with T
					str_ireplace("U","T",$tempCopy);
				if (true) {				//If True: convert into list of all possible unambiguous sequences
					$checkArray = CodonOpt_Utility::convertAmbigDNAToAllUnambig($tempCopy);
				} else {				//Otherwise do not convert
					$checkArray = array($tempCopy);
				}
				foreach ($checkArray as $checkSeq) {
					if (				//Check for repeated sequences
						array_key_exists($checkSeq,$this->ExclusionRepeatCheck)
					) {					//If sequence already exists, then return error
						$this->ExclusionSequenceErrorMsg = $Input." overlaps with ".$this->ExclusionRepeatCheck[$checkSeq];
						return false;
					} else {			//If sequence does not exist, store it for later check
						//Use unambig U>T seq as key, and ambig seq as value
						//If there is a unambig repeat, we will display the ambig seq (which is what the user input) in the error message
						$this->ExclusionRepeatCheck[$checkSeq] = $Input;
					}
				}
				return true;			//If the loop completed without returning false, return true
			} else {					//Otherwise it does not meet minimum length
				$this->ExclusionSequenceErrorMsg = "Each exclusion sequence should have at least ".CodonOpt_Controller_SetupExclusion::$MinExclusionSequenceLength." unambiguous bases. This one is too short: ".$Input;
				return false;			//Give this error message and return false			
			}
		} else {						//Otherwise some non-ACGTU character is left
			$this->ExclusionSequenceErrorMsg = "Exclusion Sequence should only contain ".implode(", ",$this::$ACGTU).", ".implode(", ",$this::$AllowedAmbiguousBases).", commas ',' and spaces";
			return false;				//Give this error message and return false
		}
	}
	
	//==========================
	// Handle Consecutive Repeat
	//==========================
	private static $Repeat_consec_length_min = 1;
	private static $Repeat_consec_length_max = 99;
	private static $Repeat_consec_count_min = 2;
	private static $Repeat_consec_count_max = 99;
	
	private function IsValidRepeat_consec_removal() {
		$FailCount = 0;
		if (! $this->IsValidRepeat_consec_removal_length() )	{ $FailCount++; }
		if (! $this->IsValidRepeat_consec_removal_count() )		{ $FailCount++; }
		if ($FailCount == 0 ) {		//If no failures so far, then check mode
			if (! $this->IsValidRepeat_consec_removal_mode() )		{ $FailCount++; }
		}
		if ($FailCount == 0 )	{ return true; }	//If bi failures return true
		else					{ return false; }	//Otherwise return false
	}
	
	//Clean Sequence
	private function IsValidRepeat_consec_removal_length() {
		//Handle repeat_consec_length
		if ($this->repeat_consec_length == "") {		//If string is empty
			$this->repeat_consec_length_clean = null;	//Set Null
		} else {										//Otherwise string not empty
			if (										//If valid integer
				CodonOpt_Utility::CheckValidPositiveInteger($this->repeat_consec_length)
			) {											//Save Clean Input
				$this->repeat_consec_length_clean = intval($this->repeat_consec_length);
				if(										//Ensure content greater than zero
					$this->repeat_consec_length_clean < $this::$Repeat_consec_length_min
				) {	
					$this->repeat_consec_lengthErrorMsg = "Please enter a value of ".$this::$Repeat_consec_length_min." or larger.";
					return false;
				} elseif (								//If it exceeds cap (hacked stream)
					$this->repeat_consec_length_clean > $this::$Repeat_consec_length_max
				) {										//Set it at cap
					$this->repeat_consec_lengthErrorMsg = "Please enter a value of ".$this::$Repeat_consec_length_max." or smaller.";
					return false;
				}
			} else {									//Otherwise not valid integer
				$this->repeat_consec_lengthErrorMsg = "Please only use positive integers.";
				return false;
			}
		}
		return true;	//If it managed to reach here, return true
	}
	
	private function IsValidRepeat_consec_removal_count() {
		//Handle repeat_consec_count
		if ($this->repeat_consec_count == "") {			//If string is empty
			$this->repeat_consec_count_clean = null;	//Set Null
		} else {										//Otherwise string not empty
			if (										//If valid integer
				CodonOpt_Utility::CheckValidPositiveInteger($this->repeat_consec_count)
			) {											//Save Clean Input
				$this->repeat_consec_count_clean = intval($this->repeat_consec_count);
				if(										//Ensure content is 2 or higher
					$this->repeat_consec_count_clean < $this::$Repeat_consec_count_min
				) {
					$this->repeat_consec_countErrorMsg = "Please enter a value of ".$this::$Repeat_consec_count_min." or larger.";
					return false;
				} elseif (								//If it exceeds cap (hacked stream)
					$this->repeat_consec_count_clean > $this::$Repeat_consec_count_max
				) {										//Set it at cap
					$this->repeat_consec_countErrorMsg = "Please enter a value of ".$this::$Repeat_consec_count_max." or smaller.";
					return false;
				}
			} else {									//Otherwise not valid integer
				$this->repeat_consec_countErrorMsg = "Please only use positive integers.";
				return false;
			}
		}
		return true;	//If it managed to reach here, return true	
	}
	
	private function IsValidRepeat_consec_removal_mode() {	
		if ($this->repeat_consec_mode) {								//If Remove Repeat enabled
			if (! isset($this->repeat_consec_length_clean) ) {	//Ensure there is content
				//(If user had instead entered invalid value, it would have already returned false above
				$this->repeat_consec_lengthErrorMsg = "Consecutive repeat removal enabled. Please enter a value.";
				return false;
			}
			if (! isset($this->repeat_consec_count_clean) ) {	//Ensure there is content
				//(If user had instead entered invalid value, it would have already returned false above
				$this->repeat_consec_countErrorMsg = "Consecutive repeat removal enabled. Please enter a value.";
				return false;
			}
		}
		return true;
	}
	
	//========================
	// Handle Allmotif Repeats
	//========================
	private static $Repeat_allmotif_length_min = 7;
	private static $Repeat_allmotif_length_max = 99;
	private static $Repeat_allmotif_count_min = 2;
	private static $Repeat_allmotif_count_max = 99;
	
	private function IsValidRepeat_allmotif_removal() {
		$FailCount = 0;
		if (! $this->IsValidRepeat_allmotif_removal_length() )	{ $FailCount++; }
		if (! $this->IsValidRepeat_allmotif_removal_count() )		{ $FailCount++; }
		if ($FailCount == 0 ) {		//If no failures so far, then check mode
			if (! $this->IsValidRepeat_allmotif_removal_mode() )		{ $FailCount++; }
		}
		if ($FailCount == 0 )	{ return true; }	//If bi failures return true
		else					{ return false; }	//Otherwise return false
	}
	
	//Clean Sequence
	private function IsValidRepeat_allmotif_removal_length() {
		//Handle repeat_allmotif_length
		if ($this->repeat_allmotif_length == "") {		//If string is empty
			$this->repeat_allmotif_length_clean = null;	//Set Null
		} else {										//Otherwise string not empty
			if (										//If valid integer
				CodonOpt_Utility::CheckValidPositiveInteger($this->repeat_allmotif_length)
			) {											//Save Clean Input
				$this->repeat_allmotif_length_clean = intval($this->repeat_allmotif_length);
				if(										//Ensure content greater than zero
					$this->repeat_allmotif_length_clean < $this::$Repeat_allmotif_length_min
				) {	
					$this->repeat_allmotif_lengthErrorMsg = "Please enter a value of ".$this::$Repeat_allmotif_length_min." or larger.";
					return false;
				} elseif (								//If it exceeds cap (hacked stream)
					$this->repeat_allmotif_length_clean > $this::$Repeat_allmotif_length_max
				) {										//Set it at cap
					$this->repeat_allmotif_lengthErrorMsg = "Please enter a value of ".$this::$Repeat_allmotif_length_max." or smaller.";
					return false;
				}
			} else {									//Otherwise not valid integer
				$this->repeat_allmotif_lengthErrorMsg = "Please only use positive integers.";
				return false;
			}
		}
		return true;	//If it managed to reach here, return true
	}
	
	private function IsValidRepeat_allmotif_removal_count() {
		//Handle repeat_allmotif_count
		if ($this->repeat_allmotif_count == "") {			//If string is empty
			$this->repeat_allmotif_count_clean = null;	//Set Null
		} else {										//Otherwise string not empty
			if (										//If valid integer
				CodonOpt_Utility::CheckValidPositiveInteger($this->repeat_allmotif_count)
			) {											//Save Clean Input
				$this->repeat_allmotif_count_clean = intval($this->repeat_allmotif_count);
				if(										//Ensure content is 2 or higher
					$this->repeat_allmotif_count_clean < $this::$Repeat_allmotif_count_min
				) {
					$this->repeat_allmotif_countErrorMsg = "Please enter a value of ".$this::$Repeat_allmotif_count_min." or larger.";
					return false;
				} elseif (								//If it exceeds cap (hacked stream)
					$this->repeat_allmotif_count_clean > $this::$Repeat_allmotif_count_max
				) {										//Set it at cap
					$this->repeat_allmotif_countErrorMsg = "Please enter a value of ".$this::$Repeat_allmotif_count_max." or smaller.";
					return false;
				}
			} else {									//Otherwise not valid integer
				$this->repeat_allmotif_countErrorMsg = "Please only use positive integers.";
				return false;
			}
		}
		return true;	//If it managed to reach here, return true	
	}
	
	private function IsValidRepeat_allmotif_removal_mode() {	
		if ($this->repeat_allmotif_mode) {								//If Remove Repeat enabled
			if (! isset($this->repeat_allmotif_length_clean) ) {	//Ensure there is content
				//(If user had instead entered invalid value, it would have already returned false above
				$this->repeat_allmotif_lengthErrorMsg = "Consecutive repeat removal enabled. Please enter a value.";
				return false;
			}
			if (! isset($this->repeat_allmotif_count_clean) ) {	//Ensure there is content
				//(If user had instead entered invalid value, it would have already returned false above
				$this->repeat_allmotif_countErrorMsg = "Consecutive repeat removal enabled. Please enter a value.";
				return false;
			}
		}
		return true;
	}
	
	//==========
	// Save Data
	//==========
	private function saveAndContinue() {
		$nullableSequence = $this->CleanedExclusion;	//Use Nullable sequence
		if ($nullableSequence == "") 					//If input is empty
		{ $nullableSequence = null; }					//Just save null instead
		$this->getCurrentJob()->setExclusion_sequence($nullableSequence);
		$this->getCurrentJob()->setRepeat_consec_mode($this->repeat_consec_mode);
		$this->getCurrentJob()->setRepeat_consec_length($this->repeat_consec_length_clean);
		$this->getCurrentJob()->setRepeat_consec_count($this->repeat_consec_count_clean);
		$this->getCurrentJob()->setRepeat_allmotif_mode($this->repeat_allmotif_mode);
		$this->getCurrentJob()->setRepeat_allmotif_length($this->repeat_allmotif_length_clean);
		$this->getCurrentJob()->setRepeat_allmotif_count($this->repeat_allmotif_count_clean);
		CodonOpt_DAO_user_jobs::update($this->getCurrentJob());
		//When finished submitting, redirect to view results
		header("Location: setup_start_run.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
		exit;
	}
}
?>