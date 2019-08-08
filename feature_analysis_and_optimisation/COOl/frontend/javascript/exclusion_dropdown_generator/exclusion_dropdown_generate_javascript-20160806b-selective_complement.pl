#  This is a Generic Table Parser. It consists of multiple modulized 
#  internal functions which act nearly independently of one another, and
#  can be exported to other programs as needed provided identifiers and
#  parameters are changed (usually marked by '--' lines).

#!c:/Perl/bin/perl.exe
use strict;
use warnings;

#options
my $OutFileName = "exclusion_dropdown_javascript_string.txt";
my $ParseType = "\t";		#INPUT: Parse Type (Tabs)

#Global Variables
my $InFileName;				#Scalar Variable to Hold Filename
my $IsFirstTopMenuPrinted = 0;
my %HHT;					#Header-Hash Table

#------------------------------
#Task Specific Global Variables
my $LastTopMenu = "";
my $TopMenu;
my $Name;
my $IncludeComplement;
my $Sequence;
my $FullSequence;
#------------------------------


{	#Automatic File Finder-Start of Subfunction
	my($tempFileName,@tempFileList);
	my $tempCriteria = "^exclusion_dropdown_baselist";			#INPUT: Regular Expression Criteria
	#Create Empty First Value (filtered by Length Error Check)
	my $tempName = ""; 
	print "Finding files which match Regular Expression criteria: ".$tempCriteria."   ";
	while (defined($tempName)) {						#Until End of List
		chomp ($tempName);								#Delete Newlines (if any)
		if (length($tempName) >= 1) {					#If it passes Length Error Check
			if (! -d $tempName) {						#If it is NOT a directory
				if ($tempName =~ m/$tempCriteria/) {	#If it matches Regular Expression Criteria
				  push(@tempFileList,$tempName);		#Copy value into TempList
				};	#End of 'If it matches Regular Expression Criteria'
			};	#End of 'If it is NOT a directory'
		};	#End of 'If it passes Length Error Check'
		$tempName = glob('*');							#Find Next Value
	};	#End of "Until End of List"
	print "Done.\n";
	if (scalar(@tempFileList) == 1) {					#If only 1 value on list
		$tempFileName = $tempFileList[0];				#Filename is that item
		print "Only one file matches criteria, using ".$tempFileName."\n";
	} elsif (scalar(@tempFileList) == 0) {				#Otherwise No File matches List
		print "No files match criteria: ".$tempCriteria;
		print ". Please enter the name of the input file: ";
		$tempFileName = <STDIN>;
		chomp($tempFileName);
	} else {	#Otherwise More than 1 file matches
		print "This directory has ".scalar(@tempFileList);
		print " Files that match criteria: ".$tempCriteria."\n";
		for (my $num=0; $num<scalar(@tempFileList); $num++)	#Print out File Names
			{print $num.". ".$tempFileList[$num]."\n";};
		print "Please enter the serial number of the file you wish to use: ";
		my $tempNum = <STDIN>;
		chomp($tempNum);
		$tempFileName = $tempFileList[$tempNum];
	};	#End of "Otherwise More than 1 file matches"
	$InFileName = $tempFileName;	#INPUT: final scalar for file name.
};	#End of Subfunction "Automatic File Finder"


#Parse in Header
open (ReadInFile,"<".$InFileName) ||
	die "Unable to open ".$InFileName;

my $InLine = <ReadInFile>;						#parse in Header
chomp($InLine);									#Remove Newline
{	#Transfer Header to HHT
	my @tempArray = split($ParseType,$InLine);	#Split Header
	my $MaxCount = scalar(@tempArray);			
	for (my $num=0; $num<$MaxCount; $num++) {	#For each header
		$HHT{$tempArray[$num]} = $num;			#Transfer to HHT
	};	#End of '$num' cycle
};	#End of 'Transfer Header to HHT'


#Parse in File
$InLine = <ReadInFile>;			#Read in Next Line
open (WriteOutFile,">".$OutFileName) ||
	die "Unable to open ".$OutFileName;
print WriteOutFile "<ul>";		#open list
while (defined($InLine)) {		#Until End of File
	chomp($InLine);				#Remove Newline if any
	if ($InLine ne "") {		#Make Sure Line isn't empty

		#-----------------------
		#Task Specific Functions
		my @tempArray = split($ParseType,$InLine);
		$TopMenu = $tempArray[ $HHT{"TopMenu"} ];
		$Name = $tempArray[$HHT{"Name"}];
		$Sequence = $tempArray[$HHT{"Sequence"}];
		$IncludeComplement = $tempArray[$HHT{"IncludeComplement"}];
		$FullSequence = $tempArray[$HHT{"FullSequence"}];
		$FullSequence =~ s/\"//g;	#Remove quotations that excel inserts
		&ProcessLine();
		#End of 'Task Specific Functions'
		#--------------------------------

	};	#End of 'Make Sure Line isn't empty'
	$InLine = <ReadInFile>;		#Read in Next Line
};	#End of 'Until End of File'
close (ReadInFile);
print WriteOutFile "</ul></li>";	#Close Menu Item
print WriteOutFile "</ul>";			#Close list
close (WriteOutFile);


#====================
#End of Main Function
#====================
#Sample output
#<ul>
#	<li><a>TopMenu</a><ul>
#		<li><a href='#' onclick='AddExclusionSequence("FullSequence")'>Name</a></li>
#	</ul></li>	
#</ul>

sub ProcessLine {
	my @FullSeqArray = split( "," , uc($FullSequence) );
	my @OutSeqArray = ();
	foreach my $tempSeq (@FullSeqArray) {
		if ($IncludeComplement) {			#If including complement
			my $tempCom = &GenerateReverseComplement($tempSeq);
			if ($tempCom eq $tempSeq) {		#If sequence is palindromic
				push(@OutSeqArray,$tempSeq);
			} else {						#Otherwise sequence is not palindromic
				print $tempSeq." of ".$FullSequence." is not palindromic, adding complement to output ".$tempCom."\n";
				push(@OutSeqArray,$tempSeq);
				push(@OutSeqArray,$tempCom);
			}
		} else {							#Otherwise NOT including complement
			push(@OutSeqArray,$tempSeq);	#Add sequence
		}
	}
	
	{	#Compile into Hash to remove repeated sequences
		my %CompileHash = ();
		my @OrderOutArray = ();
		my @RepeatArray = ();
		foreach my $tempSeq (@OutSeqArray) {
			if (defined ($CompileHash{$tempSeq}) ) {
				push(@RepeatArray,$tempSeq);
			} else {
				$CompileHash{$tempSeq} = 1;
				push(@OrderOutArray,$tempSeq);
			}
			
		}
		@OutSeqArray = @OrderOutArray;
		if (scalar(@RepeatArray) >= 1) {
			print "Repeated Sequences found: ".join(",",@RepeatArray)."\n";
		}
	}

	if ($TopMenu ne $LastTopMenu) {
		if ($IsFirstTopMenuPrinted) {			#If first Top Menu has been printed
			print WriteOutFile "</ul></li>\n";	#Close old Top Menu
		}
		print WriteOutFile "<li><a href='#1'>";			#Open new Top Menu
		print WriteOutFile $TopMenu;
		print WriteOutFile "</a><ul>";
		$IsFirstTopMenuPrinted = 1;				#First Top Menu already printed
	}
	print WriteOutFile "<li><a href='#1' onclick='AddExclusionSequence(\"";
	print WriteOutFile join(",",@OutSeqArray);
	print WriteOutFile "\")'>";
	print WriteOutFile $Name;
	print WriteOutFile "</a></li>";
	
	#Reset variables
	$LastTopMenu = $TopMenu;
	$TopMenu = "";
	$Name = "";
	$Sequence = "";
	$FullSequence = "";
}

#This generates a reverse complement of the input sequence
sub GenerateReverseComplement {
	my $inSeq = uc(shift);
	my @tempArray = split(//,$inSeq);
	my @outArray = ();
	my $MaxCountA = scalar(@tempArray);
	for (my $num=($MaxCountA-1); $num>=0; $num--) {
		push(
			@outArray,
			&ComplementLetter($tempArray[$num])
		);
	}
	return join("",@outArray);
}

sub ComplementLetter {
	my $inChar = shift;
	;;;;;if ($inChar eq 'A') {
		return 'T';
	} elsif ($inChar eq 'T') {
		return 'A';
	} elsif ($inChar eq 'C') {
		return 'G';
	} elsif ($inChar eq 'G') {
		return 'C';

	} elsif ($inChar eq 'Y') {
		return 'R';
	} elsif ($inChar eq 'R') {
		return 'Y';
	} elsif ($inChar eq 'W') {
		return 'W';
	} elsif ($inChar eq 'S') {
		return 'S';
	} elsif ($inChar eq 'K') {
		return 'M';
	} elsif ($inChar eq 'M') {
		return 'K';

	} elsif ($inChar eq 'D') {
		return 'H';
	} elsif ($inChar eq 'H') {
		return 'D';
	} elsif ($inChar eq 'V') {
		return 'B';
	} elsif ($inChar eq 'B') {
		return 'V';	

	} elsif ($inChar eq 'N') {
		return 'N';
	} else {
		die "Error-ComplementLetter: Unrecognized inChar: ".$inChar;
	}
}
