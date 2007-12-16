<?php
/*
	Plugin Name:	Show Avatar
	Plugin URI:		http://www.sterling-adventures.co.uk/blog/2007/10/31/use-avatar-bbpress-plug-in/
	Description:	Gets an avatar from gravatar.com, or uses a local avatar cached by the WordPress plugin <a href="http://zenpax.com/gravatars2/">Gravatars2</a>.
	Author:			Peter Sterling
	Author URI:		http://www.sterling-adventures.co.uk
	Version:		2.0
	Change:			1.0 - Initial release.
					2.0 - Added options menu for easier 'non-technical user' control.
*/

// Local avatar cache.
$pws_local_avatars;

// Default options.
$pws_avatar_options = bb_get_option('plugin_pws_avatars');
if(!is_array($pws_avatar_options)) {
	// Options do not exist or have not yet been loaded so we define standard options...
	$pws_avatar_options = array(
		'local_folder' => '',
		'default_avatar_uri' => 'http://',
		'avatar_size' => '30',
		'use_snap_shots' => 'on'
	);
}


// Cache local avatars if needed and return if found.
function pws_get_local_avatar($email)
{
	global $pws_local_avatars;

	// If not already cached get the local avatar array from the WordPress database.
	if(empty($pws_local_avatars)) {
		global $bbdb, $bb;
		$res = $bbdb->get_row("select option_value from {$bb->wp_table_prefix}options where option_name = 'gravatar_local' limit 1");
		$pws_local_avatars = unserialize($res->option_value);
	}

	// Return the avatar indexed by the supplied email address.
	return $pws_local_avatars[$email];
}


// Display an avatar.
function pws_get_avatar()
{
	global $pws_avatar_options;

	// Get the post author's email address.
	$post_author_email = bb_get_user_email(get_post_author_id());

	// See if there is a local avatar for that email address.
	$local = pws_get_local_avatar($post_author_email);

	// Decide if the avatar output is local or gravatar supplied.
	if(!empty($local)) $src = $pws_avatar_options['local_folder'] . $local;
	else {
		$md5 = md5($post_author_email);
		$default = urlencode($pws_avatar_options['default_avatar_uri']);
		$src = "http://www.gravatar.com/avatar.php?gravatar_id=$md5&amp;size=" . $pws_avatar_options['avatar_size'] . "&amp;default=$default";
	}

	// Output the avatar, wrapping it in an author link if there is one.
	$link = get_user_link(get_post_author_id());
	if($link) echo '<a href="' . attribute_escape($link) . '" ' . ($pws_avatar_options['use_snap_shots'] == 'on' ? '>' : 'class="snap_noshots">');
	echo "<img class='gravatar' src='$src' alt='' />";
	if($link) echo '</a>';
}


// Output HTML for plug-in options page.
function pws_options_page()
{
	global $pws_avatar_options; ?>

	<h2>Avatar Options</h2>
	Control the bevaiour of the avatar plug-in.<br />
	This plug-in gets an avatar from <a href="http://www.gravatar.com/">gravatar.com</a>, or uses a local avatar cached by the <a href="http://wordpress.org">WordPress</a> plugin <a href="http://zenpax.com/gravatars2/">Gravatars2</a>.
	<p>Please visit the author's site, <a href='http://www.sterling-adventures.co.uk/' title='Sterling Adventures'>Sterling Adventures</a>, and say "Hi"...</p>
	<form method="post">
		<p class="submit"><input name="submit" type="submit" value="<?php _e('Update'); ?>" /></p>
		<fieldset class="options">
			<legend>Avatar Plug-in Settings</legend>
			<table align="center">
				<tr><td>Path to <b>local</b> avatar folder</td><td><input type="text" name="local_folder" size="66" value="<?php echo $pws_avatar_options['local_folder']; ?>" /></td></tr>
				<tr><td></td><td><em>You may use a complete or relative path. For example,<br /></em><code>../wordpress/wp-content/gravatars/local/</code></td></tr>
				<tr><td><b>Default</b> avatar URI</td><td><input type="text" name="default_avatar_uri" size="66" value="<?php echo $pws_avatar_options['default_avatar_uri']; ?>" /></td></tr>
				<tr><td></td><td><em>You should use a complete URI. For example,<br /></em><code>http://www.your-site.com/wordpress/wp-content/gravatars/default.jpg</code></td></tr>
				<tr><td>Size of avatar image</td><td><input type="text" name="avatar_size" size="3" value="<?php echo $pws_avatar_options['avatar_size']; ?>" /></td></tr>
				<tr><td></td><td><input type="checkbox" name="use_snap_shots" <?php echo $pws_avatar_options['use_snap_shots'] == 'on' ? 'checked' : ''; ?> /> Use snap shots?<br />
			</table>
		</fieldset>
		<p class="submit"><input name="submit" type="submit" value="<?php _e('Update'); ?>" /></p>
		<input type="hidden" name="action" value="update" />
	</form>
	<fieldset class="options">
		<legend>Avatar Plug-in Usage</legend>
		After activating the plug-in...
		<ol>
			<li>Set the options above.</li>
			<li>Place this PHP code in your theme's template files where you want the user avatars to appear.<br />
			Note: An example <code>posts.php</code> is included with the plug-in download.
			<p><code>&lt;?php if(function_exists('pws_get_avatar')) pws_get_avatar(); ?&gt;</code></li></p>
		</ol>
	</fieldset>
<?php }


// Create the plug-in options menu.
function pws_options_menu()
{
	if(function_exists('bb_admin_add_submenu')) bb_admin_add_submenu(__('Avatar Options'), 'use_keys', 'pws_options_page');
}


// Process submission of (new) plug-in options.
function pws_page_process()
{
	global $pws_avatar_options;

	if(isset($_POST['submit'])) {
		if('update' == $_POST['action']) {
			$pws_avatar_options_update = array (
				'local_folder' => $_POST['local_folder'],
				'default_avatar_uri' => $_POST['default_avatar_uri'],
				'avatar_size' => $_POST['avatar_size'],
				'use_snap_shots' => $_POST['use_snap_shots']
			);
			bb_update_option('plugin_pws_avatars', $pws_avatar_options_update);
			$pws_avatar_options = bb_get_option('plugin_pws_avatars');
		}
	}
}


// Menu hooks...
add_action('bb_admin_menu_generator', 'pws_options_menu');
add_action('bb_admin-header.php', 'pws_page_process');
?>