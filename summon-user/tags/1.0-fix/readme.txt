=== Summon User ===
Tags: user, summon, notify
Contributors: thomasklaiber
Requires at least: 0.73
Tested up to: 0.74
Stable Tag: 1.0-fix

Allows you to summon a specified user to a topic.

== Description ==

Just adds a small Dropdown with all users to select one to notify him about this topic.

== Installation ==

Add `summon.php` to your `/my-plugins/` directory.

Add this behind the last `endif;` to your `/my-templates/post-form.php`:

`<p>
<label for="summon_user_id"><?php _e('Summon:'); ?>
<?php summon_user_dropdown(); ?>
</label>
</p>`

== Configuration ==

not needed.