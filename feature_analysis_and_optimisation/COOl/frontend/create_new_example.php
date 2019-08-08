<?php
require_once "Controllers/CodonOpt_DAO_user_jobs.php";
require_once "Controllers/CodonOpt_Controller_UserSession.php";

//Generate new Example, and Redirect to there
$EncryptID = CodonOpt_DAO_user_jobs::insertNewExample();
CodonOpt_Controller_UserSession::AddUserJob($EncryptID);	//Add to user session
header( "Location: viewresult.php?id=".$EncryptID );
exit;
;
?>