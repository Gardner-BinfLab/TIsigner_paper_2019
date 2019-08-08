<?php
require_once "CodonOpt_Controller_Ancestor_User_Job.php";

/*	//Readme
	========
This object is the header for all setup pages. It is a pseudo-controller rather than a true controller, in that it runs exactly once, and adds a bunch of variables for the page to use. It checks the ID of the current job (based on the $_GET HTML URL), and retrieves that job from the database. If that job is complete, it should redirect the page to "viewresults.php". If the job does not yet exist, it should redirect the page to the index.

It leaves the current job DTO in the variable "$CurrentJob", for the appropriate controller to pick up later. This header also has to display a page title. It uses the user submitted job title if it is available, or if there is no user specified job title, it uses a generic "New Sequence" name instead.
*/
class CodonOpt_Controller_Setup_Ancestor extends CodonOpt_Controller_Ancestor_User_Job {		
	//Properties and their getters (read only, no setters)
	private $pageTitle;		//Title for this page
	public function getPageTitle() { return $this->pageTitle; }
	
	//Constructor needs these 2 variables:
	public function CodonOpt_Controller_Setup_Ancestor() {
		parent::__construct(null);				//Parent extracts job (if any)
		if (									//If this is the Submit New Job page
			$this->getCurrentPage() == $this::getSubmitNewJobPage()
		) {										//Use this title
			$this->pageTitle = "Codon Optimization: Submit New Sequence";
		} else {								//Otherwise this is NOT the index page
			//Error Check: Make sure job is not submitted
			$submitted_on = $this->getCurrentJob()->getSubmitted_on();
			if ( isset( $submitted_on ) ) {		//Check if submitted
				//Redirect to output page
				header("Location: viewresult.php?".$this::getEncryptIDGetKey()."=".$this->getEncryptID());
				exit;
			}
			$SeqTitle = "New Sequence";			//default title
			$tempTitle = "".$this->getCurrentJob()->getTitle();
			if ($tempTitle != "") {				//If there is valid title
				$SeqTitle = $tempTitle;			//use it instead of Default
			}
			$this->pageTitle = "Setup for: ".$SeqTitle;
		}
	}

	//Pages and URLS within the Setup
	private static $HeaderPageTitles = array(
		"1: Input Sequence",
		"2: Optimization Settings",
		"3: Select Genes",
		"4: Motif Settings",
		"5: Submit"
	);
	private static $HeaderPageAddress = array(
		"setup_input_sequence_edit.php",					
		"setup_optimization.php",
		"setup_select_genes.php",
		"setup_exclusion.php",
		"setup_start_run.php"
	); 
	
	public function getHeaderCode() {
		$MaxSize = count(CodonOpt_Controller_Setup_Ancestor::$HeaderPageTitles);
		$ReturnString = "<table class='middle'><tbody><tr>";
		for ($num=0; $num<$MaxSize; $num++) {
			$ReturnString .= "<td>";			//Open Cell
			if ( 
				(								//If this is Submit New job page
					($this->getCurrentPage() == $this::getSubmitNewJobPage() ) &&
					($num == 0)
				)
				||								//Or, if this is the current page
				(CodonOpt_Controller_Setup_Ancestor::$HeaderPageAddress[$num] == $this->getCurrentPage())
			) {									//Show Title in bold
				$ReturnString .= "<b>".CodonOpt_Controller_Setup_Ancestor::$HeaderPageTitles[$num]."</b>";
			} else {							//Otherwise this is not the current page
				if ( 	//If this is NOT SubmitNewJob page
					$this->getCurrentPage() != $this::getSubmitNewJobPage()
				) {		//include link
				$ReturnString .= "<a class='headerlink' href='".CodonOpt_Controller_Setup_Ancestor::$HeaderPageAddress[$num]."?".CodonOpt_Controller_Ancestor_User_Job::getEncryptIDGetKey()."=".$this->getEncryptID()."'>";
				}
				$ReturnString .= CodonOpt_Controller_Setup_Ancestor::$HeaderPageTitles[$num];
				if ( 	//If this is NOT SubmitNewJob page
					$this->getCurrentPage() != $this::getSubmitNewJobPage()
				) {		//close link
				$ReturnString .= "</a>";
				}
			}
			$ReturnString .= "</td>";			//Close Cell
			if ($num<($MaxSize-1)) {			//If this is not the last cell
				$ReturnString .= 				//Add Spacer Cell
					"<td>&nbsp;&nbsp;&rArr;&nbsp;&nbsp;</td>";
			}
		}
		$ReturnString .= "</tr></tbody></table>";
		return $ReturnString;
	}
	
	/* //Old getHeaderCode
	//Code for Formatting
	private static $HeaderCodeFront = 
	"<table style='width:100%;'>
		<tr class='bottom'>";
	private static $HeaderCodeBack = 
		"</tr>
		<tr class='microrow'>
			<td colspan='11' class='steptab_current'>
				&nbsp;
			</td>
		</tr>
	</table>";
	private static $HeaderOuterSpacer = "<td>&nbsp;</td>";			//Spacing between Tabs
	private static $HeaderInnerSpacer = "<td>&nbsp;&nbsp;</td>";	//Spacing within Tab (between border & words)
	private static $HeaderEmptyRow = "<tr class='microrow'><td colspan='3'>&nbsp;</td></tr>";
	public function getHeaderCode() {
		$MaxSize = count(CodonOpt_Controller_Setup_Ancestor::$HeaderPageTitles);
		echo CodonOpt_Controller_Setup_Ancestor::$HeaderCodeFront;					//Print Start of Table
		echo CodonOpt_Controller_Setup_Ancestor::$HeaderOuterSpacer;
		for ($num=0; $num<$MaxSize; $num++) {			//Create Header with title but no links
			echo "<td>";
			if ( 
				(
					($this->getCurrentPage() == $this::getSubmitNewJobPage() ) &&
					($num == 0)
				)
				||
				(CodonOpt_Controller_Setup_Ancestor::$HeaderPageAddress[$num] == $this->getCurrentPage())
			) {							//First title in bold
				echo "<table class='steptab_current'>";
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderEmptyRow;
				echo "<tr>".CodonOpt_Controller_Setup_Ancestor::$HeaderInnerSpacer."<td>";
				echo "<b>";
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderPageTitles[$num];
				echo "</b>";
				echo "</td>".CodonOpt_Controller_Setup_Ancestor::$HeaderInnerSpacer."</tr>";
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderEmptyRow;
				echo "</table>";
				
			} else {
				echo "<table class='steptabs'>";
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderEmptyRow;
				echo "<tr>".CodonOpt_Controller_Setup_Ancestor::$HeaderInnerSpacer."<td>";
				if ( 	//If this is NOT index page
					$this->getCurrentPage() != $this::getSubmitNewJobPage()
				) {		//include link
					echo "<a class='headerlink' href='".CodonOpt_Controller_Setup_Ancestor::$HeaderPageAddress[$num]."?".CodonOpt_Controller_Ancestor_User_Job::getEncryptIDGetKey()."=".$this->getEncryptID()."'>";
				}
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderPageTitles[$num];
				if (	//If this is NOT index page
					$this->getCurrentPage() != $this::getSubmitNewJobPage()
				) {		//close link
					echo "</a>";
				}
				echo "</td>".CodonOpt_Controller_Setup_Ancestor::$HeaderInnerSpacer."</tr>";
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderEmptyRow;
				echo "</table>";
			}
			echo "</td>";
			if ($num<($MaxSize-1)) {
				echo CodonOpt_Controller_Setup_Ancestor::$HeaderOuterSpacer;
			}
		}
		echo CodonOpt_Controller_Setup_Ancestor::$HeaderOuterSpacer;
		echo CodonOpt_Controller_Setup_Ancestor::$HeaderCodeBack;		//Print End of Table
	}
	*/
}
?>

