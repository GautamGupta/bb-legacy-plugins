<?php
/*

Plugin Name: Wordpress Latest Post
Plugin URI: http://www.atsutane.net
Description: Wordpress Latest Post On Bbpress
Author: Atsutane Shirane
Author URI: http://www.atsutane.net
Version: 0.2

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Copyright 2009 Atsutane Shirane, Atsutane.net

*/

### Configuration
$blog_address = 'http://localhost/blog'; // Example: http://www.google.com
$wp_table_prefix = 'wp_';

### Function Get Wordpress Data
function get_wp_postdata($title = '', $limit = 10, $before_title = '<h2>', $after_title = '</h2>', $before = '<ul>', $after = '</ul>') {
	global $bbdb, $blog_address, $wp_table_prefix;
	$wp_url = get_wp_permalink(); // Call Permalink Function
	$wppost = $bbdb->get_results("SELECT * FROM ".$wp_table_prefix."posts WHERE post_type = 'post' AND post_status = 'publish' ORDER BY ID DESC LIMIT ".$limit);
	echo $before_title.$title.$after_title.$before;
	if ($wppost) {
		foreach ($wppost as $wp) {
			$wp_title = $wp->post_title;
			$wp_date = strtotime($wp->post_date);
			$wp_slug = $wp->post_name;
			$wp_id = $wp->ID;
			// Start Permalink Template
			$wp_showurl = $wp_url;
			$wp_showurl = str_replace("%year%", date('Y', $wp_date), $wp_showurl);
			$wp_showurl = str_replace("%monthnum%", date('m', $wp_date), $wp_showurl);
			$wp_showurl = str_replace("%day%", date('d', $wp_date), $wp_showurl);
			$wp_showurl = str_replace("%hour%", date('H', $wp_date), $wp_showurl);
			$wp_showurl = str_replace("%minute%", date('i', $wp_date), $wp_showurl);
			$wp_showurl = str_replace("%second%", date('s', $wp_date), $wp_showurl);
			$wp_showurl = str_replace("%postname%", $wp_slug, $wp_showurl);
			$wp_showurl = str_replace("%post_id%", $wp_id, $wp_showurl);
			// Stop Permalink Template
			$wp_posturl = $blog_address . $wp_showurl;
			echo '<li><a href="'.$wp_posturl.'" title="'.$wp_title.'">'.$wp_title.'</a></li>';			
		}
	}
	echo $after;
}

### Function Get Permalink Data
function get_wp_permalink() {
	global $bbdb, $wp_table_prefix;
	$wpurl = $bbdb->get_row("SELECT * FROM ".$wp_table_prefix."options WHERE option_name = 'permalink_structure' LIMIT 1");
	if ($wpurl) {
			$wp_perma = $wpurl->option_value;
	}
	return $wp_perma;
}

?>