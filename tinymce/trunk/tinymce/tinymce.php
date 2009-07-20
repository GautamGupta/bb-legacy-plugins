<?php
/*
Plugin Name: Tiny MCE
Plugin URI: http://bbpress.org/#
Description: Simple packaging of the amazing Tiny MCE tool
Author: Nicolas Grasset
Author URI: http://www.longwindyroad.com
Version: 3.2.5

Packaged with Tiny MCE 3.2.5
Tiny MCE license: GNU LESSER GENERAL PUBLIC LICENSE
http://tinymce.moxiecode.com


*/

add_action('bb_head', 'tinymce_head', 7);
add_filter('bb_allowed_tags', 'tinymce_extra_tags' );

function tinymce_head()
{
	?>
	<script language="javascript" type="text/javascript" src="<?php echo bb_get_uri(); ?>my-plugins/tinymce/tiny_mce/tiny_mce.js"></script>
	<script language="javascript" type="text/javascript">
	tinyMCE.init({
	mode : "textareas"
	});
	</script>
	<?php
}


function tinymce_extra_tags( $tags ) 
{
	$tags['p'] = array('style' => array());
	$tags['span'] = array('style' => array());
	return $tags;
}

?>