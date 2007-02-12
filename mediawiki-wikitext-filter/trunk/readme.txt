=== bbRatings ===
Tags: mediawiki, wikitext, filter
Contributors: jefgodesky
Requires at least: 0.8
Tested up to: 0.8
Stable Tag: 0.8

Filters posts and applies basic wikitext markup from MediaWiki

== Description ==

== Installation ==

Add `mediawiki-wikitext-filter.php` to your `/my-plugins/` directory.

== Configuration ==

At present, there is only one configuration option to change, on line 35: $mediawiki_filter_params["wiki"].  This establishes the wiki you wish to use for "internal" links.  "Internal" links work by concatenating the link onto the end of this parameter.  The default value, "http://en.wikipedia.org/wiki/", will point to the English Wikipedia.

== Frequently Asked Questions ==

= What markup is available? =

The color of the stars is set in the `bb-ratings.css` stylesheet file.  The yellow color comes from "`background-color: #fc0;`",
and the red color comes from "`background-color: #d00;`".  You can adjust these values to your taste.

1. Basic formatting: Bolding and Italicizing
2.  Basic internal links.  Aliases work, but this does not include template transclusion, image embedding, or anything fancy like that.
3. External links.  External links without descriptions are numbered, and descriptions are displayed.

= Does it support lists?  Tables of contents? =

No, but it would really be great if it did, so by all means give it a go.  That's the glory of open source, right?
