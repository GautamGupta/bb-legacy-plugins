<?php
/*
Plugin Name: Forum is category
Description: Turn a forum into a "category" which cannot be posted to and does not have post/topic counts.
Plugin URI: http://bbpress.org/plugins/topic/forum-is-category/
Author: Nightgunner5
Author URI: http://llamaslayers.net/daily-llama/
Version: 1.1
*/

$forums_that_are_categories = bb_get_option("forum_is_category_forums");
if (!is_array($forums_that_are_categories))
	$forums_that_are_categories = array();
reset($forums_that_are_categories);
while (list(, $forumcat) = each($forums_that_are_categories)) {
	if (!in_array(get_forum_parent($forumcat), $forums_that_are_categories)) {
		$forums_that_are_categories[] = get_forum_parent($forumcat);
		reset($forums_that_are_categories);
	}
}

function forum_is_category_restrict_posting($retvalue, $capability, $args) {
	global $forums_that_are_categories;
	if ($capability == "write_topic" && in_array($args[1], $forums_that_are_categories)) {
		return false;
	} elseif ($capability == "move_topic" && in_array($args[2], $forums_that_are_categories)) {
		return false;
	}
	return $retvalue;
}
add_filter('bb_current_user_can', 'forum_is_category_restrict_posting', 10, 3);

function forum_is_category_restrict_posttopic_view($posts, $id) {
	global $forums_that_are_categories;
	if (in_array($id, $forums_that_are_categories)) {
		return false;
	}
	return $posts;
}
add_filter('get_forum_posts', 'forum_is_category_restrict_posttopic_view', 10, 2);
add_filter('get_forum_topics', 'forum_is_category_restrict_posttopic_view', 10, 2);

function forum_is_category_admin_page_add() {
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Forum is category', 'forum-is-category'), 'use_keys', 'forum_is_category_admin_page');
	} else {
		global $bb_submenu;
		$bb_submenu['content.php'][] = array(__('Forum is category', 'forum-is-category'), 'use_keys', 'forum_is_category_admin_page');;
	}
}
add_action('bb_admin_menu_generator', 'forum_is_category_admin_page_add');

function forum_is_category_row() {
	global $forum, $forums_count, $forums_that_are_categories;

	$_forum =& $forum;

	$r  = '';
	$r .= "\t\t<div class='list-block posrel'>\n";
	$r .= "\t\t\t<div class='alignright'>\n";
	$r .= "\t\t\t\t<input type='checkbox' name='forum-is-category[]' value='".$_forum->forum_id."' id='forum-is-category[]'".((in_array($_forum->forum_id, $forums_that_are_categories)) ? " checked='checked'" : "")." />";
	$r .= "\t\t\t</div>\n";
	$r .= "\t\t\t" . get_forum_name( $_forum->forum_id ) . ' &#8212; ' . get_forum_description( $_forum->forum_id ) . "\n\t\t</div>\n";

	echo $r;
}

function forum_is_category_admin_page() {
	global $forums_that_are_categories;

?>
	<h2><?php _e('Forum is category', 'forum-is-category'); ?></h2>
	<form method="post">
<?php if ( bb_forums( 'type=list&walker=BB_Walker_ForumAdminlistitems' ) ) : ?>
<ul id="the-list" class="list-block holder">
	<li class="thead list-block"><div class="list-block"><?php _e('Name &#8212; Description'); ?></div></li>
<?php while ( bb_forum() ) : ?>
<?php forum_is_category_row(); ?>
<?php endwhile; ?>
<?php endif; // bb_forums() ?>
</ul>
	<p class="submit alignleft">
		<input name="submit" id="submit" type="submit" value="<?php _e('Update'); ?>" />
		<input type="hidden" id="action" name="action" value="forum_is_category_update" />
	</p>
	</form>
<?php
}

function forum_is_category_admin_page_process() {
	if (isset($_POST['submit'])) {
		if ('forum_is_category_update' == $_POST['action']) {
			global $forums_that_are_categories;
			$forums_that_are_categories = array_map(intval, $_POST['forum-is-category']);

			if (count($forums_that_are_categories)) {
				bb_update_option('forum_is_category_forums', $forums_that_are_categories);
			} else {
				bb_delete_option('forum_is_category_forums');
			}
		}
	}
}
add_action('bb_admin-header.php','forum_is_category_admin_page_process');

?>