<?php 

/*YOU CAN CONFIGURE THESE VARIABLES */

$num_ranks=6; //Should be equal (or less) than the number of ranks you are going to display and have images for.

$use_special_rank = 1; // To use special ranks for 'Key master' and 'Moderators'. If you want to use the the rank by post for everyone set this to '0'

$rank_max=array();
$rank_max[1]=6; //Rank 1 will be for 1-5 posts.
$rank_max[2]=11;
$rank_max[3]=26;
$rank_max[4]=51;
$rank_max[5]=101;

$rank_img=array();
$rank_img[1]="metalic_rank1.png";
$rank_img[2]="metalic_rank2.png";
$rank_img[3]="metalic_rank3.png";
$rank_img[4]="metalic_rank4.png";
$rank_img[5]="metalic_rank5.png";
$rank_img[6]="metalic_rank6.png";

$image_for_special_rank_1="metalic_admin.png";  // Image used for Key Master when use_special_rank is set to '1'
$image_for_special_rank_2="metalic_modo.png"; // Image used for Moderators when use_special_rank is set to '1'

/*END OF CONFIGURABLE VARIABLES */
?>