<?php
/*
Plugin Name: Post Meta for bbPress
Plugin URI: http://bbpress.org/plugins/topic/post-meta
Description: Create any additional custom fields for posts, including 'Name', 'Email' and 'Website' for anonymous users
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/
/*
NOTICE: you MUST change line 9 of edit-form.php template from `<?php endif; ?>` to  `<?php endif; do_action( 'edit_form_pre_post' ); ?>`
*/

$post_meta['author']->capability=array('anonymous'=>'required');
$post_meta['author']->attributes=array('label'=>'Name','size'=>50,'html'=>false);

$post_meta['email']->capability=array('anonymous'=>'required');
$post_meta['email']->attributes=array('label'=>'Email','size'=>50,'html'=>false,'regex'=>'/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/si');

$post_meta['url']->capability=array('anonymous'=>'optional');
$post_meta['url']->attributes=array('label'=>'Website','size'=>100,'html'=>false,'regex'=>'/^((\w+)\:\/+)?(([\w\-]+(\.[\w\-]+)+)(\:\d+)?)([\w\-\/\.\?;&]+)?/si');

$post_meta['title']->capability=array('administrate'=>'optional');
$post_meta['title']->attributes=array('label'=>'Post Title','size'=>100,'html'=>false);

$post_meta_options['auto insert']=true;	// create fields in new/edit post forms automatically

/* content manipulation "sub-plugin" follows */

add_filter('post_author', 'post_meta_author' );
add_filter( 'post_author_title', 'post_meta_author_title');
add_filter( 'post_author_title_link', 'post_meta_author_title');
add_filter('get_post_author_id', 'post_meta_author_id',9);
add_filter('get_topic_last_poster','post_meta_last_poster',11,2);
add_filter('get_topic_author','post_meta_topic_author',11,2);
add_filter('post_text','post_meta_post_title');

function post_meta_last_poster($name, $id) {global $topic; return $topic->topic_last_poster_name;}
function post_meta_topic_author($name, $id) {global $topic; return $topic->topic_poster_name;}

function post_meta_author($author) {
global 	$post_meta_cache, $bb_post; 
if (!isset($post_meta_cache[$bb_post->post_id])) {post_meta_cache_post($bb_post->post_id);}
if (!empty($bb_post->author)) {$author=$bb_post->author;} 
if (!empty($bb_post->url)) {$link=$bb_post->url; if (!preg_match('/^https?\:\/\//si',$link)) {$link="http://$link";}  $author="<a target='_blank' rel='nofollow' href='$link'>$author</a>";} 
return $author;
}

function post_meta_author_title($title) {
global 	$post_meta_cache, $bb_post; 
if (!isset($post_meta_cache[$bb_post->post_id])) {post_meta_cache_post($bb_post->post_id);}
if ($bb_post->poster_id==0 || $bb_post->poster_id==bb_get_option('bb_anon_user_id')) {$title=__('guest');} 
return $title;
}

function post_meta_author_id($user_id) {
global 	$post_meta_cache, $bb_post; 
if ($user_id==$bb_post->poster_id) {
	if ($user_id== bb_get_option('bb_anon_user_id')) {$id=0;} else {$id=$user_id;}
	$save=false;
	if (defined('BACKPRESS_PATH')) {$bb10=true; $user=wp_cache_get($id,'users');} 
	else {global $bb_user_cache; $bb10=false; $user=$bb_user_cache[$id];}
	if (!isset($post_meta_cache[$bb_post->post_id])) {post_meta_cache_post($bb_post->post_id);}	
	
	if (isset($bb_post->author)) {$user->user_login=$user->display_name=$bb_post->author; $save=true;}
	if (isset($bb_post->email)) {$user->user_email=$bb_post->email; $save=true;}
	if (isset($bb_post->url)) {$user->user_url=$bb_post->url; $save=true;}
	if ($id==0) {$user->title=__('guest'); $save=true;} 
	
	if ($save && $user) {
		if ($bb10) {wp_cache_set($id, $user, 'users' );}  
		else {$bb_user_cache[$id]=$user;}
	}
}
return $user_id;
}

/*  // not used, poor performance
add_filter('topic_last_poster','post_meta_last_poster',12,2);
add_filter('get_topic_last_poster','post_meta_last_poster',12,2);
function post_meta_last_poster($name, $id) {
global 	$post_meta_cache, $bb_post, $topic;
// if ($id==0 || $id == bb_get_option('bb_anon_user_id')) {}
if (!isset($post_meta_cache[$topic->topic_last_post_id])) {post_meta_cache_post($topic->topic_last_post_id);}
if (!empty($bb_post->author)) {$name=$bb_post->author;} 
return $name;		
}
*/

function post_meta_post_title($text) {
global 	$post_meta_cache, $bb_post; 
if (!isset($post_meta_cache[$bb_post->post_id])) {post_meta_cache_post($bb_post->post_id);}
if (!empty($bb_post->title)) {$text="<div style='margin:-1em 0 0.5em 0; padding:1px 0; font-size:larger; border-bottom:1px solid #ddd;'>".wp_specialchars($bb_post->title)."</div>".$text;} 
return $text;
}

/* stop editing here */

if ($post_meta_options['auto insert']) {
	add_action('post_form_pre_post', 'post_meta_form');	// new post
	add_action('edit_form_pre_post', 'post_meta_form');	// existing post - this must be added into  edit-form.php template
}	
add_action( 'bb_init', 'post_meta_init',12);
add_action( 'bb_topic.php', 'post_meta_cache_topic'); // for some reason bbPress does not have ANY actions or filters on any get_post functions in 0.9 or 1.0 :-(
add_action( 'bb_new_post', 'post_meta_save' );
add_action( 'bb_update_post', 'post_meta_save' );
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'post_meta_install');

function post_meta_init() {
	remove_filter('topic_last_poster','bb_anon_filter_poster',10,2);
	remove_filter('get_topic_author','bb_anon_filter_poster',10,2);

	$resource=array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']);
	$location=""; foreach ($resource as $name ) {if (false!==strpos($name, '.php')) {$location=bb_find_filename($name);}} 
	if ($location=="edit.php" && !empty($_GET['id'])) {$post_id = (int) $_GET['id']; post_meta_cache_post($post_id);} 
	if (!empty($_POST) && ($location=="bb-post.php" || $location=="bb-edit.php")) {post_meta_validate();}
}

function post_meta_validate() {
global $bbdb, $bb_table_prefix, $bb_post, $bb_current_user, $post_meta, $post_meta_save;
$post_meta_save=array(); 
foreach ($post_meta as $key=>$value) {
	$required=""; foreach ($value->capability as $cap=>$mode) {if ($bb_current_user->has_cap($cap)) {$required=$mode;}}
	if (empty($required)) {continue;}
	foreach ($value->attributes as $variable=>$content) {$$variable=$content;}
	$sent=""; if (!empty($_POST[$key])) {$sent=stripslashes($_POST[$key]);}
	if (!empty($html)) {$sent=post_meta_allow_tags($sent);} else {$sent=strip_tags($sent);} 
	$sent=trim(substr(trim($sent),0,$size));
	if (empty($sent)) {if ($required=="required") {post_meta_error("$label is required"); exit;}}
	else {
		if (!empty($regex)) {if (!preg_match($regex,$sent)) {post_meta_error("$label is incorrect"); exit;}}	
		$post_meta_save[$key]=$sent;
	}
	foreach ($value->attributes as $variable=>$content) {unset($$variable);} // I need to clean this function up to prevent the need for this
} 
}

function post_meta_save($post_id) {
global $bbdb, $bb_table_prefix, $bb_post, $bb_current_user, $post_meta, $post_meta_save;
if (empty($post_id) || empty($post_meta_save)) {return;}
if (empty($bb_post->topic_id)) {$bb_post=bb_get_post($post_id);}
$topic_id=$bb_post->topic_id;
$data=mysql_real_escape_string(serialize($post_meta_save));
$query="INSERT INTO `".$bb_table_prefix."postmeta`  (`post_id`,`topic_id`,`post_meta`) VALUES ('$post_id','$topic_id','$data') ON DUPLICATE KEY UPDATE `post_meta` = VALUES( `post_meta`)";
$bbdb->query($query);
if (defined('BACKPRESS_PATH')) {$bb10=true;} else {global $bb_post_cache; $bb10=false;}
if ($bb10) {wp_cache_set($post_id, $bb_post, 'bb_post' );} 
else {$bb_post_cache[$post_id]=$bb_post;}
foreach ($post_meta_save as $key=>$value) {$bb_post->$key=$value;}
$post_meta_save=array(); 	// can't unset global
if (isset($bb_post->author)) {  // unfortunate hardcode required
	$topic=get_topic($topic_id); $query="";
	if ($topic->topic_last_post_id==$post_id && $topic->topic_last_poster_name!=$bb_post->author) {
		$name=mysql_real_escape_string($bb_post->author);
		$query.=" topic_last_poster_name='$name' ";
	}
	if ($bb_post->post_position==1 && $topic->topic_poster_name!=$bb_post->author) {
		if (empty($name)) {$name=mysql_real_escape_string($bb_post->author);} else {$query.=" ,";}
		$query.=" topic_poster_name='$name' ";		
	}		
	if (!empty($query)) {$bbdb->query("UPDATE $bbdb->topics SET ".$query." WHERE topic_id=$topic_id LIMIT 1");}
}
}

function post_meta_error($error) {
bb_send_headers();
bb_get_header();
echo "<br clear='both' /><h2 id='register' style='margin-left:2em;'>".__("Error")."</h2><p align='center'><font size='+1'>".
	$error.", <br />
	<a href='javascript:history.go(-1)'>".__("please go back and try again")."</a>.
	</font></p><br />";
bb_get_footer();
exit;
}

function post_meta_form() {
global $post_meta,$bb_current_user,$bb_post,$post_id;
foreach ($post_meta as $key=>$value) {
	$required=""; foreach ($value->capability as $cap=>$mode) {if ($bb_current_user->has_cap($cap)) {$required="($mode)";}}	
	foreach ($value->attributes as $variable=>$content) {$$variable=$content;}
	if ($post_id && isset($bb_post->$key)) {$content=" value='".htmlspecialchars($bb_post->$key,ENT_QUOTES)."' ";} 
	else {$content=""; if (empty($required)) {continue;}}
	echo "<p><label for='$key'>$label:</label>
		<input type='text' id='post_meta_$key' name='$key'  size='".ceil($size/2)."' maxlength='$size' $content /> $required</p>";
}
}

function post_meta_install() {
global $bbdb,$bb_table_prefix; 
$bbdb->query("CREATE TABLE IF NOT EXISTS `".$bb_table_prefix."postmeta` (
		`post_id` 	int(10)        UNSIGNED NOT NULL default '0',
		`topic_id` 	int(10)        UNSIGNED NOT NULL default '0',
		`post_meta`   text			   NOT NULL default '',		
		PRIMARY KEY (`post_id`),
		INDEX (`topic_id`)
		) CHARSET utf8  COLLATE utf8_general_ci");	
}

function post_meta_cache_topic($topic_id=0) {
global $post_meta,$post_meta_cache,$bbdb,$bb_table_prefix,$posts, $bb_post, $topic; $where="";
if (empty($topic_id)) {return;}
if ($topic_id==$topic->topic_id && !empty($posts)) {
	foreach ($posts as $post) {$ids[$post->post_id]=$post->post_id;} 
	if (!empty($ids)) {$where="post_id IN (".implode(",",$ids).")";}
} else {$posts = get_thread( $topic_id); $where="topic_id=$topic_id";} 
// second pass to track which post_id have been checked, regardless if data present or not
if (empty($posts) || empty($where)) {return;}
foreach ($posts as $post) {$post_meta_cache[$post->post_id]=$post->post_id;}
$meta=$bbdb->get_results("SELECT post_id,post_meta FROM ".$bb_table_prefix."postmeta WHERE $where");
if (empty($meta)) {return;}
if (defined('BACKPRESS_PATH')) {$bb10=true;} else {global $bb_post_cache; $bb10=false;}
foreach ($posts as $key=>$post) {$index[$post->post_id]=$key;}
foreach($meta as $value) {
	$value->post_meta=unserialize($value->post_meta); 
	if (!isset($index[$value->post_id]) || empty($value->post_meta)) {continue;}	
	foreach ($value->post_meta as $key=>$variable) {if (!empty($variable)) {$posts[$index[$value->post_id]]->$key=$variable;}}
	if ($bb10) {wp_cache_set($value->post_id, $posts[$index[$value->post_id]], 'bb_post' );} 
	else {$bb_post_cache[$value->post_id]=$posts[$index[$value->post_id]];}	
}
// if (bb_current_user_can('administrate')) {print "<pre>"; print_r($posts); exit;}
}

function post_meta_cache_post($post_id=0) {	// fetch single post meta - to do
global $post_meta, $post_meta_cache, $bbdb,$bb_table_prefix,$posts, $bb_post, $topic; $where="";
if (empty($post_id)) {return;}
if (empty($bb_post->post_id) || $bb_post->post_id!=$post_id) {$bb_post=bb_get_post($post_id);}
$post_meta_cache[$bb_post->post_id]=$bb_post->post_id;
$meta=$bbdb->get_results("SELECT post_id,post_meta FROM ".$bb_table_prefix."postmeta WHERE post_id=$post_id LIMIT 1");
if (empty($meta)) {return;}
if (defined('BACKPRESS_PATH')) {$bb10=true;} else {global $bb_post_cache; $bb10=false;}
foreach($meta as $value) {
	$value->post_meta=unserialize($value->post_meta);
	if (empty($value->post_id) || empty($value->post_meta)) {continue;}
	foreach ($value->post_meta as $key=>$variable) {if (!empty($variable)) {$bb_post->$key=$variable;}}
	if ($bb10) {wp_cache_set($value->post_id, $bb_post, 'bb_post' );} 
	else {$bb_post_cache[$value->post_id]=$bb_post;}
}
}

function post_meta_allow_tags($text) {
	$tags=bb_allowed_tags(); unset($tags['br']);  unset($tags['p']); $tags = implode('|',array_keys($tags));
	$text=preg_replace(array("/<(\/)?b>/si","/<(\/)?i>/si"),array("<strong>","<em>"),$text);
	$text=preg_replace("/<\/?(?!($tags)).*?>/sim","", $text);
return $text;	
}

?>