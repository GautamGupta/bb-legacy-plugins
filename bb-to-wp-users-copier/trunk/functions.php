<?php
/*
 Functions for BB-to-WP users copier
*/

function bbwpuc_configuration_page() {
?>
<h2>BB-to-WP users copier</h2>

<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', array('plugin' => 'bbwpuc_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
		<fieldset>
<p>To use this plugin, first, you need to integrate Wordpress and BBpress together. Please refer to this page to know more; <a href="http://bbpress.org/documentation/integration-with-wordpress/">BBpress Integration with WordPress</a>.</p>
		<p> Always make backups before dealing with database operations.<br>
		For this plugin to work correctly, no conflict IDs sould exist between users in BBpress and Wordpress.<br>
				It is better to make your forum/blog offline while merging users, turn it on after you finish this operation.</p>
		<div id="bbwpuc-status-check">
				<div class="label">
						Have I already copied users?
				</div>
				<div class="inputs">
						<code><?php 
									if ( !bb_get_option( 'bbwpuc_status' ) )
										echo 'No, you never did.';
									else
										echo bb_get_option('bbwpuc_status'); 
						?></code>
				</div>
		</div>
		<div id="bb-users-table">
				<div class="label">
						BBpress users table
				</div>
				<div class="inputs">
						<input type="text" value="<?php echo bb_get_option('bbwpuc_bb_users'); ?>" id="bbwpuc-bb-users" name="bbwpuc_bb_users" class="text short">
						<p>By default it is set to <strong>bb_users</strong></p>
				</div>
		</div>
		<div id="wp-users-table">
				<div class="label">
						Wrodpress users table
				</div>
				<div class="inputs">
						<input type="text" value="<?php echo bb_get_option('bbwpuc_wp_users'); ?>" id="bbwpuc-wp-users" name="bbwpuc_wp_users" class="text short">
						<p>By default it is set to <strong>wp_users</strong></p>
				</div>
		</div>
		<!--<div id="wp-path">
				<div class="label">
						Wrodpress wp-config.php path
				</div>
				<div class="inputs">
						<input type="text" value="<?php echo bb_get_option('bbwpuc_wp_path'); ?>" id="bbwpuc-wp-path" name="bbwpuc_wp_path" class="text short">
						<p>Set it to <strong>wordpress</strong> if your Wordpress folder exist in, for example; www.example.com/wordpress and your BBpress forum exists in the root of www.example.com/</p>
				</div>
		</div>-->
		<div id="wp-users-table">
				<div class="label">
						Fix Anonymous Members?
				</div>
				<div class="inputs">
						<input type="checkbox" value="true" id="fix-anonymous-members" name="fix_anonymous_members" class="checkbox">
						<p>Click here to read more about this "bug" <a href="http://bbpress.org/forums/topic/why-is-integration-so-troublesom#post-18135">what causes "anonymous" to show up</a>. To fix this we will copy <strong>user_login</strong> of the user inside his <strong>display_name</strong>. Newer versions of BBpress have this "bug" fixed.</p>
				</div>
		</div>
		</fieldset>
		<fieldset class="submit">
		<?php bb_nonce_field( 'bbwpuc-settings-update' ); ?>
		<input type="hidden" name="action" value="update-bbwpuc-settings" />
		<input type="submit" value="<?php _e( 'Copy Users' ) ?>" name="submit" class="submit">
		</fieldset>
</form>

<?php 
} 

// add plugin to the admin panel menu
function bbwpuc_configuration_page_add()
{
	bb_admin_add_submenu(__('BB-to-WP users copier'), 'moderate', 'bbwpuc_configuration_page', 'options-general.php');
}


// process the form
function bbwpuc_configuration_page_process()
{	
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-bbwpuc-settings') 
	{
		$goback = remove_query_arg( array( 'updated-bbwpuc', 'bbwpuc-wrong-path' ), wp_get_referer() );
		
		// check wordpress path
		/*if ( !file_exists( BB_PATH . $_POST['bbwpuc_wp_path'] . '/wp-includes/registration.php' ) ) {
			
			$goback = add_query_arg( 'bbwpuc-wrong-path', 'true', $goback );
			bb_safe_redirect( $goback );
			exit;
		}*/
			
		bb_check_admin_referer( 'bbwpuc-settings-update' );

		if ( $_POST['bbwpuc_bb_users'] ) {
			$value = stripslashes_deep( trim( $_POST['bbwpuc_bb_users'] ) );
			if ( $value )
				bb_update_option( 'bbwpuc_bb_users', $value );
			else
				bb_delete_option( 'bbwpuc_bb_users' );
		} else {
			bb_delete_option( 'bbwpuc_bb_users' );
		}
		
		if ( $_POST['bbwpuc_wp_users'] ) {
			$value = stripslashes_deep( trim( $_POST['bbwpuc_wp_users'] ) );
			if ( $value )
				bb_update_option( 'bbwpuc_wp_users', $value );
			else
				bb_delete_option( 'bbwpuc_wp_users' );
		} else {
			bb_delete_option( 'bbwpuc_wp_users' );
		}

		if ( $_POST['bbwpuc_wp_path'] ) {
			$value = stripslashes_deep( trim( $_POST['bbwpuc_wp_path'] ) );
			if ( $value )
				bb_update_option( 'bbwpuc_wp_path', $value );
			else
				bb_delete_option( 'bbwpuc_wp_path' );
		} else {
			bb_delete_option( 'bbwpuc_wp_path' );
		}
		
		bb_update_option( 'bbwpuc_status', 'Yes, you did.' );
		
		// start copying users
		global $bbdb;
		
		$bbdb->show_errors();
		
		$sql = "SELECT * FROM ". bb_get_option('bbwpuc_bb_users') ." ";
		
		$users = $bbdb->get_results( $sql );
		foreach ($users as $user) 
		{
			$ID = $user->ID;
			$user_login = $user->user_login;
			$user_pass = $user->user_pass;
			$user_nicename = $user->user_nicename;
			$user_email = $user->user_email;
			$user_url = $user->user_URL;
			$user_registered =  date("Y-m-d H:i:s", strtotime($user_registered));
			$user_status = $user->user_status;
			$display_name = $user->display_name;	
			
			$adduser = "INSERT INTO ". bb_get_option('bbwpuc_wp_users') ." 
						(ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_status, display_name) 
					  VALUES 
						($ID, '$user_login', '$user_pass', '$user_nicename', '$user_email', '$user_url', '$user_registered', '$user_status', '$display_name') 
					  ";
			$results = $bbdb->query( $adduser );
			
			/*$data = array(
					"ID" => $ID,
					"role" => 'subscriber'
			);
			wp_update_user($data);*/
		}
		
		// apply fixes
		if ( $_POST['bbwpuc_wp_users'] )
		{			
			global $bbdb;
			$bbdb->query("UPDATE ". bb_get_option('bbwpuc_wp_users') ." SET display_name=user_login WHERE display_name='' ");
		}
		
		// return to options
		$goback = add_query_arg( 'updated-bbwpuc', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['updated-bbwpuc'] ) )
		bb_admin_notice( __( '<strong>Users copied successfully.</strong><p>One last thing to adjust is to go to the Users page inside Wordpress dashboard and change roles for the newly added users; normally you would change role from <strong>None</strong> to <strong>Subscriber</strong>.</p>' ) );
	
	if ( !empty( $_GET['bbwpuc-wrong-path'] ) )
		bb_admin_notice( __( '<strong>The Wrodpress folder path you specified is not correct; we could\'t find wp-config.php to accomplish the update process.</strong>' ), 'error' );
		
	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}
add_action( 'bbwpuc_configuration_page_pre_head', 'bbwpuc_configuration_page_process' );

?>