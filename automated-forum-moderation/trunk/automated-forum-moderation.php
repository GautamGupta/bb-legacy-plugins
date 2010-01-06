<?php
/*
Plugin Name: Automated Forum Moderation
Description: Blocks common (and sometimes accidental) human-generated spam automatically.
Plugin URI: http://nightgunner5.wordpress.com/tag/automated-forum-moderation/
Author: Ben L.(Nightgunner5)
Author URI: http://nightgunner5.wordpress.com/
Version: 0.3.1
*/

$automated_forum_moderation_data = array(
	'max_days' => 30, // Maximum time since the last post in a topic to allow posting
	'allow_double_post' => false, // Allow posting twice (false, true, or an array of forum IDs to allow it in)
	'allow_double_post_after' => 60, //the number of minutes since the previous post by that user (if the user is the last poster on a topic) that a new post can be made.  false if not applicable
	'min_words' => 2, // Minimum words in a post
	'min_chars' => 5 // Minimum characters in a post
);

########################################
# Most users should stop editing here. #
########################################

if ( !$automated_forum_moderation_data['allow_double_post_after'] )
	$automated_forum_moderation_data['allow_double_post_after'] = 2147483647; // Huge number (about 4000 years) used to prevent extra code bloat.

function automated_forum_moderation_initial_blocking( $retvalue, $capability, $args ) {
	global $bb_post, $automated_forum_moderation_data;

	if ( $capability == 'write_post' ) {
		if ( ( !$automated_forum_moderation_data['allow_double_post'] && $bb_post->poster_id == bb_get_current_user_info( 'ID' ) && ( ( bb_current_time( 'timestamp' ) - bb_gmtstrtotime( $bb_post->post_time ) ) / 60 ) < $automated_forum_moderation_data['allow_double_post_after'] ) || // Double posting is not allowed
			( is_array( $automated_forum_moderation_data['allow_double_post'] ) && ( !in_array( $bb_post->forum_id, $automated_forum_moderation_data['allow_double_post'] ) || ( ( bb_current_time( 'timestamp' ) - bb_gmtstrtotime( $bb_post->post_time ) ) / 60 ) < $automated_forum_moderation_data['allow_double_post_after'] ) && $bb_post->poster_id == bb_get_current_user_info( 'ID' ) ) || // Double posting is allowed in certain forums
			(int)( ( bb_current_time( 'timestamp' ) - bb_gmtstrtotime( $bb_post->post_time ) ) / 86400 ) > $automated_forum_moderation_data['max_days'] && !bb_current_user_can( 'close_topic', get_topic_id() ) ) // Topic is old
				return false;
	}
	return $retvalue;
}

function automated_forum_moderation_message() {
	global $bb_post, $automated_forum_moderation_data;
	if ( !empty( $bb_post ) && (int)( ( bb_current_time( 'timestamp' ) - bb_gmtstrtotime( $bb_post->post_time ) ) / 86400 ) > 30 ) {
		if ( bb_current_user_can( 'close_topic', get_topic_id() ) ) {?>
	<p><?php _e( 'This topic is old. It has been automatically closed to new replies. However, if you feel there is a need for this topic to be re-opened, you can post here.', 'automated-forum-moderation' ); ?></p>
<?php } else { ?>
	<p><?php _e( 'This topic is old. It has been automatically closed to new replies.', 'automated-forum-moderation' ); ?></p>
<?php }
	} elseif ( $bb_post->poster_id == bb_get_current_user_info( 'ID' ) ) { ?>
	<p><?php _e( 'The last post on this topic is your own. Please wait until someone else replies to post again.', 'automated-forum-moderation' ); ?></p>
<?php }
}

function automated_forum_moderation_jit_blocking( $post_text, $post_id = null, $topic_id = null ) { // JIT = Just In Time
	if ( ( !$post_id && !$topic_id ) || // Other plugins might use the pre_post filter
		( $_POST['post_id'] && bb_current_user_can( 'edit_post', $_POST['post_id'] ) ) ) // Don't bother if the post is just being edited.
		return $post_text;

	global $bbdb, $automated_forum_moderation_data;
	$last_post_in_topic = $bbdb->get_row( $bbdb->prepare( 'SELECT `post_id`, `poster_id`, `post_time`, `forum_id` FROM `' . $bbdb->posts . '` WHERE `topic_id` = %s AND `post_status` = 0 ORDER BY `post_time` DESC, `post_position` ASC LIMIT 1', $topic_id ) ); // Only get what we need

	if ( $last_post_in_topic ) {
		$last_post_time = $last_post_in_topic->post_time;
		$last_post_is_current_user = $last_post_in_topic->poster_id == bb_get_current_user_info( 'ID' );
		$forum_id = $last_post_in_topic->forum_id;
		if ( (int)( ( bb_current_time( 'timestamp' ) - bb_gmtstrtotime( $last_post_time ) ) / 86400 ) > $automated_forum_moderation_data['max_days'] && !bb_current_user_can( 'close_topic', $topic_id ) ) {
			bb_die( __( 'This topic is old. It has been automatically closed to new replies.', 'automated-forum-moderation' ) );
		}
		if ( $last_post_is_current_user && $automated_forum_moderation_data['allow_double_post'] !== true && $automated_forum_moderation_data['allow_double_post_after'] > ( ( bb_current_time( 'timestamp' ) - bb_gmtstrtotime( $last_post_time ) ) / 60 ) ) {
			if (!$automated_forum_moderation_data['allow_double_post'] || !in_array( $forum_id, $automated_forum_moderation_data['allow_double_post'] ) ) {
				bb_die( __( 'The last post on this topic is your own. Please wait until someone else replies to post on this topic again.', 'automated-forum-moderation' ) );
			}
		}
		if ( ( $words = count( preg_split( '/\s+/', $post_text ) ) ) < $automated_forum_moderation_data['min_words'] ) {
			bb_die( '<style type="text/css">.last{display:none}</style>' . sprintf( __( 'Your post is looking a little skimpy! The forum requires you to have at least %1$d words, but you only have %2$d.</p><p>Push the back button in your browser.', 'automated-forum-moderation' ), $automated_forum_moderation_data['min_words'], $words ) );
		}
		if ( ( $chars = strlen( strip_tags( $post_text ) ) ) < $automated_forum_moderation_data['min_chars'] ) {
			bb_die( '<style type="text/css">.last{display:none}</style>' . sprintf( __( 'Your post is looking a little skimpy! The forum requires you to have at least %1$d characters in your post, but you only have %2$d.</p><p>Push the back button in your browser.', 'automated-forum-moderation' ), $automated_forum_moderation_data['min_chars'], $chars ) );
		}
	} else {
		if ( ( $words = count( preg_split( '/\s+/', $post_text ) ) ) < $automated_forum_moderation_data['min_words'] ) {
			$bbdb->query( $bbdb->prepare( 'DELETE FROM `'.$bbdb->topics.'` WHERE `topic_id` = %s LIMIT 1', $topic_id));
			bb_die( '<style type="text/css">.last{display:none}</style>' . sprintf( __( 'Your post is looking a little skimpy! The forum requires you to have at least %1$d words, but you only have %2$d.</p><p>Push the back button in your browser.', 'automated-forum-moderation' ), $automated_forum_moderation_data['min_words'], $words ) );
		}
		if ( ( $chars = strlen( strip_tags( $post_text ) ) ) < $automated_forum_moderation_data['min_chars'] ) {
			$bbdb->query( $bbdb->prepare( 'DELETE FROM `'.$bbdb->topics.'` WHERE `topic_id` = %s LIMIT 1', $topic_id));
			bb_die( '<style type="text/css">.last{display:none}</style>' . sprintf( __( 'Your post is looking a little skimpy! The forum requires you to have at least %1$d characters in your post, but you only have %2$d.</p><p>Push the back button in your browser.', 'automated-forum-moderation' ), $automated_forum_moderation_data['min_chars'], $chars ) );
		}
	}
	return $post_text;
}

if ( is_topic() ) {
	add_filter( 'bb_current_user_can', 'automated_forum_moderation_initial_blocking', 10, 3 );
	add_action( 'pre_post_form', 'automated_forum_moderation_message' );
}
add_filter( 'pre_post', 'automated_forum_moderation_jit_blocking', 10, 3 );

?>