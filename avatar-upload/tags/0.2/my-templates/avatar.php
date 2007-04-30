<?php bb_get_header(); ?>

<h3 id="breadcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Upload Avatar'); ?></h3>

<?php if (bb_current_user_can('edit_user', $user->ID)) { ?>

<?php
	echo (!empty($success_message)) ? $success_message : "";
?>

<h2 id="userlogin"><?php echo get_user_name( $user->ID ); ?></h2>

<ul>
<li>The following image formats are allowed: <strong><?php echo implode($img_requirements['img_types'], ", "); ?></strong>.</li>
<li>Dimensions must be no greater than <strong><?php echo $img_requirements['max_width']; ?> x <?php echo $img_requirements['max_height']; ?> pixels</strong> (your image does not have to be square).</li>
<li>File size must be no greater than <strong><?php echo $img_requirements['max_kbytes']; ?> <abbr title="kilobytes">KB</abbr></strong></li>
<li>File names must be <strong>alpha-numeric</strong> and may contain <strong>underscores or dashes</strong> (a-z/A-Z, 0-9, _ or -).</li>
</ul>

<form enctype="multipart/form-data" method="POST" action="<?php echo bb_get_option('uri') . 'avatar-upload.php?id=' . $user->ID; ?>">
<p><label for="p_browse">Locate Image:</label><br />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $img_requirements['max_bytes']; ?>" />
<input type="file" name="p_browse" id="p_browse" size="80" /></p>

<p><input type="submit" name="submit" id="submit" value="Upload Avatar" /></p>
</form>

<h3>Your Current Avatar</h3>
<p><?php echo display_avatar($user->ID, 'new'); ?></p>

<?php } ?>

<?php bb_get_footer(); ?>