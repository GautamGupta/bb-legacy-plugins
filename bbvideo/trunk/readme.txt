=== bbVideo ===
Tags: video, embed, embedding, embedded, multimedia, film, media, videos, youtube, flashvideo, flashvideos, vimeo, myvideo, clipfish, video download
Contributors: Naden Badalgogtapeh
Requires at least: 0.8.3
Tested up to: 0.8.3.1
Stable Tag: 0.8.3

Converts links pointing to videos to embeded players.

== Description ==

This plugin converts all links to knowen videos portals to the matching embedded players and supports the reader with easy sharing options and video downloading capabilities.

== Installation ==

Add `/bbvideo/` to your `/my-plugins/` directory.

== Configuration ==

not needed.

But! You can add the following snippet to 'topic.php' just below '<?php post_form(); ?>' to have a list of video providers displayed the poster can use.

<code>
global $BBPressPluginBBVideo;
if( isset( $BBPressPluginBBVideo ) && !is_null( $BBPressPluginBBVideo ) )
{
	print( _e( 'Supported video provider:' ) );
	$BBPressPluginBBVideo->DisplayProvider();
}
</code>

== Frequently Asked Questions ==

= Why are Yahoo-Video and many other famous portals not supported? =

Unfortunately there is not always a way to get the needed data such as the video id from the link.