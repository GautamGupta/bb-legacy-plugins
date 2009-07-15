=== Instant Password  ===
Tags: register, registration, password, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows users to pick their own password during registration and log in immediately without checking email. No template edits required.

== Description ==

Allows users to pick their own password during registration and log in immediately without checking email. No template edits required.

When using this, it is highly recommended to also use the Human Test plugin to prevent instant spambot registration.

== Installation ==

* Install, activate, and test your registration page.  No edits required.

== Frequently Asked Questions ==

= How does this verify email addresses? =

* It doesn't, that's the whole point (and why some people requested the plugin). 

* Keep in mind that by allowing this password method, users may accidentally or purposely enter incorrect email addresses.

* But it's always been a security issue that passwords are emailed via plain text in many forum systems.

= Won't spammers also be able to instantly register ? =

* Install the Human Test plugin which will stop virtually all automated registrations and is compatible with Instant Password

= How do I change the successful registration behavior? =

* The default action of this plugin is to immediately login the new member and direct them back to the front-page (or ?re= value if present). 

* You can disable the redirect by commenting out this line like so: `// bb_safe_redirect(bb_get_option('uri'));` 

* customize your `register-success.php` template with a better welcome message.

* To prevent auto login entirely, comment out this line like so: `// add_action('register_user', 'instant_password_success');`

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2008-11-09) =

* Instant Password is born

= Version 0.0.2 (2008-11-09) =

* themed error pages
* validation & error detection via javascript before submit
* prevent username from being inside password

= Version 0.0.3 (2008-11-24) =

* workaround for buggy user_status change on 1st login in bbPress 0.9

= Version 0.0.4 (2008-12-13) =

* bbpress 1.0 compatibility fix for new bb_generate_password function


== To Do ==

* javascript checks of password for strength and other issues before form is submitted

* perhaps nicer error result back on registration page instead of crashing into bb_die

* optionally notify admin of failed registration

