<?php
require_once "Controllers/CodonOpt_Controller_ViewResultDetail.php";

$myController = 			//Extract Results
	new CodonOpt_Controller_ViewResultDetail(null,null,false);
$UserInsert = 				//Extract User Insert
	$myController->getCurrentResult()->getUser_insert();
if ($UserInsert == 1) {		//If this is User Insert, then Delete
	$results = 				//Save deletion results
		CodonOpt_DAO_user_results::deleteUserDefinedSeqByEncryptDisplayId( $myController->getEncryptID() , $myController->getCurrentResult()->getDisplay_id() );
}

//Regardless of result redirect back to User Defined Sequence results page	
header( "Location: viewresult_done_userdefseq.php?".$myController::getEncryptIDGetKey()."=".$myController->getEncryptID() );
exit;							
?>

