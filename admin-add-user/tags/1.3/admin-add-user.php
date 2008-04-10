<?php
/**
 * Plugin Name: Admin add user
 * Plugin Description: Allows keymasters to add user through the administration panel.
 * Author: Thomas Klaiber
 * Author URI: http://thomasklaiber.com/
 * Plugin URI: http://thomasklaiber.com/bbpress/admin-add-user/
 * Version: 1.3
 */
 
function admin_add_user_adminpage() {
	global $bbdb, $bb;
	
	// get the default registration functions
	require_once( BB_PATH . BB_INC . 'registration-functions.php');
?>
<h2><?php _e('Add New User') ?></h2>

<?php if (isset($_POST['submit'])) :

	bb_check_admin_referer('admin-add-user');

	if (empty($_POST['user_login'])) :
		$admin_add_user_error['user_login'] = "<li><strong>ERROR</strong>: Please enter a username.</li>";
	elseif (bb_user_exists(sanitize_user($_POST['user_login'], true))) :
		$admin_add_user_error['user_login'] = "<li><strong>ERROR</strong>: This username is already taken.</li>";	
	endif;
	
	if (empty($_POST['user_email'])) :
		$admin_add_user_error['email'] = "<li><strong>ERROR</strong>: Please type an e-mail address.</li>";	
	endif;
	
	if (empty($_POST['pass1']) || empty($_POST['pass2'])) :
		$admin_add_user_error['password'] = "<li><strong>ERROR</strong>: Please enter your password twice.</li>";
	elseif ($_POST['pass1'] != $_POST['pass2']) :	
		$admin_add_user_error['password'] = "<li><strong>ERROR</strong>: You passwords must match.</li>";	
	endif;
	
	if (!$admin_add_user_error) :
	
		$user_login =  $_POST['user_login'];
		$user_email = $_POST['user_email'];
		$user_url   = bb_fix_link( $_POST['user_url'] );
		$password   = $_POST['pass1'];
		
		if ( $user_id = bb_new_user( $user_login, $user_email, $user_url ) ) {
			bb_update_user_password( $user_id, $password );
			
			bb_update_usermeta( $user_id, $bbdb->prefix . 'capabilities', array('member' => true) );
			
			if ($_POST['user_send_email']) :
				/**
				 * Sending a mail again is bad, but if we don't do this,
				 * the user gets a wrong password sent.
				 * The bb_new_user() function can't be prevented from sending the first mail.
				 */
				$message = __("PLEASE IGNORE THE FIRST MAIL!\n\nYour username is: %1\$s \nYour new password is: %2\$s \nYou can now log in: %3\$s \n\nEnjoy!");

				bb_mail(
					$user_email,
					bb_get_option('name') . ': ' . __('Password'),
					sprintf( $message, $user_login, $password, bb_get_option('uri') )
				);
			endif;		
	
			do_action('bb_admin_new_user', $user_id, $password);
		}
	
		$admin_add_user_success['user_login'] = "<li>User <strong>".$user_login."</strong> has been added. <a href=\"".get_profile_tab_link($user_id, 'edit')."\">Edit user's profile &raquo;</a></li>";
		
		// clear for next use
		$_POST['user_login'] = "";
		$_POST['user_email'] = "";
		$_POST['user_url'] = "";
		$_POST['user_send_email'] = "";
	endif;

	if ($admin_add_user_error) : ?>
<div class='error'>
	<ul>
		<?php 
		echo $admin_add_user_error['user_login']; 
		echo $admin_add_user_error['email'];
		echo $admin_add_user_error['password'];
		?>
	</ul>
</div>
	<?php endif;
	if ($admin_add_user_success) : ?>
<div class='updated'>
	<ul>
		<?php 
		echo $admin_add_user_success['user_login'];
		?>
	</ul>
</div>	
	<?php endif; ?>
<?php endif; ?>

<div class="narrow">

<p>Users can <a href="<?php bb_option('uri'); ?>register.php">register themselves</a> or you can manually create users here.</p>
<form action="" method="post" name="admin-add-user">

<table class="editform" cellspacing="2" cellpadding="5">
	<tr>
		<th scope="row" width="33%"><?php _e('Username') ?>:</th>
		<td width="66%"><input name="user_login" type="text" value="<?php echo $_POST['user_login']; ?>" /></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Email') ?>:</th>
		<td><input name="user_email" type="text" value="<?php echo $_POST['user_email']; ?>" /></td>

	</tr>
	<tr>
		<th scope="row"><?php _e('Website') ?>:</th>
		<td><input name="user_url" type="text" value="<?php echo $_POST['user_url']; ?>" /></td>
	</tr>

	<tr>
		<th scope="row"><? _e('Password') ?> (twice):</th>

		<td><input name="pass1" type="password" />
		<br />
		<input name="pass2" type="password" /></td>
	</tr>
	
	<tr>
		<th scope="row"><? _e('Send Email') ?>:</th>
		<td><label><input name="user_send_email" type="checkbox" value="1" /> <? _e('Notify about registration.') ?></label></td>
	</tr>
</table>
<p class="submit">
	<input name="submit" type="submit" value="<?php _e('Add User &raquo;') ?>" /> 
</p>
<?php bb_nonce_field( 'admin-add-user' ); ?>
</form>
</div>
<?php
}

function admin_add_user_adminnav() {
	global $bb_submenu;
	$bb_submenu['users.php'][] = array(__('Add User'), 'administrate', 'admin_add_user_adminpage');
}
add_action( 'bb_admin_menu_generator', 'admin_add_user_adminnav' );

?>