<?php
/*
 Functions PHP File for
 Social It plugin (for bbPress) by www.gaut.am
*/
//
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

// returns the option tag for a form select element
// $opts array expecting keys: field, value, text
function socialit_form_select_option($opts) {
	global $socialit_plugopts;
	$opts = array_merge(
		array(
			'field'=>'',
			'value'=>'',
			'text'=>'',
		),
		$opts
	);
	return sprintf('<option%s value="%s">%s</option>',
		($socialit_plugopts[$opts['field']]==$opts['value'])?' selected="selected"':"",
		$opts['value'],
		$opts['text']
	);
}

// given an array $options of data and $field to feed into sexy_form_select_option
function socialit_select_option_group($field, $options) {
	$h='';
	foreach ($options as $value => $text) {
		$h .= socialit_form_select_option(array(
			'field' => $field,
			'value' => $value,
			'text' => $text,
		));
	}
	return $h;
}

//curl, file get contents or nothing, used for short url and for updater
function socialit_nav_browse($url, $use_POST_method = false, $POST_data = null){
	if (function_exists('curl_init')) {
		// Use cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		if($use_POST_method){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POST_data);
		}
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
		if(version_compare($latest_ver, SOCIALIT_vNum, '>')){
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
function socialit_get_current_url(){
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
	global $socialit_plugopts, $socialit_bookmarks_data, $bbdb;
	
	echo '<h2 class="socialitlogo">' . __( 'Social It', 'socialit') . '</h2>';
	
	//some links
	$donate_link = "http://gaut.am/donate/";
	$twitter_link = "http://twitter.com/Gaut_am";

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
				'. __( 'New version of Social It is available! Please download the latest version ', 'socialit' ) . '<a href="http://bbpress.org/plugins/topic/social-it/">' . __( 'here', 'socialit' ) . '</a>
			</div>
		</div>'; //box cant (shouldnt) be closed
	}
	
	//import functionality
	if(isset($_POST['import'])) {
		if ( isset($_FILES['socialit_import_file']) && !empty($_FILES['socialit_import_file']['name']) ) {
			$socialit_imported_options = join('', file($_FILES['socialit_import_file']['tmp_name']));
			$code = '$socialit_imported_options = '.$socialit_imported_options.';';
			if(@eval('return true;'.$code)){
				if ( eval($code) === null ) {
					if($_POST['export_short_urls'] != "on"){
						unset($socialit_imported_options['shorturls']);
					}
					bb_update_option(SOCIALIT_OPTIONS, $socialit_imported_options);
					$status_message = __( 'Social It Options Imported Successfully!', 'socialit' );
				}else{
					$error_message = __( 'Social It Options Import failed!', 'socialit' );
				}
			}else{
				$error_message = __( 'Found syntax errors in file being imported. Social It Options Import failed!', 'socialit' );
			}
		}else{
			$error_message = __( 'Did not receive any file to be imported. Social It Options Import failed!', 'socialit' );
		}
	}
	
	//changes have been saved
	if(isset($_POST['save_changes'])) {
		$status_message = __( 'Your changes have been saved successfully!', 'socialit' );
		
		$errmsgmap = array(
			'bookmark' => __('You can\'t display the menu if you don\'t choose a few sites to add to it!', 'socialit'),
		);
		
		// adding to err msg map if twittley is enabled.
		if (in_array('socialit-twittley', $_POST['bookmark'])) {
			$errmsgmap['twittcat'] = __('You need to select the primary category for any articles submitted to Twittley.', 'socialit');
			$errmsgmap['defaulttags'] = __('You need to set at least 1 default tag for any articles submitted to Twittley.', 'socialit');
		}
		foreach ($errmsgmap as $field=>$msg) {
			if ($_POST[$field] == '') {
				$error_message = $msg;
				break;
			}
		}
		
		if (!$error_message) {
			foreach (array(
				'topic', 'xtrastyle', 'reloption', 'targetopt', 'bookmark', 
				'twittid', 'ybuzzcat', 'ybuzzmed', 
				'twittcat', 'defaulttags', 'bgimg-yes', 'mobile-hide', 'bgimg',
				'feed', 'expand', 'autocenter',
				'sfpnonres', 'sfpres', 'sfpnonsup',
				'shorty',
			) as $field) $socialit_plugopts[$field] = $_POST[$field];
			
			/* Short URLs */
			$socialit_plugopts['shortyapi']['snip']['chk'] = $_POST['shortyapichk-snip'];
			$socialit_plugopts['shortyapi']['snip']['user'] = $_POST['shortyapiuser-snip'];
			$socialit_plugopts['shortyapi']['snip']['key'] = $_POST['shortyapikey-snip'];
			$socialit_plugopts['shortyapi']['bitly']['chk'] = $_POST['shortyapichk-bitly'];
			$socialit_plugopts['shortyapi']['bitly']['user'] = $_POST['shortyapiuser-bitly'];
			$socialit_plugopts['shortyapi']['bitly']['key'] = $_POST['shortyapikey-bitly'];
			$socialit_plugopts['shortyapi']['supr']['chk'] = $_POST['shortyapichk-supr'];
			$socialit_plugopts['shortyapi']['supr']['login'] = $_POST['shortyapiuser-supr'];
			$socialit_plugopts['shortyapi']['supr']['key'] = $_POST['shortyapikey-supr'];
			$socialit_plugopts['shortyapi']['trim']['chk'] = $_POST['shortyapichk-trim'];
			$socialit_plugopts['shortyapi']['trim']['login'] = $_POST['shortyapiuser-trim'];
			$socialit_plugopts['shortyapi']['trim']['pass'] = $_POST['shortyapipass-trim'];
			$socialit_plugopts['shortyapi']['tinyarrow']['chk'] = $_POST['shortyapichk-tinyarrow'];
			$socialit_plugopts['shortyapi']['tinyarrow']['login'] = $_POST['shortyapiuser-tinyarrow'];
			$socialit_plugopts['shortyapi']['cligs']['chk'] = $_POST['shortyapichk-cligs'];
			$socialit_plugopts['shortyapi']['cligs']['key'] = $_POST['shortyapikey-cligs'];
			/* Short URLs End */
			
			bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts); //update options
		}

		if ($_POST['clearShortUrls']) {
			$count = count($socialit_plugopts['shorturls']);
			$socialit_plugopts['shorturls'] = array();
			bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts);
			echo '<div id="clearurl" class="socialit-warning"><div class="dialog-left"><img src="'.SOCIALIT_PLUGPATH.'images/icons/warning.png" class="dialog-ico" alt=""/>'.$count. __(' Short URL(s) have been reset.', 'socialit') . '</div><div class="dialog-right"><img src="'.SOCIALIT_PLUGPATH.'images/icons/warning-delete.jpg" class="del-x" alt=""/></div></div><div style="clear:both;"></div>';
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
				'.$status_message.' | '. __( 'Maybe you would consider', 'socialit' ) .' <a href="'.$donate_link.'">'. __( 'donating', 'socialit' ) .'</a> ' . __( 'or following me on', 'socialit' ) . ' <a href="'.$twitter_link.'">Twitter</a>?
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
					<h2><?php _e('Enabled Networks', 'socialit'); ?></h2>
						<div class="bnav">
							<a href="javascript:void(null);" class="toggle" id="gle1">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
							</a>
						</div>
				</div>
				<div class="box-mid-body iconator" id="toggle1">
					<div class="padding">
						<p><?php _e('Select the Networks to display. Drag to reorder.', 'socialit'); ?></p>
						<div id="socialit-networks">
							<?php
								foreach ($socialit_plugopts['bookmark'] as $name) print socialit_network_input_select($name, $socialit_bookmarks_data[$name]['check']);
								$unused_networks = array_diff(array_keys($socialit_bookmarks_data), $socialit_plugopts['bookmark']);
								foreach ($unused_networks as $name) print socialit_network_input_select($name, $socialit_bookmarks_data[$name]['check']);
							?>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/wrench-screwdriver.png" alt="" class="box-icons" />
					<h2><?php _e('Functionality Settings', 'socialit'); ?></h2>
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
								<?php _e('This will clear <u>ALL</u> short URLs. - Are you sure?', 'socialit'); ?>
							</div>
							<div class="dialog-right">
								<label><input name="warn-choice" id="warn-yes" type="radio" value="yes" /><?php _e('Yes', 'socialit'); ?></label> &nbsp;<label><input name="warn-choice" id="warn-cancel" type="radio" value="cancel" /><?php _e('Cancel', 'socialit'); ?></label>
							</div>
						</div>
						<div id="twitter-defaults">
							<h3><?php _e('Twitter Options:', 'socialit'); ?></h3>
							<label for="twittid"><?php _e('Twitter ID:', 'socialit'); ?></label>
							<input type="text" id="twittid" name="twittid" value="<?php echo $socialit_plugopts['twittid']; ?>" />
						<div class="clearbig"></div>
						<?php
						/*
						 * Short URL Options
						*/
						?>
							<label for="shorty"><?php _e('Which URL Shortener?', 'socialit'); ?></label>
							<select name="shorty" id="shorty">
							<?php
								// output shorty select options
								print socialit_select_option_group('shorty', array(
									'bitly' => 'bit.ly',
									'trim' => 'tr.im',
									'snip' => 'snipr.com',
									'tinyarrow' => 'tinyarro.ws',
									'cligs' => 'cli.gs',
									'supr' => 'su.pr',
									'e7t' => 'e7t.us',
									'tiny' => 'tinyurl.com',
								));
								/*'rims' => 'http://ri.ms',
								'shortto' => 'http://short.to',*/
							?>
							</select>
							<label for="clearShortUrls" id="clearShortUrlsLabel"><input name="clearShortUrls" id="clearShortUrls" type="checkbox"/><?php _e('Reset all Short URLs', 'socialit'); ?></label>
							<div id="shortyapimdiv-bitly" <?php if($socialit_plugopts['shorty'] != 'bitly') { ?>class="hidden"<?php } ?>>
							<div class="clearbig"></div>
								<div id="shortyapidiv-bitly">
									<label for="shortyapiuser-bitly">User ID:</label>
									<input type="text" id="shortyapiuser-bitly" name="shortyapiuser-bitly" value="<?php echo $socialit_plugopts['shortyapi']['bitly']['user']; ?>" />
									<label for="shortyapikey-bitly">API Key:</label>
									<input type="text" id="shortyapikey-bitly" name="shortyapikey-bitly" value="<?php echo $socialit_plugopts['shortyapi']['bitly']['key']; ?>" />
								</div>
							</div>
							<div id="shortyapimdiv-trim" <?php if($socialit_plugopts['shorty'] != 'trim') { ?>class="hidden"<?php } ?>>
								<span class="socialit_option" id="shortyapidivchk-trim">
									<input <?php echo (($socialit_plugopts['shortyapi']['trim']['chk'] == "1")? 'checked=""' : ""); ?> name="shortyapichk-trim" id="shortyapichk-trim" type="checkbox" value="1" /> Track Generated Links?
								</span>
								<div class="clearbig"></div>
								<div id="shortyapidiv-trim" <?php if(!isset($socialit_plugopts['shortyapi']['trim']['chk'])) { ?>class="hidden"<?php } ?>>
									<label for="shortyapiuser-trim">User ID:</label>
									<input type="text" id="shortyapiuser-trim" name="shortyapiuser-trim" value="<?php echo $socialit_plugopts['shortyapi']['trim']['user']; ?>" />
									<label for="shortyapikey-trim">Password:</label>
									<input type="text" id="shortyapipass-trim" name="shortyapipass-trim" value="<?php echo $socialit_plugopts['shortyapi']['trim']['pass']; ?>" />
								</div>
							</div>
							<div id="shortyapimdiv-snip" <?php if($socialit_plugopts['shorty'] != 'snip') { ?>class="hidden"<?php } ?>>
								<span class="socialit_option" id="shortyapidivchk-snip">
									<input <?php echo (($socialit_plugopts['shortyapi']['snip']['chk'] == "1")? 'checked=""' : ""); ?> name="shortyapichk-snip" id="shortyapichk-snip" type="checkbox" value="1" /> Track Generated Links?
								</span>
								<div class="clearbig"></div>
								<div id="shortyapidiv-snip" <?php if(!isset($socialit_plugopts['shortyapi']['snip']['chk'])) { ?>class="hidden"<?php } ?>>
									<label for="shortyapiuser-snip">User ID:</label>
									<input type="text" id="shortyapiuser-snip" name="shortyapiuser-snip" value="<?php echo $socialit_plugopts['shortyapi']['snip']['user']; ?>" />
									<label for="shortyapikey-snip">API Key:</label>
									<input type="text" id="shortyapikey-snip" name="shortyapikey-snip" value="<?php echo $socialit_plugopts['shortyapi']['snip']['key']; ?>" />
								</div>
							</div>
							<div id="shortyapimdiv-tinyarrow" <?php if($socialit_plugopts['shorty'] != 'tinyarrow') { ?>class="hidden"<?php } ?>>
								<span class="socialit_option" id="shortyapidivchk-tinyarrow">
									<input <?php echo (($socialit_plugopts['shortyapi']['tinyarrow']['chk'] == "1")? 'checked=""' : ""); ?> name="shortyapichk-tinyarrow" id="shortyapichk-tinyarrow" type="checkbox" value="1" /> Track Generated Links?
								</span>
								<div class="clearbig"></div>
								<div id="shortyapidiv-tinyarrow" <?php if(!isset($socialit_plugopts['shortyapi']['tinyarrow']['chk'])) { ?>class="hidden"<?php } ?>>
									<label for="shortyapiuser-tinyarrow">User ID:</label>
									<input type="text" id="shortyapiuser-tinyarrow" name="shortyapiuser-tinyarrow" value="<?php echo $socialit_plugopts['shortyapi']['tinyarrow']['login']; ?>" />
								</div>
							</div>
							<div id="shortyapimdiv-cligs" <?php if($socialit_plugopts['shorty'] != 'cligs') { ?>class="hidden"<?php } ?>>
								<span class="socialit_option" id="shortyapidivchk-cligs">
									<input <?php echo (($socialit_plugopts['shortyapi']['cligs']['chk'] == "1")? 'checked=""' : ""); ?> name="shortyapichk-cligs" id="shortyapichk-cligs" type="checkbox" value="1" /> Track Generated Links?
								</span>
								<div class="clearbig"></div>
								<div id="shortyapidiv-cligs" <?php if(!isset($socialit_plugopts['shortyapi']['cligs']['chk'])) { ?>class="hidden"<?php } ?>>
									<label for="shortyapikey-cligs">API Key:</label>
									<input type="text" id="shortyapikey-cligs" name="shortyapikey-cligs" value="<?php echo $socialit_plugopts['shortyapi']['cligs']['key']; ?>" />
								</div>
							</div>
							<div id="shortyapimdiv-supr" <?php if($socialit_plugopts['shorty'] != 'supr') { ?>class="hidden"<?php } ?>>
								<span class="socialit_option" id="shortyapidivchk-supr">
									<input <?php echo (($socialit_plugopts['shortyapi']['supr']['chk'] == "1")? 'checked=""' : ""); ?> name="shortyapichk-supr" id="shortyapichk-supr" type="checkbox" value="1" /> Track Generated Links?
								</span>
								<div class="clearbig"></div>
								<div id="shortyapidiv-supr" <?php if(!isset($socialit_plugopts['shortyapi']['supr']['chk'])) { ?>class="hidden"<?php } ?>>
									<label for="shortyapiuser-supr">User ID:</label>
									<input type="text" id="shortyapiuser-supr" name="shortyapiuser-supr" value="<?php echo $socialit_plugopts['shortyapi']['supr']['login']; ?>" />
									<label for="shortyapikey-supr">API Key:</label>
									<input type="text" id="shortyapikey-supr" name="shortyapikey-supr" value="<?php echo $socialit_plugopts['shortyapi']['supr']['key']; ?>" />
								</div>
							</div>
						<?php
						/*
						 * Short URL Options End
						*/
						?>
						<div class="clearbig"></div>
						</div>
						<div id="ybuzz-defaults">
							<h3><?php _e('Yahoo! Buzz Defaults:', 'socialit'); ?></h3>
							<label for="ybuzzcat"><?php _e('Default Content Category: ', 'socialit'); ?></label>
							<select name="ybuzzcat" id="ybuzzcat">
								<?php
									// output shorty select options
									print socialit_select_option_group('ybuzzcat', array(
										'entertainment' => 'Entertainment',
										'lifestyle' => 'Lifestyle',
										'health' => 'Health',
										'usnews' => 'U.S. News',
										'business' => 'Business',
										'politics' => 'Politics',
										'science' => 'Sci/Tech',
										'world_news' => 'World',
										'sports' => 'Sports',
										'travel' => 'Travel',
									));
									
								?>
							</select>
							<div class="clearbig"></div>
							<label for="ybuzzmed"><?php _e('Default Media Type:', 'socialit'); ?></label>
							<select name="ybuzzmed" id="ybuzzmed">
								<?php
									print socialit_select_option_group('ybuzzmed', array(
										'text'=>'Text',
										'image'=>'Image',
										'audio'=>'Audio',
										'video'=>'Video',
									));
								?>
							</select>
						<div class="clearbig"></div>
						</div>
						<div id="twittley-defaults">
							<h3><?php _e('Twittley Defaults:', 'socialit'); ?></h3>
							<label for="twittcat"><?php _e('Primary Content Category:', 'socialit'); ?></label>
							<select name="twittcat" id="twittcat">
								<?php
									print socialit_select_option_group('ybuzzmed', array(
										'Technology' => 'Technology',
										'World &amp; Business' => 'World &amp; Business',
										'Science' => 'Science',
										'Gaming' => 'Gaming',
										'Lifestyle' => 'Lifestyle',
										'Entertainment' => 'Entertainment',
										'Sports' => 'Sports',
										'Offbeat' => 'Offbeat',
										'Internet' => 'Internet',
									));
								?>
							</select>
							<div class="clearbig"></div>
							<p id="tag-info" class="hidden">
								<?php _e( "Enter a comma separated list of general tags which describe your site's posts as a whole. Try not to be too specific, as one post may fall into different \"tag categories\" than other posts.", "socialit" ); ?><br />
								<?php _e( "This list is primarily used as a failsafe in case you forget to enter WordPress tags for a particular post, in which case this list of tags would be used so as to bring at least *somewhat* relevant search queries based on the general tags that you enter here.", "socialit" ); ?><br /><span title="<?php _e('Click here to close this message', 'socialit'); ?>" class="dtags-close">[<?php _e( 'close', 'socialit' ); ?>]</span>
							</p>
							<label for="defaulttags"><?php _e('Default Tags:', 'socialit'); ?></label>
							<input type="text" name="defaulttags" id="defaulttags" value="<?php echo $socialit_plugopts['defaulttags']; ?>" /><img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/question-frame.png" class="dtags-info" title="<?php _e('Click here for help with this option', 'socialit'); ?>" alt="<?php _e('Click here for help with this option', 'socialit'); ?>" />
							<div class="clearbig"></div>
						</div>
						<div id="genopts">
							<h3><?php _e('General Functionality Options:', 'socialit'); ?></h3>
							<span class="socialit_option"><?php _e('Add nofollow to the links?', 'socialit'); ?></span>
							<label><input <?php echo (($socialit_plugopts['reloption'] == "nofollow")? 'checked="checked"' : ""); ?> name="reloption" id="reloption-yes" type="radio" value="nofollow" /> <?php _e('Yes', 'socialit'); ?></label>
							<label><input <?php echo (($socialit_plugopts['reloption'] == "")? 'checked="checked"' : ""); ?> name="reloption" id="reloption-no" type="radio" value="" /> <?php _e('No', 'socialit'); ?></label>
							<span class="socialit_option"><?php _e('Open links in new window?', 'socialit'); ?></span>
							<label><input <?php echo (($socialit_plugopts['targetopt'] == "_blank")? 'checked="checked"' : ""); ?> name="targetopt" id="targetopt-blank" type="radio" value="_blank" /> <?php _e('Yes', 'socialit'); ?></label>
							<label><input <?php echo (($socialit_plugopts['targetopt'] == "_self")? 'checked="checked"' : ""); ?> name="targetopt" id="targetopt-self" type="radio" value="_self" /> <?php _e('No', 'socialit'); ?></label>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/palette.png" alt="" class="box-icons" />
					<h2><?php _e('General Look &amp; Feel', 'socialit'); ?></h2>
						<div class="bnav">
							<a href="javascript:void(null);" class="toggle" id="gle3">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
							</a>
						</div>
				</div>
				<div class="box-mid-body" id="toggle3">
					<div class="padding">
						<span class="socialit_option"><?php _e('Animate-expand multi-lined bookmarks?', 'socialit'); ?></span>
						<label><input <?php echo (($socialit_plugopts['expand'] == "1")? 'checked="checked"' : ""); ?> name="expand" id="expand-yes" type="radio" value="1" /> <?php _e('Yes', 'socialit'); ?></label>
						<label><input <?php echo (($socialit_plugopts['expand'] != "1")? 'checked="checked"' : ""); ?> name="expand" id="expand-no" type="radio" value="0" /> <?php _e('No', 'socialit'); ?></label>
						<span class="socialit_option"><?php _e('Auto-space/center the bookmarks?', 'socialit'); ?></span> 
 		                                    <label><input <?php echo (($socialit_plugopts['autocenter'] == "2")? 'checked="checked"' : ""); ?> name="autocenter" id="autocenter-space" type="radio" value="2" /> <?php _e('Space', 'socialit'); ?></label> 
 		                                    <label><input <?php echo (($socialit_plugopts['autocenter'] == "1")? 'checked="checked"' : ""); ?> name="autocenter" id="autocenter-center" type="radio" value="1" /> <?php _e('Center', 'socialit'); ?></label> 
 		                                    <label><input <?php echo (($socialit_plugopts['autocenter'] == "0")? 'checked="checked"' : ""); ?> name="autocenter" id="autocenter-no" type="radio" value="0" /> <?php _e('No', 'socialit'); ?></label>
						<br />
						<div class="dialog-box-warning" id="custom-warning">
							<div class="dialog-left">
								<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/warning.png" class="dialog-ico" alt=""/>
								<?php _e('Auto Spacing/Centering the bookmarks will void any custom CSS applied below.', 'socialit'); ?>
							</div>
						</div>
						<br />
						<br />
						<label for="xtrastyle"><?php _e('You can style the DIV that holds the menu here:', 'socialit'); ?></label><br/>
						<textarea id="xtrastyle" name="xtrastyle"<?php if($socialit_plugopts['autocenter'] == "2" || $socialit_plugopts['autocenter'] == "1"){ ?> disabled=true<?php } ?>><?php 
								$default_socialit = "margin:20px 0 0 0 !important;\npadding:25px 0 0 10px !important;\nheight:29px;/*the height of the icons (29px)*/\ndisplay:block !important;\nclear:both !important;";	
								if (!empty($socialit_plugopts['xtrastyle'])) {		
									echo $socialit_plugopts['xtrastyle']; 	
								} 	
								elseif (empty($socialit_plugopts['xtrastyle'])) {
									echo $default_socialit; 
								}
								else { 
									_e('If you see this message, please delete the contents of this textarea and click "Save Changes".', 'socialit');
								} ?>
						</textarea>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/image.png" alt="" class="box-icons" />
					<h2><?php _e('Background Image', 'socialit'); ?></h2>
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
							<?php _e('Use a background image?', 'socialit'); ?> <input <?php echo (($socialit_plugopts['bgimg-yes'] == "yes")? 'checked=""' : ""); ?> name="bgimg-yes" id="bgimg-yes" type="checkbox" value="yes" />
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
							<label class="bgimg share-enjoy">
								<input <?php echo (($socialit_plugopts['bgimg'] == "enjoy")? 'checked="checked"' : ""); ?> id="bgimg-enjoy" name="bgimg" type="radio" value="enjoy" />
							</label>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/layout-select-footer.png" alt="" class="box-icons" />
					<h2><?php _e('Menu Placement', 'socialit'); ?></h2>
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
							<?php _e('You can insert the Social It menu anywhere on your forums, the plugin will take appropiate values of that page. By default, Social It menu is displayed below the first post of the topic only, but you can insert it on Forums, Tag pages, or anywhere, just put this code where you want to insert it:', 'socialit'); ?><br />
							&lt;?php if(function_exists('selfserv_socialit')) { selfserv_socialit(); } ?&gt;
						</p>
						<span class="socialit_option"><?php _e('Display below first Post in Topic?', 'socialit'); ?></span>
						<label><input <?php echo (($socialit_plugopts['topic'] == "1")? 'checked="checked"' : ""); ?> name="topic" id="topic-show" type="radio" value="1" /> <?php _e('Yes', 'socialit'); ?></label>
						<label><input <?php echo (($socialit_plugopts['topic'] == "0" || empty($socialit_plugopts['topic']))? 'checked="checked"' : ""); ?> name="topic" id="topic-hide" type="radio" value="0" /> <?php _e('No', 'socialit'); ?></label>
						<span class="socialit_option"><?php _e('Show in RSS feed?', 'socialit'); ?></span>
						<label><input <?php echo (($socialit_plugopts['feed'] == "1")? 'checked="checked"' : ""); ?> name="feed" id="feed-show" type="radio" value="1" /> <?php _e('Yes', 'socialit'); ?></label>
						<label><input <?php echo (($socialit_plugopts['feed'] == "0" || empty($socialit_plugopts['feed']))? 'checked="checked"' : ""); ?> name="feed" id="feed-hide" type="radio" value="0" /> <?php _e('No', 'socialit'); ?></label>
						<label class="socialit_option" style="margin-top:12px;">
							<?php _e('Hide menu from mobile browsers?', 'socialit'); ?> <input <?php echo (($socialit_plugopts['mobile-hide'] == "yes")? 'checked' : ""); ?> name="mobile-hide" id="mobile-hide" type="checkbox" value="yes" />
						</label>
						<?php
						if(class_exists('Support_Forum')){ //compatibility with support forum plugin
							$support_forum = new Support_Forum();
							if($support_forum->isActive()){
								?>
								<div id="genopts">
									<p id="sfi-info" class="hidden">
										<?php _e('Social It plugin is compatible with <a href="http://bbpress.org/plugins/topic/support-forum/">Support Forum plugin</a> Made by Aditya Naik & Sam Bauers. You are seeing these options because that plugin is activated on your forums. With the help of these options you can configure whether to show Social It on non-resolved, resolved or non support topics.', 'socialit'); ?>
										<br /><span title="<?php _e('Click here to close this message', 'socialit'); ?>" class="sfi-close">[<?php _e('close', 'socialit'); ?>]</span>
									</p>
									<h3><?php _e('Compatibility with Support Forum Plugin', 'socialit'); ?>: <img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/question-frame.png" class="sfp-info" title="<?php _e('Click here for help with this option', 'socialit'); ?>" alt="<?php _e('Click here for help with this option', 'socialit'); ?>" /></h3>
									<span class="socialit_option"><?php _e('Display Social It on Non-Resolved Topics?', 'socialit'); ?></span>
									<label><input <?php echo (($socialit_plugopts['sfpnonres'] == "yes")? 'checked="checked"' : ""); ?> name="sfpnonres" id="sfpnonres-yes" type="radio" value="yes" /> <?php _e('Yes', 'socialit'); ?></label>
									<label><input <?php echo (($socialit_plugopts['sfpnonres'] == "no")? 'checked="checked"' : ""); ?> name="sfpnonres" id="sfpnonres-no" type="radio" value="no" /> <?php _e('No', 'socialit'); ?></label>
									<span class="socialit_option"><?php _e('Display Social It on Resolved Topics?', 'socialit'); ?></span>
									<label><input <?php echo (($socialit_plugopts['sfpres'] == "yes")? 'checked="checked"' : ""); ?> name="sfpres" id="sfpres-yes" type="radio" value="yes" /> <?php _e('Yes', 'socialit'); ?></label>
									<label><input <?php echo (($socialit_plugopts['sfpres'] == "no")? 'checked="checked"' : ""); ?> name="sfpres" id="sfpres-no" type="radio" value="no" /> <?php _e('No', 'socialit'); ?></label>
									<span class="socialit_option"><?php _e('Display Social It on Non-Support Topics?', 'socialit'); ?></span>
									<label><input <?php echo (($socialit_plugopts['sfpnonsup'] == "yes")? 'checked="checked"' : ""); ?> name="sfpnonsup" id="sfpnonsup-yes" type="radio" value="yes" /> <?php _e('Yes', 'socialit'); ?></label>
									<label><input <?php echo (($socialit_plugopts['sfpnonsup'] == "no")? 'checked="checked"' : ""); ?> name="sfpnonsup" id="sfpnonsup-no" type="radio" value="no" /> <?php _e('No', 'socialit'); ?></label>
									<br /><br />
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
		<div class="submit"><input type="submit" value="<?php _e('Save Changes', 'socialit'); ?>" /></div>
		<hr width=590 align="left" />
	</div>
</form>
<div id="socialit-col-left">
	<ul id="socialit-sortables">
		<li>
			<form name="social-it" id="socialit-import-options" method="post" enctype="multipart/form-data">
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/down.png" alt="" class="box-icons" />
					<h2><?php _e('Import Social It Options', 'socialit'); ?></h2>
					<div class="bnav">
						<a href="javascript:void(null);" class="toggle" id="gle6">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
						</a>
					</div>
				</div>
				<div class="box-mid-body" id="toggle6">
					<div class="padding">
						<div class="dialog-box-warning" id="import-warning">
							<div class="dialog-left">
								<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/warning.png" class="dialog-ico" alt=""/>
								<?php _e('All of your current Social It options will be overwritten by the imported value. Are you sure you want to overwrite all settings?', 'socialit'); ?>
							</div>
							<div class="dialog-right">
								<label><input name="import-warn-choice" id="import-warn-yes" type="radio" value="yes" onchange="if(this.checked==true){document.forms['socialit-import-options'].submit();jQuery('#import-warning').fadeOut();jQuery(this).is(':not(:checked)');}return;" onclick="document.forms['socialit-import-options'].submit();jQuery('#import-warning').fadeOut();jQuery(this).is(':not(:checked)');" /><?php _e('OK', 'socialit'); ?></label> &nbsp;<label><input name="import-warn-choice" id="import-warn-cancel" type="radio" value="cancel" onchange="if(this.checked==true){this.checked=false;jQuery('#import-warning').fadeOut();}return;" /><?php _e('Cancel', 'socialit'); ?></label>
							</div>
						</div>
						<div class="dialog-box-warning" id="import-short-urls-warning">
							<div class="dialog-left">
								<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/warning.png" class="dialog-ico" alt=""/>
								<?php _e('Only import the short URLs if you had taken a backup of options from this forum.', 'socialit'); ?>
							</div>
							<div class="dialog-right">
								<label><input name="import-short-urls-warn-choice" id="import-short-urls-warn-yes" type="radio" value="yes" /><?php _e('OK', 'socialit'); ?></label> &nbsp;<label><input name="import-short-urls-warn-choice" id="import-short-urls-warn-no" type="radio" value="cancel" /><?php _e('Cancel', 'socialit'); ?></label>
							</div>
						</div>
						<p><?php _e('This functionality will restore your entire Social It options from a file.<br /><strong>Make sure you have done an export and backup the exported file before you try this!', 'socialit'); ?></strong></p>
						<input type="file" id="socialit_import_file" name="socialit_import_file" size="40" />
						<div class="clearbig"></div>
						<label id="import_short_urls_label" for="import_short_urls">
							<input type="checkbox" id="import_short_urls" name="import_short_urls" /> <?php _e('Import Generated Short URLs Too', 'socialit'); ?><br />
						</label>
						<input type="hidden" name="import" value="1" />
						<div class="submit">
							<input type="button" id="import-submit" value="<?php _e('Import Options', 'socialit'); ?>" />
						</div>
					</div>
					<div class="clearbig"></div>
				</div>
			</form>
		</li>
		<li>
			<form name="social-it" id="socialit-export-options" method="post">
				<div class="box-mid-head">
					<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/up.png" alt="" class="box-icons" />
					<h2><?php _e('Export Social It Options', 'socialit'); ?></h2>
					<div class="bnav">
						<a href="javascript:void(null);" class="toggle" id="gle7">
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-plus.png" class="close" alt=""/>
							<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/toggle-min.png" class="open" style="display:none;" alt=""/>
						</a>
					</div>
				</div>
				<div class="box-mid-body" id="toggle7">
					<div class="padding">
						<div class="dialog-box-warning" id="export-short-urls-warning">
							<div class="dialog-left">
								<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/warning.png" class="dialog-ico" alt=""/>
								<?php _e('Only export short URLs if you are backing up the options, and are not importing the options on another forum.', 'socialit'); ?>
							</div>
							<div class="dialog-right">
								<label><input name="export-short-urls-warn-choice" id="export-short-urls-warn-yes" type="radio" value="yes" /><?php _e('OK', 'socialit'); ?></label> &nbsp;<label><input name="export-short-urls-warn-choice" id="export-short-urls-warn-no" type="radio" value="cancel" /><?php _e('Cancel', 'socialit'); ?></label>
							</div>
						</div>
						<p><?php _e('This functionality will dump your entire Social It options into a file', 'socialit'); ?></p>
						<label id="export_short_urls_label" for="export_short_urls">
							<input type="checkbox" id="export_short_urls" name="export_short_urls" /> <?php _e('Export Generated Short URLs Too', 'socialit'); ?><br />
						</label>
						<input type="hidden" name="export" value="1" />
						<?php
						if(isset($_POST['export'])) {
							$url = "";
							if($_POST['export_short_urls'] == "on"){
								$url = "?url=1";
							}
							echo '<iframe src="'.SOCIALIT_PLUGPATH.'export.php'.$url.'" width=0 height=0></iframe>';
						}
						?>
						<div class="submit">
							<input type="submit" value="<?php _e('Export Options', 'socialit'); ?>" />
						</div>
						<div class="clearbig"></div>
					</div>
				</div>
			</form>
		</li>
	</ul>
</div>
<div id="socialit-col-right">
	<div class="box-right">
		<div class="box-right-head">
			<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/plug.png" alt="" class="box-icons" />
			<h3><?php _e('Plugin Info', 'socialit'); ?></h3>
		</div>
		<div class="box-right-body" id="help-box">
			<div class="padding">
				<h4><?php _e('Helpful Plugin Links', 'socialit'); ?>:</h4>
				<ul>
					<li><a href="http://gaut.am/bbpress/plugins/social-it/" target="_blank"><?php _e('Plugin Info', 'socialit'); ?></a> (<?php _e('or', 'socialit'); ?> <a href="http://bbpress.org/plugins/topic/social-it/" target="_blank"><?php _e('here', 'socialit'); ?></a>)</li>
					<li><a href="http://gaut.am/bbpress/plugins/social-it/documentation/usage-and-installation-how-to-guide/" target="_blank"><?php _e('Installation &amp; Usage Guide', 'socialit'); ?></a></li>
					<li><a href="http://gaut.am/bbpress/plugins/social-it/documentation/frequently-asked-questions-faq/" target="_blank"><?php _e('Frequently Asked Questions', 'socialit'); ?></a></li>
					<li><a href="http://forum.gaut.am/" target="_blank"><?php _e('Support Forum', 'socialit'); ?></a></li>
					<li><a href="http://sexybookmarks.net/platforms/" target="_blank"><?php _e('Other Social It Platforms', 'socialit'); ?></a></li>
				</ul>
				<div class="clearbig"></div>
			</div>
		</div>
	</div>
	<div class="box-right socialit-donation-box">
		<div class="box-right-head">
			<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/money-coin.png" alt="" class="box-icons" />
			<h3><?php _e('Support by Donating', 'socialit'); ?></h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<p><?php _e('Surely the fact that we\'re making the web a shareable place one forum at a time is worth a donation, right?', 'socialit'); ?></p>
				<div class="socialit-donate-button">
					<a href="<?php echo $donate_link; ?>" title="<?php _e('Help support the development of this plugin by donating!', 'socialit'); ?>" class="socialit-buttons">
						<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/donate.png" alt="" />
					</a>
				</div>
				<div class="socialit-twitter-button">
					<a href="<?php echo $twitter_link; ?>" title="<?php _e('Get the latest information about the plugin and the latest news about Internet & Technology in the world!', 'socialit'); ?>" class="socialit-buttons">
						<?php _e('Follow Him on Twitter!', 'socialit'); ?>
					</a>
				</div>
				<div class="socialit-website-button">
					<a href="http://gaut.am/" title="<?php _e('Get the latest information about the plugin and the latest news about Internet & Technology in the world!', 'socialit'); ?>" class="socialit-buttons">
						<?php _e('Visit His Website!', 'socialit'); ?>
					</a>
				</div>				
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
				<?php echo trim(socialit_nav_browse("http://gaut.am/uploads/plugins/donations.php?pid=1&chk=ver&soft=bb&current=".SOCIALIT_vNum)); ?>
				<p><a href="<?php echo $donate_link; ?>" title="<?php _e('Help support the development of this plugin by donating!', 'socialit'); ?>"><?php _e('Donate', 'socialit'); ?></a> <?php _e('now to get to this list and your name with your website link will be here!', 'socialit'); ?></p>
			</div>
		</div>
	</div>
	<div class="box-right">
		<div class="box-right-head">
			<img src="<?php echo SOCIALIT_PLUGPATH; ?>images/icons/thumb-up.png" alt="" class="box-icons" />
			<h3><?php _e('Shout Outs', 'socialit'); ?></h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<ul class="credits">
					<li><a href="http://sexybookmarks.net/">SexyBookmarks Plugin by Josh & Norman</a></li>
					<li><a href="http://www.pinvoke.com/">GUI Icons by Pinvoke</a></li>
					<li><a href="http://wefunction.com/2008/07/function-free-icon-set/">Original Skin Icons by Function</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php

}//closing brace for function "socialit_settings_page"
function socialit_get_fetch_url() {
	/* Raw Information for Short URLs:
		* Add Bitly - Api - http://code.google.com/p/bitly-api/wiki/ApiDocumentation (Full API) - Added, except jQuery hide/show
		* Add Awesm - Suggestion by Josh, but its Tough to join & use, etc
		* Snipr: http://snipr.com/site/help?go=api (Full API) - Added, except jQuery hide/show
		* Supr: http://www.stumbleupon.com/developers/Supr:API_documentation/ (Almost Full API)
		* Cligs: http://blog.cli.gs/api (Add: urlencode to short url)
		* No API for e7t, TinyURL, shortto (Remove shortto suggested)
		* Remove rims, shortto - Done
		* Tinyarrow: http://tinyarro.ws/info/api
	*/
	global $socialit_plugopts;
	$perms = socialit_get_current_url();
	if($socialit_plugopts['shorturls'][md5($perms)] == NULL){ //url not in array, has to be generated
		$url_more = "";
		$use_POST_method = false;
		$POST_data = null;
		// which short url service should be used?
		if($socialit_plugopts['shorty'] == "e7t") {
			$first_url = "http://e7t.us/create.php?url=".$perms;
		} elseif($socialit_plugopts['shorty'] == "tiny") {
			$first_url = "http://tinyurl.com/api-create.php?url=".$perms;
		} elseif($socialit_plugopts['shorty'] == "snip") {
			if($socialit_plugopts['shortyapi']['snip']['chk'] == 1){
				$url_more = "&snipuser=".$socialit_plugopts['shortyapi']['snip']['user']."&snipapi=".$socialit_plugopts['shortyapi']['snip']['key'];
			}
			$first_url = "http://snipr.com/site/getsnip";
			$use_POST_method = true;
			//"&snipowner=".$socialit_plugopts['shortyapi']['snip']['user'].
			$POST_data = "snipformat=simple&sniplink=".rawurlencode($perms).$url_more;
		} elseif($socialit_plugopts['shorty'] == "cligs") {
			$first_url = "http://cli.gs/api/v1/cligs/create?url=".urlencode($perms)."&appid=SocialIt";
			if($socialit_plugopts['shortyapi']['cligs']['chk'] == 1){
				$first_url .= "&key=".$socialit_plugopts['shortyapi']['cligs']['key'];
			}
		} elseif($socialit_plugopts['shorty'] == "supr") {
			$first_url = "http://su.pr/api/simpleshorten?url=".$perms;
			if($socialit_plugopts['shortyapi']['supr']['chk'] == 1){
				$url_more .= "&login=".$socialit_plugopts['shortyapi']['supr']['login']."&apiKey=".$socialit_plugopts['shortyapi']['supr']['key'];
			}
		} elseif($socialit_plugopts['shorty'] == "bitly") {
			$first_url = "http://api.bit.ly/shorten?version=2.0.1&longUrl=".$perms."&history=1&login=".$socialit_plugopts['shortyapi']['bitly']['user']."&apiKey=".$socialit_plugopts['shortyapi']['bitly']['key']."&format=json";
		} elseif($socialit_plugopts['shorty'] == "trim"){
			if($socialit_plugopts['shortyapi']['trim']['chk'] == 1){
				$first_url = "http://api.tr.im/api/trim_url.json?url=".$perms."&username=".$socialit_plugopts['shortyapi']['trim']['user']."&password=".$socialit_plugopts['shortyapi']['trim']['pass'];
			}else{
				$first_url = "http://api.tr.im/api/trim_simple?url=".$perms;
			}
		} elseif($socialit_plugopts['shorty'] == "tinyarrow") {
			$first_url = "http://tinyarro.ws/api-create.php?url=".$perms;
			if($socialit_plugopts['shortyapi']['tinyarrow']['chk'] == 1){
				$url_more .= "&login=".$socialit_plugopts['shortyapi']['tinyarrow']['login']."&apiKey=".$socialit_plugopts['shortyapi']['tinyarrow']['key'];
			}
		} else { //default is e7t.us
			$first_url = "http://e7t.us/create.php?url=".$perms;
		}
		// retrieve the shortened URL
		$fetch_url = socialit_nav_browse($first_url, $use_POST_method, $POST_data);
		if($socialit_plugopts['shorty'] == "trim" && $socialit_plugopts['shortyapi']['trim']['chk'] == 1){
			$fetch_array = json_decode($fetch_url, true);
			$fetch_url = $fetch_array['url'];
		}
		if($socialit_plugopts['shorty'] == "bitly"){
			$fetch_array = json_decode($fetch_url, true);
			$fetch_url = $fetch_array['results'][$perms]['shortUrl'];
		}
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

function bookmark_list_item($name, $opts = array()) {
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
	global $socialit_plugopts, $bbdb, $public_tags, $socialit_is_mobile, $socialit_is_bot;
	$dont_get_si = false;
	if(version_compare(SI_BB_VER, '1.0', '<=')){
		$istopic = bb_is_topic();
	}else{
		$istopic = is_topic();
	}
	if((class_exists('Support_Forum')) && ($istopic)){ //compatibility with Support Forum plugin for bbPress
		$support_forum = new Support_Forum();
		if($support_forum->isActive()){
			if(($socialit_plugopts['sfpnonres'] == "no" && $support_forum->getTopicStatus() == "no") || ($socialit_plugopts['sfpres'] == "no" && $support_forum->getTopicStatus() == "yes") || ($socialit_plugopts['sfpnonsup'] == "no" && $support_forum->getTopicStatus() == "mu")){
				$dont_get_si = true;
			}
		}
	}
	if($socialit_plugopts['mobile-hide']=='yes' && ($socialit_is_mobile || $socialit_is_bot)) {
		$dont_get_si = true;
	}
	if(!$dont_get_si){
		$site_name = bb_get_option('name');
		if($istopic){
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
		if(version_compare(SI_BB_VER, '1.0', '<=')){
			$socialit_content = urlencode(substr(strip_tags(strip_shortcodes($socialit_content)),0,300));
		}else{
			$socialit_content = urlencode(substr(strip_tags($socialit_content),0,300));
		}
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
		}  elseif($socialit_plugopts['bgimg'] == 'enjoy') {
			$bgchosen = ' social-it-bg-enjoy';
		}
		
		$style=($socialit_plugopts['autocenter'])?'':' style="'.__($socialit_plugopts['xtrastyle']).'"';
		if(version_compare(SI_BB_VER, '1.0', '<=')){
			$isfeed = bb_is_feed();
		}else{
			$isfeed = is_bb_feed();
		}
		if ($isfeed) $style=''; // do not add inline styles to the feed.
		$expand = $socialit_plugopts['expand']?' social-it-expand':'';
		if ($socialit_plugopts['autocenter'] == 1) { 
			$autocenter=' social-it-center'; 
		} elseif ($socialit_plugopts['autocenter'] == 2) { 
			$autocenter=' social-it-spaced'; 
		} else { 
			$autocenter=''; 
		} 
		//write the menu
		$socials = '<div class="social-it'.$expand.$autocenter.$bgchosen.'"'.$style.'><ul class="socials">';
		foreach ($socialit_plugopts['bookmark'] as $name) {
			if ($name=='socialit-twitter') {
				$socials.=bookmark_list_item($name, array(
					'post_by'=>(!empty($socialit_plugopts['twittid']))?"RT+@".$socialit_plugopts['twittid'].":+":'',
					//'short_title'=>str_replace(" ", "+", urldecode($title)),
					'short_title'=>$title,
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
	if(version_compare(SI_BB_VER, '1.0', '<=')){
		wp_register_style('social-it', SOCIALIT_PLUGPATH.'css/style.css', false, SOCIALIT_vNum, 'all');
		wp_print_styles('social-it');
		if ($socialit_plugopts['expand'] || $socialit_plugopts['autocenter'] || $socialit_plugopts['targetopt']=='_blank') {
			wp_register_script('social-it-public-js', SOCIALIT_PLUGPATH."js/social-it-public.js", array('jquery'), SOCIALIT_vNum);
			wp_print_scripts('social-it-public-js');
		}
	}else{
		echo '<link rel="stylesheet" href="'.SOCIALIT_PLUGPATH.'css/style.css" type="text/css" media="all" />';
		echo '<script type="text/javascript" src="'.SOCIALIT_PLUGPATH.'js/social-it-public.js"></script>';
	}
	echo '<!-- End Of Code Generated By Social It Plugin By www.gaut.am -->'."\n\n";
}


//styles for admin area
function socialit_admin() {
	if($_GET['plugin'] == 'socialit_settings_page'){
		if(version_compare(SI_BB_VER, '1.0', '<=')){
			wp_register_style('social-it', SOCIALIT_PLUGPATH.'css/admin-style.css', false, SOCIALIT_vNum, 'all');
			wp_print_styles('social-it');
			if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false)){ //ie, as usual, doesnt render the css properly :| and creates problems for the developers
				wp_register_style('ie-social-it', SOCIALIT_PLUGPATH.'css/ie7-admin-style.css', false, SOCIALIT_vNum, 'all');
				wp_print_styles('ie-social-it');
			}
		}else{
			if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false)){ //ie, as usual, doesnt render the css properly :| and creates problems for the developers
				echo '<link rel="stylesheet" href="'.SOCIALIT_PLUGPATH.'css/admin-style.css" type="text/css" media="all" />';
				echo '<link rel="stylesheet" href="'.SOCIALIT_PLUGPATH.'css/ie7-admin-style.css" type="text/css" media="all" />';
			}
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
	if(version_compare(SI_BB_VER, '1.0', '<=')){
		$istopic = bb_is_topic();
	}else{
		$istopic = is_topic();
	}
	if (($istopic && $socialit_plugopts['topic'] == 1) || (bb_is_feed() && $socialit_plugopts['feed'] == 1)){ //socials should be generated and added
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

?>