jQuery(document).ready(
	function(){
		jQuery(".HideIfJavaScript").css("display","none");
		jQuery(".ShowIfJavaScript").css("display","inline");
	}
);
			
function AddExclusionSequence(InputSeq) {
	var ExclusionSequenceTextArea = document.getElementById("ExclusionSequence");
	var CurrentExclusionSequence = ExclusionSequenceTextArea.value;
	if (CurrentExclusionSequence == "") {
		ExclusionSequenceTextArea.value = InputSeq;
	} else {
		ExclusionSequenceTextArea.value = CurrentExclusionSequence + "," + InputSeq;
	}
}