<?php require_once "commonstyle-page_scripts.php"; ?>
				</td>
			</tr>
			<tr><!-- 3rd row: Footer -->
				<td>
					<!-- Footer (Formatting based on help bar)-->
					<div class="x-tab-panel-tbar" style="width: <?php echo ($SectionFormat_InnerWidth+12); ?>px;">
						<div class="x-toolbar x-small-editor x-toolbar-layout-ct" style="padding:10px; width:<?php echo ($SectionFormat_InnerWidth+10); ?>px; text-align:center; font: normal 13px tahoma,arial,helvetica,sans-serif;">
							&copy; 2012-<?php echo date("Y"); ?> <a href="http://syncti.org/">SynCTI, National University of Singapore (NUS)</a>. All Rights Reserved.<br/>
							COOL is free for academic and non-commercial use (<a href="License_Academic_and_Evaluation_for_WebMOCO.pdf">academic and evaluation license</a>). For a commercial license please <a href="contact.php">contact us</a>.<br/>
							This page requires Chrome 32, Firefox 26, or Internet Explorer 9 (without compatability mode).
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<!-- popup_help_div to show help iFrames to user -->
<!--low height allows user to click on the "view" button at the side of the visible popup -->
<div id="grey_popup_holder_div" style="display:none;">
	<table class="middle" style="border-collapse:collapse; border-style:none; padding:0px; margin:0px; box-shadow: 0px 5px 10px 5px #666666; margin-left:auto; margin-right:auto;"><tr><td>
		<?php require "commonstyle-section-1-beforetitle.php"; ?>
			<table style="width:100%; border-collapse:collapse; border-style:none; padding:0px; margin:0px;">
				<tr>
					<td style="width:100%;">
						<div id="grey_popup_title" class="middle">Title</div>
					</td>
					<td class="right">
						<input type="button" name="hide_grey_popup_holder_div_button" id="hide_grey_popup_holder_div_button" value=" X " onclick="jQuery('#grey_popup_holder_div').css('display','none')" />
					</td>
				</tr>
			</table>
		<?php require "commonstyle-section-2-beforecontent.php"; ?>
			<div id="grey_popup_content">This is some test Text</div>
		<?php require "commonstyle-section-3-aftercontent.php"; ?>
	</td></tr></table>
</div>

<!-- popup_message_div to deliver pop up messages to user -->
<!--low height allows user to click on the "view" button at the side of the visible popup -->
<div style="text-align:right; position:fixed; width:100%; height:1px; bottom:35px; left:0px; right:0px;">
	<table class='right'>
		<tr>
			<td>
				<div style="display:none; background-color:rgb(201,227,255); font-family:tahoma,arial,helvetica,sans-serif; font-style:normal; font-size:11px; color:rgb(0,29,106); border-color:rgb(0,29,106); border-style:solid; border-width:2px; padding:3px; -moz-border-radius:5px; border-radius:5px;" id="popup_message_div">
					<table>
						<tr>
							<td id="popup_message_div_content"></td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td><a href='#' style="text-decoration:none;" onclick='jQuery("#popup_message_div").css("display","none");return false;'><b>X</b></a></td>
						</tr>
					</table>
				</div>
			</td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		</tr>
	</table>
</div>
