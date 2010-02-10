/**
 * Javascript file for Admin Page
 * 
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/easy-mentions/
 */

jQuery(document).ready(function() {
	jQuery('#link-users-0').click(function() {
		if (jQuery(this).is(':checked')){
			jQuery("input[name='link-user-to']").removeAttr('disabled');
			jQuery("#option-link-user-to").removeClass('disabled');
			if (jQuery('#link-user-to-1').is(':checked')){
				jQuery("input[name='add-nofollow']").removeAttr('disabled');
				jQuery("#option-add-nofollow").removeClass('disabled');
			}
		}else{
			jQuery("input[name='link-user-to']").attr('disabled', 'disabled');
			jQuery("#option-link-user-to").addClass('disabled');
			jQuery("input[name='add-nofollow']").attr('disabled', 'disabled');
			jQuery("#option-add-nofollow").addClass('disabled');
		}
	});
	jQuery("input[name='link-user-to']").click(function() {
		if (jQuery('#link-user-to-1').is(':checked')){
			jQuery("input[name='add-nofollow']").removeAttr('disabled');
			jQuery("#option-add-nofollow").removeClass('disabled');
		}else{
			jQuery("input[name='add-nofollow']").attr('disabled', 'disabled');
			jQuery("#option-add-nofollow").addClass('disabled');
		}
	});
	jQuery('#reply-link-0').click(function() {
		if (jQuery(this).is(':checked')){
			jQuery("#reply-text").removeAttr('disabled');
			jQuery("#option-reply-text").removeClass('disabled');
		}else{
			jQuery("#reply-text").attr('disabled', 'disabled');
			jQuery("#option-reply-text").addClass('disabled');
		}
	});
	if (jQuery('#link-users-0').is(':not(:checked)')){
		jQuery("#option-link-user-to").addClass('disabled');
	}
	if (jQuery('#link-user-to-1').is(':not(:checked)')){
		jQuery("#option-add-nofollow").addClass('disabled');
	}
	if (jQuery('#reply-link-0').is(':not(:checked)')){
		jQuery("#option-reply-text").addClass('disabled');
	}
	jQuery('#reply-text').width(575);
});