var SecondsRemaining = 0;

jQuery(document).ready(
	function(){
		jQuery(".HideIfJavaScript").css("display","none");
		jQuery(".ShowIfJavaScript").css("display","inline");
		
		//Timer Function
		//enter refresh time in "minutes:seconds" Minutes should range from 0 to inifinity. Seconds should range from 0 to 59
		var limit="1:00"
		TempArray=limit.split(":");
		SecondsRemaining=TempArray[0]*60+TempArray[1]*1;
		beginrefresh();
	}
);

/*
Auto Refresh Page with Time script
By JavaScript Kit (javascriptkit.com)
Over 200+ free scripts here!
*/
function beginrefresh() {
	if (SecondsRemaining==0) {
		window.location.reload();
	}
	else{ 
		SecondsRemaining-=1;
		curmin=Math.floor(SecondsRemaining/60);
		cursec=SecondsRemaining%60;
		if (curmin!=0) {
			curtime=curmin+" minutes and "+cursec+" seconds";
		} else {
			curtime=cursec+" seconds";
		}
		console.log(curtime);
		setTimeout("beginrefresh()",1000);
	}
}