=== AutoRank ===
Contributors: Nightgunner5
Tags: ranking, ranks, score, post, count
Requires at least: 1.0
Tested up to: trunk
Stable tag: 0.1.1

Give users an automated score based on the posts they make.

== Description ==

Give users an automated score based on the posts they make.


== Installation ==

1. Do what you always do when you install a plugin.
2. Either configure the plugin in it's admin panel (most people should do this) or edit the plugin file (advanced users).

== Changelog ==

= 0.1.2 =
* Ranks can now be displayed before names instead of below names. Props [Adam](http://forums.llamaslayers.net/topic/autorank).

= 0.1.1 =
* You can now specify ranks that are achieved at specific scores.
* Post Count Plus importer

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.1 =
Initial release

== Frequently Asked Questions ==

= How is post score calculated? =

**TL;DR** Each post is scored based on where and how long it is.

First, we define a few constants, which can be changed in the admin page or in the code:
`
DEFAULT_SCORE       = 0.1
MODIFIER_FIRST      = 0.1
MODIFIER_WORD       = 0.02
MODIFIER_CHAR       = 0.0005
`
... and a few variables, which are computed immediately before the post's score is calculated:
`
CHARS          = [the number of alphanumeric characters in the post, not counting markup]
WORDS          = [the number of whitespace-separated strings of characters, not counting markup]
FIRST          = [1 if the post is the first in a topic, 0 otherwise]
MODIFIER_FORUM = [the forum-specific multiplier, or 1 if it is not set]
`
... then we solve this equation:
`
SCORE = (DEFAULT_SCORE +
         MODIFIER_FIRST * FIRST +
         MODIFIER_CHAR * ln(CHARS)^2 +
         MODIFIER_WORD * ln(WORDS)^2) *
         MODIFIER_FORUM
`
= How can I convert from Post Count Plus to AutoRank? =

* Set base score to 1.
* Set all bonuses to 0.
* Set the "Score:" text to "Posts:".
* Use the convert link from the top of the page to convert your Post Count Plus ranks.

== Screenshots ==

1. AutoRank fits seemlessly into any theme.
2. AutoRank has lots of options, so everyone can have exactly what they want.
