<?php
/*
Plugin Name: Plugin browser for bbPress
Plugin URI: http://bbpress.org/plugins/topic/57
Description: Adds one-click installation and upgrade of plugins from the bbPress plugin repository.
Author: Sam Bauers
Author URI: 
Version: 0.1.5

Version History:
0.1		: Initial Beta release
0.1.1	: Trim whitespace from items in Plugin_Browser::getRemoteList()
0.1.2	: Using CURL libraries as preference, then falling back to fopen wrappers
0.1.3	: Removed stray fclose() call
0.1.4	: Stop the truncating of files using \r\n line breaks being retrieved via CURL
		  Added _wpnonce to check action validity
0.1.5	: Support for plugins with sub-directories

To Do:
- Better error messages on failure.
- Replace remote bb_get_plugin_data call with less expensive custom method that doesn't rely on remote fopen
*/


/**
 * Plugin browser for bbPress version 0.1.5
 * 
 * ----------------------------------------------------------------------------------
 * 
 * Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)
 * 
 * ----------------------------------------------------------------------------------
 * 
 * LICENSE:
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * ----------------------------------------------------------------------------------
 * 
 * PHP version 4 and 5
 * 
 * ----------------------------------------------------------------------------------
 * 
 * @author    Sam Bauers <sam@viveka.net.au>
 * @copyright 2007 Sam Bauers
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v2
 * @version   0.1.5
 **/


/**
 * Wrapper class for the Plugin browser plugin
 *
 * @author  Sam Bauers
 * @version 0.1.5
 **/
class Plugin_Browser
{
	/**
	 * The URI of the plugin subversion repository
	 *
	 * @var string
	 **/
	var $repositoryURI = 'http://plugins-svn.bbpress.org/';
	
	
	/**
	 * An indexed array of values that report the current state of local versions
	 *
	 * @var array
	 **/
	var $localRepositoryData = false;
	
	
	/**
	 * Initialises the class
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function Plugin_Browser()
	{
		// Nothing to inititalise
	}
	
	
	/**
	 * Retrieves the options for the plugin stored in the database
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function getLocalRepositoryData()
	{
		if (!$this->localRepositoryData) {
			$this->localRepositoryData = bb_get_option('plugin_browser_local_data');
		}
		
		if ($this->localRepositoryData) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Sets the options for the plugin stored in the database
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function setLocalRepositoryData($key, $value)
	{
		if (!$this->localRepositoryData) {
			$this->localRepositoryData = bb_get_option('plugin_browser_local_data');
		}
		
		if (!$this->localRepositoryData) {
			$this->localRepositoryData = array();
		}
		
		$this->localRepositoryData[$key] = $value;
		bb_update_option('plugin_browser_local_data', $this->localRepositoryData);
	}
	
	
	/**
	 * Gets a remote file using curl or fopen
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function getRemoteFile($URI, $contentsAsArray = false, $etag_match = '|^W/"([0-9]+)//"$|')
	{
		if (is_callable('curl_init')) {
			$ch = curl_init($URI);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			$file = curl_exec($ch);
			curl_close($ch);
			
			list($header, $_contents) = split("\r\n\r\n", $file, 2);
			
			$_headers = split("\r\n", $header);
		} else {
			$handle = fopen($URI, 'r');
			$meta = stream_get_meta_data($handle);
			$_contents = '';
			while (!feof($handle)) {
				$_contents .= fread($handle, 8192);
			}
			fclose($handle);
			
			$_headers = $meta['wrapper_data'];
		}
		
		$headers = array();
		
		foreach ($_headers as $_header) {
			if (substr($_header, 0, 4) == 'HTTP') {
				list($http_equiv, $value) = split(' ', $_header);
			} else {
				list($http_equiv, $value) = split(':', $_header, 2);
			}
			$headers[$http_equiv] = trim($value);
		}
		
		if ((string) $headers['HTTP/1.1'] == '301') {
			return $headers['Location'];
		}
		
		$_contents = trim($_contents);
		
		if ($contentsAsArray) {
			$contents = split("\n", $_contents);
		} else {
			$contents = $_contents;
		}
		
		if ($headers['ETag']) {
			if (preg_match($etag_match, $headers['ETag'], $matches)) {
				$revision = (integer) $matches[1];
			}
		}
		
		return array('headers' => $headers, 'contents' => $contents, 'revision' => $revision);
	}
	
	
	/**
	 * Gets the current subversion revision from the remote repository
	 *
	 * @return integer
	 * @author Sam Bauers
	 **/
	function getRemoteRepositoryRevision()
	{
		$file = $this->getRemoteFile($this->repositoryURI);
		
		return $file['revision'];
	}
	
	
	/**
	 * Gets the current subversion revision that is stored locally
	 *
	 * @return integer
	 * @author Sam Bauers
	 **/
	function getLocalRepositoryRevision()
	{
		if ($this->getLocalRepositoryData()) {
			return $this->localRepositoryData['revision'];
		} else {
			return 0;
		}
	}
	
	
	/**
	 * Returns an array of all locally cached plugin browser data
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function getLocalRepositoryList()
	{
		if (!$this->localRepositoryList) {
			$this->localRepositoryList = bb_get_option('plugin_browser_local_list');
		}
		
		if (!$this->localRepositoryList) {
			$this->localRepositoryList = array();
		}
			
		return $this->localRepositoryList;
	}
	
	
	/**
	 * Retrieves a remote list from the subversion repository as a nice array
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function getList($lines)
	{
		$lines = preg_grep('|^\s*<li>.*</li>\s*$|', $lines);
		$lines = array_map(create_function('$input', '$output = str_replace(array("/", " "), "", strip_tags($input)); if ($output != "..") { return trim($output); }'), $lines);
		$lines = array_filter($lines);
		$lines = array_values($lines);
		
		return $lines;
	}
	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function updateLocalRepositoryList()
	{
		$file = $this->getRemoteFile($this->repositoryURI, true);
		$plugins = $this->getList($file['contents']);
		
		$latest_list = array();
		
		$current_list = $this->getLocalRepositoryList();
		
		foreach ($plugins as $plugin) {
			
			$plugin_file = $this->getRemoteFile($this->repositoryURI . $plugin . '/trunk/', true, '|^W/"([0-9]+)//.*"$|');
			
			$remote_revision = $plugin_file['revision'];
			
			$local_revision = $current_list[$plugin]['revision'];
			
			if ($local_revision < $remote_revision) {
				$latest_list[$plugin]['id'] = $plugin;
				$latest_list[$plugin]['revision'] = $remote_revision;
				
				$files = $this->getList($plugin_file['contents']);
				
				$files = preg_grep('|.*\.php|', $files);
				
				foreach ($files as $file) {
					if ($data = bb_get_plugin_data($this->repositoryURI . $plugin . '/trunk/' . $file)) {
						$latest_list[$plugin] = $data;
						$latest_list[$plugin]['filename'] = $file;
						$latest_list[$plugin]['id'] = $plugin;
						$latest_list[$plugin]['revision'] = $remote_revision;
						break;
					}
				}
			} else {
				$latest_list[$plugin] = $current_list[$plugin];
			}
		}
		
		bb_update_option('plugin_browser_local_list', $latest_list);
		
		$this->localRepositoryList = $latest_list;
		
		$this->setLocalRepositoryData('revision', $this->getRemoteRepositoryRevision());
	}
	
	
	/**
	 * Retrieves and installs a plugin
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function installPlugin($plugin_id)
	{
		if (!is_file(BBPLUGINDIR . 'pb--' . $plugin_id)) {
			if (mkdir(BBPLUGINDIR . 'pb--' . $plugin_id)) {
				$file = $this->getRemoteFile($this->repositoryURI . $plugin_id . '/trunk/', true, '|^W/"([0-9]+)//.*"$|');
				
				$remote_revision = $file['revision'];
				
				$contents = '<?php' . "\n" . '$plugin[\'revision_local\'] = ' . $remote_revision . ';' . "\n" . '?>';
				
				$handle = fopen(BBPLUGINDIR . 'pb--' . $plugin_id . '/pb--revision.php', 'w');
				fwrite($handle, $contents);
				fclose($handle);
				
				$list = $this->getList($file['contents']);
				
				foreach ($list as $item) {
					$this->installRemoteFile($this->repositoryURI . $plugin_id . '/trunk/' . $item, BBPLUGINDIR . 'pb--' . $plugin_id . '/' . $item);
				}
				
				return true;
			}
			
			return false;
		}
		
		return false;
	}
	
	
	/**
	 * Installs a file from a remote location, recurses through directories as necessary
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function installRemoteFile($URI, $path)
	{
		$file = $this->getRemoteFile($URI);
		
		if (is_string($file)) {
			mkdir($path);
			$new_file = $this->getRemoteFile($file, true, '|^W/"([0-9]+)//.*"$|');
			$list = $this->getList($new_file['contents']);
			
			foreach ($list as $item) {
				$this->installRemoteFile($file . $item, $path . '/' . $item);
			}
		} else {
			$handle = fopen($path, 'w');
			fwrite($handle, $file['contents']);
			fclose($handle);
		}
	}
	
	
	/**
	 * Removes a plugin
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function uninstallPlugin($plugin_id)
	{
		if (is_dir(BBPLUGINDIR . 'pb--' . $plugin_id)) {
			$this->uninstallFile(BBPLUGINDIR . 'pb--' . $plugin_id);
		}
		
		return true;
	}
	
	
	/**
	 * Recursively removes files
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function uninstallFile($path)
	{
		if (is_dir($path)) {
			foreach (glob($path . '/*') as $file) {
				$this->uninstallFile($file);
			}
			rmdir($path);
		} else {
			unlink($path);
		}
	}
	
	
	/**
	 * Removes the old plugin and installs an upgraded plugin
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function upgradePlugin($plugin_id)
	{
		if ($this->uninstallPlugin($plugin_id)) {
			return $this->installPlugin($plugin_id);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Tells us whether the plugins folder is writable
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function pluginsFolderWritable()
	{
		if (is_writable(BBPLUGINDIR)) {
			return true;
		} else {
			return false;
		}
	}
} // END class Plugin_Browser


// Initialise the class
$plugin_browser = new Plugin_Browser();


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'plugin_browser_admin_page_add');
add_action('bb_admin-header.php', 'plugin_browser_admin_page_process');
add_action('bb_admin_head', 'plugin_browser_add_css');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function plugin_browser_admin_page_add()
{
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Plugin browser'), 'use_keys', 'plugin_browser_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Plugin browser'), 'use_keys', 'plugin_browser_admin_page');
		if (isset($bb_submenu['plugins.php'])) { // Build 740-793
			$bb_submenu['plugins.php'][] = $submenu;
		} else { // Build 277-739
			$bb_submenu['site.php'][] = $submenu;
		}
	}
}


/**
 * Adds some CSS for use in the list
 *
 * @return void
 * @author Sam Bauers
 **/
function plugin_browser_add_css()
{
	global $plugin_browser;
	
	if ($_GET['plugin'] == 'plugin_browser_admin_page') {
		if (!$plugin_browser->getLocalRepositoryRevision()) {
			bb_admin_notice(__('To get started, fetch the latest plugin list from server using the button below.'));
		}
		
		if (!$plugin_browser->pluginsFolderWritable()) {
			bb_admin_notice(__('Your plugins directory is not writable by the web server. You will not be able to install plugins unless it is.'), 'error');
		}
?>
<style type="text/css" media="screen">
	/* <![CDATA[ */
	table.widefat tr.alt.installed td { background-color: #88bb88; }
	table.widefat tr.installed td { background-color: #aaddaa; }
	table.widefat tr.alt.upgradable td { background-color: #ffcc66; }
	table.widefat tr.upgradable td { background-color: #ffcc00; }
	/* ]]> */
</style>
<?php
	}
}


/**
 * Writes an admin page for the plugin
 *
 * @return string
 * @author Sam Bauers
 **/
function plugin_browser_admin_page()
{
	global $plugin_browser;
?>
	<h2>Plugin browser</h2>
	<p>
		This plugin allows you to browse the plugin repository at <a href="http://bbpress.org/plugins/">http://bbpress.org/plugins/</a>.<br />You can install and upgrade the plugins via the actions on the right of the list.
	</p>
	<p>
		Once installed, plugins still need to be activated in the <a href="plugins.php">Plugins</a> page.
	</p>
	<hr />
<?php
	$localList = $plugin_browser->getLocalRepositoryRevision();
	$remoteList = $plugin_browser->getRemoteRepositoryRevision();
	
	if ($localList < $remoteList) {
?>
	<form method="post" class="submit" style="float:right;">
		<input name="action" type="hidden" value="plugin_browser_post_fetch" />
		<?php bb_nonce_field('plugin_browser_post_fetch', '_wpnonce', false); ?>

		<input name="plugin_browser_revision_local" type="hidden" value="<?php echo($localList); ?>" />
		<input name="plugin_browser_revision_remote" type="hidden" value="<?php echo($remoteList); ?>" />
		<input type="submit" value="Fetch latest plugin list from server" />
	</form>
<?php
	}
?>
	<p class="submit">
		Plugin list <em>r. <?php echo($localList); ?></em> - ( Latest <em>r. <?php echo($remoteList); ?></em> )&nbsp;
	</p>
	<table class="widefat">
		<thead>
			<tr>
				<th>Plugin</th>
				<th>Description</th>
				<th class="action">Local version</th>
				<th class="action">Latest version</th>
				<th class="action">Upgrade</th>
				<th class="action">Install</th>
			</tr>
		</thead>
		<tbody>
<?php
	$plugins = $plugin_browser->getLocalRepositoryList();
	
	$bb_plugins = bb_get_plugins();
	$bb_plugins_keys = array_map(create_function('$input', 'return preg_replace("|pb\-\-([^/]+)/.*|", "$1", $input);'), array_keys($bb_plugins));
	
	foreach ($plugins as $plugin) {
		if ($plugin['version']) {
			$upgradeText = null;
			$action = 'install';
			$actionText = __('Install');
			$plugin['version_local'] = __('None');
			$plugin['revision_local'] = null;
			$class = null;
			
			if (in_array($plugin['id'], $bb_plugins_keys)) {
				$action = 'uninstall';
				$actionText = __('Uninstall');
				$class = 'installed';
				
				$plugin['version_local'] = $bb_plugins['pb--' . $plugin['id'] . '/' . $plugin['filename']]['version'];
				
				@include(BBPLUGINDIR . 'pb--' . $plugin['id'] . '/pb--revision.php');
				
				if ($plugin['revision_local'] < $plugin['revision']) {
					$upgradeText = __('Upgrade');
					$class = 'upgradable';
				}
			}
?>
			<tr<?php alt_class('plugin', $class); ?>>
				<td><?php echo($plugin['plugin_link']); ?></td>
				<td><?php echo($plugin['description']); ?><cite><?php printf( __('By %s.'), $plugin['author_link'] ); ?></cite></td>
				<td class="action" style="white-space:nowrap;">
					<?php echo($plugin['version_local']); ?>
<?php
		if ($plugin['revision_local']) {
?>
					<br /><br /><em>r. <?php echo($plugin['revision_local']); ?></em>
<?php
		}
?>
				</td>
				<td class="action" style="white-space:nowrap;"><?php echo($plugin['version']); ?><br /><br /><em>r. <?php echo($plugin['revision']); ?></em></td>
				<td class="action" style="white-space:nowrap;"><a href="?plugin=plugin_browser_admin_page&amp;action=plugin_browser_plugin_upgrade&amp;plugin_browser_plugin_id=<?php echo(urlencode($plugin['id'])); ?>&amp;_wpnonce=<?php echo(bb_create_nonce('plugin_browser_plugin_upgrade_' . $plugin['id'])); ?>"><?php echo($upgradeText); ?></a></td>
				<td class="action" style="white-space:nowrap;"><a href="?plugin=plugin_browser_admin_page&amp;action=plugin_browser_plugin_<?php echo($action); ?>&amp;plugin_browser_plugin_id=<?php echo(urlencode($plugin['id'])); ?>&amp;_wpnonce=<?php echo(bb_create_nonce('plugin_browser_plugin_' . $action . '_' . $plugin['id'])); ?>"><?php echo($actionText); ?></a></td>
			</tr>
<?php
		}
	}
?>
		</tbody>
	</table>
<?php
}


/**
 * Processes the admin page form
 *
 * @return void
 * @author Sam Bauers
 **/
function plugin_browser_admin_page_process()
{
	global $plugin_browser;
	
	switch ($_REQUEST['action']) {
		case 'plugin_browser_post_fetch':
			bb_check_admin_referer('plugin_browser_post_fetch');
			
			if ($_POST['plugin_browser_revision_local'] < $_POST['plugin_browser_revision_remote']) {
				$plugin_browser->updateLocalRepositoryList();
				
				bb_admin_notice(sprintf(
					__('The plugin list was updated from revision %s to revision %s.'),
					$_POST['plugin_browser_revision_local'],
					$_POST['plugin_browser_revision_remote']
				));
			} else {
				bb_admin_notice(__('You already have the latest plugin list.'), 'error');
			}
			break;
		
		case 'plugin_browser_plugin_install':
			$plugin_browser_plugin_id = $_REQUEST['plugin_browser_plugin_id'];
			
			bb_check_admin_referer('plugin_browser_plugin_install_' . $plugin_browser_plugin_id);
			
			if ($plugin_browser->installPlugin($_REQUEST['plugin_browser_plugin_id'])) {
				bb_admin_notice(__('Installation of plugin complete.'));
			} else {
				bb_admin_notice(__('Installation of plugin failed.'), 'error');
			}
			break;
		
		case 'plugin_browser_plugin_uninstall':
			$plugin_browser_plugin_id = $_REQUEST['plugin_browser_plugin_id'];
			
			bb_check_admin_referer('plugin_browser_plugin_uninstall_' . $plugin_browser_plugin_id);
			
			if ($plugin_browser->uninstallPlugin($plugin_browser_plugin_id)) {
				bb_admin_notice(__('Plugin was successfully uninstalled.'));
			} else {
				bb_admin_notice(__('Plugin could not be uninstalled.'), 'error');
			}
			break;
		
		case 'plugin_browser_plugin_upgrade':
			$plugin_browser_plugin_id = $_REQUEST['plugin_browser_plugin_id'];
			
			bb_check_admin_referer('plugin_browser_plugin_upgrade_' . $plugin_browser_plugin_id);
			
			if ($plugin_browser->upgradePlugin($_REQUEST['plugin_browser_plugin_id'])) {
				bb_admin_notice(__('Upgrade of plugin complete.'));
			} else {
				bb_admin_notice(__('Upgrade of plugin failed.'), 'error');
			}
			break;
	}
}
?>