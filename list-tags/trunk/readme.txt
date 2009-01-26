=== List Tags  ===
Tags: tag, tags list,autocompletion
Version: 1.0
Author: jmnieto
Requires at least: 0.8.2

Display a span list for click tags

== Description ==

Display a span list for click tags


== Installation ==

Add entire folder `listtags` to bbPress' `my-plugins/` directory and activate.

The list of tags show in new post, you can click to add.

To show in topic tags put <?php  if (function_exists('list_tags_form')) {list_tags_form();} ?>
in tag-form.php bottom of <input type="submit" name="Submit" id="tagformsub" value="<?php echo attribute_escape( __('Add &raquo;') ); ?>" />

The plugin read Tags from Wordpress and BBpress if you dont like tags of Wordpress change $integrateWP to false


== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/



== History ==

= Version 1.0 (2009-01-17) =

* first public release


