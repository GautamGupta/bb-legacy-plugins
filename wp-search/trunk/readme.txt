=== WP Search ===
Contributors: so1o
Tags: wordpress, search
Requires at least: 0.73
Tested up to: 0.74
Stable tag: 1.0

Execute search for wordpress posts when you search in bbpress

== Description ==

This plugin searches the Wordpress Posts whenever a search is executed in BBPress. 
The template for the search will need to be modified to display the posts

== Installation ==

1. Upload the file into /my-plugins/ directory 
1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

== Frequently Asked Questions ==

== Screenshots ==

== Version History ==

* 1.0 : 
  <br/>Initial Release
* 1.1 : 
  <br/>bug fix for empty private forums
  <br/>Added failsafe for installation.
* 2.0 : 
  <br/>Added choice to hide private forums or show them with private prefix
  <br/>Added selectable prefix text
  <br/>Removed redundant forum\_access\_update\_option
* 2.1 :
  <br/>Created Common Submit for all options

== Function ==

* bb\_search\_wp(
  <br/>  $q // text that needs to be searched
  <br/>)
  <br/>bb\_search\_wp function performs the search on the wordpress tables and populates 
  <br/>the variable $wp\_posts.

* bb\_wp\_post\_link(
  <br/>  $row // row from the result set from the wordpress post search.
  <br/>)
  <br/>bb\_wp\_post\_link returns the link for the post with the settings in the config.php. 
  <br/>The text shown is the title of the post.

* bb\_wp\_search\_default\_display()
  <br/>bb\_wp\_search\_default\_display function displays the posts from the wordpress for 
  <br/>the default bbpress theme. this can be used as a reference to code custom templates. 