<?php
/*
Plugin Name: My Views module - Installed/Available Plugins
Description: This plugin is part of the My Views plugin. It adds Installed/Available Plugins to the list of views.
		Note that Available Plugins requires the "Plugin Browser" plugin by Sam Bauers.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.05
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
    $query = ''; 
    bb_register_view("installed-plugins","Installed bbPress Plugins",$query);
    bb_register_view("available-plugins","Available bbPress Plugins",$query);

} else {		// Build 214-875	(0.8.2.1)

function my_views_installed_available_plugins_filter( $views ) {
	global $views;	
	$views["installed-plugins"] = "Installed bbPress Plugins";
	$views["available-plugins"] = "Available bbPress Plugins";
	 return $views;
}  
add_filter('bb_views', 'my_views_installed_available_plugins_filter');  
}

function my_views_installed_plugins_view($view) {
	if ($view=="installed-plugins") :	
		bb_get_header();
		my_views_header(1);
		my_views_installed_plugins();
		bb_get_footer();
		exit();
	endif;
} 
add_action( 'bb_custom_view', 'my_views_installed_plugins_view' );

function my_views_available_plugins_view($view) {
	if ($view=="available-plugins") :
		bb_get_header();
		my_views_header(1);
		my_views_available_plugins(); 
		bb_get_footer();
		exit();
	endif;
} 
add_action( 'bb_custom_view', 'my_views_available_plugins_view' );

function my_views_installed_plugins() {

$dir = new BB_Dir_Map( BBPLUGINDIR, array(
	'callback' => create_function('$f,$_f', 'if ( ".php" != substr($f,-4) || "_" == substr($_f, 0, 1) ) return false; return my_views_get_installed_plugin_details( $f );'),'recurse' => 1) );
$plugins  = $dir->get_results();

$_plugins = array();
if ( is_callable( 'glob' ) ) {
	foreach ( glob(BBPLUGINDIR . '_*.php') as $_plugin ) {$_data = my_views_get_installed_plugin_details( $_plugin );	$_plugins[$_plugin] = $_data ? $_data : true;}
}

$current = (array) bb_get_option( 'active_plugins' );
?>

<?php if( $plugins ) : ?> 

<table id="latest">
<thead>
	<tr>
		<th>Plugin</th>
		<th>Author</th>
		<th>Description</th>
		<th class="vers">Version</th>
		<th class="action">Status</th>
	</tr>
</thead>
<tbody>

<?php foreach ( $plugins as $p => $plugin ) : $class = in_array($p, $current) ? 'active' : ''; ?>
	<tr<?php alt_class( 'plugin', $class ); ?>>
		<td><?php echo $plugin['plugin_link']; ?></td>
		<td><? echo $plugin['author_link']; ?></td>		
		<td><?php echo $plugin['description']; ?>	</td>
		<td class="vers"><?php echo $plugin['version']; ?></td>
<?php if ( $class ) : ?>
		<td class="action">active</td>
<?php else : ?>
		<td class="action">inactive</td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>

<?php  if ( $_plugins ) : ?>

<?php foreach ( $_plugins as $p => $plugin ) : ?>
	<tr<?php alt_class( '_plugin' ); ?>>
<?php if ( is_array($plugin) ) : ?>
		<td><?php echo $plugin['plugin_link']; ?></td>
		<td><cite><?php printf( __('By %s.'), $plugin['author_link'] ); ?></cite></td>		
		<td><?php echo $plugin['description']; ?>	</td>
		<td class="vers"><?php echo $plugin['version']; ?></td>
		<td class="action">auto-load</td>
<?php else : ?>
		<td colspan="4"><?php echo wp_specialchars( $p ); ?></td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>

<?php endif; ?>
<?php endif; ?>

</tbody>
</table>

<?php
}

function my_views_get_installed_plugin_details($plugin_file) {
	$plugin_data = implode('', file($plugin_file));
	if ( !preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name) )
		return false;
	preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
	if ( preg_match("|Requires at least:(.*)|i", $plugin_data, $requires) )
		$requires = wp_specialchars( trim($requires[1]) );
	else
		$requires = '';
	if ( preg_match("|Tested up to:(.*)|i", $plugin_data, $tested) )
		$tested = wp_specialchars( trim($tested[1]) );
	else
		$tested = '';
	if ( preg_match("|Version:(.*)|i", $plugin_data, $version) )
		$version = wp_specialchars( trim($version[1]) );
	else
		$version = '';

	$plugin_name = wp_specialchars( trim($plugin_name[1]) );
	$plugin_uri = clean_url( trim($plugin_uri[1]) );
	$author_name = wp_specialchars( trim($author_name[1]) );
	$author_uri = clean_url( trim($author_uri[1]) );

	$description = trim($description[1]);
	$description = bb_encode_bad( $description );
	$description = bb_code_trick( $description );
	$description = balanceTags( $description );
	$description = bb_filter_kses( $description );
	$description = bb_autop( $description );

	$r = array(
		'name' => $plugin_name,
		'uri' => $plugin_uri,
		'description' => $description,
		'author' => $author_name,
		'author_uri' => $author_uri,
		'requires' => $requires,
		'tested' => $tested,
		'version' => $version
	);

	$r['plugin_link'] = ( $plugin_uri ) ?
		"<a href='$plugin_uri' title='" . attribute_escape( __('Visit plugin homepage') ) . "'>$plugin_name</a>" :
		$plugin_name;
	$r['author_link'] = ( $author_name && $author_uri ) ?
		"<a href='$author_uri' title='" . attribute_escape( __('Visit author homepage') ) . "'>$author_name</a>" :
		$author_name;

	return $r;
}

function my_views_available_plugins() {

if (!class_exists("Plugin_Browser")) {return;}

	$plugin_browser = new Plugin_Browser();
	
	$localList = $plugin_browser->getLocalRepositoryRevision();
	$remoteList = $plugin_browser->getRemoteRepositoryRevision();
?>	
	<table id="latest">
		<thead>
			<tr>
				<th>Plugin</th>
				<th>Author</th>
				<th>Description</th>
				<th class="action">Latest version</th>
			</tr>
		</thead>
		<tbody>
<?php
	$plugins = $plugin_browser->getLocalRepositoryList();
	
	// $bb_plugins = bb_get_plugins();
	// $bb_plugins_keys = array_map(create_function('$input', 'return preg_replace("|pb\-\-([^/]+)/.*|", "$1", $input);'), array_keys($bb_plugins));
	
	foreach ($plugins as $plugin) {
		if ($plugin['version']) {
			$upgradeText = null;
			$action = 'install';
			$actionText = __('Install');
			$plugin['version_local'] = __('None');
			$plugin['revision_local'] = null;
			$class = null;
			
?>
			<tr<?php alt_class('plugin', $class); ?>>
				<td><?php echo($plugin['plugin_link']); ?></td>
				<td><?php echo $plugin['author_link']; ?></cite></td>
				<td><?php echo($plugin['description'] . $readmeLink); ?><cite></td>				
				<td class="action" style="white-space:nowrap;"><?php echo($plugin['version']); ?><br /><small><em>r. <?php echo($plugin['revision']); ?></em></small></td>
			</tr>
<?php
		}
	}
?>
		</tbody>
	</table>
	
<?php
}

?>