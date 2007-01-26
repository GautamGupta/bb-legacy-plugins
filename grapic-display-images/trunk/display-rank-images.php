<?php
/*
Plugin Name: Graphic User Rank
Plugin URI: http://www.brightandearlyblog.com/bbPress/downloads/graphic-user-rank.zip
Description: Displays a graphic image based on the posters post count. Incorporates the post-count functions from bb-post-count by Joshua Hutchins.
Change Log: 
0.6 Changed "path_to_rank_images" to "path_to_subdirectory". Moved configurable variables to config.php in subdirectory. Added second set of rank images that are smaller than the default threadauthor width setting and made them the default set.
Uploaded revised zip file to Plugin URI.

0.5 Added the ability to display a different graphic for Key Master and Moderator

0.1 Initial release. May need major modifications. My first plugin.

Author: Jim Lynch
Author URI: http://www.brightandearlyblog.com/bbPress/
Version: 0.6
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

$path_to_subdirectory= bb_get_option('uri') . "my-plugins/ranks/";
$get_config = $path_to_subdirectory . "config.php";

include($get_config);

function get_special_rank () {
global $special_rank, $use_special_rank;
	
	if ($use_special_rank==1)
		{$title_for_rank=get_user_type( get_post_author_id() );
		switch ($title_for_rank) {
			case "Key Master" :
			$special_rank=1;
			break;
			case "Moderator" :
			$special_rank=2;
			break;
			default :
			$special_rank=0;
		}
	}
	else
	{	
	$special_rank = 0;
	}

return $special_rank;
	
}

function user_rank() {
global $num_ranks, $path_to_subdirectory, $rank_max, $rank_img, $special_rank, $image_for_special_rank_1, $image_for_special_rank_2;
        $id=get_post_author_id();
$special_rank_val = get_special_rank ();
$rank_count=get_post_count(get_post_author_id());
	if ($special_rank_val>0)
	{
	switch ($special_rank_val) {
		case 1 :
		$display_text = $path_to_subdirectory . $image_for_special_rank_1 . "' alt='#Posts:" . $rank_count . "' title='#Posts: " . $rank_count . "'";
		break;
		case 2 :
		$display_text = $path_to_subdirectory . $image_for_special_rank_2 . "' alt='#Posts: " . $rank_count . "' title='#Posts: " . $rank_count . "'";
		break;
                default :
                $display_text = $path_to_subdirectory . $image_for_special_rank_1 . "' alt='#Posts: " . $rank_count . "' title='#Posts: " . $rank_count . "'";
		}
	}
else
	{
for ($i=1;$i<$num_ranks;$i++)
		{
		 if ($rank_count<$rank_max[$i])
			{$display_text = $path_to_subdirectory . $rank_img[$i] . "' alt='#Posts: " . $rank_count . "' title='#Posts: " . $rank_count . "'";
                 break;
			}
			else
			{$display_text = $path_to_subdirectory . $rank_img[$num_ranks];
			} 	
		}
	}
$display_rank = "<img src='" . $display_text . " />";
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
