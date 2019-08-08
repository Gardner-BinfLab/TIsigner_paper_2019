<?php
require_once "CodonOpt_DAO_ancestor.php";
require_once "CodonOpt_DTO_genes.php";

class CodonOpt_DAO_genes extends CodonOpt_DAO_ancestor {
	//Internal Variables
	private static $DatabaseName = "genes";

	//Read in general data (excluding ic_freq and cc_freq) by Species
	public static function selectGenesBySpeciesSerial($InputSerial) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$IntInput = intval($InputSerial);		//Convert into integer
		$QueryParam = array($IntInput);			//Prepared Query: use integer input
		$Query = "SELECT serial,species_serial,access_id,name,description FROM ".CodonOpt_DAO_genes::$DatabaseName." WHERE species_serial=? ORDER BY access_id";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_genes($row)
				);		//Add them to results
			}
		}
		if ( count($results) >= 1 ) {			//If there is one or more results
			return $results;					//return all results
		} elseif ( count($results) == 0 ) {		//If there are no results
			return null;						//return null
		}
	}
	
	//This converts the list of genes into a format that can be stored in the inbuilt_species_gene_list column
	public static function generateGeneListForSpeciesSerial($InputSerial) {
		$Output = array();
		$GeneList = CodonOpt_DAO_genes::		//Get List of Genes
			selectGenesBySpeciesSerial($InputSerial);
		foreach ($GeneList as $Gene) {			//Extract Access_ids
			array_push( $Output , $Gene->getAccess_id() );
		}
		return $Output;							//Return of Access_ids
	}
	
	//Read in all data (including ic_freq and cc_freq) for a particular gene
	public static function selectGenesBySerial($InputSerial) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$IntInput = intval($InputSerial);		//Convert into integer
		$QueryParam = array($IntInput);			//Prepared Query: use integer input
		$Query = "SELECT * FROM ".CodonOpt_DAO_genes::$DatabaseName." WHERE serial=?";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute($QueryParam) ) {	//Execute MUST accept an array
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push($results,			//Transfer to output
					new CodonOpt_DTO_species($row)
				);		//Add them to results
			}
		}
		if ( count($results) >= 1 ) {			//If there is one result
			return $results[0];					//return that result
		} elseif ( count($results) == 0 ) {		//otherwise no results
			return null;						//return null
		} else {								//Otherwise more than one result
			die ("Error: More then 1 gene returned by selectGenesBySerial for input: ".$InputSerial);
		}
	}
}
?>
