<?php
/**
 * @package bbPM
 * @version 0.1-beta1
 * @author Nightgunner5
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3 or higher
 */

/**
 * Load bbPress core
 */
require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/bb-load.php';

if ( strpos( $_SERVER['REQUEST_URI'], bb_get_option( 'path' ) . 'pm' ) === false )
	$_SERVER['REQUEST_URI'] = bb_get_option( 'path' ) . 'pm/' . $_SERVER['QUERY_STRING'];

if ( !$template = bb_get_template( 'privatemessages.php', false ) ) {
	$template = dirname( __FILE__ ) . '/privatemessages.php';
}
/**
 * Load up the PM template
 *
 * @uses privatemessages.php (the default)
 */
require_once $template;

?>