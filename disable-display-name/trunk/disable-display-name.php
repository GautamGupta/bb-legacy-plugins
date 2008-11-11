<?php
/*
Plugin Name: Disable Display Name
Plugin URI:  http://bbpress.org/plugins/topic/disable-display-name
Description: Blocks the alternate Display Name functionality in bbPress 1.x to prevent spoofing other members and admin.
Version: 0.0.1
Author: _ck_
Author URI: http://bbshowcase.org

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/ 

function disable_display_name($display_name="", $id=0){return get_user_name($id);}
add_filter('get_user_display_name', 'disable_display_name',9,2);

function disable_display_name_keys($keys){unset($keys['display_name']); return $keys;}
add_filter('get_profile_info_keys', 'disable_display_name_keys',9);

?>