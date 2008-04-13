<?php

/****************************
 * Tracker Component Config *
 ****************************/

/* AUTOMATTIC_SVN_TRACKER__MASTER_SERVER (optional)
 * If running bbPress_Plugin_SVN_Tracker on multiple servers, you should set
 * this constant to the `uname -n` of the master server.  Otherwise, your DB
 * servers will do *way* too much work.  If not set, all servers will behave
 * like a master.
 */

//define( 'AUTOMATTIC_SVN_TRACKER__MASTER_SERVER', [the `uname -n` of the master svn tracker server] );

/* BBPRESS_PLUGIN_SVN_TRACKER__SVN_URL (required)
 * The full URL (with trailing slash) of the svn repository root.
 */

define( 'BBPRESS_SVN_TRACKER__SVN_URL', 'http://example.com/' );

/* AUTOMATTIC_SVN_TRACKER__DOWNLOAD_PATH (required)
 * The absolute path (with trailing slash) of the path zip files will be
 * created in for download.  It must be writable by the user running the svn
 * tracker processes.
 */

define( 'AUTOMATTIC_SVN_TRACKER__DOWNLOAD_PATH', dirname( __FILE__ ) . '/downloads/' );

/* AUTOMATTIC_SVN_TRACKER__DOWNLOAD_URL (required)
 * The full URL of the above downloads directory (with trailing slash).
 * or false = topic_url/file.zip
 */

define( 'AUTOMATTIC_SVN_TRACKER__DOWNLOAD_URL', bb_get_option( 'uri' ) . '/my-plugins/svn-tracker/downloads/' );

/* AUTOMATTIC_SVN_TRACKER__TRAC_URL (optional)
 * The full URL of the Trac installation for the above SVN URL
 */

define( 'AUTOMATTIC_SVN_TRACKER__TRAC_URL', false );

/* BBPRESS_SVN_TRACKER__LIVE_FORUM (required)
 * The forum id # of the forum into which the svn data will be saved.
 * Nothing else maoy go into this forum.
 * The forum must be set up ahead of time.
 */

define( 'BBPRESS_SVN_TRACKER__LIVE_FORUM',     1 );

/* BBPRESS_SVN_TRACKER__TRACKER_CLASS (optional)
 * You may specify a custom class that extends bbPress_SVN_Tracker here.
 */

//define( 'BBPRESS_SVN_TRACKER__TRACKER_CLASS', 'My_bbPress_SVN_Tracker' );



/*************************************
 * Admin Component Config (optional) *
 *************************************/

/* BBPRESS_SVN_TRACKER__ADMIN_CLASS (requried if you want to use the admin component)
 * Should be 'bbPress_SVN_Admin' or a custom class of yours that extends that class.
 */

//define( 'BBPRESS_SVN_TRACKER__ADMIN_CLASS', 'bbPress_SVN_Admin' );

/* AUTOMATTIC_SVN_TRACKER__SVN_USER (required if you want to use the admin compontent)
 * User login of a user with full svn access to the repositories.  This user
 * will also be used to own topics/posts whose owners can't be identified (a
 * very rare occurrance).
 */

define( 'AUTOMATTIC_SVN_TRACKER__SVN_USER', false );

/* AUTOMATTIC_SVN_TRACKER__SVN_PASSWORD (required required if you want to use the admin component)
 * The password of the above user.
 */

define( 'AUTOMATTIC_SVN_TRACKER__SVN_PASSWORD', false );

/* BBPRESS_SVN_ADMIN__SVN_ACCESS_FILE (required if you want to use the admin component)
 * The full path of the file to which the svn access conf will be written
 */

define( 'BBPRESS_SVN_ADMIN__SVN_ACCESS_FILE', false );

/* BBPRESS_SVN_ADMIN__*_FORUM (required if you want to use the admin component)
 * These are the forum id #s of the various forums the svn tracker admin component
 * uses to keep track of repo requests.  They must be set up ahead of time.
 */

define( 'BBPRESS_SVN_ADMIN__REQUESTS_FORUM', 2 );
define( 'BBPRESS_SVN_ADMIN__APPROVED_FORUM', 3 );
define( 'BBPRESS_SVN_ADMIN__REJECTED_FORUM', 4 );

/* BBPRESS_SVN_ADMIN__APPROVED_MESS (required if you want to use the admin component)
 * form email sent when plugin request is approved.
 * %%USER%%, %%SVN_URL%%, %%TRAC_URL%%, %%SLUG%%
 */

define( 'BBPRESS_SVN_ADMIN__APPROVED_MESS', 
"%%USER%%,

Your plugin hosting request has been aproved.

Within one hour, you will have access to your SVN repository at

%%SVN_URL%%%%SLUG%%/

with your username and password.

Enjoy!"
);

/* BBPRESS_SVN_ADMIN__NOTIFY_EMAIL (optional)
 * Send an email message to this email address when a request is made.
 * Separate mulitple emails with ", ".
 */

define( 'BBPRESS_SVN_ADMIN__NOTIFY_EMAIL', false );

/***********************
 * Server Paths Config *
 ***********************/

/* AUTOMATTIC_PATHS__TEMP_DIR (optional)
 * The working directory in which various functions create temporary files and
 * directories.  If not set, the system's temporary directory will probably be
 * used (if PHP can determine what the directory is).  Should be writable by
 * whatever user runs the svn tracker processes.  Everything created in by the
 * svn tracker will be automatically deleted.
 */

//define( 'AUTOMATTIC_PATHS__TEMP_DIR', [writable working directory] );

/* AUTOMATTIC_SVN__SVN_PATH (optional)
 * Path to the svn binary.  If not defined, `which svn` will be used.
 */

//define( 'AUTOMATTIC_SVN__SVN_PATH', '/usr/bin/svn' );

/* AUTOMATTIC_PATHS__FIND_PATH  (optional)
 * AUTOMATTIC_PATHS__GREP_PATH  (optional)
 * Path to the respective binaries.  If not defined, 'which [binary]` will be used.
 */

//define( 'AUTOMATTIC_PATHS__FIND_PATH',  '/usr/bin/find'  );
//define( 'AUTOMATTIC_PATHS__GREP_PATH',  '/usr/bin/grep'  );

/* AUTOMATTIC_SVN_TRACKER__ZIPLIB_PATH (optional)
 * Path to zip-lib.php defining the Ziplib class.  If not defined, will default
 * to: this-directory/zip-lib.php
 */

//define( 'AUTOMATTIC_SVN_TRACKER__ZIPLIB_PATH', dirname( __FILE__ ) . '/zip-lib.php' );

/* AUTOMATTIC_README__PATH (optional)
 * Path to parse-readme.php defining the Automattic_Readme class.  If not
 * defined, the svn tracker will look for it in the readme-parser/ directory:
 * this-directory/readme-parser/parse-readme.php
 */

//define( 'AUTOMATTIC_README__PATH', dirname( __FILE__ ) . '/readme-parser/parse-readme.php' );

/* AUTOMATTIC_README_MARKDOWN (optional)
 * Path to markdown.php defining the Markdown() function.  If not defined, the readme parser will look for markdown.php in its directory.
 */

//define( 'AUTOMATTIC_README_MARKDOWN', dirname( AUTOMATTIC_README__PATH ) . '/markdown.php' );

?>
