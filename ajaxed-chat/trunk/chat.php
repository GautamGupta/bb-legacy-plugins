<?php
	@require_once('../../bb-load.php');
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
			$params["container_cfg_mysql_table"]    = $bbdb->prefix . 'ajaxed_chat';
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
	} else {
		echo '<span class="pfc_registered">You need to be a registered user to login to the Chat!</span>';
	}
?>