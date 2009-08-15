<?php
/*
Plugin Name: My Views
Description:  My Views is a powerful addition to the default "views" in bbPress. It will let you customize output and adds several new views.
Plugin URI:  http://bbpress.org/plugins/topic/my-views
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.4
*/

function my_views_init() {	//	to do: make much nicer with admin interface
global $my_views;

$my_views['remove_views']=array("weird-view1","weird-view2");	// remove any views by slug name, built-in or from my-views, example: "untagged"

$my_views['prefered_order']=array(	// force views to list in the order that you desire
	"latest-discussions","subscribed-topics","no-replies","untagged","my-topics","my-posts","new-posts","most-views","most-posts","least-views","least-posts",
	"polls","support-forum-no","random-topics","leaderboard","installed-plugins","available-plugins","installed-themes","available-themes","statistics"
	);

$my_views['rss']=array(	//  this is a list of custom views where RSS is handled by My Views
	"latest-discussions","subscribed-topics","my-topics","my-posts","new-posts","most-views","most-posts","least-views","least-posts","polls","random-topics"
);

$my_views['view_pages_label']=__("pages: ");

$my_views['access_level']=array();	// not implemented yet


/*        stop editing - seriously, you can't do anything helpful below yet        */


/*  optional header and footer html code - not used publicly
$my_views['header']='
<div class="post raised" >
<b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b>
<div class="boxcontent">
';

$my_views['footer']='
</div>
<b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b>
</div>
';
*/

if (is_callable('bb_register_view')) {global $bb_views; $my_views['new_order']=$bb_views;} else {global $views; $my_views['new_order']=$views;}
$my_views['prefered_order']=array_reverse($my_views['prefered_order']);
$my_views['new_order']=array_reverse($my_views['new_order']);
foreach ($my_views['prefered_order'] as $view) {	// change display order    	$bb_views[$slug]   vs.  $view[$slug]
	if (isset($my_views['new_order'][$view])) {		
		$temp=$my_views['new_order'][$view];
		unset($my_views['new_order'][$view]);
		$my_views['new_order'][$view]=$temp;
	}
}
$my_views['new_order']=array_reverse($my_views['new_order']);
if (is_callable('bb_register_view')) {global $bb_views; $bb_views=$my_views['new_order'];} else {global $views; $views=$my_views['new_order'];}

foreach ($my_views['remove_views'] as $view) {
	if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
		bb_deregister_view($view);		 
	} else {		// Build 214-875	(0.8.2.1)
		global $views;
		unset($views[$view]);    
	}
}
if (!is_callable('bb_register_view')) {return $views;}	
}
if (!is_callable('bb_register_view')) {add_filter('bb_views','my_views_init',1000 );} else {add_action('bb_init', 'my_views_init',1000);}

function my_views_unsticky($view,$page=1){	// quick way to fix incorrect stickies on top of custom views
// todo: make this find real stickies within results and re-sticky-fy
if (intval($page)>1 || !in_array($view,array('no-replies','untagged',''))) {unset($GLOBALS['stickies']);}
}
add_action( 'bb_custom_view', 'my_views_unsticky',200,2);

function my_views_view_pages_label($passthrough) {
global $my_views;
if (strlen($passthrough)) {$passthrough=$my_views['view_pages_label'].$passthrough;}	
return $passthrough;
}
add_filter( 'view_pages', 'my_views_view_pages_label',200 );

function my_views_add_view_title($title) {
if (is_view()) {$title =  get_view_name(). ' &laquo; ' . bb_get_option( 'name' ); } 
return $title;
}
add_filter( 'bb_get_title', 'my_views_add_view_title' );

function my_views_dropdown($display=1) {	/*  makes views available as dropdown list anywhere you put <?php my_views_dropdown(); ?> */
	$views_dropdown='<form name="views_dropdown" id="views_dropdown">'
		.'<select  size=1  name="views_dropdown_select" onChange="if (this.selectedIndex >0) window.location=this.options[this.selectedIndex].value">'		
		.'<option style="text-indent: 1em;padding:2px" value="#">Show me...  </option>';		
	$views=get_views(); 
	foreach ($views as $view => $title ) {$views_dropdown.='<option  style="text-indent: 1em;padding:2px" value="'.get_view_link($view).'">'.$views[$view].'</option>';}
	$views_dropdown.='</select></form>'; 
if ($display) {echo $views_dropdown;} else {return $views_dropdown;}
}

function my_views_header($bbcrumb=false) {	/*  adds proper h2 header & dropdown to view.php template(s)  put <?php my_views_header(); ?> */	
if (is_view()) : 
if ($bbcrumb) :	
?>
<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php view_name(); ?></h3>
<?php 	
global $my_views;
if (isset($my_views['header'])) {echo $my_views['header'];}
endif;	?>
<div class="my_views_header"><h2 style="float:left;width:50%;"><?php view_name(); ?></h2><div style="float:right"><?php my_views_dropdown(); ?></div></div>
<br clear=both>
<?php
endif;
}

function my_views_footer() {
global $my_views;
if (isset($my_views['footer'])) {echo $my_views['footer'];}
}

function my_views_table_sort() {	// makes the views table sortable via client-side javascript
	if (is_view()) {	
		// $url=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/my-views-tinytable-sort.js?'.time(); 	
		$url=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/my-views-table-sort.js?1a'; 	
		echo '<script type="text/javascript" defer="defer" src="'.$url.'"></script>';
	}
} 
add_action('bb_foot', 'my_views_table_sort');


function my_views_rss() {
global $my_views, $bb_db_override, $view, $bb_views, $title, $link, $link_self, $description, $feed, $feed_id, $topics, $posts;
if ($feed!='view') {return;}
if (!in_array($feed_id,$my_views['rss'])) {return;}
$view=$feed_id; 
$title = $bb_views[$feed_id]['title'];
$bb_db_override=true;
do_action( 'bb_custom_view', $view );
if (empty($posts)) {
$posts=array(); 
if (!empty($topics)) {	// adds last posts to RSS - could optionally use  first_posts
	bb_cache_last_posts();
	foreach ($topics as $topic) {$posts[]=bb_get_last_post($topic->topic_id);}
}
}
}
add_action( 'bb_rss.php_pre_db', 'my_views_rss' );

?>