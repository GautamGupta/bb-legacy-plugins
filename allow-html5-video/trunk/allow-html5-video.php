<?php
/*
Plugin Name: Allow HTML5 Video
Plugin URI: http://stateofaffairs.info/archive/html5-video-bbpress-plugin/
Description: Allows html5 video tags to be posted in your forums.
Author: Jeremy Winter
Author URI: http://stateofaffairs.info
Version: 0.9
*/

function allow_video_as_allowed_tags( $tags ) {
$tags['video'] = array('src' => array(), 'type' => array(), 'autoplay' => array(), 'poster' => array(), 'controls' => array(), 'width' => array(), 'height' => array() );
$tags['source'] = array('src' => array(), 'type' => array(), 'media' => array() );
return $tags;
}

add_filter( 'bb_allowed_tags', 'allow_video_as_allowed_tags');

?>