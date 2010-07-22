<?php

function check_for_updates() {
if ( !bb_current_user_can('administrate') ) {die;}

global $plugins;
check_for_updates_pluginslist();
check_for_updates_masterlist();
?>
<h2><?php _e('Check for Updates'); ?></h2>  
<div style="font-size:12px;margin-top:-3em;float:right;"><?php if (empty($plugins['all'])) {echo "<font color='red'>connection error!</font></div>"; return;} $age=reset($plugins['all']); $version=key($plugins['all']); echo __('using revision')." $version (".bb_since($age)." ".__('old').") "; ?></div>

<table class="widefat">
<thead>
	<tr>
		<th style="text-align:left;">Plugin</th>
		<th>Author</th>		
		<th>Status</th>
		<th>Version&nbsp;(installed)</th>
		<th>Version&nbsp;(newest)</th>
	</tr>
</thead>
<?php $count=0; $authors=array();
foreach ($plugins as $status=>$data) {	
	if ($status=='all' || empty($data) || !is_array($data)) {continue;}
	uksort($data, 'strnatcasecmp');
	if ($status=="active") {$class="active";} 
	elseif ($status=="autoload") {$class="autoload";} 
	else {$class="";}  // inactive
	
	foreach ( $data as $p => $plugin ) { 		
		$thisclass=$class;
		$filename=basename($p); 
		if (isset($plugins['all'][$filename])) {
			$version=$plugins['all'][$filename];
			if ($plugin['version']!=$version) {$thisclass="new";} 
		} else {$version=__("unknown");}	
	?>
	<tr<?php alt_class( 'plugin', $thisclass ); ?>>
		<td style="text-align:left;"><?php echo $plugin['plugin_link']; ?></td>
		<td><?php echo $plugin['author_link']; @$authors[trim(strip_tags($plugin['author']))]++; ?></td>				
		<td class="action" nowrap><?php echo $status; ?></td>
		<td class="vers" ><?php echo $plugin['version']; ?></td>
		<td class="vers" id="cfu<?php echo $count.'">'.$version; ?></td>
	</tr>
<?php 
	$count++; 
	}
}

?>
<tr class=sortbottom>
	<th nowrap>Total Plugins: <?php echo $count; ?></th><th nowrap>Authors: <?php echo count($authors); ?></th><th colspan="3">[<a href="http://bbshowcase.org/donate/">Donate</a>]</th>
</tr>
</table>

<?php
}

function check_for_updates_plugin_details($plugin_file) {	
	$handle = fopen($plugin_file, "rb"); $plugin_data = fread($handle, 1024); fclose($handle); 	// $plugin_data = implode('', file($plugin_file));  // only first 1k for speed
	if (!preg_match("|Plugin"." Name:(.*)|i", $plugin_data, $plugin_name)) {return false;} 
	preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
	if ( preg_match("|Requires at least:(.*)|i", $plugin_data, $requires) ) {$requires = wp_specialchars( trim($requires[1]) );} else {$requires = '';}
	if ( preg_match("|Tested up to:(.*)|i", $plugin_data, $tested) ) {$tested = wp_specialchars( trim($tested[1]) );} else {$tested = '';}
	if ( preg_match("|Version:(.*)|i", $plugin_data, $version) ) {$version = wp_specialchars( trim($version[1]) );} else {$version = '';}

	$plugin_name = wp_specialchars( trim($plugin_name[1]) );
	$plugin_uri = clean_url( trim($plugin_uri[1]) );
	$author_name = wp_specialchars( trim($author_name[1]) );
	$author_uri = clean_url( trim($author_uri[1]) );
	$description = bb_autop( bb_filter_kses( balanceTags( bb_code_trick( bb_encode_bad( trim($description[1]) ) ) ) ) );

	$plugin_link = ($plugin_uri) ? "<a href='$plugin_uri' title='" . attribute_escape( __('Visit plugin homepage') ) . "'>$plugin_name</a>" : $plugin_name;
	$author_link = ($author_name && $author_uri) ? "<a href='$author_uri' title='" . attribute_escape( __('Visit author homepage') ) . "'>$author_name</a>" :  $author_name;

	return array(
		'name' => $plugin_name,
		'uri' => $plugin_uri,
		'description' => $description,
		'author' => $author_name,
		'author_uri' => $author_uri,
		'requires' => $requires,
		'tested' => $tested,
		'version' => $version,
		'plugin_link' => $plugin_link,
		'author_link' => $author_link
	);	
}

function check_for_updates_pluginslist() {
global $plugins;
$plugins['current'] = (array) str_replace("user#","",bb_get_option( 'active_plugins' ));
$plugins['current'] = array_flip($plugins['current']); // speed up by key

if ( defined( 'BB_PLUGIN_DIR' ) ) {$bbplugindir=BB_PLUGIN_DIR;} elseif (defined('BBPLUGINDIR' ) ) {$bbplugindir=BBPLUGINDIR;}
if (empty($bbplugindir) || !file_exists($bbplugindir)) {$bbplugindir=BB_PATH . 'bb-plugins/';} 	 // they are using bb-plugins instead of my-plugins

if (defined('BACKPRESS_PATH')) {require_once( BB_PATH . BB_INC . 'class.bb-dir-map.php' );}

$dir = new BB_Dir_Map( $bbplugindir, array('recurse' => 1,
	'callback' => create_function('$f,$_f', '	
	if (".php" == substr($_f,-4) && $result=check_for_updates_plugin_details($f)) {
		global $plugins;
		if ("_" == substr($_f, 0, 1)) {$key="autoload";} 
		elseif (isset($plugins["current"][$_f])) {$key="active";} 
		else {$key="inactive";}
		$plugins[$key][$_f]=$result;
	}
	')));
$dir->get_results(); 
unset($plugins['current']);
}

function check_for_updates_masterlist() {
// fetch master list - todo: mirrors, error checking, timeout
$url="http://plugins-svn.bbpress.org/check-for-updates/trunk/plugin-list.txt.gz"; 
// $url="http://bbshowcase.org/forums/my-plugins/check-for-updates/plugin-list.txt.gz"; 
if (function_exists('curl_exec')) {
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url);	
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_HEADER, 0);	
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1); 	
	$data = curl_exec($ch); curl_close($ch);
} else {
	$url=parse_url($url); 
	$data=check_for_updates_fsockfetch($url['host'],80,$url['path']);
	$data=$data['data'];	
}
$data=gzinflate(substr($data,10));
$lines=explode("\n",$data);
unset($data);
global $plugins;
foreach ($lines as $line) {$line=trim($line); if (!empty($line)) {list($key,$version)=explode("|",$line,2); $plugins['all'][$key]=trim($version);}}
unset($lines);
}

function check_for_updates_direct($url) {
$url=parse_url($url); 
$url['port']=isset($url['port']) ? $url['port'] : 80;
$result=check_for_updates_fsockfetch($url['host'],$url['port'],$url['path']);
if (!empty($result['redirect'])) {	
	if (strtolower(substr($result['redirect'],0,7))=='http://') {$url=parse_url($result['redirect']); $url['port']=isset($url['port']) ? $url['port'] : 80;} 
	else {$url['path']=$result['redirect'];}
	$result=check_for_updates_fsockfetch($url['host'],$url['port'],$url['path']); // we only try to redirect once for safety + speed
}
if (!empty($result['data']) && preg_match("|\<h2 class.+?topictitle.*?\>.+?\<small\>\((.+?)\)\<\/small\>\<\/h2\>|i", $result['data'], $version)) {return trim($version[1]);} 
else {return __("error");}
}

function check_for_updates_fsockfetch($addr="127.0.0.1", $port="80", $path="/", $user="", $pass="", $timeout="10",$agent="Mozilla/4.0 (bbPress Check for Updates)") {
        $handle = fsockopen($addr, $port, $errno, $errstr, $timeout); 
        if (!$handle)    {
                $result['code']=0;
                $result['response']="$errno $errstr";
                $result['headers']="";
                $result['data']="";
                return $result;
          }
          socket_set_blocking($handle, 1);
          socket_set_timeout($handle, $timeout);
                  
           if ($path)      {$urlString = "GET $path HTTP/1.1\r\nHost: $addr\r\nUser-Agent: $agent\r\nConnection: close\r\n";}
           if ($user)      {$urlString .= "Authorization: Basic ".base64_encode("$user:$pass")."\r\n";}
           $urlString .= "\r\n";

           fputs($handle, $urlString,strlen($urlString)); 
           $result['response']= fgets($handle);
           $result['code']=substr($result['response'],9,3);
           $result['headers']="";
           $result['data']="";

           if (in_array($result['code'],array("200","301","302","401")))  {    // Check the status of the link
           	 	$endHeader = false;                     // Strip initial header information                           
                           while ( !$endHeader && !feof($handle))       {
                               	$test=fgets($handle); 
                               	if (strpos(strtolower($test),"location:")!==false) {$result['redirect']=trim(preg_replace("/location:/i","",$test));}
                                	if ($test== "\r\n") {  $endHeader = true; } else {$result['headers'].=$test;}
                             }                             
                            $result['data'] = "";  while ($data=fread($handle,8192)) {$result['data'].=$data;} //  if (strlen($result['data'])>=8192) {break;}} 
             }

  fclose($handle); 
  return $result;      
}

function check_for_updates_header() {
?>
<style type="text/css">
table.widefat th, table.widefat td {text-align:center;}
tr.alt.new td, tr.new td  {background-color: #ee8888; font-weight: bold;}
tr.alt.new td a, tr.new td a, #bbBody tr.new td a {color:#000; text-decoration:none; border-bottom:1px dashed #000;}
tr.autoload td {background-color: #ddd;}
</style>
<?php
}
add_action( 'bb_admin_head','check_for_updates_header',100); 

?>