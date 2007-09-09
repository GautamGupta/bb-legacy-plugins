<?php bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Upload Avatar'); ?></h3>

<?php if (bb_current_user_can('edit_user', $user->ID)) { ?>

<?php
	echo (!empty($error_message)) ? '<div class="infobox"><strong>'.__($error_message).'</strong></div>' : '';
	echo (!empty($success_message)) ? '<div class="notice">'.__($success_message).'</div>' : '';
?>

<h2 id="userlogin">Avatar for <?php echo get_user_name( $user->ID ); ?></h2>

<ul>
<li><?php _e('The following image formats are allowed: <strong>' . implode($config->file_extns, ", ") . '</strong>.'); ?></li>
<li><?php _e('File names may not contain the following special characters: # ? &amp; % " | \' * `'); ?></li>
<li><?php _e('File size must be no greater than <strong>' . $config->max_kbytes . '<abbr title="kilobytes">KB</abbr></strong>'); ?></li>
<li><?php _e('Image dimensions greater than <strong>' .$config->max_width. ' x ' .$config->max_height. ' pixels</strong> will be resized automatically.'); ?></li>
</ul>

<form enctype="multipart/form-data" method="POST" action="<?php profile_tab_link($user->ID, 'avatar'); ?>">
<p><label for="p_browse"><?php _e('Locate Image'); ?>:</label><br />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $config->max_bytes; ?>" />
<input type="file" name="p_browse" id="p_browse" size="80" /></p>

<p><input type="submit" name="submit" id="submit" value="<?php _e('Upload Avatar'); ?>" /></p>
</form>

<h3><?php _e('Current Avatar'); ?></h3>

<p><?php
	echo avatarupload_display($user->ID, $force_db);

	if ($config->use_thumbnail == 1) {
		echo " &nbsp; " . avatarupload_displaythumb($user->ID, $force_db) . "</p>";
	}
?></p>

<?php if (!usingidenticon($user->ID)) { ?>
<form method="POST" action="<?php profile_tab_link($user->ID, 'avatar'); ?>">
<p><label for="useidenticon"><input type="checkbox" name="identicon" value="1" id="useidenticon" /> <?php _e('Use your Identicon instead?'); ?></label></p>
<p><input type="submit" name="submit" id="submit" value="Use Identicon" /></p>
</form>
<?php
} else {
	_e('<p>You are currently using your Identicon.</p>');
} // end if not using identicon

if ($config->default_avatar['use_default'] == 0) {
	_e("<p>The forum Admin has selected Identicons for user's default avatar. You can change it by uploading your own avatar image (you can always revert back to your Identicon if you wish).</p>");
}
?>

<?php } ?>

<?php bb_get_footer(); ?>