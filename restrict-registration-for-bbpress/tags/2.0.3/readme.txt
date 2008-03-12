=== Restrict registration for bbPress ===
Contributors: sambauers
Tags: registration user users whitelist blacklist
Requires at least: 0.8
Tested up to: 0.9
Stable tag: 2.0.3

Limits registration to email addresses from specific domains

== Description ==

When registering, a user usually must enter a valid email address,
this plugin allows the administrator to restrict the allowed email
addresses to a list of domains.

== Installation ==

Unzip the file and copy the restrict-registration.php file into the
my-plugins directory.

If the my-plugins directory is not present you need to create it under
the root directory of your forum.

Configuration can be carried out within the admin area of bbPress, you
will need to be logged in as a keymaster.

See the configuration section below for information of setting up
the whitelist and/or blacklist.

== Configuration ==

= How the Lists Work =
The whitelist and blacklist work based on a ranking system. The domain
is tested against all possible domains in both the white and the black lists.

= Whitelist Rank =
* If a whitelist exists then the domain starts with a rank of 0
* If no blacklist exists then the domain receives a rank of 1
* If a domain matches any wildcard entry then it receives a rank of 2
* If a domain matches an exact entry then it receives a rank of 3

= Blacklist Rank =
* If a blacklist exists then the domain starts with a rank of 0
* If no blacklist exists then the domain receives a rank of 1
* If a domain matches any wildcard entry then it receives a rank of 2
* If a domain matches an exact entry then it receives a rank of 3

= Resolving Ranks =
If the whitelist rank minus the black list rank is less than zero, then
it is not allowed. This effectively gives the whitelist precedence when
ranks are the same. The whitelist rank must also be at least 1.

= Example 1 =
whitelist = 'example.org, example.net, example.com'

blacklist = ''

Will allow only example.org, example.net and example.com domains.

= Example 2 =
whitelist = ''

blacklist = 'example.com, example.net'

Will allow anything but will deny example.com and example.net

= Example 3 =
whitelist = '*.example.org'

blacklist = 'internal.example.org'

Will allow only sub-domains of example.org but will deny the specific
internal.example.org sub-domain.

= Example 4 =
whitelist = '*.org, *.net, *.com'

blacklist = 'example.com, example.net'

Will allow only .org, .net and .com domains but will also deny
example.com and example.net

= Example 5 =
whitelist = 'example.org'

blacklist = '*.org'

Will allow example.org despite denying all .org domains

= Example 6 =
whitelist = 'example.org'

blacklist = 'example.org'

Will allow example.org as the whitelist takes precedence

= Example 7 =
whitelist = '*.*.org'

blacklist = '*.example.org'

Will allow subdomains of example.org as the whitelist takes
precedence and wildcard entries carry the same rank even when one
appears to be more 'correct'.

== License ==

Restrict registration for bbPress version 2.0.3
Copyright (C) 2007 Sam Bauers (sam@automattic.com)

Restrict registration for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 1.0 :
  <br/>Initial Release
* 2.0 :
  <br/>Complete re-write. Added blacklist functionality and admin
  page. Domains can now optionally be specified with wildcards
* 2.0.1 :
  <br/>Some comments cleanup
  <br/>Added support for bb_admin_add_submenu()
* 2.0.2 :
  <br/>Made PHP4 compatible
* 2.0.3 :
  <br/>Tested up to bbPress version 0.9
  <br/>Updated header and contact details