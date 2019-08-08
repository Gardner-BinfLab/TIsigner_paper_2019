<p>There are 3 sections on this page.</p>
<br/>
<p>The first section controls "Exclusion Sequences". In some circumstances, you may want the output sequence to exclude certain specific nucleotide sequences. E.g. Perhaps you are going to splice this sequence into a vector using a certain restriction enzyme, and hence you do not want the enzyme's target site to occur within the output sequence. We provide this optional facility, where you may enter nucleotide sequences which you want to be excluded from the output, as a comma seperated list.</p> 
<br/>
<span class="ShowIfJavaScript">
	<p>This page also has a dropdown menu bar, which allows you to select common Restriction Enzyme and Translation Initiation Sites by name. Clicking on a site name, will add its sequence to the textbox. For Restriction Enzyme Sites we also include the complement (since Restriction Enzymes work at the DNA level which ignores directionality). But for the Translation Initiation Sites, we do not include the complement (since Translation Initiation works at the mRNA level, and it usually does not matter if the mRNA complement has a Translation Initiation Site). For the Translation Initiation Sites, we used the following consensus sequence (a particular base was excluded from a specified position, if it occurred less than 10% of the time):</p>
	<br/>
	<table>
		<tr>
			<td>Shine-Dalgarno mRNA</td>
			<td>GG[AG]GG</td>
			<td><a target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed/22456704">source</a></td>
		</tr>
		<tr>
			<td>Kozak (Vertebrate)</td>
			<td>[ACG][AG]N[ACG]ATG</td>
			<td><a target="_blank" href="http://nar.oxfordjournals.org/content/15/20/8125.long">source</a></td>
		</tr>
		<tr>
			<td>Kozak (S. cerevisiae)</td>
			<td>[ACT]A[ACT][AC]ATG[AGT]</td>
			<td><a target="_blank" href="http://nar.oxfordjournals.org/content/15/8/3581.long">source</a></td>
		</tr>
		<tr>
			<td>Kozak (Drosophila spp.)</td>
			<td>[AC][AG][ACT][ACG]ATG</td>
			<td><a target="_blank" href="http://nar.oxfordjournals.org/content/15/4/1353.long">source</a></td>
		</tr>
	</table>
	<br/>
	<p>This dropdown menu requires Javascript to work, so if Javascript is not fully enabled, it may fail to function.</p>
</span>
<br/>
<p>The second section determines whether you want to enable removal of "Consecutive Repeats". You can specify both the "Length of Nucleotide Motif", and the "Minimum Number of Instances before removal". The "Length of Nucleotide Motif" refers to the number of nucleotides within one iteration of the repeat. E.g. If your "Length of Nucleotide Motif" is 2, this will include all possible 2^4 = 16 dinucleotide motifs (AA, AT, AC, AG, TA, TT, TC, TG, CA, CT, CC, CG, GA, GT, GC, GG). </p>
<br/>
<p>The "Minimum Number of Instances before removal" indicates how many times the motif should occur, before it is considered a repeat that should be removed. This parameter has a minimum value of 2. E.g. If "Minimum Number of Instances before removal" is 3, then a particular motif must occur 3 or more consecutive times to be considered a repeat that should be removed. Hence "ATATAT" would qualify, but "ATAT" would not.</p>
<br/>

<p>The third section determines whether you want to enable removal of "Repeated Motifs". Whereas the "Consecutive Repeats" specified in the previous section have to be next to each other, "Repeated Motifs" can have each instance of the repeat located anyway in the sequence. "Length of Nucleotide Motif" has a minimum of 7, and "Minimum Number of Instances before removal" has a minimum of 2. E.g. "<u>ATGATGATG</u>CTC<u>ATGATGATG</u>CGC<u>ATGATGATG</u>" would count as a 9 base long motif, that has 3 instances.</p>
<br/>

<p><b>Important:</b> If you do want to enable removal of "Consecutive Repeats" or "Repeated "Motifs", ensure that their respective checkbox is ticked. Otherwise the algorithm will ignore whatever values you may have entered.</p>
<br/>
<p>When you are done, click "Save and Continue".</p>
<br/>
<p>Note that the algorithm is NOT guaranteed to remove all exclusion sequences and/or repeats. It will try its best to exclude them, but there may be instances where this is simply not possible for the given protein sequence. E.g. Lets say that the protein has 2 consecutive methionine bases. This can only be coded in one way (ATGATG) under standard translation rules. If you specify "ATGATG" as an exclusion sequence, or that you want to remove repeats of 3 nucleotide motifs repeated 2 or more times (which includes ATGATG), the algorithm will be unable to meet your request. As such, the output results include count checks on whether any Exclusion Sequence or User-specified Repeats are present in the output sequence. Use these to double check whether all Exclusion Sequences and Repeats are absent.</p>