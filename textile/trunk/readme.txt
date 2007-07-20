=== Textile ===
Contributors: egypturnash
Tags: formatting, textile
Requires at least: 0.8.2.1
Tested up to: 0.8.2.1
Stable version: 0.1

Use Dean Allan's 'Textile' for formatting your posts.

== Description ==

This is a quick and dirty little wrapper for the Textile parser.

BBPress's existing HTML cleaners will be de-activated; Textile will be the only markup syntax available.

TO DO:
* wire up some admin-panel controls for Textile's options (is this possible yet, in bbpress' current state?)
* strip out all but an approved list of URL protocols from the result
* make sure turning off the existing input filters hasn't opened the door for any SQL injections
* consider storing the parsed HTML instead of the raw Textile; this would mean finding an HTML-to-Textile parser to run before a post is edited.
* add a pop-up window with full Textile syntax help

== Installation ==

1. Put textile.php and the 'textile' directory in the my-plugins directory of your BBPress installation.
2. Activate it via the site management/plugins screen of BBPress.
