/**
 * @package Support Forums
 */

/**
 * Call jQuery using a special handler to avoid namespace difficulties
 *
 * @link http://api.jquery.com/ready/ - "Aliasing the jQuery Namespace"
 */
jQuery(document).ready( function($) {
	// Check current status
	checkPosterCapabilities();

	$( '#support-forums-settings-poster-setable-0' ).click( function() { // Check status everytime poster setable checkbox changes
		checkPosterCapabilities();
	});


	/**
	 * Functions
	 */
	
	/**
	 * Disable poster-changeable checkbox if poster-setable one is not checked
	 *
	 * @return void
	 */
	function checkPosterCapabilities() {
		if ( $( '#support-forums-settings-poster-setable-0' ).is( ':checked' ) )
			$( '#support-forums-settings-poster-changeable-0' ).attr( 'disabled', false );
		else
			$( '#support-forums-settings-poster-changeable-0' ).attr( 'disabled', true );
	}
});