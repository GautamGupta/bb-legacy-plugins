<?php
/*
Plugin Name: Social It
Plugin URI: http://www.gaut.am/bbpress/plugins/social-it
Description: Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, etc. See <a href="admin-base.php?plugin=socialit_settings_page">configuration panel</a> for more settings. This plugin is inspired from the <a href="http://sexybookmarks.net/">SexyBookmarks plugin for Wordpress</a>. This plugin is also compatible with <a href="http://bbpress.org/plugins/topic/support-forum/">Support Forum plugin</a>.
Version: 1.1
Author: Gautam
Author URI: http://www.gaut.am/

	Original Social It bbPress Plugin Copyright 2009 Gautam (email : admin@gaut.am) (website: http://gaut.am)
	Original SexyBookmarks Plugin Copyright 2009 Eight7Teen (email : josh@eight7teen.com), Norman Yung (www.robotwithaheart.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/*
 Main PHP File for
 Social It plugin (for bbPress) by www.gaut.am
*/

define('SOCIALIT_OPTIONS','Social-It');
define('SOCIALIT_vNum','1.1');
define('SOCIALIT_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/'); //thanks ck for this
require_once('bookmarks-data.php');

//reload
$socialit_plugopts = bb_get_option(SOCIALIT_OPTIONS);
if(!$socialit_plugopts){
	//add defaults to an array
	$socialit_plugopts = array(
		'reloption' => 'nofollow', // 'nofollow', or ''
		'targetopt' => '_blank', // '_blank' or '_self'
		'bgimg-yes' => 'yes', // 'yes' or blank
		'bgimg' => 'caring', // 'sexy', 'caring', 'wealth'
		'shorty' => 'trim',
		'topic' => '1',
		'bookmark' => array_keys($socialit_bookmarks_data),
		'xtrastyle' => '',
		'feed' => '0', // 1 or 0
		'expand' => '1',
		'autocenter' => '0',
		'ybuzzcat' => 'science',
		'ybuzzmed' => 'text',
		'twittcat' => 'Internet',
		'default_tags' => '',
		'warn-choice' => '',
		'sfpnonres' => 'yes',
		'sfpres' => 'yes',
		'sfpnonsup' => 'yes',
		'shorturls' => array(),
	);
	bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts);
}

//curl, file get contents or nothing, used for short url and for updater
function socialit_nav_browse($url){
	if (function_exists('curl_init')) {
		// Use cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$source = curl_exec($ch);
		curl_close($ch);
		
	} elseif (function_exists('file_get_contents')) { // use file_get_contents()
		$source = file_get_contents($url);
	} else {
		$source = null;
	}
	return $source;
}

//check for updates, as bbpress doesnt check itself :(

function socialit_update_check(){
	$latest_ver = trim(socialit_nav_browse("http://gaut.am/uploads/plugins/updater.php?pid=1&chk=ver&soft=bb&current=".SOCIALIT_vNum));
	if($latest_ver){
		if(SOCIALIT_vNum < $latest_ver){
			return $latest_ver;
		}else{
			return false;
		}
	}else{
		return false;
	}
}


//add sidebar link to settings page
function socialit_menu_link() {
	if (function_exists('bb_admin_add_submenu')) {
		bb_admin_add_submenu( __( 'Social It' ), 'administrate', 'socialit_settings_page', 'options-general.php' );
	}
}

function socialit_network_input_select($name, $hint) {
	global $socialit_plugopts;
	return sprintf('<label class="%s" title="%s"><input %sname="bookmark[]" type="checkbox" value="%s"  id="%s" /></label>',
		$name,
		$hint,
		@in_array($name, $socialit_plugopts['bookmark'])?'checked="checked" ':"",
		$name,
		$name
	);
}

//get current page rss link, code taken from functions.bb-template.php in bb-includes, posts' rss prefered instead of topics'
function socialit_get_current_rss_link(){
	switch (bb_get_location()) {
		case 'profile-page':
			if ( $tab = isset($_GET['tab']) ? $_GET['tab'] : bb_get_path(2) )
				if ($tab != 'favorites')
					break;
			
			$feed = get_favorites_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'topic-page':
			$feed = get_topic_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'tag-page':
			if (bb_is_tag()) {
				$feed = bb_get_tag_posts_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			}
			break;
		
		case 'forum-page':
			$feed = bb_get_forum_posts_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'front-page':
			$feed = bb_get_posts_rss_link(BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'view-page':
			global $bb_views, $view;
			if ($bb_views[$view]['feed']) {
				$feed = bb_get_view_rss_link(null, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			}
			break;
		default:
			$feed = bb_get_posts_rss_link(BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
	}
	return $feed;
}

//gets current URL, returns string, taken from Support Forum Plugin
function socialit_get_current_url()
	{
		$schema = 'http://';
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$schema = 'https://';
		}
		if ($querystring = $_SERVER['QUERYSTRING']) {
			$querystring = ltrim($querystring, '?&');
			$querystring = rtrim($querystring, '&');
			if ($querystring) {
				$querystring = '?' . $querystring;
			}
		}
		$uri = $schema . $_SERVER['HTTP_HOST'] . rtrim($_SERVER['REQUEST_URI'], '?&') . $querystring;
		return $uri;
	}

//write settings page
function socialit_settings_page() {
	echo '<h2 class="socialitlogo">Social It</h2>';
	global $socialit_plugopts, $socialit_bookmarks_data, $bbdb;
	$donate_link = "https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6590760";
	$twitter_link = "http://twitter.com/gautam_2011";

	// processing form submission
	$status_message = "";
	$error_message = "";
	
	//check for updates
	if(socialit_update_check()){ 
		//update available
		echo '
		<div id="update-message" class="socialit-warning">
			<div class="dialog-left">
				<img src="'.SOCIALIT_PLUGPATH.'images/icons/error.png" class="dialog-ico" alt=""/>
				New version of Social It is available! Please download the latest version <a href="http://bbpress.org/plugins/topic/social-it/">here</a>.
			</div>
		</div>'; //box cant be closed
	}
	
	if(isset($_POST['save_changes'])) {
		$status_message = 'Your changes have been saved successfully! | Maybe you would consider <a href="'.$donate_link.'">donating</a> or following me on <a href="'.$twitter_link.'">Twitter</a>?';
		//was there an error?
		if($_POST['bookmark'] == '') {
			$error_message = 'You can\'t display the menu if you don\'t choose a few sites to add to it!';
		}
		elseif($_POST['twittcat'] == '' && in_array('socialit-twittley', $socialit_plugopts['bookmark'])) {
			$error_message = 'You need to select the primary category for any articles submitted to Twittley.';
		}
		elseif($_POST['defaulttags'] == '' && in_array('socialit-twittley', $socialit_plugopts['bookmark'])) {
			$error_message = 'You need to set at least 1 default tag for any articles submitted to Twittley.';
		}
		else {
			$socialit_plugopts['topic'] = $_POST['topic'];
			$socialit_plugopts['xtrastyle'] = $_POST['xtrastyle'];
			$socialit_plugopts['reloption'] = $_POST['reloption'];
			$socialit_plugopts['targetopt'] = $_POST['targetopt'];
			$socialit_plugopts['bookmark'] = $_POST['bookmark'];
			$socialit_plugopts['shorty'] = $_POST['shorty'];
			$socialit_plugopts['twittid'] = $_POST['twittid'];
			$socialit_plugopts['twittcat'] = $_POST['twittcat'];
			$socialit_plugopts['defaulttags'] = $_POST['defaulttags'];
			$socialit_plugopts['ybuzzcat'] = $_POST['ybuzzcat'];
			$socialit_plugopts['ybuzzmed'] = $_POST['ybuzzmed'];
			$socialit_plugopts['bgimg-yes'] = $_POST['bgimg-yes'];
			$socialit_plugopts['bgimg'] = $_POST['bgimg'];
			$socialit_plugopts['feed'] = $_POST['feed'];
			$socialit_plugopts['expand'] = $_POST['expand'];
			$socialit_plugopts['autocenter'] = $_POST['autocenter'];
			$socialit_plugopts['sfpnonres'] = $_POST['sfpnonres'];
			$socialit_plugopts['sfpres'] = $_POST['sfpres'];
			$socialit_plugopts['sfpnonsup'] = $_POST['sfpnonsup'];
			bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts);
		}
		
		if(in_array('socialit-yahoobuzz', $socialit_plugopts['bookmark'])) {
			$ybuzz_default_class = "";
		}
		else {
			$ybuzz_default_class = "hidden";
		}
		
		if ($_POST['clearShortUrls']) {
			$count = count($socialit_plugopts['shorturls']);
			$socialit_plugopts['shorturls'] = array();
			bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts);
			echo '<div id="clearurl" class="socialit-warning"><div class="dialog-left"><img src="'.SOCIALIT_PLUGPATH.'images/icons/warning.png" class="dialog-ico" alt=""/>'.$count.' Short URL(s) have been reset.</div><div class="dialog-right"><img src="'.SOCIALIT_PLUGPATH.'images/icons/warning-delete.jpg" class="del-x" alt=""/></div></div><div style="clear:both;"></div>';
		}
	}

	//if there was an error,
	if ($error_message != '') {
		echo '
		<div id="message" class="socialit-error">
			<div class="dialog-left">
				<img src="'.SOCIALIT_PLUGPATH.'images/icons/error.png" class="dialog-ico" alt=""/>
				'.$error_message.'
			</div>
			<div class="dialog-right">
				<img src="'.SOCIALIT_PLUGPATH.'images/icons/error-delete.jpg" class="del-x" alt="X"/>
			</div>
		</div>';
	} elseif ($status_message != '') {
		echo '
		<div id="message" class="socialit-success">
			<div class="dialog-left">
				<img src="'.SOCIALIT_PLUGPATH.'images/icons/success.png" class="dialog-ico" alt=""/>
				'.$status_message.'
			</div>
			<div class="dialog-right">
				<img src="'.SOCIALIT_PLUGPATH.'images/icons/success-delete.jpg" class="del-x" alt="X"/>
			</div>
		</div>';
	}
?>
<form name="social-it" id="social-it" action="" method="post">
	<div id="socialit-col-left">
		<ul id="socialit-sortables">
			<li>
				<div class="box-mid-head" id="iconator">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/globe-plus.png" alt="" class="box-icons" />
					<h2>Enabled Networks</h2>
						<div class="bnav">
							<a href="javascript:void(null);" class="toggle" id="gle1">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
							</a>
						</div>
				</div>
				<div class="box-mid-body iconator" id="toggle1">
					<div class="padding">
						<p>Select the Networks to display. Drag to reorder.</p>
						<div id="socialit-networks">
							<?php
								foreach ($socialit_plugopts['bookmark'] as $name) print socialit_network_input_select($name, $socialit_bookmarks_data[$name]['check']);
								$unused_networks=array_diff(array_keys($socialit_bookmarks_data), $socialit_plugopts['bookmark']);
								foreach ($unused_networks as $name) print socialit_network_input_select($name, $socialit_bookmarks_data[$name]['check']);
							?>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/wrench-screwdriver.png" alt="" class="box-icons" />
					<h2>Functionality Settings</h2>
						<div class="bnav">
							<a href="javascript:void(null);" class="toggle" id="gle2">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
							</a>
						</div>
				</div>
				<div class="box-mid-body" id="toggle2">
					<div class="padding">
						<div class="dialog-box-warning" id="clear-warning">
							<div class="dialog-left">
								<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/warning.png" class="dialog-ico" alt=""/>
								This will clear <u>ALL</u> short URLs. - Are you sure?
							</div>
							<div class="dialog-right">
								<label><input name="warn-choice" id="warn-yes" type="radio" value="yes" />Yes</label> &nbsp;<label><input name="warn-choice" id="warn-cancel" type="radio" value="cancel" />Cancel</label>
							</div>
						</div>  
						<div id="twitter-defaults">
							<h3>Twitter Options:</h3>
							<label for="twittid">Twitter ID:</label>
							<input type="text" id="twittid" name="twittid" value="<?php echo $socialit_plugopts['twittid']; ?>" />
							<div class="clearbig"></div>
							<label for="shorty">Which URL Shortener?</label>
							<select name="shorty" id="shorty">
								<?php /*<option <?php echo (($socialit_plugopts['shorty'] == "tflp")? 'selected="selected"' : ""); ?> value="tflp">Twitter Friendly Links Plugin</option>*/ ?>
								<option <?php echo (($socialit_plugopts['shorty'] == "trim")? 'selected="selected"' : ""); ?> value="trim">http://tr.im</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "snip")? 'selected="selected"' : ""); ?> value="snip">http://snipr.com</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "supr")? 'selected="selected"' : ""); ?> value="supr">http://su.pr</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "e7t")? 'selected="selected"' : ""); ?> value="e7t">http://e7t.us</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "shortto")? 'selected="selected"' : ""); ?> value="shortto">http://short.to</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "cligs")? 'selected="selected"' : ""); ?> value="cligs">http://cli.gs</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "rims")? 'selected="selected"' : ""); ?> value="rims">http://ri.ms</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "tinyarrow")? 'selected="selected"' : ""); ?> value="tinyarrow">http://tinyarro.ws</option>
								<option <?php echo (($socialit_plugopts['shorty'] == "tiny")? 'selected="selected"' : ""); ?> value="tiny">http://tinyurl.com</option>
							</select>
							<label for="clearShortUrls" id="clearShortUrlsLabel"><input name="clearShortUrls" id="clearShortUrls" type="checkbox"/>Reset all Short URLs</label>
						<div class="clearbig"></div>
						</div>
						<div id="ybuzz-defaults">
							<h3>Yahoo! Buzz Defaults:</h3>
							<label for="ybuzzcat">Default Content Category: </label>
							<select name="ybuzzcat" id="ybuzzcat">
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "entertainment")? 'selected="selected"' : ""); ?> value="entertainment">Entertainment</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "lifestyle")? 'selected="selected"' : ""); ?> value="lifestyle">Lifestyle</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "health")? 'selected="selected"' : ""); ?> value="health">Health</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "usnews")? 'selected="selected"' : ""); ?> value="usnews">U.S. News</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "business")? 'selected="selected"' : ""); ?> value="business">Business</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "politics")? 'selected="selected"' : ""); ?> value="politics">Politics</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "science")? 'selected="selected"' : ""); ?> value="science">Sci/Tech</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "world_news")? 'selected="selected"' : ""); ?> value="world_news">World</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "sports")? 'selected="selected"' : ""); ?> value="sports">Sports</option>
								<option <?php echo (($socialit_plugopts['ybuzzcat'] == "travel")? 'selected="selected"' : ""); ?> value="travel">Travel</option>
							</select>
							<div class="clearbig"></div>
							<label for="ybuzzmed">Default Media Type: </label>
							<select name="ybuzzmed" id="ybuzzmed">
								<option <?php echo (($socialit_plugopts['ybuzzmed'] == "text")? 'selected="selected"' : ""); ?> value="text">Text</option>
								<option <?php echo (($socialit_plugopts['ybuzzmed'] == "image")? 'selected="selected"' : ""); ?> value="image">Image</option>
								<option <?php echo (($socialit_plugopts['ybuzzmed'] == "audio")? 'selected="selected"' : ""); ?> value="audio">Audio</option>
								<option <?php echo (($socialit_plugopts['ybuzzmed'] == "video")? 'selected="selected"' : ""); ?> value="video">Video</option>
							</select>
						<div class="clearbig"></div>
						</div>
						<div id="twittley-defaults">
							<h3>Twittley Defaults:</h3>
							<label for="twittcat">Primary Content Category: </label>
							<select name="twittcat" id="twittcat">
								<option <?php echo (($socialit_plugopts['twittcat'] == "Technology")? 'selected="selected"' : ""); ?> value="Technology">Technology</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "World &amp; Business")? 'selected="selected"' : ""); ?> value="World &amp; Business">World &amp; Business</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Science")? 'selected="selected"' : ""); ?> value="Science">Science</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Gaming")? 'selected="selected"' : ""); ?> value="Gaming">Gaming</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Lifestyle")? 'selected="selected"' : ""); ?> value="Lifestyle">Lifestyle</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Entertainment")? 'selected="selected"' : ""); ?> value="Entertainment">Entertainment</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Sports")? 'selected="selected"' : ""); ?> value="Sports">Sports</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Offbeat")? 'selected="selected"' : ""); ?> value="Offbeat">Offbeat</option>
								<option <?php echo (($socialit_plugopts['twittcat'] == "Internet")? 'selected="selected"' : ""); ?> value="Internet">Internet</option>
							</select>
							<div class="clearbig"></div>
							<p id="tag-info" class="hidden">
								Enter a comma separated list of general tags which describe your site's posts as a whole. Try not to be too specific, as one post may fall into different "tag categories" than other posts.<br />								
								This list is primarily used as a failsafe in case you forget to enter WordPress tags for a particular post, in which case this list of tags would be used so as to bring at least *somewhat* relevant search queries based on the general tags that you enter here.<br /><span title="Click here to close this message" class="dtags-close">[close]</span>
							</p>
							<label for="defaulttags">Default Tags: </label>
							<input type="text" name="defaulttags" id="defaulttags" value="<?php echo $socialit_plugopts['defaulttags']; ?>" /><img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/question-frame.png" class="dtags-info" title="Click here for help with this option" alt="Click here for help with this option" />
							<div class="clearbig"></div>
						</div>
						<div id="genopts">
							<h3>General Functionality Options:</h3>
							<span class="socialit_option">Add nofollow to the links?</span>
							<label><input <?php echo (($socialit_plugopts['reloption'] == "nofollow")? 'checked="checked"' : ""); ?> name="reloption" id="reloption-yes" type="radio" value="nofollow" /> Yes</label>
							<label><input <?php echo (($socialit_plugopts['reloption'] == "")? 'checked="checked"' : ""); ?> name="reloption" id="reloption-no" type="radio" value="" /> No</label>
							<span class="socialit_option">Open links in new window?</span>
							<label><input <?php echo (($socialit_plugopts['targetopt'] == "_blank")? 'checked="checked"' : ""); ?> name="targetopt" id="targetopt-blank" type="radio" value="_blank" /> Yes</label>
							<label><input <?php echo (($socialit_plugopts['targetopt'] == "_self")? 'checked="checked"' : ""); ?> name="targetopt" id="targetopt-self" type="radio" value="_self" /> No</label>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/palette.png" alt="" class="box-icons" />
					<h2>General Look &amp; Feel</h2>
						<div class="bnav">
							<a href="javascript:void(null);" class="toggle" id="gle3">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
							</a>
						</div>
				</div>
				<div class="box-mid-body" id="toggle3">
					<div class="padding">
						<span class="socialit_option">Animate-expand multi-lined bookmarks?</span>
						<label><input <?php echo (($socialit_plugopts['expand'] == "1")? 'checked="checked"' : ""); ?> name="expand" id="expand-yes" type="radio" value="1" />Yes</label>
						<label><input <?php echo (($socialit_plugopts['expand'] != "1")? 'checked="checked"' : ""); ?> name="expand" id="expand-no" type="radio" value="0" />No</label>
						<span class="socialit_option">Auto-center the bookmarks?</span>
						<label><input <?php echo (($socialit_plugopts['autocenter'] == "1")? 'checked="checked"' : ""); ?> name="autocenter" id="autocenter-yes" type="radio" value="1" />Yes</label>
						<label><input <?php echo (($socialit_plugopts['autocenter'] != "1")? 'checked="checked"' : ""); ?> name="autocenter" id="autocenter-no" type="radio" value="0" />No</label>
						<br />
						<div class="dialog-box-warning" id="custom-warning">
							<div class="dialog-left">
								<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/warning.png" class="dialog-ico" alt=""/>
								Auto Centering the bookmarks will void any custom CSS applied below.
							</div>
						</div>
						<br />
						<br />
						<label for="xtrastyle">You can style the DIV that holds the menu here:</label><br/>
						<textarea id="xtrastyle" name="xtrastyle"><?php 
								$default_socialit = "margin:20px 0 0 0 !important;\npadding:25px 0 0 10px !important;\nheight:29px;/*the height of the icons (29px)*/\ndisplay:block !important;\nclear:both !important;";	
								if (!empty($socialit_plugopts['xtrastyle'])) {		
									echo $socialit_plugopts['xtrastyle']; 	
								} 	
								elseif (empty($socialit_plugopts['xtrastyle'])) {
									echo $default_socialit; 
								}
								else { 
									echo "If you see this message, please delete the contents of this textarea and click \"Save Changes\".";	
								} ?>
						</textarea>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/image.png" alt="" class="box-icons" />
					<h2>Background Image</h2>
					<div class="bnav">
						<a href="javascript:void(null);" class="toggle" id="gle4">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
						</a>
					</div>
				</div>
				<div class="box-mid-body" id="toggle4">
					<div class="padding">
						<span class="socialit_option">
							Use a background image? <input <?php echo (($socialit_plugopts['bgimg-yes'] == "yes")? 'checked=""' : ""); ?> name="bgimg-yes" id="bgimg-yes" type="checkbox" value="yes" />
						</span>
						<div id="bgimgs" <?php if(!isset($socialit_plugopts['bgimg-yes'])) { ?>class="hidden"<?php } else { echo " "; }?>>
							<label class="bgimg share-sexy">
								<input <?php echo (($socialit_plugopts['bgimg'] == "sexy")? 'checked="checked"' : ""); ?> id="bgimg-sexy" name="bgimg" type="radio" value="sexy" />
							</label>
							<label class="bgimg share-care">
								<input <?php echo (($socialit_plugopts['bgimg'] == "caring")? 'checked="checked"' : ""); ?> id="bgimg-caring" name="bgimg" type="radio" value="caring" />
							</label>
							<label class="bgimg share-care-old">
								<input <?php echo (($socialit_plugopts['bgimg'] == "care-old")? 'checked="checked"' : ""); ?> id="bgimg-care-old" name="bgimg" type="radio" value="care-old" />
							</label>
							<label class="bgimg share-love">
								<input <?php echo (($socialit_plugopts['bgimg'] == "love")? 'checked="checked"' : ""); ?> id="bgimg-love" name="bgimg" type="radio" value="love" />
							</label>
							<label class="bgimg share-wealth">
								<input <?php echo (($socialit_plugopts['bgimg'] == "wealth")? 'checked="checked"' : ""); ?> id="bgimg-wealth" name="bgimg" type="radio" value="wealth" />
							</label>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/layout-select-footer.png" alt="" class="box-icons" />
					<h2>Menu Placement</h2>
					<div class="bnav">
						<a href="javascript:void(null);" class="toggle" id="gle5">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
						</a>
					</div>
				</div>
				<div class="box-mid-body" id="toggle5">
					<div class="padding">
						<p id="placement-info">
							You can insert the Social It menu anywhere on your forums, the plugin will take appropiate values of that page. By default, Social It menu is displayed below the first post of the topic only, but you can insert it on Forums, Tag pages, or anywhere, just put this code where you want to insert it:<br />
							&lt;?php if(function_exists('selfserv_socialit')) { selfserv_socialit(); } ?&gt;
						</p>
						<span class="socialit_option">Display below first Post in Topic?</span>
						<label><input <?php echo (($socialit_plugopts['topic'] == "1")? 'checked="checked"' : ""); ?> name="topic" id="topic-show" type="radio" value="1" /> Yes</label>
						<label><input <?php echo (($socialit_plugopts['topic'] == "0" || empty($socialit_plugopts['topic']))? 'checked="checked"' : ""); ?> name="topic" id="topic-hide" type="radio" value="0" /> No</label>
						<span class="socialit_option">Show in RSS feed?</span>
						<label><input <?php echo (($socialit_plugopts['feed'] == "1")? 'checked="checked"' : ""); ?> name="feed" id="feed-show" type="radio" value="1" /> Yes</label>
						<label><input <?php echo (($socialit_plugopts['feed'] == "0" || empty($socialit_plugopts['feed']))? 'checked="checked"' : ""); ?> name="feed" id="feed-hide" type="radio" value="0" /> No</label>
						<?php
						if(class_exists('Support_Forum')){ //compatibility with support forum plugin
							$support_forum = new Support_Forum();
							if($support_forum->isActive()){
								?>
								<div id="genopts">
									<p id="sfi-info" class="hidden">
										Social It plugin is compatible with <a href="http://bbpress.org/plugins/topic/support-forum/">Support Forum plugin</a> Made by Aditya Naik & Sam Bauers. You are seeing these options because that plugin is activated on your forums. With the help of these options you can configure whether to show Social It on non-resolved, resolved or non support topics.
										<br /><span title="Click here to close this message" class="sfi-close">[close]</span>
									</p>
									<h3>Compatibility with Support Forum Plugin: <img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/question-frame.png" class="sfp-info" title="Click here for help with this option" alt="Click here for help with this option" /></h3>
									<span class="socialit_option">Display Social It on Non-Resolved Topics?</span>
									<label><input <?php echo (($socialit_plugopts['sfpnonres'] == "yes")? 'checked="checked"' : ""); ?> name="sfpnonres" id="sfpnonres-yes" type="radio" value="yes" /> Yes</label>
									<label><input <?php echo (($socialit_plugopts['sfpnonres'] == "no")? 'checked="checked"' : ""); ?> name="sfpnonres" id="sfpnonres-no" type="radio" value="no" /> No</label>
									<span class="socialit_option">Display Social It on Resolved Topics?</span>
									<label><input <?php echo (($socialit_plugopts['sfpres'] == "yes")? 'checked="checked"' : ""); ?> name="sfpres" id="sfpres-yes" type="radio" value="yes" /> Yes</label>
									<label><input <?php echo (($socialit_plugopts['sfpres'] == "no")? 'checked="checked"' : ""); ?> name="sfpres" id="sfpres-no" type="radio" value="no" /> No</label>
									<span class="socialit_option">Display Social It on Non-Support Topics?</span>
									<label><input <?php echo (($socialit_plugopts['sfpnonsup'] == "yes")? 'checked="checked"' : ""); ?> name="sfpnonsup" id="sfpnonsup-yes" type="radio" value="yes" /> Yes</label>
									<label><input <?php echo (($socialit_plugopts['sfpnonsup'] == "no")? 'checked="checked"' : ""); ?> name="sfpnonsup" id="sfpnonsup-no" type="radio" value="no" /> No</label>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			</li>
		</ul>
		<input type="hidden" name="save_changes" value="1" />
		<div class="submit"><input type="submit" value="Save Changes" /></div>
	</div>
</form>
<div id="socialit-col-right">
	<div class="box-right socialit-donation-box">
		<div class="box-right-head">
			<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/money-coin.png" alt="" class="box-icons" />
			<h3>Support by Donating</h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<p>We've got big plans for Social It, but we can't implement any of them without the proper funding!</p>
				<p>I'm sure you're aware that the development and continued support for this plugin is a <i>non-paying</i> <b>job</b>, and as such, all donations or contributions are greatly appreciated! <i>Top 5 donors will be listed on the plugin settings page (this page).</i></p>
				<div class="socialit-donate-button">
					<a href="<?php echo $donate_link; ?>" title="Help support the development of this plugin by donating!" class="socialit-buttons">
						Make Donation
					</a>
				</div>
				<div class="socialit-twitter-button">
					<a href="<?php echo $twitter_link; ?>" title="Get the latest information about the plugin and the latest news about Internet & Technology in the world!" class="socialit-buttons">
						Follow Me on Twitter
					</a>
				</div>
				<div class="socialit-website-button">
					<a href="http://gaut.am/" title="Get the latest information about the plugin and the latest news about Internet & Technology in the world!" class="socialit-buttons">
						Visit My Website!
					</a>
				</div>
				<p>Even if you do not want to donate, you can follow me on <a href="<?php echo $twitter_link; ?>">Twitter</a> or check my <a href="http://gaut.am/">website</a> for latest updates.</p>
			</div>
		</div>
	</div>
	<div class="box-right">
		<div class="box-right-head">
			<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/currency.png" alt="" class="box-icons" />
			<h3>Top Donors</h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<p>None till now! <a href="<?php echo $donate_link; ?>" title="Help support the development of this plugin by donating!">Donate</a> now to get to this list and your name with your website link will be here in the next release!</p>
			</div>
		</div>
	</div>
	<div class="box-right">
		<div class="box-right-head">
			<h3>Plugin Inspiration</h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<p>This plugin is inspired from <a href="http://sexybookmarks.net">SexyBookmarks plugin</a> for Wordpress made by <a href="http://eight7teen.com">Josh</a> & <a href="http://www.robotwithaheart.com">Norman</a>. They have really worked hard on this.</p>
			</div>
		</div>
	</div>
	<div class="box-right">
		<div class="box-right-head">
			<h3>Help</h3>
		</div>
		<div class="box-right-body" id="help-box">
			<div class="padding">
				<p>
					You can see the Plugin Information <a href="http://gaut.am/bbpress/plugins/social-it/">here</a>, FAQ <a href="http://gaut.am/bbpress/plugins/social-it/documentation/frequently-asked-questions-faq/">here</a>, Usage & Installation How-To Guide <a href="http://gaut.am/bbpress/plugins/social-it/documentation/usage-and-installation-how-to-guide/">here</a>, and Ask for Help <a href="http://forum.gaut.am/">here</a>.
				</p>
			</div>
		</div>
	</div>
</div>
<?php

}//closing brace for function "socialit_settings_page"


function socialit_get_fetch_url() { 
	global $socialit_plugopts;
	$perms = socialit_get_current_url();
	// which short url service should be used?
	if($socialit_plugopts['shorty'] == "e7t") {
		$first_url = "http://e7t.us/create.php?url=".$perms;
	} elseif($socialit_plugopts['shorty'] == "rims") {
		$first_url = "http://ri.ms/api-create.php?url=".$perms;
	} elseif($socialit_plugopts['shorty'] == "tinyarrow") {
		$first_url = "http://tinyarro.ws/api-create.php?url=".$perms;
	} elseif($socialit_plugopts['shorty'] == "tiny") {
		$first_url = "http://tinyurl.com/api-create.php?url=".$perms;
	} elseif($socialit_plugopts['shorty'] == "snip") {
		$first_url = "http://snipr.com/site/snip?&r=simple&link=".$perms;
	} elseif($socialit_plugopts['shorty'] == "shortto") {
		$first_url = "http://short.to/s.txt?url=".$perms;
	} elseif($socialit_plugopts['shorty'] == "cligs") {
		$first_url = "http://cli.gs/api/v1/cligs/create?url=".urlencode($perms);
	} elseif($socialit_plugopts['shorty'] == "supr") {
		$first_url = "http://su.pr/api?url=".$perms;
	} elseif($socialit_plugopts['shorty'] == "trim") {
		$first_url = "http://api.tr.im/api/trim_simple?url=".$perms;
	} else { //default is tr.im
		$first_url = "http://api.tr.im/api/trim_simple?url=".$perms;
	}
	
	if($socialit_plugopts['shorturls'][md5($perms)] == NULL){ //url not in array, has to be generated
		// retrieve the shortened URL
		$fetch_url = socialit_nav_browse($first_url);
		if ($fetch_url) { // remote call made and was successful
			$fetch_url = trim($fetch_url);
			$socialit_plugopts['shorturls'][md5($perms)] = $fetch_url;
			bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts); // update values for future use
		} else { //return the permalink, getting the short url was not successful
			$fetch_url = $perms;
		}
	}else{ //had been already generated and saved
		$fetch_url = $socialit_plugopts['shorturls'][md5($perms)];
	}
	
	return $fetch_url;
}

function bookmark_list_item($name, $opts=array()) {
	global $socialit_plugopts, $socialit_bookmarks_data;

	$url = $socialit_bookmarks_data[$name]['baseUrl'];
	foreach ($opts as $key=>$value) {
		$url=str_replace(strtoupper($key), $value, $url);
	}
	
	return sprintf(
		'<li class="%s"><a href="%s" rel="%s"%s title="%s">%s</a></li>',
		$name,
		$url,
		$socialit_plugopts['reloption'],
		$socialit_plugopts['targetopt']=="_blank"?' class="external"':'',
		$socialit_bookmarks_data[$name]['share'],
		$socialit_bookmarks_data[$name]['share']
	);
}

function get_socialit() {
	global $socialit_plugopts, $bbdb, $public_tags;
	$dont_get_si = false;
	if((class_exists('Support_Forum')) && (bb_is_topic())){ //compatibility with Support Forum plugin for bbPress
		$support_forum = new Support_Forum();
		if($support_forum->isActive()){
			if(($socialit_plugopts['sfpnonres'] == "no" && $support_forum->getTopicStatus() == "no") || ($socialit_plugopts['sfpres'] == "no" && $support_forum->getTopicStatus() == "yes") || ($socialit_plugopts['sfpnonsup'] == "no" && $support_forum->getTopicStatus() == "mu")){
				$dont_get_si = true;
			}
		}
	}
	if(!$dont_get_si){
		$site_name = bb_get_option('name');
		if(bb_is_topic()){
			$perms = urlencode(get_topic_link());
			$title = urlencode(get_topic_title());
			$feedperms = strtolower($perms);
			$mail_subject = $title;
			// Grab post tags for Twittley tags. If there aren't any, use default tags set in plugin options page
			$get_tags = bb_get_topic_tags(get_topic_id());
			if ($get_tags){
				foreach($get_tags as $tag) {
					$keywords = $keywords.$tag->name.',';
				}
			}
			$topic_id_ft = get_topic_id(); //topic id for getting text
			$first_post = (int) $bbdb->get_var("SELECT post_id FROM $bbdb->posts WHERE topic_id = $topic_id_ft ORDER BY post_id ASC LIMIT 1");
			$socialit_content = get_post_text($first_post);
		}else{
			$perms = socialit_get_current_url(); 
			$title = urlencode(bb_get_title());
			$feedperms = strtolower($perms);
			$mail_subject = $title;
			$socialit_content = bb_get_option('description');
		}
		$title = str_replace('+','%20',$title);
		$title = str_replace("&#8217;","'",$title);
		$short_title = substr($title, 0, 60)."...";
		$socialit_content = urlencode(substr(strip_tags(strip_shortcodes($socialit_content)),0,300));
		$socialit_content = str_replace('+','%20',$socialit_content);
		$socialit_content = str_replace("&#8217;","'",$socialit_content);
		$post_summary = stripslashes($socialit_content);
		if (!empty($keywords)) {
			$d_tags = $keywords;
		}else {
			$d_tags = $socialit_plugopts['defaulttags'];
		}
		$y_cat = $socialit_plugopts['ybuzzcat'];
		$y_med = $socialit_plugopts['ybuzzmed'];
		$t_cat = $socialit_plugopts['twittcat'];
		$current_rss_link = socialit_get_current_rss_link();
		//$mail_subject = $title;
		//$feedstructure = 'feed';
		
		// Temporary fix for bug that breaks layout when using NextGen Gallery plugin
		if( (strpos($post_summary, '[') || strpos($post_summary, ']')) ) {
			$post_summary = "";
		}
		if( (strpos($socialit_content, '[') || strpos($socialit_content,']')) ) {
			$socialit_content = "";
		}
		
		// select the background
		if(!isset($socialit_plugopts['bgimg-yes'])) {
			$bgchosen = '';
		} elseif($socialit_plugopts['bgimg'] == 'sexy') {
			$bgchosen = ' social-it-bg-sexy';
		} elseif($socialit_plugopts['bgimg'] == 'caring') {
			$bgchosen = ' social-it-bg-caring';
		} elseif($socialit_plugopts['bgimg'] == 'care-old') {
			$bgchosen = ' social-it-bg-caring-old';
		} elseif($socialit_plugopts['bgimg'] == 'love') {
			$bgchosen = ' social-it-bg-love';
		}  elseif($socialit_plugopts['bgimg'] == 'wealth') {
			$bgchosen = ' social-it-bg-wealth';
		}
		
		$style=($socialit_plugopts['autocenter'])?'':' style="'.__($socialit_plugopts['xtrastyle']).'"';
		if (bb_is_feed()) $style=''; // do not add inline styles to the feed.
		$expand = $socialit_plugopts['expand']?' social-it-expand':'';
		$autocenter = $socialit_plugopts['autocenter']?' social-it-center':'';
		//write the menu
		$socials = '<div class="social-it'.$expand.$autocenter.$bgchosen.'"'.$style.'><ul class="socials">';
		foreach ($socialit_plugopts['bookmark'] as $name) {
			if ($name=='socialit-twitter') {
				$socials.=bookmark_list_item($name, array(
					'post_by'=>(!empty($socialit_plugopts['twittid']))?"RT+@".$socialit_plugopts['twittid'].":+":'',
					'short_title'=>str_replace(" ", "+", urldecode($title)),
					'fetch_url'=>socialit_get_fetch_url(),
				));
			/*} elseif ($name=='socialit-mail') {
				$socials.=bookmark_list_item($name, array(
					'mail_subject'=>$mail_subject,
					'strip_teaser'=>$post_summary,
					'permalink'=>$perms,
				));*/
			}elseif ($name=='socialit-diigo') {
				$socials.=bookmark_list_item($name, array(
					'teaser'=>$socialit_content,
					'permalink'=>$perms,
					'title'=>$title,
				));
			}elseif ($name=='socialit-linkedin') {
				$socials.=bookmark_list_item($name, array(
					'post_summary'=>$post_summary,
					'site_name'=>$site_name,
					'permalink'=>$perms,
					'title'=>$title,
				));
			} elseif ($name=='socialit-devmarks') {
				$socials.=bookmark_list_item($name, array(
					'post_summary'=>$post_summary,
					'permalink'=>$perms,
					'title'=>$title,
				));
			} elseif ($name=='socialit-comfeed') {
				$socials.=bookmark_list_item($name, array(
					'permalink'=>$current_rss_link,
				));
			} elseif ($name=='socialit-yahoobuzz') {
				$socials.=bookmark_list_item($name, array(
					'permalink'=>$perms,
					'title'=>$title,
					'yahooteaser'=>$socialit_content,
					'yahoocategory'=>$y_cat,
					'yahoomediatype'=>$y_med,
				));
			} elseif ($name=='socialit-twittley') {
				$socials.=bookmark_list_item($name, array(
					'permalink'=>urlencode($perms),
					'title'=>$title,
					'post_summary'=>$post_summary,
					'twitt_cat'=>$t_cat,
					'default_tags'=>$d_tags,
				));
			} elseif ($name=='socialit-google') {
				$socials.=bookmark_list_item($name, array(
					'permalink'=>urlencode($perms),
					'title'=>$title,
					'annotation'=>$socialit_content,
					'labels'=>$d_tags,
				));
			} else {
				$socials.=bookmark_list_item($name, array(
					'permalink'=>$perms,
					'title'=>$title,
				));
			}
		}
		
		$socials .= '</ul><div style="clear:both;"></div></div>';
		//echo $socials;
		return $socials;
	}
}

// This function is what allows people to insert the menu wherever they please rather than above/below a post...
function selfserv_socialit() {
	echo get_socialit();
}

//write the <head> code
function socialit_public() {
	global $socialit_plugopts;
	echo "\n\n".'<!-- Start Of Code Generated By Social It Plugin By www.gaut.am -->'."\n";
	wp_register_style('social-it', SOCIALIT_PLUGPATH.'css/style.css', false, SOCIALIT_vNum, 'all');
	wp_print_styles('social-it');
	if ($socialit_plugopts['expand'] || $socialit_plugopts['autocenter'] || $socialit_plugopts['targetopt']=='_blank') {
		wp_register_script('social-it-public-js', SOCIALIT_PLUGPATH."js/social-it-public.js", array('jquery'), SOCIALIT_vNum);
		wp_print_scripts('social-it-public-js');
	}
	echo '<!-- End Of Code Generated By Social It Plugin By www.gaut.am -->'."\n\n";
}


//styles for admin area
function socialit_admin() {
	if($_GET['plugin'] == 'socialit_settings_page'){
		wp_register_style('social-it', SOCIALIT_PLUGPATH.'css/admin-style.css', false, SOCIALIT_vNum, 'all');
		wp_print_styles('social-it');
		if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false)){ //ie, as usual, doesnt render the css properly :| and creates problems for the developers
			wp_register_style('ie-social-it', SOCIALIT_PLUGPATH.'css/ie7-admin-style.css', false, SOCIALIT_vNum, 'all');
			wp_print_styles('ie-social-it');
		}
		echo '<script type="text/javascript" src="'.SOCIALIT_PLUGPATH.'js/jquery/jquery.js"></script>'; //loads newer version of jquery. bbpress uses old version
		echo '<script type="text/javascript" src="'.SOCIALIT_PLUGPATH.'js/jquery/ui.core.js?ver=1.7.1"></script>'; //ui core & sortable script not included in bbpress :-(
		echo '<script type="text/javascript" src="'.SOCIALIT_PLUGPATH.'js/jquery/ui.sortable.js?ver=1.7.1"></script>';
		echo '<script type="text/javascript" src="'.SOCIALIT_PLUGPATH.'js/social-it.js"></script>'; //social-it admin js script
	}
}

function socialit_insert_in_post($post_content) {
	global $socialit_plugopts, $bbdb;
	// decide whether or not to generate the bookmarks.
	if ((bb_is_topic() && $socialit_plugopts['topic'] == 1) || (bb_is_feed() && $socialit_plugopts['feed'] == 1)){ //socials should be generated and added
		$post_id_fc = get_post_id(); //post id for check
		if(bb_is_first($post_id_fc)){
			echo $post_content.get_socialit();
		}else{
			echo $post_content;
		}
	}else{
		echo $post_content;
	}
}

//add actions/filters
add_action('bb_admin_menu_generator', 'socialit_menu_link'); //link in settings
add_action('bb_admin_head', 'socialit_admin'); //admin css
add_action('bb_head', 'socialit_public'); //public css
add_filter('post_text', 'socialit_insert_in_post'); //to insert social it automatically below the first post of every topic
?>