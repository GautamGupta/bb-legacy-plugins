=== Ignore Member ===
Tags:  block, ignore, hide, moderate
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk

Allows members to not see posts by other members that they don't get along with. They cannot block moderators or administrators.

== Description ==

This plugin adds a "Ignore" link to each post on a topic page  (once you edit post.php) 
so members can easily block all posts by any member they don't get along with.
Any ignored members will show up in their edit profile so they can remove them if they change their mind.

== Installation ==

1. install, activate 
2. put  <? ignore_member_link(); ?> in your post.php template where you want the "Ignore" link to be seen
3. optionally put in your theme stylesheet:    a.ignore_member {color:blue;}  
4. add any mods you wish to make unignorable to the array at the top of the plugin  ie. array("1","27","55");

== Technical Note ==
If you are using rewrite=slugs and mod_rewrite (not multiviews) 
there is a possibility for a user to ignore an entire topic that ends with a member they just ignored.
The one time that happens, bbpress redirects them to /topic/ (ending in blank) which the auto-generated rewrite rules don't support.
So the member will then get  get some weird result. To fix this, add something like
RewriteRule ^topic/$ /forums/ [L,QSA]
Where "forums" is the name of your bbpress root directory.
Again, the multiviews and non-slugs setups should (in theory) not be affected.

== Version History ==

Version 0.05 (2007-08-13)

* first public release

Version 0.06 (2007-08-20) 

* admin can now see users blocked in other member's profile, and remove block if desired

