<?php
/*
Plugin Name: bbPM
Plugin URI: http://nightgunner5.wordpress.com/tag/bbpm/
Description: Adds the ability for users of a forum to send private messages to each other.
Version: 1.1
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
Text Domain: bbpm
Domain Path: /translations
*/

/**
 * @package bbPM
 * @version 1.1
 * @author Nightgunner5
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3 or higher
 */

load_plugin_textdomain( 'bbpm', false, 'translations' );

class bbPM {
	var $pm_status_id     = '_bbpm_pm';

	var $allowed_viewer_key = '_bbpm_allowed_viewer';

	/** Legacy PHP support */
	function bbPM() { $this->__construct(); }

	function __construct() {
		// Register content statuses
		add_action( 'bbp_register_post_statuses',   array( $this, 'register_post_statuses'   ), 10, 0 );

		// Enforce viewing rights
		add_action( 'bbp_map_meta_caps',            array( $this, 'map_meta_caps'            ), 10, 4 );

		// Add "Users with access:" text
		add_filter( 'the_content',                  array( $this, 'add_user_names'           ), 10, 1 );

		// Add CSS on PM pages
		add_action( 'bbp_head',                     array( $this, 'header'                   ), 10, 0 );

		// Fix invisible first message
		add_filter( 'bbp_show_lead_topic',          array( $this, 'show_lead_topic'          ), 10, 1 );
	}

	function is_pm() {
		if ( !is_single() )
			return false;

		global $post;
		if ( !$post || $post->post_status != $this->pm_status_id )
			return false;

		return true;
	}

	function register_post_statuses() {
		// PM status to allow permissions to be assigned to individual threads
		$status = apply_filters( 'bbpm_register_pm_post_status', array(
			'label'                     => __( 'PM', 'bbpm' ),
			'label_count'               => _nx_noop( 'PM <span class="count">(%s)</span>', 'PMs <span class="count">(%s)</span>', 'bbpm' ),
			'private'                   => true,
			'exclude_from_search'       => true,
			'capability_type'           => 'topic',
			'show_in_admin_status_list' => false,
			'show_in_admin_all_list'    => false
		) );
		register_post_status( $this->pm_status_id, $status );
	}

	function map_meta_caps( $caps, $cap, $user_id, $args ) {
		switch ( $cap ) {
			case 'read_reply' :
				if ( $post = get_post( $args[0] ) )
					$args[0] = $post->post_parent;

				// Fallthrough
			case 'read_topic' :

				if ( ( $post = get_post( $args[0] ) ) && $post->post_status == $this->pm_status_id ) {
					$caps      = array();
					$post_type = get_post_type_object( $post->post_type );

					$allowed_viewers = $this->get_thread_participants( $post->ID );

					if ( in_array( (int) $user_id, $allowed_viewers ) )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
				}

				break;
		}

		return $caps;
	}

	/**
	 * Get the user IDs of anyone allowed to participate in a given thread.
	 */
	function get_thread_participants( $thread_ID ) {
		if ( !( $thread = get_post( $thread_ID ) ) || $thread->post_status != $this->pm_status_id )
			return array();

		$participants = array_map( 'intval', get_post_meta( $thread->ID, $this->allowed_viewer_key ) );

		sort( $participants, SORT_NUMERIC );

		return apply_filters( 'bbpm_get_thread_participants', $participants, $thread_ID );
	}

	function add_user_names( $content ) {
		if ( $this->is_pm() ) {
			global $post;
			$participants = $this->get_thread_participants( $post->ID );

			$user_links = array_map( 'bbp_get_user_profile_link', $participants );

			$content .= '<p class="entry-meta">' . sprintf( __( 'Users with access: %s', 'bbpm' ), implode( ', ', $user_links ) ) . '</p>';
		}

		return $content;
	}

	function header() {
		if ( !$this->is_pm() )
			return;
?>
		<style type="text/css">.<?php echo bbp_get_topic_post_type(); ?> .entry-utility { display: none; }</style>
<?php
	}

	function show_lead_topic( $show_lead = false ) {
		return $show_lead || $this->is_pm();
	}

	function find_user( $string ) {
		$user_id = 0;

		if ( is_email( $string ) ) {
			$user = get_user_by( 'email', $string );
			if ( $user )
				$user_id = $user->ID;
		} elseif ( is_numeric( $string ) ) {
			$user = get_user_by( 'id', $string );
			if ( $user )
				$user_id = $user->ID;
		} else {
			$user = get_user_by( 'login', $string );
			if ( $user )
				$user_id = $user->ID;
		}

		return $user_id;
	}

	function handle_in_forum( $translated, $untranslated, $group ) {
		if ( $untranslated == 'in: <a href="%1$s">%2$s</a>' && $group == 'bbpress' )
			return '';

		return $translated;
	}

	function handle_inbox() {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			switch ( $_POST['action'] ) {
				case 'bbpm-new-thread':
					$this->handle_new_pm_thread();
					break;
			}
		}

		add_filter( 'gettext', array( &$this, 'handle_in_forum' ), 10, 3 );
	}

	function handle_new_pm_thread() {
		global $bbp;

		// Nonce check
		check_admin_referer( 'bbpm-new-thread' );

		// Check users ability to create new thread
		if ( bbp_is_anonymous() || !current_user_can( 'publish_topics' ) )
			$bbp->errors->add( 'bbp_topic_permissions', __( '<strong>ERROR</strong>: You do not have permission to send new private messages.', 'bbpm' ) );

		$topic_author  = bbp_get_current_user_id();
		$anonymous_data = false;

		// Remove wp_filter_kses filters from title and content for capable users and if the nonce is verified
		if ( current_user_can( 'unfiltered_html' ) && !empty( $_POST['_bbpm_unfiltered_html_thread'] ) && wp_create_nonce( 'bbpm-unfiltered-html-thread_new' ) == $_POST['_bbpm_unfiltered_html_thread'] ) {
			remove_filter( 'bbp_new_topic_pre_title',   'wp_filter_kses' );
			remove_filter( 'bbp_new_topic_pre_content', 'wp_filter_kses' );
		}

		// Handle Title
		if ( empty( $_POST['bbp_topic_title'] ) || !$topic_title = esc_attr( strip_tags( $_POST['bbp_topic_title'] ) ) )
			$bbp->errors->add( 'bbp_topic_title', __( '<strong>ERROR</strong>: Your message needs a title.', 'bbpm' ) );

		$topic_title = apply_filters( 'bbp_new_topic_pre_title', $topic_title );

		// Handle Content
		if ( empty( $_POST['bbp_topic_content'] ) || !$topic_content = $_POST['bbp_topic_content'] )
			$bbp->errors->add( 'bbp_topic_content', __( '<strong>ERROR</strong>: Your message needs some content.', 'bbpm' ) );

		$topic_content = apply_filters( 'bbp_new_topic_pre_content', $topic_content );

		// Check for flood
		if ( !bbp_check_for_flood( $anonymous_data, $topic_author ) )
			$bbp->errors->add( 'bbp_topic_flood', __( '<strong>ERROR</strong>: Slow down; you move too fast.', 'bbpress' ) );

		// Check for duplicate
		if ( !bbp_check_for_duplicate( array( 'post_type' => bbp_get_topic_post_type(), 'post_author' => $topic_author, 'post_content' => $topic_content, 'anonymous_data' => $anonymous_data ) ) )
			$bbp->errors->add( 'bbp_topic_duplicate', __( '<strong>ERROR</strong>: Duplicate message detected; it looks as though you&#8217;ve already said that!', 'bbpm' ) );

		// Give users access!
		$to = array( $topic_author );
		$_to = array_map( 'trim', explode( ',', strip_tags( $_POST['bbpm_send_to'] ) ) );

		foreach ( $_to as $user ) {
			if ( $user_id = $this->find_user( $user ) ) {
				$to[] = $user_id;
			} else {
				$bbp->errors->add( 'bbpm_cant_find_user', __( '<strong>ERROR</strong>: Some of the usernames in the To: field couldn\'t be parsed.', 'bbpm' ) );
				break;
			}
		}

		$to = array_filter( array_unique( $to ) );

		// Handle insertion into posts table
		if ( !is_wp_error( $bbp->errors ) || !$bbp->errors->get_error_codes() ) {

			// Add the content of the form to $post as an array
			$topic_data = array(
				'post_author'  => $topic_author,
				'post_title'   => $topic_title,
				'post_content' => $topic_content,
				'post_status'  => $this->pm_status_id,
				'post_type'    => bbp_get_topic_post_type()
			);

			// Insert reply
			$topic_id = wp_insert_post( $topic_data );

			// Check for missing topic_id or error
			if ( !empty( $topic_id ) && !is_wp_error( $topic_id ) ) {

				foreach ( $to as $user ) {
					add_post_meta( $topic_id, $this->allowed_viewer_key, $user );
				}

				// Update counts, etc...
				do_action( 'bbpm_new_thread', $topic_id,    $to,                  $topic_author );
				do_action( 'bbp_new_topic',   $topic_id, 0,      $anonymous_data, $topic_author );

				// Redirect back to new reply
				wp_redirect( bbp_get_topic_permalink( $topic_id ) . '#topic-' . $topic_id );

				// For good measure
				exit();

			// Errors to report
			} else {
				$append_error = ( is_wp_error( $topic_id ) && $topic_id->get_error_message() ) ? $topic_id->get_error_message() . ' ' : '';
				$bbp->errors->add( 'bbp_topic_error', __( '<strong>ERROR</strong>: The following problem(s) have been found with your topic:' . $append_error, 'bbpress' ) );
			}
		}
	}
}

/**
 * @global bbPM $bbPM
 */
$bbPM = new bbPM;