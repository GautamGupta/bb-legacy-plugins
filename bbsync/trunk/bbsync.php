<?php
/*
Plugin Name: bbSync
Plugin URI: 
Description: Complete integration with your bbPress install. <strong>It <em>needs</em> your <a href="options-general.php?page=bbsync.php">config</a>!</strong> :)
Version: 0.92
Author: fel64
Author URI: http://www.loinhead.net/
*/

add_action('publish_post', 'felsyncpost', 999 );
add_action('comment_post', 'felsynccomment', 999, 2 );

add_action('admin_menu', 'bbsyncaddsubmenu');
add_action('edit_form_advanced', 'bbsyncpostoptions');


	remove_action('publish_post', 'bbpress_setpost');
	remove_filter('the_content', 'bbpress_add_forumlink');
	
/* CORE */
function load_bb() {
	global $bb, $bbdb, $bb_current_user, $page, $bb_cache,
		$posts, $bb_post, $post_id, $topics, $topic, $topic_id,
		$forums, $forum, $forum_id, $tags, $tag, $tag_name, $user, $user_id, $view;  // MAINTAIN - source is template-functions.php
	global $bb_roles, $bb_table_prefix, $bb_locale, $bb_timestart, $timeend;  // INCOMPLETE
	global $opshuns;
	$opshuns = get_option('bbsync');
/**/	remove_action('bb_user_has_no_caps', 'bb_give_user_default_role');  // MAINTAIN
	require_once( $opshuns['bbpath'] ); //bb-load
/**/	add_action('bb_user_has_no_caps', 'bb_give_user_default_role');  // MAINTAIN
}

function felsyncpost( $post_id ) {
	global $bbdb, $wpdb, $current_user;
	$opshuns = get_option('bbsync');
	$post = wp_get_single_post( $post_id );
	
	if( !empty( $post->post_password ) )
		return;
		
	load_bb();
	
	if( !$current_user )
		return;
	bb_set_current_user( $current_user->ID );
	
	$post_title = $bbdb->escape( $post->post_title );
	$post_text = felprepposttext( $post_id );
	
	if( felwptobb( $post_id ) ) {
		//update topic/post
		$topic_id = felwptobb( $post_id );
		if( bb_update_topic( $post_title, $topic_id ) )
			$link_exists = true;
		$reply = bb_get_first_post( $topic_id );
		bb_update_post( mysql_real_escape_string( $post_text ), $reply->post_id, $topic_id );
	} 
	if( !$link_exists ) {
		felnewtopic( $post_id, $post_title, $post_text );
	}
}

function felnewtopic( $post_id, $post_title, $post_text ) {
	$opshuns = get_option('bbSync');
	if( !( isset( $_GET['forum'] ) && $forum_id = felinterpretforum( $_GET['forum'] ) ) )
		$forum_id = $opshuns['forum'];
	
	$topic_id = bb_new_topic( $post_title, $forum_id, $tags );
	$reply_id = bb_new_post( $topic_id, $post_text );
	echo "post_id: $post_id, post_title: $post_title, forum_id: $forum_id, post_text: $post_text.<br />";
	echo "topic_id: $topic_id, reply_id: $reply_id.<br />";
	if( $topic_id && $reply_id ) {
		felmetalink( $post_id, $topic_id );
		$r = true;
		echo 'maaaan';
	}
	
	$oldcoms = get_approved_comments( $post_id );
	if( $oldcoms ) {
		foreach( $oldcoms AS $oldcom ) {
			if( $user = bb_get_user( $oldcom->comment_author ) ) {
				$time = strtotime( $oldcom->comment_date );
				$text = '<em><strong>This comment was originally posted at ' . date( 'G:i', $time ) . ' on ' . date( 'jS F Y', $time ) . ".</strong></em>\n\n" . $oldcom->comment_content;
				bb_set_current_user( $user->ID );
				bb_new_post( $topic_id, mysql_real_escape_string( $text ) );
			}
		}
	}
	return $r;
}

function felmetalink( $post_id, $topic_id )
{
	$opshuns = get_option('bbsync');
	load_bb();
	bb_update_topicmeta( $topic_id, 'wp_post', $post_id );
	if( !update_post_meta( $post_id, 'bb_topic', $topic_id ) )
		add_post_meta( $post_id, 'bb_topic', $topic_id, true );
	return true;
}

function felwptobb( $post_id ) {
	$bb_topic = get_post_meta( $post_id, 'bb_topic', $true );
	return $bb_topic[0];
}

function felgetbbreplies() {
	global $wpdb, $post;
	$opshuns = get_option('bbsync');
	$topic_id = felwptobb( $post->ID );
	if( $topic_id ) {
		$bb_ = $opshuns['bb_'];
		$bb_posts = $bb_ . 'posts';
		$bbreplies = $wpdb->get_results("
			SELECT *
			FROM $bb_posts
			WHERE topic_id = $topic_id AND post_status = 0
		");
		array_shift( $bbreplies ); //pops first one off
		return $bbreplies;
	} else {
		return false;
	}
}

/* COMMENTS -> REPLIES */
function felsynccomment( $comment_id, $ham ) {
	global $wpdb;
	$opshuns = get_option('bbsync');
	load_bb();
	$comment = get_comment( $comment_id );
	$topic_id = felwptobb( $comment->comment_post_ID );
	$ham = $comment->comment_approved;
	if( ( $topic_id ) && ( $ham == 1 ) && ( $comment->user_id ) && bb_set_current_user( $comment->user_id ) ) {
		//topic linked, genuine comment, actual user, bb likes user
		bb_new_post( $topic_id, $comment->comment_content );
		return true;
	} else {
		return false;
	}
}

/* MIGRATION */
function felbbPostmigration() {
	global $wpdb;
	$bbPoststable = $wpdb->prefix . 'bbpress_post_posts';
	$bbPostsoptionstable = $wpdb->prefix . 'bbpress_post_options';
	
	$bbPosts = $wpdb->get_results("
		SELECT * FROM $bbPoststable
	");
	
	if( $bbPosts ) {
		foreach( $bbPosts as $bbPost ) {
			if( !felwptobb( $bbPost->post_id ) ) {
				if( !felmetalink( $bbPost->post_id, $bbPost->topic_id ) )
					$fail = true;
			}
		}
		if( $fail ) {
			$errorsfelled[] = $felerrors['notopicmetalink'];
		} else {
			$wpdb->query("
				DROP TABLE $bbPoststable, $bbPostsoptionstable
			");
			$felmessage = $felmessages['migrated'];
		}
	} else {
		$errorsfelled[] = $felerrors['nobbPost'];
	}
}

function felprepposttext( $post_id ) {
	$post = wp_get_single_post( $post_id );
	$opshuns = get_option('bbsync');
	$set_truncate = get_post_meta( $post_id, 'bbsync_truncate', true );

	if( $opshuns['nomore'] ) {
		$ptvers = get_extended( $post->post_content );
		$post_text = $ptvers['main'];
	} elseif( $opshuns['pageonly'] ) {
		list( $post_text, $throwaway ) = explode('<!--nextpage-->', $post->post_content, 2 );
	} elseif( ( isset( $_POST['truncate'] ) && $_POST['truncate'] === 0 ) || ( !( isset( $_POST['truncate'] ) && $_POST['truncate'] === 0 ) && $opshuns['truncate'] ) ) {
	//if POSTed truncate is 0 XOR default truncate is 0
		$post_text = $post->post_content;
	} elseif( $_POST['truncate'] ) {
	//if POSTed truncate is not 0
		$post_text = substr( $post->post_content, 0, $_POST['truncate'] ) . '...';
		if( !add_post_meta( $post_id, 'bbsync_truncate', $_POST['truncate'], true ) )
			update_post_meta( $post_id, 'bbsync_truncate', $_POST['truncate'] );
	} elseif( $set_truncate ) {
	//if no POSTed truncate and special data for this 'un
		$post_text = substr( $post->post_content, 0, $set_truncate ) . '...';
	} elseif( $opshuns['truncate'] ) {
	//otherwise, go to default if exists
		$post_text = substr( $post->post_content, 0, $opshuns['truncate'] ) . '...';
	} else {
	//or just don't do anything
		$post_text = $post->post_content;
	}
	
	if( !empty( $opshuns['link'] ) && $post_text != $post->post_content ) {
		$arrrsearches = array('%title%', '%name%');
		$arrrreplaces = array($post->post_title, get_bloginfo('name') );
		$linktext = str_replace($arrrsearches, $arrrreplaces, $opshuns['link'] );
		$linkurl = get_permalink( $post->ID );
		$linktext = "\n\n<a href='$linkurl'>$linktext</a>";
		$post_text .= $linktext;
	}
	
	remove_filter('pre_post', 'encode_bad');
	remove_filter('pre_post', 'bb_encode_bad');
	remove_filter('pre_post', 'bb_filter_kses', 50 );
	remove_filter('pre_post', 'addslashes', 55 );
	remove_filter('pre_post', 'bb_autop', 60 );
	remove_filter('pre_post', 'allow_images_encode_bad', 9 );
	remove_filter('pre_post', 'allow_images', 52 );
	$post_text = bb_autop( $post_text );
	
	return mysql_real_escape_string( $post_text );
}


/* INTERFACE */
function bbsyncaddsubmenu() {
	if (function_exists("add_submenu_page"))
		add_submenu_page('options-general.php', 'bbSync', 'bbSync', 10, 'bbsync.php', 'felbbsyncinterface');
}

function felbbsyncinterface() {
	if (function_exists("add_submenu_page")) {
		$opshuns = get_option('bbsync');
		
		$bbsyncurl = get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=bbsync.php';
		$felerrors = array(
			'nobbpath' => 'I don\'t have a working path to your bb-load.php file for bbPress, and I need it to make this work!',
			'nobbPost' => 'I couldn\'t find the bbPress Post table, so I couldn\'t migrate it either. Sorry!',
		);
		$felmessages = array(
			'options' => 'Thanks! Updated your options no problem. Should all work! :-)',
			'migrated' => 'Coolio, your old data from bbPress Post has been migrated and the tables it used deleted. \o/',
			'broughttospeed' => 'Yup, brought your forum to speed with old blog posts and comments. Check your forum and (hopefully) cheer!'
		);
		
		if( $_POST['migrate'] == 'bbPress Post') {
			felbbPostmigration();
		}
		
		if( $_POST['settings'] == true ) {
			//options handling
			$allowed_opshuns = array(
				'bbpath',			
				'bburl',
				'bb_',
				'forum',
				'nomore',
				'pageonly',
				'truncate',
				'bbAPI',
				'ppo',
				'link'
			);
			foreach( $_POST AS $postkey => $postvalue ) {
				if( in_array( $postkey, $allowed_opshuns ) )
					$opshuns[ $postkey ] = $postvalue;
			}
			if( !is_int( $opshuns['truncate'] ) && $opshuns['truncate'] < 0 ) {
				$opshuns['truncate'] = 0;
			}
			if( $_POST['forum'] && $forum_temp = felinterpretforum( $_POST['forum'] ) ) {
				$opshuns['forum'] = $forum_temp;
			}
			$truefalses = array(
				'nomore',
				'pageonly',
				'bbAPI',
				'ppo'
			);
			foreach( $truefalses AS $kee ) {
				if( !$_POST[ $kee ] ) {
					$opshuns[ $kee ] = false;
				}
			}
			update_option( 'bbsync', $opshuns );
		}
			
		if( !$opshuns['bbpath'] || !strpos( $opshuns['bbpath'], 'bb-load.php') || !is_readable( $opshuns['bbpath'] ) ) {
			$errorsfelled[] = $felerrors['nobbpath'];
		}
		if( $_POST['settings'] == true && !$errorsfelled ) {
				$felmessage = $felmessages['options'];
		}
		
		if( $_POST['migrate'] == true ) {
			felbbPostmigration();
		}
		
		if( $errorsfelled ) {
		foreach( $errorsfelled AS $felerror ) {
				?>
			
<div id="message" class="error fade">
	<p><?php echo $felerror; ?></p>
</div>

				<?php
			}
		}
		if( $felmessage ) {
			?>
		
<div id="message" class="updated fade">
	<p><?php echo $felmessage; ?></p>
</div>

			<?php
		}
		
		?>
	
<div class="wrap">
	<h2>bbSync ~ bbPress Synchronisation Options</h2>
	<div>
		<h3>Settings</h3>
		<form method="POST" action="<?php echo $bbsyncurl; ?>">
			<p><input type="text" name="bbpath" id="bbpath" size="40" value="<?php echo $opshuns['bbpath']; ?>" /> <label for="bbpath">The absolute <em>path</em> to your bbPress bb-load.php file. It could be <code>/var/www/htdocs/forums/bb-load.php</code> or similar.</label></p>
			<p><input type="text" name="bburl" id="bburl" size="40" value="<?php echo $opshuns['bburl']; ?>" /> <label for="bburl">What's the web address for your forum? Something like <code>http://example.com/forums</code> with no trailing slash please!</label></p>
			<ul>
				<li><input type="text" name="bb_" id="bb_" size="2" value="<?php echo( $opshuns['bb_'] ? $opshuns['bb_'] : 'bb_'); ?>" /> <label for="bb_">What's the prefix for your bbPress tables? It's probably <code>bb_</code>.</label></li>
				<li><input type="text" name="forum" id="forum" size="6" value="<?php echo $opshuns['forum']; ?>" /> <label for="forum">Which forum should the topics go in by default?</label></li>
				<li><input type="checkbox" name="nomore" id="nomore" <?php if( $opshuns['nomore'] ) echo 'checked="true"'; ?> /> <label for="nomore">Cut posts off at the <code>&lt;!--more--&gt;</code> tag? Totally overrides the next two options.</label></li>
				<li><input type="checkbox" name="pageonly" id="pageonly" <?php if( $opshuns['pageonly'] ) echo 'checked="true"'; ?> /> <label for="pageonly">Cut posts off after the <code>&lt;!--nextpage--&gt;</code> tag? Overrides the next option.</label></li>
				<li><input type="text" name="truncate" id="truncate" size="2" value="<?php echo $opshuns['truncate']; ?>" /> <label for="truncate">How much of your blog post do you want shown on the forums? Set it to 0 to get the full post.</label></li>
				<li><input type="checkbox" name="bbAPI" id="bbAPI" <?php if( $opshuns['bbAPI'] ) echo 'checked="true'; ?> /> <label for="bbAPI">Do you want me to load bbPress whenever to make things like links nicer? This could add a lot of work to your server and make it slower.</label></li>
				<li><input type="checkbox" name="ppo" id="ppo" <?php if( $opshuns['ppo'] ) echo 'checked="true"'; ?> /> <label for="ppo">Would you like options on a per-post basis? This will let you chose which forum to send the topic to and how much of the post to show.</label></li>
				<li><input type="text" name="link" id="link" value="<?php echo $opshuns['link']; ?>" /> <label for="link">Want a link back to the blog post? <code>%title%</code> and <code>%name%</code> will be replaced with the post name and blog title respectively. (Leave blank if you don't want a link.)</label></li>
			</ul>
			<input type="hidden" name="settings" value="true" />
			<input type="Submit" value="Submit Info" />
		</form>
	</div>
	<div>
		<h3>Migrate from bbPress Post</h3>
		<p>This will get all the old blog post -> topic links, transfer them into the new system, and finally kill the unnecessary tables. It's a permanent change. Backup your databases first!</p>
		<form method="POST" action="<?php echo $bbsyncurl; ?>">
			<input type="hidden" name="migrate" value="bbPress Post" />
			<input type="submit" value="Migrate" />
		</form>
	</div>
	<div>
		<h3>bbSync</h3>
		<p>This plugin should completely synchronise everything of interest between Wordpress and bbPress.</p>
		<ul>
			<li>It creates a new topic when you post in your blog, and links them behind the scenes.</li>
			<li>Replies in the thread can be shown where the comments are (quite automagically). Use <code>&lt;?php bbrepliestext(); ?&gt;</code> to just link to the thread; it accepts the link text as an argument and <code>%no%</code> and <code>%replies%</code> will be replaced with the numebr of posts and the right pluralicised form of the word reply.</li>
			<li>Comments made by the normal wp post form to integrated posts will be picked up and sent into the forum - so people can reply as they did before!</li>
			<li>Deactivates the bbPress Post plugin. If you don't like that, take out lines 18 and 19 (<code>remove_action(...</code> and <code>remove_filter(...</code>) of bbsync.php. </li>
			<li>Can migrate data from the bbPress Post plugin and then delete it. bbPress Post must not be active after migration!</li>
		</ul>
	</div>
</div>	
		<?php
	}
}

function bbsyncpostoptions() {
	global $post_id;
	$opshuns = get_option('bbsync');
	if( $opshuns['ppo'] ) {
		?>
<p>
	<strong>bbSync ~ </strong>
	<?php if( !isset( $_GET['post'] ) || ( isset( $_GET['post'] ) && !felwptobb( $_GET['post'] ) ) ) { ?><label for="forum">Forum: </label><input type="text" name="forum" id="forum" size="6" value="<?php $opshuns['forum'] ?>" /><?php } ?>
	<label for="truncate">Excerpt length: </label><input type="text" name="truncate" id="truncate" size="2" value="<?php $opshuns['truncate'] ?>" />
</p>
		<?php
	}
}

function felinterpretforum( $forumidname ) {
	global $wpdb;
	$opshuns = get_option('bbsync');
	$bb_forums = $opshuns['bb_'] . 'forums';
	$forum = $wpdb->get_row("
		SELECT forum_id, forum_name
		FROM $bb_forums
		WHERE ( forum_id = '$forumidname' OR forum_name = '$forumidname' )
	");
	if( $forum ) {
		return $forum->forum_id;
	} else {
		return 0;
	}
}

function bbreplylink( $topic_id = 0 ) {
	global $id;
	$opshuns = get_option('bbsync');
	if( !$topic_id )
		$topic_id = felwptobb( $id );
	if( $topic_id ) {
		return felforumtopiclink( $topic_id );
	}
	return false;
}

function felforumtopiclink( $topic_id ) {
	$opshuns = get_option('bbsync');
	if( $opshuns['bbAPI'] ) {
		load_bb();
		$topic = get_topic( $topic_id );
		$last_page = get_page_number( $topic->topic_posts + topic_pages_add( $topic->topic_id ) );
		$replylink =  attribute_escape( get_topic_link( $topic->topic_id, $last_page ) . '#postform' );
	} else {
		$replylink = $opshuns['bburl'] . '/topic.php?id=' . $topic_id . '#postform';
	}
	return $replylink;
}

function bbrepliestext( $texties = 'Talk about this! %no% %replies%' ) {
	global $id;
	$opshuns = get_option('bbsync');
	load_bb();
	$topic_id = felwptobb( $id );
	if( $topic_id ) {
		$topic = get_topic( $topic_id );
		$posties = $topic->topic_posts;
		$repliesword = 'replies';
		if( $posties == 1 )
			$repliesword = 'reply';
		$arrrsearches = array('%no%', '%replies%');
		$arrrreplaces = array($topic->topic_posts, $repliesword );
		$linktext = str_replace($arrrsearches, $arrrreplaces, $texties );
		$linkurl = felforumtopiclink( $topic_id );
		$linktext = "\n\n<a href='$linkurl'>$linktext</a>";
	}
}

add_action('comments_array', 'replace_comments', 1, 2);
function replace_comments( $comments, $post_ID ) {
	if( felwptobb( $post_ID ) && isset( $comments ) ) {
		foreach( $comments as $comment )
			if( $comment->comment_type !== '')
				$trackbacks[] = $comment;
		
		unset( $comments );
		foreach( felgetbbreplies() as $reply ) {
			$com->comment_ID = $reply->post_id;
			$com->comment_post_ID = $post_ID;
			$com->user_id = $reply->poster_id;
			$com->comment_content = $reply->post_text;
			$com->comment_date = $reply->post_time;
			$com->comment_author_IP = $reply->poster_ip;
			$user = get_userdata( $com->user_id );
			$com->comment_author =  $user->display_name;
			$com->comment_author_email =  $user->user_email;
			$com->comment_author_url =  $data->user_url;
			$comments[] = $com;
		}
		$comments = array_merge( (array) $comments, (array) $trackbacks );
	}
	return $comments;
}
?>