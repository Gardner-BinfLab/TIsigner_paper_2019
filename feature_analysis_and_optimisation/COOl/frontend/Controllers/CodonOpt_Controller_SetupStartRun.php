<?php
require_once "CodonOpt_Controller_Setup_Ancestor.php";
require_once "CodonOpt_Utility.php";

//This Class is meant to control the Setup Start Run
class CodonOpt_Controller_SetupStartRun extends CodonOpt_Controller_Setup_Ancestor {
	//Internal Settings and their Getters
	private static $MaxEmailLength=50;
	public static function getMaxEmailLength() 
	{ return CodonOpt_Controller_SetupStartRun::$MaxEmailLength; }
	
	
	//Form Data, and Getters and Setters
	//Setters include validation before saving data
	//Getters should return some default if property is not defined
	private $EmailAddress = null;
	public function setEmailAddress($input) {
		if ( isset($input) ) {
			$this->EmailAddress = $input;
		}
	}
	public function getEmailAddress() {
		return $this->EmailAddress;
	}
	
	//Error Messages
	private $EmailAddressErrorMsg = null;
	//Error Messages have Getters only
	public function getEmailAddressErrorMsg() {
		return $this->EmailAddressErrorMsg;
	}

	//Constructor
	public function CodonOpt_Controller_SetupStartRun() {
		parent::__construct();	//Parent extracts job
		$this->EmailAddress = $this->getCurrentJob()->getUser_email();
	}
	
	//Check the inputs are correct, and if they are, submit them
	public function checkAndStartNewRun() {
		$FailCount = 0;	//Count number of validation failures

		//The other checks are in self-contained subfunctions
		if (! $this->IsValidEmailAddress()) {$FailCount++;}		
		
		//Before proceeding, all CheckList must be true
		if ($FailCount == 0) {	//If there are no false, commence submission
			$this->startNewRun();
		}
	}
	
	//Check there is valid input Email Address
	private function IsValidEmailAddress() {
		if ( isset($this->EmailAddress) ) {		//Ensure there is email field
			if ($this->EmailAddress == "") {	//If there is NO email
				return true;					//Return true as email is optional
			} else {							//Otherwise there is email
				if ( strlen($this->EmailAddress)//Check if Length is exceeds limit
					> CodonOpt_Controller_SetupStartRun::getMaxEmailLength()
				) {								//If it does
					$this->EmailAddressErrorMsg = "Error: Email address has a maximum length of ".CodonOpt_Controller_SetupStartRun::getMaxEmailLength()." characters!";
					return false;				//Show error message and return false				
				}
				$tempBool = CodonOpt_Utility::CheckValidEmail($this->EmailAddress);
				if ($tempBool) {				//Check output, if valid
					return true;				//Return true
				} else {						//Otherwise not valid
					$this->EmailAddressErrorMsg = "Invalid Email address!";
					return false;				//Show error message and return false
				}
			}
		} else {								//If no email field
			return false;						//Likely first load, do not run
		}
	}
	
	//This function carries out submission of data
	private function startNewRun() {
		$this->getCurrentJob()->setUser_email($this->EmailAddress);	//Save email address
		$this->getCurrentJob()->submitCurrentJob();					//Submit the current job
		CodonOpt_DAO_user_jobs::update($this->getCurrentJob());		//Update Database
		//When finished submitting, redirect to view results
		header("Location: viewresult.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
		exit;
	}
}
?>