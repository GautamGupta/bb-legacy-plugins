<?php
/*
Plugin Name: bbPM
Plugin URI: http://nightgunner5.wordpress.com/tag/bbpm/
Description: Adds the ability for users of a forum to send private messages to each other.
Version: 0.1-alpha4b
Author: Nightgunner5
Author URI: http://llamaslayers.net/daily-llama/
Text Domain: bbpm
Domain Path: translations
*/

load_plugin_textdomain( 'bbpm', dirname( __FILE__ ) . '/translations' );

if ( version_compare( bb_get_option( 'version' ), '1.0-dev', '<' ) )
	include_once dirname( __FILE__ ) . '/compat.php';

class bbPM_Message {
	private $read_link;
	private $delete_link;
	private $reply_link;

	private $ID;
	private $title;
	private $read;
	private $from;
	private $to;
	private $text;
	private $date;
	private $del_s;
	private $del_r;
	private $reply;
	private $reply_to;
	private $thread_depth;
	private $exists;

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
		$this->delete_link  = bb_nonce_url( BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/pm.php?delete=' . $row->ID, 'bbpm-delete-' . $row->ID );
		$this->ID           = (int)$row->ID;
		$this->title        = apply_filters( 'get_topic_title', $row->pm_title );
		$this->read         = (bool)(int)$row->pm_read;
		$this->from         = new BP_User( (int)$row->pm_from );
		$this->to           = new BP_User( (int)$row->pm_to );
		$this->text         = apply_filters( 'get_post_text', $row->pm_text );
		$this->date         = (int)$row->sent_on;
		$this->del_s        = (bool)(int)$row->del_sender;
		$this->del_r        = (bool)(int)$row->del_reciever;
		$this->reply        = (bool)(int)$row->reply_to;
		$this->reply_to     = (int)$row->reply_to;
		$this->thread_depth = (int)$row->thread_depth;
		$this->thread       = (int)$row->pm_thread;
		$this->exists       = true;
	}

	function __get( $varName ) {
		return $this->$varName;
	}

	function delete() {
		global $bbdb;

		$bbdb->query( $bbdb->prepare( 'DELETE FROM `' . $bbdb->bbpm . '` WHERE `ID`=%d LIMIT 1', $this->ID ) );

		if ( function_exists( 'wp_cache_delete' ) )
			wp_cache_delete( $this->ID, 'bbpm' );
	}
}

class bbPM {
	var $settings;
	private $version;
	private $max_inbox;
	private $current_id;
	private $current_sent_id;
	private $the_pm;
	private $all_pm;

	function bbPM() { // INIT
		global $bbdb;
		$bbdb->bbpm = $bbdb->prefix . 'bbpm';

		// Put two slashes before the next line if you do not want a "PM this user" link in every profile.
		add_action( 'bb_profile.php', array( &$this, 'profile_filter_action' ) );
		// Put two slashes before each of the next two lines if you do not want a "PM this user" link under the author name of every post.
		add_filter( 'post_author_title_link', array( &$this, 'post_title_filter' ), 10, 2 );
		add_filter( 'post_author_title', array( &$this, 'post_title_filter' ), 10, 2 );

		add_filter( 'bb_logout_link', array( &$this, 'header_link' ) );
		add_action( 'bb_admin_menu_generator', array( &$this, 'admin_add' ) );
		add_filter( 'bb_template', array( &$this, 'template_filter' ), 10, 2 );

		$this->current_id      = 0;
		$this->current_sent_id = 0;

		$this->settings = bb_get_option( 'bbpm_settings' );
		$this->version = $this->settings ? $this->settings['version'] : false;

		if ( !$this->version || version_compare( $this->version, '0.1-alpha4b', '<' ) )
			$this->update();

		$this->max_inbox = $this->settings['max_inbox'];
	}

	function __get( $varName ) {
		if ( !in_array( $varName, array( 'version', 'max_inbox', 'current_id', 'current_sent_id', 'the_pm' ) ) )
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

				// At the end of all of the updates:
				$this->settings['version'] = '0.1-alpha4b';
				$this->version             = '0.1-alpha4b';
				bb_update_option( 'bbpm_settings', $this->settings );

			case '1.0-alpha4b':
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

	function count_pm( $user_id = 0, $sent = false, $unread_only = false ) {
		global $bbdb;

		if ( $sent )
			return (int)$bbdb->get_var( $bbdb->prepare( 'SELECT COUNT(*) FROM `' . $bbdb->bbpm . '` WHERE `pm_from`=%d AND `del_sender`=0' . ( $unread_only ? ' AND `pm_read`=0' : '' ), bb_get_user_id( $user_id ? $user_id : bb_get_current_user_info( 'ID' ) ) ) );
		else
			return (int)$bbdb->get_var( $bbdb->prepare( 'SELECT COUNT(*) FROM `' . $bbdb->bbpm . '` WHERE `pm_to`=%d AND `del_reciever`=0' . ( $unread_only ? ' AND `pm_read`=0' : '' ), bb_get_user_id( $user_id ? $user_id : bb_get_current_user_info( 'ID' ) ) ) );
	}

	function have_pm() {
		global $bbdb;

		$pm_id = (int)$bbdb->get_var( $bbdb->prepare( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `pm_to`=%d AND `del_reciever`=0 ORDER BY `sent_on` DESC LIMIT ' . $this->current_id . ',1', bb_get_current_user_info( 'id' ) ) );

		if ( $pm_id ) {
			$this->current_id++;
			$this->the_pm = new bbPM_Message( $pm_id );
			return true;
		}
		return false;
	}

	function sent_pm() {
		global $bbdb;

		$pm_id = (int)$bbdb->get_var( $bbdb->prepare( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `pm_from`=%d AND `del_sender`=0 ORDER BY `sent_on` DESC LIMIT ' . $this->current_sent_id . ',1', bb_get_current_user_info( 'id' ) ) );

		if ( $pm_id ) {
			$this->current_sent_id++;
			$this->the_pm = new bbPM_Message( $pm_id );
			return true;
		}
		return false;
	}

	function send_message( $id_reciever, $title, $message, $reply_to = null ) { // Returns the url of the new message.
		global $bbdb;

		$pm = array(
			'pm_title'  => attribute_escape( $title ),
			'pm_from'   => (int)bb_get_current_user_info( 'ID' ),
			'pm_to'     => (int)$id_reciever,
			'pm_text'   => apply_filters( 'pre_post', $message ),
			'sent_on'   => time(),
			'pm_thread' => $bbdb->get_var( 'SELECT MAX( `pm_thread` ) FROM `' . $bbdb->bbpm . '`' ) + 1,
		);

		if ( $reply_to && $this->can_read_message( $reply_to ) && $this->can_read_message( $reply_to, (int)$id_reciever ) ) {
			$pm['reply_to']     = (int)$reply_to;
			$reply_to           = new bbPM_Message( $reply_to );
			$pm['thread_depth'] = $reply_to->thread_depth + 1;
			$pm['pm_thread']    = $reply_to->thread;
		}

		if ( $this->count_pm( $pm['pm_from'], true ) > $this->max_inbox || $this->count_pm( $pm['pm_to'] ) > $this->max_inbox )
			return false;

		$bbdb->insert( $bbdb->bbpm, $pm );

		$msg = new bbPM_Message( $bbdb->insert_id );

		bb_mail( bb_get_user_email( $id_reciever ), get_user_display_name( bb_get_current_user_info( 'ID' ) ) . ' has sent you a private message on ' . bb_get_option( 'name' ) . '!', 'Hello, ' . get_user_display_name( $id_reciever ) . '!

' . get_user_display_name( bb_get_current_user_info( 'ID' ) ) . ' has sent you a private message on ' . bb_get_option( 'name' ) . '!

To read it now, go to the following address:

' . $msg->read_link );

		return $msg->read_link;
	}

	function get_thread( $id, $firstonly = false ) {
		global $bbdb;

		if ( !function_exists( 'wp_cache_get' ) || false === $thread_ids = wp_cache_get( (int)$id, 'bbpm-thread' ) ) {
			$thread_ids = (array)$bbdb->get_col( $bbdb->prepare( 'SELECT `ID` FROM `' . $bbdb->bbpm . '` WHERE `pm_thread`=%d ORDER BY `ID`', $id ) );
			if ( function_exists( 'wp_cache_add' ) )
				wp_cache_add( (int)$id, $thread_ids, 'bbpm-thread' );
		}

		if ( $firstonly )
			return new bbPM_Message( $thread_ids[0] );

		$thread = array();
		foreach ( $thread_ids as $ID ) {
			$thread[] = new bbPM_Message( $ID );
		}

		return $thread;
	}

	function can_read_message( $ID, $user_id = 0 ) {
		$msg = new bbPM_Message( $ID );
		if ( !$msg->exists )
			return false;

		if ( !$user_id )
			$user_id = bb_get_current_user_info( 'ID' );

		if ( $msg->from->ID == $user_id && !$msg->del_s )
			return 'from';
		if ( $msg->to->ID == $user_id && !$msg->del_r )
			return 'to';

		return false;
	}

	function can_read_thread( $ID, $user_id = 0 ) {
		$msg = $this->get_thread( $ID, true );

		if ( !$msg->exists )
			return false;


		if ( !$user_id )
			$user_id = bb_get_current_user_info( 'ID' );

		if ( $msg->from->ID == $user_id && !$msg->del_s )
			return 'from';
		if ( $msg->to->ID == $user_id && !$msg->del_r )
			return 'to';

		return false;
	}

	function delete_message( $ID ) {
		global $bbdb;

		if ( !$who = $this->can_read_message( $ID ) )
			bb_die( __( 'You can\'t delete that message!', 'bbpm' ) );

		$msg = new bbPM_Message( $ID );

		$total_delete = false;

		if ( $who == 'from' ) {
			if ( $msg->del_r ) {
				$msg->delete();
			} else {
				$bbdb->update( $bbdb->bbpm, array( 'del_sender' => 1 ), compact( 'ID' ) );
				if ( function_exists( 'wp_cache_delete' ) )
					wp_cache_delete( $ID, 'bbpm' );
			}
		} else {
			if ( $msg->del_s ) {
				$msg->delete();
			} else {
				$bbdb->update( $bbdb->bbpm, array( 'del_reciever' => 1 ), compact( 'ID' ) );
				if ( function_exists( 'wp_cache_delete' ) )
					wp_cache_delete( $ID, 'bbpm' );
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
		if ( is_pm() && $b == '404.php' )
			return dirname( __FILE__ ) . '/privatemessages.php';

		return $a;
	}

	function profile_filter_action() {
		add_filter( 'get_profile_info_keys', array( &$this, 'profile_filter' ) );
	}

	function profile_filter( $keys ) {
		global $user_id;
		if ( bb_get_current_user_info( 'ID' ) != $user_id ) {
			echo '<a href="' . $this->get_send_link( $user_id ) . '">' . __( 'PM this user', 'bbpm' ) . '</a>';
		}
		return $keys;
	}

	function post_title_filter( $text, $post_id ) {
		if ( $user_id = get_post_author_id( $post_id ) ) {
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
		$count = $this->count_pm( bb_get_current_user_info( 'ID' ), false, true );

		if ( $count )
			return $link . ' | <big><a href="' . $this->get_link() . '">' . sprintf( _n( '1 new Private Message!', '%s new Private Messages!', $count, 'bbpm' ), bb_number_format_i18n( $count ) ) . '</a></big>';
		return $link . ' | <a href="' . $this->get_link() . '">' . __( 'Private Messages', 'bbpm' ) . '</a>';
	}

	function admin_add() {
		global $bb_submenu;
		$bb_submenu['options-general.php'][] = array( __( 'bbPM', 'bbpm' ), 'use_keys', 'bbpm_admin_page' );
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

		bb_update_option( 'bbpm_settings', $bbpm->settings );
	}
?>
<h2><?php _e( 'bbPM', 'bbpm' ); ?></h2>
<form class="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<fieldset>
	<div>
		<label for="max_inbox">
			<?php _e( 'Maximum inbox/outbox size', 'bbpm' ); ?>
		</label>
		<div>
			<input type="text" class="text short" id="max_inbox" name="max_inbox" value="<?php echo $bbpm->settings['max_inbox']; ?>" />
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

function bbPM_update_helper_helper_0_1_alpha4( $data ) {
	return $data->ID;
}

?>