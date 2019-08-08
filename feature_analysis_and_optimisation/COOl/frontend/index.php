<?php 
require_once "Controllers/CodonOpt_Controller_Ancestor_User_Job.php";
require_once "Controllers/CodonOpt_Controller_UserSession.php";
?>
<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<title>
			Home: About Codon Optimization
		</title>
		<?php require "commonstyle-page_scripts.php"; ?>
		<script type="text/javascript" src="javascript/tablesorter/jquery.tablesorter.js"></script>
		<script type="text/javascript">
			jQuery(document).ready(
				function() { 
					jQuery("#recent_result_table").tablesorter();
				}
			); 
		</script>
		<meta name="description" content="Perform multi-objective codon optimization (MOCO) online, without needing to download or install any software."/>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				About Codon Optimization
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<div class="ErrorMessage">Important: As of 22 Mar 2016, COOL has changed URL. Please update your bookmarks.</div>
				<br/>
				<p>Codon Optimization On-Line (COOL) can be used to design a nucleotide sequence for improved expression of the desired protein within the target host organism. This method is useful for life science research, especially in the area of synthetic biology, where there is a need to achieve high expression of a foreign or artificial gene within a non-native host.</p>
				<br/>
				<!--
				<p>The main aim of Codon Optimization is to take a desired protein, and engineer a nucleotide sequence which will maximize production of that protein within a given target organism. This is most commonly used to enhance the expression of recombinant genes (where a protein is produced outside its natural host organism, and the optimal codons to use may be different).</p>
				-->
				<p>There are several methods of calculating the optimal output sequence. This web tool is an online implementation of the algorithm described in our previous publication (Chung BKS and Lee DY. Computational codon optimization of synthetic gene for protein expression. <a target="_blank" href="http://www.biomedcentral.com/1752-0509/6/134">BMC Systems Biology 6:134</a>). It has been further augmented to include additional optimization criteria, and multiple output sequences within pareto space. You may view an example of this pareto spatial distribution of results, by <a href="create_new_example.php">generating a new sample output dataset</a>. Details on how this example was generated can be found in our <a href="help.php#sample_data">guide</a>.</p>
				<br/>
				<form action="<?php echo CodonOpt_Controller_Ancestor_User_Job::getSubmitNewJobPage(); ?>" method="get" name="link_to_submit_new_job">
					<p class='middle'>
						<input name="" type="submit" value="Start Using Codon Optimization On-Line &gt;&gt;&gt;" class="bluebutton" />
					</p>
				</form>
				<br/>
				<p>Please Cite: <b>Chin JX</b>, <b>Chung BKS</b> and <b>Lee DY</b> (2014). Codon Optimization On-Line (COOL): a web-based multi-objective optimization platform for synthetic gene design. <i>Bioinformatics</i>, 30 (15): 2210-2212, doi: 10.1093/bioinformatics/btu192 (<a target="_blank" href="http://bioinformatics.oxfordjournals.org/content/30/15/2210">link</a>)
				</p>
				<br/>
				<h3>Your Recent Results</h3>
				<?php 
					$RecentResultList = CodonOpt_Controller_UserSession::getUserJobPartialDTO_RecentResults();
					if ( count($RecentResultList) == 0) {
						echo "You have no recent results.";
					} else {
						require "index-recent_results.php";
					}
				?>
				
				<div class='middle'>
					<img width="500px" src="images/Aminoacids_table.svg"></img><br/>
					Source: <a href='http://commons.wikimedia.org/wiki/File%3AAminoacids_table.svg' target='_blank'>Wikipedia</a>
				</div>
				<br/>
				
				<h3>Announcements</h3>
				<div><b>13 Jun 2016:</b> Shine-Dalgarno mRNA and Kozak sequence added as options to the Exclusion Sequence dropdown menu.</div>
				<br/>
				<div><b>30 May 2016:</b> In addition to repeats that occur consecutively, COOL can now screen for motifs of a specified length, which occur repeatedly, regardless of location.</div>
				<br/>
				<div><b>24 May 2016:</b> COOL now accepts sequences which do not end with stop codons. However, if you exclude the stop codon, it will likewise be absent in the optimized results, which may cause translation runoff.</div>
				<br/>
				<div><b>23 May 2016:</b> Exclusion Sequences (under section 4: Motif Settings) can now include any ambiguous nucelotide base.</div>
				<br/>
				<div><b>22 Mar 2016:</b> The URL for COOL has changed. Please update your bookmarks.</div>
				<br/>
				<div><b>2 Mar 2016:</b> We have carried out a significant update to our code. Users of our site should not notice any difference. If you are running a local COOL mirror, you can get the latest code from our download section.</div>
				<br/>
				<!-- 
				<div><b>25 Sep 2014:</b> The home page will now list your recently generated results.</div>
				<br/>
				<div><b>6 Jun 2014:</b> <i>Lactococcus lactis</i> has been added as a selectable expression host.</div>
				<br/>
				<div><b>17 Mar 2014:</b> COOL is now using an updated implementation of WebMOCO that should produce results much more quickly. Optimizing a 375 amino acid residue sequence, should now be finished within several minutes.</div>
				-->
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>