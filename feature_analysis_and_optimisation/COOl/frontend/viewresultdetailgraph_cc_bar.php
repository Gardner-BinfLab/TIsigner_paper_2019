<?php
require_once "Controllers/CodonOpt_Controller_ViewResultDetail.php";

//Initiate Controller with Translation Rules + Color Picker
$myController = new CodonOpt_Controller_ViewResultDetail(null,null,true);
$ColorPickerObject = $myController->getCc_ColorPicker();
$PageTitle = "CC Comparison Graph for ".$myController->getDetailDisplayName();

require "viewresultdetailgraph_common.php";
?>