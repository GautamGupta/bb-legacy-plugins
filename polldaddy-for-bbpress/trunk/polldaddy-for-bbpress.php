<?php 
/*
Plugin Name: PollDaddy for bbPress
Plugin URI: http://bbpress.org/plugins/topic/polldaddy-for-bbpress/
Description: Allow users to place PollDaddy polls in your bbPress posts.
Author: Sam Bauers
Author URI: http://unlettered.org/
Version: 1.0.4
*/



$bb_polldaddy_options = array(
	'first_only' => bb_get_option('bb_polldaddy_first_only'),
	'permission' => bb_get_option('bb_polldaddy_permission')
);

// Load the gettext textdomain
load_plugin_textdomain( 'polldaddy-for-bbpress', dirname( __FILE__ ) . '/languages' );

function bb_polldaddy_get_poll_script($poll)
{
	return '<script type="text/javascript" language="javascript" src="http://s3.polldaddy.com/p/' . $poll . '.js"></script><noscript><a href ="http://answers.polldaddy.com/poll/' . $poll . '" >' . __('View Poll', 'polldaddy-for-bbpress') . '</a></noscript>';
}

function bb_polldaddy_get_poll_script_callback($matches)
{
	return bb_polldaddy_get_poll_script($matches[1]);
}

function bb_polldaddy_first_only_error_handler($atts, $content = null)
{
	return __('<em>Sorry, polls can only be added to the first post in a thread.</em>', 'polldaddy-for-bbpress');
}

function bb_polldaddy_permission_error_handler($atts, $content = null)
{
	return __('<em>Sorry, you do not have permission to add polls.</em>', 'polldaddy-for-bbpress');
}

// For bbPress less than 1.0-alpha-2
function bb_polldaddy_preg($post_text, $post_id)
{
	global $bb_polldaddy_options;

	$callback = 'bb_polldaddy_get_poll_script_callback';

	if ($bb_polldaddy_options['first_only'] && !bb_is_first($post_id)) {
		$callback = 'bb_polldaddy_first_only_error_handler';
	}

	if (!$user_id = get_post_author_id($post_id)) {
		$callback = 'bb_polldaddy_permission_error_handler';
	}

	if (!$user = new WP_User($user_id)) {
		$callback = 'bb_polldaddy_permission_error_handler';
	}

	if ($bb_polldaddy_options['permission'] && !$user->has_cap($bb_polldaddy_options['permission'])) {
		$callback = 'bb_polldaddy_permission_error_handler';
	}

	$post_text = preg_replace_callback('@\[polldaddy poll=(?:"|\')?([0-9]+)(?:"|\')?\]@', $callback, $post_text);
	return $post_text;
}

// For bbPress greater than 1.0-alpha-1
function bb_polldaddy_shortcode_handler($atts, $content = null)
{
	extract( shortcode_atts( array('poll' => 'empty'), $atts ) );

	return bb_polldaddy_get_poll_script($poll);
}

function bb_polldaddy_shortcode($post_text, $post_id)
{
	global $bb_polldaddy_options;

	if ($bb_polldaddy_options['first_only'] && !bb_is_first($post_id)) {
		remove_shortcode('polldaddy');
		add_shortcode('polldaddy', 'bb_polldaddy_first_only_error_handler');
		return $post_text;
	}

	if (!$user_id = get_post_author_id($post_id)) {
		remove_shortcode('polldaddy');
		add_shortcode('polldaddy', 'bb_polldaddy_permission_error_handler');
		return $post_text;
	}

	if (!$user = new WP_User($user_id)) {
		remove_shortcode('polldaddy');
		add_shortcode('polldaddy', 'bb_polldaddy_permission_error_handler');
		return $post_text;
	}

	if ($bb_polldaddy_options['permission'] && !$user->has_cap($bb_polldaddy_options['permission'])) {
		remove_shortcode('polldaddy');
		add_shortcode('polldaddy', 'bb_polldaddy_permission_error_handler');
		return $post_text;
	}

	add_shortcode('polldaddy', 'bb_polldaddy_shortcode_handler');
	return $post_text;
}

if (version_compare(bb_get_option('version'), '1.0-alpha-2', 'lt')) {
	add_filter( 'post_text', 'bb_polldaddy_preg', 5, 2);
} else {
	add_filter( 'post_text', 'bb_polldaddy_shortcode', 5, 2 );
}

if (!BB_IS_ADMIN) {
	return;
}

// Add filters for the admin area
add_action('bb_admin_menu_generator', 'bb_polldaddy_admin_page_add');
add_action('bb_admin-header.php', 'bb_polldaddy_admin_page_process');

function bb_polldaddy_admin_page_add()
{
	bb_admin_add_submenu(__('PollDaddy', 'polldaddy-for-bbpress'), 'use_keys', 'bb_polldaddy_admin_page');
}

function bb_polldaddy_admin_page()
{
?>

<h2><?php _e('PollDaddy', 'polldaddy-for-bbpress'); ?></h2>

<p>
	<?php printf(__('<a href="%s">PollDaddy</a> is a free hosted polls service where you can create polls to include in your bbPress topics.', 'polldaddy-for-bbpress'), 'http://polldaddy.com/'); ?>
</p>

<form class="settings" method="post" action="admin-base.php?plugin=bb_polldaddy_admin_page">
	<fieldset>
		<legend><?php _e('Options', 'polldaddy-for-bbpress'); ?></legend>
		
		<div>
			<label for="bb_polldaddy_first_only">
				<?php _e('Allow on first post only', 'polldaddy-for-bbpress'); ?>
			</label>
			<div>
				<input type="checkbox" class="checkbox" name="bb_polldaddy_first_only" id="bb_polldaddy_first_only" value="1"<?php checked( bb_get_option('bb_polldaddy_first_only'), 1 ); ?> />
				<?php _e('Limit inclusion of PollDaddy polls to the first post of a topic.', 'polldaddy-for-bbpress'); ?>
			</div>
		</div>
		<div>
			<label for="bb_polldaddy_permission">
				<?php _e('Minimum user level', 'polldaddy-for-bbpress'); ?>
			</label>
			<div>
				<select name="bb_polldaddy_permission" id="bb_polldaddy_permission">
					<option value="participate"<?php selected( bb_get_option('bb_polldaddy_permission'), 'participate' ); ?>><?php _e('Member', 'polldaddy-for-bbpress'); ?></option>
					<option value="moderate"<?php selected( bb_get_option('bb_polldaddy_permission'), 'moderate' ); ?>><?php _e('Moderator', 'polldaddy-for-bbpress'); ?></option>
					<option value="administrate"<?php selected( bb_get_option('bb_polldaddy_permission'), 'administrate' ); ?>><?php _e('Administrator', 'polldaddy-for-bbpress'); ?></option>
					<option value="use_keys"<?php selected( bb_get_option('bb_polldaddy_permission'), 'use_keys' ); ?>><?php _e('Key Master', 'polldaddy-for-bbpress'); ?></option>
				</select>
				<p><?php _e('This option indicates the minimum level that a user must have to be able to add polls.', 'polldaddy-for-bbpress'); ?></p>
			</div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'options-polldaddy-update' ); ?>
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', 'polldaddy-for-bbpress') ?>" />
	</fieldset>
</form>

<?php
}

function bb_polldaddy_admin_page_process()
{
	if ( !empty($_GET['updated']) ) {
		bb_admin_notice( __('PollDaddy options saved.', 'polldaddy-for-bbpress') );
	}

	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && isset($_POST['submit'])) {
		bb_check_admin_referer( 'options-polldaddy-update' );

		// Deal with checkbox when it isn't checked
		if (!isset($_POST['bb_polldaddy_first_only'])) {
			$_POST['bb_polldaddy_first_only'] = false;
		}

		foreach ( (array) $_POST as $option => $value ) {
			if ( !in_array( $option, array('_wpnonce', '_wp_http_referer', 'submit') ) ) {
				$option = trim( $option );
				$value = is_array( $value ) ? $value : trim( $value );
				$value = stripslashes_deep( $value );
				if ( $value ) {
					bb_update_option( $option, $value );
				} else {
					bb_delete_option( $option );
				}
			}
		}

		$goback = add_query_arg('updated', 'true', wp_get_referer());
		bb_safe_redirect($goback);
	}
}
?>