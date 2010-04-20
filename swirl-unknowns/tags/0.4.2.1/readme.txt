=== Swirl Unknowns ===
Contributors: mr_pelle
Tags: force, redirect, non-logged, users, visitors, Adams, Bauers
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Version: 0.4.2.1
Requires at least: 1.0.2
Tested up to: 1.0.2

Non-logged-in users get redirected to a page of your choice.

== Description ==

Non-logged-in users get redirected to a page of your choice.

Based on <a href="http://blogwaffe.com/">Michael D Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">*voodoo code from Trent Adams and Sam Bauers*</a>.

== Installation ==

* Copy plugin folder into `my-plugins` folder.

* Activate plugin and check under "Plugins" admin submenu for "Swirl Unknowns".

* Add the redirection page.

* You may also add another <em>allowed page</em> to the list. Visiting an allowed page doesn't get a user to be redirected.

== Frequently Asked Questions ==

= Can I add more than one <em>allowed page</em>? =

* Not at the moment. If anyone will request this feature, I'll try to add it.

== To Do ==

* option to add a list of <em>allowed pages</em>?

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

= Version 0.1 (2010-03-31) =

* first (non?-)working version

= Version 0.2 (2010-04-01) =

* corrected redirection thanks to *voodoo code from Trent Adams and Sam Bauers*

* minor fixes

= Version 0.3 (2010-04-06) =

* created version history

* created admin page

* added disable plugin input to admin page

* added `$default_swirl_page`

* added `$another_allowed_page`

* created standalone CSS

= Version 0.4 (2010-04-07) =

* plugin renamed to fit bbPress standards

* imported CSS the way it has to be done

* added `bb-admin/` to allowed pages

* added visual confirmations to admin page

* added another <em>allowed page</em> input to admin page

* changed paths to global ones: now the plugin can be exported!

* added licence

= Version 0.4.1 (2010-04-08) =

* minor CSS corrections

* several changes in how var are accessed to speed-up plugin

* added plugin URI on MediaFire via TinyURL

= Version 0.4.2 (2010-04-08) =

* little URI edit to make plugin work without name-based permalinks activated (damn, I should have checked this earlier!)

* updated admin page with more examples

* edited CSS URI to be retrieved from plugin filename

* added official bbPress Plugin Browser URI

= Version 0.4.2.1 (2010-04-20) =

* little corrections on admin page