<?php
/*
Plugin Name: Easy Twitter Links
Version: 0.1
Plugin URI: http://gaut.am/bbpress/plugins/easy-twitter-links/
Description: Creates links automatically from #tagname or @username anywhere within your forum posts to Twitter links. This plugin is inspired from the <a href="http://eight7teen.com/ez-twitter-links/">EZ Twitter Links</a> Plugin for Wordpress made by <a href="http://eight7teen.com/">Josh</a>.
Author: Gautam Gupta
Author URI: http://gaut.am/
*/

function etl_link_tag($auto_user) {
	$user_id = '/([^a-zA-Z0-9])\@([a-zA-Z0-9_]+)/';
	$user_link = '\1@<a href="http://twitter.com/\2" rel="nofollow" target="_blank" title="View \2\'s Twitter Profile">\2</a>\3';
	$auto_tags = preg_replace($user_id,$user_link,$auto_user);
	$twitter_tags = '/(^|\s)#(\w+)/';
	$tag_links = '\1#<a href="http://search.twitter.com/search?q=%23\2" rel="nofollow" target="_blank" title="Search Twitter for &quot;\2&quot;">\2</a>';
	echo preg_replace($twitter_tags,$tag_links,$auto_tags);
}

add_filter('post_text','etl_link_tag', -300);
?>