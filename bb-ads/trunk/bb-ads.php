<?php
/**
 * Plugin Name: BB-Ads
 * Plugin Description: Display a random html or php ad wherever the function is called.
 * Author: Wittmania
 * Author URI: http://blog.wittmania.com
 * Plugin URI: http://blog.wittmania.com/bb-ads/
 * Version: 1.0
 */

// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

// This plugin is largely inspired by Dan P. Benjamin's Automatic Image Rotator
// You can find out more about it here: http://automaticlabs.com/products/rotator

function bb_ads($specific_file = 'any') {

	/* ------------------------- CONFIGURATION ----------------------- */
	//Allowed extensions:
		$extList = array();
		$extList['php'] = 'text/html';
		$extList['html'] = 'text/html';
		$extList['htm'] = 'text/html';
	
	// You shouldn't need to edit anything after this point.
		
	// --------------------- END CONFIGURATION -----------------------
	
	$ad_file = null;
	
	$folder = '/my-plugins/bb-ads-files';
	$folder = (getcwd()) . $folder; 

	
	// Adds the trailing slash to the folder if it isn't there already
	if (substr($folder,-1) != '/') {
		$folder = $folder.'/';
	}
	
	// ----------------- For serving a specific file ------------------
	if ($specific_file != 'any') {
		//Populates the $fileInfo array, checks for file's existence
		$fileInfo = pathinfo($folder . $specific_file);
		if (
			isset( $extList[ strtolower( $fileInfo['extension'] ) ] ) &&
			file_exists( $folder.$fileInfo['basename'] )
		) {
			$ad_file = $folder.$fileInfo['basename'];
		}
	// 	----------------- For serving a random file ------------------
	} else {
		$fileList = array();
		$handle = opendir($folder);
		while ( false !== ( $file = readdir($handle) ) ) {
			$file_info = pathinfo($file);
			if (
				isset( $extList[ strtolower( $file_info['extension'] ) ] )
			) {
				$fileList[] = $file;
			}
		}
		closedir($handle);
	
		if (count($fileList) > 0) {
			$fileNumber = time() % count($fileList);
			$ad_file = $folder.$fileList[$fileNumber];
		}
	}
	
	// 	----------------- Serves the file, or displays error message. ------------------
	if ($ad_file != null) {
		include_once($ad_file);
	} else {
		$error_message = "Your ad should be right here... but it's not.  See the BB-Ads readme for help.";
		echo ($error_message);
		}
}
?>