<?php
require_once "CodonOpt_Controller_ViewResult_Ancestor.php";
require_once "CodonOpt_DAO_user_results.php";
require_once "CodonOpt_Utility.php";

//Extension of View Result Job, which also handles specific serial for given job.
class CodonOpt_Controller_ViewResultDetail extends CodonOpt_Controller_ViewResult_Ancestor {
	//Variables which store the Get Keys for User Insert and Result Number
	private static $UserInsertGetKey = "usin";
	public static function getUserInsertGetKey() {
		return CodonOpt_Controller_ViewResultDetail::$UserInsertGetKey;
	}
	private static $DisplayIDGetKey = "num";
	public static function getDisplayIDGetKey() {
		return CodonOpt_Controller_ViewResultDetail::$DisplayIDGetKey;
	}
	
	//Private Attributes and their Getters (and encapsulated getters)
	private $CurrentResult;
	public function getCurrentResult() {
		return $this->CurrentResult;
	}
	
	//Get User Insert
	public function getUser_insert() {
		return $this->CurrentResult->getUser_insert();
	}
	//Get Display ID
	public function getDisplay_id() {
		return $this->CurrentResult->getDisplay_id();
	}
	
	//Get User Given Title
	public function getUser_given_title() {
		return $this->CurrentResult->getUser_given_title();
	}
	
	//Handle Hidden Stop Codons
	public function getStop_codon_motifs() {
		return $this->CurrentResult->getNumber_of_stop_codon_motifs();
	}
	
	//This function gets IC fitness but rounded off
	public function getIc_fitness() {
		return $this::RoundForDisplay( $this->CurrentResult->getIc_fitness() );
	}
	
	//This function gets CC fitness but rounded off to limit to 5 Decimal Places
	public function getCc_fitness() {
		return $this::RoundForDisplay( $this->CurrentResult->getCc_fitness() );
	}
	
	//This function gets CAI fitness but rounded off to limit to 5 Decimal Places
	public function getCai_fitness() {
		return $this::RoundForDisplay( $this->CurrentResult->getCai_fitness() );
	}
	
	//This function gets GC content fitness but rounded off to limit to 5 Decimal Places
	public function getGc_content_fitness() {
		return $this::RoundForDisplay( $this->CurrentResult->getGc_content_fitness() );
	}
	
	//GC content rounded to 2 decimal places
	public function getOuputNucleotideGCpercent() {
		return $this::RoundPercentageForDisplay( 		//Convert to Percentage
			( $this->CurrentResult->getTrigger_count_gc_content_outseq() * 100 )
		);
	}
	
	//Get output sequence that has been Nucleotide standardized
	public function getOutputNucleotide() {
		return  $this::getNucleotideStandardizer()				//standardize
			->standardize(	
			$this->CurrentResult->getOutput_sequence()			//Output sequence
		);
	}
	//Output Sequence Translated into a Protein
	private $OutputAsProtein;
	public function getOutputAsProtein() {
		return $this->OutputAsProtein;
	}
	
	//Exclusion, Repeats and Hidden Stop Codons
	public function getOutputNucleotideExclusion() {
		return
		//$this::getNucleotideStandardizer()->standardize(//standardize
			$this->CurrentResult->getOutput_sequence_exclusion()	//Output sequence with exclusion
		//)
		;
	}
	public function getOutput_sequence_repeat_consec() {
		return 			
		//$this::getNucleotideStandardizer()->standardize(		//standardize
			$this->CurrentResult->getOutput_sequence_repeat_consec()		//Output sequence with Repeats
		//)
		;
	}
	public function getOutputBaseCount_repeat_consec() {
		return $this->CurrentResult->getTrigger_count_upcase_outseq_repeat_consec();
	}
	public function getOutput_sequence_repeat_allmotif() {
		return 			
		//$this::getNucleotideStandardizer()->standardize(		//standardize
			$this->CurrentResult->getOutput_sequence_repeat_allmotif()		//Output sequence with Repeats
		//)
		;
	}
	public function getOutputBaseCount_repeat_allmotif() {
		return $this->CurrentResult->getTrigger_count_upcase_outseq_repeat_allmotif();
	}
	public function getOutputNucleotideHiddenStopCodon() {
		return 			
		//$this::getNucleotideStandardizer()->standardize(		//standardize
			$this->CurrentResult->getOutput_sequence_hidden_stop_codon()	//Output sequence with Hidden Stop Codons
		//)
		;
	}	
	

	//Exclusion Fitness Results
	private $ExclusionFitnessArray = array();	//Store seperately to hold parsed input from Job Object
	public function getExclusionFitness() {
		return $this->ExclusionFitnessArray;
	}
	public function getTotalExclusionBasesFound() {
		return $this->CurrentResult->getTrigger_count_upcase_outseq_exclusion();
	}
	
	//Link Key
	public function getLinkKey() {
		return 
			$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id()."&amp;".
			$this::getUserInsertGetKey()."=".intval($this->getUser_insert())."&amp;".
			$this::getDisplayIDGetKey()."=".$this->getDisplay_id();
	}
	
	//Diplay Name
	public function getDetailDisplayName() {
		$tempTitle = $this->getJobDisplayTitle();
		if ( $this->getUser_insert() ) {		//If this is user provided
			$tempTitle .= " (User Defined Sequence: ".$this->getUser_given_title().")";
		} else {								//Otherwise optimized sequence
			$tempTitle .= " (".$this->getDisplay_id();
			if (								//If there is some title
				strlen( $this->getUser_given_title() ) >= 1
			) {									//Append it to the title
				$tempTitle .= ": ".$this->getUser_given_title();
			}
			$tempTitle .= ")";					//Close brackets
		}
		return $tempTitle;
	}
	
	//Constructor takes in Current Job	
	public function CodonOpt_Controller_ViewResultDetail(
		$InputJob,										//The input job (pass to parent)
		$InputResult,									//Input Result (specific to this)
		$GenerateTranslationRulesAndColorPicker			//Whether to generate Translation Rules and Color Picker
	) {
		parent::__construct(							//Pass Job (if any) to Parent
			$InputJob , 								//Use specified Translation Rules + Color Picker (default true)
			$GenerateTranslationRulesAndColorPicker
		);
		if ( isset($InputResult) ) {					//If there are results
			if ( is_object($InputResult) ) {			//If results are object
				if (									//If results are correct class
					get_class($InputResult) == "CodonOpt_DTO_user_results"
				) {
					$this->CurrentResult = $InputResult;//Save Results to property
				}
			}
		}
		
		if ( isset($this->CurrentResult) ) {			//If results were found
		} else {										//Otherwise results not found
			$UserInsert;
			$DisplayID;
			foreach($_GET as $tempKey => $tempValue) {	//Go through GET items
				switch ($tempKey) {	
					case CodonOpt_Controller_ViewResultDetail::$UserInsertGetKey:
						$UserInsert = $tempValue;		//Store it to User Insert
						break;
					case CodonOpt_Controller_ViewResultDetail::$DisplayIDGetKey:
						$DisplayID = $tempValue;		//Store it to Display ID
						break;
				}
			}
			$this->CurrentResult = 						//Save As Current Result
				CodonOpt_DAO_user_results::selectByJobUserInsertDisplayId($this->getCurrentJob(),$UserInsert,$DisplayID);
			if ( isset($this->CurrentResult) ) {		//If retrieval was successful
			} else {									//Otherwise retrieval failed (valid job but not valid result)
				header( "Location: viewresult.php?".$this::getEncryptIDGetKey()."=".$this->getCurrentJob()->getEncrypt_id() );
				echo "error";
				exit;									//Redirect to results
			}			
		}
		
		//Check if this is full result
		if ( 											//If this is full result
			strlen( $this->getOutputNucleotide() )  >= 3
		) {	
			if (										//If using Translation Rules + Color Picker
				$GenerateTranslationRulesAndColorPicker
			) {
				$toRNA 									//Object to do RNA conversion
					= new CodonOpt_StandardizeNucleotide_RNA();
				$InSeq = $toRNA->standardize(			//Convert to RNA
					$this->CurrentResult->getOutput_sequence()
				);		
				
				//Translate Output to Protein	
				$CodonList = str_split($InSeq,3); 		//Break into sets of triplets
				$CodonHash = $this->getTranslationRules()->getCodonHash();
				$AAList = array();
				foreach ($CodonList as $Codon) {
					$aaBase=$CodonHash[$Codon];			//Extract amino acid
					if ( isset($aaBase) ) {				//If Amino acid found
						array_push($AAList, $aaBase);	//Add it to list
					} else {
						die ("Error: Amino Acid not found for: ".$Codon);
					}
				}
				$this->OutputAsProtein = implode($AAList,"");	//Store in this variable

				//Use Codon Optimization to fill Color Picker
				if (	$this->showOptimizationParameter( $this->getOptimize_ic() ) ||	//If maximize/minimize by IC
						$this->showOptimizationParameter( $this->getOptimize_cai() )	//OR by CAI, get Color Picker
				) {
					$this->getIc_ColorPicker()->CountOptSeqCodonUsage(					//Count Optimized Sequence Usage
						$this->getOutputNucleotide()
					);
				}
				if ( $this->showOptimizationParameter( $this->getOptimize_cc() ) ) {	//If maximize/minimize by CC
					$this->getCc_ColorPicker()->CountOptSeqCodonUsage(					//Count Optimized Sequence Usage
						$this->getOutputNucleotide()
					);
				}
			}
		}
		
		{	//Extract Exclusion Sequence
			$rawExclusionFitness = $this->CurrentResult->getExclusion_fitness();
			if ( isset ($rawExclusionFitness) ) {
				$LineArray = explode(";",							//Split according to lines
					$this::getNucleotideStandardizer()				//standardize
						->standardize(	
						$rawExclusionFitness						//Of raw sequence
					)		
				);		
				foreach ($LineArray as $CurrentLine) {				//For each line
					if ($CurrentLine != "") {						//If current line is not empty
						$ColArray = explode(":",$CurrentLine);		//Split into 2
						$this->ExclusionFitnessArray[$ColArray[0]]	//Store inside array
							= $ColArray[1];
					}
				}
			} else {
				$this->ExclusionFitnessArray =  null;
			}
		}
	}

	
	//Generate Comparison Linker
	public static function generateInOutNucleotideComparisonLinker($InCodon,$OutCodon) {
		$Output = "";
		$InCodonArray = str_split(		//Split into Protein
			$InCodon,1
		);
		$OutCodonArray = str_split(		//Split into Protein
			$OutCodon,1
		);
		$MaxSize = count($InCodonArray);
		for ($i=0; $i<$MaxSize; $i++) {
			if ($InCodon[$i] == $OutCodon[$i]) {
				$Output .= "|";
			} else {
				$InType = CodonOpt_Utility::getNucleotideBaseType($InCodon[$i]);
				$OutType = CodonOpt_Utility::getNucleotideBaseType($OutCodon[$i]);
				if ($InType == $OutType) {
					$Output .= "#";
				} else {
					$Output .= "*";
				}
			}
		}
		return $Output;
	}
	
	//Generate List of AA Codons Sorted by AA alphabetical order
	public function SortHostVsOptimizedCodonRelFreqLines() {
		//Error Check
		$SortedLines = $this->getIc_ColorPicker();	//Temporary hold color picker
		if ( isset( $SortedLines )	) {				//If IC color picker is present
			$SortedLines = 							//Extract Codon Hash
				$this->getIc_ColorPicker()->getFilteredCodonHash();
			usort(									//Sort codons according to aa3 letter name
				$SortedLines,
				function($a, $b) {					//Sort according to amino acid
					return strcmp(
						CodonOpt_Utility::convertAA1to3($a->getAAbase()) ,
						CodonOpt_Utility::convertAA1to3($b->getAAbase())
					);
				}
			);
			return $SortedLines;
		} else {									//Otherwise IC color picker is absent
			return null;							//End function
		}
	}
	
	//Generate List of AA Codons split into multiple Lines
	public function SplitHostVsOptimizedCodonRelFreqLines() {
		//Get Sorted Lines
		$AllLines = $this->SortHostVsOptimizedCodonRelFreqLines();
		if ( isset( $AllLines )	) {					//If AllLines are found
			//Variables for this function
			$PreviousBase = null;
			$CurrentCodonsPerLine = 0;
			$CurrentLine = 0;						//Start from line 0
			$CurrentAAList = array();
			$SplitLines = array();					//Variable to return
			$SplitLines[$CurrentLine] = array();	//Create nested array
			
			//Go through all codons
			foreach ($AllLines as $tempItem) {
				$TripletCode = CodonOpt_Utility::convertAA1to3($tempItem->getAAbase());
				if ($TripletCode == $PreviousBase) {			//If same as previous base
					array_push($CurrentAAList,$tempItem);		//Add it to current AA list
				} else {										//Otherwise new base
					CodonOpt_Controller_ViewResultDetail::SplitHostVsOptimizedCodonRelFreqLines_AddCurrentAAList(
						$SplitLines, $CurrentLine, $CurrentAAList
					);
					$PreviousBase = $TripletCode;			//Reset Code
					array_push($CurrentAAList,$tempItem);	//Seed current AA list
				}
			}
			CodonOpt_Controller_ViewResultDetail::SplitHostVsOptimizedCodonRelFreqLines_AddCurrentAAList(
				$SplitLines, $CurrentLine, $CurrentAAList
			);
			return $SplitLines;
		} else {								//Otherwise IC color picker is absent
			return null;						//End function
		}
	}
	
	//Add list of codons for given AA to line
	//If adding codons for this AA would exceed the line limit, add to next line
	private static $MaxCodonsPerLine = 20;
	private function SplitHostVsOptimizedCodonRelFreqLines_AddCurrentAAList(
		&$SplitLines, &$CurrentLine, &$CurrentAAList
	) {
		
		$ProjectedSize = count($SplitLines[$CurrentLine]) + count($CurrentAAList);
		if ( $ProjectedSize > 						//If projected size exceeds limit
			CodonOpt_Controller_ViewResultDetail::$MaxCodonsPerLine
		) {	
			$CurrentLine++;							//Place this AA's codons on next line
			$SplitLines[$CurrentLine] = array();	//Seed array
		}
		$SplitLines[$CurrentLine] = array_merge(
			$SplitLines[$CurrentLine],
			$CurrentAAList
		);
		$CurrentAAList = array();				//Reset list
	}
}
?>
