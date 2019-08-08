<?php 
require_once "Controllers/CodonOpt_Utility.php";
require_once "Controllers/CodonOpt_DAO_user_jobs.php";
require_once "Controllers/CodonOpt_DAO_submit_new_job_visitor_log.php";
$IsBackendRunning = CodonOpt_Utility::CheckIfScriptNameRunning("codonopt-job_m");
$AreJobsRunning = CodonOpt_Utility::CheckIfScriptNameRunning("codonopt-run_");
 ?>
<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			Visitor Count
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				Visitor Count
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<div>
					<?php
						if ($IsBackendRunning) {
							echo "The Backend is running.";
						} else {
							echo "<b>ERROR: The Backend is NOT running.</b>";
						}
					?>
				</div>
				<div>
					<?php
						if ($AreJobsRunning) {
							echo "<b>There are jobs running.</b>";
						} else {
							echo "There are currently no jobs running.</b>";
						}
					?>
				</div>
				<div>
					The latest "submit new job" visitor was on: <?php echo CodonOpt_DAO_submit_new_job_visitor_log::getLastestUpdated_on(); ?>
				</div>
				<br/>
				<table class="tablesorter">
					<thead>
						<tr>
							<th colspan='2'>Time</th>
							<th colspan='2'>Submit New Job<br/>Visitor Count</th>
							<th colspan='2'>Examples<br/>Created</th>
							<th colspan='2'>Job(s) Created<br/>but not submitted</th>
							<th colspan='2'>Job(s) Completed<br/>without error</th>
							<th colspan='2'>Job(s) Completed<br/>with errors</th>
						</tr>
					</thead>
					<?php
						for ($numA=3; $numA>=0; $numA--) {	//Go from 3 to zero
							?>
								<tr>
									<td rowspan='7'><?php echo ($numA)+1; ?> Weeks ago</td>
										<td><?php echo (($numA*7)+6)+1; ?> days ago</td>
									<td rowspan='7'><?php echo CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsByWeek($numA);?></td>
										<td><?php echo CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsByDay(($numA*7)+6);?></td>
									<td rowspan='7'><?php echo CodonOpt_DAO_user_jobs::countExamplesCreatedByWeek($numA);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countExamplesCreatedByDay(($numA*7)+6);?></td>
									<td rowspan='7'><?php echo CodonOpt_DAO_user_jobs::countJobsCreatedButNotSubmittedByWeek($numA);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countJobsCreatedButNotSubmittedByDay(($numA*7)+6);?></td>
									<td rowspan='7'><?php echo CodonOpt_DAO_user_jobs::countJobsDoneByWeek($numA);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countJobsDoneByDay(($numA*7)+6);?></td>
									<td rowspan='7'><?php echo CodonOpt_DAO_user_jobs::countErrorJobsByWeek($numA);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countErrorJobsByDay(($numA*7)+6);?></td>
								</tr>
							<?php
							for ($numB=5; $numB>=0; $numB--) {	//Go from 5 to zero
								?>
									<tr>
										<td><?php echo (($numA*7)+$numB)+1; ?> days ago</td>
										<td><?php echo CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsByDay(($numA*7)+$numB);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countExamplesCreatedByDay(($numA*7)+$numB);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countJobsCreatedButNotSubmittedByDay(($numA*7)+$numB);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countJobsDoneByDay(($numA*7)+$numB);?></td>
										<td><?php echo CodonOpt_DAO_user_jobs::countErrorJobsByDay(($numA*7)+$numB);?></td>
									</tr>
								<?php
							}
						}
					?>
					<tr>
						<td colspan='2'><b>All Time Total</b></td>
						<td colspan='2'><b><?php echo CodonOpt_DAO_submit_new_job_visitor_log::getLastestSerial();?></b></td>
						<td colspan='8'><div class="middle"><b><?php echo CodonOpt_DAO_user_jobs::getLastestSerial();?>*</b></div></td>
					</tr>
				</table>
				<br/>
				<div>*Note: This figure is the  all time total for everything: Examples created, Jobs started but not submitted AND Jobs completed (with and without errors).</div>
				<br/>
				<hr/>
				<br/>
				<div>This table shows the "Submit New Job" page visitor count by month over the past 24 months.</div>
				<br/>
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Month&nbsp;&nbsp;</th>
							<th>Year&nbsp;&nbsp;</th>
							<th>Count&nbsp;&nbsp;</th>
						</tr>
					</thead>
					<?php
					{	//Go through each month
						$CurrYear = date("Y");
						$CurrMonth = date("m");
						for ($numA=0; $numA<24; $numA++) {	//Go from 0 to 12
							if ($CurrMonth == 0) {			//If current month is zero (before Jan)
								$CurrYear--;				//Previous year
								$CurrMonth = 12;			//Set month to December
							} elseif ($CurrMonth == 13) {	//Otherwise current month is 13 (after Dec)
								$CurrYear++;				//Next Year
								$CurrMonth = 1;				//Set month to January
							}
							?>
								<tr>
									<td><?php echo $CurrMonth; ?></td>
									<td><?php echo $CurrYear; ?></td>
									<td><?php echo CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsByYearMonth($CurrYear,$CurrMonth); ?></td>
								</tr>
							<?php
							$CurrMonth--;
						}
					}
					?>
					<tr>
						<td colspan='2'><b>All Time Total</b></td>
						<td><b><?php echo CodonOpt_DAO_submit_new_job_visitor_log::getLastestSerial();?></b></td>
					</tr>
				</table>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>