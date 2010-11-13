<?php
/*
Plugin Name: XMLRPC Client Info
Plugin URI: http://master5o1.com/projects/bbpress-mobile/
Description: Client Info for interfaces to the forum connecting through XML-RPC api.
Author: Jason Schwarzenberger
Author URI: http://master5o1.com/
Version: 0.1
*/

/*
To use, put the following into post.php of your template:

<?php if (function_exists('show_client_info')) { echo show_client_info(); } ?>
or
<?php echo apply_filters( 'show_xmlrpc_client_info', '' ); ?>
*/

add_filter( 'show_xmlrpc_client_info', 'show_client_info',100); 
add_filter( 'bb_xmlrpc_prepare_post', 'xmlrpc_client_info_prepare_post', -999, 2);
if (XMLRPC_REQUEST === true) {
	add_action('bb_insert_post', 'xmlrpc_add_client_info', 1);
	add_filter('pre_post', 'xmlrpc_stripslashes', 1);
	add_filter( 'pre_topic_title', 'xmlrpc_stripslashes', 1);
}

function show_client_info($titlelink='') {
	$client = bb_get_postmeta( get_post_id(), 'xmlrpc_client' );
	$client_uri = bb_get_postmeta( get_post_id(), 'xmlrpc_client_uri' );
	if (!empty($client)) {
		if (empty($client_uri)) {
			$titlelink = $titlelink . " via <span style=\"font-variant: normal;text-transform: none;\">{$client}</span> ";
		} else {
			$titlelink = $titlelink . " via <a style=\"font-variant: normal;text-transform: none;\" href=\"{$client_uri}\" title=\"{$client}\">{$client}</a> ";
		}
	} elseif (!empty($client_uri)) {
		$titlelink = $titlelink . " via <a style=\"font-variant: normal;text-transform: none;\" href=\"{$client_uri}\" title=\"{$client_uri}\">Client</a> ";
	}
	return $titlelink;
}

function xmlrpc_client_info_prepare_post ($_post, $post) {
	$_post['xmlrpc_client'] = bb_get_postmeta( $_post['post_id'], 'xmlrpc_client' );
	$_post['xmlrpc_client_uri'] = bb_get_postmeta( $_post['post_id'], 'xmlrpc_client_uri' );
	return $_post;
}

function xmlrpc_client_info() {
	print show_client_info();
}

function xmlrpc_stripslashes($text) {
	return stripslashes($text);
}

function xmlrpc_add_client_info($post_id) {
	$client = '';
	$client_uri = '';
	if (isset($_GET['client'])) {
		$client = $_GET['client'];
		$client = urldecode($client);
		$client = trim($client);
		if (isset($_GET['client_uri'])) {
			$client_uri = $_GET['client_uri'];
			$client_uri = urldecode($client_uri);
			$client_uri = trim($client_uri);
		}
	}
	bb_update_postmeta( $post_id, 'xmlrpc_client', $client );
	bb_update_postmeta( $post_id, 'xmlrpc_client_uri', $client_uri );
}
?>
