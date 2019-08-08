<?php
require_once "CodonOpt_Controller_Setup_Sequence_Ancestor.php";
require_once "CodonOpt_Controller_UserSession.php";

//This class is meant to control the Protein Sequence submission page
class CodonOpt_Controller_Setup_SequenceSubmit extends CodonOpt_Controller_Setup_Sequence_Ancestor {
	//This function carries out submission of data
	//Create new entry
	public function saveAndContinue() {
		$EncryptID =  CodonOpt_DAO_user_jobs::insertNewJob(
			$this->getIs_input_protein(),
			$this->getNucleotide_input_translation_rule(),
			$this->getCleanedInput(),
			$this->getTitle()
		);
		CodonOpt_Controller_UserSession::AddUserJob($EncryptID);	//Add to user session
		header("Location: setup_optimization.php?".CodonOpt_Controller_Ancestor_User_Job::getEncryptIDGetKey()."=".$EncryptID);
		exit;
	}
	
	//Constructor Calls parent
	public function CodonOpt_Controller_Setup_SequenceSubmit() {
		parent::__construct();	//Call Parent
	}
}
?>