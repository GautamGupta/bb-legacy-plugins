<?php
/*
Plugin Name: Open in New Window Plugin
Plugin URI: http://www.BlogsEye.com/
Description: Opens external links in a new window, keeping your blog page in the browser so you don't lose surfers to another site.
Version: 1.0
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/************************************************************
* 	kpg_bb_open_in_new_window_fixup()
*	Shows the javascript in the footer so that the links can be adjusted
*
*************************************************************/
function kpg_bb_open_in_new_window_fixup() {
?>
<script language="javascript" type="text/javascript">
	// <!--
	// open-in-new-window-plugin
	function kpg_bb_oinw_action(event) {
		try {
			var b=document.getElementsByTagName("a");
			for (var i = 0; i < b.length; i++) {
				// IE 6 bug - the anchor might not be a link and might not support hostname
				if (b[i] && b[i].href &&b[i].href.toLowerCase().indexOf('http://') != -1) {
					if (b[i].hostname && location.hostname) {
						if ((b[i].hostname != location.hostname)) {
							b[i].target="_blank";
						}
					} else {
						if (b[i].href && (b[i].href.indexOf('<?php echo $_SERVER['HTTP_HOST']; ?>') < 0)) { 					
							b[i].target="_blank";
						}
					}
				}
			}
		} catch (ee) {}
	}
	// set the onload event
	if (document.addEventListener) {
		document.addEventListener("DOMContentLoaded", function(event) { kpg_bb_oinw_action(event); }, false);
	} else if (window.attachEvent) {
		window.attachEvent("onload", function(event) { kpg_bb_oinw_action(event); });
	} else {
		var oldFunc = window.onload;
		window.onload = function() {
			if (oldFunc) {
				oldFunc();
			}
				kpg_bb_oinw_action('load');
			};
	}
	 
	// -->
</script>

<?php
}
  // Plugin added to bbPress plugin architecture
	add_action( 'bb_head', 'kpg_bb_open_in_new_window_fixup' );
	 
?>