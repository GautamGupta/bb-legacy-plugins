<?php
/*
Plugin Name: bb Topic Views
Plugin URI: http://bbpress.org/plugins/topic/bb-topic-views/
Description: Counts the number of times a topic has been viewed, and allows the administrator to display the count in various places.
Author: Mike Wittmann, _ck_
Author URI: http://blog.wittmania.com/
Version: 1.6.4
*/

// Set this to zero if you DON'T want the view count automatically appended to the topic title.  Default is 1.
$append_to_title = 1;

/*  stop editing here */

// only executes if option is set to 1 above
if ($append_to_title && (is_front() || is_forum() || is_tags() || is_view())) {add_filter('topic_title', 'view_count_append_to_title', 99);}

add_filter('bb_head', 'update_view_count');
add_action('bb_init', 'views_session_check');
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'bb_topic_views_install');

//Force bbpress to open a session, if it hasn't already, which will help to avoid double-counting views (see below)
function views_session_check () {
	if( !isset( $_SESSION ) && is_topic()) {		// only start session if not already stared and it's a topic page
		// eaccelerator_set_session_handlers();	// todo: detect and work with memcaches
		// @session_cache_limiter('public');	// allows back button to work without losing form data - update: bad idea, causes other problems
		@session_start();
	}
}

if (!function_exists('is_tags')) {function is_tags() {return is_tag();}}	// older bbPress compatibility

function view_count_append_to_title ($title) {
/*This function appends the view count to the end of the title on the front, forum, and tags pages.  You can comment this out if you want 
to use the "show_view_count" function to place the view count somewhere else instead.*/

	global $topic;
	
	$view_count = get_view_count( $topic->topic_id );
	
	//Builds the text to be appended to the title	
	$count_display =" <em>(".$view_count . " views)</em>";
	
	//Makes this plugin play nice with the Page Links plugin by putting the pages links (if they exist) on a new line
	if (function_exists('page_links_add_links')) {
		$count_display .= "<br />";
	}
	
	$title .= $count_display;
	return $title;
}

function show_view_count () {
/*Use this function directly to display the view count somehere other than at the end of the title.  If you are going that route,
you will probably want to comment out the display_view_count_title function above*/

	global $topic;
	
	$view_count = get_view_count ( $topic->topic_id );
	echo $view_count;
}
	
function get_view_count ( $topic_id=0 ) {
	global $bbdb, $topic, $bb_topic_cache; $view_count=0;

	if (empty($topic_id)) {if (!empty($topic->topic_id)) {$topic_id=$topic->topic_id;} else {return 0;}}
	
	if (isset($topic->views) && $topic->topic_id===$topic_id) {$view_count=$topic->views;}	// bypass db for cached data
	
	elseif (defined('BACKPRESS_PATH')) {  	 // bbPress 1.0
	
		$temp_topic = wp_cache_get( $topic_id, 'bb_topic' ); 
	
		if (!empty($temp_topic->views)) {$view_count= $temp_topic->views;}		  // bypass db for cached data
	
		else {$view_count = $bbdb->get_var("SELECT meta_value FROM $bbdb->meta WHERE object_type='bb_topic' AND object_id = $topic_id AND meta_key='views' ");}

	} else {		// bbPress 0.9
	
		if (!empty($bb_topic_cache[$topic_id]->views)) {$view_count= $bb_topic_cache[$topic_id]->views;}	 // bypass db for cached data
	
		else {$view_count = $bbdb->get_var("SELECT meta_value FROM $bbdb->topicmeta WHERE topic_id = $topic_id AND meta_key='views'");}
	
	}
		
	// If it already set, it just returns the value

	if ($view_count<=0) { //If the view count hasn't bee initialized yet, this will initialize the value before it is returned
		$view_count = initialize_view_count ( $topic_id );
	}
		
	return $view_count;
}

function initialize_view_count( $topic_id ) {	//If the view count for a topic hasn't been set yet
 
	global $bbdb, $topic;
	
	$view_count = $topic->topic_posts; 	//Sets the new record to the number of posts that have been made in a topic
		
	//Adds the record to the DB so it isn't zero any longer
	/*  // this is broken somehow - occasionally inserts multiple topic_ids of zero because bbpress doesn't keep unique index on meta
	if (defined('BACKPRESS_PATH')) {  	// bbPress 1.0
	
	@$bbdb->query("INSERT INTO $bbdb->meta (object_id, object_type, meta_key, meta_value) VALUES ($topic_id, 'bb_topic', 'views', $view_count)");
	
	} else {		// bbPress 0.7 - 0.9
	
	@$bbdb->query("INSERT INTO $bbdb->topicmeta (topic_id, meta_key, meta_value) VALUES ($topic_id, 'views', $view_count)");
	
	}
	*/
	bb_delete_meta( $topic_id, 'views', 0, 'topic' );		
	bb_update_meta( $topic_id, 'views', $view_count, 'topic' );
	
	return $view_count;
}
	
function update_view_count() {
	global $bbdb, $topic, $topic_id;
	
	if (is_topic()) {			
		if (empty($topic_id)) {if (empty($topic)) {return;} else {$topic_id=$topic->topic_id;}}	// should never happen in bb_head but does for some reason in 1.0 ?
	
		if (empty($topic->views)) {$view_count=0;} else {$view_count=$topic->views;}

		if ($view_count>=1) {
			
			$last_topic_id = $_SESSION['last_topic_id'];	// Pulls the session variable for the last topic we viewed
			if ($topic_id != $last_topic_id) { 	//Makes sure we aren't viewing a different page of the same topic.
			
				// Add 1 to $view_count and update the DB
				$topic->views++; 

				if (defined('BACKPRESS_PATH')) {   // bbPress 1.0
			
				@$bbdb->query("UPDATE $bbdb->meta SET meta_value=meta_value+1 WHERE object_type = 'bb_topic' AND object_id = $topic_id AND meta_key='views' LIMIT 1");
			
				} else { // 0.7 - 0.9
			
				@$bbdb->query("UPDATE $bbdb->topicmeta SET meta_value=meta_value+1 WHERE topic_id = $topic_id AND meta_key='views' LIMIT 1");
			
				}
			
			}			
						
		} else {
			initialize_view_count ( $topic_id); // Initializes the value so the next time it is displayed it will not be zero
		}
		$_SESSION['last_topic_id'] = $topic_id;	 //Sets the session variable so it is there the next time we view a topics page
	}
}

function most_viewed_list ( $list_length = '10', $before_list = '<ul>', $after_list = '</ul>', $before_item = '<li>', $after_item = '</li>') {
	
	global $bbdb;
	
	if (defined('BACKPRESS_PATH')) {     // bbPress 1.0
	
	$most_viewed = (array) $bbdb->get_results("SELECT object_id as topic_id, meta_value FROM $bbdb->meta WHERE object_type='bb_topic' AND meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC");
	
	} else {

	$most_viewed = (array) $bbdb->get_results("SELECT topic_id, meta_value FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC");
	
	}
		
	$most_viewed = array_slice($most_viewed, 0, $list_length);
	
	echo ($before_list);
	 
	foreach ($most_viewed as $row) {
		$topic_id = $row->topic_id;
		$topic = get_topic ($topic_id);
		$uri = get_topic_link ($topic_id);
		echo ($before_item);
		echo ('<a href="' . $uri . '">' . $topic->topic_title . '</a>: ' . $row->meta_value . ' views');
		echo ($after_item . "\n");
	}
	
	echo ($after_list);
}

function most_viewed_table ( $list_length = '10') {
	global $bbdb;
	
	if (defined('BACKPRESS_PATH')) {     // bbPress 1.0
	
	$most_viewed = (array) $bbdb->get_results("SELECT object_id as topic_id, meta_value FROM $bbdb->meta WHERE object_type='bb_topic' AND meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC");
	
	} else {

	$most_viewed = (array) $bbdb->get_results("SELECT topic_id, meta_value FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC");
	
	}
		
	$most_viewed = array_slice($most_viewed, 0, $list_length);
	
	?>
		<style type="text/css">
		#most_viewed td { padding: 5px 10px; }
		#most_viewed tr:hover { background: #e4f3e1; }
		#most_viewed th {
			border-bottom: 1px solid #aaa;
			background: #ddd;
			font: 11px Verdana,Arial,Helvetica,sans-serif;
			padding: 5px 10px;
			text-transform: uppercase;
		}
		#most_viewed {
			background: #f7f7f7;
			margin-bottom: 2em;
			width: 100%;
		}

		</style>

	<h2>Most Viewed Posts (table)</h2>
	<table id="most_viewed">
	<tr><th>Topic</th><th>Views</th><th>Posts</th><th>Last Poster</th><th>Freshness</th></tr>
	
	<?php
	foreach ($most_viewed as $row) { 
		$topic_id = $row->topic_id;
		$topic = get_topic ($topic_id);
		?>
		<tr<?php topic_class($topic_id); ?>>
			<td><a href="<?php topic_link($topic_id); ?>"><?php topic_title($topic_id); ?></a></td>
			<td class="num"><?php show_view_count($topic_id); ?></td>
			<td class="num"><?php topic_posts($topic_id); ?></td>
			<td class="num"><?php topic_last_poster($topic_id); ?></td>
			<td class="num"><small><?php topic_time($topic_id); ?></small></td>
		</tr> 
	<?php 
	} //end foreach ?>
	</table>
	<?php
}

function bb_topic_views_install() {
global $bbdb;
	if (defined('BACKPRESS_PATH')) {  	 // bbPress 1.0	
		$query="DELETE FROM $bbdb->meta WHERE object_type='bb_topic' AND meta_key='views' AND cast(meta_value as UNSIGNED)=0 ";
	} else {		// bbPress 0.9			
		$query="DELETE FROM $bbdb->topicmeta WHERE meta_key='views' AND cast(meta_value as UNSIGNED)=0 ";
	}
	$bbdb->get_var($query);	// db cleanup for duplicate entries

// SELECT * FROM bb_topicmeta WHERE meta_key='views' AND topic_id IN (SELECT topic_id FROM `bb_topicmeta` WHERE meta_key='views' group by topic_id having count(meta_value)>1 ORDER BY topic_id)
}	

?>