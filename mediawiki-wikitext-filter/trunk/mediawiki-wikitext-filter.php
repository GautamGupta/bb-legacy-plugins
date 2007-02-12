<?php
/*
Plugin Name: MediaWiki Markup for bbPress
Plugin URI: http://bbpress.org/forums/topic/713
Description: Add a subset of MediaWiki markups to bbPress
Version: 0.1
Author: Jason Godesky
Author URI: http://anthropik.com/
*/

/*  Copyright 2006  Jason Godesky  (email : jason@anthropik.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
These parameters specify the functioning of this plugin.
Edit accordingly for your specific situation.
*/

# Wiki root; "internal" links will point to the concatenation
# of this root and the link specified.
$mediawiki_filter_params["wiki"] = "http://en.wikipedia.org/wiki/";

/*
Stop editing; actual plugin functionality follows.
*/

function bb_mediawikitext($text) {
    global $mediawiki_filter_params;
    // BASIC FORMATTING
    // Bold and italic
    $text = preg_replace("|(\\\'\\\'\\\'\\\'\\\')(.*?)(\\\'\\\'\\\'\\\'\\\')|",
        "<strong><em>2</em></strong>", $text);
    // Bold
    $text = preg_replace("|(\\\'\\\'\\\')(.*?)(\\\'\\\'\\\')|",
        "<strong>2</strong>", $text);
    // Italic
    $text = preg_replace("|(\\\'\\\')(.*?)(\\\'\\\')|",
        "<em>2</em>", $text);

    // LINKS
    // Internal links with aliases
    $text = preg_replace("|([[)(.*?)|(.*?)(]])|",
        "<a href=".'"'.$mediawiki_filter_params["wiki"]."2".'"'.">3</a>",
        $text);
    // Internal links without aliases
    $text = preg_replace("|([[)(.*?)(]])|",
        "<a href=".'"'.$mediawiki_filter_params["wiki"]."2".'"'.">2</a>",
        $text);
    // External links with descriptions
    $text = preg_replace("|([)(.*?) (.*?)(])|",
        "<a href=".'"'."2".'"'.">3</a>", $text);
    // External links with no description
    $count   = 1;
    $replace = TRUE;
    while ($replace) {
        $before = $text;
        $text = preg_replace("|([)(.*?)(])|",
            "<a href=".'"'."2".'"'.">[".$count."]</a>",
            $text, 1);
        if ($before==$text) { $replace = FALSE; }
        $count++;
    }

    // HEADINGS
    $text = preg_replace("|(======)(.*?)(======)|",
        "<h6>2</h6>", $text);
    $text = preg_replace("|(=====)(.*?)(=====)|",
        "<h5>2</h5>", $text);
    $text = preg_replace("|(====)(.*?)(====)|",
        "<h4>2</h4>", $text);
    $text = preg_replace("|(===)(.*?)(===)|",
        "<h3>2</h3>", $text);
    $text = preg_replace("|(==)(.*?)(==)|",
        "<h2>2</h2>", $text);

    // RETURN
    return $text;
}

add_filter('pre_post', 'bb_mediawikitext', 1);

?>