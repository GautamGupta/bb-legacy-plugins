<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/bb-load.php';

if ( strpos( $_SERVER['REQUEST_URI'], bb_get_option( 'path' ) . 'pm' ) === false )
$_SERVER['REQUEST_URI'] = bb_get_option( 'path' ) . 'pm/' . substr( $_SERVER['REQUEST_URI'], strpos( $_SERVER['REQUEST_URI'], '?' ) + 1 );

require_once dirname( __FILE__ ) . '/privatemessages.php';

?>