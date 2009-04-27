<?php
/*

Plugin Name: Wordpress Latest Post
Plugin URI: http://www.atsutane.net
Description: Wordpress Latest Post On Bbpress
Author: Atsutane Shirane
Author URI: http://www.atsutane.net
Version: 0.1

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
$blog_address = 'http://localhost'; // Example: http://www.google.com
$wp_table_prefix = 'wp_';

### Function Get Wordpress Data
function get_wp_postdata($title = '', $limit = 10, $before_title = '<h2>', $after_title = '</h2>', $before = '<ul>', $after = '</ul>') {
	global $bbdb, $blog_address, $wp_table_prefix;
	$wppost = $bbdb->get_results("SELECT * FROM ".$wp_table_prefix."posts WHERE post_type = 'post' AND post_status = 'publish' ORDER BY ID DESC LIMIT ".$limit);
	echo $before_title.$title.$after_title.$before;
	if ($wppost) {
		foreach ($wppost as $wp) {
			$wp_title = $wp->post_title;
			$wp_id = $wp->ID;
			$wp_posturl = $blog_address . '/?p=' . $wp_id;
			echo '<li><a href="'.$wp_posturl.'" title="'.$wp_title.'">'.$wp_title.'</a></li>';			
		}
	}
	echo $after;
}

?>