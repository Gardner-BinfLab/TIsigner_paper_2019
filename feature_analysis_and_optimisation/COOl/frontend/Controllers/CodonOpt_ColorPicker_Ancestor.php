<?php
require_once "CodonOpt_ColorPicker_Item.php";
require_once "CodonOpt_Controller_ViewResult_Ancestor.php";

//This class is meant to contain methods common to both Codon and CodonPair
/*Principally, it has the code for a colour picker. This is how it works
	-The child class should feed the values of each codon to the ancestor via "updateValues()"
	-The ancestor should note the highest and lowest non-zero value (We use non-zero for reasons below)
	-The child then asks the Ancestor for a color based on an input value through "getHostColorForValue()"
	-If that color is zero, the ancestor assigns it the lowest color
	-If that color is between the non-zero minimum and maximum
	-then it assigns it a colour between the second lowest and highest value

By setting 0 to the lowest, and non-zero minimum to the second lowest, we take care of colour scaling.
E.g. 
*/


abstract class CodonOpt_ColorPicker_Ancestor {
	//Hash which stores codons (populated by child)
	protected $CodonHash;
	//This returns the Filtered Hash. 
	//For individual codons this would be everything
	//For Codon Context, it removes "impossible codon pairs" (those that start with *)
	public function getFilteredCodonHash() {	
		$TempHash = array();
		foreach ($this->CodonHash as $Key=>$Codon) {
			$aa = $Codon->getAAbase();				//Extract amino acid
			if ( strlen($aa)==1 ) {					//If it is just 1
				$TempHash[$Key] = $Codon;			//This is Individual Codon
			} else {								//Otherwise this is codon context
				if ( substr($aa,0,1) == "*" ) {		//If starts with stop (impossible pair)
				} else {							//otherwise it is OK
					$TempHash[$Key] = $Codon;		//Add to Hash
				}
			}
		}
		return $TempHash;
	}

	abstract public function CountOptSeqCodonUsage($InputSeq);
	abstract public function CountQuerySeqCodonUsage($InputSeq);
	
	//Variable which stores Nucleotide Standardizer
	public static function StandardizeNucleotide($inputseq) {
		return (
			CodonOpt_Controller_ViewResult_Ancestor::getNucleotideStandardizer()->standardize(
				$inputseq
			)
		);
	}
	
	//Return Color Object for specified Codon
	public function getHostColorForCodon($input) {
		$tempItem = $this->CodonHash[$input];
		if ( isset ($tempItem) ) {
			return $tempItem->getHostColor();
		} else {
			die ("Error: Input Codon not within Codon Hash");
		}
	}
	
	//Return Color Object for specified Codon
	public function getTitleForCodon($input) {
		$tempItem = $this->CodonHash[$input];
		if ( isset ($tempItem) ) {
			return $tempItem->getHostRelFreqForDisplay();
		} else {
			die ("Error: Input Codon not within Codon Hash");
		}
	}	
	
	//Constructor Takes in String and stores it into CodonCountHash
	protected function CodonOpt_ColorPicker_Ancestor($InputString) {
		$LineArray = explode(";",								//Standardize and Split according to lines
			$this::StandardizeNucleotide($InputString)
		);		
		foreach ($LineArray as $CurrentLine) {					//For each line
			if ($CurrentLine != "") {							//If current line is not empty
				$ColArray = explode(":",$CurrentLine);			//Split into 2
				$tempCodon = $ColArray[0];
				if ( isset($this->CodonHash[$tempCodon]) ) {	//If codon is present
					if (										//If codon is zero
						$this->CodonHash[$tempCodon]->getHostRawCount() == 0
					) {											//Transfer value
						$this->CodonHash[$tempCodon]->setHostRawCount($ColArray[1]);
					} else {									//Otherwise codon is not zero
						die ("Error: The following codon occurs more than once: ".$tempCodon);
					}
				} else {										//Otherwise codon not found
					die ("Error: The following codon was not found in CodonCountHash: ".$CurrentLine);
				}
			}
		}
	}
	
	//Calculate Synonymous Total for Host
	protected function recalculateHostSynonymousTotal() {
		$SynonymousTotalHash = array();						//Synonymous Total for each Amino Acid
		foreach ($this->CodonHash as $tempKey=>$tempItem) {	//For each Codon
			$AAbase = $tempItem->getAAbase();				//Extract Amino Acid
			if ( isset($SynonymousTotalHash[$AAbase]) ) {	//If value already exists for this AA
				$SynonymousTotalHash[$AAbase] 				//Add to value
					+= $tempItem->getHostRawCount();	
			} else {										//Otherwise value not present
				$SynonymousTotalHash[$AAbase] 				//Set initial value
					= $tempItem->getHostRawCount();	
			}
		}
		foreach ($this->CodonHash as $tempKey=>$tempItem) {		//For each codon
			$tempItem->setHostSynonTotal(						//Set synonymous total
				$SynonymousTotalHash[ $tempItem->getAAbase() ] 	//For respective amino acid
			);
		}
	}
	
	//Calculate Synonymous Total for Optimized Sequence
	public function recalculateOptSeqSynonymousTotal() {
		$SynonymousTotalHash = array();						//Synonymous Total for each Amino Acid
		foreach ($this->CodonHash as $tempKey=>$tempItem) {	//For each Codon
			$AAbase = $tempItem->getAAbase();				//Extract Amino Acid
			if ( isset($SynonymousTotalHash[$AAbase]) ) {	//If value already exists for this AA
				$SynonymousTotalHash[$AAbase] 				//Add to value
					+= $tempItem->getOptSeqRawCount();	
			} else {										//Otherwise value not present
				$SynonymousTotalHash[$AAbase] 				//Set initial value
					= $tempItem->getOptSeqRawCount();	
			}
		}
		foreach ($this->CodonHash as $tempKey=>$tempItem) {		//For each codon
			$tempItem->setOptSeqSynonTotal(						//Set synonymous total
				$SynonymousTotalHash[ $tempItem->getAAbase() ] 	//For respective amino acid
			);
		}
	}
}
?>