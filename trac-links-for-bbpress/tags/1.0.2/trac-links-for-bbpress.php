<?php
/*
Plugin Name: Trac links for bbPress
Plugin URI: http://bbpress.org/plugins/topic/trac-links-for-bbpress/
Description: Allows the use of Trac shortcodes like #1234 for tickets and [1234] for changesets within post text.
Author: Sam Bauers
Author URI: http://unlettered.org/
Version: 1.0.2
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



$tl_code_blocks = array();

function tl_store_code_blocks( $text )
{
	global $tl_code_blocks;
	$tl_code_blocks = array();

	$tl_text_blocks = array();

	$all_blocks = preg_split( '@(<code>.*</code>)@mU', $text, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE );

	foreach ( $all_blocks as $block ) {
		if ( strpos( $block, '<code>' ) === 0 ) {
			$tl_code_blocks[] = $block;
		} else {
			$tl_text_blocks[] = $block;
		}
	}

	return join( '%%tl_code_block%%', $tl_text_blocks );
}

function tl_restore_code_blocks( $text )
{
	if ( strpos( $text, '%%tl_code_block%%' ) === false ) {
		return $text;
	}

	global $tl_code_blocks;

	foreach ( $tl_code_blocks as $block ) {
		$text = preg_replace( '@%%tl_code_block%%@', $block, $text, 1 );
	}

	$tl_code_blocks = array();

	return $text;
}

function tl_make_tracable( $text )
{
	global $tl_trac;
	global $tl_intertrac;
	if ( !$tl_trac ) {
		return $text;
	}

	// Check if anything in the text matches the basic form of the patterns at all
	if ( !preg_match('@(?:#[a-z0-9]+)|(?:\[[a-z0-9]+\])@i', $text ) ) {
		return $text;
	}

	// Strip out code blocks and save them
	$text = tl_store_code_blocks( $text );
$_text = $text;
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
			$_tracs[$i][0][0] = '([^&]?)#' . preg_quote( $trac_id ) . '([0-9]+)';
			$_tracs[$i][0][1] = '$1';
			$_tracs[$i][0][2] = $trac_url . 'ticket/$2';
			$_tracs[$i][0][3] = '#' . $trac_id . '$2';
			$_tracs[$i][1][0] = '\[' . preg_quote( $trac_id ) . '([0-9]+)\]';
			$_tracs[$i][1][1] = '';
			$_tracs[$i][1][2] = $trac_url . 'changeset/$1';
			$_tracs[$i][1][3] = '[' . $trac_id . '$1]';
			$i++;
		}
	}

	if ( !count( $_tracs ) ) {
		return $text;
	}

	foreach ( $_tracs as $pairs ) {
		foreach ( $pairs as $pair ) {
			$text = preg_replace( '@' . $pair[0] . '@', $pair[1] . '<a href="' . $pair[2] . '">' . $pair[3] . '</a>', $text );
		}
	}

	// Put the code blocks back in
	$text = tl_restore_code_blocks( $text );

	return $text;
}
add_filter( 'post_text', 'tl_make_tracable', 1 );
?>