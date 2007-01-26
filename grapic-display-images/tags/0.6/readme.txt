=== graphic-display-ranks ===
Tags: ranks
Contributors: actorjiml
Requires at least: 0.73
Tested up to: 0.75
Stable Tag: 0.6

== Description ==

Allows you to display an image based on the number of posts an author has made.

== Installation ==

Add `graphic-display-ranks.php`  to your `/my-plugins/` directory.
Create a subdirectory in your '/my-plugins/' directory called 'ranks'.
Add the rank images to the '/ranks/' subdirectory.
Add the gdr_config.php file to the '/ranks/' subdirectory. IMPORTANT: Be sure that the config file is in the subdirectory, NOT in the my-plugins directory.

== Configuration ==

There are several changes you can make to the configuration (located in gdr_config.php). 

1. `$num_ranks': This sets the number of ranks you are going to use. It must not be greater than the number of images you have in the '/ranks/' subdirectory.
2. `$path_to_rank_images': You will only have to change this if you have the images stored somewhere other than the default location.
3. The variables in the '$rank_max[] array. It should be set to one more than the max you want for a particular rank. EXAMPLE: if you want rank 1 to be for posters with 1-5 posts set '$rank_max[1]=6;' You only have to set the n-1 ranks. Anything greater than the last one set will display the highest number image.
4. The variables in the '$rank_img[] array. One image for each rank you are using.
5. '$use_special_rank': Set to 1 if you want to display a special image for "Key Master" and "Moderator"
Set to 0 if you want everyone to use the number of posts to select the image displayed.
6. If you are using the special rank you must set the name of the images.


== Frequently Asked Questions ==

None yet.

== Chage Log ==

-- 0.5 --

Added the ability to use a special rank for "Key Master" and "Moderator"
Added "#Posts: {#}" to the alt and title tags.
Cleaned up some extranious code.

-- 0.6 --
Changed "path_to_rank_images" to "path_to_subdirectory". 
Moved configurable variables to gdr_config.php in subdirectory. 
Added second set of rank images that are smaller than the default threadauthor width setting and made them the default set.
Uploaded revised zip file to Plugin URI.