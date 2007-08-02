<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Version: 0.8
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

// Configuration Settings
class avatarupload_config
{
	function avatarupload_config()
	{
		// Set default options
		$options = array(
			'avatar_dir'     => 'avatars/',
			'max_width'      => 150,
			'max_height'     => 150,
			'max_bytes'      => 1048576,
			'use_default'    => 1,
			'identicon_size' => 100,
			'file_extns' => array("gif", "jpg", "jpeg", "png")
		);
		
		// Get database options
		$db_options = bb_get_option('avatar_upload_options');
		
		// If there are options in the database
		if ($db_options) {
			// The database options override the defaults
			$options = array_merge($options, $db_options);
		}
		
		// If there is no trailing slash on the directory then add it
		if (substr($options['avatar_dir'], -1) != '/') {
			$options['avatar_dir'] .= '/';
		}
		
		// Avatar folder location (default is 'avatars' in the bbPress root folder)
		// You must create the folder before you install this plugin.
		$this->avatar_dir = $options['avatar_dir'];
		
		// Define maximum values allowed
		$this->max_width = $options['max_width']; // pixels
		$this->max_height = $options['max_height']; // pixels
		$this->max_bytes = $options['max_bytes']; // filesize (1024 bytes = 1 KB / 1048576 bytes = MB)

		// Use Unsharp Mask on resized truecolor images 1=yes (hidden option for now)
		$this->use_unsharpmask = 1;
		
		// Default avatar - set 'use_default' to '0' to display Identicon instead of default
		// The default URI is in the '$this->avatar_dir' folder.
		$this->default_avatar = array( 	
			'use_default' => $options['use_default'],
			'uri' =>  bb_get_option('uri') . $this->avatar_dir . 'default.png',
			'width' => 80,
			'height' => 80,
			'alt' => "User has not uploaded an avatar"
		);
		
		// Identicon dimensions (width/height are equal):
		$this->identicon_size = $options['identicon_size']; // pixels
		
		// Allowed file extensions
		$this->file_extns = $options['file_extns'];
		
		// Just pretty values (Kilobytes/megabytes) for output use
		$this->max_kbytes = round($this->max_bytes / 1024, 2);
		$this->max_mbytes = round($this->max_bytes / 1048576, 2);
	}
}

// Display the avatar image
function avatarupload_display($id, $force_db=0)
{
	if ($a = avatarupload_get_avatar($id,1,$force_db))
	{
		echo '<img src="'.$a[0].'" width="'.$a[1].'" height="'.$a[2].'" alt="'.$a[4].'" />';
	} else {
		$config = new avatarupload_config();

		if ($config->default_avatar['use_default'] == 1)
		{
			// Use a "genric" default avatar
			echo '<img src="'.$config->default_avatar['uri'].'" width="'.$config->default_avatar['width']
			.'" height="'.$config->default_avatar['height'].'" alt="'.$config->default_avatar['alt'].'" />';
		} else {
			// Or use Identicons instead.  New users will have an identicon automatically
			// created when they join, but this is for existing users with no avatar.

			felapplyidenticon($id); // create identicon

			// now fetch it from the database
			if ($a = avatarupload_get_avatar($id,1,$force_db))
			{
				echo '<img src="'.$a[0].'" width="'.$a[1].'" height="'.$a[2].'" alt="'.$a[4].'" />';
			}
		}
	}
}

// Get the avatar URI ($id = user->ID, $fulluri = full url to image,
// $force_db = get avatar from database where 'usermeta' not already available)
function avatarupload_get_avatar($id, $fulluri=1, $force_db=0)
{
	global $bbdb, $user;

	if ($id == $user->ID && $force_db == 0)
	{
		if (!empty($user->avatar_file)) {
			$a = explode("|", $user->avatar_file);
		} else {
			return false;
		}
	}
	else
	{
		$bb_query = "SELECT meta_value FROM $bbdb->usermeta WHERE meta_key='avatar_file' AND user_id='$id' LIMIT 1";

		if ( $avatar = $bbdb->get_results($bb_query) ) {
			$a = explode("|", $avatar[0]->meta_value);
		} else {
			return false;
		}
	}
	
	// do we want the full uri?
	if ($fulluri == 1)
	{
		$config = new avatarupload_config();
		$a[0] = bb_get_option('uri') . $config->avatar_dir . $a[0];
	}

	// Add the username for use in 'alt' attribute to end of array
	$a[] = $user->user_login;

	return $a;
}

// Add an "Upload Avatar" tab to the Profile menu
function add_avatar_tab()
{
	global $self;

	if ($self != 'avatar-upload.php') {
		add_profile_tab(__('Avatar'), 'edit_profile', 'moderate', 'avatar-upload.php');
	}
}
add_action( 'bb_profile_menu', 'add_avatar_tab' );


//  bbPress Identicon function by Fel64
function felapplyidenticon( $felID )
{
	$config = new avatarupload_config();
	$user = bb_get_user( $felID );

	$ifilename = strtolower($user->user_login) . "." . 'png';
	$ifilepath = BBPATH . $config->avatar_dir . $ifilename;

	// include the Identicon class.
	require_once("identicon.php");

	if (class_exists("identicon")) { $identicon = new identicon; }

	if( $identicon )
	{
		$felidenticon = $identicon->identicon_build( $user->user_login, '', false, '', false );

		if( imagepng( $felidenticon, $ifilepath ) )
		{
			$meta_avatar = $ifilename."?".time().'|'.$config->identicon_size.'|'.$config->identicon_size.'|identicon';
			bb_update_usermeta( $felID, 'avatar_file', $meta_avatar );
			$success_message = "Your identicon has been made.";
		}
	}
}

// Is user using an Identicon?
function usingidenticon($id)
{
	if ($a = avatarupload_get_avatar($id, 0, 1))
	{
		return ($a[3] == "identicon") ? true : false;
	} else {
		return false;
	}
}


// Unsharp Mask on image resize (truecolor images only)
function do_unsharp_mask($img, $use_unsharpmask=0)
{
	if ($use_unsharpmask == 1)
	{
		require_once("unsharpmask.php");
		return UnsharpMask($img, 80, 0.5, 3);
	}
	else
	{
		return $img;
	}
}

/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'avatar_upload_admin_page_add');
add_action('bb_admin-header.php', 'avatar_upload_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function avatar_upload_admin_page_add() {
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Avatar Upload'), 'use_keys', 'avatar_upload_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Avatar Upload'), 'use_keys', 'avatar_upload_admin_page');
		if (isset($bb_submenu['plugins.php'])) { // Build 740-793
			$bb_submenu['plugins.php'][] = $submenu;
		} else { // Build 277-739
			$bb_submenu['site.php'][] = $submenu;
		}
	}
}


/**
 * Writes an admin page for the plugin
 *
 * @return string
 * @author Sam Bauers
 * @modified Louise Dade
 **/
function avatar_upload_admin_page() {
	
	$config = new avatarupload_config();
	
	$gif_checked = (in_array('gif', $config->file_extns)) ? "checked=\"checked\" " : "";
	$jpg_checked = (in_array('jpg', $config->file_extns)) ? "checked=\"checked\" " : "";
	$png_checked = (in_array('png', $config->file_extns)) ? "checked=\"checked\" " : "";

	$use_default_checked = ($config->default_avatar['use_default']) ? "checked=\"checked\" " : "";
	$identicons_checked  = ($config->default_avatar['use_default']) ? "" : "checked=\"checked\" ";
?>
	<h2>Avatar Upload Settings</h2>
	<form method="post">

	<table cellpadding="5">
	<tr>
		<th scope="row" width="360"><label for="avatar_dir">Directory to where avatars are uploaded:</label></th>
		<td><code>bbPressRootDirectory/</code><input type="text" name="avatar_dir" id="avatar_dir" size="20" value="<?php echo($config->avatar_dir); ?>" /> <em>(location must be writable by the web server)</em></td>
	</tr>
	<tr>
		<th scope="row"><label for="max_width">Maximum allowed width of avatars in pixels:</label></th>
		<td><input type="text" name="max_width" id="max_width" size="4" value="<?php echo($config->max_width); ?>" /> pixels</td>
	</tr>
	<tr>
		<th scope="row"><label for="max_height">Maximum allowed height of avatars in pixels:</label></th>
		<td><input type="text" name="max_height" id="max_height" size="4" value="<?php echo($config->max_height); ?>" /> pixels</td>
	</tr>
	<tr>
		<th scope="row"><label for="max_bytes">Maximum allowed filesize of avatars in bytes:</label></th>
		<td><input type="text" name="max_bytes" id="max_bytes" size="10" value="<?php echo($config->max_bytes); ?>" /> bytes (n.b. 1 KB = 1024 bytes)</td>
	</tr>
	<tr>
		<th scope="row">Allow the following image types to be uploaded:</th>
		<td>
		<label><input type="checkbox" name="file_extns[]" value="gif" <?php echo($gif_checked); ?>/> GIF (*.gif)</label><br />
		<label><input type="checkbox" name="file_extns[]" value="jpg"  <?php echo($jpg_checked); ?>/> JPEG (*.jpeg; *.jpg)</label><br />
		<label><input type="checkbox" name="file_extns[]" value="png"  <?php echo($png_checked); ?>/> PNG (*.png)</label>
		</td>
	</tr>
	<tr>
		<th scope="row">Action to take when a user has no avatar present:</th>
		<td>
		<label><input type="radio" name="use_default" value="1" <?php echo($use_default_checked); ?>/> Use the default avatar image</label> 
		(<a href="<?php echo $config->default_avatar['uri']; ?>" target="_blank" title="View default avatar in a new window">view</a>).<br />
		<label><input type="radio" name="use_default" value="0" <?php echo($identicons_checked); ?>/> Use an auto generated identicon</label>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="identicon_size">Height &amp; width of identicons in pixels (if used):</label></th>
		<td><input type="text" name="identicon_size" id="identicon_size" size="4" value="<?php echo($config->identicon_size); ?>" /> pixels</td>
	</tr>
	</table>
		<input name="action" type="hidden" value="avatar_upload_settings_post"/>
		<p class="submit"><input type="submit" name="submit" value="Save Avatar Upload Settings" /></p>
	</form>

	<h3>Don't Forget!</h3>

	<p>If you haven't already done so: upload the files in the "additional-files" directory to the following locations.</p>
	<ul>
		<li><code>avatars/</code> - directory to the location specified on the admin page, rename if neccesary.</li>
		<li><code>avatars/default.png</code> - default avatar image into the directory created above.</li>
		<li><code>avatar-upload.php</code> - bbPress root directory.</li>
		<li><code>my-templates/avatar.php</code> - your <code>my-templates/my-template-name/</code> (or <code>bb-templates/kakumei/</code>) directory.</li>
	</ul>

<p><strong>Read the <code>readme.txt</code> file that came with this plugin for more detailed instructions.</strong></p>

<?php
}


/**
 * Processes the admin page form
 *
 * @return void
 * @author Sam Bauers
 * @modified Louise Dade
 **/
function avatar_upload_admin_page_process()
{
	if (isset($_POST['submit'])) {
		if ($_POST['action'] == 'avatar_upload_settings_post') {
			
			$options = array();
			
			if ($_POST['avatar_dir']) {
				$options['avatar_dir'] = $_POST['avatar_dir'];
			}
			
			if (ereg("^[0-9]{1,}$", $_POST['max_width'])) {
				$options['max_width'] = $_POST['max_width'];
			}
			
			if (ereg("^[0-9]{1,}$", $_POST['max_height'])) {
				$options['max_height'] = $_POST['max_height'];
			}
			
			if (ereg("^[0-9]{1,}$", $_POST['max_bytes'])) {
				$options['max_bytes'] = $_POST['max_bytes'];
			}
			
			if ($_POST['file_extns']) {
				$valid = array("gif", "jpg", "png"); // not user configurable!

				foreach ($_POST['file_extns'] as $extn)
				{
					if (in_array($extn, $valid)) {
						$options['file_extns'][] = $extn;
						if ($extn == "jpg") {
							$options['file_extns'][] = "jpeg";
						}
					}
				}
			}
			
			if (!$_POST['use_default']) {
				$options['use_default'] = 0;
			}
			
			if (ereg("^[0-9]{1,}$", $_POST['identicon_size'])) {
				$options['identicon_size'] = $_POST['identicon_size'];
			}
			
			if (count($options)) {
				bb_update_option('avatar_upload_options', $options);
			} else {
				bb_delete_option('avatar_upload_options');
			}
			
			bb_admin_notice(__('Settings Saved'));
		}
	}
}
?>