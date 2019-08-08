#!/usr/bin/perl -w
use warnings;
use strict;
$|++;

#Special: This is a test script designed to test the Codon Correlation Algorithm.
#It will calculate the Codon Correlation
#(In future, it should check for Codon Correlation flag before running the algo)
#(In future, it should also read/write Codon Correlation data to/from the database)

#General Outline of how this program works
#This program is called by "codonopt-job_manager.pl" and given a job ID to process
#	-It will start the job in the database, by updating "job_start_on"
#	-It will extract the param file for the job
#	-It will call the appropriate "co.exe" and pass it the ID to start running the job
#	-When co.exe is done it will check for a results file
#		-If there are results, it will write them back to database
#		-If there are no results, it will append "error-" to the front of the files to make them easier to find
#	-Then it will end the job in the database, by updating "job_end_on"

require "codonopt-CentralDBH.pl";

#Options
#Results Page URL
my $ResultsPageURL = "http://cool.syncti.org/viewresult.php?id=";

#Currently "co.exe" always uses unix linebreaks, even when compiled for dos
my $ParamFileLineBreak = "\012";		#unix
#my $ParamFileLineBreak = "\015\012";	#dos
#my $ParamFileLineBreak = "\015";		#mac

{	#Main Function
	my $JobNum;
	if (scalar(@ARGV) >= 1) {
		$JobNum = $ARGV[0];				
	} else {
		die "No Input Job Argument found";
	}
	&StartJobInDatabase($JobNum);		#Start the job in the database
	my $JobStr = &PadString($JobNum);
	my $ParamFile = $JobStr."_param";
	my $ResultsFile = $JobStr."_results";
	my $LogFileRun = $JobStr."-run.out";
	my $LogFileCO = $JobStr."-co.out";
	&MakeParamFile($JobNum,$ParamFile);
	my $osname = $^O;
	my $Command = "";
	if ($osname eq "linux") {
		$Command = "./co_ubuntu.exe";
	} else {
		$Command = "co_dos.exe";
	}
	$Command .= " ".$JobStr." >".$LogFileCO." 2>&1";
	print "Running: ".$Command."\n";
	system($Command);
	if(-e $ResultsFile) {	#If there is a results file
		print "Job completed for user ".$JobStr."\n";
		&ProcessCompletedJob($JobNum,$ResultsFile);
		#unlink($ParamFile);
		#unlink($ResultsFile);
		#unlink($LogFileRun);
		#unlink($LogFileCO);
	} else {				#Otherwise no results file
		print "Job failed for user ".$JobStr.", renaming files\n";
		&WriteErrorMsgToDatabase($JobNum,"general error");
		rename($ParamFile	, "error-".$ParamFile	);
		rename($LogFileRun	, "error-".$LogFileRun	);
		rename($LogFileCO	, "error-".$LogFileCO	);
		{	#Send Email to user
			my $dbh = &GetDBHandler();	#Connects to database, returning database handle
			my $userEmail = $dbh->selectrow_array("SELECT user_email FROM user_jobs WHERE serial = ".$JobNum);
			my $encryptID = $dbh->selectrow_array("SELECT encrypt_id FROM user_jobs WHERE serial = ".$JobNum);
			my $Message = "Sorry, we were unable to generate your results. For more assistance, kindly contact us and include a link to this page: ".$ResultsPageURL.$encryptID;
			&SendEmailToUser($userEmail,$Message);
			$dbh->disconnect();
		}
	}
	&EndJobInDatabase($JobNum);		#End the job in the database
}

#========================================
# Subroutines Called by the Main Function
#========================================
sub StartJobInDatabase {
	my $userID = shift;
	my $dbh = &GetDBHandler();
	$dbh->do("UPDATE user_jobs SET job_start_on = now() WHERE serial = ".$userID); # Change job status to start
	my $jobStartTime = $dbh->selectrow_array("SELECT job_start_on FROM user_jobs WHERE serial = $userID");
	print "Job for user $userID started at $jobStartTime.\n";
	$dbh->disconnect();
}

sub EndJobInDatabase {
	my $userID = shift;
	my $dbh = &GetDBHandler();
	$dbh->do("UPDATE user_jobs SET job_end_on = now() WHERE serial = ".$userID);  # Change job status to done
	my $jobEndTime = $dbh->selectrow_array("SELECT job_end_on FROM user_jobs WHERE serial = $userID");
	print "Job for user $userID ended at $jobEndTime.\n";
	$dbh->disconnect();
}

sub WriteErrorMsgToDatabase {
	my $userID = shift;
	my $ErrorMsg = shift;
	my $dbh = &GetDBHandler();
	#$dbh->do("UPDATE user_jobs SET error_text = \"".$ErrorMsg."\" WHERE serial = ".$userID);	#Insert as literal statement
	$dbh->do("UPDATE user_jobs SET error_text = ? WHERE serial = ?",undef,$ErrorMsg,$userID);	#Insert as prepared statement
	$dbh->disconnect();
}

sub SendEmailToUser {
	my $userEmail = shift;
	my $Message = shift;
	if($userEmail) {
		print "Sending e-mail to ".$userEmail." : ".$Message."\n";
		open  MAIL,"|/usr/sbin/sendmail -t";
		print MAIL "From: noreply\n"; ## don’t forget to escape the @
		print MAIL "To: ".$userEmail."\n" ;
		print MAIL "Subject: Codon Optimization On-Line (COOL)\n";
		print MAIL "\n";
		print MAIL $Message."\n";
		print MAIL "This is an automated response. Please do not reply to this email address.\n";
		close(MAIL);
	}
	return 0;
}

#===============================
# Subfunction to make Param File
#===============================
sub MakeParamFile {
	my ($userNum,$filename) = @_;
	open WriteOutFile, ">".$filename || 
		die "Unable to write to ".$filename."\n";
	binmode WriteOutFile;
	
	my %refHash = %{ &GetInputFromDB($userNum) };
	my $encryptID 			= 		$refHash{"encryptID"}			;
	my $aaSeq 				= 		$refHash{"aaSeq"}				;
	my %flagHash			= %{	$refHash{"flagHash"}			};
	my $gcTarget			= 		$refHash{"gcTarget"}			;
	my @exclSeqList 		= @{	$refHash{"exclSeqList"}			};
	my %repeatConsecNum		= %{	$refHash{"repeatConsecNum"}		};
	my %repeatAllmotifNum	= %{	$refHash{"repeatAllmotifNum"}	};
	my %c2aa				= %{	$refHash{"c2aa"}				};
	my %aa2c				= %{	$refHash{"aa2c"}				};
	my %cPair2aaPair		= %{	$refHash{"cPair2aaPair"}		};
	my %cCount_0			= %{	$refHash{"cCount_0"}			};
	my %aaCount_0			= %{	$refHash{"aaCount_0"}			};
	my %cPairCount_0		= %{	$refHash{"cPairCount_0"}		};
	my %aaPairCount_0		= %{	$refHash{"aaPairCount_0"}		};
	my %cCountRef			= %{	$refHash{"cCountRef"}			};
	my %aaCountRef			= %{	$refHash{"aaCountRef"}			};
	my %cPairCountRef		= %{	$refHash{"cPairCountRef"}		};
	my %aaPairCountRef		= %{	$refHash{"aaPairCountRef"}		};
	
	print WriteOutFile "encryptID\t".$encryptID.$ParamFileLineBreak;
	
	print WriteOutFile "aaSeq\t".$aaSeq.$ParamFileLineBreak;
	
	print WriteOutFile "flagHash\t";
	foreach my $tempKey (keys %flagHash) {
		;;;;;if ($tempKey eq "repeat_consec") {
			print WriteOutFile "Repeat:".$flagHash{$tempKey}.";";
		} elsif ($tempKey eq "repeat_allmotif") {
			print WriteOutFile "allRepeat:".$flagHash{$tempKey}.";";
		} else {
			print WriteOutFile $tempKey.":".$flagHash{$tempKey}.";";
		}
	}
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "gcTarget\t".$gcTarget.$ParamFileLineBreak;
	
	print WriteOutFile "exclSeqList\t".join(",",@exclSeqList).$ParamFileLineBreak;
	
	print WriteOutFile "repeatNum\t";
	foreach my $tempKey (keys %repeatConsecNum) {
		print WriteOutFile $tempKey.":".$repeatConsecNum{$tempKey}.";";
	}
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "allrepeatNum\t";
	foreach my $tempKey (keys %repeatAllmotifNum) {
		print WriteOutFile $tempKey.":".$repeatAllmotifNum{$tempKey}.";";
	}
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "c2aa\t";
	foreach(keys %c2aa) { print WriteOutFile "$_:$c2aa{$_};"; }
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "aa2c\t";
	foreach(keys %aa2c) { print WriteOutFile "$_:".join(",",@{$aa2c{$_}}).";"; }
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "cPair2aaPair\t";
	foreach(keys %cPair2aaPair) { print WriteOutFile "$_:$cPair2aaPair{$_};"; }
	print WriteOutFile $ParamFileLineBreak;
	
	#print WriteOutFile "cCount_0\t";
	#foreach(keys %cCount_0) { print WriteOutFile "$_:$cCount_0{$_};"; }
	#print WriteOutFile $ParamFileLineBreak;
	
	#print WriteOutFile "aaCount_0\t";
	#foreach(keys %aaCount_0) { print WriteOutFile "$_:$aaCount_0{$_};"; }
	#print WriteOutFile $ParamFileLineBreak;
	
	#print WriteOutFile "cPairCount_0\t";
	#foreach(keys %cPairCount_0) { print WriteOutFile "$_:$cPairCount_0{$_};"; }
	#print WriteOutFile $ParamFileLineBreak;
	
	#print WriteOutFile "aaPairCount_0\t";
	#foreach(keys %aaPairCount_0) { print WriteOutFile "$_:$aaPairCount_0{$_};"; }
	#print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "cCountRef\t";
	foreach(keys %cCountRef) { print WriteOutFile "$_:$cCountRef{$_};"; }
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "aaCountRef\t";
	foreach(keys %aaCountRef) { print WriteOutFile "$_:$aaCountRef{$_};"; }
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "cPairCountRef\t";
	foreach(keys %cPairCountRef) { print WriteOutFile "$_:$cPairCountRef{$_};"; }
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "aaPairCountRef\t";
	foreach(keys %aaPairCountRef) { print WriteOutFile "$_:$aaPairCountRef{$_};"; }
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "GArunParam\t";
	print WriteOutFile "0:0:0;";
	print WriteOutFile $ParamFileLineBreak;
	
	print WriteOutFile "randSeed\t";
	print WriteOutFile "0";
	print WriteOutFile $ParamFileLineBreak;
	
	close(WriteOutFile);
}

#=====================================
# Subfunctions to Read in Results File
#=====================================
sub ProcessCompletedJob {
	my ($userID,$filename) = @_;
	
	#New Function: Hash of Hashes
	my %refHash = %{ &GetInputFromDB($userID) };
	my $encryptID 			= 		$refHash{"encryptID"}			;
	my $aaSeq 				= 		$refHash{"aaSeq"}				;
	my %flagHash			= %{	$refHash{"flagHash"}			};
	my $gcTarget			= 		$refHash{"gcTarget"}			;
	my @exclSeqList 		= @{	$refHash{"exclSeqList"}			};
	my %repeatConsecNum		= %{	$refHash{"repeatConsecNum"}		};
	my %repeatAllmotifNum	= %{	$refHash{"repeatAllmotifNum"}	};
	my %c2aa				= %{	$refHash{"c2aa"}				};
	my %aa2c				= %{	$refHash{"aa2c"}				};
	my %cPair2aaPair		= %{	$refHash{"cPair2aaPair"}		};
	my %cCount_0			= %{	$refHash{"cCount_0"}			};
	my %aaCount_0			= %{	$refHash{"aaCount_0"}			};
	my %cPairCount_0		= %{	$refHash{"cPairCount_0"}		};
	my %aaPairCount_0		= %{	$refHash{"aaPairCount_0"}		};
	my %cCountRef			= %{	$refHash{"cCountRef"}			};
	my %aaCountRef			= %{	$refHash{"aaCountRef"}			};
	my %cPairCountRef		= %{	$refHash{"cPairCountRef"}		};
	my %aaPairCountRef		= %{	$refHash{"aaPairCountRef"}		};
	
	open (ReadInFile, $filename) || die;
	my $strTmp = <ReadInFile>;			#Read in first line
	my $StartOfTable = 0;				#Whether we have started reading the table
	my %HHT = ();						#Header Hash Table
	my $PreviousSeqIdent = 0;			#Previous sequence ident fallback (in case pattern match fails)
	while( defined($strTmp) ) {			#Until end of file
		chomp($strTmp);					#Remove Newline if any
		if ($strTmp ne "") {			#Ensure Line has content
			my @tempArray = split("\t",$strTmp);
			if ($StartOfTable) {		#If we have started table
				my $displayID = $tempArray[ $HHT{"Seq ident"} ];
				if ($displayID =~ m/^Seq (\d+)$/) {		#If sequence ident matches pattern
					$displayID = $1;					#Save the number
				} else {								#Otherwise sequence ident does not match pattern
					$displayID = $PreviousSeqIdent+1;	#Use Fallback
					print "WARNING: Did not recognize displayID, using PreviousSeqIdent fallback: ".$displayID."\n";
				}
				$PreviousSeqIdent = $displayID;			#Note current display ID
				my $dnaSeq = $tempArray[ $HHT{"Sequence"} ];
				my %fitHash = (
					icFit				=> 0,
					ccFit				=> 0,
					caiFit    			=> 0,
					exclSeqFit			=> 0,
					gcFit				=> 0,
					stopFit				=> 0,
					repeatConsecFit		=> 0,
					repeatAllmotifFit	=> 0,
				);
				for my $tempKey ( keys(%HHT) ) {
					;;;;;if ( $tempKey eq "IC"			) {
						$fitHash{"icFit"				} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "CC"			) {
						$fitHash{"ccFit"				} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "CAI"			) {
						$fitHash{"caiFit"				} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "ExcSeq"		) {
						$fitHash{"exclSeqFit"			} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "GC"			) {
						$fitHash{"gcFit"				} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "Hidden"		) {
						$fitHash{"stopFit"				} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "Repeat"		) {
						$fitHash{"repeatConsecFit"		} = $tempArray[ $HHT{$tempKey} ];
					} elsif ( $tempKey eq "allRepeat"	) {
						$fitHash{"repeatAllmotifFit"	} = $tempArray[ $HHT{$tempKey} ];
					#For these recognized headers, do nothing
					} elsif ( $tempKey eq "Seq ident"	) {
					} elsif ( $tempKey eq "Sequence"	) {
					} else {
						print "WARNING: Unrecognized Header element: ".$tempKey."\n";
					}
				}
				&ReturnResultsToDB($userID,$displayID,$encryptID,$dnaSeq,\%flagHash,\%fitHash,\@exclSeqList,\%repeatConsecNum,\%repeatAllmotifNum,\%c2aa,\%aa2c);
			} else {					#Otherwise we have not started table
				if (					#If this is header line
					($tempArray[0] eq "Seq ident")
				) {
					$StartOfTable = 1;	#Start of table found
					print "Start of table found: ".$strTmp."\n";
					#Populate Header Hash Table (HHT)
					my $maxCountA = scalar(@tempArray);
					for (my $numA=0; $numA<$maxCountA; $numA++) {
						$HHT{$tempArray[$numA]} = $numA;
					}
				}
			}
		}
		$strTmp = <ReadInFile>;			#Read in next line
	}
	close(ReadInFile);
	
	{	#Send Email to user
		my $dbh = &GetDBHandler();	#Connects to database, returning database handle
		my $userEmail = $dbh->selectrow_array("SELECT user_email FROM user_jobs WHERE serial = $userID");
		my $encryptID = $dbh->selectrow_array("SELECT encrypt_id FROM user_jobs WHERE serial = $userID");
		my $Message = "We have generated your results. You can retrieve them from ".$ResultsPageURL.$encryptID;
		&SendEmailToUser($userEmail,$Message);
		$dbh->disconnect();
	}
}

sub ReturnResultsToDB {
	my $userID				=   	shift()	;
	my $displayID			=   	shift()	;
	my $encryptID			=   	shift()	;
	my $optSeq				=   	shift()	;
	my %flagHash			= %{	shift()	};
	my %fitHash				= %{  	shift()	};
	my @exclSeqList			= @{	shift()	};
	my %repeatConsecNum		= %{	shift()	};
	my %repeatAllmotifNum	= %{	shift()	};
	my %c2aa				= %{	shift()	};
	my %aa2c				= %{	shift()	};

	my $icFit				= $fitHash{"icFit"};
	my $ccFit				= $fitHash{"ccFit"};
	my $caiFit				= $fitHash{"caiFit"};
	my $exclSeqFit			= $fitHash{"exclSeqFit"};
	my $gcFit				= $fitHash{"gcFit"};
	my $stopFit				= $fitHash{"stopFit"};
	my $repeatConsecFit 	= $fitHash{"repeatConsecFit"};
	my $repeatAllmotifFit	= $fitHash{"repeatAllmotifFit"};

	my @ColumnList = ();
	my @ValueList = ();
	
	# Connects to database, returning database handle
	my $dbh = &GetDBHandler();

	my $dnaOptSeq = $optSeq;
	$dnaOptSeq =~s/U/T/g;		#Substitute all U with T
	my $check = $dbh->selectrow_array(
		"SELECT output_sequence FROM user_results WHERE user_job_serial = ? AND display_id = ?",
		undef,					#Skip 2nd Arg
		$userID,				#user_job_serial
		$displayID				#display_id
	);
	if($check) {				#If row exists, update optimized sequence
		$dbh->do(
			"UPDATE user_results SET output_sequence = ? WHERE user_job_serial = ? AND display_id = ?",
			undef,				#Skip 2nd Arg
			$dnaOptSeq,			#output_sequence
			$userID,			#user_job_serial
			$displayID			#display_id
		);
	} else {					#If row does not exist, insert new optimized sequence
		$dbh->do(
			"INSERT INTO user_results(user_job_serial,display_id,output_sequence) VALUES(?,?,?)",
			undef,				#Skip 2nd Arg
			$userID,
			$displayID,
			$dnaOptSeq
		);
	}

	if($flagHash{"IC"} != 0) {
		$icFit = -abs($icFit);	#Ensure negative ICU fitness value
		#$dbh->do("UPDATE user_results SET ic_fitness = $icFit WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"ic_fitness=?");
		push(@ValueList,$icFit);

	}
	if($flagHash{"CC"} != 0) {
		$ccFit = -abs($ccFit);	#Ensure negative CC fitness value
		#$dbh->do("UPDATE user_results SET cc_fitness = $ccFit WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"cc_fitness=?");
		push(@ValueList,$ccFit);
	}
	if($flagHash{"CAI"} != 0) {
		$caiFit = abs($caiFit);	#Ensure postive CAI value
		#$dbh->do("UPDATE user_results SET cai_fitness = $caiFit WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"cai_fitness=?");
		push(@ValueList,$caiFit);
	}
	
	#New Exclusion Sequence Function
	#Changed to handle Ambiguous bases	
	if($flagHash{"ExclusionSequence"} != 0) {
		my $tempDnaSeq = lc($dnaOptSeq);
		my $totalExclusionCount = 0;					#Count instances of exclusion sequence
		my $exclFitStr = "";							#This string holds: ExclusionSeq1:Count1; ExclusionSeq2:Count2; etc
		foreach my $baseExclSeq (@exclSeqList){			#For each Exclusion Sequence
			my $baseExclSeqCount = 0;					#Counter for number of occurences
			$exclFitStr .= $baseExclSeq.":";			#Add sequence to exclFitStr
			my @tempArrayA;
			{	#Convert into list of nonambiguous sequences
				my $arrRef = &convertAmbigToAllUnambig($baseExclSeq);
				@tempArrayA = @$arrRef;
			}
			foreach my $tempSeqA (@tempArrayA) {		#For each unambiguous sequence
				for(my $i=0; $i < (length($optSeq) - length($tempSeqA)) ; $i++) {
					if(substr($optSeq,$i,length($tempSeqA)) eq $tempSeqA) {
						my $ucStrTmp = uc(substr($tempDnaSeq,$i,length($tempSeqA)));
						substr($tempDnaSeq,$i,length($tempSeqA),$ucStrTmp);
						$baseExclSeqCount++;			#Add to count for this base
						$totalExclusionCount++;			#Add to count for total
					}
				}
			}
			$exclFitStr .= $baseExclSeqCount.";";		#Store count in exclFitStr
		}
		if ($totalExclusionCount != (0-$exclSeqFit) ) {
			print "WARNING: Exclusion Sequence instance counts: ".$totalExclusionCount." vs ".$exclSeqFit."\n";
		}
		#$dbh->do("UPDATE user_results SET output_sequence_exclusion = \"$tempDnaSeq\" ,exclusion_fitness = \"$exclFitStr\" WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"output_sequence_exclusion=?");
		push(@ValueList,$tempDnaSeq);
		push(@ColumnList,"exclusion_fitness=?");
		push(@ValueList,$exclFitStr);
	}
	
	if($flagHash{"GC"} != 0) {
		#$dbh->do("UPDATE user_results SET gc_content_fitness = $gcFit WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"gc_content_fitness=?");
		push(@ValueList,$gcFit);
	}
	
	if($flagHash{"HiddenStop"} != 0) {
		my $tempDnaSeq = lc($dnaOptSeq);	#Sequence to store which bases are capitalized
		my @stopCodonList = @{$aa2c{"*"}};
		my %stopCodonHash = ();				#Convert Array into Hash
		foreach my $stopCodon (@stopCodonList) {
			$stopCodonHash{$stopCodon} = 1;
		}
		my $stopCodonCount = 0;				#Counter for number of stop codons found
		for(my $i=0; $i < (length($optSeq) - 3); $i++) {
			my $currCodon = substr($optSeq,$i,3);
			if( defined($stopCodonHash{$currCodon}) ) {
				my $ucStrTmp = uc(substr($tempDnaSeq,$i,3));
				substr($tempDnaSeq,$i,3,$ucStrTmp);
				$stopCodonCount++;
			}
		}
		$stopFit = abs($stopFit);
		if ($stopCodonCount != $stopFit) {
			print "WARNING: Stop Codon values do not match, using manually calculated value of ".$stopCodonCount." instead of ".$stopFit."\n";
		}
		#$dbh->do("UPDATE user_results SET output_sequence_hidden_stop_codon = \"$tempDnaSeq\" ,number_of_stop_codon_motifs = \"$stopCodonCount\" WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"output_sequence_hidden_stop_codon=?");
		push(@ValueList,$tempDnaSeq);
		push(@ColumnList,"number_of_stop_codon_motifs=?");
		push(@ValueList,$stopCodonCount);
	}
	
	#Test function to calculate Codon Correlation (Under Construction)
	if (0) {	#To add later: check Codon Correlation flag
		my %codonCorResult = %{ &ParseCodonCorrelation($optSeq,\%c2aa) };
		for my $tempAA ( keys(%codonCorResult) ) {
			print $tempAA." : ".$codonCorResult{$tempAA}."\n";
		}
	}
	
	#Repeats
	if($flagHash{"repeat_consec"} != 0) {
		my $tempDnaSeq = lc($dnaOptSeq);
		my $totalConsecCount = 0;
		foreach my $repConsecLen (keys %repeatConsecNum) {
			my $repConsecCount = $repeatConsecNum{$repConsecLen};
			for(my $i=0; $i < (length($optSeq) - $repConsecLen*$repConsecCount); $i++) {
				if( substr($optSeq,$i,$repConsecLen*$repConsecCount) eq substr($optSeq,$i,$repConsecLen) x $repConsecCount ) {
					my $ucStrTmp = uc(substr($tempDnaSeq,$i,$repConsecLen*$repConsecCount));
					substr($tempDnaSeq,$i,$repConsecLen*$repConsecCount,$ucStrTmp);
					$totalConsecCount++;
				}
			}
		}
		if ($totalConsecCount != 0-$repeatConsecFit) {
			print "WARNING: Repeat Consec values do not match, using manually calculated value of ".$totalConsecCount." instead of ".$repeatConsecFit."\n";
		}
		#$dbh->do("UPDATE user_results SET output_sequence_repeat_consec = \"$tempDnaSeq\" WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"output_sequence_repeat_consec=?");
		push(@ValueList,$tempDnaSeq);
	}
	
	if($flagHash{"repeat_allmotif"} != 0) {
		my @tempDnaArray = split( "" , lc($dnaOptSeq) );
		my @tempFlagArray = split( "" , lc($dnaOptSeq) );
		my $SeqLength = scalar(@tempDnaArray);
		my $totalAllmotifCount = 0;
		foreach my $repAllmotifLen (keys %repeatAllmotifNum) {
			my $repAllmotifCount = $repeatAllmotifNum{$repAllmotifLen};
			my %motifHash_all = ();		#Hash to count all motifs
			my %motifHash_flag = ();	#Hash to hold motifs that should be flagged
			#Example 10 bases would be 0-9
			#Assuming the $repAllmotifLen is 5, it would run as
			#	0-4 , 1-5 , 2-6 , 3-7 , 4-8 , 5-9
			#So highest value of numA would be: $SeqLength - $repAllmotifLen = 10 - 5 = 5
			#To reach this highest value, condition check would be: $numA<=$SeqLength-$repAllmotifLen
			for (my $numA=0; $numA<=$SeqLength-$repAllmotifLen; $numA++) {
				my $tempString = "";
				for (my $numB=0; $numB<$repAllmotifLen; $numB++) {
					$tempString .= $tempDnaArray[$numA+$numB];
				}
				if ( defined( $motifHash_all{$tempString} ) ) {		#If number already present
					$motifHash_all{$tempString}++;					#Add to number
				} else {											#Otherwise number not yet present
					$motifHash_all{$tempString} = 1;				#Define number
				}
			}
			
			#Find which motifs break limit and transfer them to %motifHash_flag
			foreach my $tempMotif ( keys(%motifHash_all) ) {					#Go through al motifs
				if ($motifHash_all{$tempMotif} >= $repAllmotifCount) {			#If count exceeds limit
					$motifHash_flag{$tempMotif} = $motifHash_all{$tempMotif};	#Store in flag hash
					$totalAllmotifCount += $motifHash_all{$tempMotif} - $repAllmotifCount + 1;
				}
			}
			
			#Go through entire sequence again and flag motifs
			for (my $numA=0; $numA<=$SeqLength-$repAllmotifLen; $numA++) {
				my $tempString = "";
				for (my $numB=0; $numB<$repAllmotifLen; $numB++) {
					$tempString .= $tempDnaArray[$numA+$numB];
				}
				if ( defined( $motifHash_flag{$tempString} ) ) {		#If motif is found in flag hash
					for (my $numB=0; $numB<$repAllmotifLen; $numB++) {	#Flag by making it upper case
						$tempFlagArray[$numA+$numB] = uc( $tempFlagArray[$numA+$numB] );
					}
				}
			}
		}
		if ($totalAllmotifCount != 0-$repeatAllmotifFit) {
			print "WARNING: Repeat Allmotif values do not match, using manually calculated value of ".$totalAllmotifCount." instead of ".$repeatAllmotifFit."\n";
		}
		#$dbh->do("UPDATE user_results SET output_sequence_repeat_allmotif = '".join("",@tempFlagArray)."' WHERE user_job_serial = \"$userID\" AND display_ID = $displayID");
		push(@ColumnList,"output_sequence_repeat_allmotif=?");
		push(@ValueList,join("",@tempFlagArray));
	}
	
	#Perform Final Update
	my $Stmt = $dbh->prepare("UPDATE user_results SET ".join(", ",@ColumnList)." WHERE user_job_serial = ? AND display_ID = ?");
	my $MaxCountA = scalar(@ValueList);
	if ($MaxCountA == scalar(@ColumnList)) {
		my $numA = 0;
		for (; $numA<$MaxCountA; $numA++) {
			$Stmt->bind_param(
				($numA+1),						#Parameter starts from '1'
				$ValueList[$numA]				#Bind Value List 
			);
		}										#When loop is over, $numA should be equal to$MaxCountA
		$Stmt->bind_param(						#Bind User ID
			($numA+1),
			$userID
		);
		$numA++;								#Increment $numA
		$Stmt->bind_param(						#Bind Display ID
			($numA+1),
			$displayID
		);
		$Stmt->execute();						#Execute statement
		#Since job has completed without issues, set error_text = null;
		$Stmt = $dbh->prepare("UPDATE user_jobs SET error_text = NULL WHERE serial = ?");
		$Stmt->execute($userID);				#Execute statement
	} else {
		die "ColumnList and ValueList are not the same size: ".join(", ",@ColumnList)." | ".join(", ",@ValueList);
	}
	$dbh->disconnect();
	return 0;
}

sub FwdTrans {
	my ($rnaSeq,$c2aaDeref) = @_;
	my %c2aa=%$c2aaDeref;
	my @list = split (/(\w{3})/, $rnaSeq);
	my $aaSeq=""; # Initialize AA sequence string
	foreach(@list) {
		if($_ =~ m/\w+/) { # Check if element is not empty
			$aaSeq .= $c2aa{$_}; # Add to AA sequence string
		}
	}
	return $aaSeq;
}

sub ParseCodonCorrelation {
	my @pCC_rawCodons = unpack('(a3)*',shift);	#Split into Codons
	my %pCC_c2aa = %{ shift() };				#Get Translation
	#Test Function to write Hash to file
	#open WriteHash,">c2aa.txt";
	#foreach my $tempKey ( keys(%pCC_c2aa) ) {
	#	print WriteHash "\"".$tempKey."\" => \"".$pCC_c2aa{$tempKey}."\",\n";
	#}
	#close WriteHash;
	#die;
	my %pCC_SortHash = ();
	my %pCC_ReturnHash = ();
	#First, sort all codons according to their aminoa acid
	foreach my $pCC_TempCodon (@pCC_rawCodons) {
		my $AA = $pCC_c2aa{$pCC_TempCodon};
		if ( defined($AA) ) {
			$pCC_SortHash{$AA} .= $pCC_TempCodon;
		} else {
			die "Could not find codon: ".$pCC_TempCodon." in: ".join(", ",keys(%pCC_c2aa));
		}
	}
	#Then count the number of changes
	foreach my $tempAA ( keys(%pCC_SortHash) ) {
		my $pCC_AllCodons = $pCC_SortHash{$tempAA};
		my @pCC_TempList = unpack('(a3)*',$pCC_AllCodons);
		my $pCC_MaxCountA = scalar(@pCC_TempList);
		my $pCC_OutStr = $pCC_TempList[0];		#Seed with first codon
		my $pCC_CorrelationCount = 0;
		for (my $numA=0; $numA<($pCC_MaxCountA-1); $numA++) {
			if ($pCC_TempList[$numA] ne $pCC_TempList[$numA+1] ) {
				$pCC_OutStr .= "#".$pCC_TempList[$numA+1]
			} else {
				$pCC_CorrelationCount++;
				$pCC_OutStr .= "=".$pCC_TempList[$numA+1]
			}
		}
		$pCC_ReturnHash{$tempAA} = $pCC_CorrelationCount."\t".($pCC_MaxCountA-1)."\t".$pCC_OutStr;
	}
	return \%pCC_ReturnHash;
}

#==============================================================================
# Support Subfunctions (called by both &MakeParamFile and &ProcessCompletedJob)
#==============================================================================
# This function reads input from DB
# Loads codon and amino acid correspondence
# Intialize all hashes and counts to zero
sub GetInputFromDB {
	my $userID = shift;
	my $aaSeq = "";
	my %flagHash = (
		"IC"				=> 0,
		"CC"				=> 0,
		"CAI"				=> 0,
		"GC"				=> 0,
		"HiddenStop"		=> 0,
		"ExclusionSequence"	=> 0,
		"repeat_consec"		=> 0,
		"repeat_allmotif"	=> 0
	);
	my %c2aa          = (); # $c2aa{"codon"} = "aa"
	my %aa2c          = (); # @{$aa2c{"aa"}} = @codon
	my %cPair2aaPair  = ();
	my $icFlag = 0;
	my $ccFlag = 0;
	
	# Connects to database, returning database handle
	my $dbh = &GetDBHandler();
	my $UserJobRowData = $dbh->selectrow_hashref(
		"SELECT * FROM `user_jobs` WHERE serial = ? LIMIT 1",
		undef,		#Skip 2nd Arg
		$userID		#serial = ?
	);
	
	# Get encrypt ID
	my $encryptID = $UserJobRowData->{encrypt_id};

	# Get input sequence for optimization
	my $protFlag = $UserJobRowData->{is_input_protein};
	my $seqTmp = $UserJobRowData->{input_sequence};
	if($protFlag == 0) { # If nucleotide input
		my %c2aaTmp = ();
		my $transCodeTmp = $UserJobRowData->{nucleotide_input_translation_rule};
		my $hashrefTmp = $dbh->selectrow_hashref(
			"SELECT * FROM translationrules WHERE ncbi_code = ?",
			undef,			#Skip 2nd Arg
			$transCodeTmp	#ncbi_code = ?
		);
		# Initialize codon to amino acid conversion hash
		my %transHashTmp = %{$hashrefTmp};
		foreach(keys %transHashTmp) {
			if($_=~m/^[A|C|G|U]{3}$/) { # Check if column name is codon
				$c2aaTmp{$_} = $transHashTmp{$_};
			}
		}
		$seqTmp =~s/T/U/g; # Change to RNA sequence
		$aaSeq = &FwdTrans($seqTmp,\%c2aaTmp);
	} else {
		$aaSeq = $seqTmp;
		#$aaSeq =~s/\W//g;		#This removes stop codon at the end if any
		#$aaSeq = $aaSeq."*";	#New Rule: Stop Codons are optional
	}

	# Check for hidden stop codon requirement
	$flagHash{"HiddenStop"} = $UserJobRowData->{optimize_hidden_stop_codon};
	
	# Check for GC content requirement
	$flagHash{"GC"} = $UserJobRowData->{optimize_gc_mode};
	my $gcTarget = $UserJobRowData->{optimize_gc_target};
	
	# Check for codon related parameters
	$flagHash{"IC"} = $UserJobRowData->{optimize_ic};
	$flagHash{"CC"} = $UserJobRowData->{optimize_cc};
	$flagHash{"CAI"} = $UserJobRowData->{optimize_cai};
	my (%cCount_0,%aaCount_0,%cPairCount_0,%aaPairCount_0) = ();
	my (%cCountRef,%aaCountRef,%cPairCountRef,%aaPairCountRef) = ();

	# Individual codon parameters are essential under all conditions
	# Check if user-defined parameters
	my $userDefSpeciesBool = $UserJobRowData->{use_custom_species};
	my $speciesCode = "";			# This variable holds the species code if NOT using user defined 
	my $transCode = "";				# This holds the translation Code serial (not the code itself)
	if($userDefSpeciesBool == 0) {	# If using inbuilt species
		$speciesCode = $UserJobRowData->{inbuilt_species_serial};
		$transCode = $dbh->selectrow_array(
			"SELECT TranslationRules_NCBI_Code FROM species WHERE serial = ?",
			undef,					# Skip 2nd Arg
			$speciesCode
		);
	} else { 						# If using custom species
		$transCode = $UserJobRowData->{TranslationRules_NCBI_code};
	}
	# Initialize hashes
	my $hashref = $dbh->selectrow_hashref(
		"SELECT * FROM translationrules WHERE ncbi_code = ?",
		undef,						# Skip 2nd Arg
		$transCode					# ncbi_code = ?
	);
	# Initialize codon to amino acid conversion hash
	my %transHash = %{$hashref};
	foreach(keys %transHash) {
		if($_=~m/^[A|C|G|U]{3}$/) { # Check if column name is codon
			$c2aa{$_} = $transHash{$_};
			$cCount_0{$_} = 0;
		}
	}
	# Initialize amino acid to codon conversion hash
	foreach(keys %c2aa) {
		my $cTmp = $_;
		my $aaTmp = $c2aa{$cTmp};
		if(defined $aa2c{$aaTmp}) {
			push(@{$aa2c{$aaTmp}},$cTmp);
		} else {
			$aa2c{$aaTmp} = [($cTmp)];
			$aaCount_0{$aaTmp} = 0;
		}
	}

	# Initialize codon frequency hashes if required
	if(($flagHash{"IC"} + $flagHash{"CC"} + $flagHash{"CAI"}) != 0)
	{
		# Initialize codon pair to amino acid pair conversion hash
		foreach(keys %c2aa) {
			my $cTmp1 = $_;
			my $aaTmp1 = $c2aa{$cTmp1};
			if($aaTmp1 ne "*") {
				foreach(keys %c2aa) {
					my $cTmp2 = $_;
					my $aaTmp2 = $c2aa{$cTmp2};
					$cPair2aaPair{$cTmp1.$cTmp2} = $aaTmp1.$aaTmp2;
					$cPairCount_0{$cTmp1.$cTmp2} = 0;
					$aaPairCount_0{$aaTmp1.$aaTmp2} = 0;
				}
			}
		}

		%cCountRef = %cCount_0;
		%aaCountRef = %aaCount_0;
		%cPairCountRef = %cPairCount_0;
		%aaPairCountRef = %aaPairCount_0;
		# Calculate reference ic and cc frequencies and update database
		if($userDefSpeciesBool == 0) # If using inbuilt species
		{
			my $gListStr = $UserJobRowData->{inbuilt_species_gene_list};
			my @gListTmp = split(/\s*\,\s*/,$gListStr);
			foreach my $gTmp (@gListTmp) # loop gene list and compile IC/CC data
			{
				my $GenesRowData = $dbh->selectrow_hashref(
					"SELECT * FROM genes WHERE access_id = ?",
					undef,	#Skip 2nd Arg
					$gTmp	#access_id = ?
				);
				if($flagHash{"IC"} || $flagHash{"CAI"})
				{
					my $icFreqStr = $GenesRowData->{ic_frequency};
					my (@lTmp1,@lTmp2) = ();
					@lTmp1 = split(/\;/,$icFreqStr);
					foreach(@lTmp1) {
						@lTmp2 = split(/\:/,$_);
						$lTmp2[0] =~s/T/U/g; # Change to RNA sequence
						$cCountRef{$lTmp2[0]} += $lTmp2[1];
						my $aaTmp = $c2aa{$lTmp2[0]};
						$aaCountRef{$aaTmp} += $lTmp2[1];
					}
				}
				if($flagHash{"CC"})
				{
					my $ccFreqStr = $GenesRowData->{cc_frequency};
					my (@lTmp1,@lTmp2) = ();
					@lTmp1 = split(/\;/,$ccFreqStr);
					foreach(@lTmp1) {
						@lTmp2 = split(/\:/,$_);
						$lTmp2[0] =~s/T/U/g; # Change to RNA sequence
						$cPairCountRef{$lTmp2[0]} += $lTmp2[1];
						my $aaPairTmp = $cPair2aaPair{$lTmp2[0]};
						$aaPairCountRef{$aaPairTmp} += $lTmp2[1]; 
					}
				}			
			}
			# Update database
			if($flagHash{"IC"} || $flagHash{"CAI"})
			{
				my $freqStr="";
				foreach(keys %cCountRef) { $freqStr.="$_:$cCountRef{$_};"; }
				$dbh->do("UPDATE user_jobs SET ic_frequency = \'$freqStr\' WHERE serial = $userID");
			}
			if($flagHash{"CC"})
			{
				my $freqStr="";
				foreach(keys %cPairCountRef) { $freqStr.="$_:$cPairCountRef{$_};"; }
				$dbh->do("UPDATE user_jobs SET cc_frequency = \'$freqStr\' WHERE serial = $userID");
			}
		}
		else # if user defined frequencies, just extract from database
		{
			if($flagHash{"IC"} || $flagHash{"CAI"})
			{
				my $icFreqString = $UserJobRowData->{ic_frequency};
				my @lTmp1 = split(/\;/,$icFreqString);
				foreach(@lTmp1) {
					my @lTmp2 = split(/\:/,$_);
					$lTmp2[0] =~s/T/U/g;	# Change to RNA sequence
					$cCountRef{$lTmp2[0]} = $lTmp2[1];
					my $aaTmp = $c2aa{$lTmp2[0]};
					$aaCountRef{$aaTmp} += $lTmp2[1];
				}
			}
			if($flagHash{"CC"})
			{
				my $ccFreqString = $UserJobRowData->{cc_frequency};
				my @lTmp1 = split(/\;/,$ccFreqString);
				foreach(@lTmp1) {
					my @lTmp2 = split(/\:/,$_);
					$lTmp2[0] =~s/T/U/g;	# Change to RNA sequence
					if(not defined $cPair2aaPair{$lTmp2[0]}) {
						print "Error: codon pair $lTmp2[0] is not defined!\n";
					}else{
						$cPairCountRef{$lTmp2[0]} = $lTmp2[1];
						my $aaPairTmp = $cPair2aaPair{$lTmp2[0]};
						$aaPairCountRef{$aaPairTmp} += $lTmp2[1]; 
					}
				}
			}
		}
	}# End of codon hashes initialization
	
	# Check for exclusion sequences input
	my @exclSeqList = ();
	my $exclSeqString = "";
	$exclSeqString = $UserJobRowData->{exclusion_sequence}; 
	if($exclSeqString) {
		$flagHash{"ExclusionSequence"} = 1;
		$exclSeqString =~s/T/U/g; # convert to RNA sequence
		my @lTmp = split(/\,+/,$exclSeqString);
		foreach(@lTmp) {
			my $strTmp = $_;
			if($strTmp) {
				$strTmp =~s/\W//g; # remove non-alphanumeric
				push(@exclSeqList,$strTmp);
			}
		}
	}
	
	# Check for consecutive repeat input
	$flagHash{"repeat_consec"} = $UserJobRowData->{repeat_consec_mode};
	my %repeatConsecNum = ();		# $repeatConsecNum{"repeatConsecLength"} = "Min no. of repeat";
	if($flagHash{"repeat_consec"} != 0) {
		my $repConsecLength = $UserJobRowData->{repeat_consec_length};
		my $repConsecCount = $UserJobRowData->{repeat_consec_count};
		$repeatConsecNum{$repConsecLength} = $repConsecCount;
	}
	
	# Check for consecutive repeat input
	$flagHash{"repeat_allmotif"} = $UserJobRowData->{repeat_allmotif_mode};
	my %repeatAllmotifNum = ();		# $repeatAllmotifNum{"repeatAllmotifLength"} = "Min no. of repeat";
	if($flagHash{"repeat_allmotif"} != 0) {
		my $repAllmotifLength = $UserJobRowData->{repeat_allmotif_length};
		my $repAllmotifCount = $UserJobRowData->{repeat_allmotif_count};
		$repeatAllmotifNum{$repAllmotifLength} = $repAllmotifCount;
	}
	
	# Disconnect from database
	$dbh->disconnect();

	#New Return: Hash of Hashes
	my %returnHash = ();
	$returnHash{"encryptID"}			= $encryptID;
	$returnHash{"aaSeq"}				= $aaSeq;
	$returnHash{"flagHash"} 			= \%flagHash;
	$returnHash{"gcTarget"}				= $gcTarget;
	$returnHash{"exclSeqList"}			= \@exclSeqList;
	$returnHash{"repeatConsecNum"}		= \%repeatConsecNum;
	$returnHash{"repeatAllmotifNum"}	= \%repeatAllmotifNum;
	$returnHash{"c2aa"}					= \%c2aa;
	$returnHash{"aa2c"}					= \%aa2c;
	$returnHash{"cPair2aaPair"}			= \%cPair2aaPair;
	$returnHash{"cCount_0"}				= \%cCount_0;
	$returnHash{"aaCount_0"}			= \%aaCount_0;
	$returnHash{"cPairCount_0"}			= \%cPairCount_0;
	$returnHash{"aaPairCount_0"}		= \%aaPairCount_0;
	$returnHash{"cCountRef"}			= \%cCountRef;
	$returnHash{"aaCountRef"}			= \%aaCountRef;
	$returnHash{"cPairCountRef"}		= \%cPairCountRef;
	$returnHash{"aaPairCountRef"}		= \%aaPairCountRef;
	return \%returnHash;
}

#This subfunction is meant to convert ambiguous bases into all possible non-ambiguous sequences
#If there is more than 1 ambiguous bases, it relies on self-recursion to convert them all, one base for each recursion
#Returns a reference to an array with all the unambiguous bases. Sample Usage:
#	$arrRef = &convertAmbigToAllUnambig("KAMTRGYCN");
#	@tempArrayA = @$arrRef;
#	print scalar(@tempArrayA)." : ".join(", ",@tempArrayA)."\n";
sub convertAmbigToAllUnambig {
	my $InSeq = shift;
	my @outArrayA = ();
	my @outArrayB = ();
	
	#First Define Ambiguous Bases
	my %AmbigBaseHash = (
		#UpperCase
		"K",	"GU"	,
		"M",	"AC"	,
		"R",	"AG"	,
		"Y",	"CU"	,
		"S",	"CG"	,
		"W",	"AU"	,
		"B",	"CGU"	,
		"V",	"ACG"	,
		"H",	"ACU"	,
		"D",	"AGU"	,
		"N",	"AUCG"	,
		#Lower Case
		"k",	"gu"	,
		"m",	"ac"	,
		"r",	"ag"	,
		"y",	"cu"	,
		"s",	"cg"	,
		"w",	"au"	,
		"b",	"cgu"	,
		"v",	"acg"	,
		"h",	"acu"	,
		"d",	"agu"	,
		"n",	"aucg"
	);
	
	#First loop looks for 1st ambiguous base, and replaces it
	AMBIGLOOP1: foreach my $ambigKey (keys %AmbigBaseHash) {
		if ($InSeq =~ m/$ambigKey/) {
			my ($before,$after) = split($ambigKey,$InSeq,2);		#Split ONLY the 1st instance of thie ambiguous letter
			my @tempList = split("",$AmbigBaseHash{$ambigKey});		#Get the list of unambiguous letters
			foreach my $clearKey (@tempList) {						#Foreach unambiguous letter
				push(@outArrayA, ($before.$clearKey.$after) );		#Add it to the output array
			};
			last AMBIGLOOP1;										#Do this ONLY for 1st ambiguous base found
		}
	}
	if (scalar(@outArrayA) == 0) {									#If outArrayA is empty
		push(@outArrayB,$InSeq);									#Inseq has no ambiguous bases
		return \@outArrayB;											#Return InSeq in an array
	}
	
	#Otherwise at least 1 ambiguous base was found
	foreach my $tempSeqA (@outArrayA) {								#Go through each output sequence
		AMBIGLOOP2: foreach my $ambigKey (keys %AmbigBaseHash) {	#Check for ambiguous bases
			if ($tempSeqA =~ m/$ambigKey/) {
				my $arrRef = &convertAmbigToAllUnambig($tempSeqA);	#Recurse and pass sequence with ambiguous base
				my @tempArrayB = @$arrRef;							#Convert pointer into array
				foreach my $tempSeqB (@tempArrayB) {				#Add array sequences to 
					push(@outArrayB,$tempSeqB);						#To @outArrayB
				}
				last AMBIGLOOP2;									#Do this ONLY for 1st ambiguous base found
			}
		}
	}
	
	if (scalar(@outArrayB) == 0) {									#If array is empty
		return \@outArrayA;											#outArrayA has no ambiguous sequence so return that
	} else {														#Otherwise ambiguous sequence found
		return \@outArrayB;											#Return outArrayB
	}
}

=begin
=end
=cut
