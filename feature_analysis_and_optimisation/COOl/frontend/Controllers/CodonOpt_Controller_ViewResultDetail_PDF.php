<?php
require_once "CodonOpt_Controller_ViewResultDetail.php";
require_once "CodonOpt_SVGmaker_RadarGeneric.php";
require_once "PDFrotate.php";

/*	//Quick Reference
	//===============
$pdf->AddPage();
$pdf->SetFont('Helvetica', 'B', 10);	//Change Font
$pdf->SetFontSize(10);					//Change Font Size

//Write a string (no linebreak at end. Will automatically wrap around to next line if itis too long)
$pdf->Write(5, 'CD Collection');		
$pdf->Ln();								//Linebreak

//For Cells
SetTextColor(int r [, int g, int b])	//Change Font Color
SetFillColor(int r [, int g, int b])
Cell(float w [, float h [, string txt [, mixed border [, int ln [, string align [, boolean fill [, mixed link]]]]]]])

//For Rectangle
SetFillColor(int r [, int g, int b])
Rect(float x, float y, float w, float h [, string style]) //Use 'F' for just Fill

//For Lines
SetLineWidth(float width)
SetDrawColor(int r [, int g, int b])
Line(float x1, float y1, float x2, float y2)

//Print Floating Text
Text(float x, float y, string txt)
*/

class CodonOpt_Controller_ViewResultDetail_PDF extends CodonOpt_Controller_ViewResultDetail {
	//Constructor Takes in Job and Result
	public function CodonOpt_Controller_ViewResultDetail_PDF($InputJob,$InputResult) {
		parent::__construct($InputJob,$InputResult,true);	//Pass Job to Parent
	}
	
	//Color for Grey Rows, in alternating even/odd tables
	private static $GreyEvenRowColor = 240;
	
	//Take in PDF and fill it with details Result Output Sequence
	public function PopulatePDF_OutputSeq($InputPDF) {
		$InputPDF->SetMargins(20,20);			//Set Margins
		$InputPDF->AddPage();					//Create new page

		//Title
		$InputPDF->SetFont("Arial", "B", 15);	//Change Font
		$InputPDF->SetFillColor(255,255,255);	//Set Fill to White
		$PrintTitle = "".$this->getDetailDisplayName();
		$InputPDF->Write(7,	$PrintTitle	);		//Write Title
		$InputPDF->Ln();						//Linebreak
		$InputPDF->Ln();						//Linebreak
		$this->WriteSummary($InputPDF);			//Write Summary

		//Input Sequence As Protein
		$InputPDF->SetFont("Arial", "B", 12);				//Change Font
		$InputPDF->Write(7, "Input Sequence As Protein");	//Write Title
		$InputPDF->Ln();									//Linebreak
		$InputPDF->SetFont("Courier", "", 12);				//Change Font
		$InputPDF->Write(7,									//Write Sequence
			$this->getOutputAsProtein()
		);				
		$InputPDF->Ln();									//Linebreak
		$InputPDF->Ln();									//Linebreak


		//Optimized Nucleotide Sequence
		$InputPDF->SetFont("Arial", "B", 12);				//Change Font
		$InputPDF->Write(7, "Optimized Nucleotide Sequence");//Write Title
		$InputPDF->Ln();									//Linebreak
		$InputPDF->SetFont("Courier", "", 12);				//Change Font
		$InputPDF->Write(7,									//Write Sequence
			$this->getOutputNucleotide()
		);				
		$InputPDF->Ln();									//Linebreak
		$InputPDF->Ln();									//Linebreak


		//Output Sequence Relative Frequency Distribution
		$IC_ColorPicker = $this->getIc_ColorPicker();
		$CC_ColorPicker = $this->getCc_ColorPicker();
		if (	isset( $IC_ColorPicker ) or 
				isset( $CC_ColorPicker )
		) {
			$this->GenerateColorFrequencyDistribution($InputPDF);
		}
		$InputPDF->Ln();							//Linebreak


		//Decide whether to generate Exclusion Fitness Table
		if ( $this->showExclusionReport() ) {
			//Write Title
			$InputPDF->SetFont("Arial", "B", 12);	//Change Font
			$InputPDF->SetFillColor(255,255,255);	//Set Fill to white
			$InputPDF->SetTextColor(0,0,0);			//Change Font to Black
			$InputPDF->Write(7,
				"Exclusion Sequence Fitness (Total Exclusion Bases: ".$this->getTotalExclusionBasesFound().")"
			);										//Title
			$InputPDF->Ln();						//Linebreak	

			$ExclusionFitnessArray = $this->getExclusionFitness();
			if ( isset( $ExclusionFitnessArray ) ) {	//If there is exclusion sequence
				$this->GenerateExclusionFitnessTable(	//Generate Table
					$InputPDF
				);
				$InputPDF->Ln();							//Linebreak
			}
			$OutputNucleotideExclusion = $this->getOutputNucleotideExclusion();
			if ( isset($OutputNucleotideExclusion) ) {
				$InputPDF->SetFont("Arial", "B", 12);	//Change Font
				$InputPDF->Write(7,"Output Sequence with Exclusion Highlight");	
				$InputPDF->Ln();							//Linebreak
				$this->OutputNucleotideAndHighlightCapitalLetters( $InputPDF , $OutputNucleotideExclusion );
				$InputPDF->Ln();							//Linebreak
			}	
		}

		//Decide whether to generate Consecutive Repeats Report
		if ( $this->showRepeat_consec_report() ) {
			$InputPDF->SetFont("Arial", "B", 12);		//Change Font
			$InputPDF->Write(7,							//Write Report Title
				"Consecutive Repeat Fitness (Total Consecutive Repeat Bases: ".$this->getOutputBaseCount_repeat_consec().")"
			);
			$InputPDF->Ln();							//Linebreak
			//$InputPDF->SetFont("Arial", "B", 12);		//Change Font
			//$InputPDF->Write(7,"Repeat Settings");	//Write Settings Title
			//$InputPDF->Ln();							//Linebreak
			$InputPDF->SetFont("Arial", "", 12);		//Change Font
			if ( $this->getRepeat_consec_mode() ) {				//If 1 is used
				$InputPDF->Write(5,						//Write Settings for 1
					"• Remove Consecutive Repeats with motif length of ".
					$this->getRepeat_consec_length().
					" repeated ".
					$this->getRepeat_consec_count().
					" or more times"
				);		//Write Report Title
				$InputPDF->Ln();						//Linebreak			
			}
			$InputPDF->Ln();							//Linebreak
			$RepeatFitness = $this->getOutput_sequence_repeat_consec();
			if ( isset($RepeatFitness) ) {
				$InputPDF->SetFont("Arial", "B", 12);	//Change Font
				$InputPDF->Write(7,"Output Sequence with Consecutive Repeat Highlight");		
				$InputPDF->Ln();						//Linebreak
				$this->OutputNucleotideAndHighlightCapitalLetters( $InputPDF , $RepeatFitness );
				$InputPDF->Ln();						//Linebreak
			}
		}

		//Decide whether to generate Repeated Motif Report
		if ( $this->showRepeat_allmotif_report() ) {
			$InputPDF->SetFont("Arial", "B", 12);		//Change Font
			$InputPDF->Write(7,							//Write Report Title
				"Repeated Motif Fitness (Total Repeated Motif Bases: ".$this->getOutputBaseCount_repeat_allmotif().")"
			);
			$InputPDF->Ln();							//Linebreak
			//$InputPDF->SetFont("Arial", "B", 12);		//Change Font
			//$InputPDF->Write(7,"Repeat Settings");	//Write Settings Title
			//$InputPDF->Ln();							//Linebreak
			$InputPDF->SetFont("Arial", "", 12);		//Change Font
			if ( $this->getRepeat_allmotif_mode() ) {				//If 1 is used
				$InputPDF->Write(5,						//Write Settings for 1
					"• Remove Motifs of length ".
					$this->getRepeat_allmotif_length().
					" which are repeated ".
					$this->getRepeat_allmotif_count().
					" or more times, regardless of location"
				);		//Write Report Title
				$InputPDF->Ln();						//Linebreak			
			}
			$InputPDF->Ln();							//Linebreak
			$RepeatFitness = $this->getOutput_sequence_repeat_allmotif();
			if ( isset($RepeatFitness) ) {
				$InputPDF->SetFont("Arial", "B", 12);	//Change Font
				$InputPDF->Write(7,"Output Sequence with Repeated Motif Highlight");		
				$InputPDF->Ln();						//Linebreak
				$this->OutputNucleotideAndHighlightCapitalLetters( $InputPDF , $RepeatFitness );
				$InputPDF->Ln();						//Linebreak
			}
		}
		
		//Decide whether to generate Hidden Stop Codon
		if ( $this->showOptimizationParameter( $this->getOptimize_hidden_stop_codon() ) ) {
			$InputPDF->SetFont("Arial", "B", 12);	//Change Font, Write Title
			$InputPDF->Write(7, "Hidden Stop Codons (".$this->getOptimizationParameterType( $this->getOptimize_hidden_stop_codon() )."imize. Total Found: ".$this->getStop_codon_motifs().")");
			$InputPDF->Ln();							//Linebreak
			$OutputNucleotideHiddenStopCodon = $this->getOutputNucleotideHiddenStopCodon();
			if ( isset($OutputNucleotideHiddenStopCodon) ) {
				$InputPDF->SetFont("Arial", "B", 12);	//Change Font, Write Title
				$InputPDF->Write(7,"Output Sequence with Hidden Stop Codon Highlight");
				$InputPDF->Ln();							//Linebreak
				$this->OutputNucleotideAndHighlightCapitalLetters( $InputPDF , $OutputNucleotideHiddenStopCodon );
			} else {
				$InputPDF->SetFont("Arial", "", 12);		//Change Font, Write Report
				$InputPDF->Write(7,"No Hidden Stop Codons found.");
				$InputPDF->Ln();							//Linebreak	
			}
			$InputPDF->Ln();								//Linebreak
		}

		//Decide whether to generate Bar Charts/Radar Plots
		if ( isset($IC_ColorPicker) ) {
			//$InputPDF->SetFont("Arial", "B", 12);	//Change Font
			//$InputPDF->Write(7,						//Write Report Title
			//	"Host Vs Optimized Codon Relative Frequency Chart"
			//);
			//$InputPDF->Ln();						//Linebreak
			$this->GenerateHostVsOptimizedCodonRadarComparison($InputPDF);
			//$this->GenerateHostVsOptimizedCodonRelFreqComparison($InputPDF);
			$InputPDF->Ln();						//Linebreak	
		}
		
		//Decide whether to generate input/output nucleotide comparison
		if ( $this->isInputSeqNucelotide() ) {	//If Input sequence is nucleotide
			$this->GenerateQueryOptimizedNucleotideComparison ($InputPDF);
			$InputPDF->Ln();					//Linebreak
		}
	}
	
	//Take in PDF and fill it with details Result Species Table
	public function PopulatePDF_SpeciesTable($InputPDF,$ShowOptimizedUsageCount ) {
		$IC_ColorPicker = $this->getIc_ColorPicker();
		$CC_ColorPicker = $this->getCc_ColorPicker();
		$ShowOptimizedUsageCount 				//Ensure input is boolean
			= (bool) $ShowOptimizedUsageCount;
		$InputPDF->SetMargins(20,20);			//Set Margins
		$InputPDF->AddPage();					//Create new page
		
		//Expression Host
		$InputPDF->SetFont("Arial", "B", 12);	//Change Font
		$InputPDF->Cell(37, 7, "Expression Host: ", 0 , 0 , "L", false);
		$InputPDF->SetFont("Arial", "", 12);	//Change Font
		$InputPDF->Cell(1, 7, $this->getSpeciesName(), 0 , 0 , "L", false);
		$InputPDF->Ln();						//Linebreak
		$InputPDF->Ln();						//Linebreak
		
		//Decide whether to generate the IC color picker table
		if ( isset($IC_ColorPicker) ) {
			$this->GenerateFrequencyColorTable(
				$InputPDF,
				$this->getIc_ColorPicker(),
				"Individual Codon Frequency and Colour Table",
				"",								//Codon only (no pair)
				$ShowOptimizedUsageCount
			);
			$InputPDF->Ln();					//Linebreak
		}


		//Decide whether to generate the CC color picker table
		if ( isset($CC_ColorPicker) ) {
			$this->GenerateFrequencyColorTable(
				$InputPDF,
				$this->getCc_ColorPicker(),
				"Codon Context Frequency and Colour Table",
				"Pair",							//Codon Pair
				$ShowOptimizedUsageCount
			);
			$InputPDF->Ln();					//Linebreak
		}
	}

	
	//=========================================================
	//Other Functions which the main function call on are here:
	//=========================================================
	
	//Write Fitness Summary
	function WriteSummary($InputPDF) {	
		//Settings for this Subfunction
		$RowCounter = 1;
		$InputPDF->SetFont("Arial", "B", 12);	//Change Font
		$InputPDF->Write(7,						//Write Title
			"Summary Report"
		);
		$InputPDF->Ln();	
		$InputPDF->SetFont("Arial", "", 12);	//Change Font
		if ( $this->isInputSeqNucelotide() ) {
			$this->WriteSummaryLine($InputPDF,"Input Sequence GC content",$this->getInputSequenceGCpercent(),$RowCounter);
			$RowCounter++;
		}
		if ( $this->showGC_target() ) {
			$this->WriteSummaryLine($InputPDF,"User Specified Target GC content",$this->getGC_target(),$RowCounter);
			$RowCounter++;
		}
		$this->WriteSummaryLine($InputPDF,"Output Sequence GC content",$this->getOuputNucleotideGCpercent(),$RowCounter);
		$RowCounter++;
		if ( $this->showGC_target() ) {
			$this->WriteSummaryLine($InputPDF,"Derived GC content fitness",$this->getGc_content_fitness(),$RowCounter);
			$RowCounter++;
		}
		if ( $this->showOptimizationParameter( $this->getOptimize_ic() ) ) {
			$this->WriteSummaryLine($InputPDF,
				"Individual Codon Fitness (".$this->getOptimizationParameterType( $this->getOptimize_ic() ).")",
				$this->getIc_fitness(),$RowCounter
			);
			$RowCounter++;
		}
		if ( $this->showOptimizationParameter( $this->getOptimize_cc() ) ) {
			$this->WriteSummaryLine($InputPDF,
				"Codon Context Fitness (".$this->getOptimizationParameterType( $this->getOptimize_cc() ).")",
				$this->getCc_fitness(),$RowCounter
			);
			$RowCounter++;
		}
		if ( $this->showOptimizationParameter( $this->getOptimize_cai() ) ) {
			$this->WriteSummaryLine($InputPDF,
				"Codon Adaptation Index (".$this->getOptimizationParameterType( $this->getOptimize_cai() ).")",
				$this->getCai_fitness(),$RowCounter
			);
			$RowCounter++;
		}
		$InputPDF->Ln();	
	}
	
	
	//Write Even/odd Color filled Summary Table
	function WriteSummaryLine($InputPdf,$ColA,$ColB,$RowCounter) {
		$RowHeight = 5;
		$ColWidthA = 100;
		$ColWidthB = 25;
		if ( ($RowCounter%2)==0 ) {				//For Even rows
			$InputPdf->SetFillColor(			//Set Fill to Grey
				$this::$GreyEvenRowColor,$this::$GreyEvenRowColor,$this::$GreyEvenRowColor
			);
		}
		$InputPdf->Cell($ColWidthA, $RowHeight, $ColA, 0 , 0 , "L", true);	//Write 1st column
		$InputPdf->Cell($ColWidthB, $RowHeight, $ColB, 0 , 0 , "L", true);	//Write 2nd column
		$InputPdf->Ln();						//Linebreak
		$InputPdf->SetFillColor(255,255,255);	//Reset Fill to White
	}	

	
	//Generates the Colored Coded Codon Frequency Output Sequence
	function GenerateColorFrequencyDistribution($InputPDF) {
		$IC_ColorPicker = $this->getIc_ColorPicker();
		$CC_ColorPicker = $this->getCc_ColorPicker();
		
		//If either Color Picker is set
		//Pre-Generate Colored Codon Distribution
		$InputPDF->SetFont("Arial", "B", 12);		//Change Font
		$InputPDF->Write(7,							//Write Title
			"Output Sequence Relative Frequency Distribution"
		);				
		$InputPDF->Ln();							//Linebreak
		$InputPDF->SetFont("Arial", "", 12);		//Change Font
		{
			$tempMsg = "Note: ";					//Generate Message
			if ( isset($IC_ColorPicker) ) {
				$tempMsg .= "Colour of codons indicates the frequency for that codon, with respect to the host. ";
			}
			if ( isset($CC_ColorPicker) ) {
				$tempMsg .= "Block colour between codons indicates the frequency of the adjacent codon pairs (I.e. the codons to the left and right), with respect to the host.";
			}
			$InputPDF->Write(7,$tempMsg);			//Write Message
		}
		$InputPDF->Ln();							//Linebreak
		$InputPDF->Ln();							//Linebreak
			
		//Insert Color Scale
		$InputPDF->SetFont("Arial", "B", 12);		//Change Font
		$InputPDF->Write(7,
				"Rarely Used (0)".
				"                                                       ".
				"Frequently Used (1)"
			);
		$InputPDF->Ln();							//Linebreak
		$InputPDF->Image("images/LowToHighFrequency.png");
		$InputPDF->Ln();							//Linebreak

		//Color Intensity Sequence
		$InputPDF->SetFont("Courier", "", 12);		//Change Font

		//Settings for this Subfunction
		$RowHeight = 5;
		$CodonColWidth = 10;
		$FirstColWidth = 17;
		$CodonContextColWidth = 4;
		
		//Generate Top Row
		$InputPDF->Cell(							//Write Row 1st column
			$FirstColWidth, $RowHeight, "Number", 0 , 0 , "L", false
		);
		for ($num = 1; $num <=10; $num++) {
			$InputPDF->Cell($CodonColWidth, $RowHeight, $num, 0 , 0 , "L", false);
			if ( isset($CC_ColorPicker) ) {
				$InputPDF->Cell(					//Write empty Cell
					$CodonContextColWidth, $RowHeight, " ", 0 , 0 , "L", false
				);	
			}
		}
		$InputPDF->Ln();							//Linebreak
		
		//Generate Other Rows
		$RawCodonArray = str_split(					//Split into codons
			$this->getOutputNucleotide(),3
		);
		$CodonColorList = array();
		$ContextColorList = array();
		$PreviousCodon = "";
		foreach ($RawCodonArray as $Codon) {		//For each codon
			//Determine what Codon will be
			$codonColor="";	//Reset with each cycle
			if ( isset($IC_ColorPicker) ) {
				$codonColor = $IC_ColorPicker->getHostColorForCodon($Codon);
			} else {
				$codonColor = new CodonOpt_DTO_color(0,0,0);
			}
			
			//Determine what Codon Context will be
			$contextColor="";	//Reset with each cycle
			if ( isset($CC_ColorPicker) ) {
				$context = $PreviousCodon.$Codon;
				if (strlen($context) == 6) {
					$contextColor = $CC_ColorPicker->getHostColorForCodon($context);
				} else {
					$contextColor = new CodonOpt_DTO_color(255,255,255);
				}
			} else {
				$contextColor = new CodonOpt_DTO_color(255,255,255);
			}
			//Store current codon as previous codon for next cycle
			$PreviousCodon = $Codon;
			
			//Store output in array
			array_push($CodonColorList,$codonColor);
			array_push($ContextColorList,$contextColor);
		}
		array_push($ContextColorList, new CodonOpt_DTO_color(255,255,255) );	//Place 1 last empty cell to suppress warnings
		
		//Place Pre-generated arrays into tables
		//This lets us "see" the next codon context before the next codon is available
		$MaxSize = count($CodonColorList);
		$RowCount = 0;
		$CellCount = 0;

		$InputPDF->SetTextColor(0,0,0);					//Change Color to black
		$InputPDF->Cell($FirstColWidth, $RowHeight, (($RowCount*10)+1), 0 , 0 , "L", false);	//Write Row 1st column
		for ($num=0; $num<$MaxSize; $num++) {
			//Put everything together into a table
			$InputPDF->SetTextColor(					//Change Font Color
				$CodonColorList[$num]->getRed(),
				$CodonColorList[$num]->getGreen(),
				$CodonColorList[$num]->getBlue()
			);
			$InputPDF->SetFillColor(255 , 255, 255);	//Set Fill to White
			$InputPDF->Cell($CodonColWidth, $RowHeight, $RawCodonArray[$num], 0 , 0 , "L", false);
			
			if ( isset($CC_ColorPicker) ) {
				$InputPDF->SetFillColor(
					$ContextColorList[$num+1]->getRed(),
					$ContextColorList[$num+1]->getGreen(),
					$ContextColorList[$num+1]->getBlue()
				);	//Set Fill to Color
				$InputPDF->Cell($CodonContextColWidth, $RowHeight, " ", 0 , 0 , "L", true);	//Write empty Cell with Fill
			}
			
			$CellCount++;
			if ($CellCount == 10) {				//When cell count is 10
				$RowCount++;					//add 1 to row
				$CellCount = 0;
				$InputPDF->Ln();				//Linebreak
				$InputPDF->SetTextColor(0,0,0);	//Change Color to black
				$InputPDF->Cell($FirstColWidth, $RowHeight, (($RowCount*10)+1), 0 , 0 , "L", false);	//Write Row 1st column
			}
		};
		$InputPDF->Ln();						//Linebreak
		
		$InputPDF->SetTextColor(0,0,0);			//Change Color to black	
	}
	
	
	//Function that generates tables
	function GenerateFrequencyColorTable($InputPdf, $InputColorPicker, $Title, $ColNameB, $ShowOptimizedColumn) {
		//Settings for this subfunction
		$RowHeight = 5;
		$ColWidthA = 20;	//Amino Acid
		$ColWidthB = 25;	//Codon
		$ColWidthC = 18;	//Host Relative Frequency
		$ColWidthD = 33;	//Host Usage Count
		$ColWidthE = 27;	//Host Synonymous Total
		$ColWidthF = 19;	//Query Usage Count
		$ColWidthG = 23;	//Optimized Raw count
		$ColWidthH = 31;	//Optimized Synonymous Total
		$ColWidthI = 25;	//Optimized Relative Frequency
		$RowCounter = 1;
		
		//Write Title
		$InputPdf->SetFont("Arial", "B", 12);	//Change Font
		$InputPdf->SetFillColor(255,255,255);	//Set Fill to white
		$InputPdf->SetTextColor(0,0,0);			//Change Font to Black
		$InputPdf->Write(7,$Title);				//Title
		$InputPdf->Ln();						//Linebreak		
		
		//Write First Row line 1
		$InputPdf->SetFillColor(204,255,255);	//Set Fill to cyan
		$InputPdf->Cell($ColWidthA, $RowHeight, "Amino", 0 , 0 , "L", true);			//Write 1st column
		$InputPdf->Cell($ColWidthB, $RowHeight, "Codon", 0 , 0 , "L", true);			//Write 2nd column
		//$InputPdf->Cell($ColWidthC, $RowHeight, "Host", 0 , 0 , "L", true);			//Write 3rd column	
		//$InputPdf->Cell($ColWidthD, $RowHeight, "Host", 0 , 0 , "L", true);			//Write 4th column
		$InputPdf->Cell($ColWidthE, $RowHeight, "Host", 0 , 0 , "L", true);				//Write 5th column
		if ( $this->isInputSeqNucelotide() ) {											//If show Query
			$InputPdf->Cell($ColWidthF, $RowHeight, "Query", 0 , 0 , "L", true);		//Write 6th column
		}
		if ( $ShowOptimizedColumn ) {													//If show Optimized
			$InputPdf->Cell($ColWidthG, $RowHeight, "Optimized", 0 , 0 , "L", true);	//Write 7th column
			$InputPdf->Cell($ColWidthH, $RowHeight, "Optimized", 0 , 0 , "L", true);	//Write 8th column
			$InputPdf->Cell($ColWidthI, $RowHeight, "Optimized", 0 , 0 , "L", true);	//Write 9th column
		}
		$InputPdf->Ln();						//Linebreak
		
		//Write First Row line 2
		$InputPdf->Cell($ColWidthA, $RowHeight, "Acid", 0 , 0 , "L", true);				//Write 1st column
		$InputPdf->Cell($ColWidthB, $RowHeight, $ColNameB, 0 , 0 , "L", true);			//Write 2nd column
		//$InputPdf->Cell($ColWidthC, $RowHeight, "Usage", 0 , 0 , "L", true);			//Write 3rd column	
		//$InputPdf->Cell($ColWidthD, $RowHeight, "Synonymous", 0 , 0 , "L", true);		//Write 4th column
		$InputPdf->Cell($ColWidthE, $RowHeight, "Relative", 0 , 0 , "L", true);			//Write 5th column
		if ( $this->isInputSeqNucelotide() ) {											//If show Query
			$InputPdf->Cell($ColWidthF, $RowHeight, "Usage", 0 , 0 , "L", true);		//Write 6th column
		}
		if ( $ShowOptimizedColumn ) {													//If show Optimized
			$InputPdf->Cell($ColWidthG, $RowHeight, "Usage", 0 , 0 , "L", true);		//Write 7th column
			$InputPdf->Cell($ColWidthH, $RowHeight, "Synonymous", 0 , 0 , "L", true);	//Write 8th column
			$InputPdf->Cell($ColWidthI, $RowHeight, "Relative", 0 , 0 , "L", true);		//Write 9th column
		}
		$InputPdf->Ln();						//Linebreak
		
		//Write First Row line 3
		$InputPdf->Cell($ColWidthA, $RowHeight, "", 0 , 0 , "L", true);					//Write 1st column
		$InputPdf->Cell($ColWidthB, $RowHeight, "", 0 , 0 , "L", true);					//Write 2nd column
		//$InputPdf->Cell($ColWidthC, $RowHeight, "Count", 0 , 0 , "L", true);			//Write 3rd column	
		//$InputPdf->Cell($ColWidthD, $RowHeight, "Total", 0 , 0 , "L", true);			//Write 4th column
		$InputPdf->Cell($ColWidthE, $RowHeight, "Frequency", 0 , 0 , "L", true);		//Write 5th column
		if ( $this->isInputSeqNucelotide() ) {											//If show Query
			$InputPdf->Cell($ColWidthF, $RowHeight, "Count", 0 , 0 , "L", true);		//Write 6th column
		}
		if ( $ShowOptimizedColumn ) {													//If show Optimized
			$InputPdf->Cell($ColWidthG, $RowHeight, "Count", 0 , 0 , "L", true);		//Write 7th column
			$InputPdf->Cell($ColWidthH, $RowHeight, "Total", 0 , 0 , "L", true);		//Write 8th column
			$InputPdf->Cell($ColWidthI, $RowHeight, "Frequency", 0 , 0 , "L", true);	//Write 9th column
		}
		$InputPdf->Ln();						//Linebreak	
		
		//Write Other Rows
		$InputPdf->SetFont("Arial", "", 12);			//Change Font
		$AllLines = $InputColorPicker->getFilteredCodonHash();
		usort(											//Sort according to amino acid
			$AllLines,
			function($a, $b) {					
				return strcmp($a->getAAbase(), $b->getAAbase());
			}
		);
		foreach ($AllLines as $tempItem) {
			//Write 1st-4th column
			if ($RowCounter%2 == 0) {					//For even rows	
				$InputPdf->SetFillColor(				//Set Fill to this color
					$this::$GreyEvenRowColor , $this::$GreyEvenRowColor , $this::$GreyEvenRowColor 
				);
			} else {									//For Odd rows
				$InputPdf->SetFillColor(255,255,255);	//Set Fill to Color
			}
			$InputPdf->SetTextColor(0,0,0);				//Change Font to Black
			$InputPdf->Cell($ColWidthA, $RowHeight, $tempItem->getAAbase(), 0 , 0 , "L", true);		
			$InputPdf->Cell($ColWidthB, $RowHeight, $tempItem->getCodon(), 0 , 0 , "L", true);	
			//$InputPdf->Cell($ColWidthC, $RowHeight, $tempItem->getHostRawCount(), 0 , 0 , "L", true);	
			//$InputPdf->Cell($ColWidthD, $RowHeight, $tempItem->getHostSynonTotal(), 0 , 0 , "L", true);	
			
			//Write 5th column
			$HostColor = $tempItem->getHostColor();
			$InputPdf->SetFillColor(					//Set Fill to Color
				$HostColor->getRed(),
				$HostColor->getGreen(),
				$HostColor->getBlue()
			);
			$InputPdf->SetTextColor(255,255,255);		//Change Font to White
			$InputPdf->Cell($ColWidthE, $RowHeight, $tempItem->getHostRelFreqForDisplay(), 0 , 0 , "L", true);
			$InputPdf->SetTextColor(0,0,0);				//Change Font to Black
			if ($RowCounter%2 == 0) {					//For even rows	
				$InputPdf->SetFillColor(				//Set Fill to this color
					$this::$GreyEvenRowColor , $this::$GreyEvenRowColor , $this::$GreyEvenRowColor 
				);
			} else {									//For Odd rows
				$InputPdf->SetFillColor(255,255,255);	//Set Fill to Color
			}
			
			//Write 6th column
			if ( $this->isInputSeqNucelotide() ) {		//If show Query
				$InputPdf->Cell($ColWidthF, $RowHeight, $tempItem->getQuerySeqUsageCount(), 0 , 0 , "L", true);	
			}
			
			//For Optimized Columns
			if ( $ShowOptimizedColumn ) {				//If show Optimized
				//Write 7th Column
				$InputPdf->Cell($ColWidthG, $RowHeight, $tempItem->getOptSeqRawCount(), 0 , 0 , "L", true);
				
				//Write 8th Column
				$InputPdf->Cell($ColWidthH, $RowHeight, $tempItem->getOptSeqSynonTotal(), 0 , 0 , "L", true);
				
				//Write 9th Column
				$OptSeqColor = $tempItem->getOptSeqColor();
				$InputPdf->SetFillColor(				//Set Fill to Color
					$OptSeqColor->getRed(),
					$OptSeqColor->getGreen(),
					$OptSeqColor->getBlue()
				);
				$InputPdf->SetTextColor(255,255,255);	//Change Font to White		
				$InputPdf->Cell($ColWidthI, $RowHeight, $tempItem->getOptSeqRelFreqForDisplay(), 0 , 0 , "L", true);
				$InputPdf->SetTextColor(0,0,0);			//Change Font back to Black
			}
			
			//Write 8th Column

			
			//Finish Up
			$InputPdf->Ln();							//Linebreak
			$RowCounter++;								//Add 1 to row count
		}
	}


	//Generate Exclusion Sequence
	function GenerateExclusionFitnessTable($InputPdf) {
		//Settings for this subfunction
		$RowHeight = 5;
		$ColWidthA = 135;
		$ColWidthB = 25;
		$RowCounter = 1;
		
		//Write First Row
		$InputPdf->SetFillColor(204,255,255);	//Set Fill to cyan
		$InputPdf->Cell($ColWidthA, $RowHeight, "Exclusion Sequence", 0 , 0 , "L", true);	//Write 1st column
		$InputPdf->Cell($ColWidthB, $RowHeight, "Frequency", 0 , 0 , "L", true);			//Write 2nd column
		$InputPdf->Ln();						//Linebreak
		
		//Write Other Rows
		$InputPdf->SetFont("courier", "", 12);		//Change Font
		foreach ($this->getExclusionFitness() as $tempKey=>$tempValue) {
			if ($RowCounter%2 == 0) {					//For even rows	
				$InputPdf->SetFillColor(				//Set Fill to this color
					240,240,240
				);
			} else {									//For Odd rows
				$InputPdf->SetFillColor(255,255,255);	//Set Fill to Color
			}
			$SeqArray = str_split(						//Split into codons
				$tempKey,50
			);
			$SeqCount = $tempValue;						//Make a copy of frequency
			foreach ($SeqArray as $SeqLine) {
				$InputPdf->Cell($ColWidthA, $RowHeight, $SeqLine, 0 , 0 , "L", true);	//Write 1st column
				$InputPdf->Cell($ColWidthB, $RowHeight, $SeqCount, 0 , 0 , "L", true);	//Write 2nd column
				$InputPdf->Ln();						//Linebreak
				$SeqCount = "";							//Clear Frequency for subsequent lines
			}
			$RowCounter++;								//Add 1 to row count
		}
	}
	

	//Write Sequence and highlight bases in capital letters with yellow
	function OutputNucleotideAndHighlightCapitalLetters($InputPdf,$InputSeq) {
		$NucPerLine = 30;
		$RowHeight = 5;
		$ColWidthA = 25;
		$ColWidthB = 4;
		$InputPdf->SetFont("courier", "", 12);		//Change Font
		$InputPdf->SetFillColor(255,255,255);		//Set Fill to Color
		$LineCounter = 0;
		$tempLinesArray = str_split($InputSeq,$NucPerLine);
		foreach ($tempLinesArray as $tempLine) {
			$InputPdf->Cell($ColWidthA, $RowHeight, ($LineCounter*$NucPerLine+1), 0 , 0 , "L", true);
			$tempBaseArray = str_split( $tempLine , 1 );
			foreach ($tempBaseArray as $tempBase) {
				if ( ctype_upper($tempBase) ) {
					$InputPdf->SetFont("courier", "b", 12);		//Change Font
					$InputPdf->SetFillColor(255,255,0);			//Set Fill to Color
					$InputPdf->Cell($ColWidthB, $RowHeight, $tempBase, 0 , 0 , "L", true);
					$InputPdf->SetFont("courier", "", 12);		//Change Font
					$InputPdf->SetFillColor(255,255,255);		//Set Fill to Color					
				} else {		
					$InputPdf->Cell($ColWidthB, $RowHeight, $tempBase, 0 , 0 , "L", true);
				}
			}
			$InputPdf->Ln();						//Linebreak
			$LineCounter++;							//Add to Line Count
		}
	}

	//Function that Break Rows for Exclusion Sequence
	function GenerateQueryOptimizedNucleotideComparison ($InputPdf) {
		//Settings for this subfunction
		$RowHeight = 5;
		$InstructionColWidthA = 5;
		$InstructionColWidthB = 3;
		$InstructionColWidthC = 30;
		$TableColWidthA = 27;
		$TableCellWidth = 3;
		
		//Pre-Generate Colored Codon Distribution
		$ProteinArray = str_split(		//Split into Protein
			$this->getOutputAsProtein(),1
		);
		$InCodonArray = str_split(		//Split into codons
			$this->getInputSequence(),3
		);			
		$OutCodonArray = str_split(		//Split into codons
			$this->getOutputNucleotide(),3
		);
		//Arrays to store rows
		$ProteinRow = array("");
		$InCodonRow = array("");
		$LinkerRow = array("");
		$OutCodonRow = array("");
		
		//Write Title and notes
		$InputPdf->SetFont("Arial", "B", 12);	//Change Font
		$InputPdf->Write(7,						//Title
			"Query/Optimized Nucleotide Comparison"
		);
		$InputPdf->Ln();						//Linebreak
		$InputPdf->SetFont("Arial", "", 12);	//Change Font
		$InputPdf->Cell(						//Write 1st column
			$InstructionColWidthA, $RowHeight, "• ", 0 , 0 , "L", false
		);
		$InputPdf->Cell(						//Write 2nd+3rd column
			($InstructionColWidthB+$InstructionColWidthC), $RowHeight, 
			"This comparison is only performed for nucleotide input sequence", 
			0 , 0 , "L", false);
		$InputPdf->Ln();						//Linebreak
		$InputPdf->Cell(						//Write 1st column
			$InstructionColWidthA, $RowHeight, "• ", 0 , 0 , "L", false
		);
		$InputPdf->SetTextColor(0,0,255);		//Change Font to Blue
		$InputPdf->Cell(						//Write 2nd column
			$InstructionColWidthB, $RowHeight, "#", 0 , 0 , "L", false
		);
		$InputPdf->SetTextColor(0,0,0);			//Change Font to Black
		$InputPdf->Cell(						//Write 3rd column	
			$InstructionColWidthC, $RowHeight, " are Transversions", 0 , 0 , "L", false
		);	
		$InputPdf->Ln();						//Linebreak	
		$InputPdf->Cell(						//Write 1st column
			$InstructionColWidthA, $RowHeight, "• ", 0 , 0 , "L", false
		);
		$InputPdf->SetTextColor(255,0,0);		//Change Font to Red
		$InputPdf->Cell(						//Write 2nd column
			$InstructionColWidthB, $RowHeight, "*", 0 , 0 , "L", false
		);
		$InputPdf->SetTextColor(0,0,0);			//Change Font to Black
		$InputPdf->Cell(						//Write 3rd column	
			$InstructionColWidthC, $RowHeight, " are Transistions", 0 , 0 , "L", false
		);	
		$InputPdf->Ln();						//Linebreak		
		$InputPdf->SetFont("Courier", "", 12);	//Change Font
		
		
		{	//Sort Cells into Rows
			$CellCounter = 0;
			$RowCounter = 0;
			$MaxSize = count($ProteinArray);
			for ($CellCount=0; $CellCount<$MaxSize; $CellCount++) {
				//Generate Linker
				$Linker = $this::generateInOutNucleotideComparisonLinker($InCodonArray[$CellCount],$OutCodonArray[$CellCount]);
				
				//Define Rows to prevent warnings
				$ProteinRow[$RowCounter] .= $ProteinArray[$CellCount]."  "." ";	//Protein needs 2 extra spaces
				$InCodonRow[$RowCounter] .= $InCodonArray[$CellCount]." ";
				$LinkerRow[$RowCounter] .= $Linker." ";
				$OutCodonRow[$RowCounter] .= $OutCodonArray[$CellCount]." ";
				$CellCounter++;						//Add to cell counter
				if ($CellCounter == 10) {			//If this is last cell
					$RowCounter++;					//Go to next row
					$CellCounter = 0;				//Reset Cell counter
					$ProteinRow[$RowCounter] = "";	//Seed new row
					$InCodonRow[$RowCounter] = "";
					$LinkerRow[$RowCounter] = "";
					$OutCodonRow[$RowCounter] = "";
				}
			}
		}


		{	//Go through Each Row and convert into table
			$MaxSize = count($ProteinRow);
			for ($RowCount=0; $RowCount<$MaxSize; $RowCount++) {
				//Protein
				$InputPdf->Cell(						//Write 1st column
					$TableColWidthA, $RowHeight, (($RowCount*10)+1), 0 , 0 , "L", false
				);
				$PrintArray = str_split(				//Split into Individual Bases
					$ProteinRow[$RowCount],1
				);
				foreach ($PrintArray as $PrintCell) {	//Write Each Cellcolumn
					$InputPdf->Cell(				
						$TableCellWidth, $RowHeight, $PrintCell, 0 , 0 , "L", false
					);			
				}
				$InputPdf->Ln();						//Linebreak	
				
				//Query
				$InputPdf->Cell(						//Write 1st column
					$TableColWidthA, $RowHeight, "Query", 0 , 0 , "L", false
				);
				$PrintArray = str_split(				//Split into Individual Bases
					$InCodonRow[$RowCount],1
				);
				foreach ($PrintArray as $PrintCell) {	//Write Each Cellcolumn
					$InputPdf->Cell(				
						$TableCellWidth, $RowHeight, $PrintCell, 0 , 0 , "L", false
					);			
				}
				$InputPdf->Ln();						//Linebreak	

				//Linker
				$InputPdf->Cell(						//Write 1st column
					$TableColWidthA, $RowHeight, " ", 0 , 0 , "L", false
				);
				$PrintArray = str_split(				//Split into Individual Bases
					$LinkerRow[$RowCount],1
				);
				foreach ($PrintArray as $PrintCell) {	//Write Each Cellcolumn
					;;;;;;;if ($PrintCell == "#") {			//If cell is Hash
						$InputPdf->SetTextColor(0,0,255);	//Change Font to Blue
					} else if ($PrintCell == "*") {			//If cell is Star
						$InputPdf->SetTextColor(255,0,0);	//Change Font to Red
					}
					$InputPdf->Cell(				
						$TableCellWidth, $RowHeight, $PrintCell, 0 , 0 , "L", false
					);
					$InputPdf->SetTextColor(0,0,0);		//Change Font to Black
				}
				$InputPdf->Ln();						//Linebreak	
				
				//Optimized
				$InputPdf->Cell(						//Write 1st column
					$TableColWidthA, $RowHeight, "Optimized", 0 , 0 , "L", false
				);
				$PrintArray = str_split(				//Split into Individual Bases
					$OutCodonRow[$RowCount],1
				);
				foreach ($PrintArray as $PrintCell) {	//Write Each Cellcolumn
					$InputPdf->Cell(				
						$TableCellWidth, $RowHeight, $PrintCell, 0 , 0 , "L", false
					);			
				}
				$InputPdf->Ln();						//Linebreak	
				$InputPdf->Ln();						//Linebreak	(End of Row spacer)
			}
		}
	}
	
	//Generate Host Vs Optimized Codon Relative Frequency Comparison
	private function GenerateHostVsOptimizedCodonRelFreqComparison($InputPDF) {
		//$InputPDF->SetFillColor(255,0,0);					//Set fill color
		//$InputPDF->Cell(2, 50, "", 0, 0, 'L', false );	//Create Empty Cell
		//$tempX = $InputPDF->GetX();						//Extract Coords
		//$tempY = $InputPDF->GetY();
		//$InputPDF->Rect($tempX, $tempY, 1, 50 , 'F');		//Draw Rectangle on top of cell
		
		//Create Legend
		$InputPDF->SetFont("Courier", "", 12);					//Change Font
		$InputPDF->SetFillColor(255,0,0);
		$InputPDF->Cell(5, 5, "", 0, 0, 'L', true );			//Create Filled Cell
		$InputPDF->Cell(5, 5, "Host", 0, 0, 'L', false );		//Label
		$InputPDF->Ln();										//Linebreak
		$InputPDF->SetFillColor(0,0,255);
		$InputPDF->Cell(5, 5, "", 0, 0, 'L', true );			//Create Filled Cell
		$InputPDF->Cell(5, 5, "Optimized", 0, 0, 'L', false );	//Label
		$InputPDF->Ln();										//Linebreak	
		$InputPDF->Ln();										//Linebreak	
		
		//Generate Graph
		$InputPDF->SetFont("Courier", "", 9);					//Change Font
		$SplitLines = $this::SplitHostVsOptimizedCodonRelFreqLines();
		$BarHeight = 50;
		$BarWidth = 3;
		$TitleHeight = 4;
		$SpacerWidth = 2;
		$AxisWidth = 7;
		$YAxisXoffset = -7;
		$YAxisYoffset = 1;
		foreach ($SplitLines as $tempLine) {
			//Generate Chart axis
			$InputPDF->Cell($AxisWidth, $BarHeight, "", 0, 0, 'L', false );		//Create Empty Spacer
			$tempX = $InputPDF->GetX()-$SpacerWidth;							//Extract Coords
			$tempY = $InputPDF->GetY();
			$InputPDF->SetLineWidth(0.3);
			$InputPDF->SetDrawColor(0,0,0);
			$InputPDF->Line($tempX, $tempY, $tempX, $tempY+$BarHeight);			//Y Axis
			$tempXstart = $tempX; 												//X Axis Coords (drawn later)
			$tempYstart = $tempY+$BarHeight;
			$InputPDF->Line($tempX, $tempY, $tempX-1, $tempY);					//Tick 1.0
			$InputPDF->Text($tempX+$YAxisXoffset, $tempY+$YAxisYoffset, "1.0");
			$InputPDF->Line(													//Tick 0.5
				$tempX, $tempY+($BarHeight/2), $tempX-1, $tempY+($BarHeight/2)
			);
			$InputPDF->Text($tempX+$YAxisXoffset, $tempY+($BarHeight/2)+$YAxisYoffset, "0.5");			
			$InputPDF->Line(													//Tick 0.0
				$tempX, $tempY+$BarHeight, $tempX-1, $tempY+$BarHeight
			);
			$InputPDF->Text($tempX+$YAxisXoffset, $tempY+$BarHeight+$YAxisYoffset, "0.0");
			
			//Print Charts
			foreach ($tempLine as $tempItem) {
				//Generate Host Frequency
				$HostRelFreqHeight = $tempItem->getHostRelFreqForDisplay() * $BarHeight;
				$InputPDF->SetFillColor(255,0,0);								//Set fill color
				$InputPDF->Cell($BarWidth, $BarHeight, "", 0, 0, 'L', false );	//Create Empty Cell
				$tempX = $InputPDF->GetX() - $BarWidth;							//Extract Coords
				$tempY = $InputPDF->GetY() + $BarHeight - $HostRelFreqHeight;
				$InputPDF->Rect(												//Draw Rectangle on top of cell
					$tempX, $tempY, $BarWidth, $HostRelFreqHeight , 'F'
				);
				//Generate Optimize Sequence Frequency
				$OptSeqRelFreqHeight = $tempItem->getOptSeqRelFreqForDisplay() * $BarHeight;
				$InputPDF->SetFillColor(0,0,255);								//Set fill color
				$InputPDF->Cell($BarWidth, $BarHeight, "", 0, 0, 'L', false );	//Create Empty Cell
				$tempX = $InputPDF->GetX() - $BarWidth;							//Extract Coords
				$tempY = $InputPDF->GetY() + $BarHeight - $OptSeqRelFreqHeight;
				$InputPDF->Rect(												//Draw Rectangle on top of cell
					$tempX, $tempY, $BarWidth, $OptSeqRelFreqHeight , 'F'
				);
				$InputPDF->Cell(												//Create Empty Spacer
					$SpacerWidth, $BarHeight, "", 0, 0, 'L', false
				);
			}			
			$InputPDF->Line(													//X Axis (drawn on top of charts)
				$tempXstart , $tempYstart , $tempXstart+170 , $tempYstart
			);
			$InputPDF->Ln();						//Linebreak	
			
			//Print Codon
			$InputPDF->Cell(						//Create Axis Spacer
				$AxisWidth, $TitleHeight, "", 0, 0, 'C', false
			);				
			foreach ($tempLine as $tempItem) {
				$InputPDF->Cell(					//Codon Name
					($BarWidth*2), $TitleHeight, $tempItem->getCodon(), 0, 0, 'C', false
				);
				$InputPDF->Cell(					//Create Empty Spacer
					$SpacerWidth, $TitleHeight, "", 0, 0, 'C', false
				);
			}
			$InputPDF->Ln();						//Linebreak	
			
			//Print Amino Acid
			$PreviousAA = null;
			$CodonCount = 0;
			$InputPDF->Cell(						//Create Axis Spacer
				$AxisWidth, $TitleHeight, "", 0, 0, 'C', false
			);				
			foreach ($tempLine as $tempItem) {
				$TripletCode = CodonOpt_Utility::convertAA1to3($tempItem->getAAbase());
				if ($PreviousAA == $TripletCode) {	//If this is the same AA\
					$CodonCount++;					//Add to codon count
				} else {
					if ( isset($PreviousAA) ) {		//If this is not first codon of line
						$this->GenerateHostVsOptimizedCodonRelFreqComparison_PrintAA
							($InputPDF,$CodonCount,$PreviousAA,$BarWidth,$TitleHeight,$SpacerWidth);
						$PreviousAA = $TripletCode;	//Reset values
					} else {						//Otherwise this is first codon of line
						$PreviousAA = $TripletCode;	//Save AA
					}
				}
			}
			$this->GenerateHostVsOptimizedCodonRelFreqComparison_PrintAA
				($InputPDF,$CodonCount,$PreviousAA,$BarWidth,$TitleHeight,$SpacerWidth);			
			$InputPDF->Ln();						//Linebreak	
			$InputPDF->Ln();						//Linebreak	
		}	
		
		//$tempItem->getAAbase()
		//$tempItem->getCodon();
		//$tempItem->getHostRelFreqForDisplay();
		//$tempItem->getOptSeqRelFreqForDisplay();
	}
	
	
	//Generate the Radar Plot
	private function GenerateHostVsOptimizedCodonRadarComparison($InputPDF) {
		$InputPDF->AddPage();		//Create new page
		$SortedLines = $this->SortHostVsOptimizedCodonRelFreqLines();
		//Extract Host and Optimized, and store it in Radar
		$HostRadarData = new CodonOpt_SVGmaker_RadarData("Host","rgb(255,0,0)");
		$OptRadarData = new CodonOpt_SVGmaker_RadarData("Optimized","rgb(0,0,255)");
		foreach ($SortedLines as $tempItem) {
			$tempKey = $tempItem->getAAbase().":".$tempItem->getCodon(); 
			$HostRadarData->AddDataPoint( $tempKey , $tempItem->getHostRelFreqForDisplay() );
			$OptRadarData->AddDataPoint( $tempKey , $tempItem->getOptSeqRelFreqForDisplay() );
		}
		$RadarMaker = new CodonOpt_SVGmaker_RadarGeneric("Host vs Optimized Individual Codon Usage","Mouseover a codon for details",1);
		$RadarMaker->AddData($HostRadarData);
		$RadarMaker->AddData($OptRadarData);
		$RadarMaker->GeneratePDFAminoAcidRadar($InputPDF);
	}
	
	private function GenerateHostVsOptimizedCodonRelFreqComparison_PrintAA(
		$InputPDF,&$CodonCount,$PreviousAA,$BarWidth,$TitleHeight,$SpacerWidth
	) {
		$tempWidth = 				//Calculate Width
			(($CodonCount+1) * $BarWidth * 2) + ($CodonCount * $SpacerWidth);
		$InputPDF->SetDrawColor(127,127,127);
		$InputPDF->Cell(			//Print Amino Acid Name
			$tempWidth, $TitleHeight, $PreviousAA, 1, 0, 'C', false
		);
		$InputPDF->Cell(			//Create Empty Spacer
			$SpacerWidth, $TitleHeight, "", 0, 0, 'C', false
		);						
		$CodonCount = 0;			//Reset values
	}
}
?>