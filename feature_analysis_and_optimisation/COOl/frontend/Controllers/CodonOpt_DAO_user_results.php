<?php
require_once "CodonOpt_DAO_user_jobs.php";	//Provides DAO ancestor, and encrypt_id format checker
require_once "CodonOpt_DTO_user_results.php";

class CodonOpt_DAO_user_results extends CodonOpt_DAO_ancestor {
	//Internal Variables
	private static $DatabaseName = "user_results";
	private static $FastColumnList = "user_job_serial,user_insert,display_id,user_given_title,ic_fitness,cc_fitness,cai_fitness,gc_content_fitness,number_of_stop_codon_motifs,trigger_count_upcase_outseq_exclusion,trigger_count_upcase_outseq_repeat_consec,trigger_count_upcase_outseq_repeat_allmotif,trigger_count_upcase_outseq_hidden_stop_codon,trigger_count_gc_content_outseq";
	private static $EncryptIDlength = 15;
	
	
	//Read in data by Encrypt ID.
	//If no object for this ID is found, return 
	//This function used for CSV export of all columns
	public static function selectFastAndOutputByEncryptExampleId($InputID,$ExampleSerial) {
		$ColumnList = CodonOpt_DAO_user_results::$FastColumnList.",output_sequence";
		return CodonOpt_DAO_user_results::selectColumnNamesByEncryptId($ColumnList,$InputID,$ExampleSerial);
	}
	
	//This function used for View Result Summary
	public static function selectFastColumnsByEncryptExampleId($InputID,$ExampleSerial) {
		return CodonOpt_DAO_user_results::selectColumnNamesByEncryptId(CodonOpt_DAO_user_results::$FastColumnList,$InputID,$ExampleSerial);
	}
	
	//Select specified columns for all results with specified encrypt ID
	private static function selectColumnNamesByEncryptId($ColumnNames,$InputID,$ExampleSerial) {
		if ( 				//If Encrypt ID is valid
			CodonOpt_DAO_user_jobs::checkValidEncryptIDFormat($InputID) 
		) {					//Run Query
			$ConditionString = "(user_job_serial = (SELECT serial FROM user_jobs WHERE encrypt_id = ?))";
			$ConditionArray = array($InputID);
			if (			//If Example Serial is set
				isset($ExampleSerial)
			) {				//Add to condition array
				$IntExampleSerial = intval($ExampleSerial);
				$ConditionString .= " OR (user_job_serial = ?)";
				array_push($ConditionArray,$IntExampleSerial);
			}
			return CodonOpt_DAO_user_results::selectColumnNamesByCondition(
				$ColumnNames , $ConditionString , $ConditionArray
			);
		} else {			//Otherwise Encrypt ID format invalid
			return null;	//Return Null
		}
	}
	
	//Select User Defined results with specified encrypt ID
	public static function selectUserDefinedSeqByEncryptId($InputID) {
		if ( 				//If Encrypt ID is valid
			CodonOpt_DAO_user_jobs::checkValidEncryptIDFormat($InputID) 
		) {					//Run Query
			$ConditionString = "user_job_serial = (SELECT serial FROM user_jobs WHERE encrypt_id = ?) AND user_insert = 1";
			$ConditionArray = array($InputID);
			$ColumnList = CodonOpt_DAO_user_results::$FastColumnList;//.",output_sequence";
			return CodonOpt_DAO_user_results::selectColumnNamesByCondition(
				$ColumnList , $ConditionString , $ConditionArray
			);
		} else {			//Otherwise Encrypt ID format invalid
			return null;	//Return Null
		}	
	}
	
	public static function deleteUserDefinedSeqByEncryptDisplayId(
		$InputEncryptID , $InputDisplayID
	) {
		$IntDisplayID = null;
		if ( isset($InputDisplayID) ) {					//Check Serial is not null
			if ( strlen($InputDisplayID) >= 1 )			//Check that it has content
			$IntDisplayID = intval($InputDisplayID);	//Convert string to integer (sanitizes)
		}
		if ( isset($IntDisplayID) AND 					//If Serial and Encrypt ID are valid
			CodonOpt_DAO_user_jobs::checkValidEncryptIDFormat($InputEncryptID) 
		) {
			$ConditionArray = array($InputEncryptID,$IntDisplayID);
			$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
			$Query = "DELETE FROM ".CodonOpt_DAO_user_results::$DatabaseName." WHERE user_job_serial = (SELECT serial FROM user_jobs WHERE encrypt_id = ?) AND display_id = ? AND user_insert = 1";
			$stmt = $dbh->prepare($Query);
			if ( $stmt->execute($ConditionArray) ) {	//Execute MUST accept an array
				return true;							//Return true if successful
			}
		}
		return null;	//Return Null		
	}
	
	//Read in data by Encrypt ID and Display ID.
	//If no object for this ID is found, return null
	public static function selectByJobUserInsertDisplayId($InputJob,$UserInsert,$InputDisplayID) {
		if (											//Make sure input is User Job
			get_class($InputJob) == "CodonOpt_DTO_user_jobs"
		) {
			$IntDisplayID = null;
			if ( isset($InputDisplayID) ) {				//Check Serial is not null
				if ( strlen($InputDisplayID) >= 1 )		//Check that it has content
				$IntDisplayID = intval($InputDisplayID);//Convert string to integer (sanitizes)
			}
			$IntUserInsert = null;
			if ( isset($UserInsert) ) {					//Check User Insert is not null
				if ( strlen($UserInsert) >= 1 )			//Check that it has content
				$IntUserInsert = intval($UserInsert);	//Convert string to integer (sanitizes)
			}
			if ( isset($IntDisplayID) AND 				//If Serial and User Insert are valid
				 isset($IntUserInsert)
			) {											//Check whether this is example
				$ExampleID = $InputJob->getExample_serial();
				$ConditionString = null;				//Variable to hold condition string
				$ConditionArray = null;					//Variable to hold condition parameters
				if ( isset($ExampleID) AND 				//If this is an example
					($IntUserInsert == 0)				//And this is NOT a user defined sequence
				) {
					$ConditionString = "user_job_serial = ? AND user_insert = ? AND display_id = ?";
					$ConditionArray = array($ExampleID,$IntUserInsert,$IntDisplayID);				
				} else {								//Otherwise this is not example OR not user defined
					$ConditionString = "user_job_serial = (SELECT serial FROM user_jobs WHERE encrypt_id = ?) AND user_insert = ? AND display_id = ?";
					$ConditionArray = array($InputJob->getEncrypt_id(),$IntUserInsert,$IntDisplayID);
				}										//Run Query									
				$results = CodonOpt_DAO_user_results::selectColumnNamesByCondition(
					"*" , $ConditionString , $ConditionArray
				);
				if ( count($results) == 1 ) {			//If there is one row
					return $results[0];					//Return that row
				} elseif ( count($results)== 0 ) {		//If there are no rows
					return null;						//return null
				} else {
					die("Error: More than 1 results found!");
				}			
			} else {									//Otherwise either Serial and User Insert is invalid
				return null;							//Return Null
			}
		} else {										//Otherwise input was NOT a job
			die("Invalid Input User Job");
		}
	}
	
	//Generic Selection Function
	private static function selectColumnNamesByCondition(
		$ColumnNames , $ConditionString , $ConditionArray
	) {	
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = "SELECT ".$ColumnNames." FROM ".CodonOpt_DAO_user_results::$DatabaseName." WHERE ".$ConditionString." ORDER BY user_insert , display_id";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($ConditionArray) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {			//Go through rows one by one
				$resultDTO = 						//Create Result DTO
					new CodonOpt_DTO_user_results($row);
				$UserInsert = $resultDTO->getUser_insert();
				$DisplayID = $resultDTO->getDisplay_id();
				$Title = $resultDTO->getUser_given_title();
				if ($DisplayID == 0) {				//If Display ID is 0
					if (! $UserInsert) {			//And this is NOT User Insert
						if (strlen($Title) == 0) {	//If title is empty
							$resultDTO->setUser_given_title("Initial Nucleotide Input");
						}							//Then replace title with (Original Nucleotide Sequence)
					}
				}
				array_push($results,$resultDTO);	//Add to list of results
			}
		}
		if ( count($results)== 0 ) {			//If there are no rows
			return null;						//return null
		} else {								//Otherwise 1 or more results
			return $results;					//Return Results
		}
	}
	
	//Select User Defined results with specified encrypt ID
	public static function getCurrentMaxDisplayIdForUserDefinedSeqBySerial($InputSerial) {
		$IntSerial = intval($InputSerial);			//Convert to integer
		$ConditionString = "(user_job_serial = ?) AND (user_insert = 1)";
		$ConditionArray = array($IntSerial);		//Input Serial
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = 									//Generate Query
			"SELECT MAX(display_id) AS max_display_id FROM ".CodonOpt_DAO_user_results::$DatabaseName." WHERE ".$ConditionString;
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($ConditionArray) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {			//Go through rows one by one
				array_push(							//Transfer to output
					$results,$row["max_display_id"]
				);
			}
		}
		if ( count($results) == 1 ) {			//If there is one row
			$CurrMax = $results[0];				//Get Current Max
			if ( isset($CurrMax) ) {			//If this is valid number
				return intval($CurrMax);		//Return that number
			} else {							//Otherwise not valid number (current no User Def Seq)
				return -1;						//Return -1
			}
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}
	
	//Create
	public static function insertNewUserResult($InResult) {
		$ColumnList = array(			//Starting List of compulsory columns
			"user_job_serial", "user_insert", "display_id", "user_given_title", "output_sequence"
		);
		$ValueList = array(				//Starting List of compulsory values
			$InResult->getUser_job_serial(), $InResult->getUser_insert(), $InResult->getDisplay_id(), $InResult->getUser_given_title(), $InResult->getOutput_sequence()
		);
		$QuestionList = array(			//Starting List of compulsory question marks
			"?","?","?","?","?"
		);
		
		//Add additional details: For each column, add to ColumnList, ValueList, and QuestionList
		if ( $InResult->getIsChanged_output_sequence_exclusion() ) {
			array_push( $ColumnList , "output_sequence_exclusion" );
			array_push( $ValueList , $InResult->getOutput_sequence_exclusion() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_output_sequence_repeat_consec() ) {
			array_push( $ColumnList , "output_sequence_repeat_consec" );
			array_push( $ValueList , $InResult->getOutput_sequence_repeat_consec() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_output_sequence_repeat_allmotif() ) {
			array_push( $ColumnList , "output_sequence_repeat_allmotif" );
			array_push( $ValueList , $InResult->getOutput_sequence_repeat_allmotif() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_output_sequence_hidden_stop_codon() ) {
			array_push( $ColumnList , "output_sequence_hidden_stop_codon" );
			array_push( $ValueList , $InResult->getOutput_sequence_hidden_stop_codon() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_ic_fitness() ) {
			array_push( $ColumnList , "ic_fitness" );
			array_push( $ValueList , $InResult->getIc_fitness() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_cc_fitness() ) {
			array_push( $ColumnList , "cc_fitness" );
			array_push( $ValueList , $InResult->getCc_fitness() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_cai_fitness() ) {
			array_push( $ColumnList , "cai_fitness" );
			array_push( $ValueList , $InResult->getCai_fitness() );
			array_push( $QuestionList , "?" );
		}		
		if ( $InResult->getIsChanged_gc_content_fitness() ) {
			array_push( $ColumnList , "gc_content_fitness" );
			array_push( $ValueList , $InResult->getGc_content_fitness() );
			array_push( $QuestionList , "?" );
		}
		if ( $InResult->getIsChanged_exclusion_fitness() ) {
			array_push( $ColumnList , "exclusion_fitness" );
			array_push( $ValueList , $InResult->getExclusion_fitness() );
			array_push( $QuestionList , "?" );		
		}
		if ( $InResult->getIsChanged_number_of_stop_codon_motifs() ) {
			array_push( $ColumnList , "number_of_stop_codon_motifs" );
			array_push( $ValueList , $InResult->getNumber_of_stop_codon_motifs() );
			array_push( $QuestionList , "?" );
		}
		
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = 							//Generate Query
			"INSERT INTO ".CodonOpt_DAO_user_results::$DatabaseName." (".
			implode(",",$ColumnList).		//With this list of columns
			") VALUES (".
			implode(",",$QuestionList).		//And this number of question marks
			")";
		$stmt = $dbh->prepare($Query);
		if ( $stmt->execute($ValueList) ) {	//If execution is successful
			return true;
		} else {
			die("User Defined Sequence Insert Statement Failed");
		}
	}	
}
?>
