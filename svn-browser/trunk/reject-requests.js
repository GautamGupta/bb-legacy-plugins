jQuery( function($) {

	$(':radio').change( function() {
		var th = $(this);
		var box = th.parents( 'div:first' );
		if ( 2 == th.val() ) {
			if ( box.find( 'textarea' ).show().removeAttr( 'disabled' ).size() )
				return;
			var id = this.id.substring(0,this.id.length-2); // Could grab it from STRONG, but this is faster
			box.append('<p>Reject Reason (logged here and sent to user):<br /><textarea name="reject[' + id + ']" rows="5" cols="65"></textarea>');
		} else {
			box.find( 'textarea' ).hide().attr( 'disabled', true );
		}
	} );

} );
