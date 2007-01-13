<?php
// This is completely based upon the:
// WordPress Mobile Edition
// version 2.0, 2006-11-03
//
// Copyright (c) 2002-2006 Alex King
// http://alexking.org/projects/wordpress
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: bbPress Mobile Edition
Plugin URI: http://blog.trentadams.ca/2006/12/22/mobile-bbpress-plugin/
Description: Show a mobile view of the forum if the visitor is on a known mobile device.
Author: Trent Adams (Alex King Code)
Author URI: http://blog.trentadams.ca
Version: 0.73
*/ 

$_SERVER['REQUEST_URI'] = ( isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] . (( isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')));

function mobile_check() {
		   
	if (!isset($_SERVER["HTTP_USER_AGENT"]) ) {
		return false;
	}
	$whitelist = array(
		'Stand Alone/QNws'
	);
	foreach ($whitelist as $browser) {
		if (strstr($_SERVER["HTTP_USER_AGENT"], $browser)) {
			return false;
		}
	}
	$small_browsers = array(
		'2.0 MMP'
		,'240x320'
		,'AvantGo'
		,'BlackBerry'
		,'Blazer'
		,'Cellphone'
		,'Danger'
		,'DoCoMo'
		,'Elaine/3.0'
		,'EudoraWeb'
		,'hiptop'
		,'MMEF20'
		,'MOT-V'
		,'NetFront'
		,'Newt'
		,'Nokia'
		,'Opera Mini'
		,'Palm'
		,'portalmmm'
		,'Proxinet'
		,'ProxiNet'
		,'SHARP-TQ-GX10'
		,'Small'
		,'SonyEricsson'
		,'Symbian OS'
		,'SymbianOS'
		,'TS21i-10'
		,'UP.Browser'
		,'UP.Link'
		,'Windows CE'
		,'WinWAP'
	);
	foreach ($small_browsers as $browser) {
		if (strstr($_SERVER["HTTP_USER_AGENT"], $browser)) {
			return true;
		}
	}
	return false;
}

?>