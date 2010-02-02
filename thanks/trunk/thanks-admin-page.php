<form method="post" id="thanks_config_form">
<fieldset id="thanks-voting"><legend>Voting</legend>
<ul>
<li><label for="thanks_voting">Voting phrase:</label>
<input type="text" value="<?php echo thanks_get_voting_phrase("thanks_voting"); ?>" name="thanks_voting" id="thanks_voting" size="50"/></li>

<li><label for="thanks_success">AJAX success message:</label>
<input type="text" value="<?php echo thanks_get_voting_phrase("thanks_success"); ?>" name="thanks_success" id="thanks_success" size="50"/></li>

<li><label for="thanks_position">Voting/report DIV position:</label>
<select name="thanks_position" id="thanks_position"/>
<option value="before"<?php if (thanks_get_voting_phrase("thanks_position") == "before") { echo " selected"; } ?>>Before the post</option>
<option value="after"<?php if (thanks_get_voting_phrase("thanks_position") == "after") { echo " selected"; } ?>>After the post</option>
</select></li>
</ul>
</fieldset>
<fieldset id="thanks-reporting"><legend>Reporting</legend>
<ul><li><label for="thanks_output_none">Post with no votes:</label>
<input type="text" value="<?php echo thanks_get_voting_phrase("thanks_output_none"); ?>" name="thanks_output_none" id="thanks_output_none" size="50"/></li>

<li><label for="thanks_output_one">Post with one vote:</label>
<input type="text" value="<?php echo thanks_get_voting_phrase("thanks_output_one"); ?>" name="thanks_output_one" id="thanks_output_one" size="50"/></li>

<li><label for="thanks_output_many">Post with many votes:</label>
<input type="text" value="<?php echo thanks_get_voting_phrase("thanks_output_many"); ?>" name="thanks_output_many" id="thanks_output_many" size="50"/></li>

<li><em><b>Note:</b> a "#" symbol in the reporting phrase will be replaced with the actual number of votes for a given post.</em></li>
</ul>
</fieldset>
<fieldset id="thanks-submit">
<input type="submit" name="thanks_option_reset" id="thanks_option_reset" value="Reset to Defaults" />
<input type="submit" name="thanks_option_submit" id="thanks_option_submit" value="Save Options" />
</fieldset>
</form>
<?php 
	$options = array('thanks_post', 'thanks_posts', 'thanks_voting', 'thanks_output_none', 'thanks_output_one', 'thanks_output_many', 'thanks_success', 'thanks_position');
	$exist = false;
	for ($i=0; $i < count($options); $i++) {
		$value = bb_get_option($options[$i]);
		$exist |= isset($value);
	}
	if ($exist) {
?>
<fieldset id="thanks-uninstall-message"><legend>Uninstall / Cleanup</legend>
<p>Data about "thank you" votes and options will stay in the database even after this plugin has been deactivated, so that next time you activate the plugin your forum will continue to function correctly.</p><br/>

<p>Clicking the "Remove all votes" button will remove all "thank you" votes and options from the database.</p>
</fieldset>
<fieldset id="thanks-uninstall">
<form method="post" id="thanks_uninstall_form">
<input type="submit" name="thanks_remove_all" id="thanks_remove_all" value="Remove all votes">
</form>
</fieldset>
<?php } ?>

<br/>