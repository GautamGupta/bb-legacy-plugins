<?php
/*
	Header and table cell rendering
*/

function ud_sanity_check_columns($columnNames, $columnHeaderRenderers, $columnRenderers) {
	$displayColumns = array();
	foreach( $columnNames as $columnName ) {
		$headerRenderer = $columnHeaderRenderers[$columnName];
		$cellRenderer = $columnRenderers[$columnName];
		if (isset($headerRenderer) && function_exists($headerRenderer) &&
			isset($cellRenderer) && function_exists($cellRenderer)) {
			$displayColumns[] = $columnName;
		}
	}
	return $displayColumns;
}

function ud_get_column_names() {
	global $headerRenderers;
	return array_keys($headerRenderers);
}

function ud_get_column_header_renderers() {
	global $headerRenderers;
	return $headerRenderers;
}

function ud_get_column_renderers() {
	global $columnRenderers;
	return $columnRenderers;
}

function ud_render_default_header($columnName) {
	return $columnName;
}

function ud_render_user($columnName, $user) {
	return '<a href="'.get_user_profile_link($user->ID).'">'.(empty($user->display_name) ? $user->user_nicename : $user->display_name).'</a>';
}

function ud_render_website($columnName, $user) {
	$value = '';

	if (isset($user->user_url) && strlen($user->user_url) > 7) {
		$value = '<a href="' . $user->user_url . '" rel="nofollow">' . $user->user_url . '</a>'; 
	} else {
		$value = '&nbsp;';
	}

	return $value;
}

function ud_render_registered($columnName, $user) {
	return $user->user_registered;
}

function ud_register_column($columnName, $columnRenderer, $headerRenderer = 'ud_render_default_header') { 
	global $columnRenderers, $headerRenderers;
	
	$columnRenderers[$columnName] = $columnRenderer;
	$headerRenderers[$columnName] = $headerRenderer;
}

function ud_register_column_at_left($columnName, $columnRenderer, $headerRenderer = 'ud_render_default_header') {
	global $columnRenderers, $headerRenderers;

	$cr = array();
	$hr = array();
	$cr[$columnName] = $columnRenderer;
	$hr[$columnName] = $headerRenderer;
	foreach( array_keys($columnRenderers) as $columnName ) {
		$cr[$columnName] = $columnRenderers[$columnName];
		$hr[$columnName] = $headerRenderers[$columnName];
	}
	$columnRenderers = $cr;
	$headerRenderers = $hr;
}

function ud_register_default_columns() {
	ud_register_column('User', 'ud_render_user');
	ud_register_column('Website', 'ud_render_website');
	ud_register_column('Registered', 'ud_render_registered');
}

?>