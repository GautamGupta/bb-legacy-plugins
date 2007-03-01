<?php
/*
Plugin Name: Fix bbPress
Plugin URI: http://bbpress.org/#
Description: Fixes the known bugs of the current release of bbPress.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.8.1-1
*/

function fix_bbpress_0_8_1() {

	if ( '0.8.1' != bb_get_option( 'version' ) )
		return;

	function fix_bb_autop($pee, $br = 1) { // Reduced to be faster
		$pee = $pee . "\n"; // just to make things a little easier, pad the end
		$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
		// Space things out a little
		$pee = preg_replace('!(<(?:ul|ol|li|blockquote|pre|p)[^>]*>)!', "\n$1", $pee); 
		$pee = preg_replace('!(</(?:ul|ol|li|blockquote|pre|p)>)!', "$1\n", $pee);
		$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines 
		$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
		$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end 
		$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace 
		$pee = preg_replace('!<p>\s*(</?(?:ul|ol|li|blockquote|p)[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
		$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
		$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
		$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
		$pee = preg_replace('!<p>\s*(</?(?:ul|ol|li|blockquote|p)[^>]*>)!', "$1", $pee);
		$pee = preg_replace('!(</?(?:ul|ol|li|blockquote|p)[^>]*>)\s*</p>!', "$1", $pee); 
		if ($br) $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
		$pee = preg_replace('!(</?(?:ul|ol|li|blockquote|p)[^>]*>)\s*<br />!', "$1", $pee);
		$pee = preg_replace('!<br />(\s*</?(?:p|li|ul|ol)>)!', '$1', $pee);
		if ( false !== strpos( $pee, '<pre' ) )
			$pee = preg_replace_callback('!(<pre.*?>)(.*?)</pre>!is', 'fix_bb_autop_pre', $pee);
		return $pee; 
	}

	function fix_bb_autop_pre( $matches ) {
		return $matches[1] . clean_pre($matches[2])  . '</pre>';
	}
	

	function fix_encodeit( $matches ) {
		$text = trim($matches[2]);
		$text = htmlspecialchars($text, ENT_QUOTES);
		$text = str_replace(array("\r\n", "\r"), "\n", $text);
		$text = preg_replace("|\n\n\n+|", "\n\n", $text);
		$text = str_replace('&amp;lt;', '&lt;', $text);
		$text = str_replace('&amp;gt;', '&gt;', $text);
		$text = "<code>$text</code>";
		if ( "`" != $matches[1] )
			$text = "<pre>$text</pre>";
		return $text;
	}

	function fix_decodeit( $matches ) {
		$text = $matches[2];
		$trans_table = array_flip(get_html_translation_table(HTML_ENTITIES));
		$text = strtr($text, $trans_table);
		$text = str_replace('<br />', '', $text);
		$text = str_replace('&#38;', '&', $text);
		$text = str_replace('&#39;', "'", $text);
		if ( '<pre><code>' == $matches[1] )
			$text = "\n$text\n";
		return "`$text`";
	}

	function fix_code_trick( $text ) {
		$text = str_replace(array("\r\n", "\r"), "\n", $text);
		$text = preg_replace_callback("|(`)(.*?)`|", 'fix_encodeit', $text);
		$text = preg_replace_callback("!(^|\n)`(.*?)`!s", 'fix_encodeit', $text);
		return $text;
	}

	function fix_code_trick_reverse( $text ) {
		$text = preg_replace_callback("!(<pre><code>|<code>)(.*?)(</code></pre>|</code>)!s", 'fix_decodeit', $text);
		$text = str_replace(array('<p>', '<br />'), '', $text);
		$text = str_replace('</p>', "\n", $text);
		return $text;
	}

	function fix_encode_bad( $text ) {
		$text = wp_specialchars( $text );
		$text = preg_replace('|&lt;br /&gt;|', '<br />', $text);
		foreach ( bb_allowed_tags() as $tag => $args ) {
			if ( 'br' == $tag )
				continue;
			if ( $args )
				$text = preg_replace("|&lt;(/?$tag.*?)&gt;|", '<$1>', $text);
			else
				$text = preg_replace("|&lt;(/?$tag)&gt;|", '<$1>', $text);
		}

		$text = fix_code_trick( $text );
		return $text;
	}

	function fix_bozo_profile_admin_keys( $a ) { 
		global $user; 
		$a['is_bozo'] = array(0, __('This user is a bozo')); 
		return $a; 
	}

	function fix_profile_edited( $user_id ) {
		$user = bb_get_user( $user_id );
		if ( $user->is_bozo )
			bozon( $user->ID );
		else
			fermion( $user->ID );
	}

	remove_filter( 'pre_post', 'encode_bad' );
	add_filter( 'pre_post', 'fix_encode_bad', 11 );
	remove_filter( 'pre_post', 'balanceTags' );
	add_filter( 'pre_post', 'balanceTags', 12 );
	remove_filter( 'pre_post', 'bb_autop', 60 );
	add_filter( 'pre_post', 'fix_bb_autop', 60 );

	remove_filter( 'edit_text', 'code_trick_reverse' );
	add_filter( 'edit_text', 'fix_code_trick_reverse', 9 );

	remove_filter( 'get_profile_admin_keys', 'bozo_profile_admin_keys' );
	add_filter( 'get_profile_admin_keys', 'fix_bozo_profile_admin_keys' );

	add_action( 'profile_edited', 'fix_profile_edited' );

}

add_action( 'bb_init', 'fix_bbpress_0_8_1' );

?>
