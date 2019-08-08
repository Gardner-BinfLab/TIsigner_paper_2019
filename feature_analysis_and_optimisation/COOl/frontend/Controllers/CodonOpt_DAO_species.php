<?php
require_once "CodonOpt_DAO_ancestor.php";
require_once "CodonOpt_DTO_species.php";

class CodonOpt_DAO_species extends CodonOpt_DAO_ancestor {
	//Iternal Variables
	private static $DatabaseName = "species";

	//Read in data by Encrypt ID.
	//If no object for this ID is found, return null
	public static function selectAllSpecies() {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$QueryParam = array();					//Prepared Query: empty array
		$Query = "SELECT serial,name FROM ".CodonOpt_DAO_species::$DatabaseName." ORDER BY name";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_species($row)
				);		//Add them to results
			}
		}
		return $results;
	}
	
	public static function selectSpeciesBySerial($InputSerial) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$IntInput = intval($InputSerial);		//Convert into integer
		$QueryParam = array($IntInput);			//Prepared Query: use integer input
		$Query = "SELECT serial,name,TranslationRules_NCBI_Code FROM ".CodonOpt_DAO_species::$DatabaseName." WHERE serial=?";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_species($row)
				);		//Add them to results
			}
		}
		if ( count($results) == 1 ) {
			return $results[0];
		} elseif ( count($results) == 0 ) {
			return null;
		} else {
			die ("Error: More then 1 species returned by selectSpeciesBySerial for input: ".$InputSerial);
		}
	}
}
?>
