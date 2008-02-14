<?php
	require('../..//bb-load.php');
	$text =  apply_filters('pre_post',  $_POST['data']) ;
	$text =  apply_filters('post_text',  $text) ;
	$body = '<div id="live_preview_header">Your Preview post</div><br />';
	$body .= stripslashes( apply_filters('post_text',  $text) );
	$body .= '<hr /><br />';
	echo $body;
?>