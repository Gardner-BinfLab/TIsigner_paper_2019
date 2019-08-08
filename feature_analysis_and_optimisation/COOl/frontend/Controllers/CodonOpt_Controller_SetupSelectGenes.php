<?php
require_once("CodonOpt_Controller_Setup_Ancestor.php");
require_once("CodonOpt_DAO_species.php");
require_once "CodonOpt_DAO_genes.php";
require_once("CodonOpt_Utility.php");


//This Class is meant to control the Exclusion Sequence page
class CodonOpt_Controller_SetupSelectGenes extends CodonOpt_Controller_Setup_Ancestor {
	//Internal Variables with Getters
	private static $GeneSortMaxLength = 1000000;
	public static function getGeneSortMaxLength() {
		return CodonOpt_Controller_SetupSelectGenes::$GeneSortMaxLength;
	}
	private static $DisabledHeaderOptionValue = "null_header";
	public static function getDisabledHeaderOptionValue() {
		return CodonOpt_Controller_SetupSelectGenes::$DisabledHeaderOptionValue;
	}
	
	//Form Fields and Getter/Setters
	private $gene_sort_box;						//User Input Gene Sort Box
	public function getGene_sort_box() {
		return $this->gene_sort_box;
	}
	public function setGene_sort_box($input) {
		if ( isset ($input) ) {
			$this->gene_sort_box = $input;
		}
	}
	private $saved_gene_sort_box;				//The Gene Sort Box as found in database
	private $clean_saved_gene_sort_list;		//Cleaned Up Gene Sort List
	
	//List of Genes by Access_ID
	//Stored/Returned as an Array (explode/implode done internally)
	//We store by Access_ID, so that it can be displayed directly on output page
	private $gene_select_hash = array();
	private static $gene_select_delimiter = ",";
	public function getGene_select_hash() {
		return $this->gene_select_hash;
	}
	public function setGene_select_hash($inArray) {
		if ( isset ($inArray) ) {					//Make sure input is valid
			if ( is_array($inArray) ) {				//Make sure input is an array
				$this->gene_select_hash = $this->FindRecognizedGenesInArray($inArray);
			}
		}
	}

	//List of genes (get only)
	private $all_gene_list = array();
	public function getAll_gene_list() {
		return $this->all_gene_list;
	}
	private $sorted_gene_list = null;
	public function getSorted_gene_list() {
		return $this->sorted_gene_list;					//return sorted genes
	}
	private $unsorted_gene_list = null;
	public function getUnsorted_gene_list() {
		return $this->unsorted_gene_list;				//return unsorted genes
	}	
	private $unrecognized_gene_list = null;
	public function getUnrecognized_gene_list() {
		return $this->unrecognized_gene_list;			//return unrecognized genes
	}	
	
	//Error Messages only have Getters
	private $GeneSortBoxErrorMsg;
	public function getGeneSortBoxErrorMsg() {
		return $this->GeneSortBoxErrorMsg;
	}
	private $GeneSelectErrorMsg;
	public function getGeneSelectErrorMsg() {
		return $this->GeneSelectErrorMsg;
	}	
	
	//Name of Selected Species
	private $SpeciesName = null;
	public function getSpeciesName() {
		return $this->SpeciesName;
	}
	public function get_heg_link() {
		return 
			"sample-heg-".					//Prefix
			str_ireplace(					//Name, replace space with underscore
				" ","_",$this->SpeciesName
			).
			".txt";							//Suffix
	}
	
	//===========
	//Constructor
	//===========
	public function CodonOpt_Controller_SetupSelectGenes() {
		parent::__construct();								//Parent extracts job
		$species = CodonOpt_DAO_species::selectSpeciesBySerial(
			$this->getCurrentJob()->getInbuilt_species_serial()
		);													//Extract species with this serial
		if ( isset($species) ) {							//If species found
			$this->SpeciesName = $species->getName();		//Save String
			$temp_all_gene_list = 							//Extract List of Genes
				CodonOpt_DAO_genes::selectGenesBySpeciesSerial( $this->getCurrentJob()->getInbuilt_species_serial() );
			if ( isset($temp_all_gene_list) ) {
				foreach ($temp_all_gene_list as $gene) {	//Go through list of genes
					$this->all_gene_list[					//Store in All Gene List
						$gene->getAccess_id()				//Hash by Access_id
					] = $gene;
				}
			} else {
				die ("Error! No Genes found for species: ".$this->SpeciesName);
			}
		} else {
			die( "Error: Species with this serial not found: ".$this->CurrentJob->getInbuilt_species_serial() );
		}				
		$this->ReloadVariables();
		
		
	}
	
	//================
	//Other Functions:
	//================
	private static function ExtractUniqueGeneID($inputStr) {
		$output = array();
		$tempCopy = $inputStr;						//Copy currently saved content
		$tempCopy = str_ireplace					//Replace all Seperator Chars with comma
			(CodonOpt_Utility::GetTabSpacesLineBreaks(),",",$tempCopy);
		$tempArray = explode(",",$tempCopy);		//Divide into individual elements
		$tempHash = array();						//Hash to remove repeats.
		foreach ($tempArray as $geneid) {			//For each Gene ID
			if (strlen($geneid) >= 1) {				//Check it has content
				if (! array_key_exists				//If Key is NOT in Hash
					($geneid,$tempHash) 
				) {
					array_push						//Add to array
						($output , $geneid);
					$tempHash[$geneid] = $geneid;	//Add it to hash
				}
			}
		}
		return $output;
	}
	
	//Make Note of which Genes are recognized within array
	private function FindRecognizedGenesInArray($inArray) {
		$output = array();					//Variable to hold output
		foreach($inArray as $geneid) {		//Go through array
			if ( array_key_exists			//If Gene ID is in all genes array
				( $geneid, $this->all_gene_list )
			) {								//Store to Hash
				$output[$geneid] = $geneid;
			}
		}
		return $output;						//return list of recognized genes
	}
	
	private function ReloadVariables() {
		$this->saved_gene_sort_box = $this->getCurrentJob()->getInbuilt_species_gene_sort();
		$this->gene_sort_box = $this->saved_gene_sort_box;
		{	//Load Selected Genes
			$this->gene_select_hash = array();			//Reset Array
			$tempArray = explode(						//Parse from Job
				CodonOpt_DAO_user_jobs::getInbuilt_species_gene_list_delimiter(),
				$this->getCurrentJob()->getInbuilt_species_gene_list()
			);
			foreach ($tempArray as $geneid) {
				$this->gene_select_hash[$geneid] = $geneid;
			}
		}
		
		//Reset Current Lists:
		$this->sorted_gene_list = array();
		$this->unsorted_gene_list = array();
		$this->unrecognized_gene_list = array();
		
		//Classify into sorted list
		$this->clean_saved_gene_sort_list = 	//Get List of Unique IDs
			CodonOpt_Controller_SetupSelectGenes::ExtractUniqueGeneID($this->saved_gene_sort_box);		
		$sorted_gene_id_list = array();
		foreach (								//Go through genes on sort list in order
			$this->clean_saved_gene_sort_list as $gene_id
		) {	
			if ( strlen($gene_id) >= 1 ) {		//Ensure Gene ID is valid
				if ( array_key_exists			//If it is in user input list of genes
					($gene_id , $this->all_gene_list)
				) {
					if (! array_key_exists  	//If it is NOT in existing list (do not place duplicates
						($gene_id , $sorted_gene_id_list)
					) {
						array_push				//Add it to list of sorted genes according to order
							($this->sorted_gene_list,$this->all_gene_list[$gene_id]);
						$sorted_gene_id_list	//Add ID to list of recognized genes
							[$gene_id] = true;	
					}
				} else {
					$this->unrecognized_gene_list[$gene_id] = $gene_id;
				}
			}
		}

		//Put leftover in unsorted list (and sort by Serial)
		foreach ($this->all_gene_list as $gene) {
			if (! array_key_exists(			//NOT!
				$gene->getAccess_id(),		//If Access_id
				$sorted_gene_id_list		//is (NOT) in list of sorted gene ID
			) ) {
				array_push					//Add it to unsorted list
					($this->unsorted_gene_list,$gene);
			}				
		}
		usort( 								//Sort according to serial
			$this->unsorted_gene_list,
			function($a, $b) {				//Nested Function
				$output = strcmp( $a->getAccess_id(), $b->getAccess_id() );
				return $output;
			}
		);
	}
	
	//Update Just the Gene Sort Box
	public function CheckAndUpdateGeneSort() {
		;;;;;;if ( strlen($this->gene_sort_box) > CodonOpt_Controller_SetupSelectGenes::$GeneSortMaxLength ) {
			$this->GeneSortBoxErrorMsg = "Gene Sorting List is too long. Please trim it down to ".CodonOpt_Controller_SetupSelectGenes::$GeneSortMaxLength." characters or less";
		} elseif ( strlen($this->gene_sort_box) == 0) {
			$this->GeneSortBoxErrorMsg = "Please input some Gene IDs";			
		} else {										//If String not too short or long
			$OtherChar = array("_","-",".");			//Check for illegal Chars
			$tempCopy = $this->gene_sort_box."";		//(concatenate to remove null)
			$tempCopy = str_ireplace($OtherChar,"",$tempCopy);
			$tempCopy = str_ireplace(CodonOpt_Utility::GetAlphaNumeric(),"",$tempCopy);
			$tempCopy = str_ireplace(CodonOpt_Utility::GetTabSpacesLineBreaks(),"",$tempCopy);
			if ($tempCopy == "") {						//If string is now empty
				$tempArray = 							//Extract Unique IDs
					CodonOpt_Controller_SetupSelectGenes::ExtractUniqueGeneID($this->gene_sort_box);	
				$tempArray = 							//Shortlist to recognized genes
					$this->FindRecognizedGenesInArray($tempArray);	
				if ( count($tempArray) >= 1 ) {			//If at least one gene recognized
					//Save gene sort to database
					$this->getCurrentJob()->setInbuilt_species_gene_sort($this->gene_sort_box);
					$this->getCurrentJob()->			//Save to Current Job
						setInbuilt_species_gene_list( 	//Store Reognized Genes to Job
							implode( CodonOpt_DAO_user_jobs::getInbuilt_species_gene_list_delimiter(), array_keys($tempArray) )
					);
					CodonOpt_DAO_user_jobs::update($this->getCurrentJob());
					$this->ReloadVariables();			//Reload variables genes on list
				} else {
					$this->GeneSortBoxErrorMsg = "None of the specified Gene IDs were recognized. Please check your input.";
				}				
			} else {									//Otherwise string not empty
				$this->GeneSortBoxErrorMsg = "Gene Sorting List can only contain alphanumeric characters, dash, underscore, spaces, tabs and linebreaks.";
			}
		}
	}
	
	public function CheckAndSaveGeneSelection() {
		if (count($this->gene_select_hash) >= 1) {	//If there is at least one gene
			$this->getCurrentJob()->				//Save to Current Job
				setInbuilt_species_gene_list( implode(
					CodonOpt_DAO_user_jobs::getInbuilt_species_gene_list_delimiter(),
					array_keys($this->gene_select_hash)
				)
			);
			CodonOpt_DAO_user_jobs::				//Update to Database
				update($this->getCurrentJob());		//Redirect to next step
			header("Location: setup_exclusion.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id());
			exit;
		} else {
			echo "";
			$this->GeneSelectErrorMsg = "Please select at least 1 gene.";
		}
	}
}
?>