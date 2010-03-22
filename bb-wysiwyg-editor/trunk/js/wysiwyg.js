/**
 * BB Wysiwyg Editor Javascript Main File
 *
 * @package         bb-wysiwyg-editor
 * @subpackage      wysiwyg.js
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright (C) 2010 Saidmade Srl
 *
 */
$(function() {
    $('#post_content').wysiwyg({
        controls: {
            indent : { visible : true },
            outdent : { visible : true },
            insertHorizontalRule : { visible : false },
            removeFormat : { visible : false },
            separator09 : { visible : false }
        },
        css : bb_wysiwyg_editor_url + "bb-wysiwyg-editor.css"
    });
});

function quote_user_click( action_url ) {
	$.get( action_url, function( quoted ) {
		var previous_content = $('#post_content').wysiwyg('getContent');
        $('#post_content').wysiwyg('setContent', quoted );
        document.location = '#post_content_anchor';
	});
}