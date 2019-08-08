<?php
/*	//Readme
	========
This object controls getting the job.
*/
require_once "CodonOpt_DAO_user_jobs.php";

class CodonOpt_Controller_Ancestor_User_Job {
	//Static Properties and their getters (read only, no setters)
	private static $encryptIDGetKey = "id";
	public static function getEncryptIDGetKey() {
		return CodonOpt_Controller_Ancestor_User_Job::$encryptIDGetKey;
	}
	private static $SubmitNewJobPage = "setup_input_sequence_create.php";
	public static function getSubmitNewJobPage() {
		return CodonOpt_Controller_Ancestor_User_Job::$SubmitNewJobPage;
	}

	//Object Properties and their getters (read only, no setters)
	private $currentPage;
	public function getCurrentPage() { return $this->currentPage; }
	private $encryptID;		//Encrypt ID of current job
	public function getEncryptID() { return $this->encryptID; }
	
	//Object Properties and their getters/setters
	private $currentJob;	//DTO object of current job
	public function getCurrentJob() { return $this->currentJob; }
	public function reloadCurrentJob() {
		if ( isset($this->encryptID) ) {		//If encrypt was found
			$this->currentJob = CodonOpt_DAO_user_jobs::selectByEncryptId($this->encryptID);
			if ( isset($this->currentJob) ) {	//If current job found
			} else {							//Otherwise no current job for this ID
				header( "Location: ".$this::getSubmitNewJobPage() );
				exit;							//Redirect back to index
			}				
		} else {								//Otherwise EncryptID not set
			header( "Location: ".$this::getSubmitNewJobPage() );
			exit;								//Redirect back to index		
		}
	}
	
	//Constructor needs these 2 variables:
	public function CodonOpt_Controller_Ancestor_User_Job(
		$InputJob										//Accepts a job (can take null)
	) {
		$this->currentPage = 							//just use page name
			pathinfo($_SERVER["SCRIPT_NAME"],PATHINFO_BASENAME);
		if ( isset($InputJob) ) {						//If there is a current job
			if ( is_object($InputJob) ) {				//If Job is object
				if ( 									//If job is correct class
					get_class($InputJob) == "CodonOpt_DTO_user_jobs"
				) {
					$this->currentJob = $InputJob;		//Store Job
					$this->encryptID = 					//Store Encrypt ID
						$InputJob->getEncrypt_id();	
				}
			}
		}
		
		if ( isset($this->currentJob) ) {						//If Job was found
		} else {												//Otherwise job not found
			if (
				$this->currentPage == $this::getSubmitNewJobPage()
			) {													//If this is the Submit New Job page, do not retrieve job
			} else {											//Otherwise this is NOT the index page
				foreach($_GET as $tempKey => $tempValue) {		//Go through GET items
					switch ($tempKey) {							//Find the ID
						case CodonOpt_Controller_Ancestor_User_Job::$encryptIDGetKey:
							$this->encryptID = $tempValue;		//Store it to Encrypt
							break;
					}
				}
				$this->reloadCurrentJob();						//Load current job
			}
		}
	}
}
?>

