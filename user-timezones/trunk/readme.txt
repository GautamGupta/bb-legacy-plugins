=== User Timezones ===
Tags: timezone
Contributors: mdawaffe
Requires at least: 0.8
Tested up to: 0.8
Stable Tag: trunk

Allows users to specify their own timezone.

== Description ==

This is sort of in "proof of concept" state right now.  Users can specify a timezone (such as -8 or +4.5) in their profile.
It does not include a more pleasant interface (like selecting 'PST' or 'Los Angeles, CA').

Any times filtered through `bb_offset_time()` (which include `bb_post_time()`, `topic_time()`, `topic_start_time()`) will be adjusted to
the user's timezone.

== Installation ==

Add `user-timezones.php` to your `/my-plugins/` directory.

== Configuration ==

None!
