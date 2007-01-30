<?php
/**
 * Plugin Name: Since last visit
 * Plugin Description: Displays all new posts since user's last visit.
 * Author: Thomas Klaiber
 * Author URI: http://www.la-school.com
 * Plugin URI: http://www.la-school.com/2006/
 * Version: 0.5
 */

function user_last_online_init() {
	global $bbdb, $bb_current_user, $bb_table_prefix;

	$now = get_online_timeout_time();
	
	if ( bb_is_user_logged_in() ) :
		$user_last_online = $bbdb->get_var("SELECT activity FROM ".$bb_table_prefix."online WHERE user_id=$bb_current_user->ID LIMIT 1");
				
		if ( strtotime($user_last_online) < $now ) :
			bb_update_usermeta($bb_current_user->ID, "last_visit", $user_last_online);
		endif;		
	endif;
}
add_action('bb_init', 'user_last_online_init', 1);

function since_last_visit_views( $views ) {
	global $views;
	$views['since-last-visit'] = 'Posts since last visit';
	return $views;
}
add_filter('bb_views', 'since_last_visit_views');

function view_since_last_visit( $view ) {
	global $bbdb, $topics, $view_count;

	switch ( $view ) :
	case 'since-last-visit' :
		add_filter('get_latest_topics_where', 'view_since_last_visit_where');	
		$topics = get_latest_topics( 0, $page );
		$view_count = bb_count_last_query();
	break;
	default :
		do_action( 'bb_custom_view', $view );
	endswitch;
}
add_action( 'bb_custom_view', 'view_since_last_visit' );

function view_since_last_visit_where( $where ) {
	global $where, $bb_current_user;
	
	if ( bb_is_user_logged_in() ) :
		$user = bb_get_user($bb_current_user->ID);
		$last_visit = $user->last_visit;
	else :
		$last_visit = time();
	endif;
	
	$where = " WHERE topic_status = 0 AND topic_time > '$last_visit' ";
	return $where;
}

function view_since_last_visit_user() {
	global $bb_current_user;
	
	if ( bb_is_user_logged_in() ) :
		$user = bb_get_user($bb_current_user->ID);
		return $user->last_visit;
	else :
		return time();
	endif;	
}

?>