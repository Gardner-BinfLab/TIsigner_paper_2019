<?php
require_once "Controllers/CodonOpt_Controller_ViewResultDetail_PDF.php";

//Note: This page works on localhost, but on on the bioinfo1 server. I suspect that it is because the operation takes too long and is timing out on bioinfo1. The set_time_limit(0) has not helped.
//Ensure there is valid job
$mySummaryController = new CodonOpt_Controller_ViewResult_Ancestor(null,false);
$ResultsSummaryArray					//Select job details by Encrypt ID
	= CodonOpt_DAO_user_results::selectFastAndOutputByEncryptExampleId(
		$mySummaryController->getCurrentJob()->getEncrypt_id() ,
		$mySummaryController->getCurrentJob()->getExample_serial()
);
set_time_limit(0);						//Remove Time Limits
$pdf = new PDFrotate();					//Instantiate Class

//Go through each result and export to PDF
$myResultController = null;							//Hold Controller
foreach ($ResultsSummaryArray as $TempResult) {
	$myResultController = 							//Generate Controller for this Result
		new CodonOpt_Controller_ViewResultDetail_PDF( $mySummaryController->getCurrentJob() , $TempResult );
	$myResultController->PopulatePDF_OutputSeq(		//Fill with Controller
		$pdf
	);
}
$myResultController->PopulatePDF_SpeciesTable(		//Fill Species Table from last Controller
	$pdf,
	false											//Exclude Optimized Usage Count
);
$pdf->Output();									//Display this PDF
exit;
?>