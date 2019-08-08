<?php
require "CodonOpt_Controller_Setup_Sequence_Ancestor.php"; 

//This class is meant to control the Protein Sequence submission page
class CodonOpt_Controller_Setup_SequenceEdit extends CodonOpt_Controller_Setup_Sequence_Ancestor {
	//Constructor: This class must take in a current job
	public function CodonOpt_Controller_Setup_SequenceEdit() {
		parent::__construct();	//Parent extracts job
		$this->setIs_input_protein( $this->getCurrentJob()->getIs_input_protein() );
		$this->setNucleotide_input_translation_rule( $this->getCurrentJob()->getNucleotide_input_translation_rule() );
		$this->setTitle( $this->getCurrentJob()->getTitle() );
		$this->setInputsequence( $this->getCurrentJob()->getInput_sequence() );
	}
	
	//This function carries out submission of data
	//Create new entry
	public function saveAndContinue() {
		//Save new sequence and title
		$this->getCurrentJob()->setIs_input_protein( $this->getIs_input_protein() );
		$this->getCurrentJob()->setNucleotide_input_translation_rule( $this->getNucleotide_input_translation_rule() );
		$this->getCurrentJob()->setInput_sequence( $this->getCleanedInput() );	//Save new cleaned input sequence
		$this->getCurrentJob()->setTitle( $this->getTitle() );					//Save new Title
		CodonOpt_DAO_user_jobs::update($this->getCurrentJob());					//Update Database
		//When finished submitting, redirect to view results
		header("Location: setup_optimization.php?".CodonOpt_Controller_Ancestor_User_Job::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
		exit;
	}
}
?>