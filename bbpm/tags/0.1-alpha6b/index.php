<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/bb-load.php';

if ( strpos( $_SERVER['REQUEST_URI'], bb_get_option( 'path' ) . 'pm' ) === false )
	$_SERVER['REQUEST_URI'] = bb_get_option( 'path' ) . 'pm/' . $_SERVER['QUERY_STRING'];

require_once dirname( __FILE__ ) . '/privatemessages.php';

?>