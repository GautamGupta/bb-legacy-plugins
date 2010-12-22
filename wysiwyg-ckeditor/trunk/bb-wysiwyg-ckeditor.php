<?php
/*
Plugin Name: Wysiwyg CKEditor
Plugin URI: 
Description: Add Wysiwyg Editor to Textarea
Author: Alex Galashov
Version: 0.4
*/

function bbwe_head()
{
    ?><!-- BB Wysiwyg CKEditor --><?php
    if ( bb_is_topic() && bb_current_user_can('write_posts')  && !bb_is_topic_edit() )
    {
        global $topic, $page;

        $add = topic_pages_add();
        $last_page = get_page_number( $topic->topic_posts + $add );

        if ( $page == $last_page )
        {
            if ( isset( $_GET['quoted'] ) || intval($_GET['quoted']) > 0 )
            {
                $quoted_post = bb_quote_jschars( bb_get_quoted_post( intval( $_GET['quoted'] ) ) );
                if ( !empty( $quoted_post ) )
                    printf( '<script type="text/javascript">var bb_quoted_post="%s";</script>', $quoted_post );
            }
        }
    }
    else if(bb_get_location() == 'topic-edit-page') // when editing a topic
    {
        $quoted_post = bb_quote_jschars( get_post_text() );
        printf( '<script type="text/javascript">var bb_quoted_post="%s";</script>', $quoted_post );
    }

    ?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<?php
$dir = basename(dirname(dirname(__FILE__)));
?>
<script type="text/javascript" src="<?php echo bb_get_option('url').$dir ?>/bb-wysiwyg-ckeditor/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
var bb_wysiwyg_ckeditor_url = "<?php echo bb_get_option('url').$dir ?>/bb-wysiwyg-ckeditor/";

var bb_ckeditor_savedContents = '';
function EditorInit()
{
    //bb_ckeditor_savedContents = jQuery('#post_content').html();
    jQuery('#post_content').html(''); // for edit mode
    if (jQuery("#post_content").length > 0)
    {
        CKEDITOR.replace(
        'post_content',
        {
            skin : 'office2003',
            toolbar :
            [
                ['PasteText','-','Undo','Redo','-','Find','Replace'],
                ['Link','Unlink','Image','Smiley'],
                ['Bold','Italic','Underline','Strike','Blockquote','RemoveFormat'],
                ['NumberedList','BulletedList','JustifyLeft','JustifyRight']
            ],
            removePlugins : 'scayt,spellchecker',
            contentsCss : bb_wysiwyg_ckeditor_url+'text_editor.css'
            ,height:<?php if(bb_get_location() == 'topic-edit-page') echo ("'350px'"); else echo ("'250px'"); ?>
            //,extraPlugins : 'autogrow'
        });
    }
}

jQuery(document).ready(function(){
    if ( typeof( CKEDITOR ) == "undefined" )
        return;
    
    // custom smiles
    CKEDITOR.config.smiley_path='/smiles/'; // path to your smiles folder
    CKEDITOR.config.smiley_images =
    [
	'smile.gif','lol.gif','rofl.gif','crazy.gif','evil.gif','wall.gif','applause.gif','yahoo.gif','cool.gif',
        'sad.gif','pray.gif','O_O.gif','sos.gif','bad.gif','beer.gif','bomb.gif','angel.gif','wink.gif','shy.gif',
        'cry.gif','facepalm.gif','friends.gif','dance.gif','tongue.gif','boss.gif','heart.gif','bayan.gif','fly.gif',
        'tease.gif','kiss.gif','kissed.gif','fingers.gif','confused.gif','hi.gif','bye.gif'
    ];
    CKEDITOR.config.smiley_descriptions =
    [
        'Smile','Laughing','ROFL','crazy','Evil','Wall','Applause','Yahoo','Cool','Sad','Pray','O_O',
        'HELP','Bad','Beer','Bomb','Angel','Wink','Shy','Cry','Facepalm','Frieds','Dance','Tongue','Boss',
        'Heart','Bayan','Fly','Tease','Kiss','Kissed','\\m/','%)','Hi!','Bye!'
    ];
    // end custom smiles

    CKEDITOR.on( 'instanceReady', function( ev )
    {
        CKEDITOR.instances.post_content.execCommand('selectAll',null);
        if ( typeof( window[ 'bb_quoted_post' ] ) != "undefined" )
            CKEDITOR.instances.post_content.insertHtml(bb_quoted_post);
        else if (bb_ckeditor_savedContents != '')
            CKEDITOR.instances.post_content.insertHtml(bb_ckeditor_savedContents);
    });

    EditorInit();
});

function quote_user_click( action_url ) {
    jQuery.get( action_url, function( quoted ) {
        CKEDITOR.instances.post_content.insertHtml(quoted);
        document.location = '#post_content_anchor';
    });
}
</script>
<style type="text/css">
.cke_editor_post_content
{
    /* otherwise it will not work in IE if put inside layout with "margin:auto"*/
    position: relative;
}
</style>
<!-- BB Wysiwyg CKEditor -->
<?php }

bb_add_action('bb_head', 'bbwe_head');

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

/// Internal function. Retrieves the given post, if the post exists, then it's returned inside a <blockquote>. 
function bb_get_quoted_post($post_id) {
    $post = bb_get_post($post_id);
    if ( $post )
    {
        $text = $post->post_text;
        $quoted = bb_get_user( $post->poster_id );
        $quotelink = get_post_link( $post->post_id );
        return sprintf( "<blockquote><p><cite>%s <a href=\"%s\">wrote</a>:</cite>%s</p></blockquote><p></p>", get_user_display_name( $quoted->ID ), $quotelink, $text );
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
        $action_url = bb_nonce_url( BB_PLUGIN_URL . 'bb-wysiwyg-ckeditor/quote.ajax.php', 'quote-' . $post_id );
        $action_url = add_query_arg( 'quoted', $post_id, $action_url );
        $link = '<a class="quote_link" href="#post_content" onClick="javascript:quote_user_click(\'' . $action_url . '\')">Quote</a>';
    } else {
        $quote_url = add_query_arg( 'quoted', $post_id, get_topic_link( 0, $last_page ) );
        $quote_url = bb_nonce_url( $quote_url, 'quote-' . $post_id );
        $link = '<a class="quote_link" href="'. $quote_url . '#postform" id="quote_' . $post_id . '">Quote</a>';
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

// generate the "quote" link
function bb_quote_post_link($post_links) {
    if ( $link = bb_quote_link() ) {
        $post_links[] = $link;
    }
    return $post_links;
}
add_filter('bb_post_admin', 'bb_quote_post_link');

function wysiwyg_post_submitted($text)
{
    $text = preg_replace("/^(\\s*<p>\\s*&nbsp;\\s*<\/p>)+/",'',$text);
    $text = preg_replace("/(\\s*<p>\\s*&nbsp;\\s*<\/p>\\s*)+<\/blockquote>/",'</blockquote>',$text);
    return $text;
}
add_filter('pre_post', 'wysiwyg_post_submitted');

?>