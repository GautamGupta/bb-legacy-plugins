<?php
/*
Plugin Name: Memberlist
Plugin URI: http://faq.rayd.org/memberlist/
Description: Displays all active members
Change Log: .73a - Added Active User Count on memberlist page
				 - Added support for multiple pages (default is 10 per page)
				 - Added better support for WPMU integration
			.73b - Added optional functionality to sort by post count
					- Requires Post Count plugin and uncommented post count code on memberlist.php
					- Get Post Count from here: http://faq.rayd.org/bbpress_postcount
				 - Fixed some strange behavior with page changing combined with ordering
				 - Added ability to change the number of users per page
			.73c - Small change for servers that need rewrite rules in .htaccess
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: .73c
*/

function get_memberlist($order = ID, $pagenum = 1, $usercount = 10) {
	global $bbdb, $bb;
	if ( isset($_GET['page']) )
		$page = ($_GET['page'] - 1) * $usercount;
	else
		$page = ($pagenum - 1) * $usercount;
	if ( $bb->wp_table_prefix ) {
		if( $order == 'postcount' )
			$result = $bbdb->get_results("SELECT * , COUNT(*) AS posts FROM ".$bb->wp_table_prefix."posts RIGHT JOIN ".$bb->wp_table_prefix."users ON poster_id = ID WHERE user_status = 0 GROUP BY user_login ORDER BY posts DESC, post_text DESC LIMIT $page, $usercount");
		else
			$result = $bbdb->get_results("SELECT * FROM ".$bb->wp_table_prefix."users WHERE user_status = 0 ORDER BY $order LIMIT $page, $usercount");
	} else {
		if( $order == 'postcount' )
			$result = $bbdb->get_results("SELECT * , COUNT(*) AS posts FROM $bbdb->posts RIGHT JOIN $bbdb->users ON poster_id = ID WHERE user_status = 0 GROUP BY user_login ORDER BY posts DESC, post_text DESC LIMIT $page, $usercount");
		else
			$result = $bbdb->get_results("SELECT * FROM $bbdb->users WHERE user_status = 0 ORDER BY $order LIMIT $page, $usercount");
	}
	return $result;
}

function get_mlist_time( $timevar ) {
	global $bb;
	return strftime("%m/%d/%y at %r",strtotime($timevar)+($bb->gmt_offset * 60 * 60));
}

function get_mlist_pages($currentpage, $usercount = 10) {
	$numpages = ceil(bb_mlist_count_users() / $usercount);
	$i = 1;
	
	if ( $currentpage <= 1 )
		echo " ";
	else
		if ( isset($_GET['orderby']) )
			if( isset($_GET['usercount']) )
				echo '<a href="'.bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=".$_GET['orderby']."&page=".($currentpage-1)).'">Prev</a>, ';
			else
				echo '<a href="'.bb_get_memberlist_link("orderby=".$_GET['orderby']."&page=".($currentpage-1)).'">Prev</a>, ';
		else
			if( isset($_GET['usercount']) )
				echo '<a href="'.bb_get_memberlist_link("usercount=".$_GET['usercount']."&page=".($currentpage-1)).'">Prev</a>, ';
			else
				echo '<a href="'.bb_get_memberlist_link("page=".($currentpage-1)).'">Prev</a>, ';
	
	while ( $i <= $numpages ) {
		if ($i == $currentpage ) {
			echo "<b>".$i."</b>";
			if ( $currentpage != $numpages )
				echo ", "; }
		elseif ( isset($_GET['orderby']) )
			if( isset($_GET['usercount']) )
				echo '<a href="'.bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=".$_GET['orderby']."&page=".$i).'">'.$i.'</a>, ';
			else
				echo '<a href="'.bb_get_memberlist_link("orderby=".$_GET['orderby']."&page=".$i).'">'.$i.'</a>, ';
		else
			if( isset($_GET['usercount']) )
				echo '<a href="'.bb_get_memberlist_link("usercount=".$_GET['usercount']."&page=".$i).'">'.$i.'</a>, ';
			else
				echo '<a href="'.bb_get_memberlist_link("page=".$i).'">'.$i.'</a>, ';
		$i++;
	}
	
	if ( $currentpage == $numpages )
		echo " ";
	else
		if ( isset($_GET['orderby']) )
			if( isset($_GET['usercount']) )
				echo '<a href="'.bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=".$_GET['orderby']."&page=".($currentpage+1)).'">Next</a>';
			else
				echo '<a href="'.bb_get_memberlist_link("orderby=".$_GET['orderby']."&page=".($currentpage+1)).'">Next</a>';
		else
			if( isset($_GET['usercount']) )
				echo '<a href="'.bb_get_memberlist_link("usercount=".$_GET['usercount']."&page=".($currentpage+1)).'">Next</a>';
			else
				echo '<a href="'.bb_get_memberlist_link("page=".($currentpage+1)).'">Next</a>';

}

function bb_memberlist_link() {
	echo apply_filters('bb_memberlist_link', bb_get_memberlist_link() );
}

function bb_get_memberlist_link( $tag = '' ) {
	if ( bb_get_option('mod_rewrite') )
		$r = bb_get_option('uri') . "mlist/" . ( '' != $tag ? "?$tag" : '' );
	else
		$r = bb_get_option('uri') . "mlist.php" . ( '' != $tag ? "?$tag" : '' );
	return apply_filters( 'get_memberlist_link', $r );
}

function bb_mlist_get_name_order_link() {
	if ( isset($_GET['orderby']) && $_GET['orderby'] == 'user_login') :
		if( isset($_GET['usercount']) )
			echo bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=user_login DESC");
		else
			echo bb_get_memberlist_link("orderby=user_login DESC");
	else :
		if( isset($_GET['usercount']) )
			echo bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=user_login");	
		else
			echo bb_get_memberlist_link("orderby=user_login");
	endif;
}

function bb_mlist_get_date_order_link() {
	if ( isset($_GET['orderby']) && $_GET['orderby'] == 'user_registered') :
		if( isset($_GET['usercount']) )
			echo bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=user_registered DESC");
		else
			echo bb_get_memberlist_link("orderby=user_registered DESC");
	else :
		if( isset($_GET['usercount']) )
			echo bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=user_registered");
		else
			echo bb_get_memberlist_link("orderby=user_registered");
	endif;
}

function bb_mlist_get_postcount_order_link() {
	if( isset($_GET['usercount']) )
		echo bb_get_memberlist_link("usercount=".$_GET['usercount']."&orderby=postcount");
	else
		echo bb_get_memberlist_link("orderby=postcount");
}


function bb_mlist_delete_users() {
	global $bbdb, $bb;
	$tode = $_POST["todel"];
	
	if ( isset($tode) ) {
	foreach ( $tode as $d ) :
		if ( $bb->wp_table_prefix )
			$delteeded = $bbdb->query("DELETE FROM ".$bb->wp_table_prefix."users WHERE ID = $d"); 
		else
			$delteeded = $bbdb->query("UPDATE $bbdb->users SET user_status = 1 WHERE ID = $d"); 
	endforeach; }
}

function bb_mlist_count_users() {
	global $bbdb, $bb;
	if ( $bb->wp_table_prefix )
		return $bbdb->query("SELECT * FROM ".$bb->wp_table_prefix."users WHERE user_status = 0");
	else
		return $bbdb->query("SELECT * FROM $bbdb->users WHERE user_status = 0");
}

function mlist_is_selected($usercount, $selectvalue){

	if( $usercount == $selectvalue )
		echo 'selected="selected"';
	else
		echo '';

}

?>