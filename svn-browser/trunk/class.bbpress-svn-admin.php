<?php

require( dirname(__FILE__) . '/class.automattic-svn-admin.php' );

class bbPress_SVN_Admin extends Automattic_SVN_Admin {
	var $requests_forum  = BBPRESS_SVN_ADMIN__REQUESTS_FORUM;
	var $approved_forum  = BBPRESS_SVN_ADMIN__APPROVED_FORUM;
	var $rejected_forum  = BBPRESS_SVN_ADMIN__REJECTED_FORUM;

	var $svn_access_file = BBPRESS_SVN_ADMIN__SVN_ACCESS_FILE;
	var $approved_mess   = BBPRESS_SVN_ADMIN__APPROVED_MESS;
	var $notify_email    = BBPRESS_SVN_ADMIN__NOTIFY_EMAIL;

	function init() {
		global $argc, $argv;

		add_filter( 'bb_user_has_cap', array(&$this, 'bb_user_has_cap'), 100, 3 );
		add_filter( 'bb_template', array(&$this, 'bb_template'), 10, 2 );
		add_action( 'bb_parse_query', array(&$this, 'bb_parse_query'), 100 );
		add_filter( 'bb_topic_labels', array(&$this, 'bb_topic_labels') );
		add_action( 'bb_repermalink_result', array(&$this, 'bb_repermalink_result'), 10, 2 );

		add_action( 'pre_post_form', array(&$this, 'pre_post_form'), 100 );
		add_action( 'post_post_form', array(&$this, 'post_post_form'), -1 );

		if ( bb_current_user_can( 'administrate' ) ) {
			bb_register_view( 'no-data', 'No Data', array( 'forum_id' => $this->tracker->live_forum, 'open' => 0 ) );
		}

		if ( isset($argc) && $argc ) {
			if ( 'svn' == $argv[1] && 2 < $argc ) {
				if ( 'access' == $argv[2] ) // php bb-load.php svn access
					return $this->write_svn_access( $this->svn_access_file );
				if ( 'add' == $argv[2] ) // php bb-load.php svn add
					return $this->add_approved_plugins();
			}
		}

		if ( $redir = $this->delta() ) {
			wp_redirect( $redir );
			exit;
		}

	}

	function bb_user_has_cap( $all_caps, $caps, $args ) {
		switch ( $args[0] ) :
		case 'write_topic' :
			$forum_id = $args[2];
			if ( in_array( $forum_id, array( $this->rejected_forum, $this->approved_forum ) ) )
				unset($all_caps['write_topics']);
			break;
		case 'write_post' :
			if ( bb_current_user_can( 'administrate' ) )
				break;

			$topic = get_topic( $args[2] );
			if ( !$topic || $topic->forum_id == $this->rejected_forum )
				unset($all_caps['write_posts']);
			break;
		endswitch;
		return $all_caps;
	}

	function bb_template( $file, $template ) {
		global $forum;
		if ( 'post-form.php' == $template && is_forum() ) {
			if ( $forum->forum_id == $this->requests_forum )
				return dirname( __FILE__ ) . '/request-form.php';
		} elseif ( 'forum.php' == $template ) {
			if ( $forum->forum_id != $this->requests_forum )
				return $file;

			if ( bb_current_user_can( 'administrate' ) )
				bb_enqueue_script( 'reject-requests', bb_get_plugin_uri( dirname( __FILE__ ) ) . '/reject-requests.js', array( 'jquery' ), 1 );
			return dirname( __FILE__ ) . '/requests-forum.php';
		}

		return $file;
	}

	function bb_parse_query( &$query ) {
		// Let admin and our queries go through
		if ( is_bb_admin() || 0 === strpos( $query->query_id, 'svn_tracker_' ) || 'bb_view_no-data' == $query->query_id )
			return;

		// Not so robust: forum_id could be more than one forum
		switch ( $query->query_vars['forum_id'] ) :
		case $this->tracker->live_forum :
			if ( !bb_current_user_can( 'administrate' ) )
				$query->query_vars['open'] = 1;
			return;
			break;
		case $this->requests_forum : // You can see your own topics if logged in
		case $this->approved_forum :
			if ( !bb_is_user_logged_in() ) {
				$query->query_vars = false;
			} elseif ( !bb_current_user_can( 'administrate' ) ) {
				$query->query_vars['topic_author_id'] = bb_get_current_user_info( 'id' );
				$query->query_vars['open'] = 1;
			}
			return;
			break;
		case $this->rejected_forum : // Admin only
			if ( !bb_current_user_can( 'administrate' ) )
				$query->query_vars = false;
			return;
			break;
		endswitch;

		// All other queries should be restricted
		$query->query_vars['forum_id'] = "-$this->rejected_forum,-$this->approved_forum,-$this->requests_forum";
		if ( !bb_current_user_can( 'administrate' ) )
			$query->query_vars['open'] = 1;
	}

	function bb_topic_labels( $label ) {
		global $topic;
		if ( $this->approved_forum == $topic->forum_id )
			return sprintf(__('[approved] %s'), $label);
		return $label;
	}


	function bb_repermalink_result( $url, $location ) {
		if ( 'forum-page' !== $location )
			return $url;
		global $bb, $forum;

		list($url, $anchor) = explode( '#', $url );
		if ( isset($anchor) && $anchor )
			$anchor = "#$anchor";

		list($url, $query) = explode( '?', $url );
		if ( isset($query) && $query )
			$query = "?$query";

		if ( $forum->forum_id == $this->requests_forum )
			$bb->page_topics = -1;

		if ( bb_current_user_can( 'administrate' ) || !in_array( $forum->forum_id, array( $this->rejected_forum, $this->approved_forum ) ) ) // Admins can see the other forums
			return "$url$query$anchor";

		// Hoi polloi cannot
		return bb_get_option( 'uri' ) . "$query$anchor";
	}

	function admin_page() {
		if ( !$topic = get_topic( get_topic_id() ) )
			return;
		if ( !$this->current_user_can_admin() )
			return;

		$r  = "<h3>Links</h3>\n";
		$r .= "<ul id='admin-links'>\n";
		$r .= "\t<li><a href='{$this->tracker->svn_url}$topic->topic_slug/'>SVN Repo</a></li>\n";
		if ( $this->tracker->trac_url ) {
			$r .= "\t<li><a href='{$this->tracker->trac_url}browser/$topic->topic_slug/'>Trac Browser</a></li>\n";
			$r .= "\t<li><a href='{$this->tracker->trac_url}log/$topic->topic_slug/'>Revision Log</a></li>\n";
		}
		$r .= "</ul>\n\n";

//		$r .= "<h3>" . $this->update_link() . "</h3>\n\n";

		$r .= "<h3>Committers</h3>\n";
		$r .= "<ul id='committers'>\n";
		$current_user_name = bb_get_current_user_info( 'name' );
		foreach ( $this->get_committers() as $committer ) {
			$delete = bb_nonce_url( add_query_arg( array( 'action' => 'delete-committer', 'committer' => $committer, 'topic_id' => $topic->topic_id ), $this->tracker->section_url( 'admin' ) ), "delete-committer_{$committer}_$topic->topic_id" );
			if ( $user = bb_get_user( $committer ) )
				$text = "<a href='" . get_user_profile_link( $user->ID ) . "'>$committer</a>";
			else
				$text = $committer;
			if ( $committer != $current_user_name )
				$text .= " [<a href='$delete'>remove</a>]";
			$r .= "\t<li>$text</li>\n";
		}
		$r .= "</ul>\n";
		$r .= "<form action='' method='post'>\n";
		$r .= "<p>\n";
		$r .= "\t<input type='text' name='committer' value='' />\n";
		$r .= "\t<input type='hidden' name='action' value='add-committer' />\n";
		ob_start();
			bb_nonce_field( 'add-committer_' . $topic->topic_id );
			$r .= "\t" . ob_get_contents() . "\n";
		ob_end_clean();
		$r .= "\t<input type='hidden' name='topic_id' value='$topic->topic_id' />\n";
		$r .= "\t<input type='submit' value='Add Committer' />\n";
		$r .= "</p>\n";
		$r .= "</form>\n\n";

		if ( bb_current_user_can( 'administrate' ) ) {
			$primary_author = attribute_escape( $topic->topic_poster_name );
			$r .= "<h3>Primary Author</h3>\n";
			$r .= "<form action='' method='post'>\n";
			$r .= "<p>\n";
			$r .= "\t<input type='text' name='primary-author' value='$primary_author' />\n";
			$r .= "\t<input type='hidden' name='action' value='primary-author' />\n";
			ob_start();
				bb_nonce_field( 'primary-author_' . $topic->topic_id );
				$r .= "\t" . ob_get_contents() . "\n";
			ob_end_clean();
			$r .= "\t<input type='hidden' name='topic_id' value='$topic->topic_id' />\n";
			$r .= "\t<input type='submit' value='Change Author' />\n";
			$r .= "</p>\n";
			$r .= "</form>\n\n";
		}
		
		return $r;
	}

	function requests_page() {
		global $topics, $topic, $forums, $forum, $bb_posts, $bb_post;
		if ( !bb_is_user_logged_in() )
			return false;

		if ( !$can_admin = bb_current_user_can( 'administrate' ) ) {
			$requests_query = new BB_Query( 'topic', array(
				'forum_id' => "$this->requests_forum,$this->approved_forum",
				'topic_author_id' => bb_get_current_user_info( 'id' ),
				'open' => 1
			), 'svn_tracker_requests_user' );
			$topics = $requests_query->results;
		}

		$class = '';

		if ( $topics ) :
	?>

	<form action='' method='post'>

	<?php	foreach ( (array) $topics as $topic ) :
			bb_get_first_post();
			$user_topics_query = new BB_Query( 'topic', array( 'forum_id' => $this->tracker->live_forum, 'per_page' => 1, 'topic_author_id' => $topic->topic_poster ), 'svn_tracker_requets' );
			$class = $user_topics_query->count && $can_admin ? 'pending has-plugins' : 'pending';
	?>

		<div<?php alt_class( 'requests', $class ); ?>>
			<p class="request-info">
				<?php bb_topic_labels(); ?><strong><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></strong>
				from
				<a href="<?php user_profile_link( get_topic_author() ); ?>"><?php topic_author(); ?></a>
	<?php		if ( isset($topic->uri) && $topic->uri && 'http://' != $topic->uri ) : ?>

				[<a href="<?php echo bb_fix_link( $topic->uri ); ?>">plugin</a>]

	<?php		endif; ?>

			</p>
			<p class="request-time">
				<?php topic_time( 'Y-n-j' ); ?>
			</p>

			<br class="clear" />

			<div class="description"><?php post_text(); ?></div>

	<?php		if ( 1 < $topic->topic_posts ) : ?>

			<p class="request-more"><a href="<?php topic_link(); ?>"><?php printf( __ngettext( '%s reply', '%s replies', $topic->topic_posts - 1 ), $topic->topic_posts - 1 ); ?></a></p>
			<br class="clear" />

	<?php		endif; if ( $can_admin ) : ?>

			<p>
				<input id="<?php echo $topic->topic_slug; ?>-0" type="radio" value="0" name="mod[<?php echo $topic->topic_slug; ?>]" checked="checked" />
				<label for="<?php echo $topic->topic_slug; ?>-0">Let it ride</label>
				<input id="<?php echo $topic->topic_slug; ?>-1" type="radio" value="1" name="mod[<?php echo $topic->topic_slug; ?>]" />
				<label for="<?php echo $topic->topic_slug; ?>-1">Approve</label>
				<input id="<?php echo $topic->topic_slug; ?>-2" type="radio" value="2" name="mod[<?php echo $topic->topic_slug; ?>]" class="reject-input" />
				<label for="<?php echo $topic->topic_slug; ?>-2">Reject</label>
			</p>

	<?php		endif; ?>

		</div>

	<?php	endforeach; if ( $can_admin ) : ?>

		<p class="submit">
			<input type="hidden" name="action" value="moderate-requests" />
			<?php bb_nonce_field( 'moderate-requests' ); ?>
			<input type="submit" value="Moderate Requests" />
		</p>

	<?php endif; ?>

	</form>

	<?php
		endif;

		return $can_admin;
	}

	function update_link( $id  = 0 ) {
		if ( !$topic = get_topic( get_topic_id( $id ) ) )
			return false;
//		if ( !$this->current_user_can_admin( $topic->topic_id ) )
//			return false;
		if ( !bb_current_user_can( 'administrate' ) )
			return false;
	
		return "<a href='" . clean_url( bb_nonce_url(add_query_arg( array('action' => 'update', 'topic_id' => $topic->topic_id), $this->tracker->section_url( 'admin', $topic->topic_id ) ), 'update_' . $topic->topic_id ) ) . "'>Update this plugin</a>.\n";
	}

	// returns whether or not to redirect
	function delta() {
		if ( !$action = isset( $_GET['action'] ) ? $_GET['action'] : @$_POST['action'] )
			return false;

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] )
			$array =& $_POST;
		else
			$array =& $_GET;

		$query_args = array();
		switch ( $action ) :
		case 'add-committer' :
			if ( !$topic = get_topic( isset($array['topic_id']) ? $array['topic_id'] : get_topic_id() ) )
				return false;
			if  ( !$this->current_user_can_admin( $topic->topic_id ) )
				return false;

			if ( !$committer = bb_get_user( $array['committer'] ) ) {
				$query_args['error'] = 'no-user';
				break;
			}

			bb_check_admin_referer( 'add-committer_' . $topic->topic_id );

			if ( !$this->add_svn_access( $committer->user_login, $topic->topic_slug, 'rw' ) ) {
				$query_args['error'] = 'broken';
				break;
			}

			$query_args['message'] = 'add-committer';
			break;
		case 'delete-committer' :
			if ( !$topic = get_topic( isset($array['topic_id']) ? $array['topic_id'] : get_topic_id() ) )
				return false;
			if  ( !$this->current_user_can_admin( $topic->topic_id ) )
				return false;

			if ( !$committer = bb_get_user( $array['committer'] ) ) {
				$query_args['error'] = 'no-user';
				break;
			}

			if ( bb_get_current_user_info( 'name' ) == $committer->user_login ) {
				$query_args['error'] = 'self';
				break;
			}

			bb_check_admin_referer( 'delete-committer_' . $committer->user_login . "_$topic->topic_id");

			if ( !$this->del_svn_access( $committer->user_login, $topic->topic_slug ) ) {
				$query_args['error'] = 'broken';
				break;
			}

			$query_args['message'] = 'delete-committer';
			break;
		case 'primary-author' :
			if ( !$topic = get_topic( isset($array['topic_id']) ? $array['topic_id'] : get_topic_id() ) )
				return false;
			if ( !bb_current_user_can( 'administrate' ) )
				return false;

			if ( !$author = bb_get_user( $array['primary-author'] ) ) {
				$query_args['error'] = 'no-user';
				break;
			}
			
			bb_check_admin_referer( 'primary-author_' . $topic->topic_id);

			bb_insert_topic( array( 'topic_id' => $topic->topic_id, 'topic_poster' => $author->ID ) );

			$post = bb_get_first_post( $topic->topic_id );
			bb_insert_post( array( 'post_id' => $post->post_id, 'poster_id' => $author->ID ) );

			$query_args['message'] = 'primary-author';
			break;
		case 'update' :
			if ( !$topic = get_topic( isset($array['topic_id']) ? $array['topic_id'] : get_topic_id() ) )
				return false;
//			if ( !$this->current_user_can_admin( $topic->topic_id ) )
//				return false;
			if ( !bb_current_user_can( 'administrate' ) )
				return false;

			bb_check_admin_referer( 'update_' . $topic->topic_id );

			// Just do the root triggers
			$this->tracker->pull_triggers( "$topic->topic_slug/", array( 'roots' => 'grouped' ) );

			$query_args['message'] = 'updated';
			break;
		case 'moderate-requests' :
			if ( !bb_current_user_can( 'administrate' ) )
				return false;

			bb_check_admin_referer( 'moderate-requests' );

			foreach ( $array['mod'] as $slug => $mod ) {
				if ( 1 == $mod ) {
					$this->approve_request( $slug );
				} elseif ( 2 == $mod ) {
					$reject_reason = isset($array['reject'][$slug]) ? stripslashes($array['reject'][$slug]) : '';
					$this->reject_request( $slug, $reject_reason );
				} else {
					continue;
				}
			}
			return add_query_arg( 'message', 'moderate-requests', wp_get_referer() );
			break;
		case 'submit-request' :
			$name = stripslashes( $array['topic'] );
			$desc = stripslashes( $array['post_content'] );
			$url  = stripslashes( @$array['request_url'] );
			return $this->submit_request( $name, $desc, $url );
			break;
		endswitch;

		if ( $query_args )
			return add_query_arg( $query_args, $this->tracker->section_url( 'admin', $topic->topic_id ) );
		return false;
	}
		
	function current_user_can_admin( $topic_id = 0 ) {
		if ( !$user = bb_get_current_user_info( 'user_login' ) )
			return false;

		if ( bb_current_user_can( 'administrate' ) )
			return true;

		$topic = get_topic( get_topic_id( $topic_id ) );

		return $this->user_can_write_path( $user, $topic->topic_slug );
	}

	function get_committers( $topic_id = 0 ) {
		$topic = get_topic( get_topic_id( $topic_id ) );

		$this->load_svn_access( $topic->topic_slug );
		$committers = array();

		foreach ( $this->svn_access["/$topic->topic_slug"] as $user => $access )
			if ( false !== strpos( $access, 'w' ) )
				$committers[] = $user;

		return $committers;
	}

	function pre_post_form() {

		global $forum;
		if ( !is_forum() || $forum->forum_id != $this->requests_forum )
			return;

		if ( isset($_GET['required']) ) :
?>

		<p class="error">You must fill out all of the required fields.</p>

<?php
		endif;

		if ( isset($_GET['already']) ) :
?>

		<p class="error">That name already exists. Please choose another name.</p>

<?php
		endif;

		ob_start( array(&$this, 'post_form_callback') );
	}

	function post_post_form() {
		global $forum;
		if ( !is_forum() || $forum->forum_id != $this->requests_forum )
			return;
		ob_end_flush();
	}

	function post_form_callback( $content ) {
		$url = clean_url( remove_query_arg( array( 'required', 'already', 'name' ) ) );
		$content = preg_replace( '#action=([\'"]).*?\1#', "action='$url'", $content );
		$content = preg_replace( '#<input type=[\'"]hidden[\'"] name=[\'"]_wpnonce[\'"] value=[\'"].*?[\'"] />#', '', $content );
		$content = preg_replace(
			'#<input type=[\'"]hidden[\'"] name=[\'"]_svn_request_nonce[\'"] value=[\'"](.*?)[\'"] />#',
			'<input type="hidden" name="_wpnonce" value="$1" />',
			$content
		);
		return $content;
	}

	function submit_request( $name, $description, $url ) {
		if ( !bb_current_user_can( 'write_topic', $this->requests_forum ) )
			return false;

		bb_check_admin_referer( 'submit-request' );

		$user = bb_get_user( bb_get_current_user_info( 'id' ) );

		$name = bb_slug_sanitize( $name );
		$name = str_replace( '_', '-', $name );
		$name = trim( $name, '-' );

		$url = bb_fix_link( $url );

		$description = trim( $description );
		$description = strip_tags( $description );
		$description = wp_specialchars( $description );

		$redirect_args = array();

		if ( !$name || !$description ) {
			$redirect_args['required'] = '';
			$redirect_args['name'] = $name;
			return add_query_arg( $redirect_args );
		}

		$exists = true;
		do {
			if ( in_array( $name, array( 'add', 'requests', 'all' ) ) )
				break;

			if ( $exists = (bool) get_topic( $name ) )
				break;

			if ( $exists = (bool) $this->load_svn_access( $name ) )
				break;

			if ( $exists = (bool) $this->tracker->svn->ls( $this->tracker->svn_url . $name ) )
				break;

			if ( !$topic_id = bb_insert_topic( array( 'topic_title' => $name, 'forum_id' => $this->requests_forum ) ) )
				break;

			$exists = false;

		} while(0);

		if ( $exists ) {
			$redirect_args['already'] = '';
			$redirect_args['name'] = $name;
		} else {
			$post_id = bb_insert_post( array( 'topic_id' => $topic_id, 'post_text' => $description ) );
			bb_update_topicmeta( $topic_id, 'uri', $url );

			if ( $this->notify_email ) {
				$subject  = '[' . bb_get_option( 'name' ) . "] Request: $name";
				$message  = "User: " . bb_get_current_user_info( 'name' ) . "\nRequest: $name\n\n";
				$message .= get_forum_link( $this->requests_forum );
				$headers  = "Reply-To: $user->user_login <$user->user_email>";

				bb_mail( $this->notify_email, $subject, $message, $headers );
			}
		}
		return add_query_arg( $redirect_args );
	}

	function approve_request( $topic_id ) {
		if ( !$topic = get_topic( $topic_id ) )
			return false;
		bb_move_topic( $topic->topic_id, $this->approved_forum );
		return bb_update_topicmeta( $topic->topic_id, 'approved', bb_current_time() );
	}

	function reject_request( $topic_id, $reject_reason = '' ) {
		if ( !$topic = get_topic( $topic_id ) )
			return false;
		$old_forum = $topic->forum_id;
		$user = bb_get_user( $topic->topic_poster );
		bb_move_topic( $topic->topic_id, $this->rejected_forum );
		bb_insert_post( array( 'topic_id' => $topic->topic_id, 'post_text' => 'Rejected: ' . $reject_reason ) );
		bb_insert_topic(array( 'topic_id' => $topic->topic_id, 'topic_slug' => 'rejected-' . $topic->topic_slug . '-rejected' ) );
		if ( $old_forum != $this->tracker->live_forum && $reject_reason ) {
			$subject = '[' . bb_get_option( 'name' ) . "] Request Denied: $topic->topic_slug";
			bb_mail( $user->user_email, $subject, $reject_reason );
		}
	}

	function add_approved_plugins() {
//		if ( !$this->tracker->is_master )
//			return;

		$topics_query = new BB_Query( 'topics', array( 'forum_id' => $this->approved_forum, 'per_page' => 100 ), 'svn_tracker_approved' );
		$mkdir = array( "{$this->tracker->svn_url}%%SLUG%%" );
		foreach ( array( 'trunk', 'branches', 'tags' ) as $dir )
			$mkdir[] = "{$this->tracker->svn_url}%%SLUG%%/$dir";

		$subject = '[' . bb_get_option( 'name' ) . '] Request Approved: %%SLUG%%';

		foreach ( (array) $topics_query->results as $topic ) {
			$user = bb_get_user( $topic->topic_poster );
			$user_name = get_user_name( $user->ID );

			$user_mkdir = str_replace( '%%SLUG%%', $topic->topic_slug, $mkdir );

			if ( !$this->tracker->svn->mkdir( $user_mkdir, array( 'message' => "adding $topic->topic_slug by $user_name" ) ) )
				continue;

			list( $user_message, $user_subject ) = str_replace(
				array( '%%USER%%', '%%SVN_URL%%', '%%TRAC_URL%%', '%%SLUG%%' ),
				array( $user_name, $this->tracker->svn_url, $this->tracker->trac_url, $topic->topic_slug ),
				array( $this->approved_mess, $subject )
			);

			bb_close_topic( $topic->topic_id );
			bb_move_topic( $topic->topic_id, $this->tracker->live_forum );

			$this->add_svn_access( $user_name, $topic->topic_slug, 'rw' );

			bb_mail( $user->user_email, $user_subject, $user_message );

			// TODO tracadmin component
		}
	}

}

?>
