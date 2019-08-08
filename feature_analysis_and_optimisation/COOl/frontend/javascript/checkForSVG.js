//A simple function to check if there is SVG
function checkForSVG () {
	var returnObj = "";
	if (typeof SVGRect != "undefined") {	//true if supported, false if not
		returnObj = true;					//Set return to true
	} else {								//Otherwise SVG is not supported
		returnObj = false;					//set return to false
	}
	return returnObj;
}