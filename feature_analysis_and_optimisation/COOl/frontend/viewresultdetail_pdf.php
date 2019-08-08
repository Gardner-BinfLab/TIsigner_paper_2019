<?php
require_once "Controllers/CodonOpt_Controller_ViewResultDetail_PDF.php";

//Initiate Controller
$myController = new CodonOpt_Controller_ViewResultDetail_PDF(null,null);
$pdf = new PDFrotate();						//Instantiate Class
$myController->PopulatePDF_OutputSeq(		//Fill with Controller
	$pdf
);
$myController->PopulatePDF_SpeciesTable(	//Fill with Controller
	$pdf,
	true									//Include Optimized Usage Count
);
$pdf->Output();								//Display this PDF
exit;

?>

