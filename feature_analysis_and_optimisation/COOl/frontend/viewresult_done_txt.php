<?php
require_once "Controllers/CodonOpt_Controller_ViewResultSummaryAll.php";

$Delimiter = "\t";

//Initiate Controller without Translation Rules + Color Picker
$myController = new CodonOpt_Controller_ViewResultSummaryAll(
	null,			//No job by default
	true			//Include Output Sequence
);

//Generate Headers
header(									//Force Download
	"Content-disposition: attachment; filename=".$myController->getJobDisplayTitle().".txt"
);
header("Content-Type: text/plain");		//Set output to plain text
//Print Serial Column
echo $myController->getTextCodeColumn()->getCSVHeaderTitle();
echo $Delimiter;
$RowCount = $myController->getCurrentRowCount();
//Print All Other Columns
foreach ($myController->getTableColumns() as $HashName=>$ColumnObject) {
	echo $ColumnObject->getCSVHeaderTitle();
	echo $Delimiter;
	if ( $RowCount != $ColumnObject->CountDataPoints() ) {
		die ("Row Count Does Not Match across Columns: ".$RowCount." and ".$ColumnObject->CountDataPoints()." for ".$HashName);
	}
}
echo "\n";

//Generate Rows
for ($num=0; $num<$RowCount; $num++) {
	//Print Serial Column
	echo $myController->getTextCodeColumn()->getDataPoint($num);
	echo $Delimiter;
	//Print All Other Columns			
	foreach ($myController->getTableColumns() as $HashName=>$ColumnObject) {
		echo $ColumnObject->getDataPoint($num);
		echo $Delimiter;
	}
	echo "\n";
}
?>