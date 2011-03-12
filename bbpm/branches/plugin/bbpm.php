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

load_plugin_textdomain( 'bbpm', dirname( __FILE__ ) . '/translations' );

class bbPM
{
	var $thread_post_type = '_bbpm_thread';
	var $pm_status_id     = '_bbpm_pm';
	
	var $thread_slug = 'pm/view';

	var $allowed_viewer_key = '_bbpm_allowed_viewer';

	/** Legacy PHP support */
	function bbPM() { $this->__construct(); }

	function __construct() {
		// Register content types
		add_action( 'bbp_register_post_types',      array( $this, 'register_post_types'      ), 10, 0 );

		// Enforce viewing rights
		add_action( 'bbp_map_meta_caps',            array( $this, 'map_meta_caps'            ), 10, 4 );

		// Skip moderation for replies to PM threads
		add_action( 'wp_insert_comment',            array( $this, 'insert_reply'             ), 10, 2 );

		// Add "Users with access:" text
		add_filter( 'the_content',                  array( $this, 'add_user_names'           ), 10, 1 );

		// Add CSS on PM pages
		add_action( 'bbp_head',                     array( $this, 'header'                   ), 10, 0 );

		// Change "Post Comment" to "Post Reply" on PM pages
		add_filter( 'comment_form_defaults',        array( $this, 'fix_comment_text'         ), 10, 1 );
	}

	function is_pm() {
		if ( !is_single() )
			return false;

		global $post;
		if ( !$post || $post->post_type != $this->thread_post_type )
			return false;

		return true;
	}

	function register_post_types() {
		// Thread labels
		$thread['labels'] = array(
			'name'               => __( 'PMs',                      'bbpm' ),
			'singular_name'      => __( 'PM',                       'bbpm' ),
			'add_new'            => __( 'New PM',                   'bbpm' ),
			'add_new_item'       => __( 'Create New PM',            'bbpm' ),
			'edit'               => __( 'Edit',                     'bbpm' ),
			'edit_item'          => __( 'Edit PM',                  'bbpm' ),
			'new_item'           => __( 'New PM',                   'bbpm' ),
			'view'               => __( 'View PM',                  'bbpm' ),
			'view_item'          => __( 'View PM',                  'bbpm' ),
			'search_items'       => __( 'Search PMs',               'bbpm' ),
			'not_found'          => __( 'No PMs found',             'bbpm' ),
			'not_found_in_trash' => __( 'No PMs found in Trash',    'bbpm' ),
			'parent_item_colon'  => __( 'Inbox:',                   'bbpm' )
		);

		// Thread rewrite
		$thread['rewrite'] = array(
			'slug'       => $this->thread_slug,
			'with_front' => false
		);

		// Thread supports
		$thread['supports'] = array(
			'title',
//			'editor',
			'author',
//			'revisions',
			'comments'
		);

		// Topic Filter
		$thread = apply_filters( 'bbpm_register_thread_post_type', array(
			'labels'            => $thread['labels'],
			'rewrite'           => $thread['rewrite'],
			'supports'          => $thread['supports'],
			'capabilities'      => bbp_get_topic_caps(),
			'capability_type'   => 'topic',
			'menu_position'     => '100',
			'public'            => true,
			'show_in_nav_menus' => false,
			'show_ui'           => false,
			'can_export'        => true,
			'hierarchical'      => false,
			'query_var'         => true,
			'menu_icon'         => ''
		) );

		// Register Thread content type
		register_post_type( $this->thread_post_type, $thread );

		// PM status to allow permissions to be assigned to individual threads
		$status = apply_filters( 'bbpm_register_pm_post_status', array(
			'label'                     => __( 'PM', 'bbpm' ),
			'label_count'               => _nx_noop( 'PM <span class="count">(%s)</span>', 'PMs <span class="count">(%s)</span>', 'bbpm' ),
			'private'                   => true,
			'exclude_from_search'       => true,
			'capability_type'           => 'topic',
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true
		) );
		register_post_status( $this->pm_status_id, $status );
	}

	function map_meta_caps( $caps, $cap, $user_id, $args ) {
		switch ( $cap ) {
			case 'read_topic' :
			case 'read_reply' :

				if ( ( $post = get_post( $args[0] ) ) && $post->post_type == $this->thread_post_type ) {
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

	function get_thread_participants( $thread_ID ) {
		if ( !( $thread = get_post( $thread_ID ) ) || $thread->post_type != $this->thread_post_type )
			return array();

		$participants = get_post_meta( $thread->ID, $this->allowed_viewer_key );
		$participants[] = (int) $thread->post_author;

		sort( $participants, SORT_NUMERIC );

		return apply_filters( 'bbpm_get_thread_participants', $participants, $thread_ID );
	}

	function insert_reply( $id, $reply ) {
		if ( !( $thread = get_post( $reply->comment_post_ID ) ) || $thread->post_type != $this->thread_post_type )
			return;

		if ( $reply->comment_status == '0' )
			wp_set_comment_status( $id, '1' );
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
?>
		<style type="text/css">.<?php echo $this->thread_post_type; ?> .entry-utility { display: none; }</style>
<?php
	}

	function fix_comment_text( $defaults ) {
		if ( $this->is_pm() )
			$defaults['label_submit'] = __( 'Post Reply', 'bbpm' );

		return $defaults;
	}
}

/**
 * @global bbPM $bbPM
 */
$bbPM = new bbPM;