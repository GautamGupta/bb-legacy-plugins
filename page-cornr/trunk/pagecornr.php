<?php
/*
Plugin Name: Page Cornr
Plugin URI: http://bbpress.org/plugins/topic/page-cornr/
Description: Adds a page peel in the corner of the site... with jQuery! no Flash :D (Original WP plugin by Luxiano)
Version: 0.3.6
Author: Tomcraft1980
Author URI: http://www.xtc-modified.org
*/

	// for version control and installation
	define('pagecornr_VERSION', '0.3.6');

  // detect the plugin path
	$pagecornr_path = bb_get_option('uri').'my-plugins/page-cornr'; //don't change
	$pagecornr_img_corner = $pagecornr_path.'/images/corner.png';

  //force WordPress to include jQuery
  add_action( 'wp_print_scripts', 'pagecornr_add_jquery' );
  function pagecornr_add_jquery( ) {
    bb_enqueue_script( 'jquery' );
  }


	function pagecornr_addjs() {
    pagecornr_add_jquery();
		global $pagecornr_path;
		echo '<script type="text/javascript" src="' . $pagecornr_path . '/pagecornr.js"></script>' . "\n";
?>

<!--[if lt IE 7.]>
<style type="text/css">
img, div, a { behavior: url(<?php echo $pagecornr_path.'/iepngfix.htc'; ?>) }
</style>
<![endif]-->
		
<!-- Pagecornr styles -->
<link rel="stylesheet" href="<?php echo $pagecornr_path.'/pagecornr.css'; ?>" type="text/css" media="screen" /> 
<!-- Pagecornr styles end -->

<?php
		}
	
	function pagecornr() {
	global $pagecornr_path, $pagecornr_ad_url, $pagecornr_img_corner, $pagecornr_ad_msg;
?>
	
<div id="pagecornr">
	<a href="<?php echo $pagecornr_ad_url; ?>">
		<img src="<?php echo $pagecornr_img_corner; ?>" alt="<?php echo $pagecornr_ad_msg ?>" />
		<span class="bg_msg"><?php echo $pagecornr_ad_msg ?></span>
	</a>
</div>

<?php
	}
	
	  // try to always get the values from the database
	$pagecornr_version = bb_get_option(pagecornr_version);
	$pagecornr_ad_url = bb_get_option(pagecornr_ad_url);
	$pagecornr_ad_msg = bb_get_option(pagecornr_ad_msg);
	
	// if the database value returns empty use defaults
	if($pagecornr_version != pagecornr_VERSION) 
	{
		$pagecornr_version = pagecornr_VERSION; bb_update_option('pagecornr_version', pagecornr_VERSION);
		$pagecornr_ad_url = 'http://'; bb_update_option('pagecornr_ad_url', $pagecornr_ad_url);
		$pagecornr_ad_msg = 'None :P'; bb_update_option('pagecornr_ad_msg', $pagecornr_ad_msg);
		
	}
	
	function bb_pagecornr_page_add() {
        bb_admin_add_submenu( __( 'Page Cornr Options', 'page-cornr' ), 'use_keys', 'bb_pagecornr_page', 'options-general.php' );
	}
	
	add_action('bb_admin_menu_generator', 'bb_pagecornr_page_add');
	
	//print options page
	function bb_pagecornr_page() 
	{
     	global $pagecornr_path, $pagecornr_version, $pagecornr_ad_url, $pageconrnr_ad_msg;
 
     	// if settings are updated
		if(isset($_POST['update_pagecornr'])) 
		{
			if(isset($_POST['pagecornr_ad_url'])) {
				bb_update_option('pagecornr_ad_url', $_POST['pagecornr_ad_url']);
				$pagecornr_ad_url = $_POST['pagecornr_ad_url'];
			}	
			if(isset($_POST['pagecornr_ad_msg'])) {
				bb_update_option('pagecornr_ad_msg', $_POST['pagecornr_ad_msg']);
				$pagecornr_ad_msg = $_POST['pagecornr_ad_msg'];
			}	

		}
		
		// if the user clicks the uninstall button, clean all options and show good-bye message
		if(isset($_POST['uninstall_pagecornr'])) 
		{
			bb_delete_option(pagecornr_ad_url);
			bb_delete_option(pagecornr_ad_msg);
			bb_delete_option(pagecornr_version);
			echo '<div class="wrap"><h2>Good Bye!</h2><p>All Page Cornr settings were removed and you can now go to the <a href="plugins.php">plugin menu</a> and deactivate it.</p><h3>Thank you for using Page Cornr '.$pagecornr_version.'!</h3><p style="text-align:right"><small>if this happend by accident, <a href="admin-base.php?plugin=bb_pagecornr_page">click here</a> to reinstall</small></p></div>';
						
		} 
		else // show the menu
		{
			$pagecornr_version = bb_get_option(pagecornr_version);
			$pagecornr_ad_url = bb_get_option(pagecornr_ad_url);
			$pagecornr_ad_msg = bb_get_option(pagecornr_ad_msg);
			

			// if the pagecornr_version is empty or unequal, 
			// write the defaults to the database
			/*if(trim($pagecornr_version) == '') 
			{
				$pagecornr_version = pagecornr_VERSION;
				$pagecornr_ad_url = 'http://';
				$pagecornr_ad_msg = 'None :P';
			}//	*/		
			
			echo '<div class="wrap"><h2>Page Cornr Options</h2><small style="display:block;text-align:right">Version: '.$pagecornr_version.'</small><form method="post" action="admin-base.php?plugin=bb_pagecornr_page">';		
			echo '<input type="hidden" name="update_pagecornr" value="true" />';
			
			echo '<table class="form-table">';
			
			echo '<tr valign="top">';
			echo '<th scope="row">Page Cornr Ad URL</th>';
			echo '<td><input type="text" value="'.$pagecornr_ad_url.'" name="pagecornr_ad_url" size="40" /><br/>URL to point to when clicked on Ad (begins with <strong>http://</strong>)</td>';
			echo '</tr>';		
			
			echo '<tr valign="top">';
			echo '<th scope="row">Alternative text Ad</th>';
			echo '<td><input type="text" value="'.$pagecornr_ad_msg.'" name="pagecornr_ad_msg" size="40" /><br/>Alternative text of the Ad</td>';
			echo '</tr>';		
	
			echo '</table>';
			echo '<p class="submit"><input type="submit" name="Submit" value="Update Options &raquo;" /></p>';
			
			echo '</form>';
			
			
			echo '<h2>Uninstall</h2><form method="post" action="admin-base.php?plugin=bb_pagecornr_page">';
			echo '<input type="hidden" name="uninstall_pagecornr" value="true" />';
			echo '<p class="submit"><input type="submit" name="Submit" value="Clear Settings &raquo;" /></p>';		
			echo '</form>';
			
			
			echo '<p>The plugin assumes all files are installed at:<br />'.$pagecornr_path.'/</p></div>';
			
		}
	}

	
	add_action('bb_head', 'pagecornr_addjs');
	add_action('bb_foot', 'pagecornr');
?>