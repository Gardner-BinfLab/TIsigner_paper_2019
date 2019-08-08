<?php


//This abstract class contains common utilities as static functions
//Most of these are for checking or processing nucleotide sequence
class CodonOpt_Utility {

	//This Function generates random strings
	private static $AlphaNumeric = array(
		"0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"
	);
	public static function GetAlphaNumeric() {
		return CodonOpt_Utility::$AlphaNumeric;
	}
	public static function GetRandomString($InputLength) {
		$IntInput = intval($InputLength);
		$max = count(CodonOpt_Utility::$AlphaNumeric)-1;
		$output = "";
		for ($num=0; $num<$IntInput; $num ++) {
			$output .= CodonOpt_Utility::$AlphaNumeric[rand(0,$max)];
		}
		return $output;
	}
	
	//Allowed Symbols for Titles
	//Must be usable for writing file names, so none of: \/:*?"<>|
	private static $AllowedTitleSymbols = array("-","_",".",",","(",")","[","]");
	public static function getAllowedTitleSymbols() {
		return CodonOpt_Utility::$AllowedTitleSymbols;
	}
	
	//Tabs Spaces And Linebreaks
	private static $TabSpacesLineBreaks = array(" ","\t","\r","\n");
	public static function GetTabSpacesLineBreaks() {
		return CodonOpt_Utility::$TabSpacesLineBreaks;
	}	
	
	//This function cleans up given input sequences.
	//It removes, spaces, tabs, linebreaks and underscores
	private static $CleanTarget = array(" ","_","\t","\r");
	public static function CleanSequence($InputSeq) {
		$OutputSeq = strtoupper($InputSeq);
		$OutputSeq = str_ireplace(
			CodonOpt_Utility::$CleanTarget,				//Remove spaces, underscore, tabs, return carriage
			"",$OutputSeq
		);		
		$OutputSeq = str_ireplace("\n","",$OutputSeq);	//Remove new lines (place AFTER return carriage)
		return $OutputSeq;
	}
	
	//Number Check Functions 
	private static $Digits = array("0","1","2","3","4","5","6","7","8","9");
	public static function getDigits() { return CodonOpt_Utility::$Digits; }
	
	//Check Valid Positive Integer (Rejects empty strings, accepts 0)
	public static function CheckValidPositiveInteger($InputStr) {
		if (strlen($InputStr) == 0) {			//If String is empty
			return false;						//Return False
		} else {								//Otherwise String has content
			$EmptyNumbers = str_ireplace(CodonOpt_Utility::$Digits,"",$InputStr);
			if (strlen($EmptyNumbers) == 0) {	//If string only has digits
				return true;					//It is a positive Integer (including 0)
			} else {							//Otherwise it has other stuff
				return false;					//It is not positive integer
			}
		}
	}
	
	//Rounds number to given significant figures
	public static function RoundToSignificantFigure($number, $sigdigs) { 
		$multiplier = 1;			//final multiplier
		;;;;;;if ($number < 0) {	//If number is negative
			$number *= -1;			//Make it positive
			$multiplier = -1;		//Final multiplier reverts number back to negative
		} elseif ($number == 0) {	//If number is zero
			return 0;				//immediately return 0
		}
		while ($number < 0.1) {
			$number *= 10; 
			$multiplier /= 10; 
		} 
		while ($number >= 1) { 
			$number /= 10; 
			$multiplier *= 10; 
		} 
		return round($number, $sigdigs) * $multiplier; 
	} 
	
	//Checks whether the input email is valid:
	public static function CheckValidEmail($InputEmail) {
		//Method 1: Regex (need to fix offset 67 before usable)
		//$testVar1 = preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}??)$^", $InputEmail);
		//return ($testVar1 == 1);
		
		//Method 2: filter_var returns the Email itself if valid, and an empty string otherwise
		$testVar2 = filter_var( $InputEmail , FILTER_VALIDATE_EMAIL );
		//print "testVar2: ".$testVar2;
		if ( strlen($testVar2) >= 3 ) {	//Need at least 3 chars
			return true;
		} else {
			return false;
		}
	}
	
	public static function getNucleotideBaseType($InputBase) {
		switch ($InputBase) {
			case "A":
			case "G":
				return "U";	//Purines
				break;
			case "C":
			case "T":
			case "U":
				return "Y";	//Pyrimidines
				break;				
			default:
				die("Unrecognized getNucleotideBaseType: ".$InputBase);
		}
	}
	
	public static function count_GC_content($inputSeq) {
		$G_count = substr_count($inputSeq,"G") + substr_count($inputSeq,"g");
		$C_count = substr_count($inputSeq,"C") + substr_count($inputSeq,"c");
		$TotalLength = strlen($inputSeq);
		return (( $G_count+$C_count) / $TotalLength);
	}
	
	public static function convertAA1to3($input1) {
		switch($input1) {
			case "A": return "Ala"; break;
			case "R": return "Arg"; break;
			case "N": return "Asn"; break;
			case "D": return "Asp"; break;
			case "C": return "Cys"; break;
			case "E": return "Glu"; break;
			case "Q": return "Gln"; break;
			case "G": return "Gly"; break;
			case "H": return "His"; break;
			case "I": return "Ile"; break;
			case "L": return "Leu"; break;
			case "K": return "Lys"; break;
			case "M": return "Met"; break;
			case "F": return "Phe"; break;
			case "P": return "Pro"; break;
			case "S": return "Ser"; break;
			case "T": return "Thr"; break;
			case "W": return "Trp"; break;
			case "Y": return "Tyr"; break;
			case "V": return "Val"; break;
			case "*": return "*St"; break;
			default:
				die("Unrecognized AA letter: ".$input1);
		}
	}
	
	#Function to check if a given Script Name is running
	public static function CheckIfScriptNameRunning($BackendPattern) {
		$Output = exec('ps -A | grep -i "'.$BackendPattern.'"');
		if ($Output) {				#If output is NOT false
			$IsMatch = preg_match("/".$BackendPattern."/",$Output);
			if ( $IsMatch ) {		#If Pattern is in output
				return true;		#Set to true
			}	#Otherwise output does not match
		}
		return false;
	}
	
	//This subfunction is meant to convert ambiguous DNA bases (T not U) into all possible non-ambiguous sequences
	//If there is more than 1 ambiguous bases, it relies on self-recursion to convert them all, one base for each recursion
	//Returns a reference to an array with all the unambiguous bases. Sample Test Code:
	//	require_once "Controllers/CodonOpt_Utility.php";
	//	echo implode( ",",CodonOpt_Utility::convertAmbigDNAToAllUnambig("ATCG") )." | ";
	//	echo implode( ",",CodonOpt_Utility::convertAmbigDNAToAllUnambig("BamTs") )." | ";
	//First we define which DNA bases are ambiguous
	private static $AmbigDNAArray = array(
			#UpperCase
			"K" => "GT"		,
			"M" => "AC"		,
			"R" => "AG"		,
			"Y" => "CT"		,
			"S" => "CG"		,
			"W" => "AT"		,
			"B" => "CGT"	,
			"V" => "ACG"	,
			"H" => "ACT"	,
			"D" => "AGT"	,
			"N" => "ATCG"	,
			#Lower Case
			"k" => "gt"		,
			"m" => "ac"		,
			"r" => "ag"		,
			"y" => "ct"		,
			"s" => "cg"		,
			"w" => "at"		,
			"b" => "cgt"	,
			"v" => "acg"	,
			"h" => "act"	,
			"d" => "agt"	,
			"n" => "atcg"
	);
	public static function convertAmbigDNAToAllUnambig($InSeq) {
		$outArrayA = array();
		$outArrayB = array();
		#First loop looks for 1st ambiguous base, and replaces it
		foreach  (CodonOpt_Utility::$AmbigDNAArray as $ambigKey => $ambigVal) {
			$tempArray = explode($ambigKey,$InSeq,2);		#Split ONLY the 1st instance of thie ambiguous letter
			if ( count($tempArray) == 2 ) {					#If Split was successful
				$tempClearList = str_split($ambigVal);		#Get the list of unambiguous letters
				foreach ($tempClearList as $clearKey) {		#Foreach unambiguous letter
					array_push(								#Add it to the output array
						$outArrayA, ($tempArray[0].$clearKey.$tempArray[1])
					);
				};
				break;										#Do this ONLY for 1st ambiguous base found
			}
		}
		if (count($outArrayA) == 0) {						#If outArrayA is empty
			array_push($outArrayB,$InSeq);					#Inseq has no ambiguous bases
			return $outArrayB;								#Return InSeq in an array
		}
		
		#Otherwise at least 1 ambiguous base was found
		foreach ($outArrayA as $tempSeqA) {					#Go through each output sequence
			foreach (										#Check for ambiguous bases
				CodonOpt_Utility::$AmbigDNAArray as $ambigKey => $ambigVal
			) {	
				$tempArray = explode($ambigKey,$tempSeqA,2);#Split ONLY the 1st instance of thie ambiguous letter
				if ( count($tempArray) == 2 ) {				#If a split was found
					$tempArrayB = 							#Recurse and pass sequence with ambiguous base
						CodonOpt_Utility::convertAmbigDNAToAllUnambig($tempSeqA);
					foreach ($tempArrayB as $tempSeqB) {	#Add array sequences
						array_push($outArrayB,$tempSeqB);	#To @outArrayB
					}
					break;									#Do this ONLY for 1st ambiguous base found
				}
			}
		}
		if (count($outArrayB) == 0) {						#If outArrayB is empty
			return $outArrayA;								#outArrayA has no ambiguous sequence so return that
		} else {											#Otherwise ambiguous sequence found
			return $outArrayB;								#Return outArrayB
		}
	}
}
?>