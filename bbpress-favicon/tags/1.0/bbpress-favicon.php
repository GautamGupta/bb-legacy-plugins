<?php
/*
Plugin Name: bbPress Favicon
Plugin URI: http://rohan-kapoor.com/projects/plugins/bbpress-favicon
Description: Adds a custom favicon to all bbPress pages! Compliments my WordPress/MU Favicon Plugin!
Version: 1.0
Author: Rohan Kapoor
Author URI: http://rohan-kapoor.com
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function bb_sitewide_favicon() {
	echo '<link rel="shortcut icon" href="http://idwellness.org/wp-content/bp-themes/idwellness home/favicon.ico" />';
}

add_action('bb_admin_head', 'bb_sitewide_favicon');
add_action('bb_head', 'bb_sitewide_favicon');

?>
