=== bbRatings ===
Tags: rating, rate, vote
Contributors: mdawaffe
Requires at least: 0.74
Tested up to: 0.74
Stable Tag: 0.7

Allows users to rate topics on a simple 1-5 star scale.

== Description ==

== Installation ==

Add `bb-ratings.php`, `bb-ratings.css`, `bb-ratings.js`, and `star.gif` to your `/my-plugins/` directory.

== Configuration ==

The plugin offers the following template tags for use in your templates.

1. `bb_rating()`: Displays the average rating for the current topic.
2. `bb_rating_count()`: Displays the number of ratings the current topic has received.
3. `bb_rating_dingus()`: Displays the line of stars that users click on to rate the current topic.
4. `bb_current_user_rating()`: Displays the rating the currently logged in user gave to the current topic.
5. `bb_user_rating()`: Displays the rating the current post's author gave to the topic if you use this in the post list.

The plugin alse provides the following potentially useful functions.

1. `bb_top_topics()`: Returns an array of the most highly rated topics.
2. `bb_get_user_rating()`: Returns the rating the post's author gave.
3. `bb_get_current_user_rating()`: Same as `bb_get_user_rating()` but for the currently logged in user. 

== Frequently Asked Questions ==

= How do I change the color of the stars? =

The color of the stars is set in the `bb-ratings.css` stylesheet file.  The yellow color comes from "`background-color: #fc0;`",
and the red color comes from "`background-color: #d00;`".  You can adjust these values to your taste.

= How do I change the background color of the stars? =

Unfortunately, the stars' background color is fixed by the actual image file (`star.gif`).  To change it, you will have to edit
that image with your favorite image editing program.  Replace all the white outer pixles with the color of your choice, leaving
the inner pixels as they are (those inner pixels should be transparent).

= How do I change the size of the stars? =

The size of the `star.gif` image file is 17px by 17px.  It is displayed at 16px by 16px to help round off the sharp edges
(an old Web 1.0 trick).

To change the size, find all the places in `bb-ratings.css` that say "`16px`" and change it to the size you want.

You will also need to Change the places in `bb-ratings.css`, `bb-ratings.php` *and* `bb-ratings.js` where it says "`85`" to be:
( the size you picked for your stars + 1 ) * 5

This should work reasonably well for making the stars smaller.  If you want to make the stars bigger, they may end up looking ugly.
If anyone can create better star images (SVG would be super cool), contact the author of this plugin.
