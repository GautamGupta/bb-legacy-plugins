<?php
/*
Plugin Name: Graphic User Rank
Plugin URI: http://www.brightandearlyblog.com/bbPress/downloads/graphic-user-rank.zip
Description: Displays a graphic image based on the posters post count. Incorporates the post-count functions from bb-post-count by Joshua Hutchins
Change Log: .1 Initial release. May need major modifications. My first plugin.
Author: Jim Lynch
Author URI: http://www.brightandearlyblog.com/bbPress/
Version: .1
*/

/* bb-post-count code by Joshua Hutchins */

function post_count() {
	if (  get_post_author_id() ) 
		{echo 'Post Count: ' . get_post_count( get_post_author_id() );
                }
	else
                {
		echo 'Error';
                }
}

function get_post_count ( $id ) {
	global $bbdb;

	return $bbdb->query("SELECT * FROM $bbdb->posts WHERE poster_id = $id AND post_status = 0");

}

/* End bb-post-count code. Thank you Joshua! */

/*YOU CAN CONFIGURE THESE VARIABLES */

$num_ranks=6; //Should be equal (or less) than the number of ranks you are going to display and have images for.

$path_to_rank_images= bb_get_option('uri') . 
"my-plugins/ranks/"; //You should not need to change this unless you have your images in a different location.

$rank_max=array();
$rank_max[1]=6; //Rank 1 will be for 1-5 posts.
$rank_max[2]=11;
$rank_max[3]=26;
$rank_max[4]=51;
$rank_max[5]=101;

$rank_img=array();
$rank_img[1]="pen_ranks1.gif";
$rank_img[2]="pen_ranks2.gif";
$rank_img[3]="pen_ranks3.gif";
$rank_img[4]="pen_ranks4.gif";
$rank_img[5]="pen_ranks5.gif";
$rank_img[6]="pen_ranks6.gif";

/*END OF CONFIGURABLE VARIABLES */

function user_rank() {
global $bbdb, $num_ranks, $path_to_rank_images, $rank_max, $rank_img, $bb;
        $id=get_post_author_id();
$rank_count=get_post_count(get_post_author_id());
for ($i=1;$i<$num_ranks;$i++)
		{
		 if ($rank_count<$rank_max[$i])
			{$display_text=$path_to_rank_images . $rank_img[$i];
                 break;
			}
			else
			{$display_text = $path_to_rank_images . $rank_img[$num_ranks];
			} 	
		}
$display_rank = "<img src='" . $display_text . "' alt='Rank' />";
return $display_rank;
}

function apply_author_title_image( $r ) {
$r = '<br />';
$get_image=user_rank();
$r .= $get_image;
return $r;
}
add_filter('post_author_title', 'apply_author_title_image');

?>