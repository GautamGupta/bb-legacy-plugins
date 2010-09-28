/**
 * @package Support Forums
 */

/**
 * Call jQuery using a special handler to avoid namespace difficulties
 *
 * @link http://api.jquery.com/ready/ - "Aliasing the jQuery Namespace"
 */
jQuery(document).ready( function($) {
	// Check current statuses
	enableIfChecked( 'support-forums-settings-poster-changeable-0', 'support-forums-settings-poster-setable-0' );
	enableIfChecked( 'support-forums-remove-settings-0',            'support-forums-import-settings-0' );

	$( '#support-forums-settings-poster-setable-0' ).click( function() { // Check status everytime poster-setable checkbox changes
		enableIfChecked( 'support-forums-settings-poster-changeable-0', 'support-forums-settings-poster-setable-0' );
	});

	$( '#support-forums-import-settings-0' ).click( function() { // Check status everytime import-settings checkbox changes
		enableIfChecked( 'support-forums-remove-settings-0',            'support-forums-import-settings-0' );
	});


	/**
	 * Functions
	 */

	/**
	 * Enable former checkbox if the latter is checked
	 *
	 * @param id_to_enable   string The id of the object to enable
	 * @param id_to_check    string The id of the object to check to status of
	 *
	 * @returns void
	 */
	function enableIfChecked( id_to_enable, id_to_check ) {
		$( '#' + id_to_enable ).attr( 'disabled', function() {
			return ( !$( '#' + id_to_check ).is( ':checked' ) ); // I'm actually doing it the other way round, i.e. disable the former checkbox if the latter is not checked
		});
	}
});
