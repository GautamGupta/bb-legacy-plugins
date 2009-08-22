<?php
/*
Plugin Name: bbPM
Plugin URI: http://nightgunner5.wordpress.com/tag/bbpm/
Description: Adds the ability for users of a forum to send private messages to each other.
Version: 0.1-alpha6
Author: Nightgunner5
Author URI: http://llamaslayers.net/daily-llama/
Text Domain: bbpm
Domain Path: /translations/
*/

load_plugin_textdomain( 'bbpm', dirname( __FILE__ ) . '/translations' );

class bbPM_Message {
	private $read_link;
	private $reply_link;

	private $ID;
	private $title;
	private $from;
	private $text;
	private $date;
	private $reply;
	private $reply_to;
	private $thread_depth;
	private $exists;
	private $thread;

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
			$this->read_link    = BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?' . $row->pm_thread . '#pm-' . $row->ID;
			$this->reply_link   = BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?' . $row->ID . '/reply';
		}
		$this->ID           = (int)$row->ID;
		$this->title        = apply_filters( 'get_topic_title', $row->pm_title );
		$this->from         = new BP_User( (int)$row->pm_from );
		$this->text         = apply_filters( 'get_post_text', $row->pm_text );
		$this->date         = (int)$row->sent_on;
		$this->reply        = (bool)(int)$row->reply_to;
		$this->reply_to     = (int)$row->reply_to;
		$this->thread_depth = (int)$row->thread_depth;
		$this->thread       = (int)$row->pm_thread;
		$this->exists       = true;
	}

	function __get( $varName ) {
		return $this->$varName;
	}
}

class bbPM {
	var $settings;
	private $version;
	private $max_inbox;
	private $current_pm;
	private $the_pm;
	private $all_pm;

	function bbPM() { // INIT
		global $bbdb;
		$bbdb->bbpm = $bbdb->prefix . 'bbpm';

		// Put two slashes before the next line if you do not want a "PM this user" link in every profile.
		add_action( 'bb_profile.php', array( &$this, 'profile_filter_action' ) );
		// Put two slashes before each of the next two lines if you do not want a "PM this user" link under the author name of every post.
		add_filter( 'post_author_title_link', array( &$this, 'post_title_filter' ), 11, 2 );
		add_filter( 'post_author_title', array( &$this, 'post_title_filter' ), 11, 2 );

		add_action( 'bb_admin_menu_generator', array( &$this, 'admin_add' ) );
		add_filter( 'bb_template', array( &$this, 'template_filter' ), 10, 2 );

		$this->current_pm = array();

		$this->settings = bb_get_option( 'bbpm_settings' );
		$this->version = $this->settings ? $this->settings['version'] : false;

		if ( !$this->version || $this->version != '0.1-alpha6' )
			$this->update();

		if ( $this->settings['auto_add_link'] )
			add_filter( 'bb_logout_link', array( &$this, 'header_link' ) );

		$this->max_inbox = $this->settings['max_inbox'];
	}

	function __get( $varName ) {
		if ( !in_array( $varName, array( 'version', 'max_inbox', 'current_pm', 'the_pm' ) ) )
			return null;
		return $this->$varName;
	}

	private function update() {
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

				if ( $legacy_messages ) {
					foreach ( $legacy_messages as $msg )
						$bbdb->insert( $bbdb->bbpm, array(
							'pm_title' => attribute_escape( $msg->pmtitle ),
							'pm_read'  => (int)$msg->seen,
							'pm_from'  => (int)$msg->id_sender,
							'pm_to'    => (int)$msg->id_receiver,
							'pm_text'  => apply_filters( 'pre_post', $msg->message ),
							'sent_on'  => strtotime( $msg->created_on ),
						) );

					$bbdb->query( 'DROP TABLE `' . $bbdb->prefix . 'privatemessages`' );
				}
				$this->settings['max_inbox'] = 50; // Will be configurable later.

				unset( $legacy_messages );
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
				$_all_pm = (array)$bbdb->get_col( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `reply_to` IS NULL AND `thread_id`=0' );
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
				}

				// At the end of all of the updates:
				$this->settings['version'] = '0.1-alpha6';
				$this->version             = '0.1-alpha6';
				bb_update_option( 'bbpm_settings', $this->settings );

			case '0.1-alpha6':
				// Do nothing, this is the newest version.
		}
	}

	private function update_helper_0_1_alpha4( $start_id ) {
		global $bbdb;

		$thread_items = array( $start_id );

		$pm_list = array_map( 'bbPM_update_helper_helper_0_1_alpha4', (array)$bbdb->get_results( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `reply_to`=\'' . $start_id . '\'' ) );

		$thread_items = array_merge( $thread_items, $pm_list );

		foreach ( $pm_list as $pm ) {
			$thread_items = array_merge( $thread_items, $this->update_helper_0_1_alpha4( $pm ) );
		}

		return array_unique( $thread_items );
	}

	function count_pm( $user_id = 0, $unread_only = false ) {
		global $bbdb;

		$thread_member_of = (array)$bbdb->get_col( $bbdb->prepare( 'SELECT `object_id` FROM `' . $bbdb->meta . '` WHERE `object_type`=%s AND `meta_key`=%s AND `meta_value` LIKE %s', 'bbpm_thread', 'to', '%,' . $user_id . ',%' ) );

		if ( function_exists( 'wp_cache_add' ) )
			wp_cache_add( $user_id, $thread_member_of, 'bbpm-user-messages' );

		$threads = count( $thread_member_of );

		$this->cache_threads( $thread_member_of );

		if ( $unread_only )
			foreach ( $thread_member_of as $thread )
				if ( $this->get_last_read( $thread ) == $this->get_thread_meta( $thread, 'last_message' ) )
					$threads--;

		return $threads;
	}

	function pm_pages() {
		return;

		global $bbdb;

		$total = $bbdb->get_var( $bbdb->prepare( 'SELECT COUNT(*) FROM `' . $bbdb->meta . '` WHERE `meta_value` LIKE %s AND `object_type`=%s', '%,' . bb_get_current_user_info( 'ID' ) . ',%', 'bbpm_thread' ) );
	}

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

			if ( function_exists( 'wp_cache_get' ) && false !== $threads = wp_cache_get( bb_get_current_user_info( 'ID' ), 'bbpm-user-messages' ) )
				$threads = array_slice( $threads, $start, $end );
			else
				$threads = (array)$bbdb->get_col( $bbdb->prepare( 'SELECT `object_id` FROM `' . $bbdb->meta . '` WHERE `object_type` = %s AND `meta_key` = %s AND `meta_value` LIKE %s LIMIT ' . $start . ',' . $end, 'bbpm_thread', 'to', '%,' . bb_get_current_user_info( 'ID' ) . ',%' ) );

			$this->cache_threads( $threads );

			$this->current_pm[$start . '_' . $end] = array();

			foreach ( $threads as $thread ) {
				$this->current_pm[$start . '_' . $end][] = array( 'id' => $thread, 'members' => $this->get_thread_members( $thread ), 'title' => $this->get_thread_title( $thread ), 'last_message' => $this->get_thread_meta( $thread, 'last_message' ) );
			}

			usort( $this->current_pm[$start . '_' . $end], array( &$this, '_newer_last_message' ) );

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

	function _newer_last_message( $a, $b ) {
		return $a['last_message'] > $b['last_message'] ? -1 : 1;
	}

	/**
	 * @param int $id_reciever
	 * @param string $title
	 * @param string $message
	 * @return string|bool The URL of the new message or false if any of the message boxes is full.
	 */
	function send_message( $id_reciever, $title, $message ) {
		if ( $this->count_pm( $pm['pm_from'] ) > $this->max_inbox || $this->count_pm( $pm['pm_to'] ) > $this->max_inbox )
			return false;

		global $bbdb;

		$pm = array(
			'pm_from'   => (int)bb_get_current_user_info( 'ID' ),
			'pm_text'   => apply_filters( 'pre_post', $message ),
			'sent_on'   => bb_current_time( 'timestamp' ),
			'pm_thread' => $bbdb->get_var( 'SELECT MAX( `pm_thread` ) FROM `' . $bbdb->bbpm . '`' ) + 1
		);

		$bbdb->insert( $bbdb->bbpm, $pm );

		$msg = new bbPM_Message( $bbdb->insert_id );

		bb_update_meta( $pm['pm_thread'], 'title', $title, 'bbpm_thread' );
		bb_update_meta( $pm['pm_thread'], 'to', bb_get_current_user_info( 'ID' ) == $id_reciever ? ',' . $id_reciever . ',' : ',' . bb_get_current_user_info( 'ID' ) . ',' . $id_reciever . ',', 'bbpm_thread' );

		if ( bb_get_current_user_info( 'ID' ) != $id_reciever )
			bb_mail( bb_get_user_email( $id_reciever ), get_user_display_name( bb_get_current_user_info( 'ID' ) ) . ' has sent you a private message on ' . bb_get_option( 'name' ) . '!', 'Hello, ' . get_user_display_name( $id_reciever ) . '!

' . get_user_display_name( bb_get_current_user_info( 'ID' ) ) . ' has sent you a private message on ' . bb_get_option( 'name' ) . '!

To read it now, go to the following address:

' . $msg->read_link );
		bb_update_meta( $pm['pm_thread'], 'last_message', $msg->ID, 'bbpm_thread' );

		return $msg->read_link;
	}

	function send_reply( $reply_to, $message ) {
		global $bbdb;

		$reply_to = new bbPM_Message( $reply_to );

		$pm = array(
			'pm_from'      => (int)bb_get_current_user_info( 'ID' ),
			'pm_text'      => apply_filters( 'pre_post', $message ),
			'sent_on'      => bb_current_time( 'timestamp' ),
			'pm_thread'    => $reply_to->thread,
			'reply_to'     => (int)$reply_to->ID,
			'thread_depth' => $reply_to->thread_depth + 1
		);

		$bbdb->insert( $bbdb->bbpm, $pm );

		$msg = new bbPM_Message( $bbdb->insert_id );

		bb_update_meta( $pm['pm_thread'], 'last_message', $msg->ID, 'bbpm_thread' );

		$to = array_filter( explode( ',', $bbdb->get_var( $bbdb->prepare( 'SELECT `meta_value` FROM `' . $bbdb->meta . '` WHERE `meta_key` = %s AND `object_type` = %s AND `object_id` = %d', 'to', 'bbpm_thread', $pm['pm_thread'] ) ) ) );
		foreach ( $to as $recipient ) {
			bb_mail( bb_get_user_email( $recipient ), get_user_display_name( bb_get_current_user_info( 'ID' ) ) . ' has sent you a private message on ' . bb_get_option( 'name' ) . '!', 'Hello, ' . get_user_display_name( $recipient ) . '!

' . get_user_display_name( bb_get_current_user_info( 'ID' ) ) . ' has sent you a private message on ' . bb_get_option( 'name' ) . '!

To read it now, go to the following address:

' . $msg->read_link );
		}

		return $msg->read_link;
	}

	private function _make_thread( $thread, $reply_to = null, $thread_id = null ) {
		$ret = array();

		foreach ( $thread as $pm ) {
			if ( ( ( $thread_id && $pm->pm_thread == $thread_id ) || !$thread_id ) && $pm->reply_to == $reply_to ) {
				$ret[] = $pm->ID;
				$ret = array_merge( $ret, $this->_make_thread( $thread, $pm->ID, $thread_id ) );
			}
		}

		return $ret;
	}

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

	function cache_threads( $IDs ) {
		if ( !function_exists( 'wp_cache_add' ) )
			return;

		foreach ( $IDs as $i => $id ) {
			if ( wp_cache_get( $id, 'bbpm-cached' ) )
				unset( $IDs[$i] );

			wp_cache_add( $id, true, 'bbpm-cached' );
		}

		if ( !$IDs )
			return;

		global $bbdb;

		$users = array();

		$thread_posts = (array)$bbdb->get_results( 'SELECT * FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` IN (' . implode( ',', array_map( 'intval', $IDs ) ) . ') ORDER BY `ID`' );
		$thread_meta = (array)$bbdb->get_results( 'SELECT `object_id`,`meta_key`,`meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type` = \'bbpm_thread\' AND `object_id` IN (' . implode( ',', array_map( 'intval', $IDs ) ) . ')' );

		foreach ( $thread_meta as $meta ) {
			wp_cache_add( $meta->meta_key, $meta->meta_value, 'bbpm-thread-' . $meta->object_id );

			if ( $meta->meta_key == 'to' )
				$users = array_merge( $users, explode( ',', $meta->meta_value ) );
		}

		foreach ( $thread_posts as $pm )
			wp_cache_add( (int)$pm->ID, $pm, 'bbpm' );

		foreach ( $IDs as $id ) {
			$thread_ids = $this->_make_thread( $thread_posts, null, $id );

			wp_cache_add( (int)$id, $thread_ids, 'bbpm-thread' );
		}

		$users = array_values( array_filter( array_unique( $users ) ) );

		bb_cache_users( $users );
	}

	function get_thread_title( $thread_ID ) {
		return $this->get_thread_meta( $thread_ID, 'title' );
	}

	function get_thread_members( $thread_ID ) {
		return array_values( array_filter( explode( ',', $this->get_thread_meta( $thread_ID, 'to' ) ) ) );
	}

	function can_read_message( $ID, $user_id = 0 ) {
		$msg = new bbPM_Message( $ID );
		if ( !$msg->exists )
			return false;

		return $this->can_read_thread( $msg->thread, $user_id );
	}

	function can_read_thread( $ID, $user_id = 0 ) {
		$user_id = (int)$user_id;

		if ( !$user_id )
			$user_id = bb_get_current_user_info( 'ID' );

		return strpos( $this->get_thread_meta( $ID, 'to' ), ',' . $user_id . ',' ) !== false;
	}

	function unsubscribe( $ID ) {
		global $bbdb;

		if ( $members = $this->get_thread_meta( $ID, 'to' ) ) {
			if ( strpos( $members, ',' . bb_get_current_user_info( 'ID' ) . ',' ) !== false ) {
				$members = str_replace( ',' . bb_get_current_user_info( 'ID' ), '', $members );
				if ( $members == ',' ) {
					$bbdb->query( $bbdb->prepare( 'DELETE FROM `' . $bbdb->bbpm . '` WHERE `pm_thread` = %d', $ID ) );
					$bbdb->query( $bbdb->prepare( 'DELETE FROM `' . $bbdb->meta . '` WHERE `object_type` = %s AND `object_id` = %d', 'bbpm_thread', $ID ) );
				} else {
					bb_update_meta( $ID, 'to', $members, 'bbpm_thread' );
				}
			}
		}
	}

	function add_member( $ID, $user ) {
		global $bbdb;

		if ( $members = $this->get_thread_meta( $ID, 'to' ) ) {
			if ( strpos( $members, ',' . $user . ',' ) === false ) {
				$members .= ',' . $user;
				bb_update_meta( $ID, 'to', $members, 'bbpm_thread' );
				bb_mail( bb_get_user_email( $user ), sprintf( __( '%s has added you to a conversation on %s!', 'bbpm' ), get_user_display_name( bb_get_current_user_info( 'ID' ) ), bb_get_option( 'name' ) ), sprintf( __( "Hello, %s!\n%s has added you to a private message conversation on %s!\nTo read it now, go to the following address:\n%s", 'bbpm' ), get_user_display_name( $user ), get_user_display_name( bb_get_current_user_info( 'ID' ) ), bb_get_option( 'name' ), bb_get_option( 'mod_rewrite' ) ? bb_get_uri( 'pm/' . $ID ) : BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?' . $ID ) );
			}
		}
	}

	function new_pm_link() {
		if ( bb_get_option( 'mod_rewrite' ) )
			bb_uri( 'pm/new' );
		else
			echo BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?new';
	}

	function template_filter( $a, $b ) {
		if ( is_pm() && $b == '404.php' ) {
			if ( !$template = bb_get_template( 'privatemessages.php', false ) ) {
				$template = dirname( __FILE__ ) . '/privatemessages.php';
			}
			return $template;
		}

		return $a;
	}

	function profile_filter_action() {
		add_filter( 'get_profile_info_keys', array( &$this, 'profile_filter' ) );
	}

	function profile_filter( $keys ) {
		global $user_id;
		if ( bb_get_current_user_info( 'ID' ) != $user_id && bb_current_user_can( 'write_posts' ) ) {
			echo '<a href="' . $this->get_send_link( $user_id ) . '">' . __( 'PM this user', 'bbpm' ) . '</a>';
		}
		return $keys;
	}

	function post_title_filter( $text, $post_id ) {
		if ( $post_id && ( $user_id = get_post_author_id( $post_id ) ) && bb_current_user_can( 'write_posts' ) ) {
			$text .= "<br/>\n";
			$text .= '<a href="' . $this->get_send_link( $user_id ) . '">' . __( 'PM this user', 'bbpm' ) . '</a>';
		}
		return $text;
	}

	function get_link() {
		if ( bb_get_option( 'mod_rewrite' ) )
			return bb_get_uri( 'pm' );
		return BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/';
	}

	function get_send_link( $user_id = 0 ) {
		$user_name = get_user_name( bb_get_user_id( $user_id ) );

		if ( bb_get_option( 'mod_rewrite' ) )
			return bb_get_uri( 'pm/new/' . urlencode( $user_name ) );
		return BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?new/' . urlencode( $user_name );
	}

	function header_link( $link ) {
		$count = $this->count_pm( bb_get_current_user_info( 'ID' ), true );

		if ( $count )
			return $link . ' | <big><a href="' . $this->get_link() . '">' . sprintf( _n( '1 new Private Message!', '%s new Private Messages!', $count, 'bbpm' ), bb_number_format_i18n( $count ) ) . '</a></big>';
		return $link . ' | <a href="' . $this->get_link() . '">' . __( 'Private Messages', 'bbpm' ) . '</a>';
	}

	function admin_add() {
		bb_admin_add_submenu( __( 'bbPM', 'bbpm' ), 'use_keys', 'bbpm_admin_page', 'options-general.php' );
	}

	function get_last_read( $thread_ID ) {
		return (int)bb_get_usermeta( bb_get_current_user_info( 'ID' ), 'bbpm_last_read_' . (int)$thread_ID );
	}

	function get_thread_meta( $thread_ID, $key ) {
		if ( !function_exists( 'wp_cache_get' ) || false === $result = wp_cache_get( $key, 'bbpm-thread-' . $thread_ID ) ) {
			global $bbdb;
			$result = $bbdb->get_var( $bbdb->prepare( 'SELECT `meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type` = %s AND `meta_key` = %s AND `object_id` = %d', 'bbpm_thread', $key, $thread_ID ) );

			if ( function_exists( 'wp_cache_add' ) )
				wp_cache_add( $key, $result, 'bbpm-thread-' . $thread_ID );
		}

		return $result;
	}

	function mark_read( $thread_ID ) {
		if ( $this->get_last_read( $thread_ID ) != $this->get_thread_meta( $thread_ID, 'last_message' ) )
			bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'bbpm_last_read_' . (int)$thread_ID, (int)$this->get_thread_meta( $thread_ID, 'last_message' ) );
	}

	/* Loop - Threads */
	function thread_alt_class() {
		alt_class( 'bbpm_threads', $this->the_pm['last_message'] == $this->get_last_read( $this->the_pm['id'] ) ? '' : 'unread_posts_row' );
	}

	function thread_freshness() {
		$the_pm = new bbPM_Message( $this->the_pm['last_message'] );

		echo bb_since( $the_pm->date );
	}

	function thread_unsubscribe_url() {
		echo bb_nonce_url( BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/pm.php?unsubscribe=' . $this->the_pm['id'], 'bbpm-unsubscribe-' . $this->the_pm['id'] );
	}

	function thread_read_before() {
		if ( $this->the_pm['last_message'] != $this->get_last_read( $this->the_pm['id'] ) ) {
			echo '<span class="unread_posts">';

			if ( !function_exists( 'utplugin_show_unread' ) )
				echo '<strong>New:</strong> ';
		}
	}

	function thread_read_after() {
		if ( $this->the_pm['last_message'] != $this->get_last_read( $this->the_pm['id'] ) )
			echo '</span>';
	}
}
global $bbpm;
$bbpm = new bbPM;

function is_pm() {
	return substr( ltrim( str_replace( bb_get_option( 'path' ), '', $_SERVER['REQUEST_URI'] . '/' ), '/' ), 0, 3 ) == 'pm/';
}

function bbpm_admin_page() {
	global $bbpm;

	if ( bb_verify_nonce( $_POST['_wpnonce'], 'bbpm-admin' ) ) {
		$bbpm->settings['max_inbox'] = max( (int)$_POST['max_inbox'], 1 );
		$bbpm->settings['auto_add_link'] = !empty( $_POST['auto_add_link'] );

		bb_update_option( 'bbpm_settings', $bbpm->settings );
	}
?>
<h2><?php _e( 'bbPM', 'bbpm' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>
<form class="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<fieldset>
	<div id="option-max_inbox">
		<label for="max_inbox">
			<?php _e( 'Maximum inbox/outbox size', 'bbpm' ); ?>
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
	if ( strpos( __FILE__, '/' ) !== false && fileperms( dirname( dirname( __FILE__ ) ) ) & 0x1FF != 0755 ) {
		bb_admin_notice( sprintf( __( 'The <code>my-plugins</code> directory has its permissions set to %s. This is not recommended. Please use 755 instead.', 'bbpm' ), decoct( fileperms( dirname( dirname( __FILE__ ) ) ) & 0x1FF ) ), 'error' );
	}
}
add_action( 'bb_admin-header.php', 'bbpm_admin_header' );

function bbPM_update_helper_helper_0_1_alpha4( $data ) {
	return $data->ID;
}

function bbpm_messages_link() {
	global $bbpm;

	$count = $bbpm->count_pm( bb_get_current_user_info( 'ID' ), true );

	if ( $count )
		echo '<a class="pm-new-messages-link" href="' . $bbpm->get_link() . '">' . sprintf( _n( '1 new Private Message!', '%s new Private Messages!', $count, 'bbpm' ), bb_number_format_i18n( $count ) ) . '</a>';
	else
		echo '<a class="pm-no-new-messages-link" href="' . $bbpm->get_link() . '">' . __( 'Private Messages', 'bbpm' ) . '</a>';
}

?>