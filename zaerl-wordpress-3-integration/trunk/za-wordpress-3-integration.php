<?php
/*
Plugin Name: zaerl WordPress 3 integration
Plugin Description: WordPress 3 deep integration fix
Version: 0.1
Plugin URI: http://www.zaerl.com
Author: zaerl
Author URI: http://www.zaerl.com

zaerl WordPress 3 integration: WordPress 3 deep integration fix
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
	
define('ZA_WI_VERSION', '0.1');
define('ZA_WI_ID', 'za-wordpress-3-integration');
define('ZA_WI_NAME', 'zaerl WordPress 3 integration');

function za_wi_initialize()
{
	if(isset($GLOBALS['wp_version']) &&
		version_compare($GLOBALS['wp_version'], '3.0-RC1', '>=') &&
		bb_get_location() == 'register-page')
		$GLOBALS['user_email'] = null;
}

add_action('bb_init', 'za_wi_initialize');

?>