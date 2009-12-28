<?
/*
Plugin Name: bb Topic Icons
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/topic-icons
Description: Adds configurable icons next to topics based on their status
Author: Paul Hawke
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.3
*/

/****************************************************************************
 *
 * Configure the following constants to fine-tune the CSS classes that are
 * generated, the icon filenames that are used, and the text used in the
 * legend (if you have one displayed).  Note: filenames are likely to be
 * taken away in a future version and replaced with the concept of "icon sets"
 * whose filenames are fixed, so dont get used to editing the filenames,
 * as this will break in future versions.
 *
 ****************************************************************************/

// css class for "normal" topics
define( NORMAL_TOPIC_CLASS, 'normal-post' );

// css class for "closed" topics
define( CLOSED_TOPIC_CLASS, 'closed-post' );

// css class for "busy" topics - more posts than the
// busy threshold, defined below.
define( BUSY_TOPIC_CLASS, 'hot-post' );

// css class for "sticky" topics
define( STICKY_TOPIC_CLASS, 'sticky-post' );

// css class for the unsorted list used in the legend display
define( LEGEND_CLASS, 'topic_icon_legend' );

/****************************************************************************/

// busy threshold - a topic with more posts than this is counted as "busy"
// for purposes of picking an icon.
define( BUSY_THRESHOLD, 15 );

/****************************************************************************/

// description used in the legend for a "normal" topic
define( NORMAL_TOPIC_DESC, 'Normal' );

// description used in the legend for a "closed" topic
define( CLOSED_TOPIC_DESC, 'Closed' );

// description used in the legend for a "busy" topic - more posts than the
// busy threshold, defined above.
define( BUSY_TOPIC_DESC, 'Very Busy' );

// description used in the legend for a "sticky" topic
define( STICKY_TOPIC_DESC, 'Sticky' );

/****************************************************************************/

// width of the images, in pixels
define( ICON_WIDTH, '20' );

// height of the images, in pixels
define( ICON_HEIGHT, '20' );

// gap between the icon and the topic text, in pixels
define( ICON_TEXT_GAP, '6' );

/****************************************************************************/

// image file name for the "normal" topic icon
define( NORMAL_TOPIC_IMAGE, 'topic.png' );

// image file name for the "closed" topic icon
define( CLOSED_TOPIC_IMAGE, 'locked.png' );

// image file name for the "busy" topic icon
define( BUSY_TOPIC_IMAGE, 'hot.png' );

// image file name for the "sticky" topic icon
define( STICKY_TOPIC_IMAGE, 'sticky.png' );

// the URL base for where to find the default icon set.
define( ICON_SET_URL_BASE, BB_PLUGIN_URL.'bb-topic-icons/icon-sets/' );

/****************************************************************************
 *
 * Shouldnt be much need to edit anything beyond this point - configuration
 * is all done via the constants (above) and through and admin area page in
 * bbPress at runtime.
 *
 ****************************************************************************/

require( 'interface.status-interpreter.php' );
require( 'interface.status-renderer.php' );
require( 'class.default-status-interpreter.php' );
require( 'class.default-status-renderer.php' );

$status_interpreter = new DefaultStatusInterpreter(3);
$status_renderer = new DefaultStatusRenderer();

function topic_icons_legend() {
	$icon_set_name = topic_icons_get_active_icon_set();
	$icon_set_url = ICON_SET_URL_BASE . $icon_set_name;
	$images = array(STICKY_TOPIC_IMAGE, NORMAL_TOPIC_IMAGE, BUSY_TOPIC_IMAGE, CLOSED_TOPIC_IMAGE);
	$descriptions = array(STICKY_TOPIC_DESC, NORMAL_TOPIC_DESC, BUSY_TOPIC_DESC, CLOSED_TOPIC_DESC);
	
	echo '<ul id="'.LEGEND_CLASS.'">';
	for ($i=0; $i < count($images); $i++) {
		echo '<li><img src="'.$icon_set_url.'/'.$images[$i];
		echo '" width="'.ICON_WIDTH.'" height="'.ICON_HEIGHT.'" align="absmiddle" alt="';
		echo $descriptions[$i].' Topic Icon">&nbsp;';
		echo $descriptions[$i].' Topic</li>';
	}
	echo '</ul>';
}

function topic_icons_css() {
	$icon_set_name = topic_icons_get_active_icon_set();
	$icon_set_url = ICON_SET_URL_BASE . $icon_set_name;
?>
<style type="text/css"><!--
.<?php echo NORMAL_TOPIC_CLASS; ?>, .<?php echo BUSY_TOPIC_CLASS; ?>, .<?php echo STICKY_TOPIC_CLASS; ?>, .<?php echo CLOSED_TOPIC_CLASS; ?> {
	width: <?php echo ICON_WIDTH; ?>px;
	height: <?php echo ICON_HEIGHT; ?>px;
	margin-right: <?php echo ICON_TEXT_GAP; ?>px;
	position: relative;
	float: left;
}

.<?php echo NORMAL_TOPIC_CLASS; ?> {
	background: url(<?php echo $icon_set_url.'/'.NORMAL_TOPIC_IMAGE; ?>) no-repeat;
}

.<?php echo BUSY_TOPIC_CLASS; ?> {
	background: url(<?php echo $icon_set_url.'/'.BUSY_TOPIC_IMAGE; ?>) no-repeat;
}

.<?php echo STICKY_TOPIC_CLASS; ?> {
	background: url(<?php echo $icon_set_url.'/'.STICKY_TOPIC_IMAGE; ?>) no-repeat;
}

.<?php echo CLOSED_TOPIC_CLASS; ?> {
	background: url(<?php echo $icon_set_url.'/'.CLOSED_TOPIC_IMAGE; ?>) no-repeat;
}
--></style>
<?php
}

function topic_icons_get_active_icon_set() {
	// for now - later this will be dynamic, read from options, etc.
	return 'default';
}

function topic_icons_label( $label ) {
	global $topic, $status_interpreter, $status_renderer;
	
	$status = $status_interpreter->getStatus(bb_get_location(), $topic);

	$output = $status_renderer->renderStatus($status);
	
	return sprintf(__('<div class="%s">%s</div>'), $output, $label);
}

function topic_icons_init( ) {
	remove_filter('bb_topic_labels', 'bb_closed_label', 10);
	remove_filter('bb_topic_labels', 'bb_sticky_label', 20);

	add_filter('bb_topic_labels', 'topic_icons_label', 11);

	add_action('bb_head', 'topic_icons_css');
}

topic_icons_init();

?>
