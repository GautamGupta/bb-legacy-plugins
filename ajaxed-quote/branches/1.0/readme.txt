=== Ajaxed Quote ===
Tags: quote, reply, post, ajax
Contributors: Detective
Requires at least: 0.9.0.2
Tested up to: 0.9.0.2
Stable Tag: 1.0

Allows quoting of existing messages when replying. Based on the plugin by Michael Nolan (http://bbpress.org/plugins/topic/quote/).

== Description ==

Allows quoting of messages when replying. Based on the plugin by Michael Nolan (http://bbpress.org/plugins/topic/quote/).

* Using JS if the user is viewing the last page of a topic. In this way, a user can quote more than one message in a reply, because the quoted post is retrieved using AJAX. 
* Using the mechanism of the original plugin.

Among added features: the suggested ones in the comments of the original plugin, plus nonces, removal of nested blockquotes and localization.

== Installation ==

1. Upload the folder `ajaxed-quote` to your `/my-plugins/` directory.
1. Modify your `post.php` template to include the quote link, outputs "Quote" by default: `<?php bb_quote_link(); ?>`
1. Modify your `post-form.php` template to include the function `bb_quote_post`. Example: 
`<textarea name="post_content" cols="50" rows="8" id="post_content" tabindex="3">
<?php if (function_exists('bb_quote_post')) bb_quote_post(); ?>
</textarea>`
1. You're done :)

== Configuration ==

None necessary.

 