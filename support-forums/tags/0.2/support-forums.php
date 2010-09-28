<?php
/*
Plugin Name: Support Forums
Plugin URI: http://bbpress.org/plugins/topic/support-forums/
Description: Turns any number of forums into support forums, where users can mark topics as resolved, not resolved or not a support question. Based on <a href="http://www.adityanaik.com/">so1o</a>, <a href="http://blogwaffe.com/">mdawaffe</a> and <a href="http://profiles.wordpress.org/users/sambauers">SamBauers</a>' <a href="http://bbpress.org/plugins/topic/support-forum/">Support Forum</a> plugin.
Version: 0.2
Author: so1o, mdawaffe, SamBauers, mr_pelle
Author URI: http://scr.im/mrpelle
*/

/**
 * @license CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
 * @license Copyright (C) 2007 Aditya Naik (http://www.adityanaik.com/)
 * @license Copyright (C) 2007 Sam Bauers (http://unlettered.org/)
 */


/**
 * Define constants
 */
define( 'SUPPORT_FORUMS_ID',                             'support-forums' );
define( 'SUPPORT_FORUMS_NAME',                           'Support Forums' );
define( 'SUPPORT_FORUMS_ICONS_URI',                      bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ) . 'icons/' );
define( 'SUPPORT_FORUMS_ACTIVE_TEMPLATE_IMAGES_URI',     bb_get_active_theme_uri() . 'images/' );
define( 'SUPPORT_FORUMS_VIEWS_IGNORE_STICKIES_PRIORITY', false ); // Set this to true to make support views override stickies priority order


// Create text domain for translations
bb_load_plugin_textdomain( SUPPORT_FORUMS_ID, dirname( __FILE__ ) . '/languages' );


/**
 * Wrapper class for plugin settings
 */
class Support_Forums_Settings {
	/**
	 * List of support forums ids
	 *
	 * @var array
	 */
	var $forums;

	/**
	 * New support topics default status
	 *
	 * @var string
	 */
	var $defaultStatus;

	/**
	 * List of pairs (support statuses, their display statements)
	 *
	 * @var array
	 */
	var $statuses;

	/**
	 * List of enabled support views
	 *
	 * @var array
	 */
	var $views;

	/**
	 * List of enabled icons and their directory (if different from the default one)
	 *
	 * @var array
	 */
	var $icons;

	/**
	 * Whether or not users can set topic support status on creation
	 *
	 * @var boolean
	 **/
	var $posterSetable;

	/**
	 * Whether or not support topic poster can change its status at anytime
	 *
	 * @var boolean
	 */
	var $posterChangeable;


	/**
	 * Class constructor
	 */
	function Support_Forums_Settings() {
		$settings = bb_get_option( 'support_forums_settings' );

		// Define support statuses whether or not plugin is enabled
		$this->statuses = array(
			'not_support' =>  __( 'not a support question', SUPPORT_FORUMS_ID ),
			'not_resolved' => __( 'not resolved', SUPPORT_FORUMS_ID ),
			'resolved' =>     __( 'resolved', SUPPORT_FORUMS_ID )
		);

		if ( !is_array( $settings ) ) { // Plugin is disabled
			$this->forums = false;
		} else { // Get plugin settings
			$this->forums = ( is_array( $settings['forums'] ) ) ? // Get support forums ids list
				$settings['forums'] :
				false;

			$this->defaultStatus = (string) $settings['default_status'];

			if ( empty( $this->defaultStatus ) ) // Should never happen
				$this->defaultStatus = 'not_support';

			$this->views =  ( is_array( $settings['views'] ) ) ? // Get views list
				$settings['views'] :
				false;

			$this->icons =  ( is_array( $settings['icons'] ) ) ? // Get icons list and directory
				$settings['icons'] :
				false;

			$this->posterSetable = (bool) $settings['poster_setable'];

			$this->posterChangeable = (bool) $settings['poster_changeable'];
		}
	}


	/**
	 * Functions
	 */

	/**
	 * Whether or not plugin is enabled
	 *
	 * @return boolean
	 */
	function isEnabled() {
		return is_array( $this->forums );
	}

	/**
	 * Whether or not views are enabled
	 *
	 * @return boolean
	 */
	function areViewsEnabled() {
		return is_array( $this->views );
	}

	/**
	 * Whether or not icons are enabled
	 *
	 * @return boolean
	 */
	function areIconsEnabled() {
		return is_array( $this->icons );
	}

	/**
	 * Whether or not users can set topic support status on creation
	 *
	 * @return boolean
	 */
	function isStatusSetable() {
		if ( false === (bool) $this->isEnabled() )
			return false;

		return (bool) $this->posterSetable;
	}

	/**
	 * Whether or not current user can change topic support status
	 *
	 * @global $topic
	 *
	 * @uses bb_current_user_can()
	 * @uses bb_get_current_user_info()
	 * @uses apply_filters()
	 *
	 * @return boolean
	 */
	function isStatusChangeable() {
		if ( false === (bool) $this->isEnabled() )
			return false;

		if ( !is_topic() )
			return false;

		global $topic;

		if ( bb_current_user_can( 'edit_others_topics', $topic->topic_id ) ) // Super-users override plugin settings
			return true;

		if ( $topic->topic_poster != bb_get_current_user_info( 'id' ) ) // Current user is not topic poster
			return false;

		if ( false === (bool) $this->isStatusSetable() ) // We did not want users to set topic support status on creation, neither do we want them to change it afterwards
			return false;

		return (bool) apply_filters( 'support_forums_poster_changeable', $this->posterChangeable, $topic->topic_id );
	}

	/**
	 * Return topic support status
	 *
	 * @global $topic
	 *
	 * @return string
	 */
	function getTopicStatus() {
		if ( false === (bool) $this->isEnabled() )
			return;

		global $topic;

		if ( !in_array( $topic->forum_id, $this->forums ) ) // Topic parent forum is not a support forum
			return;

		return ( !empty( $topic->topic_support_status ) ) ? $topic->topic_support_status : $this->defaultStatus;
	}

	/**
	 * Echo support topic additional meta
	 *
	 * @global $topic
	 *
	 * @return void
	 */
	function supportTopicMeta() {
		if ( false === (bool) $this->isEnabled() )
			return;

		global $topic;

		if ( true === (bool) $this->areIconsEnabled() ) { // Add closed and sticky statements if icons are enabled
			$icons_uri = ( array_key_exists( 'dir', $this->icons ) ) ? SUPPORT_FORUMS_ACTIVE_TEMPLATE_IMAGES_URI . $this->icons['dir'] : SUPPORT_FORUMS_ICONS_URI;

			if ( $topic->topic_sticky && in_array( 'sticky', $this->icons ) ) // Add sticky statement
				$meta .= sprintf(
					'<li>%1$s <img src="%2$s-%3$s.png" alt="" title="%4$s" style="vertical-align: middle;" /> %4$s</li>%5$s',
					__( 'This topic is', SUPPORT_FORUMS_ID ),
					$icons_uri . SUPPORT_FORUMS_ID,
					'sticky',
					__( 'sticky', SUPPORT_FORUMS_ID ),
					"\n"
				);

			if ( !$topic->topic_open && in_array( 'closed', $this->icons ) ) // Add closed statement
				$meta .= sprintf(
					'<li>%1$s <img src="%2$s-%3$s.png" alt="" title="%4$s" style="vertical-align: middle;" /> %4$s</li>%5$s',
					__( 'This topic is', SUPPORT_FORUMS_ID ),
					$icons_uri . SUPPORT_FORUMS_ID,
					'closed',
					__( 'closed', SUPPORT_FORUMS_ID ),
					"\n"
				);

			// Echo $meta now, because $this->topicStatusDisplay() echoes too
			echo $meta;
		}

		if ( in_array( $topic->forum_id, $this->forums ) ) { // Topic parent forum is a support forum
			printf( '<li id="support-status">%s ', __( 'This topic is', SUPPORT_FORUMS_ID ) );

			$this->topicStatusDisplay();

			echo '</li>' . "\n";
		}
	}

	/**
	 * Echo support statuses dropdown menu | topic support status statement
	 *
	 * @return void
	 */
	function topicStatusDisplay() {
		if ( false === (bool) $this->isEnabled() )
			return;

		if ( false === (bool) $this->isStatusChangeable() ) // Users cannot change topic support status
			// Just display topic support status statement
			$this->statusStatement();
		else
			// Display support statuses dropdown menu
			$this->statusesDropdown();
	}

	/**
	 * Echo topic support status statement
	 *
	 * @return void
	 */
	function statusStatement() {
		if ( false === (bool) $this->isEnabled() )
			return;

		if ( !$topic_status = $this->getTopicStatus() ) // Topic is not a support topic
			return;

		if ( true === (bool) $this->areIconsEnabled() ) // Add icons, if enabled
			if ( in_array( 'status', $this->icons ) ) { // Add status icon if enabled
				$icons_uri = ( array_key_exists( 'dir', $this->icons ) ) ? SUPPORT_FORUMS_ACTIVE_TEMPLATE_IMAGES_URI . $this->icons['dir'] : SUPPORT_FORUMS_ICONS_URI;

				$statement .= sprintf(
					'<img src="%1$s-%2$s.png" alt="" title="%3$s" style="vertical-align: middle;" /> ',
					$icons_uri . SUPPORT_FORUMS_ID,
					str_replace( '_', '-', $topic_status ),
					$this->statuses[$topic_status]
				);
			}

		$statement .= $this->statuses[$topic_status];

		echo $statement;
	}

	/**
	 * Echo support statuses dropdown menu
	 *
	 * @global $topic
	 *
	 * @uses get_topic_link()
	 * @uses bb_nonce_field()
	 *
	 * @return void
	 */
	function statusesDropdown() {
		if ( false === (bool) $this->isEnabled() )
			return;

		global $topic;

		if ( !$topic_status = $this->getTopicStatus() ) // Topic is not a support topic
			return;

		if ( true === (bool) $this->areIconsEnabled() ) // Add icons, if enabled
			if ( in_array( 'status', $this->icons ) ) { // Add status icon if enabled
				$icons_uri = ( array_key_exists( 'dir', $this->icons ) ) ? SUPPORT_FORUMS_ACTIVE_TEMPLATE_IMAGES_URI . $this->icons['dir'] : SUPPORT_FORUMS_ICONS_URI;

				$menu .= sprintf(
					'<img src="%1$s-%2$s.png" alt="" title="%3$s" style="vertical-align: middle;" /> ',
					$icons_uri . SUPPORT_FORUMS_ID,
					str_replace( '_', '-', $topic_status ),
					$this->statuses[$topic_status]
				);
			}

		$menu .= '<form action="' . get_topic_link( $topic->topic_id ) . '" method="post" style="display: inline;"><div style="display: inline;">' . "\n";
		$menu .= '<select name="support_forums_statuses_dropdown" id="support-forums-statuses-dropdown" tabindex="2">' . "\n";

		// Add options to support statuses dropdown menu
		foreach ( $this->statuses as $status => $display ) {
			// Make current support status selected
			$selected = ( $status == $topic_status ) ? ' selected="selected"' : '';

			$menu .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>%4$s',
				$status,
				$selected,
				$display,
				"\n"
			);
		}

		$menu .= '</select>' . "\n";
		$menu .= '<input type="hidden" name="action" value="update-support-forums-topic-status" />' . "\n";

		// Echo $menu now, because bb_nonce_field() echoes too
		echo $menu;

		bb_nonce_field( 'support-forums-update-status-topic-' . $topic->topic_id );

		$menu = '<p class="submit" style="display: inline;"><input type="submit" name="submit" value="' . __( 'Update Status &raquo;', SUPPORT_FORUMS_ID ) . '" /></p>' . "\n"; // $menu gets resetted
		$menu .= '</div></form>' . "\n";

		// Echo new $menu
		echo $menu;
	}

	/**
	 * Process support statuses dropdown menu
	 *
	 * @global $topic
	 *
	 * @uses bb_check_admin_referer()
	 * @uses remove_query_arg()
	 * @uses add_query_arg()
	 * @uses bb_safe_redirect()
	 *
	 * @return void
	 */
	function statusesDropdownProcess() {
		if ( false === (bool) $this->isEnabled() )
			return;

		if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-support-forums-topic-status' && is_topic() ) {
			global $topic;

			bb_verify_nonce( 'support-forums-update-status-topic-' . $topic->topic_id );

			$goback = remove_query_arg( array( 'message' ), wp_get_referer() );

			if ( true === (bool) $this->isStatusChangeable() && $_POST['support_forums_statuses_dropdown'] )
				$goback = ( true === (bool) $this->setTopicStatus( $topic->topic_id, $_POST['support_forums_statuses_dropdown'] ) ) ?
					add_query_arg( 'message', 'support-forums-topic-status-updated', $goback ) :
					add_query_arg( 'message', 'update-support-forums-topic-status-error', $goback );

			bb_safe_redirect( $goback );
			exit;
		}
	}

	/**
	 * Add jQuery library to page head, if not yet included
	 *
	 * @uses is_front()
	 * @uses is_bb_tag()
	 * @uses wp_script_is()
	 * @uses wp_enqueue_script()
	 *
	 * @return void
	 */
	function addJQuery() {
		if ( false === (bool) $this->isEnabled() )
			return;

		if ( true === (bool) $this->isStatusSetable() && ( ( is_front() && $_GET['new'] ) || is_bb_tag() ) ) { // Script is necessary only when users can select topic destination forum
			if ( wp_script_is( 'jquery' ) ) // Nothing to do, since jQuery is already included in page head
				return;

			wp_enqueue_script( 'jquery' );
		}
	}

	/**
	 * Echo a jQuery script for support statuses dropdown menu visibility
	 *
	 * Call jQuery using a special handler to avoid namespace difficulties
	 *
	 * @link http://api.jquery.com/ready/ - "Aliasing the jQuery Namespace"
	 *
	 * @uses is_front()
	 * @uses is_bb_tag()
	 *
	 * @return void
	 */
	function newTopicStatusesDropdownJQuery() {
		if ( false === (bool) $this->isEnabled() )
			return;

		if ( true === (bool) $this->isStatusSetable() && ( ( is_front() && $_GET['new'] ) || is_bb_tag() ) ) { // Script is necessary only when user can select topic destination forum
			$script = <<<EOF
<!-- Support Forums dropdown menu jQuery -->
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready( function($) {
	var support_forums = [ %FORUMS% ];

	// Check current status
	checkStatusesDropdownVisibility();

	$( '#forum-id' ).change( function() { // Check status everytime destination forum changes
		checkStatusesDropdownVisibility();
	});


	/**
	 * Functions
	 */

	/**
	 * Hide support statuses dropdown menu if selected destination forum is not a support forum
	 *
	 * @returns void
	 */
	function checkStatusesDropdownVisibility() {
		if ( -1 == $.inArray( parseInt( $( '#forum-id' ).val() ), support_forums ) ) // parseInt() makes the trick
			$( '#post-form-support-status-container' ).hide();
		else
			$( '#post-form-support-status-container' ).show();
	}
});
/* ]]> */
</script>
EOF;
			echo str_replace( '%FORUMS%', implode( ', ', $this->forums ), $script );
		}
	}

	/**
	 * Echo new topic support statuses dropdown menu
	 *
	 * @global $forum
	 *
	 * @return void
	 */
	function newTopicStatusesDropdown() {
		if ( false === (bool) $this->isEnabled() )
			return;

		if ( !is_topic() && true === (bool) $this->isStatusSetable() ) {
			global $forum;

			if ( !$forum || in_array( $forum->forum_id, $this->forums ) ) { // Check if topic is being created from forum front page or forum is a support forum
				$menu .= '<p id="post-form-support-status-container">';
				$menu .= sprintf( '<label for="support-status">%s ', __( 'This topic is:', SUPPORT_FORUMS_ID ) );
				$menu .= '<select name="support_status" id="support-status">' . "\n";

				// Add option to new topic support statuses dropdown menu
				foreach ( $this->statuses as $status => $display ) {
					$selected = ( $status == $this->defaultStatus ) ? ' selected="selected"' : '';
					$menu .= sprintf( '<option value="%1$s"%1$s>%3$s</option>%4$s', $status, $selected, $display, "\n" );
				}

				$menu .= '</select>' . "\n";
				$menu .= '</label></p>' . "\n";

				echo $menu;
			}
		}
	}

	/**
	 * Process new topic support statuses dropdown menu
	 *
	 * @param int $post_id Post id
	 *
	 * @uses bb_get_post()
	 *
	 * @return boolean
	 */
	function newTopicStatusesDropdownProcess( $post_id ) {
		if ( false === (bool) $this->isEnabled() )
			return false;

		if ( true === (bool) $this->isStatusSetable() && $_POST['support_status'] ) {
			$topic_id = bb_get_post( $post_id )->topic_id;

			return $this->setTopicStatus( $topic_id, $_POST['support_status'] );
		}
	}

	/**
	 * Whether or not topic support status change was successful
	 *
	 * @param int $topic_id  Support topic id
	 * @param string $status Topic new support status
	 *
	 * @uses apply_filters()
	 * @uses bb_update_topicmeta()
	 *
	 * @return boolean
	 */
	function setTopicStatus( $topic_id = 0, $status ) {
		if ( false === (bool) $this->isEnabled() )
			return false;

		if ( !$topic_id )
			return false;

		// Apply filters, if any
		$status = apply_filters( 'support_forums_set_topic_status', $status, $topic_id );

		if ( !array_key_exists( $status, $this->statuses ) )
			return false;

		return bb_update_topicmeta( $topic_id, 'topic_support_status', $status );
	}

	/**
	 * Whether or not views register was successful
	 *
	 * @uses bb_register_view()
	 *
	 * @return boolean
	 */
	function registerSupportViews() {
		if ( false === (bool) $this->isEnabled() )
			return false;

		if ( false === (bool) $this->areViewsEnabled() )
			return false;

		// Temporary var for function return value
		$success = true;

		foreach ( $this->views as $view ) {
			$additional_meta_value = ( $this->defaultStatus == $view ) ? // Default support status view
				',NULL' : // Include topics with no Support Forums meta key set. No space between the comma and NULL!
				'';

			$query = array(
				'sticky' =>    ( true === (bool) SUPPORT_FORUMS_VIEWS_IGNORE_STICKIES_PRIORITY ) ? 'all' : NULL,
				'meta_key' =>  'topic_support_status',
				'meta_value'=> $view . $additional_meta_value,
				'forum_id' =>  join( ',', $this->forums ) // View must include support forums only
			);

			$title = __( 'Support topics that are %s', SUPPORT_FORUMS_ID );

			if ( 'not_resolved' == $view ) { // Filter not resolved support topics by age
				$delay = 7200; // Minimum support topic age (in seconds)
				$query['started'] = '<' . gmdate( 'YmdH', time() - $delay );
				$title .= sprintf(
					__( ', at least %d hours old', SUPPORT_FORUMS_ID ),
					$delay / 3600
				);
			}

			// A single error makes $success false
			$success = $success && (bool) bb_register_view( 'support-forums-' . str_replace( '_', '-', $view ), sprintf( $title, $this->statuses[$view] ), $query );
		}

		return (bool) $success;
	}

	/**
	 * Prepend support, sticky and closed icons or statements to all topics labels
	 *
	 * @param string $label Topic current label
	 *
	 * @global $topic
	 *
	 * @return string
	 */
	function modifyTopicLabel( $label ) {
		if ( false === (bool) $this->isEnabled() )
			return $label;

		if ( false === (bool) $this->areIconsEnabled() ) // Icons are disabled
			return $label;

		global $topic;

		$icons_uri = ( array_key_exists( 'dir', $this->icons ) ) ? SUPPORT_FORUMS_ACTIVE_TEMPLATE_IMAGES_URI . $this->icons['dir'] : SUPPORT_FORUMS_ICONS_URI;

		// Prepend labels in reverse order
		if ( $topic_status = $this->getTopicStatus() ) // Topic is a support topic
			$label = ( in_array( 'status', $this->icons ) ) ? // Support icons are enabled
				sprintf(
					'<img src="%1$s-%2$s.png" alt="%3$s" title="%3$s" style="vertical-align: middle;" /> ',
					$icons_uri . SUPPORT_FORUMS_ID,
					str_replace( '_', '-', $topic_status ),
					$this->statuses[$topic_status]
				) . $label :
				'[' . $this->statuses[$topic_status] . '] ' . $label;

		if ( !$topic->topic_open ) // Topic is closed
			$label = ( in_array( 'closed', $this->icons ) ) ? // Closed icon is enabled
				sprintf(
					'<img src="%1$s-%2$s.png" alt="%3$s" title="%3$s" style="vertical-align: middle;" /> ',
					$icons_uri . SUPPORT_FORUMS_ID,
					'closed',
					__( 'closed', SUPPORT_FORUMS_ID )
				) . $label :
				'[' . __( 'closed', SUPPORT_FORUMS_ID ) . '] ' . $label;

		if ( $topic->topic_sticky ) // Topic is sticky
			$label = ( in_array( 'sticky', $this->icons ) ) ? // Sticky icon is enabled
				sprintf(
					'<img src="%1$s-%2$s.png" alt="%3$s" title="%3$s" style="vertical-align: middle;" /> ',
					$icons_uri . SUPPORT_FORUMS_ID,
					'sticky',
					__( 'sticky', SUPPORT_FORUMS_ID )
				) . $label :
				'[' . __( 'sticky', SUPPORT_FORUMS_ID ) . '] ' . $label;

		return $label;
	}

	/**
	 * Add a class to support topics
	 *
	 * @param array $class Topic current classes list
	 *
	 * @global $topic
	 *
	 * @return array
	 */
	function addSupportTopicClass( $class ) {
		if ( false === (bool) $this->isEnabled() )
			return $class;

		global $topic;

		if ( in_array( $topic->forum_id, $this->forums ) && !empty( $topic->topic_support_status ) && $this->defaultStatus != $topic->topic_support_status ) // Do not add default support status class to topics
			$class[] = $topic->topic_support_status;

		return $class;
	}
}

// Initialize the class
$support_forums_settings = new Support_Forums_Settings();

if ( bb_is_admin() ) // Load admin.php if on admin area
	require_once( 'includes/admin.php' );

if ( true === (bool) $support_forums_settings->isEnabled() ) { // Load plugin core if plugin is enabled
	// Add plugin actions
	add_action( 'topicmeta',              array( $support_forums_settings, 'supportTopicMeta' ) ); // This function includes statusesDropdown()
	add_action( 'bb_topic.php_pre_db',    array( $support_forums_settings, 'statusesDropdownProcess' ) );

	if ( true === (bool) $support_forums_settings->isStatusSetable() ) { // Add new topics support statuses dropdown menu &co. if status is setable
		add_action( 'bb_init',            array( $support_forums_settings, 'addJQuery' ) );
		add_action( 'bb_head',            array( $support_forums_settings, 'newTopicStatusesDropdownJQuery' ), 100 );
		add_action( 'post_form_pre_post', array( $support_forums_settings, 'newTopicStatusesDropdown' ) );	
		add_action( 'bb-post.php',        array( $support_forums_settings, 'newTopicStatusesDropdownProcess' ) );
	}

	if ( true === (bool) $support_forums_settings->areViewsEnabled() ) // Add views registration if views are enabled
		add_action( 'bb_init',            array( $support_forums_settings, 'registerSupportViews' ) );

	if ( true === (bool) $support_forums_settings->areIconsEnabled() ) { // Add icons filters if icons are enabled
		// Remove closed and sticky topic label filters
		remove_filter( 'bb_topic_labels', 'bb_closed_label', 10 );
		remove_filter( 'bb_topic_labels', 'bb_sticky_label', 20 );
	
		add_filter( 'bb_topic_labels',    array( $support_forums_settings, 'modifyTopicLabel' ), 30, 1 );
	}

	// Add plugin filters
	add_filter( 'topic_class',            array( $support_forums_settings, 'addSupportTopicClass' ), 10, 1 );
}
