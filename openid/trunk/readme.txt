=== OpenID for bbPress ===
Tags: _ck_, OpenID
Contributors: _ck_
Requires at least: 0.9
Tested up to: 1.0 alpha 5
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Adds OpenID login support to bbPress so users may login using an identity from another provider. 

== Description ==

Adds OpenID login support to bbPress so users may login using an identity from another provider. 

Give your members the ability to add OpenID to their account instead of using passwords, and new members can register instantly via an OpenID provider.

Account creation now supported. Attempts to get along with Instant Password plugin.

== Installation ==

* This plugin requires CURL to be installed with SSL support on your server.
   Check your PHPINFO for a CURL section which should have the word OpenSSL listed.

* Add the entire `openid/` folder to bbPress' `my-plugins/` directory.

* Activate (there is no admin menu yet)

* There is a replacement `login-form.php` that you can replace in the default kakumei `login-form.php` and it should work okay on some other themes too. 

* Edit your `login.php` template and between the first H3 and H2 place `do_action('openid_login');`
`
ie.

<h3 class="bbcrumb">...

do_action('openid_login');

<h2 id="userlogin">...
`

* Test the following once the above is completed:

1. Login normally and go into your profile and edit.
2. Then you should see a new box for adding OpenID
3. Try the help link for suggestions on providers to enter
4. Add one or more OpenIDs
5. Logout
6. Then try the login form toggle and enter an OpenID, rather than your normal login.

== Frequently Asked Questions ==

= What is OpenID =

* http://en.wikipedia.org/wiki/OpenID

* http://openid.net/

= How do I modify my alternate login to handle OpenID ? =

* You can add an input field called `openid_url` to any form and the plugin will automatically pickup on any entry.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-11-01) =

* public alpha test, new account creation not yet supported

= Version 0.0.2 (2008-12-13) =

* Account creation now supported. Attempts to get along with Instant Password plugin.