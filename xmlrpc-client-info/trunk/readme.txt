=== XMLRPC Client Info ===
Contributors: master5o1
Tags: xmlrpc, client, info, xmlrpc client info, mobile, master5o1, 5o1
Requires at least: 1.0
Tested up to: 1.1

== Description ==

XMLRPC Client Info gets and displays client info about a post made through XMLRPC API.

I created this plugin for use by my [bbPress Mobile](http://master5o1.com/projects/bbpress-mobile/) project, a mobile interface to bbPress using the XMLRPC api.

This plugin receives the GET variable $client and $client_uri and stores them as meta data for the post that is being created.  It then allows the data to be displayed (e.g. in the post meta area of the theme) in the format "via Client Name."

To use, put the following into post.php of your template:

&lt;?php echo apply_filters( 'show_xmlrpc_client_info', '' ); ?&gt;

== Other Notes ==
* Inspired by Facebook's "via Facebook for Android."
* Created for use by [bbPress Mobile](http://master5o1.com/projects/bbpress-mobile/).

= License =
GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html

== Installation ==

1. Upload the extracted `xmlrpc-client-info` folder to the `/my-plugins/` directory
2. add <?php echo apply_filters( 'show_xmlrpc_client_info', '' ); ?> to somewhere in post.php of your template
3. Activate the plugin through the 'Plugins' menu in bbPress
4. Enjoy!

== Frequently Asked Questions ==

None

== Screenshots ==

Screenshots can be found [here](http://master5o1.com/projects/bbpress-mobile/).

== Changelog ==

None
