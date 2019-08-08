<!DOCTYPE html>
<html <?php require "commonstyle-attributes_html.php"; ?> >
	<head>
		<?php require "commonstyle-page_scripts.php"; ?>
		<title>
			Download
		</title>
	</head>
	<body <?php require "commonstyle-attributes_body.php"; ?> >
		<?php require "commonstyle-page_header.php"; ?>
			<?php require "commonstyle-section-1-beforetitle.php"; ?>
				Download
			<?php require "commonstyle-section-2-beforecontent.php"; ?>
				<table>
					<tr>
						<td colspan='3'>
							Here you may download the source code for this web interface, and the "WebMOCO" executable binary that we use to perform codon optimization. You may use them to setup an internal mirror of this site, or create a different site relying on another codon optimization algorithm. You can also use the back end processing binary as a stand-alone tool.
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<a href="download_front_end.zip">Front_End</a>
						</td>
						<td>&nbsp;&nbsp;</td>
						<td>This is a zip of the front end, i.e. this web interface itself. Requires PHP 5.3.6 or better. Before starting the site, remember to setup the MySQL database, and the back end binary (see below). You also need to tell PHP how to login to your MySQL database, by changing the login details found at "Controllers/CodonOpt_DAO_ancestor.php". This source code is released under the <a href="License_MIT.pdf">MIT license</a>. <!--<b>Note:</b> Although the web interface source code is open, this very website generates results based on the WebMOCO backend, and hence usage of COOL is based on the <a href="License_Academic_and_Evaluation_for_WebMOCO.pdf">WebMOCO license</a>.--></td>
					</tr>
					<tr><td>&nbsp;</td></tr>			
					<tr>
						<td>
							<a href="download_SQL_tables.zip">MySQL_Tables</a>
						</td>
						<td></td>
						<td>This is a zip of the data files necessary to populate the MySQL database tables. They only include the table names and contents, and excludes the database name. We leave it up to you to determine the database name, username and password. These login details have to be added to both the front and back end. These data files are released under the <a href="License_MIT.pdf">MIT license</a>.</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td>
							<a href="COOL_20160707a-nosource.tar">Back_End</a>
						</td>
						<td></td>
						<td>This is a zip of the back end "co_ubuntu.exe" Codon Optimization binary for Linux 64 bit. This can be run independently on its own, reading/writing data to/from text files, without needing to setup the front end or MySQL database. If you do decide to run it through the front end web interface, then you have to setup the MySQL database login details for the back end in the "codonopt-CentralDBH.pl" file. <b>Note:</b> We are in the process of filing a patent for the backend code, and will only provide the backend in the format of an executable binary. By downloading this binary, you acknowledge that you have read and agree to the <a href="License_Academic_and_Evaluation_for_WebMOCO.pdf">WebMOCO license</a>. For a commercial license, please <a href="contact.php">contact us</a>. </td>
					</tr>			
				</table>
			<?php require "commonstyle-section-3-aftercontent.php"; ?>
		<?php require "commonstyle-page_footer.php"; ?>
	</body>
</html>