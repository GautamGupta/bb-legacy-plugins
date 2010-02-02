<?php
require_once( '../../bb-load.php' );

$post_id = $_POST['post_id'];
$user_id = $_POST['user_id'];

$meta = bb_get_post_meta("thanks", $post_id);
if (!isset($meta)) {
	$meta = array();
}
$tmp = array();
for ($i=0; $i<count($meta); $i++) {
	$tmp[$meta[$i]] = "X";
}
$tmp[$user_id] = "X";
$meta = array_keys($tmp);
$store = bb_update_postmeta($post_id, "thanks", $meta);

$opt = bb_get_option("thanks_posts");
if (!isset($opt)) {
	$opt = array();
}
$tmp = array();
for ($i=0; $i<count($opt); $i++) {
	$tmp[$opt[$i]] = "X";
}
$tmp[$post_id] = "X";
$opt = array_keys($tmp);
$store2 = bb_update_option( 'thanks_posts', $opt );

$array = array();
$array['result'] = "OK";
$array['votes'] = $meta;
$array['store'] = $store;
$array['store2'] = $store2;

echo json_encode($array)."\n";
?>