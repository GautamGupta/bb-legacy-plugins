<?php
/*
Plugin Name: My Views module - Installed/Available Themes
Description: This plugin is part of the My Views plugin. It adds Installed/Available Themes to the list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.091
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
    $query = ''; 
//  bb_register_view("installed-themes","Installed bbPress Themes",$query);
    bb_register_view("available-themes","Available bbPress Themes",$query);

} else {		// Build 214-875	(0.8.2.1)

function my_views_installed_available_themes_filter( $passthrough ) {
	global $views;
//	$views["installed-themes"] = "Installed bbPress Themes";
	$views["available-themes"] = "Available bbPress Themes";
	return $passthrough;
}  
add_filter('bb_views', 'my_views_installed_available_themes_filter');  
}

function my_views_installed_themes_view($view) {
	if ($view=="installed-themes") :	
		bb_get_header();
		my_views_header(1);
		my_views_installed_themes();
		my_views_footer();		
		bb_get_footer();
		exit();
	endif;
} 
add_action( 'bb_custom_view', 'my_views_installed_themes_view' );

function my_views_available_themes_view($view) {
	if ($view=="available-themes") :
		bb_get_header();
		my_views_header(1);
		my_views_available_themes(); 
		bb_get_footer();
		exit();
	endif;
} 
add_action( 'bb_custom_view', 'my_views_available_themes_view' );

function my_views_available_themes() {

?>	
	<table id="latest">		
			<tr>
				<th>Theme</th>
				<th>Author</th>				
				<th>Screenshot</th>				
			</tr>		
		
<?php 

$themes = array();	// $themes = bb_get_themes();
$theme_roots = array(BBPATH . 'bb-templates/', BBTHEMEDIR );
foreach ( $theme_roots as $theme_root )
	if ( $themes_dir = @dir($theme_root) )
		while( ( $theme_dir = $themes_dir->read() ) !== false )
			if ( is_dir($theme_root . $theme_dir) && is_readable($theme_root . $theme_dir) && '.' != $theme_dir{0} )
				$themes[$theme_dir] = $theme_root . $theme_dir . '/';

ksort($themes);

// $activetheme = bb_get_option('bb_active_theme'); bb_admin_theme_row( $themes[basename($activetheme)] ); unset($themes[basename($activetheme)] ); 
$count=0;
foreach ( $themes as $theme ) {

	$theme_data = file_exists( $theme . 'style.css' ) ? my_views_get_theme_data( $theme . 'style.css' ) : false;
	if (file_exists( $theme . 'screenshot.png' )) {
		if ( 0 === strpos($theme, BBTHEMEDIR) ) {$screen_shot = BBTHEMEURL . substr($theme, strlen(BBTHEMEDIR))."screenshot.png";}
		elseif ( 0 === strpos($theme, BBPATH) ) {$screen_shot = bb_get_option( 'uri' ) . substr($theme, strlen(BBPATH))."screenshot.png";}
	} else {$screen_shot=false;}
	if (function_exists('bb_ts_set_theme_cookie')) {$activation_url = '?bbtheme='.urlencode(basename($theme));}
	else {$activation_url="#";} // clean_url( bb_nonce_url( add_query_arg( 'theme', urlencode($theme), bb_get_option( 'uri' ) . 'bb-admin/themes.php' ), 'switch-theme' ) );
	$authors[]=trim(strip_tags( $theme_data['Author']));
	++$count;
?>
	<tr<?php alt_class( 'theme', $class ); ?>>		
		
		<td align=center>
		<h2><a href="<?php echo $activation_url; ?>" title="<?php echo attribute_escape( __('Click to activate') ); ?>"><?php echo $theme_data['Title']; ?></a></h2>
		<div class=num><?php echo $theme_data['Version']; ?></div>
		<div><?php echo $theme_data['Description']; ?></div>
		</td>
		<td><?php echo $theme_data['Author']; if ( $theme_data['Porter'] ) printf(__(',<div>ported by <cite>%s</cite></div>'), $theme_data['Porter']); ?></td>		
		<td align=center class="screen-shot"><?php // if ( $screen_shot ) : ?>
				<a href="<?php echo $activation_url; ?>" title="<?php echo attribute_escape( __('Click to activate') ); ?>">
				<img width=300 alt="<?php echo attribute_escape( $theme_data['Title'] ); ?>" src="<?php echo $screen_shot; ?>" /></a><?php // endif; ?>
		</td>		
	</tr>
	
<?php } ?>	
		
	<tr class=sortbottom><th nowrap>Total Themes: <?php echo $count; ?></th><th nowrap>Authors: <?php echo count(array_unique($authors)); ?></th><th>&nbsp;</th></tr>

	</table>
	
<?php
}

function my_views_get_theme_data( $theme_file ) {
	 $theme_data = implode( '', file( $theme_file ) );
	// $fd = fopen ("log_file.txt", "r");  $buffer = fgets($fd, 512);  fclose ($fd);  $lines[] = $buffer;
	
	$theme_data = str_replace ( '\r', '\n', $theme_data ); 
	preg_match( '|Theme Name:(.*)|i', $theme_data, $theme_name );
	preg_match( '|Theme URI:(.*)|i', $theme_data, $theme_uri );
	preg_match( '|Description:(.*)|i', $theme_data, $description );
	preg_match( '|Author:(.*)|i', $theme_data, $author_name );
	preg_match( '|Author URI:(.*)|i', $theme_data, $author_uri );
	preg_match( '|Ported By:(.*)|i', $theme_data, $porter_name );
	preg_match( '|Porter URI:(.*)|i', $theme_data, $porter_uri );
//	preg_match( '|Template:(.*)|i', $theme_data, $template );
	if ( preg_match( '|Version:(.*)|i', $theme_data, $version ) )
		$version = wp_specialchars( trim( $version[1] ) );
	else
		$version ='';
	if ( preg_match('|Status:(.*)|i', $theme_data, $status) )
		$status = wp_specialchars( trim($status[1]) );
	else
		$status = 'publish';

	$description = trim($description[1]);
	$description = bb_encode_bad( $description );
	$description = bb_code_trick( $description );
	$description = balanceTags( $description );
	$description = bb_filter_kses( $description );
	$description = bb_autop( $description );

	$name = $theme_name[1];
	$name = wp_specialchars( trim($name) );
	$theme = $name;

	if ( '' == $author_uri[1] ) {
		$author = wp_specialchars( trim($author_name[1]) );
	} else {
		$author = '<a href="' . clean_url( trim($author_uri[1]) ) . '" title="' . attribute_escape( __('Visit author homepage') ) . '">' . wp_specialchars( trim($author_name[1]) ) . '</a>';
	}

	if ( '' == $porter_uri[1] ) {
		$porter = wp_specialchars( trim($porter_name[1]) );
	} else {
		$porter = '<a href="' . clean_url( trim($porter_uri[1]) ) . '" title="' . attribute_escape( __('Visit porter homepage') ) . '">' . wp_specialchars( trim($porter_name[1]) ) . '</a>';
	}

	return array(
		'Name' => $name,
		'Title' => $theme,
		'Description' => $description,
		'Author' => $author,
		'Porter' => $porter,
		'Version' => $version,
//		'Template' => $template[1],
		'Status' => $status,
		'URI' => clean_url( $theme_uri[1] )
	);
}

?>