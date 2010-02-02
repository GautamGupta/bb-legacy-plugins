=== Transient Cleanup ===
Contributors: Nightgunner5
Tags: database, optimization, cleanup, transients
Requires at least: 1.0
Tested up to: trunk
Stable tag: 0.1

Clean up the transients that are used to store temporary data

== Description ==

Transient Cleanup does exactly what its name implies - It cleans up the transients that are used to store temporary data.

Simply put, bbPress uses meta options called transients to store temporary data. They are defined with an expiration date, but they don't ever get deleted by default.

Transient Cleanup deletes the expired transients from your database once per day at midnight in whatever timezone your forum is set up with. It also optimizes your meta table if it has over 10KB (by default) of overhead.

== Installation ==

1. Upload the entire `transient-cleanup` folder to the `my-plugins` directory of your bbPress installation.
2. Advanced users might want to modify the constants at the top of `transient-cleanup.php`
3. Activate the plugin in your administration panel.

== Changelog ==

= 0.1 =
* First public release