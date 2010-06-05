<?php
/**
 * @package Facebook Like
 * @subpackage Public Section
 * @author Gautam Gupta (www.cyberfundu.com)
 * @link http://gaut.am/bbpress/plugins/facebook-like/
 */

function fblike_schema( $attr ) {
	$attr .= "\n xmlns:og=\"http://opengraphprotocol.org/schema/\""; 
	$attr .= "\n xmlns:fb=\"http://www.facebook.com/2008/fbml\""; 

	return $attr;
}

function fblike_header_meta() {
	global $fblike_plugopts;
    
	$fbid		= $fblike_plugopts['facebook_id'];
	$fbappid	= $fblike_plugopts['facebook_app_id'];
	$fbpageid	= $fblike_plugopts['facebook_page_id'];
	$image		= $fblike_plugopts['facebook_image'];
	$title		= htmlspecialchars( get_topic_title() );
	$title		= version_compare( phpversion(), 5, '>=' ) ? html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) : html_entity_decode( $title );
	
	if ( $fbid != null )
		echo '<meta property="fb:admins" content="' . $fbid . '" />'."\n";
	
	if ( $fbappid != null )
		echo '<meta property="fb:app_id" content="' . $fbappid . '" />'."\n";
	
	if ( $fbpageid != null )
		echo '<meta property="fb:page_id" content="' . $fbpageid . '" />'."\n";
	
	if ( $image != null )
		echo '<meta property="og:image" content="' . $image . '" />'."\n";
	
	echo '<meta property="og:site_name" content="' . htmlspecialchars( bb_get_option( 'name' ) ) . '" />' . "\n";	
	echo '<meta property="og:title" content="' . $title . '" />' . "\n";
	echo '<meta property="og:url" content="' . get_topic_link() . '" />' . "\n";
	
	if ( $description = trim( htmlspecialchars( $post->post_text ) ) )
		echo '<meta property="og:description" content="' . $description . '" />' . "\n";
    
	foreach( array( 'longitude', 'latitude', 'street_address', 'locality', 'region', 'postal_code', 'country', 'email', 'phone_number', 'fax_number' ) as $k )
		if ( $v = htmlspecialchars( trim( $fblike_plugopts[$k] ) ) )
			echo '<meta property="og:' . $k . '" content="' . $v . '" />' . "\n";
}

function fblike_footer() {
	global $fblike_plugopts;

	if( $fblike_plugopts['xfbml'] != true ) return;
	
	$appids = trim( $fblike_plugopts['facebook_app_id'] );
	$appids = explode( ',', $appids );
	
	if ( !count( $appids ) ) return;
	
	foreach( (array) $appids as $appid )
		if( !is_numeric( $appid ) )
			return;
	
	if( $fblike_plugopts['xfbml_async'] == true ) {
echo "<div id=\"fb-root\"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({appId: '$appid', status: true, cookie: true, xfbml: true});
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());
</script>
";
	} else {
echo '<div id="fb-root"></div>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script>
  window.fbAsyncInit = function() {
    FB.init({appId: \'' . $appid . '\', status: true, cookie: true, xfbml: true});
  };
</script>
';
	}
}

/**
 * Appends Like button under Topic title
 */
function fblike_button() {
	global $fblike_plugopts, $topic, $page;
    
	$purl		= get_topic_link();
	$button		= "\n<!-- Facebook Like Button v" . FBLIKE_VER . " BEGIN (By Gautam - http://gaut.am/) -->\n";
	$showfaces	= $fblike_plugopts['showfaces'] == true ? 'true' : 'false';
	$url		= urlencode( $purl );
	$align		= $fblike_plugopts['align'];
	$xfbml_font 	= '';
	
	$url = $url	. '&amp;layout=' 	. $fblike_plugopts['layout'] 
			. '&amp;show_faces='	. $showfaces
			. '&amp;width='		. $fblike_plugopts['width'] 
			. '&amp;action='	. $fblike_plugopts['verb'] 
			. '&amp;colorscheme='	. $fblike_plugopts['colorscheme']
	;
	
	if( $fblike_plugopts['font'] != '' ) {
		$url .= '&amp;font=' . urlencode( $fblike_plugopts['font'] );
		$xfbml_font = ' font="' . $fblike_plugopts['font'] . '"';
	}
	
	$margin	= $fblike_plugopts['margin_top']	. 'px '
		    . $fblike_plugopts['margin_right']	. 'px ' 
		    . $fblike_plugopts['margin_bottom']	. 'px '
		    . $fblike_plugopts['margin_left']	. 'px';
    
	if( $fblike_plugopts['xfbml'] == true )
		$button .= '<br /><fb:like href="' . $purl . '" layout="' . $fblike_plugopts['layout'] . '" show_faces="' . $showfaces . '" width="' . $fblike_plugopts['width'] . '" action="' . $fblike_plugopts['verb'] . '" colorscheme="' . $fblike_plugopts['colorscheme'] . '"' . $xfbml_font . '></fb:like>';
	else
		$button .= '<iframe src="http://www.facebook.com/plugins/like.php?href=' . $url . '" scrolling="no" frameborder="0" allowTransparency="true" style="border:none;overflow:hidden;width:' . $fblike_plugopts['width'] . 'px;height:' . $fblike_plugopts['height'] . 'px;align:' . $align.';margin:' . $margin . '"></iframe>';
    
	if( $align == 'right' )
		$button = '<div style="float:right;clear:both;text-align:right;">' . $button . '</div>';
    
	$button .= "\n<!-- Facebook Like Button END -->\n";
    
	echo $button;
}

add_filter( 'bb_language_attributes', 'fblike_schema' );

add_action( 'under_title', 'fblike_button' );
add_action( 'bb_head', 'fblike_header_meta' );
add_action( 'bb_foot', 'fblike_footer' );
