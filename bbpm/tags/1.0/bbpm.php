<?php
/*
Plugin Name: bbPM
Plugin URI: http://nightgunner5.wordpress.com/tag/bbpm/
Description: Adds the ability for users of a forum to send private messages to each other.
Version: 1.0
Author: Ben L. (Nightgunner5)
Author URI: http://nightgunner5.wordpress.com/
Text Domain: bbpm
Domain Path: translations/
*/

/**
 * @package bbPM
 * @version 1.0
 * @author Nightgunner5
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3 or higher
 */

bb_load_plugin_textdomain( 'bbpm', dirname( __FILE__ ) . '/translations' );


/**
 * Private message class
 *
 * To get private message number 15 from the database and check if it existed, you would do the following:
 * <code>
 * $message = new bbPM_Message( 15 );
 * if ( $message->exists )
 *     echo 'Private message 15 exists!';
 * else
 *     echo 'Private message 15 does not exist.';
 * </code>
 *
 * @package bbPM
 * @since 0.1-alpha1
 * @author Nightgunner5
 */
class bbPM_Message {
	/**
	 * @var string The URL of the thread that has this message
	 * @since 0.1-alpha1
	 */
	var $read_link;
	/**
	 * @var string The URL of the page that has the reply form for this message
	 * @since 0.1-alpha1
	 */
	var $reply_link;

	/**
	 * @var int The message ID
	 * @since 0.1-alpha1
	 */
	var $ID;
	/**
	 * @var string The PM thread's title
	 * @since 0.1-alpha1
	 */
	var $title;
	/**
	 * @var BP_User The sender of the message
	 * @since 0.1-alpha1
	 */
	var $from;
	/**
	 * @var string The PM's text content in HTML
	 * @since 0.1-alpha1
	 */
	var $text;
	/**
	 * @var int The unix timestamp of when this private message was sent
	 * @since 0.1-alpha1
	 */
	var $date;
	/**
	 * @var bool True if this is a reply, false if this is the first message in a thread
	 * @since 0.1-alpha1
	 */
	var $reply;
	/**
	 * @var int The ID of the message this is a reply to or 0
	 * @since 0.1-alpha1
	 */
	var $reply_to;
	/**
	 * @var int The depth of this message in the thread. 0 for the first message, 1 for direct replies, 2 for replies to direct replies, etc.
	 * @since 0.1-alpha1
	 */
	var $thread_depth;
	/**
	 * @var bool True if this message exists, false if this message does not
	 * @since 0.1-alpha1
	 */
	var $exists;
	/**
	 * @var int The ID of this PM's thread
	 * @since 0.1-alpha6
	 */
	var $thread;

	/**
	 * Gets a private message from the database (or cache, if available)
	 *
	 * @param int $ID The ID of the private message to retrieve.
	 * @see bbPM_Message
	 */
	function bbPM_Message( $ID ) {
		global $bbpm, $bbdb;

		if ( !function_exists( 'wp_cache_get' ) || false === $row = wp_cache_get( (int)$ID, 'bbpm' ) ) {
			$row = $bbdb->get_row( $bbdb->prepare( 'SELECT * FROM `' . $bbdb->bbpm . '` WHERE `ID`=%d', $ID ) );
		}

		if ( !$row ) {
			$this->exists = false;
			if ( function_exists( 'wp_cache_add' ) )
				wp_cache_add( (int)$ID, 0, 'bbpm' );
			return;
		}

		if ( function_exists( 'wp_cache_add' ) )
			wp_cache_add( (int)$ID, $row, 'bbpm' );

		if ( bb_get_option( 'mod_rewrite' ) ) {
			$this->read_link    = bb_get_uri( 'pm/' . $row->pm_thread ) . '#pm-' . $row->ID;
			$this->reply_link   = bb_get_uri( 'pm/' . $row->ID . '/reply' );
		} else {
			$this->read_link    = bb_get_uri( '', array( 'pm' => $row->pm_thread ) ) . '#pm-' . $row->ID;
			$this->reply_link   = bb_get_uri( '', array( 'pm' => $row->ID . '/reply' ) );
		}
		$this->ID           = (int)$row->ID;
		$this->title        = apply_filters( 'get_topic_title', $bbpm->get_thread_title( $row->pm_thread ), 0 );
		$this->from         = new BP_User( (int)$row->pm_from );
		$this->text         = apply_filters( 'get_post_text', $row->pm_text );
		$this->date         = (int)$row->sent_on;
		$this->reply        = (bool)(int)$row->reply_to;
		$this->reply_to     = (int)$row->reply_to;
		$this->thread_depth = (int)$row->thread_depth;
		$this->thread       = (int)$row->pm_thread;
		$this->exists       = true;
	}
}

/**
 * Most of the bbPM functionality is included in this class.
 *
 * @package bbPM
 * @since 0.1-alpha1
 * @author Nightgunner5
 */
class bbPM {
	/**
	 * @var array The bbPM settings, as chosen by the user
	 * @since 0.1-alpha3
	 */
	var $settings;

	/**
	 * @var string The current bbPM version
	 * @since 0.1-alpha1
	 */
	var $version;

	/**
	 * @var int The maximum number of PM conversations per user
	 * @since 0.1-alpha1
	 * @deprecated use {@link bbPM::$settings}['max_inbox']
	 */
	var $max_inbox;

	/**
	 * @var array The current list of bbPM threads
	 * @since 0.1-alpha6
	 * @access private
	 */
	var $current_pm;

	/**
	 * @var string The current bbPM thread
	 * @since 0.1-alpha1
	 */
	var $the_pm;

	/**
	 * @access private
	 */
	var $_profile_context;

	/**
	 * Initializes bbPM
	 *
	 * @global BPDB_Multi Adds bbpm table
	 */
	function bbPM() { // INIT
		global $bbdb;
		$bbdb->bbpm = $bbdb->prefix . 'bbpm';

		// Put two slashes before the next line if you do not want a "PM this user" link in every profile.
		add_action( 'bb_profile.php', array( &$this, 'profile_filter_action' ) );
		// Put two slashes before each of the next two lines if you do not want a "PM this user" link under the author name of every post.
		add_filter( 'post_author_title_link', array( &$this, 'post_title_filter' ), 11, 2 );
		add_filter( 'post_author_title', array( &$this, 'post_title_filter' ), 11, 2 );

		add_filter( 'get_profile_info_keys', array( &$this, 'profile_edit_filter' ), 9, 2 );

		add_action( 'bb_admin_menu_generator', array( &$this, 'admin_add' ) );
		add_filter( 'bb_template', array( &$this, 'template_filter' ), 10, 2 );

		add_action( 'bb_recount_list', array( &$this, 'add_recount' ) );

		$this->current_pm = array();

		$this->settings = bb_get_option( 'bbpm_settings' );
		$this->version = $this->settings ? $this->settings['version'] : false;

		if ( !$this->version || $this->version != '1.0' )
			$this->update();

		if ( $this->settings['auto_add_link'] )
			add_filter( 'bb_logout_link', array( &$this, 'header_link' ) );

		if ( defined( 'BBPM_STT_FIX' ) && BBPM_STT_FIX )
			add_action( 'bb_init', array( &$this, 'subscribe_to_topic_fix' ) );

		$this->max_inbox = $this->settings['max_inbox'];
	}

	/**
	 * @access private
	 */
	function update() {
		global $bbdb;
		switch ( $this->version ) { // Don't use break - each update needs to be installed.
			case false:
				$bbdb->query( '
CREATE TABLE `' . $bbdb->bbpm . '` (
`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
`pm_title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`pm_read` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
`pm_from` BIGINT UNSIGNED NOT NULL,
`pm_to` BIGINT UNSIGNED NOT NULL,
`pm_text` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
`sent_on` INT( 10 ) NOT NULL,
`del_sender` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
`del_reciever` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
`reply_to` BIGINT UNSIGNED DEFAULT NULL,
INDEX ( `pm_to` , `pm_from`, `reply_to` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci
				' );

				$legacy_messages = (array)$bbdb->get_results( 'SELECT * FROM `' . $bbdb->prefix . 'privatemessages`' );
				$legacy_threads = array();

				if ( $legacy_messages ) {
					foreach ( $legacy_messages as $msg ) {
						$bbdb->insert( $bbdb->bbpm, array(
							'pm_title' => attribute_escape( $msg->pmtitle ),
							'pm_read'  => (int)$msg->seen,
							'pm_from'  => (int)$msg->id_sender,
							'pm_to'    => (int)$msg->id_receiver,
							'pm_text'  => apply_filters( 'pre_post', $msg->message, 0, 0 ),
							'sent_on'  => strtotime( $msg->created_on ),
							'reply_to' => @$legacy_threads[$msg->pmtitle . ':' . min( $msg->id_sender, $msg->id_receiver ) . ':' . max( $msg->id_sender, $msg->id_receiver )]
						) );

						$legacy_threads[$msg->pmtitle . ':' . min( $msg->id_sender, $msg->id_receiver ) . ':' . max( $msg->id_sender, $msg->id_receiver )] = $bbdb->insert_id;
					}

					$bbdb->query( 'DROP TABLE `' . $bbdb->prefix . 'privatemessages`' );
				}
				$this->settings['max_inbox'] = 50; // Will be configurable later.

				unset( $legacy_messages, $legacy_threads );
			case '0.1-dev':
				$bbdb->query( 'ALTER TABLE `' . $bbdb->bbpm . '` ADD `thread_depth` INT( 10 ) UNSIGNED NOT NULL DEFAULT \'0\'' );

				$_all_replies = (array)$bbdb->get_results( 'SELECT `ID`,`reply_to` FROM `' . $bbdb->bbpm . '` WHERE `reply_to` IS NOT NULL' );

				foreach ( $_all_replies as $reply ) {
					$all_replies[$reply->ID] = $reply->reply_to;
				}

				foreach ( $all_replies as $ID => $reply ) {
					$depth = 1;
					while ( isset( $all_replies[$reply] ) ) {
						$reply = $all_replies[$reply];
						$depth++;
					}
					$bbdb->update( $bbdb->bbpm, array( 'thread_depth' => $depth ), array( 'ID' => $ID ) );
				}
				unset( $_all_replies, $all_replies, $depth, $reply, $ID );

			case '0.1-alpha1':
			case '0.1-alpha2':
			case '0.1-alpha3':
				$bbdb->query( 'ALTER TABLE `' . $bbdb->bbpm . '` ADD `pm_thread` BIGINT UNSIGNED NOT NULL, ADD INDEX ( pm_thread )' );

			case '0.1-alpha4':
				$_all_pm = (array)$bbdb->get_col( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `reply_to` IS NULL AND `pm_thread`=0' );
				$threads = 0;

				foreach ( $_all_pm as $pm ) {
					$threads++;

					$bbdb->query( 'UPDATE `' . $bbdb->bbpm . '` SET `pm_thread`=\'' . $threads . '\' WHERE `ID` in (' . implode( ',', $this->update_helper_0_1_alpha4( $pm ) ) . ')' );
				}

			case '0.1-alpha4b':
				$this->settings['auto_add_link'] = true;

			case '0.1-alpha5':
				$threads_done = array();

				$messages = $bbdb->get_results( 'SELECT * FROM `' . $bbdb->bbpm . '`' );

				foreach ( $messages as $message ) {
					if ( in_array( (int)$message->pm_thread, $threads_done ) )
						continue;

					$threads_done[] = (int)$message->pm_thread;

					if ( $message->reply_to )
						$message->pm_title = substr( $message->pm_title, 4 );

					bb_update_meta( (int)$message->pm_thread, 'title', $message->pm_title, 'bbpm_thread' );
					bb_update_meta( (int)$message->pm_thread, 'to', $message->pm_from == $message->pm_to ? ',' . $message->pm_to . ',' : ',' . $message->pm_from . ',' . $message->pm_to . ',', 'bbpm_thread' );
					bb_update_meta( (int)$message->pm_thread, 'last_message', (int)$bbdb->get_var( $bbdb->prepare( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` = %d ORDER BY `ID` DESC LIMIT 1', $message->pm_thread ) ), 'bbpm_thread' );
					$last_read = (int)$bbdb->get_var( $bbdb->prepare( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` = %d AND `seen` = 1 ORDER BY `ID` DESC LIMIT 1', $message->pm_thread ) );
					bb_update_usermeta( $message->pm_to, 'bbpm_last_read_' . $message->pm_thread, $last_read );
					bb_update_usermeta( $message->pm_from, 'bbpm_last_read_' . $message->pm_thread, $last_read );
				}

				$bbdb->query( 'ALTER TABLE `' . $bbdb->bbpm . '` DROP COLUMN `pm_to`, DROP COLUMN `pm_title`, DROP COLUMN `pm_read`, DROP COLUMN `del_sender`, DROP COLUMN `del_reciever`' );

			case '0.1-alpha6':
			case '0.1-alpha6b':
				$this->settings['email_new']     = true;
				$this->settings['email_reply']   = true;
				$this->settings['email_add']     = true;
				$this->settings['email_message'] = false;
				$this->settings['threads_per_page'] = 0;

				wp_cache_flush( 'bbpm-user-messages' ); // For memcached

			case '0.1-alpha7':
			case '1.0-beta1':
				// At the end of all of the updates:
				$this->settings['version'] = '1.0';
				$this->version             = '1.0';
				bb_update_option( 'bbpm_settings', $this->settings );

			case '1.0':
				// Do nothing, this is the newest version.
		}
	}

	/**
	 * @since 0.1-alpha4
	 * @access private
	 * @see bbPM::update()
	 */
	function update_helper_0_1_alpha4( $start_id ) {
		global $bbdb;

		$thread_items = array( $start_id );

		$pm_list = array_map( 'bbPM_update_helper_helper_0_1_alpha4', (array)$bbdb->get_results( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `reply_to`=\'' . $start_id . '\'' ) );

		$thread_items = array_merge( $thread_items, $pm_list );

		foreach ( $pm_list as $pm ) {
			$thread_items = array_merge( $thread_items, $this->update_helper_0_1_alpha4( $pm ) );
		}

		return array_unique( $thread_items );
	}

	/**
	 * @global BPDB_Multi Accessing the database
	 * @param int $user_id The user to get a count of private message threads from (default current user)
	 * @param bool $unread_only True to get only unread threads, false to get all threads that a user has access to.
	 * @return int The number of private message threads that matched the criteria.
	 */
	function count_pm( $user_id = 0, $unread_only = false ) {
		global $bbdb;

		$user_id = (int)$user_id;

		if ( !$user_id )
			$user_id = bb_get_current_user_info( 'ID' );

		if ( !function_exists( 'wp_cache_get' ) || false === $thread_member_of = wp_cache_get( $user_id, 'bbpm-user-messages' ) ) {
			$thread_member_of = (array)$bbdb->get_col( $bbdb->prepare( 'SELECT `object_id` FROM `' . $bbdb->meta . '` WHERE `object_type`=%s AND `meta_key`=%s AND `meta_value` LIKE %s', 'bbpm_thread', 'to', '%,' . $user_id . ',%' ) );

			$this->cache_threads( $thread_member_of );

			if ( function_exists( 'wp_cache_add' ) )
				wp_cache_add( $user_id, $thread_member_of, 'bbpm-user-messages' );
		}

		$threads = count( $thread_member_of );

		if ( $unread_only )
			foreach ( $thread_member_of as $thread )
				if ( $this->get_last_read( $thread ) == $this->get_thread_meta( $thread, 'last_message' ) )
					$threads--;

		return $threads;
	}

	/**
	 * @uses bbPM::$settings to get the pagination settings
	 * @return int the number of threads per page
	 */
	function threads_per_page() {
		return $this->settings['threads_per_page'] ? $this->settings['threads_per_page'] : bb_get_option( 'page_topics' );
	}

	/**
	 * @uses bbPM::count_pm() counting total messages
	 * @param int $current The current page number
	 * @return void
	 */
	function pm_pages( $current ) {
		$total = ceil( $this->count_pm() / $this->threads_per_page() );

		echo bb_paginate_links( array(
			'current' => $current,
			'total' => $total,
			'base' => $this->get_link() . '%_%',
			'format' => bb_get_option( 'mod_rewrite' ) ? '/page/%#%' : '=page/%#%'
		) );
	}

	/**
	 * Get the next private message thread, if available
	 *
	 * @global BPDB_Multi Getting PM threads
	 * @see bbPM::the_pm This will have the PM thread data
	 * @since 0.1-alpha1
	 * @param int $start The starting index of the PM threads to get
	 * @param int $end The ending index of the PM threads to get - Must be greater than $start
	 * @return bool True if the next private message could be found, false otherwise
	 */
	function have_pm( $start = 0, $end = 0 ) {
		$start = (int)$start;
		$end   = (int)$end;

		if ( $start < 0 )
			$start = 0;

		if ( $end < 1 )
			$end = 2147483647;

		if ( $start > $end )
			return false;

		$end -= $start;

		if ( !isset( $this->current_pm[$start . '_' . $end] ) ) {
			global $bbdb;

			if ( function_exists( 'wp_cache_get' ) && false !== $threads = wp_cache_get( bb_get_current_user_info( 'ID' ), 'bbpm-user-messages' ) ) {
				usort( $threads, array( &$this, '_newer_last_message_1' ) );
				$threads = array_slice( $threads, $start, $end );
			} else {
				$threads = (array)$bbdb->get_col( $bbdb->prepare( 'SELECT `object_id` FROM `' . $bbdb->meta . '` as `m` JOIN `' . $bbdb->bbpm . '` as `b` ON `m`.`object_id` = `b`.`pm_thread` WHERE `object_type` = %s AND `meta_key` = %s AND `meta_value` LIKE %s GROUP BY `b`.`pm_thread` ORDER BY `b`.`ID` DESC LIMIT ' . $start . ',' . $end, 'bbpm_thread', 'to', '%,' . bb_get_current_user_info( 'ID' ) . ',%' ) );
				$this->cache_threads( $threads );
			}

			$this->current_pm[$start . '_' . $end] = array();

			foreach ( $threads as $thread ) {
				$this->current_pm[$start . '_' . $end][] = array( 'id' => $thread, 'members' => $this->get_thread_members( $thread ), 'title' => $this->get_thread_title( $thread ), 'last_message' => $this->get_thread_meta( $thread, 'last_message' ) );
			}

			usort( $this->current_pm[$start . '_' . $end], array( &$this, '_newer_last_message_2' ) );

			if ( $this->current_pm[$start . '_' . $end] ) {
				$this->the_pm = reset( $this->current_pm[$start . '_' . $end] );
				return true;
			}
			return false;
		}

		if ( $this->the_pm = next( $this->current_pm[$start . '_' . $end] ) )
			return true;
		return false;
	}

	/**
	 * @access private
	 */
	function _newer_last_message_1( $a, $b ) {
		return $this->get_thread_meta( $a, 'last_message' ) > $this->get_thread_meta( $b, 'last_message' ) ? -1 : 1;
	}

	/**
	 * @access private
	 */
	function _newer_last_message_2( $a, $b ) {
		return $a['last_message'] > $b['last_message'] ? -1 : 1;
	}

	/**
	 * @param int $id_reciever
	 * @param string $title
	 * @param string $message
	 * @return string|bool The URL of the new message or false if any of the message boxes is full.
	 */
	function send_message( $id_reciever, $title, $message ) {
		if ( $this->count_pm() > $this->max_inbox || $this->count_pm( $id_reciever ) > $this->max_inbox )
			return false;

		global $bbdb;

		$pm = array(
			'pm_from'   => (int)bb_get_current_user_info( 'ID' ),
			'pm_text'   => apply_filters( 'pre_post', $message, 0, 0 ),
			'sent_on'   => bb_current_time( 'timestamp' ),
			'pm_thread' => $bbdb->get_var( 'SELECT MAX( `pm_thread` ) FROM `' . $bbdb->bbpm . '`' ) + 1
		);

		$bbdb->insert( $bbdb->bbpm, $pm );

		$msg = new bbPM_Message( $bbdb->insert_id );

		bb_update_meta( $pm['pm_thread'], 'title', $title, 'bbpm_thread' );
		bb_update_meta( $pm['pm_thread'], 'to', bb_get_current_user_info( 'ID' ) == $id_reciever ? ',' . $id_reciever . ',' : ',' . bb_get_current_user_info( 'ID' ) . ',' . $id_reciever . ',', 'bbpm_thread' );

		if ( function_exists( 'wp_cache_delete' ) ) {
			wp_cache_delete( $id_reciever, 'bbpm-user-messages' );
			wp_cache_delete( bb_get_current_user_info( 'ID' ), 'bbpm-user-messages' );
		}

		if ( $this->settings['email_new'] && !bb_get_usermeta( $id_reciever, 'bbpm_emailme' ) && bb_get_current_user_info( 'ID' ) != $id_reciever )
			bb_mail( bb_get_user_email( $id_reciever ),
				sprintf(
					__( '%s has sent you a private message on %s: "%s"', 'bbpm' ),
					get_user_display_name( bb_get_current_user_info( 'ID' ) ),
					bb_get_option( 'name' ),
					$title
				), $this->settings['email_message'] ? sprintf(
					__( "Hello, %s!\n\n%s has sent you a private message entitled \"%s\" on %s!\n\nTo read it now, go to the following address:\n\n%s\n\nDo NOT reply to this message.\n\nThe contents of the message are:\n\n%s", 'bbpm' ),
					get_user_display_name( $id_reciever ),
					get_user_display_name( bb_get_current_user_info( 'ID' ) ),
					$title,
					bb_get_option( 'name' ),
					$msg->read_link,
					strip_tags( $msg->text )
				) : sprintf(
					__( "Hello, %s!\n\n%s has sent you a private message entitled \"%s\" on %s!\n\nTo read it now, go to the following address:\n\n%s", 'bbpm' ),
					get_user_display_name( $id_reciever ),
					get_user_display_name( bb_get_current_user_info( 'ID' ) ),
					$title,
					bb_get_option( 'name' ),
					$msg->read_link
				)
			);

		bb_update_meta( $pm['pm_thread'], 'last_message', $msg->ID, 'bbpm_thread' );
		bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'bbpm_last_read_' . $pm['pm_thread'], $msg->ID );

		do_action( 'bbpm_new', $msg );
		do_action( 'bbpm_send', $msg );

		return $msg->read_link;
	}

	/**
	 * Send a reply to a private message
	 *
	 * @param int $reply_to The ID of the message that is being replied to
	 * @param string $message The reply message
	 * @return string A link to the new message
	 * @global BPDB_Multi sending the reply
	 * @since 0.1-alpha6
	 */
	function send_reply( $reply_to, $message ) {
		global $bbdb;

		$reply_to = new bbPM_Message( $reply_to );

		$pm = array(
			'pm_from'      => (int)bb_get_current_user_info( 'ID' ),
			'pm_text'      => apply_filters( 'pre_post', $message, 0, 0 ),
			'sent_on'      => bb_current_time( 'timestamp' ),
			'pm_thread'    => (int)$reply_to->thread,
			'reply_to'     => (int)$reply_to->ID,
			'thread_depth' => $reply_to->thread_depth + 1
		);

		$bbdb->insert( $bbdb->bbpm, $pm );

		$msg = new bbPM_Message( $bbdb->insert_id );

		bb_update_meta( $pm['pm_thread'], 'last_message', $msg->ID, 'bbpm_thread' );

		if ( $this->settings['email_reply'] ) {
			$to = $this->get_thread_members( $pm['pm_thread'] );

			foreach ( $to as $recipient ) {
				if ( $to != bb_get_current_user_info( 'ID' ) && !bb_get_usermeta( $recipient, 'bbpm_emailme' ) )
					bb_mail( bb_get_user_email( $recipient ),
						sprintf(
							__( '%s has sent you a private message on %s: "%s"', 'bbpm' ),
							get_user_display_name( bb_get_current_user_info( 'ID' ) ),
							bb_get_option( 'name' ),
							$this->get_thread_title( $msg->thread )
						), $this->settings['email_message'] ? sprintf(
							__( "Hello, %s!\n\n%s has sent you a private message entitled \"%s\" on %s!\n\nTo read it now, go to the following address:\n\n%s\n\nDo NOT reply to this message.\n\nThe contents of the message are:\n\n%s", 'bbpm' ),
							get_user_display_name( $recipient ),
							get_user_display_name( bb_get_current_user_info( 'ID' ) ),
							$this->get_thread_title( $msg->thread ),
							bb_get_option( 'name' ),
							$msg->read_link,
							strip_tags( $msg->text )
						) : sprintf(
							__( "Hello, %s!\n\n%s has sent you a private message entitled \"%s\" on %s!\n\nTo read it now, go to the following address:\n\n%s", 'bbpm' ),
							get_user_display_name( $recipient ),
							get_user_display_name( bb_get_current_user_info( 'ID' ) ),
							$this->get_thread_title( $msg->thread ),
							bb_get_option( 'name' ),
							$msg->read_link
						)
					);
			}
		}

		bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'bbpm_last_read_' . $pm['pm_thread'], $msg->ID );

		do_action( 'bbpm_reply', $msg );
		do_action( 'bbpm_send', $msg );

		return $msg->read_link;
	}

	/**
	 * @access private
	 */
	function _make_thread( $thread, $reply_to = null ) {
		$ret = array();

		foreach ( $thread as $pm ) {
			if ( $pm->reply_to == $reply_to ) {
				$ret[] = $pm->ID;
				$ret = array_merge( $ret, $this->_make_thread( $thread, $pm->ID, $thread_id ) );
			}
		}

		return $ret;
	}

	/**
	 * Get the messages in a private messaging thread
	 *
	 * @since 0.1-alpha4b
	 * @param int $id The ID number of the thread to get
	 * @return array The array of private messages in the thread, or an empty array if the thread does not exist.
	 * @global BPDB_Multi Get the threads
	 * @uses bbPM_Message The thread is given as an array of {@link bbPM_Message}s
	 */
	function get_thread( $id ) {
		global $bbdb;

		if ( !function_exists( 'wp_cache_get' ) || false === $thread_ids = wp_cache_get( (int)$id, 'bbpm-thread' ) ) {
			$thread_posts = (array)$bbdb->get_results( $bbdb->prepare( 'SELECT * FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` = %d ORDER BY `ID`', $id ) );

			if ( function_exists( 'wp_cache_add' ) )
				foreach ( $thread_posts as $pm )
					wp_cache_add( (int)$pm->ID, $pm, 'bbpm' );

			$thread_ids = $this->_make_thread( $thread_posts );

			if ( function_exists( 'wp_cache_add' ) )
				wp_cache_add( (int)$id, $thread_ids, 'bbpm-thread' );
		}

		$thread = array();
		foreach ( $thread_ids as $ID ) {
			$thread[] = new bbPM_Message( $ID );
		}

		return $thread;
	}

	/**
	 * Store the meta and last messages of each thread in the cache
	 *
	 * @since 0.1-alpha6
	 * @global BPDB_Multi Get the meta and last messages
	 * @param array $IDs An array of integer IDs of PM threads
	 * @return void
	 */
	function cache_threads( $IDs ) {
		if ( !function_exists( 'wp_cache_add' ) )
			return;

		foreach ( $IDs as $i => $id ) {
			if ( !(int)$id || wp_cache_get( $id, 'bbpm-cached' ) )
				unset( $IDs[$i] );

			wp_cache_add( $id, true, 'bbpm-cached' );
		}

		if ( !$IDs )
			return;

		global $bbdb;

		$users = array();
		$posts = array();

		$thread_meta = (array)$bbdb->get_results( 'SELECT `object_id`,`meta_key`,`meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type` = \'bbpm_thread\' AND `object_id` IN (' . implode( ',', array_map( 'intval', $IDs ) ) . ')' );

		foreach ( $thread_meta as $meta ) {
			wp_cache_add( $meta->meta_key, $meta->meta_value, 'bbpm-thread-' . $meta->object_id );

			if ( $meta->meta_key == 'to' )
				$users = array_merge( $users, explode( ',', $meta->meta_value ) );
			if ( $meta->meta_key == 'last_message' )
				$posts[] = (int)$meta->meta_value;
		}

		$thread_posts = (array)$bbdb->get_results( 'SELECT * FROM `' . $bbdb->bbpm . '` WHERE `ID` IN (' . implode( ',', $posts ) . ') ORDER BY `ID`' );

		foreach ( $thread_posts as $pm )
			wp_cache_add( (int)$pm->ID, $pm, 'bbpm' );

		$users = array_values( array_filter( array_unique( $users ) ) );

		bb_cache_users( $users );
	}

	/**
	 * Get the title of a private message thread
	 *
	 * @since 0.1-alpha6
	 * @param int $thread_ID The ID of the thread
	 * @return string The title of the thread
	 * @uses bbPM::get_thread_meta() Getting the thread's title
	 */
	function get_thread_title( $thread_ID ) {
		return $this->get_thread_meta( $thread_ID, 'title' );
	}

	/**
	 * Get the IDs of the members of a private message thread
	 *
	 * @since 0.1-alpha6
	 * @param int $thread_ID The ID of the thread
	 * @return array The members of the thread
	 * @uses bbPM::get_thread_meta() Getting the thread's member list
	 */
	function get_thread_members( $thread_ID ) {
		return array_values( array_filter( explode( ',', $this->get_thread_meta( $thread_ID, 'to' ) ) ) );
	}

	/**
	 * Figure out if a user can read a given PM
	 *
	 * @since 0.1-alpha1
	 * @param int $ID The ID of the PM
	 * @param int $user_id The ID of the user, or zero for the current user
	 * @return bool True if the user can read the message, false if they cannot
	 * @uses bbPM::can_read_thread() If the message exists, the thread is checked, because there are no message-based permissions
	 */
	function can_read_message( $ID, $user_id = 0 ) {
		$msg = new bbPM_Message( $ID );
		if ( !$msg->exists )
			return false;

		return $this->can_read_thread( $msg->thread, $user_id );
	}

	/**
	 * Figure out if a user can read a given PM thread
	 *
	 * @since 0.1-alpha4b
	 * @param int $ID The ID of the thread
	 * @param int $user_id The ID of the user, or zero for the current user
	 * @return bool True if the user can read the thread, false if they cannot
	 * @uses bbPM::get_thread_meta() Check for the user ID in the thread's member list
	 */
	function can_read_thread( $ID, $user_id = 0 ) {
		$user_id = (int)$user_id;

		if ( !$user_id )
			$user_id = bb_get_current_user_info( 'ID' );

		return strpos( $this->get_thread_meta( $ID, 'to' ), ',' . $user_id . ',' ) !== false;
	}

	/**
	 * Unsubscribe the current user from a PM thread
	 *
	 * @since 0.1-alpha6
	 * @param int $ID The ID of the thread to unsubscribe from
	 * @return void
	 * @uses bbPM::get_thread_meta() Check if the current user is actually on the member list
	 * @global BPDB_Multi Delete the thread if it has no members left
	 */
	function unsubscribe( $ID ) {
		global $bbdb;

		if ( $members = $this->get_thread_meta( $ID, 'to' ) ) {
			if ( strpos( $members, ',' . bb_get_current_user_info( 'ID' ) . ',' ) !== false ) {
				$members = str_replace( ',' . bb_get_current_user_info( 'ID' ) . ',', ',', $members );
				if ( $members == ',' ) {
					$bbdb->query( $bbdb->prepare( 'DELETE FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` = %d', $ID ) );
					$bbdb->query( $bbdb->prepare( 'DELETE FROM `' . $bbdb->meta . '` WHERE `object_type` = %s AND `object_id` = %d', 'bbpm_thread', $ID ) );
					if ( function_exists( 'wp_cache_flush' ) )
						wp_cache_flush( 'bbpm-thread-' . $ID );
				} else {
					bb_update_meta( $ID, 'to', $members, 'bbpm_thread' );
					if ( function_exists( 'wp_cache_set' ) )
						wp_cache_set( 'to', $members, 'bbpm-thread-' . $ID );
				}
				if ( function_exists( 'wp_cache_delete' ) )
					wp_cache_delete( bb_get_current_user_info( 'ID' ), 'bbpm-user-messages' );
				do_action( 'bbpm_unsubscribe', $ID );
			}
		}
	}

	/**
	 * Add a member to a PM thread
	 *
	 * @since 0.1-alpha6
	 * @param int $ID The ID of the thread
	 * @param int $user The ID of the user
	 * @return bool|void True if the user was added, false if the user has reached their message limit, and null if the PM thread doesn't exist
	 * @uses bbPM::count_pm() Count the messages a user has, make sure the limit is not exceeded
	 */
	function add_member( $ID, $user ) {
		if ( $this->count_pm( $user ) > $this->settings['max_inbox'] )
			return false;

		global $bbdb;

		if ( $members = $this->get_thread_meta( $ID, 'to' ) ) {
			if ( strpos( $members, ',' . $user . ',' ) === false ) {
				$members .= $user . ',';
				bb_update_meta( $ID, 'to', $members, 'bbpm_thread' );

				if ( function_exists( 'wp_cache_delete' ) ) {
					wp_cache_delete( 'to', 'bbpm-thread-' . $ID );
					wp_cache_delete( $user, 'bbpm-user-messages' );
				}

				do_action( 'bbpm_add_member', $ID, $user );

				if ( $this->settings['email_add'] && !bb_get_usermeta( $user, 'bbpm_emailme' ) ) {
					bb_mail( bb_get_user_email( $user ),
						sprintf(
							__( '%s has added you to a conversation on %s: "%s"', 'bbpm' ),
							get_user_display_name( bb_get_current_user_info( 'ID' ) ),
							bb_get_option( 'name' ),
							$this->get_thread_title( $ID )
						), sprintf(
							__( "Hello, %s!\n\n%s has added you to a private message conversation titled \"%s\" on %s!\n\nTo read it now, go to the following address:\n\n%s", 'bbpm' ),
							get_user_display_name( $user ),
							get_user_display_name( bb_get_current_user_info( 'ID' ) ),
							$this->get_thread_title( $ID ),
							bb_get_option( 'name' ),
							bb_get_option( 'mod_rewrite' ) ? bb_get_uri( 'pm/' . $ID ) : BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?' . $ID
						)
					);
				}
			}
			return true;
		}
	}

	/**
	 * Echoes the URL of the page where new private messaging threads can be created
	 */
	function new_pm_link() {
		if ( bb_get_option( 'mod_rewrite' ) )
			bb_uri( 'pm/new' );
		else
			bb_uri( '', array( 'pm' => 'new' ) );
	}

	/**
	 * @access private
	 */
	function template_filter( $a, $b ) {
		if ( ( is_pm() && $b == '404.php' ) || ( !bb_get_option( 'mod_rewrite' ) && is_pm() && $b == 'front-page.php' ) ) {
			if ( !$template = bb_get_template( 'privatemessages.php', false ) ) {
				$template = dirname( __FILE__ ) . '/privatemessages.php';
			}
			return $template;
		}

		return $a;
	}

	/**
	 * @access private
	 */
	function profile_filter_action() {
		add_filter( 'get_profile_info_keys', array( &$this, 'profile_filter' ) );
	}

	/**
	 * @access private
	 */
	function profile_filter( $keys ) {
		global $user_id;
		if ( bb_get_current_user_info( 'ID' ) != $user_id && bb_current_user_can( 'write_posts' ) ) {
			echo '<a href="' . $this->get_send_link( $user_id ) . '">' . __( 'PM this user', 'bbpm' ) . '</a>';
		}
		return $keys;
	}

	/**
	 * @access private
	 */
	function profile_edit_filter( $keys, $context = '' ) {
		if ( $context == 'profile-edit' && !$this->_profile_context )
			$this->_profile_context = true;

		if ( $this->_profile_context )
			$keys['bbpm_emailme'] = array( 0, __( 'Don\'t email me when I get a PM', 'bbpm' ), 'checkbox', '1', '' );

		return $keys;
	}

	/**
	 * @access private
	 */
	function post_title_filter( $text, $post_id = 0 ) {
		if ( $post_id && ( $user_id = get_post_author_id( $post_id ) ) && bb_current_user_can( 'write_posts' ) ) {
			$text .= "<br/>\n";
			$text .= '<a href="' . $this->get_send_link( $user_id ) . '">' . __( 'PM this user', 'bbpm' ) . '</a>';
		}
		return $text;
	}

	/**
	 * Get the URL of the PM list page
	 *
	 * @since 0.1-alpha1
	 * @return string The URL
	 */
	function get_link() {
		if ( bb_get_option( 'mod_rewrite' ) )
			return bb_get_uri( 'pm' );
		return bb_get_uri( '?pm' );
	}

	/**
	 * Get the URL of a page where a private message can be written to a given user.
	 *
	 * @since 0.1-alpha3
	 * @param int $user_id The ID of the user
	 * @return string The URL
	 */
	function get_send_link( $user_id = 0 ) {
		$user = bb_get_user( $user_id );
		if ( $user )
			$user_name = $user->user_nicename;

		if ( bb_get_option( 'mod_rewrite' ) )
			return bb_get_uri( 'pm/new/' . $user_name );
		return bb_get_uri( '', array( 'pm' => 'new/' . $user_name ) );
	}

	/**
	 * @access private
	 */
	function header_link( $link ) {
		$count = $this->count_pm( bb_get_current_user_info( 'ID' ), true );

		if ( $count )
			return $link . ' | <big><a href="' . $this->get_link() . '">' . sprintf( _n( '1 new Private Message!', '%s new Private Messages!', $count, 'bbpm' ), bb_number_format_i18n( $count ) ) . '</a></big>';
		return $link . ' | <a href="' . $this->get_link() . '">' . __( 'Private Messages', 'bbpm' ) . '</a>';
	}

	/**
	 * @access private
	 */
	function admin_add() {
		bb_admin_add_submenu( __( 'bbPM', 'bbpm' ), 'use_keys', 'bbpm_admin_page', 'options-general.php' );
	}

	/**
	 * Get the message ID that a user last read in a PM thread
	 *
	 * @since 0.1-alpha6
	 * @param int $thread_ID The ID of the thread
	 * @return int The ID of the last message read by the user, or 0 if the user has never read the thread
	 */
	function get_last_read( $thread_ID ) {
		return (int)bb_get_usermeta( bb_get_current_user_info( 'ID' ), 'bbpm_last_read_' . (int)$thread_ID );
	}

	/**
	 * Get information about a bbPM thread
	 *
	 * @since 0.1-alpha6
	 * @param int $thread_ID The ID of the thread to get information about
	 * @param string $key The type of information to get
	 * @return string|void The information requested, or null if the information could not be found.
	 */
	function get_thread_meta( $thread_ID, $key ) {
		if ( !function_exists( 'wp_cache_get' ) || false === $result = wp_cache_get( $key, 'bbpm-thread-' . $thread_ID ) ) {
			global $bbdb;
			$result = $bbdb->get_var( $bbdb->prepare( 'SELECT `meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type` = %s AND `meta_key` = %s AND `object_id` = %d', 'bbpm_thread', $key, $thread_ID ) );

			if ( function_exists( 'wp_cache_add' ) )
				wp_cache_add( $key, $result, 'bbpm-thread-' . $thread_ID );
		}

		return $result;
	}

	/**
	 * Mark a PM thread as read for the current user
	 *
	 * @since 0.1-alpha6
	 * @uses bbPM::get_last_read Get the current last read ID to reduce database usage
	 * @param int $thread_ID The ID of the PM thread to mark as read
	 * @return void
	 */
	function mark_read( $thread_ID ) {
		if ( $this->get_last_read( $thread_ID ) != $this->get_thread_meta( $thread_ID, 'last_message' ) )
			bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'bbpm_last_read_' . (int)$thread_ID, (int)$this->get_thread_meta( $thread_ID, 'last_message' ) );
	}

	/**
	 * @since 0.1-alpha6
	 */
	function thread_alt_class() {
		alt_class( 'bbpm_threads', $this->the_pm['last_message'] == $this->get_last_read( $this->the_pm['id'] ) ? '' : 'unread_posts_row' );
	}

	/**
	 * @since 0.1-alpha6
	 */
	function thread_freshness() {
		$the_pm = new bbPM_Message( $this->the_pm['last_message'] );

		echo apply_filters( 'bbpm_freshness', bb_since( $the_pm->date ), $the_pm->date );
	}

	/**
	 * @since 0.1-alpha6
	 */
	function thread_unsubscribe_url() {
		echo bb_nonce_url( BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/pm.php?unsubscribe=' . $this->the_pm['id'], 'bbpm-unsubscribe-' . $this->the_pm['id'] );
	}

	/**
	 * @since 0.1-alpha6
	 */
	function thread_read_before() {
		if ( $this->the_pm && $this->the_pm['last_message'] != $this->get_last_read( $this->the_pm['id'] ) ) {
			echo '<span class="unread_posts">';

			if ( !function_exists( 'utplugin_show_unread' ) )
				echo '<strong>' . __( 'New:', 'bbpm' ) . '</strong> ';
		}
	}

	/**
	 * @since 0.1-alpha6
	 */
	function thread_read_after() {
		if ( $this->the_pm && $this->the_pm['last_message'] != $this->get_last_read( $this->the_pm['id'] ) )
			echo '</span>';
	}

	/**
	 * Compatibility with {@link http://bbpress.org/plugins/topic/subscribe-to-topic/ Subscribe to Topic}.
	 *
	 * Used when {@link bbPM::unsubscribe()} is called from {@link pm.php} so
	 * {@link http://bbpress.org/plugins/topic/subscribe-to-topic/ Subscribe to Topic}
	 * doesn't remove the unsubscribe parameter from the URL, and so topics aren't
	 * unsubscribed from unknowingly.
	 *
	 * @since 0.1-alpha6b
	 * @see BBPM_STT_FIX
	 */
	function subscribe_to_topic_fix() {
		remove_action( 'bb_init', 'stt_init', 150 );
	}

	/**
	 * @see bbPM::recount()
	 * @access private
	 * @since 0.1-alpha7
	 */
	function add_recount() {
		global $recount_list;

		$recount_list[] = array( 'bbpm', __( 'Remove deleted users from bbPM threads', 'bbpm' ), array( &$this, 'recount' ) );
	}

	/**
	 * Delete unused bbPM data
	 *
	 * So far, the actions used are:
	 *
	 * - Remove users that have been deleted from thread member lists
	 * - Delete threads with no users (this only deletes threads if they had deleted users in them, otherwise threads should be deleted automatically)
	 *
	 * @todo Optimize this (maybe ask _ck_)
	 * @since 0.1-alpha7
	 * @return string A description of the actions used
	 * @global BPDB_Multi Get, set, and delete as needed
	 */
	function recount() {
		global $bbdb;

		$result = __( 'Cleaning up bbPM messages&hellip; ', 'bbpm' );		

		// Get all of the PM thread member lists
		$members = $bbdb->get_results( $bbdb->prepare( 'SELECT `object_id`,`meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type` = %s AND `meta_key` = %s', 'bbpm_thread', 'to' ) );
		$users = array();

		foreach ( $members as $thread ) {
			$member = array_slice( explode( ',', $thread->meta_value ), 1, -1 );
			foreach ( $member as $user ) {
				if ( !isset( $users[$user] ) )
					$users[$user] = true;
			}
		}

		$users = array_keys( $users );

		bb_cache_users( $users );

		$users_noexist = array();

		foreach ( $users as $user ) {
			if ( !bb_get_user( $user ) ) {
				$users_noexist[] = ',' . $user . ',';
			}
		}

		$threads_delete = array();

		if ( $users_noexist ) {
			foreach ( $members as $thread ) {
				if ( $thread->meta_value != $_members = str_replace( $users_noexist, ',', $thread->meta_value ) ) {
					if ( $_members == ',' ) {
						$threads_delete[] = $thread->object_id;
					} else {
						bb_update_meta( $thread->object_id, 'to', $_members, 'bbpm_thread' );
						if ( function_exists( 'wp_cache_set' ) )
							wp_cache_set( 'to', $_members, 'bbpm-thread-' . $thread->object_id );
					}
				}
			}

			$result .= sprintf( _n( 'Removed %s nonexistant user from bbPM threads.', 'Removed %s nonexistant users from bbPM threads.', count( $users_noexist ), 'bbpm' ), bb_number_format_i18n( count( $users_noexist ) ) );
		}

		if ( count( $threads_delete ) ) {
			if ( function_exists( 'wp_cache_flush' ) )
				foreach ( $threads_delete as $ID )
					wp_cache_flush( 'bbpm-thread-' . $ID );

			$bbdb->query( 'DELETE FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` IN (' . implode( ',', $threads_delete ) . ')' );
			$bbdb->query( 'DELETE FROM `' . $bbdb->meta . '` WHERE `object_type` = \'bbpm_thread\' AND `object_id`  IN (' . implode( ',', $threads_delete ) . ')' );

			$result .= sprintf( _n( 'Deleted %s thread. ', 'Deleted %s threads. ', count( $threads_delete ), 'bbpm' ), bb_number_format_i18n( count( $threads_delete ) ) );
		}

		return $result;
	}
}

/**
 * The bbPM object
 *
 * @global bbPM $GLOBALS['bbpm']
 * @name $bbpm
 * @since 0.1-alpha1
 */
$GLOBALS['bbpm'] = new bbPM;

/**
 * @since 0.1-alpha1
 * @return bool true if the current page is the private messaging page, false otherwise.
 */
function is_pm() {
	return substr( ltrim( substr( $_SERVER['REQUEST_URI'] . '/', strlen( bb_get_option( 'path' ) ) ), '/' ), 0, 3 ) == 'pm/';
}

function bbpm_admin_page() {
	global $bbpm;

	if ( bb_verify_nonce( $_POST['_wpnonce'], 'bbpm-admin' ) ) {
		$bbpm->settings['max_inbox'] = max( (int)$_POST['max_inbox'], 1 );
		$bbpm->settings['auto_add_link'] = !empty( $_POST['auto_add_link'] );
		$bbpm->settings['email_new'] = !empty( $_POST['email_new'] );
		$bbpm->settings['email_reply'] = !empty( $_POST['email_reply'] );
		$bbpm->settings['email_add'] = !empty( $_POST['email_add'] );
		$bbpm->settings['email_message'] = !empty( $_POST['email_message'] );
		$bbpm->settings['threads_per_page'] = max( (int)$_POST['threads_per_page'], 0 );

		bb_update_option( 'bbpm_settings', $bbpm->settings );
	}

?>
<h2><?php _e( 'bbPM', 'bbpm' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>
<form class="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<fieldset>
	<div id="option-max_inbox">
		<label for="max_inbox">
			<?php _e( 'Maximum PM threads per user', 'bbpm' ); ?>
		</label>
		<div class="inputs">
			<input type="text" class="text short" id="max_inbox" name="max_inbox" value="<?php echo $bbpm->settings['max_inbox']; ?>" />
		</div>
	</div>
	<div id="option-auto_add_link">
		<label for="auto_add_link">
			<?php _e( 'Automatically add header link', 'bbpm' ); ?>
		</label>
		<div class="inputs">
			<input type="checkbox" id="auto_add_link" name="auto_add_link"<?php if ( $bbpm->settings['auto_add_link'] ) echo ' checked="checked"'; ?> />
			<p><?php _e( 'You will need to add <code>&lt;?php if ( function_exists( \'bbpm_messages_link\' ) ) bbpm_messages_link(); ?&gt;</code> to your template if you disable this.', 'bbpm' ); ?></p>
		</div>
	</div>
	<div id="option-email_settings">
		<div class="label">
			<?php _e( 'Email options', 'bbpm' ); ?>
		</div>
		<div class="inputs">
			<input type="checkbox" id="email_new" name="email_new"<?php if ( $bbpm->settings['email_new'] ) echo ' checked="checked"'; ?> /> <?php _e( 'When a new message is recieved', 'bbpm' ); ?><br />
			<input type="checkbox" id="email_reply" name="email_reply"<?php if ( $bbpm->settings['email_reply'] ) echo ' checked="checked"'; ?> /> <?php _e( 'When a new reply is recieved', 'bbpm' ); ?><br />
			<input type="checkbox" id="email_add" name="email_add"<?php if ( $bbpm->settings['email_add'] ) echo ' checked="checked"'; ?> /> <?php _e( 'When a user is added to a conversation', 'bbpm' ); ?><br />
			<input type="checkbox" id="email_message" name="email_message"<?php if ( $bbpm->settings['email_message'] ) echo ' checked="checked"'; ?> /> <?php _e( 'Include contents of message', 'bbpm' ); ?>
		</div>
	</div>
	<div id="option-threads_per_page">
		<label for="threads_per_page">
			<?php _e( 'Maximum PM threads per page', 'bbpm' ); ?>
		</label>
		<div class="inputs">
			<input type="text" class="text short" id="threads_per_page" name="threads_per_page" value="<?php echo $bbpm->settings['threads_per_page']; ?>" />
			<p><?php _e( 'Enter 0 or leave this blank to use your forum\'s default setting.', 'bbpm' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbpm-admin' ); ?>
	<input type="submit" class="submit" value="<?php _e( 'Save settings', 'bbpm' ); ?>" />
</fieldset>
</form>
<?php
}

function bbpm_admin_header() {
	if ( basename( dirname( dirname( __FILE__ ) ) ) != 'my-plugins' ) {
		bb_admin_notice( sprintf( __( 'bbPM is installed in the "<code>%s</code>" directory. It should be installed in "<code>my-plugins</code>"', 'bbpm' ), basename( dirname( dirname( __FILE__ ) ) ) ), 'error' );
	}
	if ( strpos( __FILE__, '/' ) !== false && decoct( fileperms( dirname( dirname( __FILE__ ) ) ) & 0x1FF ) != '755' ) {
		bb_admin_notice( sprintf( __( 'The <code>my-plugins</code> directory has its permissions set to %s. This is not recommended. Please use 755 instead.', 'bbpm' ), decoct( fileperms( dirname( dirname( __FILE__ ) ) ) & 0x1FF ) ), 'error' );
	}
}
add_action( 'bb_admin-header.php', 'bbpm_admin_header' );

/**
 * @access private
 */
function bbPM_update_helper_helper_0_1_alpha4( $data ) {
	return $data->ID;
}

/**
 * Used to get a bbPM header link in a different place in the template.
 *
 * To use this, add the following code to your template where you would like it
 * to appear, and change the admin setting so it won't show up twice.
 * <code>
 * <?php if ( function_exists( 'bbpm_messages_link' ) ) bbpm_messages_link(); ?>
 * </code>
 *
 * @see bbPM::header_link()
 * @global bbPM 
 * @uses bbPM::get_link() linking to the PM page
 * @uses bbPM::count_pm() counting new messages
 * @since 0.1-alpha5
 * @return void
 */
function bbpm_messages_link() {
	global $bbpm;

	$count = $bbpm->count_pm( bb_get_current_user_info( 'ID' ), true );

	if ( $count )
		echo '<a class="pm-new-messages-link" href="' . $bbpm->get_link() . '">' . sprintf( _n( '1 new Private Message!', '%s new Private Messages!', $count, 'bbpm' ), bb_number_format_i18n( $count ) ) . '</a>';
	else
		echo '<a class="pm-no-new-messages-link" href="' . $bbpm->get_link() . '">' . __( 'Private Messages', 'bbpm' ) . '</a>';
}

// Emulate an actual page if pretty permalinks is off.
if ( isset( $_GET['pm'] ) && !bb_get_option( 'mod_rewrite' ) ) {
	$_SERVER['REQUEST_URI'] = bb_get_option( 'path' ) . rtrim( 'pm/' . $_GET['pm'], '/' );
}
