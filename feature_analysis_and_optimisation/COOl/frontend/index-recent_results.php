<?php
//You can test the recent results table by using:
//http://localhost/codonopt/index-recent_results-test_add.php?id=

if (! isset($RecentResultList) ) {	//If there is no current controller
	header("Location: index.php");
	exit;							//Go back to Submit New Job
}
?>
<table id="recent_result_table" class="tablesorter">
	<thead>
		<tr>
			<th>No.&nbsp;&nbsp;&nbsp;&nbsp;</th>
			<th>Link&nbsp;&nbsp;&nbsp;&nbsp;</th>
			<th>Name&nbsp;&nbsp;&nbsp;&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php {
			$MaxCountA = count($RecentResultList);
			for ($numA=0; $numA<$MaxCountA; $numA++) {
				$tempResult = $RecentResultList[$numA];
				echo "<tr>";
				echo "<td>".($numA+1)."</td>";
				$TargetURL = "viewresult.php?".CodonOpt_Controller_Ancestor_User_Job::getEncryptIDGetKey()."=".$tempResult->getEncrypt_id();
				echo "<td><a href='".$TargetURL."'>".$tempResult->getEncrypt_id()."</a></td>";
				echo "<td><div class='index_recent_results_list_name_column'>".$tempResult->getTitle()."</div></td>";
				echo "</tr>";
			}
		} ?>
	</tbody>
</table>