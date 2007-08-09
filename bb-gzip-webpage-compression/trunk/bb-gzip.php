<?php
/*
Plugin Name: bbPress Web Compress (bb-gzip)
Description:  compresses bbpress pages on the fly
Plugin URI:  http://bbpress.org/plugins/topic/66
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.02
*/
/*
License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Makes bbPress pages smaller and faster for all modern web browsers that support it and leaves others alone. This plugin is only for those that do not have compression available on their host.

== Description ==

This plugin is only for bbPress users on servers which have no other compression methods installed (mod_gzip, mod_deflate, etc.)
Please check to make sure your pages are NOT already compressed, ie. via http://www.pipeboost.com/report.asp or http://whatsmyip.org/mod_gzip_test/

bbPress Web Compress gzips output to all modern web browsers that support it and leaves others alone.
A 30k page can be reduced to as little as 5k which can be appreciated even on broadband and reduce your hosting bill if you are near your limit.
Your static files, css, javascript & images will not be compressed.

(some coders think this is as easy as setting ob_start("ob_gzhandler") but there are often missed checks and performance options)

== Instructions ==

Check to make sure web pages aren't already compressed, install, activate, analyze webpages again to see size/speed improvements.

== Version History ==

Version 0.01 (2007-08-06)
*   bb-gzip is born

Version 0.02 (2007-08-09)
*   a couple extra checks for special conditions added, chunked output if possible

*/

function bb_web_compress() {
if (extension_loaded('zlib')) :	// don't even bother unless the library is available
	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) :	// double check php's untrustworthy internal check for "gzip"		
		ini_set('zlib.output_compression_level', 1);	// needs php >4.3,  anything beyond 1 is  a completely waste of cpu cycles, 1 vs 5 vs 9 is a few hundred bytes 
		ob_start("ob_gzhandler",4096);		// start buffering and hook the callback for compression - buffer set to 4k for chunked output if server supports		
	endif;		
endif;
} 
add_action('bb_send_headers', 'bb_web_compress',50);

?>