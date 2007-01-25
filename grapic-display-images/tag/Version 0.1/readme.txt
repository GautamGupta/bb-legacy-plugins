=== graphic-display-ranks ===
Tags: ranks
Contributors: actorjiml
Requires at least: 0.73
Tested up to: 0.75
Stable Tag: 0.1

== Description ==

Allows you to display an image based on the number of posts an author has made.

== Installation ==

Add `graphic-display-ranks.php`  to your `/my-plugins/` directory.
Create a subdirectory of your '/my-plugins/' directory called 'ranks'.
Add the rank images to the '/ranks/' subdirectory.

== Configuration ==

There are several changes you can make to the configuration. 

1. `$num_ranks': This sets the number of ranks you are going to use. It must not be greater than the number of images you have in the '/ranks/' subdirectory.
2. `$path_to_rank_images': You will only have to change this if you have the images stored somewhere other than the default location.
3. The variables in the '$rank_max[] array. It should be set to one more than the max you want for a particular rank. EXAMPLE: if you want rank 1 to be for posters with 1-5 posts set '$rank_max[1]=6;' You only have to set the n-1 ranks. Anything greater than the last one set will display the highest number image.
4. The variables in the '$rank_img[] array. One for each rank you are using. 



== Frequently Asked Questions ==

None yet.