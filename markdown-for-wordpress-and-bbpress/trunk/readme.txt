=== Markdown for WordPress and bbPress ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Plugin URI: http://mitcho.com/code/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=mitcho%40mitcho%2ecom&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: text, format, formatting, post, encoding
Requires at least: 0.9.0.2
Tested up to: 0.9.0.2
Stable tag: 1.0

A text-to-HTML conversion tool for web writers

== Description ==

[Markdown syntax](http://daringfireball.net/projects/markdown/syntax) allows you to write using an easy-to-read, easy-to-write plain text format.

Markdown for WordPress and bbPress (Markdown-WPBB) is based on the famed PHP Markdown Extra by [Michel Fortin](http://www.michelf.com/projects/php-markdown/), in turn based on the original Perl version by [John Gruber](http://www.daringfireball.net/). All I did was make the same package work with bbPress too. ^^

== Installation ==

= WordPress =

PHP Markdown works with WordPress, version 1.2 or later. PHP Markdown is already bundled with WordPress. Still, you can find here the latest version that may be newer than the latest WordPress version.

1.	To use Markdown-WPBB with WordPress, place the "markdown.php" file in the "plugins" folder. 
	This folder is located inside "wp-content" at the root of your site:

	`(site home)/wp-content/plugins/`
	
2.	Activate the plugin with the administrative interface of WordPress. In the "Plugins" section 
	you will now find "Markdown for WordPress and bbPress." To activate the plugin, click on the "Activate" button on the same line as Markdown. Your entries will now be formatted with Markdown.
3.	To post Markdown content, you'll first have to disable the "visual" editor in the User 
	section of WordPress.

You can configure Markdown-WPBB to not apply to the comments on your WordPress weblog. See the "FAQ" section below.

= bbPress =

1.	To use Markdown-WPBB with bbPress, place the "markdown.php" file in the "plugins" folder. 
	This folder is located inside "wp-content" at the root of your site:

	`(site home)/my-plugins/`

	If you don't have a `/my-plugins/` directory in your bbPress installaltion, create it on the 
	root level of your bbPress installation.
2.	Activate the plugin with the administrative interface of bbPress. Find "Markdown for 
	WordPress and bbPress" on the "Plugins" screen and hit "Activate."

== Frequently Asked Questions ==

= What if I do not need Markdown formatting for comments in WordPress? =

Michel Fortin says: "By default, the Markdown plugin applies to both posts and comments on your WordPress weblog. To deactivate one or the other, edit the MARKDOWN_WP_POSTS or MARKDOWN_WP_COMMENTS definitions under the "WordPress settings" header at the start of the "markdown.php" file."

= What if I do not want the "Extra" features of Markdown Extra? =

You can disable the "Extra" features by changing line 62 of `markdown.php`, replacing `MarkdownExtra_Parser` with `Markdown_Parser`. Voilˆ.

For more information on the "Extra" features, check out Michel Fortin's [Markdown Extra page](http://michelf.com/projects/php-markdown/extra/).