<?php
/*
Plugin Name: SourceCode Highlighter
Plugin URI: http://www.victorcuervo.com/mis-proyectos/sourcecode-highlighter/
Description: SourceCode HighLighter is a plugin developed for bbPress that highlight source code in a message. SourceCode HighLighter is developed usign Generic Syntax Highlighter (GeSHi).
Version: 1.0
Author: Victor Cuervo
Author URI: http://www.victorcuervo.com/

Copyright 2010  Victor Cuervo  (email : contacto_at_victorcuervo_dot_com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

include_once('geshi.php');


function sourcecode_highlighter_preg_callback($matches) {

	$lang = $matches[2];
	$line = $matches[4];
	$code = htmlspecialchars_decode($matches[5]);

	if ($lang != null) {

		$tabstop = 2;

		$geshi =& new GeSHi($code, $lang);
		// Las tab solo tienen sentido para DIV, no para pre
		$geshi->set_tab_width($tabstop);

		if ($line != null) {
			$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
			$geshi->start_line_numbers_at($line); 
		}
		return $geshi->parse_code();
	}

	return $matches[0];


}

function sourcecode_highlighter($content) {
	/* */
	$pattern = '/<pre(\s*lang=\'([^"]*)\')?(\s*lineno=\'([^"]*)\')?>((\\n|.)*)<\/pre>/U';
	$content = preg_replace_callback($pattern, 'sourcecode_highlighter_preg_callback', $content);
	return $content;
}

function sourcecode_header() {
	/* Insert CSS */
	$sourcecode_path = bb_get_option('uri')."bb-plugins/sourcecode-highlighter";
	$head = "<link rel=\"stylesheet\" href=\"".$sourcecode_path."/sourcecode-highlighter.css\" type=\"text/css\" media=\"all\" />\n";
	echo $head;
}

function sourcecode_allow_pre_tag( $tags ) {
	/* Add PRE to the allowed tags list with attibutes 'lang' and 'lineno'. */
	$tags['pre'] = array('lang' => array(), 'lineno' => array());
	return $tags;
}


/* Actions */
// Add the CSS file into the HEAD of the page
add_action('bb_head', 'sourcecode_header');

/* Filters */
// Transform the text to display the post
add_filter('post_text', 'sourcecode_highlighter');

// Disable HTML transformation when editing the POST
remove_filter('edit_text', 'htmlspecialchars');

// Allow PRE tag and PRE attributtes
add_filter( 'bb_allowed_tags', 'sourcecode_allow_pre_tag' );

?>