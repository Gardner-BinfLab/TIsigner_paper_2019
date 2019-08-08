#!/usr/bin/perl -w

#To check whether this script is running in linux, type: ps -A | grep "codonopt"
use warnings;
use strict;
$|++;

require "codonopt-CentralDBH.pl";

# Job batches (maximum of $maxActive jobs)
my $maxActive = 3;			#Number of active tasks
my $SleepCount = 5;			#Duration in seconds between loops
my $lastActiveCount = 0;	#Number of active jobs

#Main Function: Loop continuously
while(1) {
	my $dbh = &GetDBHandler();	#Connects to database, returning database handle
	my @jobActiveList = ();
	my @jobStartList = ();
	my $userID = "";			#User ID to temporarily store DBH query results
	
	{	#Count Active Tasks
		my $sth = $dbh->prepare("SELECT serial FROM user_jobs WHERE job_start_on IS NOT NULL AND job_end_on IS NULL")
			or die "Couldn't prepare statement: " . $dbh->errstr;
		$sth->execute;
		while($userID = $sth->fetchrow_array()) {
			push(@jobActiveList,$userID);
		}
		$sth->finish;
	}

	{	#Find List of all Pending Jobs
		my $sth = $dbh->prepare("SELECT serial FROM user_jobs WHERE submitted_on IS NOT NULL AND job_start_on IS NULL")
			or die "Couldn't prepare statement: " . $dbh->errstr;
		$sth->execute;
		while($userID = $sth->fetchrow_array()) {
			if( scalar(@jobStartList) <		#If current list of jobs to start
				($maxActive - scalar(@jobActiveList))
			) {								#is LESS than currently available free slots (Total Active - Current Active)
				push(@jobStartList,$userID);
			} else { last; }
		}
		$sth->finish;
	}
	
	# Print job update and start new jobs
	if (												#Run accounting IF:
		(scalar(@jobActiveList) != $lastActiveCount) or #Currently active jobs is different last count
		(scalar(@jobStartList) >= 1)					#OR if there are new jobs to start
	) {	
		my $currTime = $dbh->selectrow_array("SELECT NOW()");
		print "Job update on ".$currTime.":\n";
		if(scalar(@jobActiveList) >= 1) {
			print "Job in progress for IDs: ".join(",",@jobActiveList)."\n";
		} else {
			print "No currently active jobs.\n";
		}
		if(scalar(@jobStartList) >= 1) {
			my $RunJobFile = &FindLatestRunJob();		#Get Latest File Name
			print "Starting: ";
			foreach(@jobStartList) {
				my $JobID = $_;							#Get Job ID
				my $JobStr = &PadString($JobID);		#Get Padded String
				my $LogFileRun = $JobStr."-run.out";	#File to write output
				print $JobID.", ";	#Print Job ID
				my $osname = $^O;
				my $Command = "";
				if ($osname eq "linux") {
					$Command = "./".$RunJobFile;
				} else {
					$Command = "perl ".$RunJobFile;
				}
				$Command .= " ".$JobID." > ".$LogFileRun." 2>&1 &";
				print "Calling: ".$Command."\n";
				system($Command);
				sleep(3);			#Pause to prevent overload
			}  
			print "\n";
		} else {
			print "No new jobs.\n";
		}
		print "\n";					#Line Break
		$lastActiveCount = scalar(@jobActiveList) + scalar(@jobStartList);
	}

	{	#New Housekeeping functions
		#Modified for recordkeeping purposes
		#Examples and jobs created but not submitted are removed only after 1 month so that they can be tracked on visitor_count.php
		&HouseKeep	#Remove jobs created more than 1 month ago, but which have not ended
			($dbh, "serial", "user_jobs", "job_end_on IS NULL AND created_on < date_sub(now(),interval 32 day)");	
		
		&HouseKeep	#Remove which ended more than 1 month ago (including examples)
			($dbh, "serial", "user_jobs", "job_end_on < date_sub(now(),interval 32 day)");
		
		#Special Housekeep: assign "Secondary Script Timout"
		#This occurs if the job has started more than X days ago but has not ended
		my $SubStmt = $dbh->prepare("UPDATE user_jobs SET error_text = 'Secondary script Timeout', job_end_on = now() WHERE job_start_on < date_sub(now(),interval 3 day) AND job_end_on IS null")
			or die "Could not prepare statement: ".$dbh->errstr;
		$SubStmt->execute;
	}
	
	#{	#Old Housekeeping functions
	#	&HouseKeep	#Remove Examples created more than 1 day ago
	#		($dbh, "serial", "user_jobs", "example_serial IS NOT NULL AND job_start_on < date_sub(now(),interval 1 day)");
		
	#	&HouseKeep	#Remove jobs created and idling for > 1 day
	#		($dbh, "serial", "user_jobs", "submitted_on IS NULL AND created_on < date_sub(now(),interval 1 day)");
			
	#	&HouseKeep	#Remove jobs completed more than 1 month ago
	#		($dbh, "serial", "user_jobs", "job_end_on < date_sub(now(),interval 1 month)");	
		
		#Note: This last housekeep does not work as intended
		#It was originally designed to kill jobs if they ran for too long, but deleting the database entry does not stop the job from running!
	#	&HouseKeep	#Remove jobs started and not completed for within 5 days
	#		($dbh, "serial", "user_jobs", "job_end_on IS NULL AND job_start_on < date_sub(now(),interval 5 day)");
	#}
	
	$dbh->disconnect();	#Disconnect from database
	sleep($SleepCount);	#Pause between loops
}

#Subfunction to handle Housekeeping for a particular Where Phrase
#This subfunction is recycled from SynLinker. Although there is only 1 TargetDB (and 1 TargetCol) for now, we preserve this format in case more tables are added in the future
sub HouseKeep {
	my $SubDBH = shift;			#Extract Database Handler
	my $TargetCol = shift;
	my $TargetDB = shift;
	my $WherePhrase = shift;	#Extract Target Parameter
	my @HousekeepList = ();		#List of items to housekeep
	my $JobID;					#Generic Job ID
	my $SubStmt = $SubDBH->prepare("SELECT ".$TargetCol." FROM ".$TargetDB." WHERE ".$WherePhrase)
		or die "Could not prepare statement: ".$SubDBH->errstr;
	$SubStmt->execute;
	while($JobID = $SubStmt->fetchrow_array()) {
		push(@HousekeepList,$JobID);
	}
	$SubStmt->finish;
	if(scalar(@HousekeepList) >= 1) {
		$SubDBH->do("DELETE FROM ".$TargetDB." WHERE ".$WherePhrase);
		print "Housekeep ".$WherePhrase." for ".$TargetDB.": ".join(", ",@HousekeepList)."\n";
	}	
}

#This subfunction finds the latest version of codonopt-run_job-change_to_775-YYYYMMDD
sub FindLatestRunJob {
	my($tempFileName,@tempFileList);
	my $tempCriteria = "^codonopt\-run_job\-change_to_775\-";			#INPUT: Regular Expression Criteria
	#Create Empty First Value (filtered by Length Error Check)
	my $tempName = ""; 
	#print "Finding files which match Regular Expression criteria: ".$tempCriteria." ... ";
	while (defined($tempName)) {						#Until End of List
		chomp ($tempName);								#Delete Newlines (if any)
		if ($tempName ne "") {							#If it passes NOT an empty string
			if (! -d $tempName) {						#If it is NOT a directory
				if ($tempName =~ m/$tempCriteria/) {	#If it matches Regular Expression Criteria
					push(@tempFileList,$tempName);		#Copy value into TempList
				};	#End of 'If it matches Regular Expression Criteria'
			};	#End of 'If it is NOT a directory'
		};	#End of 'If it passes Length Error Check'
		$tempName = glob('*');							#Find Next Value
	};	#End of "Until End of List"
	#print "Done.\n";
	if (scalar(@tempFileList) == 1) {					#If only 1 value on list
		$tempName = $tempFileList[0];					#Filename is that item
	} elsif (scalar(@tempFileList) == 0) {				#Otherwise No File matches List
		die "Did not find anyy files which match: ".$tempCriteria
	} else {										#Otherwise More than 1 file matches
		my @SortFileList = reverse sort(@tempFileList);
		$tempName = $SortFileList[0];
	};	#End of "Otherwise More than 1 file matches"
	#print "returning: ".$tempName
	return $tempName;
}

=begin
=end
=cut
