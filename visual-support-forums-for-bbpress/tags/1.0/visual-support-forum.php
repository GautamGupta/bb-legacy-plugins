<?php
/*
Plugin Name: Visual Support Forums
Plugin URI: http://www.network.net.au/bbpress/plugins/support-forum/visual-support-forum.latest.zip
Description: This plugin adds some icons before topic names to indicate support/closed status, requires the Support Forums plugin by Aditya Naik.
Author: Sam Bauers
Version: 1.0
Author URI: http://www.network.net.au/

Version History:
1.0		: Initial Release
*/

/*
Visual Support Forums for bbPress version 1.0
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

if ( function_exists('support_forum_check') && support_forum_check()) {

	// Add our own handling of the support forum dropdown
	remove_action('topicmeta','support_forum_show_support_dropdown');
	add_action('topicmeta','visual_support_forum_show_support_dropdown');
	
	// Add whether the topic is closed here
	function visual_support_forum_show_support_dropdown() {
		?>
		<li id="resolution-flipper"><?php _e('This topic is') ?> <?php visual_support_forum_topic_resolved(); ?></li>
		<?php
		global $topic;
		if ( '0' === $topic->topic_open ) {
		?>
		<li><?php _e('This topic is') ?> <img src="<?php bb_option('uri') ?>my-plugins/visual-support-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> <?php _e('closed'); ?></li>
		<?php
		}
	}
	
	// Almost clone of orginal in support forum plugin
	function visual_support_forum_topic_resolved( $yes = '', $no = '', $mu = '', $id = 0 ) {
		global $bb_current_user, $topic;
		if ( empty($yes) )
			$yes = __('resolved');
		if ( empty($no) )
			$no = __('not resolved');
		if ( empty($mu) )
			$mu = __('not a support question');
		if ( bb_current_user_can( 'edit_topic', $topic->topic_id ) ) :
			$resolved_form  = '<form id="resolved" method="post" style="display:inline;" ><div style="display:inline;">' . "\n";
			$resolved_form .= '<input type="hidden" name="action" value="support_forum_post_process" />' . "\n";
			$resolved_form .= '<input type="hidden" name="id" value="' . $topic->topic_id . "\" />\n";
			$resolved_form .= '<select name="resolved" id="resolvedformsel" tabindex="2">' . "\n";

			$cases = array( 'yes', 'no', 'mu' );
			$resolved = support_forum_get_topic_resolved( $id );

			foreach ( $cases as $case ) {
				$selected = ( $case == $resolved ) ? ' selected="selected"' : '';
				$resolved_form .= "<option value=\"$case\"$selected>${$case}</option>\n";
			}

			$resolved_form .= "</select>\n";
			$resolved_form .= '<input type="submit" name="submit" id="resolvedformsub" value="'. __('Change') .'" />' . "\n</div>";
			echo $resolved_form;
			bb_nonce_field( 'support-forum-resolve-topic_' . $topic->topic_id );
			echo "\n</form>";
		else:
			// Only changes are here...
			// Override original to add images into the normal view
			$status = support_forum_get_topic_resolved( $id );
			echo '<img src="' . bb_get_option('uri') . 'my-plugins/visual-support-' . $status . '.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> ';
			echo $$status;
		endif;
	}

	// Just in case you dont have the categories patch installed - that's most people BTW, but not me
	if (!function_exists('is_category')) {
		function is_category() {
			return false;
		}
	}

	add_filter( 'topic_title', 'visual_support_forum_topic_title', 40);

	function visual_support_forum_topic_title($title) {
		if ( is_forum() || is_category() || is_front() || is_view() ) :
			if ( empty($yes) )
				$yes = __('resolved');
			if ( empty($no) )
				$no = __('not resolved');
			if ( empty($mu) )
				$mu = __('not a support question');
			// Gets the status of the topic, or the defualt if none
			$status = support_forum_get_topic_resolved( $id );
			// If status is not one of the three above or the default, then make it the default
			if (!$$status) {
				$status = support_forum_get_default_status();
			}
			$status_image = '<img src="' . bb_get_option('uri') . 'my-plugins/visual-support-' . $status . '.png" alt="[' . $$status . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />';
			$title = $status_image . $title;
		endif;
	
		return $title;
	}

	remove_filter('topic_title', 'closed_title', 30);
	add_filter('topic_title', 'visual_support_forum_closed_title', 30);

	function visual_support_forum_closed_title( $title ) {
		if ( is_forum() || is_category() || is_front() || is_view() ) {
			global $topic;
			if ( '0' === $topic->topic_open ) {
				return sprintf(__('<img src="' . bb_get_option('uri') . 'my-plugins/visual-support-closed.png" alt="[' . __('closed') . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />%s'), $title);
			}
		}
		
		return $title;
	}
}
?>