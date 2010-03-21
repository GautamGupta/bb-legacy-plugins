/**
 * Profile Edit Javascript File (uncompressed)
 * Compressed output is echoed on the profile edit page
 *
 * @package After the Deadline bbPress Plugin
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/**
 * Compression is done by http://closure-compiler.appspot.com/home
 * Method - Go there, paste this javascript there and compress with simple method (Advanced causes errors!)
 */

function atd_show_phrases( ignored )
{
	var element = jQuery( '#atd_ignores' ).get( 0 );
	var items   = new Array();
	ignored.sort();
	for ( var i = 0; i < ignored.length; i++ ) {
		if ( ignored[i].length > 0 )
			items.push( '<span id="atd_' + i + '"><a class="ntdelbutton" href="javascript:atd_unignore(\'' + ignored[i].replace("'", "\\'") + '\')">X</a>&nbsp;' + ignored[i] + '</span>' );
	}
	element.innerHTML = items.length >= 1 ? items.join("<br>") : '';
}
function atd_unignore( phrase, eid ) {
	/* get the ignored values and remove the unwanted phrase */
	var ignored = jQuery( '#atd_ignored_phrases' ).val().split( /,/g );
	ignored = jQuery.map(ignored, function(value, index) { return value == phrase ? null : value; });
	jQuery( '#atd_ignored_phrases' ).val( ignored.join(',') );
	/* update the UI */
	atd_show_phrases( ignored );
	/* show a nifty message to the user */
	jQuery( '#atd_message' ).show();
}

function atd_ignore () {
	/* get the ignored values and update the hidden field */
	var ignored = jQuery( '#atd_ignored_phrases' ).val().split( /,/g );
	jQuery.map(jQuery( '#atd_add_ignore' ).val().split(/,\s*/g), function(value, index) { ignored.push(value); });
	jQuery( '#atd_ignored_phrases' ).val( ignored.join(',') );
	/* update the UI */
	atd_show_phrases( ignored );
	jQuery( '#atd_add_ignore' ).val('');
	/* show that nifteroo messaroo to the useroo */
	jQuery( '#atd_message' ).show();
}

function atd_ignore_init() {
	jQuery( '#atd_message' ).hide();
	atd_show_phrases( jQuery( '#atd_ignored_phrases' ).val().split( /,/g ) );
}

/* document.ready() does not execute in IE6 unless it's at the bottom of the page. oi! */
if (navigator.appName == 'Microsoft Internet Explorer')
	setTimeout( atd_ignore_init, 2500 );
else
	jQuery( document ).ready( atd_ignore_init );