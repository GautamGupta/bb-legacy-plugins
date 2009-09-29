<?php
/*
Plugin Name: Ajaxed Chat
Plugin URI: http://www.gaut.am/bbpress/plugins/ajaxed-chat/
Description: Adds a MultiUser Chat Room using PHP and Ajax with PHPFreeChat Script (phpfreechat.net)
Version: 1.0
Author: Gautam
Author URI: http://gaut.am/

	Original Social It bbPress Plugin Copyright 2009 Gautam (email : admin@gaut.am) (website: http://gaut.am)

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
 Ajaxed Chat Plugin (for bbPress)
 By www.Gaut.am
*/

// Create Text Domain For Translations
load_plugin_textdomain('ajaxed_chat', '/my-plugins/ajaxed-chat/languages/');

//defines
define('AJAXED_CHAT_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/');
define('AJAXED_CHAT_DIR', dirname(__FILE__));
define('AJAXED_CHAT_OPTIONS','Ajaxed-Chat');
define('AJAXED_CHAT_vNum','1.0');

//reload
$ajaxed_chat_plugopts = bb_get_option(AJAXED_CHAT_OPTIONS);
if(!$ajaxed_chat_plugopts){
	//add defaults to an array
	$ajaxed_chat_plugopts = array(
		'serverid' => md5(bb_get_option('uri')),
		'chatname' => 'My Chat',
		'channels' => 'General,Help',
		'adminpassword' => mt_rand(),
		'clock' => 0,
		'flood' => 0,
		'ping' => 1,
		'log' => 1,
		'height' => '440px',
		'censor' => 0,
		'registered' => 0,
		'method' => 'Mysql', //'Mysql' or 'File'
		'theme' => 'default',
		'language' => 'en_US',
	);
	bb_update_option(AJAXED_CHAT_OPTIONS, $ajaxed_chat_plugopts);
}

function ajaxed_chat_load() {
	global $ajaxed_chat_plugopts, $bbdb;
	@require_once(AJAXED_CHAT_DIR.'/chat/src/phpfreechat.class.php');
	$okaytoconnect = true;
	$current_user = bb_get_current_user();
	$user_name = addslashes($current_user->display_name);
	if ($ajaxed_chat_plugopts['registered'] == true && empty($user_name)) $okaytoconnect = false;
	if ($okaytoconnect) {
		$params = array();	
		/* PARAMETERS */
		$params["title"] = $ajaxed_chat_plugopts['chatname'];
		$params["nick"] = $user_name;
		$params["isadmin"] = false; // do not use it on production servers ;)
		if ( bb_current_user_can('moderate') ) $params['isadmin'] = true;
		$params["serverid"] = $ajaxed_chat_plugopts['serverid']; // calculate a unique id for this chat
		$params["height"] = $ajaxed_chat_plugopts['height'];
		// setup urls
		$params["data_public_url"]   = AJAXED_CHAT_PLUGPATH."chat/data/public";
		$params["server_script_url"] = AJAXED_CHAT_PLUGPATH."chat.php";
		$params["theme_default_url"] = AJAXED_CHAT_PLUGPATH."chat/themes";
		$params["theme"] = $ajaxed_chat_plugopts['theme'];
		// admins
		$params['admins'] = array('admin'  => $ajaxed_chat_plugopts['adminpassword']);
		// setup paths
		if ($ajaxed_chat_plugopts['method'] == 'Mysql') {
			$params["container_type"] = "Mysql";
			$params["container_cfg_mysql_host"]     = $bbdb->db_servers['dbh_global']['host'];
			$params["container_cfg_mysql_port"]     = '3306';
			$params["container_cfg_mysql_database"] = $bbdb->db_servers['dbh_global']['name'];
			$params["container_cfg_mysql_table"]    = $bbdb->prefix.'ajaxed_chat';
			$params["container_cfg_mysql_username"] = $bbdb->db_servers['dbh_global']['user'];
			$params["container_cfg_mysql_password"] = $bbdb->db_servers['dbh_global']['password'];
		} else {
			$params["container_type"]         = "File";
			$params["container_cfg_chat_dir"] = AJAXED_CHAT_DIR."/data/private/chat";
		}
		if (empty($ajaxed_chat_plugopts['language'])) $ajaxed_chat_plugopts['language'] = 'en_US';
		$params["language"] = $ajaxed_chat_plugopts['language'];
		// Channels
		$params["channels"] = explode(",", $ajaxed_chat_plugopts['channels']);
		$params["max_channels"] = count($params["channels"]) + 5;
		$params["quit_on_closedwindow"] = false;
		$params['shownotice'] = 1;
		if ( bb_current_user_can('moderate') ) $params['shownotice'] = 7;
		if ($ajaxed_chat_plugopts['ping'] == '1') { $params['display_ping'] = true; } else { $params['display_ping'] = false; }
		//if ($ajaxed_chat_plugopts['debug'] == '1') { $params['debug'] = true; } else { $params['debug'] = false; }
		if ($ajaxed_chat_plugopts['clock'] == '1') { $params['clock'] = false; } else { $params['clock'] = true; }
		$params['debug'] = false;
		$skip_proxies = array();
		
		if ($ajaxed_chat_plugopts['flood'] == '1') $skip_proxies[] = 'noflood';
		if ($ajaxed_chat_plugopts['censor'] == '1') $skip_proxies[] = 'censor';
		if ($ajaxed_chat_plugopts['log'] = '1') $skip_proxies[] = 'log';
		
		$params['skip_proxies'] = $skip_proxies;

		$params['short_url'] = false;
		$params['showsmileys'] = true;

		$chat = new phpFreeChat( $params );
		$chat->printChat();
		if (isset($_GET['chat'])) {
			exit();
		}
	} else {
		echo '<span class="pfc_registered">'.__('You need to be a registered user to login to the Chat!', 'ajaxed_chat').'</span>';
	}
}

//add sidebar link to settings page
function ajaxed_chat_menu_link() {
	if (function_exists('bb_admin_add_submenu')) {
		bb_admin_add_submenu( __( 'Ajaxed Chat' ), 'administrate', 'ajaxed_chat_settings_page', 'options-general.php' );
	}
}

function ajaxed_chat_nav_browse($url){
	if (function_exists('curl_init')) {
		// Use cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$source = trim(curl_exec($ch));
		curl_close($ch);
		
	} elseif (function_exists('file_get_contents')) { // use file_get_contents()
		$source = trim(file_get_contents($url));
	} else {
		$source = null;
	}
	return $source;
}

function ajaxed_chat_update_check(){
	$latest_ver = ajaxed_chat_nav_browse("http://gaut.am/uploads/plugins/updater.php?pid=4&chk=ver&soft=bb&current=".AJAXED_CHAT_vNum);
	if($latest_ver && version_compare($latest_ver, AJAXED_CHAT_vNum, '>')){
		return $latest_ver;
	}else{
		return false;
	}
}

// Draw the menu page itself
function ajaxed_chat_settings_page() {
	global $ajaxed_chat_plugopts, $bbdb;
	
	//links
	$donate_link = "http://gaut.am/donate/";
	$twitter_link = "http://twitter.com/Gaut_am";
	$website_link = "http://gaut.am/";
	
	// processing form submission
	$status_message = "";
	
	if ($_POST['action'] == 'ajaxed_chat_save_settings') {
		bb_check_admin_referer('ac-update-options');
		$ajaxed_chat_plugopts['clock'] = ( $_POST['clock'] == 1 ? 1: 0 );
		$ajaxed_chat_plugopts['flood'] = ( $_POST['flood'] == 1 ? 1 : 0 );
		$ajaxed_chat_plugopts['ping'] = ( $_POST['ping'] == 1 ? 1: 0 );
		$ajaxed_chat_plugopts['registered'] = ( $_POST['registered'] == 1 ? 1: 0 );
		$ajaxed_chat_plugopts['censor'] = ( $_POST['censor'] == 1 ? 1: 0 );
		$ajaxed_chat_plugopts['method'] = $_POST['method'];
		$ajaxed_chat_plugopts['serverid'] = $_POST['serverid'];
		$ajaxed_chat_plugopts['height'] = $_POST['height'];
		$ajaxed_chat_plugopts['chatname'] = $_POST['chatname'];
		$ajaxed_chat_plugopts['channels'] = $_POST['channels'];
		$ajaxed_chat_plugopts['adminpassword'] = $_POST['adminpassword'];
		$ajaxed_chat_plugopts['theme'] = $_POST['theme'];
		$ajaxed_chat_plugopts['language'] = $_POST['language'];
		bb_update_option(AJAXED_CHAT_OPTIONS, $ajaxed_chat_plugopts);
		$status_message = __('Your changes have been saved successfully!', 'ajaxed_chat');
	}
	
	if(ajaxed_chat_update_check()){ //update available
		echo '
		<div id="update-message" class="ac-warning">
			<div class="dialog-left">
				<img src="'.AJAXED_CHAT_PLUGPATH.'images/icons/error.png" class="dialog-ico" alt=""/>
				'. __('New version of Ajaxed Chat is available! Please download the latest version ', 'ajaxed_chat') . '<a href="http://bbpress.org/plugins/topic/ajaxed-chat/">' . __('here', 'ajaxed_chat') . '</a>.
			</div>
		</div>';
	}
	
	if ($status_message != '') {
		echo '
		<div id="message" class="ac-success">
			<div class="dialog-left">
				<img src="'.AJAXED_CHAT_PLUGPATH.'images/icons/success.png" class="dialog-ico" alt=""/>
				'.$status_message.' | '. __( 'Maybe you would consider', 'ajaxed_chat' ) .' <a href="'.$donate_link.'">'. __( 'donating', 'ajaxed_chat' ) .'</a> ' . __( 'or following me on', 'ajaxed_chat' ) . ' <a href="'.$twitter_link.'">Twitter</a>?
			</div>
		</div>';
	}
	
	?>
	<h2><?php _e('Ajaxed Chat Settings', 'ajaxed_chat'); ?></h2>
	<form name="ajaxed-chat" id="ajaxed-chat" action="" method="post">
	<?php bb_nonce_field('ac-update-options'); ?>
	<div id="ac-col-left">
	<ul id="ac-sortables">
		<li>
			<div class="box-mid-head">
				<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/wrench-screwdriver.png" class="box-icons" />
				<h2><?php _e('General Settings', 'ajaxed_chat'); ?></h2>
			</div>
			<div class="box-mid-body">
			<div class="padding">
				<p id="placement-info">
					<?php _e('You can call the Ajaxed Chat by putting this code in your template:', 'ajaxed_chat'); ?><br />
					<i>&lt;?php if (function_exists('ajaxed_chat_load')) ajaxed_chat_load(); ?&gt;</i>
				</p>
				<div class="clearbig"></div>
				<div id="general_settings">
					<label for="serverid"><?php _e('Server ID:', 'ajaxed_chat'); ?></label>
					<input type="text" name="serverid" id="serverid" value="<?php echo $ajaxed_chat_plugopts['serverid']; ?>" />&nbsp;<small>(The server ID needs to be a very unique identifier for the chat. If you are confused, you can leave this value as it is.)</small>
				<div class="clearbig"></div>
					<label for="chatname"><?php _e('Chat Name:', 'ajaxed_chat'); ?></label>
					<input type="text" name="chatname" id="chatname" value="<?php echo $ajaxed_chat_plugopts['chatname']; ?>" />
				<div class="clearbig"></div>
					<label for="channels"><?php _e('Channel Names:', 'ajaxed_chat'); ?></label>
					<input type="text" name="channels" id="chanels" value="<?php echo $ajaxed_chat_plugopts['channels']; ?>" />&nbsp;<small>(<?php _e('Separated by commas (",") - Do not put spaces after commas', 'ajaxed_chat'); ?>)</small>
				<div class="clearbig"></div>
					<label for="adminpassword"><?php _e('Admin Password:', 'ajaxed_chat'); ?></label>
					<input type="text" name="adminpassword" id="adminpassword" value="<?php echo $ajaxed_chat_plugopts['adminpassword']; ?>" />
				<div class="clearbig"></div>
					<label for="language"><?php _e('Language:', 'ajaxed_chat'); ?></label>
					<?php 
						$current_language_directory = $ajaxed_chat_plugopts['language'];
						if (empty($current_language_directory)) $current_language_directory = 'en_US';					
						$language_directories = glob(AJAXED_CHAT_DIR . '/chat/i18n/*');
					?>		
					<select name="language" id="language">
						<?php
							foreach ($language_directories as $language) {
								if (is_dir($language)) { 
									$language_dir_name = basename($language); ?>
									<option value="<?php echo $language_dir_name; ?>" <?php if ($current_language_directory == $language_dir_name) { ?>selected="selected"<?php } ?>><?php echo $language_dir_name; ?></option>
								<?php
								}
							}
						?>
					</select>
				<div class="clearbig"></div>
					<label for="method"><?php _e('Storage Method:', 'ajaxed_chat'); ?></label>
					<label><input name="method" id="method-file" type="radio" value="File"<?php if($ajaxed_chat_plugopts['method'] == "File"){ echo " checked=\"true\""; } ?> />&nbsp;<?php _e('File', 'ajaxed_chat'); ?></label>
					&nbsp;
					<label><input name="method" id="method-mysql" type="radio" value="Mysql"<?php if($ajaxed_chat_plugopts['method'] == "Mysql"){ echo " checked=\"true\""; } ?> />&nbsp;<?php _e('Mysql', 'ajaxed_chat'); ?></label>
				</div>
			</div>
			</div>
		</li>
		<li>
			<div class="box-mid-head">
				<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/user.png" class="box-icons" />
				<h2><?php _e('Chat Box Settings', 'ajaxed_chat'); ?></h2>
			</div>
			<div class="box-mid-body">
			<div class="padding">
				<div id="chatbox_settings">
					<label class="ac_option">
						<?php _e('Turn on Ping?', 'ajaxed_chat'); ?>&nbsp;
						<input name="ping" type="checkbox" value="1" <?php checked('1', $ajaxed_chat_plugopts['ping']); ?> />
					</label>
				<div class="clearbig"></div>
					<label class="ac_option">
						<?php _e('Turn off Flood Checking?', 'ajaxed_chat'); ?>&nbsp;
						<input name="flood" type="checkbox" value="1" <?php checked('1', $ajaxed_chat_plugopts['flood']); ?> />
					</label>
				<div class="clearbig"></div>
					<label class="ac_option">
						<?php _e('Turn off the Censor Proxy?', 'ajaxed_chat'); ?>&nbsp;
						<input name="censor" type="checkbox" value="1" <?php checked('1', $ajaxed_chat_plugopts['censor']); ?> />
					</label>
				<div class="clearbig"></div>
					<label class="ac_option">
						<?php _e('Disable the timestamp on each chat message?', 'ajaxed_chat'); ?>&nbsp;
						<input name="clock" type="checkbox" value="1" <?php checked('1', $ajaxed_chat_plugopts['clock']); ?> />
					</label>
				<div class="clearbig"></div>
					<label class="ac_option">
						<?php _e('Disable text logging of the chat?', 'ajaxed_chat'); ?>&nbsp;
						<input name="log" type="checkbox" value="1" <?php checked('1', $ajaxed_chat_plugopts['log']); ?> />&nbsp;<small>Text chat logs are stored in the my-plugins/ajaxed-chat/chat/data/private/logs/serverid directory.</small>
					</label>
				<div class="clearbig"></div>
					<label class="ac_option">
						<?php _e('Disable unregistered users from viewing the chat?', 'ajaxed_chat'); ?>&nbsp;
						<input name="registered" type="checkbox" value="1" <?php checked('1', $ajaxed_chat_plugopts['registered']); ?> />
					</label>
				</div>
			</div>
			</div>
		</li>
		<li>
			<div class="box-mid-head">
				<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/layout-select-footer.png" class="box-icons" />
				<h2><?php _e('Layout Settings', 'ajaxed_chat'); ?></h2>
			</div>
			<div class="box-mid-body">
			<div class="padding">
				<div id="layout_settings">
					<label for="serverid"><?php _e('Height of Chat Box:', 'ajaxed_chat'); ?></label>
					<input type="text" name="height" id="height" value="<?php echo $ajaxed_chat_plugopts['height']; ?>" />
				<div class="clearbig"></div>
					<label for="serverid"><?php _e('Theme:', 'ajaxed_chat'); ?></label>
					<?php
						$current_theme_directory = $ajaxed_chat_plugopts['theme'];
						if (empty($current_theme_directory)){ $current_theme_directory = 'default'; }
						$theme_directories = glob(AJAXED_CHAT_DIR . '/chat/themes/*');
					?>
					<select name="theme">
						<?php
							foreach ($theme_directories as $theme) {
								if (is_dir($theme)) { 
									$theme_dir_name = basename($theme); ?>
									<option class="level-0" value="<?php echo $theme_dir_name; ?>" <?php if ($current_theme_directory == $theme_dir_name) { ?>selected="selected"<?php } ?>><?php echo $theme_dir_name; ?></option>
								<?php
								}
							}
						?>
					</select>
				</div>
			</div>
			</div>
		</li>	
	</ul>
		<input type="hidden" name="action" value="ajaxed_chat_save_settings" />
		<div class="submit"><input type="submit" value="<?php _e('Save Changes', 'ajaxed_chat'); ?>" /></div>
		<?php _e('Please run the /rehash command in chat room after you have done any changes to the configuration.', 'ajaxed_chat'); ?>
	</div>
</form>
<div id="ac-col-right">
	<div class="box-right">
		<div class="box-right-head">
			<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/plug.png" alt="" class="box-icons" />
			<h3><?php _e('Plugin Info', 'ajaxed_chat'); ?></h3>
		</div>
		<div class="box-right-body" id="help-box">
			<div class="padding">
				<h4><?php _e('Helpful Plugin Links', 'ajaxed_chat'); ?>:</h4>
				<ul>
					<li><a href="http://gaut.am/bbpress/plugins/ajaxed-chat/" target="_blank"><?php _e('Plugin Info', 'ajaxed_chat'); ?></a> (<?php _e('or', 'ajaxed_chat'); ?> <a href="http://bbpress.org/plugins/topic/ajaxed-chat/" target="_blank"><?php _e('here', 'ajaxed_chat'); ?></a>)</li>
					<li><a href="http://www.phpfreechat.net/commands" target="_blank"><?php _e('Chat Commands', 'ajaxed_chat'); ?></a></li>
					<li><a href="http://phpfreechat.net/faq/" target="_blank"><?php _e('Frequently Asked Questions', 'ajaxed_chat'); ?></a></li>
					<li><a href="http://forum.gaut.am/" target="_blank"><?php _e('Support Forum', 'ajaxed_chat'); ?></a></li>
					<li><a href="http://phpfreechat.net/forum/" target="_blank"><?php _e('Other Ajaxed Chat Platforms', 'ajaxed_chat'); ?></a></li>
				</ul>
				<div class="clearbig"></div>
			</div>
		</div>
	</div>
	<div class="box-right ac-donation-box">
		<div class="box-right-head">
			<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/money-coin.png" alt="" class="box-icons" />
			<h3><?php _e('Support by Donating', 'ajaxed_chat'); ?></h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<p><?php _e('Surely the fact that we\'re making the web a chatable place one forum at a time is worth a donation, right?', 'ajaxed_chat'); ?></p>
				<div class="ac-donate-button">
					<a href="<?php echo $donate_link; ?>" title="<?php _e('Help support the development of this plugin by donating!', 'ajaxed_chat'); ?>" class="ac-buttons">
						<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/donate.png" alt="" />
					</a>
				</div>
				<div class="ac-twitter-button">
					<a href="<?php echo $twitter_link; ?>" title="<?php _e('Get the latest information about the plugin and the latest news about Internet & Technology in the world!', 'ajaxed_chat'); ?>" class="ac-buttons">
						<?php _e('Follow us on Twitter!', 'ajaxed_chat'); ?>
					</a>
				</div>
				<div class="ac-website-button">
					<a href="http://gaut.am/" title="<?php _e('Get the latest information about the plugin and the latest news about Internet & Technology in the world!', 'ajaxed_chat'); ?>" class="ac-buttons">
						<?php _e('Visit our Website!', 'ajaxed_chat'); ?>
					</a>
				</div>				
			</div>
		</div>
	</div>
	<div class="box-right">
		<div class="box-right-head">
			<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/currency.png" alt="" class="box-icons" />
			<h3>Top Donors</h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<?php echo ajaxed_chat_nav_browse("http://gaut.am/uploads/plugins/donations.php?pid=4&chk=ver&soft=bb&current=".AJAXED_CHAT_vNum); ?>
				<p><a href="<?php echo $donate_link; ?>" title="<?php _e('Help support the development of this plugin by donating!', 'ajaxed_chat'); ?>"><?php _e('Donate', 'ajaxed_chat'); ?></a> <?php _e('now to get to this list and your name with your website link will be here!', 'ajaxed_chat'); ?></p>
			</div>
		</div>
	</div>
	<div class="box-right">
		<div class="box-right-head">
			<img src="<?php echo AJAXED_CHAT_PLUGPATH; ?>images/icons/thumb-up.png" alt="" class="box-icons" />
			<h3><?php _e('Shout Outs', 'ajaxed_chat'); ?></h3>
		</div>
		<div class="box-right-body">
			<div class="padding">
				<ul class="credits">
					<li><a href="http://phpfreechat.net/">PHP Free Chat Script</a></li>
					<li><a href="http://www.pinvoke.com/">GUI Icons by Pinvoke</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php
} //closing braces for ajaxed chat admin page

//styles for admin area
function ajaxed_chat_admin_head() {
	if($_GET['plugin'] == 'ajaxed_chat_settings_page'){
		wp_register_style('ajaxed-chat', AJAXED_CHAT_PLUGPATH.'css/admin-style.css', false, AJAXED_CHAT_vNum, 'all');
		wp_print_styles('ajaxed-chat');
		if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false)){ //ie, as usual, doesnt render the css properly :| and creates problems for the developers
			wp_register_style('ie-ajaxed-chat', AJAXED_CHAT_PLUGPATH.'css/ie7-admin-style.css', false, AJAXED_CHAT_vNum, 'all');
			wp_print_styles('ie-ajaxed-chat');
		}
	}
}

//loads full screen chat box, when called - yoursite.com/?chat
if (isset($_GET['chat'])) {
	add_action('bb_init', 'ajaxed_chat_load');
}

//action hooks
add_action('bb_admin_head', 'ajaxed_chat_admin_head'); //admin css
add_action('bb_admin_menu_generator', 'ajaxed_chat_menu_link'); //link in settings
?>