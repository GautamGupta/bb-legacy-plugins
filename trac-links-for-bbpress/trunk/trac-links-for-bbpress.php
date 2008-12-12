<?php
/*
Plugin Name: Trac links for bbPress
Plugin URI: http://bbpress.org/plugins/topic/trac-links-for-bbpress/
Description: Allows the use of Trac shortcodes like #1234 for tickets and [1234] for changesets within post text.
Author: Sam Bauers
Author URI: http://unlettered.org/
Version: 1.0.1
*/



// Add a single Trac site here - this will be the primary Trac
// which is addressable by basic codes like #123 or [4321]
$tl_trac = 'http://trac.bbpress.org/';

// Use these if you have multiple trac installs to reference, the
// key needs to be prepended to the code like: #BB123 or [WP4321]
$tl_intertrac = array(
	'BB' => 'http://trac.bbpress.org/',
	'BP' => 'http://backpress.automattic.com/',
	'WP' => 'http://trac.wordpress.org/'
);



function tl_make_tracable( $text )
{
	global $tl_trac;
	global $tl_intertrac;

	if ( !$tl_trac ) {
		return $text;
	}

	if ( !preg_match('@(?:[^&]#[a-z0-9]+)|(?:\[[a-z0-9]+\])@i', $text ) ) {
		return $text;
	}

	$tracs = array( $tl_trac );
	if ( isset( $tl_intertrac ) && is_array( $tl_intertrac ) ) {
		$tracs = array_merge( $tracs, $tl_intertrac );
	}

	$_tracs = array();
	$i = 0;
	foreach ( $tracs as $trac_id => $trac_url ) {
		if ( $trac_url ) {
			if ( !$parsed_trac_url = parse_url( $trac_url ) ) {
				continue;
			}
			if ( $trac_id === 0 ) {
				$trac_id = '';
			}
			$trac_url = rtrim( $trac_url, '/' ) . '/';
			$_tracs[$i][0][0] = '[^&]#' . preg_quote( $trac_id ) . '([0-9]+)';
			$_tracs[$i][0][1] = $trac_url . 'ticket/$1';
			$_tracs[$i][0][2] = '#' . $trac_id . '$1';
			$_tracs[$i][1][0] = '\[' . preg_quote( $trac_id ) . '([0-9]+)\]';
			$_tracs[$i][1][1] = $trac_url . 'changeset/$1';
			$_tracs[$i][1][2] = '[' . $trac_id . '$1]';
			$i++;
		}
	}

	if ( !count( $_tracs ) ) {
		return $text;
	}

	foreach ( $_tracs as $pairs ) {
		foreach ( $pairs as $pair ) {
			$text = preg_replace( '@' . $pair[0] . '@', '<a href="' . $pair[1] . '">' . $pair[2] . '</a>', $text );
		}
	}

	return $text;
}
add_filter( 'post_text', 'tl_make_tracable', 1 );
?>