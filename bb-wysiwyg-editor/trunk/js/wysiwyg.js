/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(function() {
    $('#post_content').wysiwyg({
        controls: {
            indent : { visible : true },
            outdent : { visible : true },
            insertHorizontalRule : { visible : false },
            removeFormat : { visible : false },
            separator09 : { visible : false }
        }
    });
});

function quote_user_click( action_url ) {
	$.get( action_url, function( quoted ) {
		var previous_content = $('#post_content').wysiwyg('getContent');
        $('#post_content').wysiwyg('setContent', quoted );
        document.location = '#post_content_anchor';
	});
}