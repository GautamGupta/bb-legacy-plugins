/*
Plugin Name: bbPress Recent Replies
Plugin URI: http://bbpress.org/plugins/topic/bbpress-recent-replies/
Version: 0.1b
Description: Shows recent replies just like we have recent comments plugin on WordPress
Author: Ashfame
Author URI: http://www.ashfame.com/
Donate: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8357028
Possible Improvements : 1) Better MySQL queries 2) Options
*/

1)

Call
ashfame_recent_bbpress_replies($limit=5,$heading_opening_tag="<h2>",$heading_closing_tag="</h2>");
within your template anywhere to show recent reply list.

2)

To edit CSS, change $css variable in plugin file or remove the line
add_action('bb_head','ashfame_recent_bbpress_replies_style');
and add CSS in your theme's stylesheet

3)

For more options, you can change it in plugin itself.