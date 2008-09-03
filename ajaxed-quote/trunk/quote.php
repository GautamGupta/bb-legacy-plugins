<?php
/*
Plugin Name: Ajaxed Quote
Plugin URI: http://alumnos.dcc.uchile.cl/~egraells
Description: Quote message(s) when replying in an ajaxed way. Initially based on "Quote" by Michael Nolan.
Author: Eduardo Graells
Author URI: http://alumnos.dcc.uchile.cl/~egraells
Version: 1.0
License: GPLv3.
*/

/// Localization

add_action('bb_init', 'ajaxed_quote_initialize');

function ajaxed_quote_initialize() {
	load_plugin_textdomain('ajaxed-quote', BB_PLUGIN_DIR . 'ajaxed-quote');
}

/// Internal function. Retrieves the given post, if the post exists, then it's returned inside a <blockquote>. Nested blockquotes are removed.

function bb_get_quoted_post($post_id) {
	$post = bb_get_post($post_id);
	if ($post) {
		$text = preg_replace('/<blockquote>((.|[\n\r])*?)<\/blockquote>/', '',$post->post_text);
		$text = bb_code_trick_reverse($text);		
		$quoted = bb_get_user( $post->poster_id );
		$quotelink = get_post_link( $post->post_id );
		return sprintf("<blockquote><cite>%s <a href=\"%s\">%s</a>:</cite>\n%s</blockquote>\n", $quoted->user_login, $quotelink, __('said', 'ajaxed-quote'), $text);
	}
	return false;
}

/// Template tag. Put this in your textarea in your new post-form. 

function bb_quote_post() {
	if (!is_topic() || !bb_current_user_can('write_posts'))
		return;
	$post_id = (int) $_GET['quoted'];
	$quoted_post = bb_get_quoted_post($post_id);
	if ($quoted_post)
		echo $quoted_post;
}

/// Template tag. Put this in your post template, it will print a link to quote the current post.

function bb_quote_link() {
	if (!is_topic())
		return;
		
	global $page, $topic, $bb_post;
	
	if (!$topic || !topic_is_open( $bb_post->topic_id ) || !bb_is_user_logged_in() || !bb_current_user_can('write_posts')) 
		return;
	
	$post_id = get_post_id();
	$action_url = bb_nonce_url('quote.ajax.php', 'quote-' . $post_id);
	
	$add = topic_pages_add();
	$last_page = get_page_number($topic->topic_posts + $add);
	
	if ($page == $last_page)
		echo '<a href="#post_content" onClick="javascript:quote_user_click(\'' . $post_id . '\', \'' . $action_url . '\')">' . __('Quote', 'ajaxed-quote') . '</a>';
	else
		echo '<a href="'. get_topic_link(0, $last_page ) . '&quoted=' . $post_id . '#postform" id="quote_' . $post_id . '">' . __('Quote', 'ajaxed-quote') . '</a>'; 
}

/// Prints JS header.

add_action('bb_init', 'bb_quote_print_js');

function bb_quote_print_js() {
	if (is_topic() && bb_is_user_logged_in()) {
		bb_enqueue_script('sack', bb_get_option('uri') . 'my-plugins/ajaxed-quote/sack/tw-sack.js?ver=1.6.1');
		bb_enqueue_script('ajaxed-quote', bb_get_option('uri') . 'my-plugins/ajaxed-quote/quote.js.php?bloginfo=' . bb_get_option('uri'));
	}
}

/// Allows <cite> in quotes.

add_filter('bb_allowed_tags', 'bb_quote_tags');

function bb_quote_tags($tags) {
	$tags['cite'] = array();
	return $tags;
}

?>