=== bbPress Attachments ===
Tags:  attachments, attachment, attach, uploads, upload, files, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Gives members the ability to upload attachments on their posts. This is an early beta release for review. Feedback, bug reports, feature ideas, improvements are encouraged. Please note there are important security considerations when allowing uploads of any kind to your server.

== Description ==

Gives members the ability to upload attachments on their posts. 
This is an early beta release for review. 
Feedback, bug reports, feature ideas, improvements are encouraged. 
Please note there are important security considerations when allowing uploads of any kind to your server.

== Installation ==

* make a directory `/bb-attachments/`  ABOVE your webroot ie.`/home/username/bb-attachments/` 
* `chmod 777` the above `/bb-attachments/` directory
* edit `edit-post.php` and below `<?php edit_form(); ?>` put `<?php if (function_exists('bb_attachments')) {bb_attachments();} ?>`
* install plugin in it's own bb-attachments directory in `my-plugins` then activate plugin 
* there are some optional settings you can adjust in `bb-attachments.php`
* default upload role setting is set to `moderate` in beta for security reasons, you can reduce to `participate` to allow members to test

== Frequently Asked Questions ==

* demo: http://bbshowcase.org/forums/topic/put-your-test-posts-here
* members's ability to upload attachments is tied to their ability to edit post - ie. if edit ends in 1 hour, so does adding attachments
* the plugin will try to create the base upload directory itself, but in most cases will fail so you need to follow the first installation step
* if available, posix is used to write files with owner's id so you can delete/move files manually via FTP
* needs PHP >= 4.3
* mime_content_type function or shell access must exist to verify mime types 
* filesize max might be 2mb because of passthrough/readfile limit (supposedly fixed in newer PHP)
* administrators can debug settings (ie. PHP upload limit) by adding to url `?bb_attachments_diagnostic`

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

* 0.0.5	first public beta release for review
* 0.0.6	advanced restrictions by file type & user role, upload form displays allowed file types
* 0.0.7	one more mime option for windows/no-shell-access users
	
== To Do ==

* map mime types to match extensions?
* check for file duplicates before saving
* thumbnails for image attachments
* serving images inline rather than just downloading
* pre-validate upload filenames via javascript to spare user upload time with rejection
* deal with attachments on new, unsaved posts - tricky but possible - will take time
* admin menu
