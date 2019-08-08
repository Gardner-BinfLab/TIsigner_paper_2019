<?php
@session_start();
//This Class contains static functions to interface with the Session

require_once ("CodonOpt_DAO_user_jobs.php");
/*
=================
Session Controls:
=================
-Started/Submitted Jobs

*/
abstract class CodonOpt_Controller_UserSession {
	//=============================
	//User Fusion Protein functions
	//=============================
	public static function AddUserJob($InputEncryptID) {
		$myList = CodonOpt_Controller_UserSession::GetUserJobList();
		array_push($myList,$InputEncryptID);
		CodonOpt_Controller_UserSession::SaveUserJobList($myList);
	}
	//Returns a COPY of the current list
	//Changes made to the copy will NOT be saved, see "SaveLinkerList()" function below
	public static function GetUserJobList() {
		if ( isset($_SESSION['user_codonopt_job_list']) ) {
			return $_SESSION['user_codonopt_job_list'];
		} else {
			return array();
		}
	}
	//Save changes made to the list
	private static function SaveUserJobList($InList) {
		$_SESSION['user_codonopt_job_list'] = $InList;
	}
	
	//This returns a list of partial DTOs (none of the text fields) instead of merely having the encrypt_id
	//At the same time, it updates the list of jobs that have not been deleted
	public static function getUserJobPartialDTO_All() {
		$Result = CodonOpt_DAO_user_jobs::		//Get old list of results
			selectPartialDTOListByEncryptID( CodonOpt_Controller_UserSession::GetUserJobList() );
		$RefreshArray = array();				//Refresh the current array: remove PDBs that have been archived/deleted
		if ( isset($Result) ) {					//If there are results
			foreach ($Result as $tempDTO) {		//Go through each current result and add it to new array
				array_push( $RefreshArray,$tempDTO->getEncrypt_id() );
			}
		}
		CodonOpt_Controller_UserSession::		//New array overwrites old array
			SaveUserJobList($RefreshArray);
		return $Result;
	}
	
	//This gets a list of recent results
	public static function getUserJobPartialDTO_RecentResults() {
		$InResult = CodonOpt_Controller_UserSession::getUserJobPartialDTO_All();
		$OutResult = array();
		if ( isset($InResult) ) {							//Check if there is any results
			foreach ($InResult as $tempResult) {			//For each result
				$JobEnd = $tempResult->getJob_end_on();
				if ( isset($JobEnd) ) {						//Ensure Job has ended
					$ExampleSerial = $tempResult->getExample_serial();
					if ( isset($ExampleSerial) ) {			//If it is example
					} else {								//Otherwise it is NOT example
						array_push($OutResult,$tempResult);	//Add it to output
					}
				}
			}
		}
		return $OutResult;
	}
}
?>