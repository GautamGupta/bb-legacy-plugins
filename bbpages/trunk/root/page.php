<?php
require_once('./bb-load.php');
$page_id = 0;

bb_repermalink();

if ( !$page )
	bb_die(__('Page not found.'));

do_action( 'bb-page.php', $page_id );

bb_load_template( 'page.php', array('bb_db_override', 'page_id') );
?>