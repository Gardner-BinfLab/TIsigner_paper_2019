<?php


abstract class CodonOpt_DAO_ancestor {
	//Connection Handler
	private static $databasename = "redacted";
	private static $connection = "mysql:host=redacted;port=redacted;";
	private static $username = "redacted";
	private static $password = "redacted";
	protected static $DatabaseHandler = null;
	
	//Function to get Database Name
	public static function getDatabaseName() {
		return CodonOpt_DAO_ancestor::$databasename;
	}
	
	//Function to get DatabaseHandler
	//private static $counter = 0;
	public static function getDatabaseHandler() {
		//CodonOpt_DAO_ancestor::$counter++;
		//echo CodonOpt_DAO_ancestor::$counter;						//display number of times this function was called
		if (! isset(CodonOpt_DAO_ancestor::$DatabaseHandler) ) {	//If no connection yet
			try {
				CodonOpt_DAO_ancestor::$DatabaseHandler = new PDO(	//Try and make connection
					CodonOpt_DAO_ancestor::$connection."dbname=".CodonOpt_DAO_ancestor::$databasename.";",
					CodonOpt_DAO_ancestor::$username,
					CodonOpt_DAO_ancestor::$password
				);
			} catch (PDOException $e) {
				//DO NOT DUMP TO SCREEN: Write details to Error Log since senstive information is inside
				echo "PDO Exception! Refer to Error Log!";
				$ErrorFile=fopen("PDO_error.log","w+") or exit ("Unable to open file!");
				echo fwrite($ErrorFile,$e->getMessage());
				fclose($ErrorFile);
				die();
			}
		}
		return CodonOpt_DAO_ancestor::$DatabaseHandler;		//Return Connection object
	}
}
?>
