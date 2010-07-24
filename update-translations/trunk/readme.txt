=== Update Translations ===
Contributors: Nightgunner5
Donate link: http://nightgunner5.is-a-geek.net:1337/glotpress/projects/
Requires at least: 1.0
Tested up to: 1.1
Stable tag: 0.1

Download up-to-date plugin translations from Nightgunner5's Translation Station with the push of a button.

== Description ==
Download up-to-date plugin translations from Nightgunner5's Translation Station with the push of a button.

== Installation ==
= For Forum Owners =
Simply drop this plugin's folder into your bbPress installation's my-plugins folder.

= For Plugin Developers =
For a plugin called `My AWESOME plugin`, insert the following into your plugin's header before the `*/`:

    Text Domain: my-awesome-plugin
    Domain Path: /translations

(Of course, you'll need to change the text domain to fit your plugin.)

The text domain must match what you use when localizing, for example: `__( 'Some words', 'my-awesome-plugin' )`.

At the end of your plugin's main file, before the `?>` if there is one, add the following code:

    load_plugin_textdomain( 'my-awesome-plugin', dirname( __FILE__ ) . '/translations' );

Also, make sure that you follow the [WordPress/bbPress internationalization guidelines](http://codex.wordpress.org/I18n_for_WordPress_Developers).

== Frequently Asked Questions ==
= How does this work? Magic? Elves? =
Update Translations only works as long as there are volunteer translators to translate plugins. Specifically, Update Translations queries [Nightgunner5's Translation Station](http://nightgunner5.is-a-geek.net:1337/glotpress/projects/) for translation files.

== Changelog ==
= 0.1 =
* Initial release

== Upgrade Notice ==
= 0.1 =
Download up-to-date plugin translations with the push of a button.
