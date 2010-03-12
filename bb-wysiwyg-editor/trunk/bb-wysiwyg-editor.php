<?php
/*
Plugin Name: BB Wysiwyg Editor
Plugin URI: http://www.saidmade.com/
Description: Add Wysiwyg Editor to Textarea
Author: Giovambattista Fazioli
Version: 1.1.1
Author URI: http://www.undolog.com/
*/


function bbwe_head() { ?>
<!-- BB Wysiwyg Editor -->
<link rel='stylesheet' href='<?php echo bb_get_option('url') ?>my-plugins/bb-wysiwyg-editor/js/jwysiwyg/jquery.wysiwyg.css' type='text/css' media='all' />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo bb_get_option('url') ?>my-plugins/bb-wysiwyg-editor/js/jwysiwyg/jquery.wysiwyg.js"></script>
<script type="text/javascript" src="<?php echo bb_get_option('url') ?>my-plugins/bb-wysiwyg-editor/js/wysiwyg.js"></script>
<!-- BB Wysiwyg Editor -->
<?php }

bb_add_action('bb_head', 'bbwe_head');


/*
function eelst_bb_post_text($content) {
    return "[$content]";
}

bb_add_filter('post_text', 'eelst_bb_post_text');
*/

function eelst_bb_allowed_tags( $tags ) {
    $tags['a']          = array('href' => array(), 'title' => array(), 'class' => array());
    $tags['img']        = array('src' => array(), 'title' => array(), 'alt' => array());
    $tags['b']          = array('style' => array(), 'class' => array());
    $tags['span']       = array('class' => array());
    $tags['div']        = array('style' => array(), 'class' => array(), 'align' => array() );
    $tags['p']          = array('style' => array(), 'class' => array(), 'align' => array() );
    $tags['i']          = array();
    $tags['u']          = array();
    $tags['s']          = array();
    $tags['strike']     = array();
    $tags['center']     = array();
    $tags['blockquote'] = array();
    $tags['cite']       = array();
    $tags['sub']        = array();
    $tags['sup']        = array();
    $tags['ol']         = array();
    $tags['ul']         = array();
    $tags['hr']         = array();
    $tags['br']         = array();
    $tags['h1']         = array();
    $tags['h2']         = array();
    $tags['h3']         = array();
    $tags['h4']         = array();
    $tags['h5']         = array();
    $tags['h6']         = array();
    
    return $tags;
}

add_filter( 'bb_allowed_tags', 'eelst_bb_allowed_tags' );


/**
 * Add Quote Function
 */

/// Internal function. Retrieves the given post, if the post exists, then it's returned inside a <blockquote>. Nested blockquotes are removed.
function bb_get_quoted_post($post_id) {
	$post = bb_get_post($post_id);
	if ( $post ) {
        $text = $post->post_text;
		//$text = preg_replace( '/<blockquote>((.|[\n\r])*?)<\/blockquote>/', '',$post->post_text );
		$text = trim( bb_code_trick_reverse( $text ) );
		$quoted = bb_get_user( $post->poster_id );
		$quotelink = get_post_link( $post->post_id );
		return sprintf( "<blockquote><cite>%s <a href=\"%s\">ha detto</a>:</cite> %s</blockquote> ", get_user_display_name( $quoted->ID ), $quotelink, $text );
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
		$action_url = bb_nonce_url( BB_PLUGIN_URL . 'bb-wysiwyg-editor/quote.ajax.php', 'quote-' . $post_id );
		$action_url = add_query_arg( 'quoted', $post_id, $action_url );
		$link = '<a class="quote_link" href="#post_content" onClick="javascript:quote_user_click(\'' . $action_url . '\')">Quota</a>';
	} else {
		$quote_url = add_query_arg( 'quoted', $post_id, get_topic_link( 0, $last_page ) );
		$quote_url = bb_nonce_url( $quote_url, 'quote-' . $post_id );
		$link = '<a class="quote_link" href="'. $quote_url . '#postform" id="quote_' . $post_id . '">Quota</a>';

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
"$(document).ready(function(){
   $(\"#post_content\").wysiwyg(\"setContent\", bb_quoted_post );
});";
			printf( '<script type="text/javascript">%s</script>', $quote_script );
		}
	}
}

add_filter('bb_post_admin', 'bb_quote_post_link');
function bb_quote_post_link($post_links) {
    if ( $link = bb_quote_link() ) {
        $post_links[] = $link;
    }
    return $post_links;
}

?>