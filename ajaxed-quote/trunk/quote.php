<?php
/*
Plugin Name: Ajaxed Quote
Plugin URI: http://mirlo.cl/plugins/
Description: Quote posts using Ajax.
Author: Eduardo Graells
Author URI: http://mirlo.cl
Version: 1.2
License: GPLv3.
*/

/// Localization

define( 'AJAXED_QUOTE_VERSION', '1.1' );

add_action('bb_init', 'ajaxed_quote_initialize');

function ajaxed_quote_initialize() {
	load_plugin_textdomain( 'ajaxed-quote', BB_PLUGIN_DIR . 'ajaxed-quote' );
}

/// Internal function. Retrieves the given post, if the post exists, then it's returned inside a <blockquote>. Nested blockquotes are removed.

function bb_get_quoted_post($post_id) {
	$post = bb_get_post($post_id);
	if ( $post ) {
		$text = preg_replace( '/<blockquote>((.|[\n\r])*?)<\/blockquote>/', '',$post->post_text );
		$text = trim( bb_code_trick_reverse( $text ) ) . "\n";		
		$quoted = bb_get_user( $post->poster_id );
		$quotelink = get_post_link( $post->post_id );
		return sprintf( "<blockquote><cite>%s <a href=\"%s\">%s</a>:</cite>\n%s</blockquote>\n", get_user_display_name( $quoted->ID ), $quotelink, __('said', 'ajaxed-quote'), $text );
	}
	return false;
}

function bb_quote_link() {
	if ( !bb_is_topic() )
		return false;
		
	global $page, $topic, $bb_post;
	
	if ( !$topic || !topic_is_open( $bb_post->topic_id ) || !bb_is_user_logged_in() || !bb_current_user_can('write_posts') ) 
		return false;
	
	$post_id = get_post_id();
	
	$add = topic_pages_add();
	$last_page = get_page_number( $topic->topic_posts + $add );
	
	if ( $page == $last_page ) {
		$action_url = bb_nonce_url( BB_PLUGIN_URL . 'ajaxed-quote/quote.ajax.php', 'quote-' . $post_id );
		$action_url = add_query_arg( 'quoted', $post_id, $action_url ); 
		$link = '<a class="quote_link" href="#post_content" onClick="javascript:quote_user_click(\'' . $action_url . '\')">' . __('Quote', 'ajaxed-quote') . '</a>';
	} else {
		$quote_url = add_query_arg( 'quoted', $post_id, get_topic_link( 0, $last_page ) );
		$quote_url = bb_nonce_url( $quote_url, 'quote-' . $post_id );
		$link = '<a class="quote_link" href="'. $quote_url . '#postform" id="quote_' . $post_id . '">' . __('Quote', 'ajaxed-quote') . '</a>';

	}
	return apply_filters( 'bb_quote_link', $link );
}

/// from php.net/htmlspecialchars
function bb_quote_jschars( $str ) {
    $str = ereg_replace( "\\\\", "\\\\", $str );
    $str = ereg_replace( "\"", "\\\"", $str );
    $str = ereg_replace( "'", "\\'", $str );
    $str = ereg_replace( "\r\n", "\\n", $str );
    $str = ereg_replace( "\r", "\\n", $str );
    $str = ereg_replace( "\n", "\\n", $str );
    $str = ereg_replace( "\t", "\\t", $str );
    $str = ereg_replace( "<", "\\x3C", $str ); // for inclusion in HTML
    $str = ereg_replace( ">", "\\x3E", $str );
    return $str;
}

/// Prints JS header.

add_action('bb_init', 'bb_quote_print_js');
add_action('bb_head', 'bb_quote_header_js', 100);

function bb_quote_print_js() {
	if ( bb_is_topic() && bb_current_user_can('write_posts')  && !bb_is_topic_edit() ) {
		global $topic, $page;
		
		$add = topic_pages_add();
		$last_page = get_page_number( $topic->topic_posts + $add );
		
		if ( isset( $_GET['quoted'] ) )
			bb_check_admin_referer( 'quote-' . intval( $_GET['quoted'] ) );
			
		if ( $last_page != $page )
			return;
			
		bb_enqueue_script('jquery');
	}
}

function bb_quote_header_js() {
	if ( bb_is_topic() && bb_current_user_can('write_posts')  && !bb_is_topic_edit() ) {
		global $topic, $page;
		
		$add = topic_pages_add();
		$last_page = get_page_number( $topic->topic_posts + $add );
		
		if ( $page != $last_page )
			return;
		
		if ( isset( $_GET['quoted'] ) || intval($_GET['quoted']) > 0 ) {
			$quoted_post = bb_quote_jschars( bb_get_quoted_post( intval( $_GET['quoted'] ) ) );
			if ( empty( $quoted_post ) )
				return;
				
			printf( '<script type="text/javascript">var bb_quoted_post="%s";</script>', $quoted_post );
			
			$quote_script = 
"jQuery(document).ready(function(){
   jQuery(\"textarea#post_content\").val( bb_quoted_post );
});";
			printf( '<script type="text/javascript">%s</script>', $quote_script );
		}
			
		?> 
<script type="text/javascript"> 
function quote_user_click( action_url ) {
	jQuery.get( action_url, function( quoted ) {
		previous_content = jQuery("textarea#post_content").val();
		jQuery("textarea#post_content").val( previous_content + quoted );
	});
}
</script>
		<?php 
		
	}
}

/// Allows <cite> in quotes.

add_filter('bb_allowed_tags', 'bb_quote_tags');

function bb_quote_tags($tags) {
	$tags['cite'] = array();
	return $tags;
}

add_filter('bb_post_admin', 'bb_quote_post_link');
function bb_quote_post_link($post_links) {
        if ( $link = bb_quote_link() )
                $post_links[] = $link;
        return $post_links;
}
