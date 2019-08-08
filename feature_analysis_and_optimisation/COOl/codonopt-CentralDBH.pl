#!/usr/bin/perl -w
use warnings;
use strict;
use DBI;
$|++;

#Centralize Login Details to one subfunction
sub GetDBHandler {
	# DATABASE VARIABLES
	my $platform = "mysql";
	my $database = "redacted";
	my $host = "redacted";
	my $port = "redacted";
	my $user = "redacted";
	my $password = "redacted";

	# Connects to database, returning database handle
	my $dbh = DBI->connect("dbi:$platform:$database:$host:$port",$user,$password)
		or die "Couldn't connect to database: " . DBI->errstr;
	return $dbh;
}

sub PadString {
	my $StrLength = 10;
	my $InputStr = shift;
	my $OutputStr = "".$InputStr;
	while (length($OutputStr) < $StrLength) {	#While string is too short
		$OutputStr = "0".$OutputStr;			#Pad with "0" in front
	}
	return $OutputStr;
}
