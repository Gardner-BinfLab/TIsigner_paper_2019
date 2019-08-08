<?php
require_once "CodonOpt_Controller_Setup_Ancestor.php";
require_once "CodonOpt_DAO_translationrules.php";
require_once "CodonOpt_Utility.php";
require_once "CodonOpt_StandardizeNucleotide_DNA.php";
require_once "CodonOpt_StandardizeNucleotide_RNA.php";


//This class is meant to control the Protein Sequence submission page
abstract class CodonOpt_Controller_Setup_Sequence_Ancestor extends CodonOpt_Controller_Setup_Ancestor {
	//Internal Variables
	private static $RHKDESTNQCGPAVILMFYW = 
		array("R","H","K","D","E","S","T","N","Q","C","G","P","A","V","I","L","M","F","Y","W");

	//Translation Rules List
	private static $TranslationRulesList;
	public function getTranslationRulesList() {
		if (! isset(CodonOpt_Controller_Setup_Sequence_Ancestor::$TranslationRulesList) ) {	//If list is not yet set
			CodonOpt_Controller_Setup_Sequence_Ancestor::$TranslationRulesList				//retrieve from database
				= CodonOpt_DAO_translationrules::selectAllTranslationRules();
		}
		return CodonOpt_Controller_Setup_Sequence_Ancestor::$TranslationRulesList;
	}
	
	//Limits on Length of input sequence
	private static $InputSequenceMaxLength = 10000;
	public static function getInputSequenceMaxLength() 
	{ return CodonOpt_Controller_Setup_Sequence_Ancestor::$InputSequenceMaxLength; }
	private static $TitleMaxLength = 75;
	public static function getTitleMaxLength() 
	{ return CodonOpt_Controller_Setup_Sequence_Ancestor::$TitleMaxLength; }
	
	
	//Form Data, and Getters and Setters
	//Setters include validation before saving data
	//Getters should return some default if property is not defined
	
	//Whether input is protein
	private $is_input_protein;
	public function getIs_input_protein() {
		if ( isset($this->is_input_protein) ) {		//If property is set
			return $this->is_input_protein;			//Return property
		} else {									//Otherwise property not set
			return 1;								//Return 1 by default
		}
	}
	public function setIs_input_protein($input) {
		if ( isset($input) ) {
			$this->is_input_protein = (bool)$input;
		}			
	}
	
	//Selected Translation Rules Code
	private $nucleotide_input_translation_rule;
	public function getNucleotide_input_translation_rule() {
		if ( isset($this->nucleotide_input_translation_rule) ) {	//If property is set
			return $this->nucleotide_input_translation_rule;		//Return property
		} else {													//Otherwise property not set
			return 1;												//Return 1 by default
		}
	}
	public function setNucleotide_input_translation_rule($input) {
		if ( isset($input) ) {
			$int_input = intval($input);								//Convert to integer
			$tempList = CodonOpt_Controller_Setup_Sequence_Ancestor::getTranslationRulesList();
			$FoundNCBI_code = null;
			foreach ($tempList as $DTO) {								//Go through each item on list
				if ($DTO->getNCBI_code() == $int_input) {				//if input matches that item serial
					$FoundNCBI_code = $int_input;						//valid serial was found
				}
			}
			if ( isset ($FoundNCBI_code) ) {							//If valid serial was found
				$this->nucleotide_input_translation_rule = $int_input;	//save serial to code
			}
		}
	}
	
	//Input Sequence
	private $InputSequence = null;
	public function setInputSequence($input) {
		if ( isset($input) ) {
			$this->InputSequence = $input;
		}
	}
	public function getInputSequence() {
		return $this->InputSequence;
	}
	//Cleaned input convert to upper case and without spaces/tabs/linebreaks/underscores etc
	private $CleanedInput = null;
	protected function getCleanedInput() {
		return $this->CleanedInput;
	}
	
	//Title
	private $Title = null;
	public function setTitle($input) {
		if ( isset($input) ) {
			$this->Title = $input;
		}
	}
	public function getTitle() {
		return $this->Title;
	}
	
	//Error Messages
	private $InputSequenceErrorMsg = null;
	private $TitleErrorMsg = null;
	//Error Messages have Getters only
	public function getInputSequenceErrorMsg() {
		return $this->InputSequenceErrorMsg;
	}
	public function getTitleErrorMsg() {
		return $this->TitleErrorMsg;
	}
	
	//Check the inputs are correct, and if they are, submit them
	public function checkAndSave() {
		$FailCount = 0;	//Count number of validation failures

		//The other checks are in self-contained subfunctions
		if (! $this->IsValidInputSequence() ) {$FailCount++;}	//Check Input Sequence
		if (! $this->IsValidTitle()) {$FailCount++;}			//Check Title
		
		//Before proceeding, all CheckList must be true
		if ($FailCount == 0) {	//If there are no false, commence submission
			$this->saveAndContinue();
		}
	}
	
	//Check there is valid input sequence
	private function IsValidInputSequence() {
		if ( isset($this->InputSequence) ) {				//If there is input sequence
			$this->CleanedInput = CodonOpt_Utility::CleanSequence($this->InputSequence);
			if ( $this->CleanedInput == "" ) {				//If Cleaned sequence is empty
				$this->InputSequenceErrorMsg = "Please enter some input sequence!";
				return false;								//Input Sequence not valid
			} else {										//Otherwise there is some Cleaned Sequence
				if ( strlen($this->CleanedInput)			//Check Length is within limit
					<= CodonOpt_Controller_Setup_Sequence_Ancestor::$InputSequenceMaxLength
				) {	
					if ( $this->getIs_input_protein() ) {	//If input is protein
						return $this->CheckCleanedInputAsProtein();
						break;								//Check it as protein
					} else {								//Otherwise input is DNA/RNA
						return $this->CheckCleanedInputAsNucleotide();
						break;								//Check it as DNA/RNA				
					}
				} else {									//Otherwise Length exceeds limits
					$this->InputSequenceErrorMsg = "Input Sequence Too Long. Please reduce it to ".CodonOpt_Controller_Setup_Sequence_Ancestor::$InputSequenceMaxLength." characters or less";
					return false;
				}
			}
		} else {			//Otherwise there is no input sequence
			return false;	//Return false
			//No error messages as this is likely first load
		}
		return false;
	}	
	
	//Check the input sequence as Protein
	private function CheckCleanedInputAsProtein() {
		//RHKDESTNQCUGPAVILMFYW
		$tempCopy = $this->CleanedInput;
		$tempCopy = str_ireplace(								//Remove RHKDESTNQCUGPAVILMFYW
			CodonOpt_Controller_Setup_Sequence_Ancestor::$RHKDESTNQCGPAVILMFYW,	
			"",$tempCopy
		);
		$tempCopy = str_ireplace(								//Remove *
			"*","",$tempCopy
		);
		if ($tempCopy == "") {									//If nothing is left, there are no illegal characters
			$tempCopy = substr($this->CleanedInput, 0, -1);		//Get all but last char
			$tempCopy = str_ireplace(							//Remove RHKDESTNQCUGPAVILMFYW
				CodonOpt_Controller_Setup_Sequence_Ancestor::$RHKDESTNQCGPAVILMFYW,	
				"",$tempCopy
			);
			if ($tempCopy == "") {								//If there is no "*" in the middle
				if (strncmp($this->CleanedInput,"M",1) == 0) {	//Ensure that first base is Methionine
					return true;								//return true if it is
				} else {										//Otherwise first base is not Methionine
					$this->InputSequenceErrorMsg = "Protein sequence should start with Methionine (M).";
					return false;
				}
			} else {											//Otherwise there is "*" in the middle
				$this->InputSequenceErrorMsg = "Termination signal (*) detected in the middle of the protein sequence.";
				return false;
			}
		} else {												//Otherwise, non-protein character found
			$this->InputSequenceErrorMsg = "Protein sequence should only contain unambiguous amino acids (".implode(", ",CodonOpt_Controller_Setup_Sequence_Ancestor::$RHKDESTNQCGPAVILMFYW)."). You may also include a termination signal (*) at the end.";
			return false;
		}
	}
	
	//Check the input sequence as Nucleotide
	//Due to the complexity of nucleotide checking operations it is divided into 2 parts
	//This first part checks for general characteristics (No invalid bases, length divisible by 3)
	private function CheckCleanedInputAsNucleotide() {
		$ATUCG = array ("A","T","U","C","G");
		$ATCG = array ("A","T","C","G");
		$AUCG = array ("A","U","C","G");
	
		//Ensure
		$tempCopy = $this->CleanedInput;
		$tempCopy = str_ireplace($ATUCG,"",$tempCopy);
		if ($tempCopy == "") {								//If nothing is left, it is correct
			$seqlength = strlen($this->CleanedInput);		//Extract length
			if ( ($seqlength%3) == 0 ) {					//If string is divisible by 3, run start/stop checks
				$tempCopyU = $this->CleanedInput;			//Remove U
				$tempCopyU = str_ireplace($AUCG, "", $tempCopyU);	
				$tempCopyT = $this->CleanedInput;			//Remove T
				$tempCopyT = str_ireplace($ATCG, "", $tempCopyT);
				;;;;;;if ($tempCopyU == "") {				//If sequence is RNA (only U present)
					return $this->CheckCleanedInputAsNucleotideStartStop(
						new CodonOpt_StandardizeNucleotide_RNA()
					);										//Check with RNA standard
				} elseif ($tempCopyT == "") {				//Otherwise If sequence is DNA (only T present)
					return $this->CheckCleanedInputAsNucleotideStartStop(
						new CodonOpt_StandardizeNucleotide_DNA()
					);										//Check with DNA standard
				} else {									//Otherwise neither DNA or RNA (both T and U present)
					$this->InputSequenceErrorMsg = "Both 'T' (for DNA) and 'U' (for RNA) bases detected. Please use only one or the other.";
					return false;			
				}				
			} else {
				$this->InputSequenceErrorMsg = "Current sequence is ".$seqlength." bases long. DNA/RNA sequence length should be divisible by 3.";
				return false;	
			}
		} else {											//Otherwise, non-protein character found
			$this->InputSequenceErrorMsg = "DNA/RNA sequence should only contain unambiguous nucleotide bases (".implode(", ",$ATUCG).").";
			return false;
		}
	}
	
	//This is the 2nd nucleotide checking function
	//It checks that it starts with M and end with stop, with no stop in between
	private function CheckCleanedInputAsNucleotideStartStop($StandardizeNucleotide) {
		$TransRule = 								//Retrieve User selected Translation code.
			CodonOpt_DAO_translationrules::selectByNCBIcode($this->nucleotide_input_translation_rule);
		if ( isset($TransRule) ) {
			//Extract list of M and * (Stop codon)
			$CodonHash = $TransRule->getCodonHash();
			$MHash = array();
			$StopHash = array();
			foreach ($CodonHash as $tempKey=>$tempValue) {
				;;;;;;if ($tempValue == "M") {		//If Base is M
					$MHash[							//Store it in MHash
						$StandardizeNucleotide->standardize( $tempKey )	//Standardize to DNA or RNA
					] = $StandardizeNucleotide->standardize( $tempKey );
				} elseif ($tempValue == "*") {		//If Base is stop
					$StopHash[						//Store it in Stop Hash
						$StandardizeNucleotide->standardize( $tempKey )
					] = $StandardizeNucleotide->standardize( $tempKey );			
				}
			}
			$CodonList 								//Break into sets of triplets
				= str_split($this->CleanedInput,3);
			$MaxCountA = count($CodonList);
			for ($numA=0; $numA<$MaxCountA; $numA++) {
				$Codon = $CodonList[$numA];			//Get codon at this position
				;;;;;;if ($numA == 0) {				//If this if first codon
					if (! isset($MHash[$Codon]) ) {	//Check MHash
						$this->InputSequenceErrorMsg = "Nucleotide sequence should begin with Methionine (".implode($MHash,", ").").";
						return false;				
					}
				} elseif ( $numA == ($MaxCountA-1) ) {	//If this is last codon
					//Check that last codon is stop: Ending with stop codon is now optional
					//if (! isset($StopHash[$Codon]) ) {
					//	$this->InputSequenceErrorMsg = "Nucleotide sequence should end with stop codon (".implode($StopHash,", ").").";
					//	return false;			
					//}
				} else {							//Otherwise this is somewhere between first/last codon
					if (isset($StopHash[$Codon]) ) {
						$this->InputSequenceErrorMsg = "Stop Codon detected in the middle of nucleotide sequence: ".strtolower($CodonList[$numA-1]).$Codon.strtolower($CodonList[$numA+1]);
						return false;
					}
				}
			}
			return true;							//If it made it here, should be correct
		} else {
			$this->InputSequenceErrorMsg = "Invalid Translation Ruleset or Database connection error.";
			return false;
		}
	}
	
	//Check there is valid input Title
	private function IsValidTitle() {
		if ( isset($this->Title) ) {				//Ensure there is Title field
			if ($this->Title == "") {				//If there is NO Title
				return true;						//Return true as Title is optional
			} else {								//Otherwise there is Title
				$titleCopy = CodonOpt_Utility::CleanSequence($this->Title);
				$titleCopy = str_ireplace(			//Remove AlphaNumeric
					CodonOpt_Utility::GetAlphaNumeric(),	
					"",$titleCopy
				);
				$titleCopy = str_ireplace(			//Remove Symbols
					CodonOpt_Utility::getAllowedTitleSymbols(),
					"",$titleCopy
				);
				if ( strlen($titleCopy) >= 1 ) {	//If there are any characters remaining
					$this->TitleErrorMsg = "Title can only contain alpha-numeric characters, spaces, and the following symbols: ".implode( " ",CodonOpt_Utility::getAllowedTitleSymbols() );
					return false;					//Display Error Message
				} else {							//Otherwise there are no characters remaining
					if ( strlen($this->Title)		//Check Length is within limit
						<= CodonOpt_Controller_Setup_Sequence_Ancestor::$TitleMaxLength
					) {	
						return true;							//return true if it is
					} else {									//Otherwise Length exceeds limits
						$this->TitleErrorMsg = "Input Title Too Long. Please reduce it to ".CodonOpt_Controller_Setup_Sequence_Ancestor::$TitleMaxLength." characters or less";
						return false;
					}
				}
			}
		} else {								//If no email Title
			return false;						//Likely first load, do not run
		}
	}
	
	//Constructor calls parent to extract job and get page title
	public function CodonOpt_Controller_Setup_Sequence_Ancestor() {
		parent::__construct();					//Parent extracts job if any
	}
	
	//This function carries out submission of data 
	//Exact implementation depends on child class (either create or update)
	public abstract function saveAndContinue();
}
?>