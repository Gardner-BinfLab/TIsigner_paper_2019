<?php
require_once "CodonOpt_DAO_ancestor.php"; 
require_once "CodonOpt_DTO_translationrules.php"; 


class CodonOpt_DAO_translationrules extends CodonOpt_DAO_ancestor {
	//Iternal Variables
	private static $DatabaseName = "translationrules";

	//Read in all data.
	//Only select Name and NCBI code (to speed things up)
	public static function selectAllTranslationRules() {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$QueryParam = array();					//Prepared Query: empty array
		$Query = "SELECT NCBI_code,name FROM ".CodonOpt_DAO_translationrules::$DatabaseName." ORDER BY NCBI_code";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_translationrules($row)
				);		//Add them to results
			}
		}
		return $results;
	}
	
	//Read in Data by NCBI code
	//If no object found of this ID, return null
	//Read in all fields (*)
	public static function selectByNCBIcode($input) {
		$intput = intval($input);				//Convert input into integer
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$QueryParam = array($intput);			//Prepared Query: input as integer
		$Query = "SELECT * FROM ".CodonOpt_DAO_translationrules::$DatabaseName." WHERE NCBI_code = ?";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_translationrules($row)
				);		//Add them to results
			}
		}
		if ( count($results) == 1 ) {			//If there is one row
			return $results[0];					//Return that row
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}		
	}
}
?>
