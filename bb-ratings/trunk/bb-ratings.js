addLoadEvent( function() {
	$$( '.star.select' ).each( function(i) {
		i.onclick = ajaxRate;
	} );
} );

function ajaxRate( e ) {
	Event.stop(e);
	var params = Event.findElement(e, 'a').href.split('?')[1].toQueryParams();
	var id = params.id;
	if ( !id )
		id = topicId;
	var rate = params.rate;

	var rateAjax = new WPAjax( false, 'rate-response' );
	rateAjax.options.parameters += '&action=rate-topic&id=' + id + '&rate=' + rate;
	rateAjax.request(rateAjax.url);
	$$( '.star-rating.select' ).each( function(i) {
		i.style.width = 100 * rate / 5 + 'px';
	} );
}
