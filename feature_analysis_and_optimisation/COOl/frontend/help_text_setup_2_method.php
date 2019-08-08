<p>There are several options to set here. Firstly, you need to select your optimization criteria. There are 4 parameters, and each parameter has 3 settings: "Ignore", "Maximize" and "Minimize". At least 1 parameter should be selected (i.e. set to Maximize or Minimize) in order for the Algorithm to have some optimization target to work with.</p>
<br/>
<p><u>Individual Codon Usage:</u> abbreviated as ICU. This refers to how closely the output nucleotide sequence matches the codon usage pattern of the Expression Host. When set to "Maximize", the output sequence will try to match the host as closely as possible (subject to other optimization criteria, and motif settings). Conversely, when set to "Minimize", the output sequence codon usage pattern will try to deviate from the host as much as it can.</p>
<br/>
<p><u>Codon Context:</u> abbreviated as CC. This is like ICU, but applied to pairs of codon (6 nucleotides in all), instead of each individual codon. Hence when set to maximize, it tries to match the codon pair usage pattern of the Expression Host. The recommended default criteria is to Maximize Codon Context only.</p>
<br/>
<p><u>Codon Adaptation Index:</u> abbreviated as CAI. It measures the fitness for each codon, relative to the target expression host, but it is calculated differently from ICU. The score for each position is calculated by using the host frequecy of the codon at that position, divided by the host frequency of the most common synonymous codon for that amino acid. (E.g. Phenyalanine is encoded by both TTT and TTC. Lets say that the expression host uses TTT with a frequency of 0.8, and TTC with a frequency of 0.2. The score for each occurence of TTT is 0.8/0.8 = 1, and the score for each occurence of TTC is 0.2/0.8 = 0.25). Hence, CAI is maximized, when only the most common codons are used.</p>
<br/>
<p><u>Number of Hidden Stop Codons:</u> In each protein-coding sequence, there is only ever 1 stop codon, at the end of the protein. Instead, this parameter refers to stop codon sequences that are found outside the normal reading frame. (E.g. Methionine-Threonine is encoded by A<u>TGA</u>CN, and the underline highlights a hidden stop codon). Selecting "Maximize" on "Number of Hidden Stop Codons", will maximize the number of hidden stop codons in the output sequence. This may be useful according to the <a href="http://www.ncbi.nlm.nih.gov/pubmed/15585128" target="_blank">ambush hypothesis</a>, by causing erroneous frame-shifted translations to terminate early.</p>
<br/>
<p>The second section is where you can enable GC content as an optimization target. This field might be useful if your desired nucleotide sequence should have a specific GC content value (e.g. if it needs a specific melting temperature). To enable GC content optimization, ensure that the checkbox is ticked, and enter your target as a percentage value (e.g. "38" instead of "0.38").</p>
<br/>
<p>The next section specifies the individual codon (and/or codon pair) usage pattern values, which will serve as the optimization targets for the expression host. Firstly, there is a radio button, where you choose either to "Use Inbuilt Expression Host", or to "Input Custom Codon Usage Pattern Values".</p>
<br/>
<p>If you choose "Use Inbuilt Expression Host", you are using values for one of our inbuilt species. You may select the target species from the dropdown list. In the next page, you can then pick genes of the selected Expression Host to use. The algorithm will also automatically apply the translation rules appropriate to that species (i.e. which codons code for which amino acids).</p>
<br/>
<p>If you choose to "Input Custom Codon Usage Pattern Values", then you need to select the translation rules for your custom species from the dropdown list.</p>
<br/>
<p><b>Important:</b> This translation rules you select here, applies to the expression host, where the protein will be produced. Whereas the translation rules you selected back in <a href="#setup_1_input">1: Input Sequence</a> applies to the nucleotide sequence's original host, where the DNA/RNA was taken from. E.g. lets say you are trying to express a <i>Blepharisma</i> sequence in <i>Salmonella bongori</i> (which uses Standard translation rules). Then here, you should select "Standard Code".</p>
<br/>
<p>Additionally, you also need to input the individual codon (and/or codon pair) usage pattern values for whichever optimization criteria is being used. (E.g. if you chose to maximize or minimize "Individual Codon Usage" or "Codon Adaptation Index" at the top, you need to provide codon usage pattern values for your custom species in the "Individual Codon Usage Pattern" textbox.) There are 2 ways you can input the codon usage pattern values, as follows:</p>
<br/>
<p>The first way is to upload the coding sequences of your target host as FASTA format, and the website will convert these sequences into the appropriate codon usage pattern values. To do this, click on "Convert Fasta Into Codon Usage Pattern Values", and you will be led to another page, where you can upload your FASTA formatted file. If the upload is successful, your converted codon usage pattern values will be seen in the "Individual Codon Usage Pattern" and "Codon Context Usage Pattern" textboxes.</p>
<br/>
<p>The second way is to calculate the codon usage pattern values on your own, format these values into text, and paste the text into the provided textboxes. The text format for codon usage pattern values take the general form of a list of nucleotide sequences on seperate lines. Within the same line, each nucleotide sequence is followed by a space or tab, and then an integer stating the frequency of that sequence. Please only use integers, and not fractions or decimals. For Individual Codon Usage, the nucleotide sequence will be exactly 3 bases long (<a href="sample-EColi.All.IndividualCodon.txt" target="_blank">Sample Format</a>). For Codon Context, the nucleotide sequence will be 6 bases long (<a href="sample-EColi.All.CodonContext.txt" target="_blank">Sample Format</a>).</p>
<br/>
<p>When you are done, click "Save and Continue".</p>