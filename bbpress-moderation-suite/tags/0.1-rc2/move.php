<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

/* $Id$ */

function bbmodsuite_move_install() {
	// Nothing here yet...
}

function bbmodsuite_move_uninstall() {
	// Nothing here yet...
}

function bbpress_moderation_suite_move() { ?>
<h2><?php _e( 'Move!', 'bbpress-moderation-suite' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>
<?php switch ( $_GET['action'] ) {
	case 'submit':
		// Already processed at this point, so nothing needs to be done. ?>
<p><?php _e( 'Action completed successfully.', 'bbpress-moderation-suite' ); ?></p>
<?php	break;
	case 'merge':
		$topic = get_topic( $_GET['topic'] );
		if ( !$topic ) {
			echo '<p>' . __( 'Topic not found.', 'bbpress-moderation-suite' ) . '</p>';
			return;
		}
		$GLOBALS['bb_posts'] = get_thread( $topic->topic_id, 1 );
		bb_admin_list_posts(); ?>
<form class="settings" method="post" action="<?php echo esc_attr( add_query_arg( 'action', 'submit', remove_query_arg( 'post' ) ) ); ?>">
<fieldset>
	<div id="option-topicid">
		<label for="topicid">
			<?php _e( 'Which topic?', 'bbpress-moderation-suite' ); ?>
		</label>
		<div class="inputs">
			<input type="text" class="text long" id="topicid" name="topicid" />
			<p><?php _e( 'Type the ID or slug of the topic you want to merge this one with.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<input type="submit" class="submit" name="submit" value="<?php esc_attr_e( 'Submit', 'bbpress-moderation-suite' ); ?>" />
	<input type="hidden" name="topic" id="topic" value="<?php echo $topic->topic_id; ?>" />
	<input type="hidden" name="_action" id="_action" value="merge" />
	<?php bb_nonce_field( 'bbmodsuite_move-mergetopic_' . $topic->topic_id ); ?>
</fieldset>
</form>

<script type="text/javascript">
//<![CDATA[
jQuery(function($){
	var autocompleteTimeout, ul = $('<ul/>').css({
		position: 'absolute',
		zIndex: 10000,
		backgroundColor: '#fff',
		fontSize: '1.2em',
		padding: 2,
		marginTop: -1,
		MozBorderRadius: 2,
		WebkitBorderRadius: 2,
		borderRadius: 2,
		border: '1px solid #ccc',
		borderTopWidth: '0'
	}).hide().insertAfter('#topicid');
	$('#topicid').attr({
		autocomplete: 'off'
	}).keyup(function(){
		// IE compat
		if (document.selection) {
			// The current selection
			var range = document.selection.createRange();
			// We'll use this as a 'dummy'
			var stored_range = range.duplicate();
			// Select all text
			stored_range.moveToElementText(this);
			// Now move 'dummy' end point to end point of original range
			stored_range.setEndPoint('EndToEnd', range);
			// Now we can calculate start and end points
			this.selectionStart = stored_range.text.length - range.text.length;
			this.selectionEnd = this.selectionStart + range.text.length;
		}

		try {
			clearTimeout(autocompleteTimeout);
		} catch (ex) {}

		if (!this.value.length)
			return;

		autocompleteTimeout = setTimeout(function(text, pos){
			$.post('<?php echo addslashes( str_replace( '&amp;', '&', bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_move', 'action' => 'ajax-topic' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ) ) ); ?>', {
				text: text,
				pos: pos,
				topic: <?php echo $topic->topic_id; ?>,
				_wpnonce: '<?php echo bb_create_nonce( 'bbmodsuite_move-gettopic_ajax' ); ?>'
			}, function(data){
				ul.empty().show();
				$.each(data, function(i, d){
					var name = d[1],
						val  = d[0];
					if (name.length)
						$('<li/>').css({
							listStyle: 'none'
						}).text(name).attr({
							title: val
						}).click(function(){
							$('#topicid').val(val);
							ul.empty();
						}).appendTo(ul);
				});
			}, 'json');
		}, 750, this.value, this.selectionStart);
	}).blur(function(){
		setTimeout(function(){
			ul.hide();
		}, 100);
	});
});
//]]>
</script>
<?php	 break;
	case 'split':
		$topic = get_topic( $_GET['topic'] );
		if ( !$topic ) {
			echo '<p>' . __( 'Topic not found.', 'bbpress-moderation-suite' ) . '</p>';
			return;
		}
		global $bb_posts, $bb_post;
		$bb_posts = get_thread( $topic->topic_id, array( 'per_page' => -1 ) ); ?>
<form class="settings" method="post" action="<?php echo esc_attr( add_query_arg( 'action', 'submit', remove_query_arg( 'post' ) ) ); ?>">
<fieldset>
<table id="posts-list" class="widefat" cellspacing="0" cellpadding="0">
<thead>
	<tr>
		<th scope="col"><?php _e( 'Post' ); ?></th>
		<th scope="col"><?php _e( 'Author' ); ?></th>
		<th scope="col"><?php _e( 'Date' ); ?></th>
		<th scope="col"><?php _e( 'Split?', 'bbpress-moderation-suite' ); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th scope="col"><?php _e( 'Post' ); ?></th>
		<th scope="col"><?php _e( 'Author' ); ?></th>
		<th scope="col"><?php _e( 'Date' ); ?></th>
		<th scope="col"><?php _e( 'Split?', 'bbpress-moderation-suite' ); ?></th>
	</tr>
</tfoot>
<tbody>
<?php
		foreach ( $bb_posts as $bb_post ) {
?>
	<tr id="post-<?php post_id(); ?>"<?php alt_class('post', post_del_class()); ?>>
		<td class="post">
			<?php post_text(); ?>
			<div style="padding: 0">
				<span class="row-actions">
					<a href="<?php echo esc_url( get_post_link() ); ?>"><?php _e( 'View' ); ?></a>
<?php
	bb_post_admin( array(
		'before_each' => ' | ',
		'each' => array(
			'undelete' => array(
				'before' => ' '
			)
		),
		'last_each' => array(
			'before' => ' | '
		)
	) );
?>
				</span>&nbsp;
			</div>
		</td>

		<td class="author">
			<a href="<?php user_profile_link( get_post_author_id() ); ?>">
				<?php post_author_avatar( '16' ); ?>
				<?php post_author(); ?>
			</a>
		</td>

		<td class="date">
<?php
	if ( bb_get_post_time( 'U' ) < ( time() - 86400 ) ) {
		bb_post_time( 'Y/m/d\<\b\r \/\>H:i:s' );
	} else {
		printf( __( '%s ago' ), bb_get_post_time( 'since' ) );
	}
?>
		</td>

		<td class="split">
			<input type="checkbox" class="checkbox" name="post[<?php post_id(); ?>]" id="post[<?php post_id(); ?>]"/>
		</td>
	</tr>
<?php 
		}
?>
</tbody>
</table>
</fieldset>
<fieldset>
	<div id="option-topicid">
		<label for="topicid">
			<?php _e( 'Topic title?', 'bbpress-moderation-suite' ); ?>
		</label>
		<div class="inputs">
			<input type="text" class="text long" id="topicid" name="topicid" />
		</div>
	</div>
	<div id="option-forum">
		<label for="forum">
			<?php _e( 'Which forum?', 'bbpress-moderation-suite' ); ?>
		</label>
		<div class="inputs">
			<?php bb_forum_dropdown(); ?>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<input type="submit" class="submit" name="submit" value="<?php esc_attr_e( 'Submit', 'bbpress-moderation-suite' ); ?>" />
	<input type="hidden" name="topic" id="topic" value="<?php echo $topic->topic_id; ?>" />
	<input type="hidden" name="_action" id="_action" value="split" />
	<?php bb_nonce_field( 'bbmodsuite_move-splittopic_' . $topic->topic_id ); ?>
</fieldset>
</form>

<script type="text/javascript">
//<![CDATA[
jQuery(function($){
	var lastChecked;
	$(':checkbox').click(function(event) {
		if (lastChecked && event.shiftKey) {
			var start = $(':checkbox').index(this),
				end = $(':checkbox').index(lastChecked);

			for (var i = Math.min(start, end); i <= Math.max(start, end); i++) {
				$(':checkbox')[i].checked = lastChecked.checked;
			}
		}

		lastChecked = this;
	});
});
//]]>
</script>
<?php	break;
	case 'move':
		$post = bb_get_post( $_GET['post'] );
		if ( !$post || !bb_current_user_can( 'delete_post', $post->post_id ) ) { ?>
<p><?php _e( 'Post not found.', 'bbpress-moderation-suite' ); ?></p>
<?php	} else {
			$GLOBALS['bb_posts'] = array( $post );
			bb_admin_list_posts(); ?>
<form class="settings" method="post" action="<?php echo esc_attr( add_query_arg( 'action', 'submit', remove_query_arg( 'post' ) ) ); ?>">
<fieldset>
	<div id="option-newtopic">
		<label for="newtopic">
			<?php _e( 'Create new topic?', 'bbpress-moderation-suite' ); ?>
		</label>
		<div class="inputs">
			<input type="radio" class="radio" name="newtopic" value="yes" /> <?php _e( 'Create a new topic just for this post', 'bbpress-moderation-suite' ); ?><br/>
			<input type="radio" class="radio" name="newtopic" value="no" checked="checked" /> <?php _e( 'Use an existing topic', 'bbpress-moderation-suite' ); ?>
		</div>
	</div>
	<div id="option-topicid">
		<label for="topicid">
			<?php _e( 'Which topic?', 'bbpress-moderation-suite' ); ?>
		</label>
		<div class="inputs">
			<input type="text" class="text long" id="topicid" name="topicid" />
			<p><?php _e( 'For new topics, this is the title of the topic. For existing topics, type either the ID number or the slug.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
	<div id="option-forum">
		<label for="forum">
			<?php _e( 'Which forum?', 'bbpress-moderation-suite' ); ?>
		</label>
		<div class="inputs">
			<?php bb_forum_dropdown(); ?>
			<p><?php _e( 'For new topics, this is the forum where the topic will go. For existing topics, ignore this option.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<input type="submit" class="submit" name="submit" value="<?php esc_attr_e( 'Submit', 'bbpress-moderation-suite' ); ?>" />
	<input type="hidden" name="post" id="post" value="<?php echo $post->post_id; ?>" />
	<input type="hidden" name="_action" id="_action" value="move" />
	<?php bb_nonce_field( 'bbmodsuite_move-movepost_' . $post->post_id ); ?>
</fieldset>
</form>

<script type="text/javascript">
//<![CDATA[
jQuery(function($){
	var autocompleteTimeout, ul = $('<ul/>').css({
		position: 'absolute',
		zIndex: 10000,
		backgroundColor: '#fff',
		fontSize: '1.2em',
		padding: 2,
		marginTop: -1,
		MozBorderRadius: 2,
		WebkitBorderRadius: 2,
		borderRadius: 2,
		border: '1px solid #ccc',
		borderTopWidth: '0'
	}).hide().insertAfter('#topicid');
	$('#topicid').attr({
		autocomplete: 'off'
	}).keyup(function(){
		// IE compat
		if (document.selection) {
			// The current selection
			var range = document.selection.createRange();
			// We'll use this as a 'dummy'
			var stored_range = range.duplicate();
			// Select all text
			stored_range.moveToElementText(this);
			// Now move 'dummy' end point to end point of original range
			stored_range.setEndPoint('EndToEnd', range);
			// Now we can calculate start and end points
			this.selectionStart = stored_range.text.length - range.text.length;
			this.selectionEnd = this.selectionStart + range.text.length;
		}

		try {
			clearTimeout(autocompleteTimeout);
		} catch (ex) {}

		if ($('input:radio[name=newtopic]:checked').val() == 'yes') // New topic, can't autocomplete.
			return;

		if (!this.value.length)
			return;

		autocompleteTimeout = setTimeout(function(text, pos){
			$.post('<?php echo addslashes( str_replace( '&amp;', '&', bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_move', 'action' => 'ajax-topic' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ) ) ); ?>', {
				text: text,
				pos: pos,
				topic: <?php echo $post->topic_id; ?>,
				_wpnonce: '<?php echo bb_create_nonce( 'bbmodsuite_move-gettopic_ajax' ); ?>'
			}, function(data){
				ul.empty().show();
				$.each(data, function(i, d){
					var name = d[1],
						val  = d[0];
					if (name.length)
						$('<li/>').css({
							listStyle: 'none'
						}).text(name).attr({
							title: val
						}).click(function(){
							$('#topicid').val(val);
							ul.empty();
						}).appendTo(ul);
				});
			}, 'json');
		}, 750, this.value, this.selectionStart);
	}).blur(function(){
		setTimeout(function(){
			ul.hide();
		}, 100);
	});
});
//]]>
</script>
<?php	}
		break;
	default: ?>
<p><?php _e( 'You need to use Move! from a topic.', 'bbpress-moderation-suite' ); ?></p>
<?php
	}
}

function bbmodsuite_move_pre_header() {
	global $bbdb;
	if ( !isset( $_GET['action'] ) )
		return;
	switch ( $_GET['action'] ) {
		case 'submit':
			switch ( $_POST['_action'] ) {
				case 'move':
					bb_check_admin_referer( 'bbmodsuite_move-movepost_' . $_POST['post'] );
					if ( $_POST['newtopic'] == 'yes' ) {
						// Sanity check...
						if ( empty( $_POST['topicid'] ) || !trim( $_POST['topicid'] ) )
							bb_die( __( 'Invalid request', 'bbpress-moderation-suite' ) );
						if ( empty( $_POST['forum_id'] ) || !bb_get_forum( $_POST['forum_id'] ) )
							bb_die( __( 'Invalid request', 'bbpress-moderation-suite' ) );

						$post = bb_get_post( $_POST['post'] );

						if ( $post->forum_id != $_POST['forum_id'] ) {
							// We're moving the post out of this forum
							$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` - 1 WHERE `forum_id` = %d', $post->forum_id ) );
							// And into this forum
							$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` + 1 WHERE `forum_id` = %d', $_POST['forum_id'] ) );
						}
						// And out of its original topic
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->topics . '` SET `topic_posts` = `topic_posts` - 1 WHERE `topic_id` = %d', $post->topic_id ) );

						$topic = bb_insert_topic( array(
							'topic_title' => $_POST['topicid'],
							'forum_id'    => $_POST['forum_id'],
							'topic_posts' => 1
						) );
						$post = bb_insert_post( array(
							'post_id'  => $post->post_id,
							'topic_id' => $topic
						) );
						if ( function_exists( 'bbmodsuite_modlog_log' ) )
							bbmodsuite_modlog_log( sprintf( __( 'moved a post by %1$s to a new topic: %2$s', 'bbpress-moderation-suite' ), '<a href="' . get_user_profile_link( get_post_author_id( $post->post_id ) ) . '">' . get_post_author( $post->post_id ) . '</a>', '<a href="' . get_post_link( $post ) . '">' . esc_html( get_topic_title( $topic ) ) . '</a>' ), 'move_movepost' );
					} else {
						$post  = bb_get_post( $_POST['post'] );
						$topic = get_topic( $_POST['topicid'] );

						if ( !$post || !$topic )
							bb_die( __( 'Invalid request', 'bbpress-moderation-suite' ) );

						if ( $post->forum_id != $topic->forum_id ) {
							// We're moving the post out of this forum
							$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` - 1 WHERE `forum_id` = %d', $post->forum_id ) );
							// And into this forum
							$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` + 1 WHERE `forum_id` = %d', $topic->forum_id ) );
						}
						// We're moving the post out of its original topic
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->topics . '` SET `topic_posts` = `topic_posts` - 1 WHERE `topic_id` = %d', $post->topic_id ) );
						// And into the new one
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->topics . '` SET `topic_posts` = `topic_posts` + 1 WHERE `topic_id` = %d', $topic->topic_id ) );

						bb_insert_post( array(
							'post_id'  => $post->post_id,
							'topic_id' => $topic->topic_id
						) );

						if ( function_exists( 'bbmodsuite_modlog_log' ) )
							bbmodsuite_modlog_log( sprintf( __( 'moved a post by %1$s to topic: %2$s', 'bbpress-moderation-suite' ), '<a href="' . get_user_profile_link( get_post_author_id( $post->post_id ) ) . '">' . get_post_author( $post->post_id ) . '</a>', '<a href="' . get_post_link( $post->post_id ) . '">' . esc_html( get_topic_title( $topic->topic_id ) ) . '</a>' ), 'move_movepost' );
					}
					break;
				case 'merge':
					bb_check_admin_referer( 'bbmodsuite_move-mergetopic_' . $_POST['topic'] );
					$old_topic = get_topic( $_POST['topic'] );
					$new_topic = get_topic( $_POST['topicid'] );

					// Sanity check...
					if ( !$old_topic || !$new_topic || $old_topic->topic_id == $new_topic->topic_id )
						bb_die( __( 'Invalid request', 'bbpress-moderation-suite' ) );

					if ( $post->forum_id != $topic->forum_id ) {
						// We're moving the posts out of this forum
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` - %d WHERE `forum_id` = %d', $old_topic->topic_posts, $old_topic->forum_id ) );
						// And into this forum
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` + %d WHERE `forum_id` = %d', $old_topic->topic_posts, $new_topic->forum_id ) );
					}
					// Move the post count first
					$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->topics . '` SET `topic_posts` = %d WHERE `topic_id` = %d', $old_topic->topic_posts + $new_topic->topic_posts, $new_topic->topic_id ) );
					// Then move the posts
					$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->posts . '` SET `topic_id` = %d, `forum_id` = %d WHERE `topic_id` = %d', $new_topic->topic_id, $new_topic->forum_id, $old_topic->topic_id ) );
					// And finally, delete the old topic
					$bbdb->query( $bbdb->prepare( 'DELETE FROM `' . $bbdb->topics . '` WHERE `topic_id` = %d', $old_topic->topic_id ) );
					// Now that the topic no longer exists, we can remove it from the count
					$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `topics` = `topics` - 1 WHERE `forum_id` = %d', $old_topic->forum_id ) );
					if ( function_exists( 'bbmodsuite_modlog_log' ) )
						bbmodsuite_modlog_log( sprintf( __( 'merged %1$d post(s) from %2$s to topic: %3$s', 'bbpress-moderation-suite' ), $old_topic->topic_posts, esc_html( $old_topic->topic_title ), '<a href="' . get_topic_link( $new_topic->topic_id ) . '">' . esc_html( get_topic_title( $new_topic->topic_id ) ) . '</a>' ), 'move_mergetopic' );
					break;
				case 'split':
					bb_check_admin_referer( 'bbmodsuite_move-splittopic_' . $_POST['topic'] );
					$old_topic = get_topic( $_POST['topic'] );

					// Sanity check...
					if ( empty( $_POST['post'] ) )
						return;
					if ( !$old_topic )
						bb_die( __( 'Invalid request', 'bbpress-moderation-suite' ) );
					$posts = array_map( 'intval', array_keys( $_POST['post'] ) );
					if ( $bbdb->get_var( 'SELECT COUNT(*) FROM `' . $bbdb->posts . '` WHERE `topic_id` = ' . $old_topic->topic_id . ' AND `post_id` IN (' . implode( ',', $posts ) . ')' ) != count( $posts ) )
						bb_die( __( 'Invalid request', 'bbpress-moderation-suite' ) );

					$new_topic = get_topic( bb_insert_topic( array(
						'topic_title' => $_POST['topicid'],
						'forum_id'    => $_POST['forum_id']
					) ) );

					if ( $old_topic->forum_id != $new_topic->forum_id ) {
						// We're moving the posts out of this forum
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` - %d WHERE `forum_id` = %d', count( $posts ), $old_topic->forum_id ) );
						// And into this forum
						$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->forums . '` SET `posts` = `posts` + %d WHERE `forum_id` = %d', count( $posts ), $new_topic->forum_id ) );
					}
					// Move the post count first
					$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->topics . '` SET `topic_posts` = `topic_posts` - %d WHERE `topic_id` = %d', count( $posts ), $old_topic->topic_id ) );
					$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->topics . '` SET `topic_posts` = %d WHERE `topic_id` = %d', count( $posts ), $new_topic->topic_id ) );
					// Then move the posts
					$bbdb->query( $bbdb->prepare( 'UPDATE `' . $bbdb->posts . '` SET `topic_id` = %d, `forum_id` = %d WHERE `post_id` IN (' . implode( ',', $posts ) . ')', $new_topic->topic_id, $new_topic->forum_id ) );

					if ( function_exists( 'bbmodsuite_modlog_log' ) )
						bbmodsuite_modlog_log( sprintf( __( 'splitted %1$d post(s) from %2$s to topic: %3$s', 'bbpress-moderation-suite' ), count( $posts ), esc_html( $old_topic->topic_title ), '<a href="' . get_topic_link( $new_topic->topic_id ) . '">' . esc_html( get_topic_title( $new_topic->topic_id ) ) . '</a>' ), 'move_splittopic' );
					break;
			}
			break;
		case 'ajax-topic':
			header( 'Content-Type: application/json' );

			if ( !bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite_move-gettopic_ajax' ) )
				exit;

			$name = bbmodsuite_stripslashes( $_POST['text'] );
			$name = str_replace( array( '%', '?' ), array( '\\%', '\\?' ), substr( $_POST['text'], 0, $_POST['pos'] ) ) . '%' . str_replace( array( '%', '?' ), array( '\\%', '\\?' ), substr( $_POST['text'], $_POST['pos'] ) );

			global $bbdb;
			$results = $bbdb->get_results( $bbdb->prepare( 'SELECT `topic_title`, `topic_slug` FROM `' . $bbdb->topics . '` WHERE ( `topic_slug` LIKE %s OR `topic_title` LIKE %s OR `topic_id` = %d ) AND `topic_id` != %d ORDER BY LENGTH(`topic_slug`) ASC LIMIT 15', $name, $name, $_POST['text'], $_POST['topic'] ) );

			$ret = '[';

			foreach ( $results as $i => $result ) {
				if ( $i )
					$ret .= ',';
				$ret .= '["' . addslashes( $result->topic_slug ) . '","' . addslashes( $result->topic_title ) . '"]';
			}

			exit( $ret . ']' );
			break;
		case 'move':
			wp_enqueue_script( 'jquery' );
			break;
	}
}
add_action( 'bbpress_moderation_suite_move_pre_head', 'bbmodsuite_move_pre_header' );

function bbmodsuite_move_can_view() {
	return 'moderate';
}

function bbmodsuite_move_admin_add() {
	bb_admin_add_submenu( __( 'Move!', 'bbpress-moderation-suite' ), bbmodsuite_move_can_view(), 'bbpress_moderation_suite_move', 'bbpress_moderation_suite' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_move_admin_add' );

function bbmodsuite_move_post_admin( $a, $args ) {
	if ( bb_current_user_can( 'delete_post', get_post_id( $args['post_id'] ) ) ) {
		$a['move'] = $args['before_each'] . '<a href="' . bb_get_uri( 'bb-admin/admin-base.php', array(
			'plugin' => 'bbpress_moderation_suite_move',
			'action' => 'move',
			'post'   => get_post_id( $args['post_id'] )
		), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ) . '">' . __( 'Move', 'bbpress-moderation-suite' ) . '</a>' . $args['after_each'];
	}
	return $a;
}
add_filter( 'bb_post_admin', 'bbmodsuite_move_post_admin', 10, 2 );

function bbmodsuite_move_topic_admin( $a, $args = null ) {
	$defaults = array(
		'topic_id' => 0,
		'before'   => '[',
		'after'    => ']'
	);
	$args = wp_parse_args( $args, $defaults );
	if ( !empty( $a['move'] ) ) {
		$a['merge'] = $args['before'] . '<a href="' . bb_get_uri( 'bb-admin/admin-base.php', array(
			'plugin' => 'bbpress_moderation_suite_move',
			'action' => 'merge',
			'topic'  => get_topic_id( $args['topic_id'] )
		), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ) . '">' . __( 'Merge topic', 'bbpress-moderation-suite' ) . '</a>' . $args['after'];
		$a['split'] = $args['before'] . '<a href="' . bb_get_uri( 'bb-admin/admin-base.php', array(
			'plugin' => 'bbpress_moderation_suite_move',
			'action' => 'split',
			'topic'  => get_topic_id( $args['topic_id'] )
		), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ) . '">' . __( 'Split topic', 'bbpress-moderation-suite' ) . '</a>' . $args['after'];
	}

	return $a;
}
add_filter( 'bb_topic_admin', 'bbmodsuite_move_topic_admin', 10, 2 );

function bbmodsuite_move_modlog_descriptions( $a ) {
	$a['move_movepost'] = __( 'Post moving', 'bbpress-moderation-suite' );
	$a['move_mergetopic'] = __( 'Topic merging', 'bbpress-moderation-suite' );
	$a['move_splittopic'] = __( 'Topic splitting', 'bbpress-moderation-suite' );
}
add_filter( 'bbmodsuite_modlog_get_type_description', 'bbmodsuite_move_modlog_descriptions' );
