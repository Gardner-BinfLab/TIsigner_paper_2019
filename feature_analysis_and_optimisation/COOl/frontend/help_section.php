<?php
/*
This page serves up a specific "help_text_???.php" section, but wrapping it in <html> and <body> tags for correct formatting. See the help.php for more details.
*/

$Section;
foreach($_GET as $tempKey => $tempValue) {
	switch ($tempKey) {
		//Basic Variables
		case "section":
			$Section = $tempValue;
			break;
	}
}

if (isset ($Section) ) {
	require "help_text_".$Section.".php";
	echo "<br/>";
	echo "<a href='help.php#".$Section."' target='_blank'>Read More...</a>";
} else {
	echo "Section not found!";
}

//Original Page Format (now redundant using AJAX div)
/*
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class="  ext-strict" style="height:100%;">
	<head>
		<?php require "commonstyle-page_scripts.php"; ?>
	</head>
	<body bgcolor="#f3f3f3" class="   ext-webkit ext-chrome" style="height:100%;">
		<div class="x-panel-mc">
		</div>
	</body>
</html>
*/
?>
