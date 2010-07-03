=== Profanity Filter ===
Contributors: Nightgunner5
Tags: profanity, censor
Requires at least: 1.0
Tested up to: 1.1-alpha
Stable tag: 0.2

Changes profanity to stars, cartoon swears, or an administrator-specified word using the Double Metaphone algorithm.

== Description ==
Changes profanity to stars, cartoon swears, or an administrator-specified word using a modified version of the [Double Metaphone](http://en.wikipedia.org/wiki/Double_Metaphone) algorithm.

With the default settings of "cabbage" and "soup", a post such as:
>I really like **this** cubbage soop!  
>It has *so much ca*bage in its cabbajy goodness.
>
>I ate evrycabb4ge!

Would be shown as:
>I really like **this** #%$\*@!^ #%$@!  
>It has *so much %^%#!\** in its !^^$%^# goodness.
>
>I ate evry%@@$@^^!

Profanity Filter filters post contents, topic titles, and user names as they are displayed, but keeps the filtered and unfiltered forms, as well as the time it filtered the content, to reduce load on your server and produce updated content as soon as needed.

== Installation ==
1. Unzip the plugin's zip file and upload the contents to your forum's `my-plugins` folder.
2. Set up your list of profane words to search for on the settings page.

== Screenshots ==
1. The default settings for Profanity Filter protect you from cabbage soup, but not liquid soap.
2. Starting in Profanity Filter 0.2.1, you can share your settings with other installations of Profanity Filter.

== Changelog ==
= 0.2.1 =
* Profanity Filter can share settings using the new [Profanity Filter Communication API](http://nightgunner5.wordpress.com/profanity-filter-communication-api/).
* XMLRPC requests are now censored correctly.
* Tags are now censored.
* Minor changes in the phonetic matching algorithm:
  * Vowels are completely ignored.
  * The whitelist only matches whole words.

= 0.2 =
* Now over 300x faster!

= 0.1 =
* Initial release

== Upgrade Notice ==
= 0.2.1 =
Sharing, 

= 0.2 =
Now over 300x faster!

= 0.1 =
Initial release