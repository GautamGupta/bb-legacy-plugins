<?php
/*
Plugin Name: Edit History
Description: Allows you to see a detailed history of exactly what has been changed in any post and optionally rollback (undo) to a previous edit. Uses a word based difference algorithm to minimize storage requirements instead of saving the entire previous post on each edit (ie. changing one word only uses a few bytes).
Plugin URI: http://bbpress.org/plugins/topic/102
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.3

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

$edit_history['view_level']='participate'; 	// participate/moderate/administrate   (only visible when EDIT link is available ie. 1 hour for participate)

/*    stop editing here   */

add_filter( 'post_edit_uri', 'edit_history_link');
add_action( 'bb_update_post', 'edit_history_update_post');
add_action( 'bb_init', 'edit_history_init');
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__),'edit_history_create_table');

function edit_history_init() {
global $bbdb, $post_id, $bb_post, $topic;
if (isset($_GET['edit_history']) && $post_id=intval($_GET['edit_history'])) {
$bb_post = bb_get_post( $post_id );
if (bb_current_user_can( 'edit_post', $post_id)) {
$topic = get_topic($bb_post->topic_id);
$edit_history=$bbdb->get_results("SELECT * FROM bb_edit_history WHERE post_id = $post_id ORDER BY time DESC LIMIT 999");
bb_get_header();
?>
<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <a href="<?php topic_link(); ?>"><?php topic_title( $bb_post->topic_id ); ?></a> &raquo; <a href="<?php post_link($bb_post->post_id); ?>"><?php _e('Post'); ?> <?php echo $bb_post->post_position; ?></a> &raquo;  <?php _e('Edit History'); ?></h3>
<div id="ajax-response"></div>
<ol id="thread" start="<?php echo $list_start; ?>">
<li id="post-<?php post_id(); ?>"<?php $del_class = post_del_class(); alt_class('post', $del_class); ?>>
<?php
bb_post_template();
echo "</li>";
if (is_array($edit_history)) {
foreach ($edit_history as $edit) {
	$diff=unserialize(stripslashes($edit->diff));
	$newer=$bb_post->post_text;
	$bb_post->post_text=edit_history_undo($bb_post->post_text,$diff);
?>
<li id="post-<?php post_id(); ?>"<?php $del_class = post_del_class(); alt_class('post', $del_class); ?>>
<div class="threadauthor">
	<p>
	</p>
</div>		
<div class="threadpost">
	<?php $link=get_user_name($edit->user_id); //  user_profile_link($id) ?>
	<div><span style="background:#eeee00;">&nbsp;<?php printf( __('Edited %s ago by %s'), bb_since($edit->time), $link); ?>&nbsp;</span></div>
	<div class="post"><?php  echo force_balance_tags(apply_filters( 'post_text', edit_history_visual($bb_post->post_text, $newer), $bb_post->post_id)); ?></div>
	<div class="poststuff"><?php printf( __('Edited %s ago by %s'), bb_since($edit->time), $link); ?></div>
</div>
</li>
<?php
}
echo "</ol>";
}
bb_get_footer();
exit();
}
}
}

function edit_history_link($link) { 
global $edit_history;
	if ( bb_current_user_can($edit_history['view_level']) ) { 
		echo " <a href='" . attribute_escape( add_query_arg('edit_history',get_post_id(),remove_query_arg(array('edit_history'))) ) . "' >" . __('History') ."</a> ";
	}
	return $link;
}

function edit_history_update_post($post_id) {
global $bbdb, $bb_post;
if ($post_id) {$new = $bbdb->get_var("SELECT post_text FROM $bbdb->posts WHERE post_id = $post_id LIMIT 1");}
$old=$bb_post->post_text;
if ($post_id && $post_id==$bb_post->post_id && $new && $old != $new) {
$old=edit_history_split($old); $new=edit_history_split($new);
$time=time(); $user_ip=$_SERVER["REMOTE_ADDR"]; $user_id=bb_get_current_user_info( 'id' );
$diff=addslashes(serialize(edit_history_trim_diff(edit_history_diff($old,$new)))); // looks so simple eh? not!
@$bbdb->query("INSERT INTO bb_edit_history ( `time`  , `post_id` , `user_id`, `user_ip`, `diff` ) VALUES ('$time', '$post_id' ,  '$user_id' , inet_aton('$user_ip') , '$diff')");
}
}

function edit_history_split($words) {return preg_split ("/(?!<<[^>]+)\s+(?![^<]+>)/", $words);}

function edit_history_undo($new,$diff) {
	$old=edit_history_split($new); $offset=-1;
	foreach ($diff as $key=>$value) {
		$d=edit_history_split($diff[$key]); $icount=$d[0]; unset($d[0]);
		array_splice ( $old  , $key+$offset  , $icount , $d );
		$offset+=count($d) -1;	
	}
return implode(" ",$old);	
}

function edit_history_trim_diff($diff) {
	foreach ($diff as $key=>$value) {
		if (!is_array($value)) {unset($diff[$key]);}
		else {		
			if ($diff[$key]['d']==$diff[$key]['i']) {unset($diff[$key]);} 
			else {$diff[$key]=trim(count($diff[$key]['i'])." ".implode(' ',$diff[$key]['d']));}
		}
	}
return $diff;	
}

function edit_history_diff($old, $new) {	
	foreach($old as $oindex => $ovalue){
		$nkeys = array_keys($new, $ovalue);
		foreach($nkeys as $nindex){
			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
				$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
			if($matrix[$oindex][$nindex] > $maxlen){
				$maxlen = $matrix[$oindex][$nindex];
				$omax = $oindex + 1 - $maxlen;
				$nmax = $nindex + 1 - $maxlen;
			}
		}	
	}
	if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
	return array_merge(
		edit_history_diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
		array_slice($new, $nmax, $maxlen),
		edit_history_diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

function edit_history_visual($old, $new) {
	$diff = edit_history_diff(edit_history_split($old),edit_history_split($new));
	// if (bb_current_user_can('administrate')) {echo "<code>"; print_r($diff); echo "</code>";}
	foreach($diff as $k){
		if (is_array($k)) {
			$d=(!empty($k['d'])?implode(' ',$k['d']):'');
			$i=(!empty($k['i'])?implode(' ',$k['i']):'');
			if ($d && $i && str_replace("</p>","",$d)==str_replace("</p>","",$k['i'][0])) {$ret.=$k['i'][0]; unset($d); unset($k['i'][0]); $i=implode(' ',$k['i']);}	
			if ($d && $i && substr($d,-6)=="<br />" && substr($i,-6)=="<br />") {$d=substr($d,0,-6);}			
			// to do: strip end tags in $d that have no start tags as they will be in $i  - also for ending bbcode tags			
			// if ($d && $i) {$d=force_balance_tags(post_text($d));}
			$ret .= (!empty($d)?"<del style='background:#ffdddd;'>".str_replace("<p>","</del><p><del style='background:#ffdddd;'>",$d)."</del> ":'').
				(!empty($i)?"<ins style='text-decoration:none; background:#ddFFdd;'>".str_replace("<p>","</ins><p><ins style='text-decoration:none; background:#ddFFdd;'>",$i)."</ins> ":'');			
		} else { $ret .= $k . ' ';}
	}
	return $ret;
}

function edit_history_create_table() {
global $bbdb; 
$bbdb->query("CREATE TABLE IF NOT EXISTS `bb_edit_history` (
		`time`       int(10) UNSIGNED NOT NULL default '0',
		`post_id` int(10) UNSIGNED NOT NULL default '0',
		`user_id` int(10) UNSIGNED NOT NULL default '0',
		`user_ip` int(10) UNSIGNED NOT NULL default '0',
		`diff`         text      NOT NULL default '',
		INDEX (`post_id`)
		)");	
} 
?>