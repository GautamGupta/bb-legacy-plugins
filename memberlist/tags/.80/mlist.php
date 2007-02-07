<?php
require_once('./bb-load.php');

bb_mlist_delete_users();

if ( isset($_GET['usercount']) )
	$usercount = $_GET['usercount'];
else
	$usercount = 10;

if ( isset($_GET['orderby']) ) :
	$order = $_GET['orderby'];
else :
	$order = 'ID';
endif;

if ( isset($_GET['page']) && ($_GET['page'] <= ceil(bb_mlist_count_users() / $usercount) ) ) :
	$currentpage = $_GET['page'];
elseif ( !isset($_GET['page']) ) :
	$currentpage = 1;
else :
	$currentpage = 0;
endif;

$memberresult = get_memberlist($order, $currentpage, $usercount);

bb_load_template( 'memberlist.php', array('bb_db_override', 'usercount', 'order', 'currentpage', 'memberresult') );
?>

