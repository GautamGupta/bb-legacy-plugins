<?php bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Upload Avatar'); ?></h3>

<?php if (bb_current_user_can('edit_user', $user->ID)) { ?>

<?php
	echo (!empty($success_message)) ? '<div class="notice">'.__($success_message).'</div>' : "";
?>

<h2 id="userlogin"><?php echo get_user_name( $user->ID ); ?></h2>

<ul>
<li><?php _e('The following image formats are allowed: <strong>' . implode($config->file_extns, ", ") . '</strong>.'); ?></li>
<li><?php _e('Dimensions must be no greater than <strong>' .$config->max_width. ' x ' .$config->max_height. ' pixels</strong> (your image does not have to be square).'); ?></li>
<li><?php _e('File size must be no greater than <strong>' . $config->max_kbytes . '<abbr title="kilobytes">KB</abbr></strong>'); ?></li>
<li><?php _e('File names must be <strong>alpha-numeric</strong> and may contain <strong>underscores or dashes</strong> (a-z/A-Z, 0-9, _ or -).'); ?></li>
</ul>

<form enctype="multipart/form-data" method="POST" action="<?php profile_tab_link($user->ID, 'avatar'); ?>">
<p><label for="p_browse"><?php _e('Locate Image'); ?>:</label><br />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $config->max_bytes; ?>" />
<input type="file" name="p_browse" id="p_browse" size="80" /></p>

<p><input type="submit" name="submit" id="submit" value="<?php _e('Upload Avatar'); ?>" /></p>
</form>

<h3><?php _e('Your Current Avatar'); ?></h3>
<p><?php echo avatarupload_display($user->ID, 'new'); ?></p>

<?php } ?>

<?php bb_get_footer(); ?>