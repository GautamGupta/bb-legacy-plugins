*** Allow HTML5 Video ***
Tags: video, html
Contributors: Jeremy Winter
Requires at least: 0.8.4
Tested up to: 1.0.2
Stable Tag: 0.9

Allow users to include HTML5 video tags in their posts.

-- Description --

Based off of mdawaffe's and qayqay12's Allow Images plugin. This plugin allows 
<video> and <source> elements to be posted in your forum. Here is the 
list of allowed tags and allowed attributes.

<video>
-src
-type
-autoplay
-poster
-controls
-width
-height

<source>
-src
-type
-media

-- Installation --

1. Add the `allow-html5-video.php` file to bbPress' `my-plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in bbPress.

*Make sure you have set up your webhost to allow the correct MIME types for OGG and OGV files.

MIME Type: video/ogg  
Extension(s): ogv ogg ogm

-- Frequently Asked Questions --

What are some example embed codes?

Example #1 (Works in most modern browsers)

<video src="test-video.ogv" controls>  
  Your browser does not support the <code>video</code> element.  
</video>

Example #2 (Should Work in all modern browsers)

<video controls>  
<source src="test-video.ogg" type="video/ogg">  
<source src="test-video.mp4"> 
Your browser does not support the <code>video</code> element.  
</video>

-- Changelog --

- 0.9 -
*initial release