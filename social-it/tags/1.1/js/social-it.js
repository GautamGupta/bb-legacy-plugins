/*
 Javascript file for Admin functions for
 Social It plugin (for bbPress) by www.gaut.am
*/

jQuery(document).ready(function() {
	if (jQuery('#iconator')) jQuery('#socialit-networks').sortable({ 
	delay:        250,
	cursor:      'move',
	scroll:       true,
	revert:       true, 
	opacity:      0.7
});
	if (jQuery('#social-it')) { jQuery('#socialit-sortables').sortable({ 
	handle:      '.box-mid-head',
	delay:        250,
	cursor:      'move',
	scroll:       true,
	revert:       true, 
	opacity:      0.7
});


jQuery('#clear-warning').css({ display:'none' });
if (jQuery('#autocenter-yes').is(':not(:checked)')){
	jQuery('#custom-warning').css({ display:'none' });
}

if (jQuery('#autocenter-yes').is(':checked')) {
	this.checked=jQuery('#xtrastyle').attr('disabled', true);
	this.checked=jQuery('#custom-warning').fadeIn('fast');
}
else {
	jQuery('#xtrastyle').removeAttr('disabled');
	this.checked=jQuery('#custom-warning').fadeOut();
}

jQuery('#autocenter-yes').click(function() {
	this.checked=jQuery('#xtrastyle').attr('disabled', true);
	this.checked=jQuery('#custom-warning').fadeIn('fast');
});

jQuery('#autocenter-no').click(function() {
	this.checked=jQuery('#xtrastyle').removeAttr('disabled');
	this.checked=jQuery('#custom-warning').fadeOut();
});



jQuery('.toggle').click(function(){
	var id = jQuery(this).attr('id');
	jQuery('#tog'+ id).slideToggle('slow');

	if (jQuery('#'+ id + ' img.close').is(':hidden')){
		jQuery('#'+ id +' img.close').show();
		jQuery('#'+ id +' img.open').fadeOut();
	} else {
		jQuery('#'+ id + ' img.open').show();
		jQuery('#'+ id + ' img.close').fadeOut();
	}
});

jQuery('#bgimg-yes').click(function() {
  jQuery('#bgimgs').toggleClass('hidden').toggleClass('');
});

// Apply "smart options" to Yahoo! Buzz
if (jQuery('#socialit-yahoobuzz').is(':checked')) {
	jQuery('#ybuzz-defaults').is(':visible');
}
else if (jQuery('#socialit-yahoobuzz').is(':not(:checked)')) {
	jQuery('#ybuzz-defaults').is(':hidden');
}
jQuery('#socialit-yahoobuzz').click(function() {
	if (this.checked) {
		this.checked=jQuery('#ybuzz-defaults').fadeIn('fast');
	}
	else {
		jQuery('#ybuzz-defaults').fadeOut();
	}
});

// Apply "smart options" to Twittley
if (jQuery('#socialit-twittley').is(':checked')) {
	jQuery('#twittley-defaults').is(':visible');
}
else if (jQuery('#socialit-twittley').is(':not(:checked)')) {
	jQuery('#twittley-defaults').is(':hidden');
}
jQuery('#socialit-twittley').click(function() {
	if (this.checked) {
		this.checked=jQuery('#twittley-defaults').fadeIn('fast');
	}
	else {
		jQuery('#twittley-defaults').fadeOut();
	}
});

// Apply "smart options" to Twitter
if (jQuery('#socialit-twitter').is(':checked')) {
	jQuery('#twitter-defaults').is(':visible');
}
else if (jQuery('#socialit-twitter').is(':not(:checked)')) {
	jQuery('#twitter-defaults').is(':hidden');
}
jQuery('#socialit-twitter').click(function() {
	if (this.checked) {
		this.checked=jQuery('#twitter-defaults').fadeIn('fast');
	}
	else {
		jQuery('#twitter-defaults').fadeOut();
	}
});

jQuery('.dtags-info').click(function() {
	jQuery('#tag-info').fadeIn('fast');
});

jQuery('.dtags-close').click(function() {
	jQuery('#tag-info').fadeOut();
});

jQuery('.sfp-info').click(function() {
	jQuery('#sfi-info').fadeIn('fast');
});

jQuery('.sfi-close').click(function() {
	jQuery('#sfi-info').fadeOut();
});

jQuery('#yourversion .del-x').click(function() {
	jQuery('#yourversion').fadeOut();
});

jQuery('div#message img.del-x').click(function() {
	jQuery('div#message').fadeOut();
});

jQuery('div#clearurl img.del-x').click(function() {
  jQuery('div#clearurl').fadeOut();
});


jQuery('#clearShortUrls').click(function() {
	if (jQuery('#clearShortUrls').is(':checked')) {
		this.checked=jQuery('#clear-warning').fadeIn('fast');
	}else{
		this.checked=jQuery(this).is(':not(:checked)');
	}
	this.checked=jQuery(this).is(':not(:checked)');
});

jQuery('#warn-cancel').click(function() {
	this.checked=jQuery('#clear-warning').fadeOut();
	this.checked=jQuery(this).is(':not(:checked)');
});

jQuery('#warn-yes').click(function() {
	this.checked=jQuery('#clear-warning').fadeOut();
	this.checked=jQuery('#clearShortUrls').attr('checked', 'checked');
	this.checked=jQuery(this).is(':not(:checked)');
});

}});