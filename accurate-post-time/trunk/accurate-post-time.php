<?php
/*
Plugin Name: Accurate Post Time
Plugin URI:
Description: Outputs an accurate post time in, optionally, your choice of formatting. Use <?php aposttime( 'DATE FORMAT' ); ?> in your template, <a href="http://uk3.php.net/date">date formatted according to PHP standards.</a>
Author: fel64
Version: Phi
Author URI: http://www.loinhead.net/
*/

function aposttime ( $dtformat = "jS F 'y" ) {
	global $bb_post;
	$aposttime = date( $dtformat, strtotime( $bb_post->post_time ) );
	echo $aposttime;
}
?>
