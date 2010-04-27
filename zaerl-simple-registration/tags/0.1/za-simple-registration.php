<?php
/*
Plugin Name: zaerl Simple Registration
Plugin Description: A simpler registration page
Version: 0.1
Plugin URI: http://www.zaerl.com
Author: zaerl
Author URI: http://www.zaerl.com

zaerl Easter: a simpler registration page for bbPress
Copyright (C) 2010  Francesco Bigiarini

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

*/
	
define('ZA_SR_VERSION', '0.1');
define('ZA_SR_ID', 'za-simple-registration');
define('ZA_EASTER_NAME', 'zaerl Simple Registration');

function za_simple_registration($fields, $context)
{
	unset($fields['first_name']);
	unset($fields['last_name']);
	unset($fields['user_url']);
	unset($fields['from']);
	unset($fields['occ']);
	unset($fields['interest']);

	return $fields;
}

if(bb_get_location() == 'register-page')
	add_filter('get_profile_info_keys', 'za_simple_registration', 10, 2);

?>