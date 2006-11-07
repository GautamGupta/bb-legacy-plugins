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
	rateAjax.addOnComplete( ajaxRateUpdate );
	rateAjax.options.parameters += '&action=rate-topic&id=' + id + '&rate=' + rate;
	rateAjax.request(rateAjax.url);
}

function ajaxRateUpdate( transport ) {
	var rate = transport.responseXML.getElementsByTagName('response_data')[0].firstChild.nodeValue;
	if ( !rate )
		return;
	$$( '.star-rating.select' ).each( function(i) {
		i.style.width = 85 * rate / 5 + 'px';
	} );
}
