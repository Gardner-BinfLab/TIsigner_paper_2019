<?php
require_once "CodonOpt_DAO_ancestor.php";
require_once "CodonOpt_DTO_user_jobs.php";
require_once "CodonOpt_DAO_genes.php";
require_once "CodonOpt_Utility.php";

class CodonOpt_DAO_user_jobs extends CodonOpt_DAO_ancestor {
	//Internal Variables
	private static $DatabaseName = "user_jobs";
	private static $EncryptIDlength = 15;
	private static $inbuilt_species_gene_list_delimiter = ",";
	public static function getInbuilt_species_gene_list_delimiter() {
		return CodonOpt_DAO_user_jobs::$inbuilt_species_gene_list_delimiter;
	}
	
	//Create
	public static function insertNewJob(
		$Is_input_protein,
		$Nucleotide_input_translation_rule,
		$InputSequence,$Title
	) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		//First generate a new random encrypt key
		$EncryptID = CodonOpt_Utility::GetRandomString(CodonOpt_DAO_user_jobs::$EncryptIDlength);
		$CurrObj = CodonOpt_DAO_user_jobs::selectByEncryptId($EncryptID);
		while ( isset($CurrObj) ) {	//While current ID is in use (and return object is NOT null)
			//Generate another ID, and get another object
			$EncryptID = CodonOpt_Utility::GetRandomString(CodonOpt_DAO_user_jobs::$EncryptIDlength);
			$CurrObj = CodonOpt_DAO_user_jobs::selectByEncryptId($EncryptID);
		}
		$stmt = $dbh->prepare("INSERT INTO ".CodonOpt_DAO_user_jobs::$DatabaseName." (created_on, encrypt_id, is_input_protein, nucleotide_input_translation_rule, input_sequence, title, inbuilt_species_serial, inbuilt_species_gene_list) VALUES (current_timestamp, :encrypt_id, :is_input_protein, :nucleotide_input_translation_rule, :input_sequence, :title, :inbuilt_species_serial, :inbuilt_species_gene_list)");
		$stmt->bindParam(":encrypt_id", $EncryptID);
		$stmt->bindParam(":is_input_protein", $Is_input_protein);
		$stmt->bindParam(":nucleotide_input_translation_rule", $Nucleotide_input_translation_rule);
		$stmt->bindParam(":input_sequence", $InputSequence);
		$stmt->bindParam(":title", $Title);
		$TargetSpeciesSerial = 1;	//Assume Using first species
		$stmt->bindParam(":inbuilt_species_serial", $TargetSpeciesSerial);
		$stmt->bindParam(":inbuilt_species_gene_list",
			implode(
				CodonOpt_DAO_user_jobs::$inbuilt_species_gene_list_delimiter,
				CodonOpt_DAO_genes::generateGeneListForSpeciesSerial($TargetSpeciesSerial)
			)
		);
		$stmt->execute();
		return $EncryptID;			//Return the encrypted ID
	}
	
	//Create New Example
	public static function insertNewExample() {
		//$IntExampleSerial = intval($ExampleSerial);
		$IntExampleSerial = 1;		//Convert to Integer
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		//First generate a new random encrypt key
		$EncryptID = CodonOpt_Utility::GetRandomString(CodonOpt_DAO_user_jobs::$EncryptIDlength);
		$CurrObj = CodonOpt_DAO_user_jobs::selectByEncryptId($EncryptID);
		while ( isset($CurrObj) ) {	//While current ID is in use (and return object is NOT null)
			//Generate another ID, and get another object
			$EncryptID = CodonOpt_Utility::GetRandomString(CodonOpt_DAO_user_jobs::$EncryptIDlength);
			$CurrObj = CodonOpt_DAO_user_jobs::selectByEncryptId($EncryptID);
		}
		$stmt = $dbh->prepare("INSERT INTO ".CodonOpt_DAO_user_jobs::$DatabaseName." (created_on, submitted_on, job_start_on, job_end_on, encrypt_id, example_serial, input_sequence) VALUES (current_timestamp, current_timestamp, current_timestamp, current_timestamp, :encrypt_id, :example_serial, :input_sequence)");
		$stmt->bindParam(":encrypt_id", $EncryptID);
		$stmt->bindParam(":example_serial", $IntExampleSerial);
		$InputSequence = "";
		$stmt->bindParam(":input_sequence", $InputSequence);
		$stmt->execute();
		return $EncryptID;			//Return the encrypted ID
	}

	//Check if Encrypt ID is valid
	public static function checkValidEncryptIDFormat($InputID) {
		if (						//Check length is correct
			strlen($InputID) == CodonOpt_DAO_user_jobs::$EncryptIDlength
		) {
			$copyID = str_ireplace(	//Check that it is pure alphanumeric
				CodonOpt_Utility::GetAlphaNumeric(),
				"",$InputID
			);
			if ($copyID == "") {
				return true;
			} else {				//Otherwise it is not pure alphanumeric
				return false;
			}
		} else {					//Otherwise length is wrong
			return false;
		}
	}
	
	//Read in data by Encrypt ID.
	//If no object for this ID is found, return null
	//If object has example serial, retrive that example and diguise the example as requested encrypt
	public static function selectByEncryptId($InputID) {
		if (
			CodonOpt_DAO_user_jobs::checkValidEncryptIDFormat($InputID) 
		) {											//If it is valid format
			$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
			$QueryParam = array($InputID);			//Prepared Query: Create ID
			$Query = "SELECT * FROM ".CodonOpt_DAO_user_jobs::$DatabaseName." WHERE encrypt_id = ?";
			$stmt = $dbh->prepare($Query);
			$results = array();
			if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
				while ($row = $stmt->fetch()) {		//Go through rows one by one
					array_push($results,			//Transfer to output
						new CodonOpt_DTO_user_jobs($row)
					);		//Add them to results
				}
			}
			if ( count($results) == 1 ) {			//If there is one row
				$ExampleSerial = $results[0]->getExample_serial();
				if ( isset ($ExampleSerial) ) {		//If this has example serial
					$ExampleJob =  					//Retrieve example serial
						CodonOpt_DAO_user_jobs::selectBySerial($ExampleSerial);
					//Disguise example serial and encrypt ID with the requested ID
					$ExampleJob->setSerial( $results[0]->getSerial() );
					$ExampleJob->setEncrypt_id( $results[0]->getEncrypt_id() );
					$ExampleJob->setExample_serial( $results[0]->getExample_serial() );
					return $ExampleJob;				//Return modified job
				} else {							//Otherwise this is not example
					return $results[0];				//Return the result
				}
			} elseif ( count($results)== 0 ) {		//If there are no rows
				return null;						//return null
			} else {
				die("Error: More than 1 results found!");
			}
		} else {									//Otherwise it is NOT pure alpha-numeric
			return null;							//No return
		}
	}
	
	private static function selectBySerial($InputSerial) {
		$IntSerial = intval($InputSerial);
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$QueryParam = array($IntSerial);		//Prepared Query: Create Serial
		$Query = "SELECT * FROM ".CodonOpt_DAO_user_jobs::$DatabaseName." WHERE serial = ?";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_user_jobs($row)
				);		//Add them to results
			}
		}
		if ( count($results) == 1 ) {			//If there is one row
			return $results[0];					//Return the result
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}

	//Select multiple by a list of encrypt IDs
	//Only returns partial DTO, where the file_content_pdb is simply NULL if empty, and '1' for everything else
	public static function selectPartialDTOListByEncryptID($InputArray) {
		$ValidHash = array();							//Store as Hash to remove duplicates
		foreach ($InputArray as $tempKey=>$tempValue) {	//Search both Keys and Values
			$ValidCheck = CodonOpt_DAO_user_jobs::checkValidEncryptIDFormat($tempKey);
			if ($ValidCheck) {
				$ValidHash[$tempKey] = 1;				//Add to hash
			}
			$ValidCheck = CodonOpt_DAO_user_jobs::checkValidEncryptIDFormat($tempValue);
			if ($ValidCheck) {
				$ValidHash[$tempValue] = 1;				//Add to hash
			}
		}
		if ( count($ValidHash) >= 1 ) {					//If there is at least one valid element
			$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
			$QueryParam = array();						//Prepared Query: Create ID
			$QuestionMarkArray = array();
			foreach ($ValidHash as $tempKey=>$tempValue) {
				array_push($QueryParam,$tempKey);		//Convert Hash into Array
				array_push($QuestionMarkArray,"?");		//Add question mark to list
			}
			$Query = 
				"SELECT ".
				"serial , example_serial , encrypt_id , is_input_protein , nucleotide_input_translation_rule , updated_on , created_on , submitted_on , job_start_on , job_end_on , error_text , post_processed_on , title , repeat_consec_mode , repeat_consec_length , repeat_consec_count , repeat_allmotif_mode , repeat_allmotif_length , repeat_allmotif_count , user_email , optimize_ic , optimize_cc , optimize_cai , optimize_hidden_stop_codon , optimize_gc_mode , optimize_gc_target , inbuilt_species_serial , use_custom_species , TranslationRules_NCBI_code".
				" FROM ".CodonOpt_DAO_user_jobs::$DatabaseName.
				" WHERE encrypt_id in (".implode(",",$QuestionMarkArray).")";
			//Commands to check query
			//echo $Query."<br/><br/>";
			//var_dump($QueryParam);
			$stmt = $dbh->prepare($Query);
			$results = array();
			if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
				while ($row = $stmt->fetch()) {		//Go through rows one by one
					array_push($results,			//Transfer to output
						new CodonOpt_DTO_user_jobs($row)
					);		//Add them to results
				}
			}
			if ( count($results) >= 1 ) {			//If there is one row
				return $results;					//Return the result
			} elseif ( count($results)== 0 ) {		//If there are no rows
				return null;						//return null
			}
		} else {									//Otherwise no valid EncryptIDs
			return null;							//No return
		}
	}
	
	//Update input object
	public static function update($InputDTO) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		
		//This is a parallel array with Column Names in one, and new Values in the other
		$ColNames = array();
		$Values = array();
		if ( $InputDTO->getIsChanged_is_input_protein() ) {
			array_push($ColNames," is_input_protein=? ");
			array_push($Values,$InputDTO->getIs_input_protein() );
		}
		if ( $InputDTO->getIsChanged_nucleotide_input_translation_rule() ) {
			array_push($ColNames," nucleotide_input_translation_rule=? ");
			array_push($Values,$InputDTO->getNucleotide_input_translation_rule() );
		}
		if ( $InputDTO->getIsChanged_input_sequence() ) {
			array_push($ColNames," input_sequence=? ");
			array_push($Values,$InputDTO->getInput_sequence() );
		}
		if ( $InputDTO->getIsChanged_submitted_on() ) {
			array_push($ColNames," submitted_on=current_timestamp ");
			//array_push($Values,$InputDTO->getSubmitted_on() );
		}
		if ( $InputDTO->getIsChanged_post_processed_on() ) {
			array_push($ColNames," post_processed_on=current_timestamp ");
			//array_push($Values,$InputDTO->getPost_processed_on() );
		}
		if ( $InputDTO->getIsChanged_title() ) {
			array_push($ColNames," title=? ");
			array_push($Values,$InputDTO->getTitle() );
		}		
		if ( $InputDTO->getIsChanged_user_email() ) {
			array_push($ColNames," user_email=? ");
			array_push($Values,$InputDTO->getUser_email() );
		}
		if ( $InputDTO->getIsChanged_exclusion_sequence() ) {
			array_push($ColNames," exclusion_sequence=? ");
			array_push($Values,$InputDTO->getExclusion_sequence() );
		}
		if ( $InputDTO->getIsChanged_repeat_consec_mode() ) {
			array_push($ColNames," repeat_consec_mode=? ");
			array_push($Values,$InputDTO->getRepeat_consec_mode() );
		}
		if ( $InputDTO->getIsChanged_repeat_consec_length() ) {
			array_push($ColNames," repeat_consec_length=? ");
			array_push($Values,$InputDTO->getRepeat_consec_length() );
		}
		if ( $InputDTO->getIsChanged_repeat_consec_count() ) {
			array_push($ColNames," repeat_consec_count=? ");
			array_push($Values,$InputDTO->getRepeat_consec_count() );
		}
		if ( $InputDTO->getIsChanged_repeat_allmotif_mode() ) {
			array_push($ColNames," repeat_allmotif_mode=? ");
			array_push($Values,$InputDTO->getRepeat_allmotif_mode() );
		}
		if ( $InputDTO->getIsChanged_repeat_allmotif_length() ) {
			array_push($ColNames," repeat_allmotif_length=? ");
			array_push($Values,$InputDTO->getRepeat_allmotif_length() );
		}
		if ( $InputDTO->getIsChanged_repeat_allmotif_count() ) {
			array_push($ColNames," repeat_allmotif_count=? ");
			array_push($Values,$InputDTO->getRepeat_allmotif_count() );
		}
		if ( $InputDTO->getIsChanged_optimize_ic() ) {
			array_push($ColNames," optimize_ic=? ");
			array_push($Values,$InputDTO->getOptimize_ic() );
		}
		if ( $InputDTO->getIsChanged_optimize_cc() ) {
			array_push($ColNames," optimize_cc=? ");
			array_push($Values,$InputDTO->getOptimize_cc() );
		}
		if ( $InputDTO->getIsChanged_optimize_cai() ) {
			array_push($ColNames," optimize_cai=? ");
			array_push($Values,$InputDTO->getOptimize_cai() );
		}
		if ( $InputDTO->getIsChanged_optimize_hidden_stop_codon() ) {
			array_push($ColNames," optimize_hidden_stop_codon=? ");
			array_push($Values,$InputDTO->getOptimize_hidden_stop_codon() );
		}
		if ( $InputDTO->getIsChanged_optimize_gc_mode() ) {
			array_push($ColNames," optimize_gc_mode=? ");
			array_push($Values,$InputDTO->getOptimize_gc_mode() );
		}
		if ( $InputDTO->getIsChanged_optimize_gc_target() ) {
			array_push($ColNames," optimize_gc_target=? ");
			array_push($Values,$InputDTO->getOptimize_gc_target() );
		}
		if ( $InputDTO->getIsChanged_inbuilt_species_serial() ) {
			array_push($ColNames," inbuilt_species_serial=? ");
			array_push($Values,$InputDTO->getInbuilt_species_serial() );
		}
		if ( $InputDTO->getIsChanged_inbuilt_species_gene_sort() ) {
			array_push($ColNames," inbuilt_species_gene_sort=? ");
			array_push($Values,$InputDTO->getInbuilt_species_gene_sort() );
		}		
		if ( $InputDTO->getIsChanged_inbuilt_species_gene_list() ) {
			array_push($ColNames," inbuilt_species_gene_list=? ");
			array_push($Values,$InputDTO->getInbuilt_species_gene_list() );
		}
		if ( $InputDTO->getIsChanged_use_custom_species() ) {
			array_push($ColNames," use_custom_species=? ");
			array_push($Values,$InputDTO->getUse_custom_species() );
		}
		if ( $InputDTO->getIsChanged_ic_frequency() ) {
			array_push($ColNames," ic_frequency=? ");
			array_push($Values,$InputDTO->getIc_frequency() );
		}
		if ( $InputDTO->getIsChanged_cc_frequency() ) {
			array_push($ColNames," cc_frequency=? ");
			array_push($Values,$InputDTO->getCc_frequency() );
		}
		if ( $InputDTO->getIsChanged_translationRules_NCBI_code() ) {
			array_push($ColNames," translationRules_NCBI_code=? ");
			array_push($Values,$InputDTO->getTranslationRules_NCBI_code() );
		}
		
		//Compile Statement
		$UpdateStatement = "UPDATE ".CodonOpt_DAO_user_jobs::$DatabaseName." SET ";
		$UpdateStatement .= implode(",",$ColNames);
		$UpdateStatement .= " WHERE serial='".$InputDTO->getSerial()."'; ";

		//Run Statement
		//echo $UpdateStatement;
		//var_dump($Values);
		$stmt = $dbh->prepare($UpdateStatement);
		$Success = $stmt->execute($Values);
		//var_dump($stmt);
		//exit;
		return $Success;
	}
	
	//Statistics Functions
	//Get latest serial (which is also equal to total number of job submissions AND examples)
	public static function getLastestSerial() {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = "SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name = '".CodonOpt_DAO_user_jobs::$DatabaseName."' AND table_schema = '".CodonOpt_DAO_ancestor::getDatabaseName()."'";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute() ) {				//Execute
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push ($results,$row["AUTO_INCREMENT"]);
			}									//Add count serial to array
		}
		if ( count($results) == 1 ) {			//If there is one row
			return ($results[0]-1);				//Return the result minus 1 (since result is for NEXT row)
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}
	


	
	//Count number of jobs finished by week
	//Input '0' for last week, '3' for 4 weeks ago
	//No point going past 4 weeks ago, as backend will housekeep after ~30 days.
	public static function countJobsDoneByWeek($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( job_end_on < date_sub(now(),interval ".(($IntNum+0)*7)." day) )";
		$LowerLimit = "( job_end_on > date_sub(now(),interval ".(($IntNum+1)*7)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NULL) AND (error_text IS NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	//Count number of jobs finished by day
	//Input '0' for today
	public static function countJobsDoneByDay($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( job_end_on < date_sub(now(),interval ".($IntNum+0)." day) )";
		$LowerLimit = "( job_end_on > date_sub(now(),interval ".($IntNum+1)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NULL) AND (error_text IS NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	
	//Examples
	public static function countExamplesCreatedByWeek($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( created_on < date_sub(now(),interval ".(($IntNum+0)*7)." day) )";
		$LowerLimit = "( created_on > date_sub(now(),interval ".(($IntNum+1)*7)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NOT NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	public static function countExamplesCreatedByDay($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( created_on < date_sub(now(),interval ".($IntNum+0)." day) )";
		$LowerLimit = "( created_on > date_sub(now(),interval ".($IntNum+1)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NOT NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	
	//Jobs created but not submitted
	public static function countJobsCreatedButNotSubmittedByWeek($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( created_on < date_sub(now(),interval ".(($IntNum+0)*7)." day) )";
		$LowerLimit = "( created_on > date_sub(now(),interval ".(($IntNum+1)*7)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NULL) AND (submitted_on IS NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	public static function countJobsCreatedButNotSubmittedByDay($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( created_on < date_sub(now(),interval ".($IntNum+0)." day) )";
		$LowerLimit = "( created_on > date_sub(now(),interval ".($IntNum+1)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NULL) AND (submitted_on IS NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	
	//Errors
	public static function countErrorJobsByWeek($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( job_end_on < date_sub(now(),interval ".(($IntNum+0)*7)." day) )";
		$LowerLimit = "( job_end_on > date_sub(now(),interval ".(($IntNum+1)*7)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NULL) AND (error_text IS NOT NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	public static function countErrorJobsByDay($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( job_end_on < date_sub(now(),interval ".($IntNum+0)." day) )";
		$LowerLimit = "( job_end_on > date_sub(now(),interval ".($IntNum+1)." day) )";
		return (CodonOpt_DAO_user_jobs::countJobsWhere("(example_serial IS NULL) AND (error_text IS NOT NULL) AND ".$UpperLimit." AND ".$LowerLimit));
	}
	
	//We cannot count examples, or jobs created but not submitted, as they are housekept very quickly
	
	//Counts Jobs which are NOT examples which meet some criteria
	private static function countJobsWhere($InputWhere) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = "SELECT COUNT(serial) FROM ".CodonOpt_DAO_user_jobs::$DatabaseName." WHERE (".$InputWhere.")";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute() ) {				//Execute
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push ($results,$row["COUNT(serial)"]);
			}									//Add count serial to array
		}
		if ( count($results) == 1 ) {			//If there is one row
			return $results[0];					//Return the result
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}

}
?>
