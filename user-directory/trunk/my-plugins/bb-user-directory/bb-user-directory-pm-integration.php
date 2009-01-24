<?php
/*
	Private messaging plugin integration
*/

function ud_render_pm_header($columnName) {
	return '&nbsp;';
}

function ud_render_pm($columnName, $user) {
	$value = '';
	if ( bb_current_user_can('write_post') ) {
		$value .= '<a title="PM This User" href="';
		$value .= apply_filters('pm_user_link', bb_get_pm_link( '?user='.$user->ID ) );
		$value .= '" rel="nofollow">';
		$value .= '<img src="'.bb_get_active_theme_uri();
		$value .= 'newmail.png" border="0" align="top" /></a>';
	}
	return $value;
}

function ud_register_pm_if_present() {
	if (function_exists('bb_get_pm_link')) {
		ud_register_column_at_left('PM Integration', 'ud_render_pm', 'ud_render_pm_header');
	}
}

add_action( 'bb_head', 'ud_register_pm_if_present' );
add_action( 'bb_admin_head', 'ud_register_pm_if_present' );

?>