<?php
require_once "Controllers/CodonOpt_ColorPicker_Ancestor.php";
?>

<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			Help for Codon Optimization
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<script type="text/javascript" src="javascript/svgweb/src/svg.js" data-path="javascript/svgweb/src"></script>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(
				function(){
					jQuery(".HideIfJavaScript").css("display","none");
					jQuery(".ShowIfJavaScript").css("display","inline");
				}
			);
		</script>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				Help for Codon Optimization
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<a id="top"></a>
				<h3>Table of Contents</h3>
				<table>
					<tbody>
						<tr>
							<td colspan="2"><a href="#using_this_website">1: Submitting a Job</a></td>
						</tr>
						<tr>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td><a href="#setup_1_input">1.1: Input Sequence</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#setup_2_method">1.2: Optimization Settings</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#setup_3_select_gene">1.3: Select Genes</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#setup_4_exclusion">1.4: Motif Settings</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#setup_5_start">1.5: Submit</a></td>
						</tr>
						<tr>
							<td colspan="2"><a href="#wait_for_results">2: Waiting for Results</a></td>
						</tr>
						<tr>
							<td colspan="2"><a href="#sample_data">3: Sample Data</a></td>
						</tr>
						<tr>
							<td colspan="2"><a href="#intepret_results">4: Intepreting Results</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#intepret_results_summary">4.1: Summary Graph and Table</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#intepret_results_userdefseq">4.2: Add/Remove User Defined Sequence</a></td>
						</tr>
						<tr>
							<td></td>
							<td><a href="#intepret_results_details">4.3: Detailed Results</a></td>
						</tr>
					</tbody>
				</table>
				<br/>
				
				<a id="using_this_website"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>1: Submitting a Job</h3>
				<p>This website generates and visualizes a codon optimized nucleotide sequence based on the following inputs:</p>
				<table>
					<tbody>
						<tr>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>User submitted Sequence (Protein or Nucleotide).</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td colspan='2'>Optimization Settings: How the output sequence will be optimized. There are several possible target criteria, which are described in detail below, and you may select one or more of these. Selecting two or more parameters uses the  Multi-Objective Codon Optimization algorithm (as described in <a href="http://www.biomedcentral.com/1752-0509/6/134">our paper</a>).</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td colspan='2'>Target species: For the purposes of Codon Optimization, a species is defined by its Translation Rules (which codon translates to which amino acid), and its Codon Frequency values, for both individual codons (3 nucleotide bases), and paired codons (6 nucleotide bases). You may pick one of the following:</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td>Use Inbuilt Species Codon Frequency: Select an existing target organism within our database, which comes with a predefined Translation Rule.</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td>Input Custom Codon Frequency Values: Define a custom expression host by selecting a Translation Rule, and entering your own Codon Frequency values.</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td colspan='2'>Motif Settings: You may specify nucleotide sequences which you do NOT want to be present. This includes specific sequences (such as a restriction enzyme site) and repeats.</td>
						</tr>
					</tbody>
				</table>
				<br/>
				<p>In the rest of this document, each given user-submitted sequence (and its input parameters) is referred to as a "job". The overall process of running a job can be summarized as follows:</p>
				<table>
					<tbody>
						<tr>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>Input desired sequence and select other job parameters.</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td colspan='2'>Start running job (no further changes can be made to input sequence or parameters).</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td colspan='2'>Browse result summary (Sorting table and customizable Pareto Plot)</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td colspan='2'>View Result Details (output sequence and associated details)</td>
						</tr>
					</tbody>
				</table>
				<br/>
				<p>Further details on each step or input above, may be found in the sections below. Other pages on this website also include a "help" link at the top right corner, which directs you to its respective section within this guide. If you experience trouble using this site, or have other comments and suggestions, feel free to <a href="contact.php">contact us</a>.</p>
				<br/>
				<p><b>Important:</b> All the setup pages have "Save and Continue" near the bottom left corner. You must click on this for your inputs to be saved. Navigating to the other setup pages, by clicking on their link tab along the top row, will bring you there without saving any changes that you may have made. On the other hand, if you do NOT want to save changes you may have made (e.g. if you accidentally erased some previously saved input), then navigating to another page (by clicking on their link tab along the top row without clicking "Save and Continue"), will discard your changes.</p>
				<br/>
				
				<a id="setup_1_input"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>1.1: Input Sequence</h3> 
				<?php require "help_text_setup_1_input.php"; ?>
				<br/>
				
				<a id="setup_2_method"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>1.2: Optimization Settings</h3> 
				<?php require "help_text_setup_2_method.php"; ?>
				<br/>
		
				<a id="setup_3_select_gene"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>1.3: Select Genes</h3> 
				<?php require "help_text_setup_3_select_gene.php"; ?>
				<br/>
				
				<a id="setup_4_exclusion"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>1.4: Motif Settings</h3> 
				<?php require "help_text_setup_4_exclusion.php"; ?>
				<br/>
				
				<a id="setup_5_start"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>1.5: Submit Job</h3>
				<?php require "help_text_setup_5_start.php"; ?>
				<br/>
				
				<a id="wait_for_results"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>2: Waiting for Results</h3>
				<?php require "help_text_wait_for_results.php"; ?>
				<br/>
				
				<a id="sample_data"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>3: Sample Data</h3>
				<p>We have provided an option for users to <a href="create_new_example.php">generate a sample output dataset</a>, so that they may view an example pareto spatial distribution, without having to go through the whole job submission process. This sample data was generated using the following inputs and parameters:</p>
				<table>
					<tbody>
						<tr>
							<td>&bull;&nbsp;</td>
							<td colspan='3'>Input Sequence is the Human Insulin Sample Protein Data.</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>Sequence Type is "Protein"</td>
						</tr>
						<tr>
							<td>&bull;&nbsp;</td>
							<td colspan='3'>Optimization Settings:</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>Set "Individual Codon Usage" to "Maximize"</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>Set "Codon Context" to "Maximize"</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>Set "Codon Adaptation Index" to "Ignore"</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>Set "Number of Hidden Stop Codons" to "Ignore"</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>"Optimize GC content" is not checked</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>We choose "Use Inbuilt Expression Host" with a target expression host of "Saccharomyces cerevisiae"</td>
						</tr>
						<tr>
							<td>&bull;&nbsp;</td>
							<td colspan='3'>Gene Selection:</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>We have simply opted for the "Sample List" of genes.</td>
						</tr>
						<tr>
							<td>&bull;&nbsp;</td>
							<td colspan='3'>Motif Settings:</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>No "Exclusion Sequences" were specified</td>
						</tr>
						<tr>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td colspan='2'>For "Remove Repeats", we have checked "Enable Repeat Removal"</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td>Length of Nucleotide Motif: 3</td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td>&bull;&nbsp;</td>
							<td>Minimum Number of Repeats: 3</td>
						</tr>
					</tbody>
				</table>
				<br/>
				<p>For help on how to intepret this sample data, see the next section below.</p>
				<br/>
				
				<a id="intepret_results"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>4: Intepreting Results</h3>
				<p>In the following sections, we explore how to understand the results that this website gives you. But firstly, a quick note on how the results are generated. This website uses a genetic algorithm, the details of which can be found in our <a href="http://www.biomedcentral.com/1752-0509/6/134">previous publication</a>. What follows is a simplified summary:</p>
				<table>
					<tbody>
						<tr>
							<td>&bull;&nbsp;</td>
							<td>Initially, a random population of nucleotide sequences is generated. These nucleotide sequences encode the same desired protein, but differ from each other in having different synonymous codons.</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td>The fitness of each nucleotide sequence is measured. Which fitness parameters are measured depends on what are the Optimization and Motif settings the user has specified. (E.g. if the user has opted to maximize Codon Context, then Codon Context fitness will be measured.)</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td>About half of the nucleotide sequences, which have the lowest fitness will be eliminated.</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td>The remaining sequences with higher fitness are "mutated" (this involves taking a sequence, and randomly changing a few synonymous codons to create slightly different variants, but which otherwise still code for the same protein). These mutated variants form a new population of nucleotide sequences. This is considered 1 generation.</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td>This new population is measured, eliminated, and mutated (repeat from step 2), to create the next generation. And the cycle is repeated for many generations.</td>
						</tr>
						<tr>
							<td>&bull;</td>
							<td>At the end, the final generation has its fitness measured. The fittest sequence(s) are output as the result(s) for this job.</td>
						</tr>
					</tbody>
				</table>
				<br/>
				<p>If the job has only one optimization parameter, then the fittest sequence will be the one with the highest fitness value for that one optimization parameter, and hence there will only be one output sequence. But if there are multiple optimization parameters, multiple output sequences can be considered to have approximately the same overall fitness value, but with different values for the different fitness parameters. In this case, the results will contain ALL of these output sequences, and we let the users select one which best fits their requirements.</p>
				<br/>
				<p>E.g. Lets say that we have 2 optimization parameters. We are trying to maximize both Individual Codon Usage and Codon Context. The algorithm will produce a range of output sequences. At one extreme, there will be a sequence with very high Codon Context (CC) fitness, but very low Individual Codon (IC) Fitness (top left point in the graph below). There will be other sequences with increasing IC fitness, but decreasing CC fitness (curve decreases as it moves rightwards). Until we reach the other extreme, with a sequence which has very high IC fitness, but very low CC fitness (bottom right point).</p>
				<br/>
				<img src="images/example_cc_vs_ic_fitness_curve.png" alt="Graph of CC fitness vs IC fitness" />
				<br/>
				<p>Note that the difference between "Maximize" and "Minimize" for a parameter, is based on how fitness is calculated, but only during the evolution process. When "Minimize" is selected, the algorithm simply calculates the "Maximize" fitness value, and then takes the negative. In this way, sequences which have the highest value under "Maximize", will have the lowest value under "Minimize". However, this only applies within the evolution process- the fitness value provided in the final output reports will be based on the "Maximize" calculation, regardless of whether "Maximize" or "Minimize" was selected.</p>
				<br/>
				
				<a id="intepret_results_summary"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>4.1: Summary Graph and Table</h3> 
				<?php require "help_text_intepret_results_summary.php"; ?>
				<br/>
				
				<a id="intepret_results_userdefseq"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>4.2: Add/Remove User Defined Sequence</h3>
				<?php require "help_text_intepret_results_userdefseq.php"; ?>
				<br/>
				
				<a id="intepret_results_details"></a><hr/>
				<a href="#top" class="right">back to top</a>
				<h3>4.3: Detailed Results</h3>
				<?php require "help_text_intepret_results_details.php"; ?>
				<br/>
				
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>
