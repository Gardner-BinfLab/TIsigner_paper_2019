<?php
//This Page is the Form for submitting new user defined sequences
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";
if (isset($myController) ) {		//If there is a controller
	if (							//If controller of correct class
		get_class($myController) == "CodonOpt_Controller_ViewResultSummaryUser"
	) {
	} else {						//Otherwise controller of wrong class
		die ( "Controller of wrong class: ".get_class($myController) );	
	}
} else {							//Otherwise no controller
	header( "Location: ".CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage() );
	exit;							//Redirect back to submit new job
}
?>
<form action="viewresult_done_userdefseq.php?<?php echo $myController::getEncryptIDGetKey(); ?>=<?php echo $myController->getEncryptID(); ?>" method="Post" name="userdefseq_form" >
	<table class="GeneralInputPlusErrorTotalWidth">
		<tr>
			<td colspan="3">
				<h3>Add New User Defined Nucleotide Sequence to Results</h3> 
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<span class="instructions">A valid nucleotide sequence must consist entirely of unambiguous bases (A, C, G, T, U), and the number of bases should be divisible by 3. It should code for the protein sequence which was originally specified, using the translation rules of the expression host (<?php echo $myController->getTranslationRules()->getName(); ?>). This input sequence textbox also accepts spaces and linebreaks that may be used to space out the sequence.</span>
			</td>
		</tr>			
		<tr>
			<td colspan="2"><a id="entry_start1"></a>
				<textarea id="InputSequence" name="InputSequence" rows="8" cols="57" class="GeneralInputWidth" maxlength="<?php echo $myController::getInputSequenceMaxLength(); ?>" ><?php echo htmlentities($myController->getInputSequence()); ?></textarea>
				<!-- Line breaks to pad space for error message--><br/>&nbsp;
			</td>
			<td>
				<div class="ErrorMessage_RightColumn" id="InputSequenceErrorMessage"><?php echo $myController->getInputSequenceErrorMsg(); ?></div>
			</td>
		</tr>
		<tr>
			<td>
				<h3>Enter Title (Optional):</h3>
				<!-- Line breaks to pad space for error message--><br/>
			</td>
			<td class="right">
				<input type="text" id="Title" name="Title" size="<?php echo $myController::getTitleMaxLength(); ?>" maxlength="<?php echo $myController::getTitleMaxLength(); ?>" value="<?php echo htmlentities($myController->getTitle()); ?>"/>
			</td>
			<td rowspan="2">
				<div class="ErrorMessage_RightColumn" id="TitleErrorMessage"><?php echo $myController->getTitleErrorMsg(); ?></div>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" name="posted" value="Add Sequence" class='minimalstylebutton' />
			</td>
			<td class="right">
				<input type="submit" name="save_and_return" value="Add And Return to Summary" class='minimalstylebutton' />
			</td>
			<!-- No third column, as 3rd column for row above, spans 2 rows -->
		</tr>		
	</table>
</form>