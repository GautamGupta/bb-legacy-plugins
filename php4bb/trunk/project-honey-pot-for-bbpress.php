<?php
/*
Plugin Name: Project Honey Pot for bbPress
Description: Block spammers before they ever register on your forum.
Plugin URI: http://nightgunner5.wordpress.com/tag/project-honey-pot-for-bbpress
Author: Nightgunner5
Author URI: http://llamaslayers.net/
Version: 0.1
Requires at least: 1.0
Tested up to: trunk
*/

global $php4bb_counter;
$php4bb_counter = mt_rand( 1, 7 );

function php4bb_block() {
	$settings = bb_get_option( 'php4bb' );
	if ( $settings['httpbl-redirect'] ) {
		wp_redirect( $settings['httpbl-redirect'] );
		exit;
	}
	bb_die( __( 'You have been blocked from this forum because your IP is <a href="http://www.projecthoneypot.org/ip_' . $_SERVER['REMOTE_ADDR'] . '">listed on Project Honey Pot</a>.', 'php4bb' ) );
}

function php4bb_maybe_block() {
	$settings = bb_get_option( 'php4bb' );
	if ( !$settings['httpbl-key'] )
		return;

	$check = $settings['httpbl-key'] . implode( '.', array_reverse( explode( '.', $_SERVER['REMOTE_ADDR'] ) ) ) . 'dnsbl.httpbl.org';
	$response = gethostbyname( $check );

	if ( $response == $check )
		return;

	$response = explode( '.', $response );

	if ( $response[0] != '127' )
		return;

	$days = (int)$response[1];
	$threat = (int)$response[2];
	$type = (int)$response[3];

	if ( $type == 0 ) // Search engine
		return;

	if ( $settings['httpbl-days'] >= $days && $settings['httpbl-threat'] <= $threat && ( ( $settings['httpbl-confirmedonly'] && $type > 1 ) || !$settings['httpbl-confirmedonly'] ) )
		php4bb_block();
}
php4bb_maybe_block();

function php4bb_head() {
	$settings = bb_get_option( 'php4bb' );
	$noemail = $settings['noemail'];
?><meta name="no-email-collection" content="<?php echo $noemail ? $noemail : 'http://www.unspam.com/noemailcollection'; ?>" /><?php
}
add_action( 'bb_header', 'php4bb_head' );

function php4bb_add_link( $a ) {
	echo php4bb_generate_hpot_link();
	return $a;
}
add_action( 'bb_foot', 'php4bb_add_link' );
add_action( 'bb_after_header.php', 'php4bb_add_link' );
add_action( 'bb_after_post.php', 'php4bb_add_link' );
add_action( 'pre_post_form', 'php4bb_add_link' );
add_filter( 'view_pages', 'php4bb_add_link' );

function php4bb_generate_hpot_link() {
	global $php4bb_counter;
	if ( !mt_rand( 0, $php4bb_counter ) || $php4bb_counter-- < 1 )
		return '';

	$settings = bb_get_option( 'php4bb' );
	if ( !$settings['hpot-url'] ) {
		$php4bb_counter = 0;
		return '';
	}

	$linkformats = array( '<a href="%"><!-- _ --></a>', '<a href="%" style="display: none;">_</a>', '<div style="display: none;"><a href="%">_</a></div>', '<a href="%"></a>', '<!-- <a href="%">_</a>  -->', '<div style="position: absolute; top: -250px; left: -250px;"><a href="%">_</a></div>', '<a href="%"><span style="display: none;">_</span></a> ', '<a href="%"><div style="height: 0px; width: 0px;"></div></a>' );
	$text = array( 'Private', 'Secret', 'Email', 'Contact us', 'Magical rainbow ponies', 'Admin', 'Edit page', 'Register' );

	$link = $linkformats[mt_rand( 0, count( $linkformats ) - 1 )];
	$link = str_replace( '_', $text[mt_rand( 0, count( $text ) - 1 )], $link );
	$link = str_replace( '%', $settings['hpot-url'], $link );

	return $link;
}

if ( bb_is_admin() )
	require_once dirname( __FILE__ ) . '/project-honey-pot-for-bbpress-admin.php';

?>