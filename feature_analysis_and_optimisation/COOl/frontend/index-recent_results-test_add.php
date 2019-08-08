<?php
//This page allows you to add jobs (based on encrypt_ID), to the recent results table on the index page for testing purposes.

require_once "Controllers/CodonOpt_Controller_UserSession.php";
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";

$myController = new CodonOpt_Controller_Ancestor_User_Job(null);
CodonOpt_Controller_UserSession::AddUserJob( $myController->getEncryptID() );

echo $myController->getEncryptID()." added to recent results.";
?>
