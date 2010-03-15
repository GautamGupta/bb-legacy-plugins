<?php
/*
 Plugin Name: Plugin Browser for bbPress
 Plugin URI: http://gaut.am/bbpress/plugins/plugin-browser/
 Description: Adds one-click installation and upgrade of plugins from the bbPress plugin repository.
 Author: Sam Bauers, Gautam Gupta
 Author URI: http://gaut.am/
 Version: 0.2
*/

/**
 * @license GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html
 */

bb_load_plugin_textdomain( 'plugin-browser-for-bbpress', dirname( __FILE__ ) . '/translations' ); /* Create Text Domain For Translations */

/**
 * Wrapper class for the Plugin Browser for bbPress plugin
 *
 * @author Sam Bauers <sam@viveka.net.au>
 * @author Gautam Gupta <admin@gaut.am>
 */
class Plugin_Browser {
	/**
	 * The URI of the plugin subversion repository
	 *
	 * @var string
	 */
	var $repositoryURI = 'http://plugins-svn.bbpress.org/';
	
	
	/**
	 * An indexed array of values that report the current state of local versions
	 *
	 * @var array
	 */
	var $localRepositoryData = false;
	
	
	/**
	 * Initialises the class
	 *
	 * @return void
	 */
	function Plugin_Browser() {
		// Nothing to inititalise
	}
	
	
	/**
	 * Retrieves the options for the plugin stored in the database
	 *
	 * @return mixed The options
	 */
	function getLocalRepositoryData() {
		if ( !$this->localRepositoryData )
			$this->localRepositoryData = bb_get_option( 'plugin_browser_local_data' );
		
		return $this->localRepositoryData;
	}
	
	
	/**
	 * Sets the options for the plugin stored in the database
	 *
	 * @return void
	 */
	function setLocalRepositoryData( $key, $value ) {
		if ( $this->localRepositoryData )
			$this->localRepositoryData = (array) bb_get_option( 'plugin_browser_local_data' );
		
		$this->localRepositoryData[$key] = $value;
		bb_update_option( 'plugin_browser_local_data', $this->localRepositoryData );
	}
	
	/**
	 * Gets a remote file
	 *
	 * @uses WP_Http
	 *
	 * @param string $URI The URL from which contents have to be fetched
	 * @param bool|string $getContents To get the body or not. If set to 'array', then the body is returned in an array form (split by '\n')
	 * @param string|bool $etag_match The ETag match to be done for revision. If set to false, then the match is not done, and revision is returned as 0.
	 * 
	 * @return array Array of headers, contents and revision
	 **/
	function getRemoteFile( $URI, $getContents = false, $etag_match = '|^W/"([0-9]+)//"$|' ) {
		/* make args and get data */
		$args = array( 'user_agent' => 'Plugin Browser for bbPress' );
		if ( !$getContents )
			$args['method'] = 'HEAD';
		$file = wp_remote_request( $URI, $args );
		
		/* Process headers & content */
		if ( $etag_match ) {
			$headers	= wp_remote_retrieve_headers( $file );
			$revision	= ( $headers['etag'] && preg_match( $etag_match, $headers['etag'], $matches ) ) ? (int) $matches[1] : 0;
		}
		$contents = trim( wp_remote_retrieve_body( $file ) );
		if ( $getContents === 'array' ) $contents = split( "\n", $contents );
		
		return array( 'headers' => $headers, 'contents' => $contents, 'revision' => $revision );
	}
	
	
	/**
	 * Gets the current subversion revision from the remote repository
	 *
	 * @return integer Remote Revision
	 */
	function getRemoteRepositoryRevision() {
		$file = $this->getRemoteFile( $this->repositoryURI );
		
		return $file['revision'];
	}
	
	
	/**
	 * Gets the current subversion revision that is stored locally
	 *
	 * @return integer If there is local revision, then that else 0
	 */
	function getLocalRepositoryRevision() {
		if ( $this->getLocalRepositoryData() )
			return $this->localRepositoryData['revision'];
		
		return 0;
	}
	
	
	/**
	 * Returns an array of all locally cached plugin browser data
	 *
	 * @return array Local Repository List
	 */
	function getLocalRepositoryList() {
		if ( !$this->localRepositoryList ) {
			if ( !$this->localRepositoryList = bb_get_option( 'plugin_browser_local_list' ) )
				$this->localRepositoryList = array();
		}
		
		return $this->localRepositoryList;
	}
	
	
	/**
	 * Formats the fetched remote list from the subversion repository as a nice array
	 *
	 * @return array Processed content
	 */
	function getList( $lines ) {
		$lines = preg_grep( '|^\s*<li>.*</li>\s*$|', $lines );
		$lines = array_map( create_function( '$input', '$output = str_replace(array("/", " "), "", strip_tags($input)); if ($output != "..") { return trim($output); }' ), $lines );
		$lines = array_filter( $lines );
		$lines = array_values( $lines );
		
		return $lines;
	}
	
	
	/**
	 * Gets the plugin meta data from the first few lines of comments in the file
	 *
	 * Adapted from the bbPress native function
	 *
	 * @param string $plugin_code The plugin code
	 *
	 * @return array Processed content
	 */
	function getPluginData( $plugin_code ) {
		/* Grab just the first commented area from the file */
		if ( !preg_match( '|/\*(.*?Plugin Name:.*?)\*/|ims', $plugin_code, $plugin_block ) )
			return false;
		$plugin_data = trim( $plugin_block[1] );
		
		preg_match( '|Plugin Name:(.*)$|mi',	$plugin_data, $name		);
		preg_match( '|Plugin URI:(.*)$|mi',	$plugin_data, $uri		);
		preg_match( '|Version:(.*)|i',		$plugin_data, $version		);
		preg_match( '|Description:(.*)$|mi',	$plugin_data, $description	);
		preg_match( '|Author:(.*)$|mi',		$plugin_data, $author		);
		preg_match( '|Author URI:(.*)$|mi',	$plugin_data, $author_uri	);
		
		$fields = array(			
			'name'		=> 'html',
			'uri'		=> 'url',
			'version'	=> 'text',
			'description'	=> 'html',
			'author'	=> 'html',
			'author_uri'	=> 'url'
		);
		foreach ( $fields as $field => $san ) {
			if ( !empty( ${$field} ) ) {
				${$field} = trim(${$field}[1]);
				switch ( $san ) {
				case 'html' :
					${$field} = bb_filter_kses( ${$field} );
					break;
				case 'text' :
					${$field} = esc_html(  ${$field} );
					break;
				case 'url' :
					${$field} = esc_url( ${$field} );
					break;
				}
			} else {
				${$field} = '';
			}
		}
		
		$plugin_data = compact( array_keys( $fields ) );
		$plugin_data['description'] = trim( $plugin_data['description'] );
		
		return $plugin_data;
	}
	
	
	/**
	 * Updates the local list of plugins
	 *
	 * @return integer The remote revision no.
	 */
	function updateLocalRepositoryList() {
		$file		= $this->getRemoteFile( $this->repositoryURI, 'array' );
		$remote_r	= $file['revision'];
		$plugins	= $this->getList( $file['contents'] );
		
		$latest_list	= array();
		$current_list	= $this->getLocalRepositoryList();
		
		foreach ( (array) $plugins as $plugin ) {
			$plugin_trunk		= $this->getRemoteFile( $this->repositoryURI . $plugin . '/trunk/', 'array', '|^W/"([0-9]+)//.*"$|' );
			$remote_revision	= $plugin_trunk['revision'];
			$local_revision		= $current_list[$plugin]['revision'];
			$local_version		= $current_list[$plugin]['version'];
			
			if ( !$local_revision || $local_revision < $remote_revision ) { /* Local revision and remote revision are always normal integers, so a normal php comparison would work on them (no need of version_compare) */
				$files = $this->getList( $plugin_trunk['contents'] );
				$files = preg_grep( '|.*\.php|', $files );
				if( !$files || count( $files ) <= 0 )
					continue;
				
				foreach ( $files as $file ) {
					$plugin_file = $this->getRemoteFile( $this->repositoryURI . $plugin . '/trunk/' . $file, true, false );
					if ( $data = $this->getPluginData( $plugin_file['contents'] ) ) {
						$latest_list[$plugin]			= $data;
						$latest_list[$plugin]['file']		= $file;
						$latest_list[$plugin]['revision']	= $remote_revision;
						break;
					}
				}
			} else {
				$latest_list[$plugin] = $current_list[$plugin];
			}
		}
		
		bb_update_option( 'plugin_browser_local_list', $latest_list );
		$this->localRepositoryList = $latest_list;
		$this->setLocalRepositoryData( 'revision', $remote_r );
		
		return $remote_r;
	}
	
	/**
	 * Nonce an URL
	 *
	 * @param string $key The unique key for nonce
	 * @param array $values The values to be appended in the URL
	 * @param string $page The main page whose nonce has to be made
	 *
	 * @uses bb_get_uri() To make the basic URL
	 * @uses bb_nonce_url() To make nonce
	 * @uses esc_attr() To escape the URL
	 *
	 * @return string The nonced URL
	 */
	function nonceUrl( $key, $values = array(), $page = 'bb-admin/admin-base.php' ) {
		return esc_attr(
			bb_nonce_url(
				bb_get_uri(
					$page,
					$values,
					BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
				),
				$key
			)
		);
	}
	
	
	/**
	 * Retrieves and installs a plugin
	 *
	 * @return boolean True if plugin is installed else false
	 */
	function installPlugin( $plugin_id ) {
		if ( !is_file( BB_PLUGIN_DIR . $plugin_id ) ) {
			if ( mkdir( BB_PLUGIN_DIR . $plugin_id ) ) {
				$file = $this->getRemoteFile( $this->repositoryURI . $plugin_id . '/trunk/', 'array', '|^W/"([0-9]+)//.*"$|' );
				$list = $this->getList( $file['contents'] );
				
				foreach ( $list as $item )
					$this->installRemoteFile($this->repositoryURI . $plugin_id . '/trunk/' . $item, BB_PLUGIN_DIR . $plugin_id . '/' . $item);
				
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 * Installs a file from a remote location, recurses through directories as necessary
	 *
	 * @return boolen true
	 */
	function installRemoteFile( $URI, $path ) {
		$file = $this->getRemoteFile( $URI, true, false );
		
		if ( is_string( $file ) ) {
			if ( !is_dir( $path ) )
				mkdir( $path, 0755 );
			$new_file	= $this->getRemoteFile( $file, 'array', '|^W/"([0-9]+)//.*"$|' );
			$list		= $this->getList( $new_file['contents'] );
			
			foreach ( $list as $item )
				$this->installRemoteFile( $file . $item, $path . '/' . $item );
		} else {
			$handle = fopen( $path, 'w' );
			fwrite( $handle, $file['contents'] );
			fclose( $handle );
		}
		
		return true;
	}
	
	
	/**
	 * Removes a plugin
	 *
	 * @return boolean true
	 */
	function uninstallPlugin( $plugin_id ) {
		$this->uninstallFile( BB_PLUGIN_DIR . $plugin_id);
		return true;
	}
	
	
	/**
	 * Recursively removes files
	 *
	 * @return boolen true
	 */
	function uninstallFile( $path ) {
		if ( is_dir( $path ) ) {
			foreach ( glob( $path . '/*' ) as $file )
				$this->uninstallFile( $file );
			rmdir( $path );
		} else {
			unlink( $path );
		}
		return true;
	}
	
	
	/**
	 * Removes the old plugin and installs an upgraded plugin
	 *
	 * @return boolean True if plugin upgraded else false
	 **/
	function upgradePlugin( $plugin_id ) {
		if ( $this->uninstallPlugin( $plugin_id ) )
			return $this->installPlugin($plugin_id);
		
		return false;
	}
	
	
	/**
	 * Tells us whether the plugins folder is writable
	 *
	 * @return boolean If writable then true else false
	 */
	function pluginsFolderWritable() {
		if ( is_writable( BB_PLUGIN_DIR ) )
			return true;
		
		return false;
	}
} /* End class Plugin_Browser */

$plugin_browser = new Plugin_Browser(); /* Initialise the class */

/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */

/* Add filters for the admin area */
add_action( 'bb_admin_menu_generator', 'plugin_browser_admin_page_add' );
add_action( 'bb_admin_head', 'plugin_browser_add_css' );

/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 */
function plugin_browser_admin_page_add() {
	bb_admin_add_submenu( __( 'Plugin Browser', 'plugin-browser-for-bbpress' ), 'use_keys', 'plugin_browser_admin_page' );
}


/**
 * Adds some CSS for use in the list
 *
 * @return void
 */
function plugin_browser_add_css() {
	global $plugin_browser;
	
	if ( $_GET['plugin'] == 'plugin_browser_admin_page' ) {
		if ( !$plugin_browser->pluginsFolderWritable() )
			bb_admin_notice( __( 'Your plugins directory is not writable by the web server. You will not be able to install plugins unless it is.', 'plugin-browser-for-bbpress' ), 'error' );
		?>
<style type="text/css" media="screen">
	/* <![CDATA[ */
	table.widefat tr.upgrade td { background-color: #DDDFFF; }
	/* ]]> */
</style>
		<?php
	}
}


/**
 * Writes an admin page for the plugin
 *
 * @return void
 */
function plugin_browser_admin_page() {
	global $plugin_browser;
	
	require_once( '../bb-admin/includes/functions.bb-plugin.php' );
	
	$localList	= $plugin_browser->getLocalRepositoryRevision();
	$remoteList	= $plugin_browser->getRemoteRepositoryRevision();

	if ( !$localList || $localList < $remoteList || ( isset( $_GET['force_update'] ) && $_GET['force_update'] == '1' ) ) {
		$oldList	= $localList;
		$localList	= $plugin_browser->updateLocalRepositoryList();
		bb_admin_notice( sprintf( __( 'The plugin list has been updated from revision %1$s to revision %2$s.', 'plugin-browser-for-bbpress' ), $oldList, $remoteList ) );
	}
	
	/* Load Plugin Arrays */
	$plugins	= (array) $plugin_browser->getLocalRepositoryList(); /* Full plugin list from repository */
	
	/*
	 * Abbreviations
	 * iu	- install/uninstall
	 * ad	- activate/deactivate
	 * u	- upgrade
	 * pb	- plugin browser
	 */
	
	if ( isset( $_REQUEST['pb_action'] ) ) {
		$pbaction = $_REQUEST['pb_action'];
		$plugin_browser_plugin_id = $_REQUEST['pb_plugin_id'];
		if ( !in_array( $plugin_browser_plugin_id, array_keys( $plugins ) ) ) bb_die( __( 'Invalid Request', 'plugin-browser-for-bbpress' ) );
		bb_check_admin_referer( $pbaction . '-plugin_' . $plugin_browser_plugin_id );
		switch ( $pbaction ) {
			case 'install':
				if ( $plugin_browser->installPlugin( $plugin_browser_plugin_id ) )
					bb_admin_notice( sprintf( __( '"%s" plugin was successfully installed.', 'plugin-browser-for-bbpress' ), $plugins[$plugin_browser_plugin_id]['name'] ) );
				else
					bb_admin_notice( sprintf( __( '"%s" plugin could not be installed. Please try again.', 'plugin-browser-for-bbpress' ), $plugins[$plugin_browser_plugin_id]['name'] ), 'error' );
				break;
			case 'uninstall':
				if ( $plugin_browser->uninstallPlugin( $plugin_browser_plugin_id ) )
					bb_admin_notice( sprintf( __( '"%s" plugin was successfully uninstalled.', 'plugin-browser-for-bbpress' ), $plugins[$plugin_browser_plugin_id]['name'] ) );
				else
					bb_admin_notice( sprintf( __( '"%s" plugin could not be uninstalled. Please try again.', 'plugin-browser-for-bbpress' ), $plugins[$plugin_browser_plugin_id]['name'] ), 'error' );
				break;
			case 'upgrade':
				if ( $plugin_browser->upgradePlugin( $plugin_browser_plugin_id ) )
					bb_admin_notice( sprintf( __( '"%s" plugin was successfully upgraded.', 'plugin-browser-for-bbpress' ), $plugins[$plugin_browser_plugin_id]['name'] ) );
				else
					bb_admin_notice( sprintf( __( '"%s" plugin could not be upgraded. Please try again.', 'plugin-browser-for-bbpress' ), $plugins[$plugin_browser_plugin_id]['name'] ), 'error' );
				break;
		}
	}
	
	/* Load Plugin Arrays */
	$bb_plugins	= (array) bb_get_plugins(); /* Local plugin list, which are installed */
	$active_plugins	= (array) bb_get_option( 'active_plugins' ); /* Local plugin list, which are installed and activated */
	
	?>
	<h2><?php _e( 'Plugin Browser for bbPress', 'plugin-browser-for-bbpress' ); ?></h2>
	
	<?php do_action( 'bb_admin_notices' ); ?>
	
	<p><?php printf( __( 'This plugin allows you to browse the plugin repository at %s.', 'plugin-browser-for-bbpress' ), '<a href="http://bbpress.org/plugins/">http://bbpress.org/plugins/</a>' ); ?><br /><?php _e( 'You can install, uninstall, activate, deactivate and upgrade the plugins via the actions below the plugin name.', 'plugin-browser-for-bbpress' ); ?></p>
	<p><?php _e( 'Some plugins require additional configuration to work. If you are not getting the results you expect, then check the plugin\'s page.', 'plugin-browser-for-bbpress' ); ?></p>
	<hr />
	
	<table id="plugins-list" class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Plugin',		'plugin-browser-for-bbpress' ); ?></th>
				<th><?php _e( 'Latest v.',	'plugin-browser-for-bbpress' ); ?></th>
				<th><?php _e( 'Local v.',	'plugin-browser-for-bbpress' ); ?></th>
				<th><?php _e( 'Description',	'plugin-browser-for-bbpress' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Plugin',		'plugin-browser-for-bbpress' ); ?></th>
				<th><?php _e( 'Latest v.',	'plugin-browser-for-bbpress' ); ?></th>
				<th><?php _e( 'Local v.',	'plugin-browser-for-bbpress' ); ?></th>
				<th><?php _e( 'Description',	'plugin-browser-for-bbpress' ); ?></th>
			</tr>
		</tfoot>
		<tbody>
	<?php
	
	foreach ( $plugins as $plugin_folder => $plugin ) {
		if ( $plugin['version'] ) {
			$iuaction	= 'install';
			$iuactionText	= __( 'Install', 'plugin-browser-for-bbpress' );
			$class		= 'inactive';
			$plugin_page	= '<a href="http://bbpress.org/plugins/topic/' . $plugin_folder . '/" target="_blank">' . __( 'Plugin Page', 'plugin-browser-for-bbpress' ) . '</a>';
			$cpf		= 'user#' . $plugin_folder . '/' . $plugin['file'];
			$cpf2		= 'user#pb--' . $plugin_folder . '/' . $plugin['file']; /* If the plugin had been installed earlier with this plugin */
			$plugin['version_local']	= __( 'None', 'plugin-browser-for-bbpress' );
			$plugin['revision_local']	= null;
			
			if ( $bb_plugins[$cpf] || $bb_plugins[$cpf2] ) {
				if ( $bb_plugins[$cpf2] ) {
					$bb_plugins[$cpf]	= $bb_plugins[$cpf2];
					$cpf			= $cpf2;
				}
				$iuaction	= 'uninstall';
				$iuactionText	= __( 'Uninstall', 'plugin-browser-for-bbpress' );
				
				if ( in_array( $cpf, array_values( $active_plugins ) ) ) {
					$class		=  'active';
					$action		= 'deactivate';
					$action_class	= 'delete';
					$action_text	= __( 'Deactivate', 'plugin-browser-for-bbpress' );
				} else {
					$class		= '';
					$action		= 'activate';
					$action_class	= 'edit';
					$action_text	= __( 'Activate', 'plugin-browser-for-bbpress' );
				}
				
				$adhref = $plugin_browser->nonceUrl( $action . '-plugin_' . $cpf, array( 'plugin_request' => 'all', 'action' => $action, 'plugin' => urlencode( $cpf ) ), 'bb-admin/plugins.php' );
				$adhtml = ' | <a class="' . $action_class . '" href="' . $adhref . '" target="_blank">' . $action_text . '</a>';
				
				if ( version_compare( $plugin['version'], $bb_plugins[$cpf]['version'], '>' ) ) {
					$class = 'upgrade';
					$uhref = $plugin_browser->nonceUrl( 'upgrade-plugin_' . $plugin_folder, array( 'plugin' => 'plugin_browser_admin_page', 'pb_action' => 'upgrade', 'pb_plugin_id' => urlencode( $plugin_folder ) ) );
					$uhtml = ' | <a href="' . $uhref . '">' . __( 'Upgrade', 'plugin-browser-for-bbpress' ) . '</a>';
				}
			}
			
			$iuhref = $plugin_browser->nonceUrl( $iuaction . '-plugin_' . $plugin_folder, array( 'plugin' => 'plugin_browser_admin_page', 'pb_action' => $iuaction, 'pb_plugin_id' => urlencode( $plugin_folder ) ) );
			$iuhtml = '<a href="' . $iuhref . '">' . $iuactionText . '</a>';
			?>
			
			<tr class="<?php echo $class; ?>">
				<td class="plugin-name">
					<span class="row-title">
						<?php echo ( $plugin['uri'] ) ? "<a href='{$plugin['uri']}' title='" . esc_attr__( 'Visit plugin site', 'plugin-browser-for-bbpress' ) . "'>{$plugin['name']}</a>" : $plugin['name']; ?>
						<br />
						<small><?php _e( 'By ', 'plugin-browser-for-bbpress' ); echo ( $plugin['author'] && $plugin['author_uri'] ) ? "<a href='{$plugin['author_uri']}' title='" . esc_attr__( 'Visit author homepage', 'plugin-browser-for-bbpress' ) . "'>" . $plugin['author'] . "</a>" : $plugin['author']; ?> | <?php echo $plugin_page; ?></small>
					</span>
					<div><span class="row-actions"><?php echo $iuhtml . $adhtml . $uhtml; ?></span></div>
				</td>
				<td class="latest-ver"><?php echo $plugin['version']; ?></td>
				<td class="local-ver"><?php echo ( $bb_plugins[$cpf]['version'] ) ? $bb_plugins[$cpf]['version'] : '-'; ?></td>
				<td class="plugin-description"><?php echo bb_autop( preg_replace( '/[\r\n]+/', "\n", $plugin['description'] ) ); ?></td>
			</tr>
			
			<?php
			unset( $adhtml );
			unset( $uhtml );
		}
	}
	?>
		</tbody>
	</table>
<?php
}
?>