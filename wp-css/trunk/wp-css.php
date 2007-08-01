<?php
/*
Plugin Name: WordPress Admin CSS
Plugin URI: http://box87.com/misc/wp-css
Description: Adds a css file to bbPress to make the administration page the same colors as WordPress.
Author: Michael Meyer
Author URI: http://box87.com
Version: 1.0.1
*/

function wp_css_admin_head() { ?>
<link rel="stylesheet" href="<?php echo bb_get_plugin_uri(); ?>wp-css/wp-css.css.php" type="text/css" />
<?php }
add_action( "bb_admin_head", "wp_css_admin_head" );
?>
