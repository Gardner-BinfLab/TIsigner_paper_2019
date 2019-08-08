<?php
require_once "CodonOpt_DAO_ancestor.php";

class CodonOpt_DAO_submit_new_job_visitor_log extends CodonOpt_DAO_ancestor {
	//Internal Variables
	private static $DatabaseName = "submit_new_job_visitor_log";
	
	//Create
	public static function insertNewVisitor() {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$stmt = $dbh->prepare("INSERT INTO ".CodonOpt_DAO_submit_new_job_visitor_log::$DatabaseName." () VALUES ()");
		$stmt->execute();
	}
	
	//Statistics functions
	//Get latest serial (which is also equal to total number of page visits)
	public static function getLastestSerial() {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = "SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name = '".CodonOpt_DAO_submit_new_job_visitor_log::$DatabaseName."' AND table_schema = '".CodonOpt_DAO_ancestor::getDatabaseName()."'";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute() ) {				//Execute
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push ($results,$row["AUTO_INCREMENT"]);
			}									//Add count serial to array
		}
		if ( count($results) == 1 ) {			//If there is one row
			return ($results[0]-1);				//Return the result minus 1 (since result is for NEXT row)
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}
	
	//Get latest time (which is also equal to total number of page visits)
	public static function getLastestUpdated_on() {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = "SELECT updated_on FROM ".CodonOpt_DAO_submit_new_job_visitor_log::$DatabaseName." ORDER BY serial DESC LIMIT 1;";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute() ) {				//Execute
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push ($results,$row["updated_on"]);
			}									//Add timing to array
		}
		if ( count($results) == 1 ) {			//If there is one row
			return ($results[0]);				//Return the result
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}

	//Count number of visitors by day
	//Input '0' for today
	public static function countVisitorsByDay($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( updated_on < date_sub(now(),interval ".($IntNum+0)." day) )";
		$LowerLimit = "( updated_on > date_sub(now(),interval ".($IntNum+1)." day) )";
		return (CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsWhere($UpperLimit." AND ".$LowerLimit));
	}
	
	//Count number of visitors by week
	//Input '0' for last week, '3' for 4 weeks ago
	public static function countVisitorsByWeek($InputNum) {
		$IntNum = intval($InputNum);
		$UpperLimit = "( updated_on < date_sub(now(),interval ".(($IntNum+0)*7)." day) )";
		$LowerLimit = "( updated_on > date_sub(now(),interval ".(($IntNum+1)*7)." day) )";
		return (CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsWhere($UpperLimit." AND ".$LowerLimit));
	}
	
	//Count number of visitors for a certain year and month
	//E.g. InputYear=2014 and InputMonth=12 to get visitors for Dec 2014.
	//An alternative Group By Query would be:
	//SELECT COUNT(serial) FROM submit_new_job_visitor_log GROUP BY YEAR(updated_on), MONTH(updated_on);
	public static function countVisitorsByYearMonth($InputYear,$InputMonth) {
		$IntYear = intval($InputYear);
		$IntMonth = intval($InputMonth);
		return (CodonOpt_DAO_submit_new_job_visitor_log::countVisitorsWhere("YEAR(updated_on)=".$IntYear." AND MONTH(updated_on)=".$IntMonth));
	}
	
	//Counts Jobs which are NOT examples which meet some criteria
	private static function countVisitorsWhere($InputWhere) {
		$dbh = CodonOpt_DAO_ancestor::getDatabaseHandler();
		$Query = "SELECT COUNT(serial) FROM ".CodonOpt_DAO_submit_new_job_visitor_log::$DatabaseName." WHERE (".$InputWhere.")";
		$stmt = $dbh->prepare($Query);
		$results = array();
		if ( $stmt->execute() ) {				//Execute
			while ($row = $stmt->fetch()) {		//Go through rows one by one
				array_push ($results,$row["COUNT(serial)"]);
			}									//Add count serial to array
		}
		if ( count($results) == 1 ) {			//If there is one row
			return $results[0];					//Return the result
		} elseif ( count($results)== 0 ) {		//If there are no rows
			return null;						//return null
		} else {
			die("Error: More than 1 results found!");
		}
	}
}
?>
