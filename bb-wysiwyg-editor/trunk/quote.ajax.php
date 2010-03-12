<?php

if ( !isset( $_GET['quoted'] ) ) {
    die('');
}

require('../../bb-load.php');

if ( bb_current_user_can('write_posts') ){
	$quoted_id = (int) $_GET['quoted'];
	bb_check_admin_referer('quote-' . $quoted_id);
	if ( $quoted_id <= 0 ) {
        die('');
    }
	
	$quoted_post = bb_get_quoted_post( $quoted_id );
	if ( $quoted_post === false || empty( $quoted_post ) ) {
        die('');
    }
	die($quoted_post);

} else {
    die('');
}
	
