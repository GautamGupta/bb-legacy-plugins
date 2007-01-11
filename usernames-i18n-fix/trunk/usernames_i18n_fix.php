<?php
/*
Plugin Name: Usernames I18N Fix
Plugin URI: http://sewar.wordpress.com/bbpress/plugins/usernames-i18n-fix/
Description: Allow users to use I18N non-English  names.
Author: Sewar
Author URI: http://sewar.wordpress.com
Version: 1.0
*/

/*
    Usernames I18N Fix, bbPress plugin allows users to use I18N non-English names.
    Copyright (C) 2006  Sewar

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function user_sanitize_i18n_fix($text, $raw, $strict){
	$new_text = $raw;

	if ( $strict )
		$new_text = preg_replace('|-+|', '-', $new_text);

	return $new_text;
}

add_filter('user_sanitize', 'user_sanitize_i18n_fix', 10, 3);

?>