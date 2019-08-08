<?php
require_once "CodonOpt_Controller_Ancestor_User_Job.php";
require_once "CodonOpt_StandardizeNucleotide_DNA.php";
require_once "CodonOpt_StandardizeNucleotide_RNA.php";
require_once "CodonOpt_DAO_species.php";
require_once "CodonOpt_DAO_translationrules.php";
require_once "CodonOpt_ColorPicker_Codon.php";
require_once "CodonOpt_ColorPicker_CodonPair.php";
require_once "CodonOpt_Utility.php";

//This View Result handles functions related to the very basics of Job
//It is extended by ViewResultSummary for the summary page and ViewResultDetail for the details page
//It is also used by the Still Processing Page to get the page title
class CodonOpt_Controller_ViewResult_Ancestor extends CodonOpt_Controller_Ancestor_User_Job {	
	//Variable which stores Nucleotide Standardizer
	private static $NucleotideStandardizer; 
	public static function getNucleotideStandardizer() {
		if (! isset(CodonOpt_Controller_ViewResult_Ancestor::$NucleotideStandardizer) ) {
			//Standardize to Either DNA or RNA
			CodonOpt_Controller_ViewResult_Ancestor::$NucleotideStandardizer = new CodonOpt_StandardizeNucleotide_DNA();
			//CodonOpt_Controller_ViewResult_Ancestor::$NucleotideStandardizer = new CodonOpt_StandardizeNucleotide_RNA();
		}
		return (CodonOpt_Controller_ViewResult_Ancestor::$NucleotideStandardizer);
	}
	//Round Numbers off for Display (to 4 significant figures)
	public static function RoundForDisplay($InputNum) {
		return CodonOpt_Utility::RoundToSignificantFigure($InputNum,5);
	}
	//This is used to round off for percentages
	//Used for: Input Sequence GC content, User Target GC content, Output Sequence GC content
	public static function RoundPercentageForDisplay($InputNum) {
		return round($InputNum,2)."%";
	}
	
	public function getJobDisplayTitle() {
		$tempTitle = "".$this->getCurrentJob()->getTitle();
		if ($tempTitle != "") {				//If there is valid title
			return $tempTitle;				//Return that title
		} else {							//Otherwise no valid title
			return "Your Target Sequence";	//Return generic reply
		}
	}
	
	//Whether to show Optimization Parameter 
	public function showOptimizationParameter($Input) {
		if ($Input >= 1) {
			return true;
		} else {
			return false;
		}
	}
	//What Type of Optimization is Parameter
	public function getOptimizationParameterType($Input) {
		;;;;;;if ($Input == 0) {
			return "Ignore";
		} elseif ($Input == 1) {
			return "Max";
		} elseif ($Input == 2) {
			return "Min";
		} else {
			die ("Unexpected Input in getOptimizationParameterType: ".$Input);
		}
	}
	
	
	//This function determines whether we should show CC fitness
	public function getOptimize_ic() {
		return $this->getCurrentJob()->getOptimize_ic();
	}
	
	//This function determines whether we should show CC fitness
	public function getOptimize_cc() {
		return $this->getCurrentJob()->getOptimize_cc();
	}
	
	//This function determines whether we should show CAI fitness
	public function getOptimize_cai() {
		return $this->getCurrentJob()->getOptimize_cai();
	}

	//Handle Hidden Stop Codons
	public function getOptimize_hidden_stop_codon() {
		return $this->getCurrentJob()->getOptimize_hidden_stop_codon();
	}
	
	//This function determines whether we should show CAI fitness
	public function showGC_target() {
		$Optimize_gc_mode = $this->getCurrentJob()->getOptimize_gc_mode();
		if ($Optimize_gc_mode >= 1) { 
			return true;
		} else {
			return false;
		}
	}
	//This function gets GC Target Value (and adds a % at the end so that its clear)
	public function getGC_target() {
		return $this::RoundPercentageForDisplay( $this->getCurrentJob()->getOptimize_gc_target() );
	}
	
	
	//Whether to show the repeat_consec_ report (yes if any repeat_consec_mode is yes)
	//This function is seperate from getRepeat_consec_mode(), so that if we want to, in future we can implement multiple consecutive repeat options
	//E.g. remove 3 bp long repeated 5 times, AND remove 5 bp long repeated 3 times
	public function showRepeat_consec_report() {
		$TrueCount = 0;
		if ( $this->getCurrentJob()->getRepeat_consec_mode() ) { $TrueCount++; }
		if ($TrueCount >= 1) {
			return true;
		} else {
			return false;
		}
	}

	//This function gets the motif length and count
	public function getRepeat_consec_mode() {
		return $this->getCurrentJob()->getRepeat_consec_mode();
	}
	public function getRepeat_consec_length() {
		return $this->getCurrentJob()->getRepeat_consec_length();
	}
	public function getRepeat_consec_count() {
		return $this->getCurrentJob()->getRepeat_consec_count();
	}
	
	//Whether to show the repeat_allmotif_ report (yes if any repeat_allmotif_mode is yes)
	//This function is seperate from getRepeat_allmotif_mode(), so that if we want to, in future we can implement multiple consecutive repeat options
	//E.g. remove 3 bp long repeated 5 times, AND remove 5 bp long repeated 3 times
	public function showRepeat_allmotif_report() {
		$TrueCount = 0;
		if ( $this->getCurrentJob()->getRepeat_allmotif_mode() ) { $TrueCount++; }
		if ($TrueCount >= 1) {
			return true;
		} else {
			return false;
		}
	}

	//This function gets the motif length and count
	public function getRepeat_allmotif_mode() {
		return $this->getCurrentJob()->getRepeat_allmotif_mode();
	}
	public function getRepeat_allmotif_length() {
		return $this->getCurrentJob()->getRepeat_allmotif_length();
	}
	public function getRepeat_allmotif_count() {
		return $this->getCurrentJob()->getRepeat_allmotif_count();
	}
	
	//Whether to show the exclusion report (yes if exclusion sequence is present)
	public function showExclusionReport() {
		$Exclusion_sequence = $this->getCurrentJob()->getExclusion_sequence();
		if ( isset($Exclusion_sequence) ) {
			if ( strlen( trim($Exclusion_sequence) )>=1 ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}	

	//Input Sequence
	public function getInputSequence() {
		if ( $this->getCurrentJob()->getIs_input_protein() ) {		//If input is protein
			return $this->getCurrentJob()->getInput_sequence();		//return sequenct directly
		} else {												//otherwise if nucleotide
			return $this::getNucleotideStandardizer()			//standardize
				->standardize(	
				$this->getCurrentJob()->getInput_sequence()			//Output sequence
			);		
		}
	}
	public function getInputSequenceGCpercent() {	
		return $this::RoundPercentageForDisplay(
			CodonOpt_Utility::count_GC_content( $this->getInputSequence() ) * 100
		);
	}
	//Checks whether input sequence is nucleotide
	public function isInputSeqNucelotide() {
		return (! $this->getCurrentJob()->getIs_input_protein() );
	}
	
	//For Individual Codon
	//If Color Picker not created, then this was not an optimization method
	private $Ic_ColorPicker = null;	//Default to null
	public function getIc_ColorPicker() {
		return $this->Ic_ColorPicker;
	}
	public function getColorForIc_Codon($tempKey) {
		return $this->Ic_ColorPicker->getColorForCodon($tempKey);
	}
	
	//For Codon Context
	//If Color Picker not created, then this was not an optimization method
	private $Cc_ColorPicker = null;
	public function getCc_ColorPicker() {
		return $this->Cc_ColorPicker;
	}
	public function getColorForCc_Codon($tempKey) {
		return $this->Cc_ColorPicker->getColorForCodon($tempKey);
	}
	
	private $TranslationRules = null;
	public function getTranslationRules() {
		return $this->TranslationRules;
	}
	
	private $SpeciesName = null;
	public function getSpeciesName() {
		return $this->SpeciesName;
	}
	
	
	//Constructor takes in Current Job	
	public function CodonOpt_Controller_ViewResult_Ancestor(
		$InputJob,								//Input Job (accepts null, in which case it will auto-search)
		$GenerateTranslationRulesAndColorPicker	//Whether to generate Translation Rules and Color Picker
	) {
		parent::__construct($InputJob);			//Parent saves or extracts job
		{	//Error Check: Make sure that job is complete
			$JobEndOn = $this->getCurrentJob()->getJob_end_on();
			$SubmitOn = $this->getCurrentJob()->getSubmitted_on();
			$PostProcOn = $this->getCurrentJob()->getPost_processed_on();
			$EncryptID = $this->getCurrentJob()->getEncrypt_id();
			if ( isset($SubmitOn) ) {			//If job has been submitted, 
				if ( isset($JobEndOn) ) {		//If job has ended
					if ( isset($PostProcOn) ) {	//If job has been post processed
					} else {					//Otherwise job has NOT been post processed
						$this->PostProcessCurrentJob();
					}
				} else {						//Otherwise job has not yet ended
				}
			} else {							//Otherwise job not submitted (but job still valid)
				header("Location: setup_optimization.php?".$this::getEncryptIDGetKey()."=".$EncryptID);	
				exit;							//Go back to Setup
			}			
		}
		
		//Run this section unless specified otherwise
		//So as to speed up the Result Summary Page
		if ( $GenerateTranslationRulesAndColorPicker ) {
			//Rest of processes only occur if Job was stored successfully
			$RawIc_Frequency;	//Local variables to store the string
			$RawCc_Frequency;
			$TranslationRulesNCBIcode;
			//Get Codon Frequency
			$RawIc_Frequency = $this->getCurrentJob()->getIc_frequency();
			$RawCc_Frequency = $this->getCurrentJob()->getCc_frequency();
			if ( $this->getCurrentJob()->getUse_custom_species() ) {//If using custom species
				$TranslationRulesNCBIcode = $this->getCurrentJob()->getTranslationRules_NCBI_code();
				$this->SpeciesName = "User Defined Custom Species";
			} else {												//Otherwise using inbuilt Species
				$species = CodonOpt_DAO_species::selectSpeciesBySerial(
					$this->getCurrentJob()->getInbuilt_species_serial()	//Extract species with this serial
				);
				if ( isset($species) ) {							//If species found
					$TranslationRulesNCBIcode = $species->getTranslationRules_NCBI_code();
					$this->SpeciesName = $species->getName();
				} else {
					die( "Error: Species with this serial not found: ".$this->getCurrentJob()->getInbuilt_species_serial() );
				}
			}
			
			//Extract Translation Rules for this result
			$this->TranslationRules = CodonOpt_DAO_translationrules::selectByNCBIcode($TranslationRulesNCBIcode);
			if (! isset($this->TranslationRules) ) {
				die ("Error! Translation Rules not found for: ".$TranslationRulesNCBIcode);
			}
			
			//Get IC/CAI and CC Color Pickers (if appropriate)
			//Note: Filling out with CountOptSeqCodonUsage will be done in ViewResultDetail constructor
			if (	$this->showOptimizationParameter( $this->getOptimize_ic() ) ||	//If maximize/minimize by IC
					$this->showOptimizationParameter( $this->getOptimize_cai() )	//OR by CAI
			) {
				$this->Ic_ColorPicker = new CodonOpt_ColorPicker_Codon($RawIc_Frequency, $this->TranslationRules);
				if ( $this->isInputSeqNucelotide() ) {
					$this->Ic_ColorPicker->CountQuerySeqCodonUsage( $this->getInputSequence() );
				}
			}
			if ( $this->showOptimizationParameter( $this->getOptimize_cc() ) ) {	//If maximize/minimize by CC
				$this->Cc_ColorPicker = new CodonOpt_ColorPicker_CodonPair($RawCc_Frequency, $this->TranslationRules);
				if ( $this->isInputSeqNucelotide() ) {
					$this->Cc_ColorPicker->CountQuerySeqCodonUsage( $this->getInputSequence() );
				}
			}
		}
	}
	
	//Post Process Current Job
	protected function PostProcessCurrentJob() {
		//Flag Post Processing as done FIRST
		//Because later processes will also call the post processing check
		//And this will be called repeatedly, unless it is flagged as DONE on the first check
		$this->getCurrentJob()->postProcessCurrentJob();		//Flag current job as post processed
		if ( $this->getCurrentJob()->getIs_input_protein() ) {	//If input data was protein
			//Do nothing
		} else {											//Otherwise input data was nucleotide
			//Insert original input into table of results	
			require_once "CodonOpt_Controller_ViewResultSummaryInsertNewResult.php";
			$InputResultMaker = 							//Generate object to make Result DTO
				new CodonOpt_Controller_ViewResultSummaryInsertNewResult( $this->getCurrentJob() , false );
			$InputResultMaker->								//Put original input sequence into object
				setInputSequence( $this->getCurrentJob()->getInput_sequence() );
			$InputResultMaker->setTitle("");				//Give Empty Title
			$ResultDTO = 									//Generate Result DTO
				$InputResultMaker->validateGenerateNewUserResult();
			if ( isset($ResultDTO) ) {						//If generation was successful
				$ResultDTO->setDisplay_id(0);				//Add Display ID to object
				CodonOpt_DAO_user_results::					//Save object to Database
					insertNewUserResult($ResultDTO);
			} else {										//Otherwise generation failed
				//Do nothing: Likely cause is that input uses different set of translation rules
				//die ( "Original Sequence is not valid input. ".$InputResultMaker->getInputSequenceErrorMsg() );
			}
		}
		//Finish up: Update Job with Post Processed flag
		CodonOpt_DAO_user_jobs::update($this->getCurrentJob());	//Update Post Processed Job
		$this->reloadCurrentJob(); 								//Reload Current job
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
}
?>
