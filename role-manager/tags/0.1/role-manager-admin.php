<?php

function role_manager_admin_do_submit() {
	if ( !bb_verify_nonce( $_REQUEST['_wpnonce'], 'role-manager_' . $_GET['submit'] . ( in_array( $_GET['submit'], array( 'edit', 'delete' ) ) ? '-' . $_GET['role'] : '' ) ) ) {
		_e( 'Nothing to see here.' );
		return;
	}

	switch ( $_GET['submit'] ) {
		case 'delete':
			$old_roles = bb_get_option( 'role_manager_default' );

			if ( isset( $old_roles[$_GET['role']] ) ) { // Exists by default, probably a bad idea to get rid of it.
?>
<h2>Role <strong><?php echo $old_roles[$_GET['role']][0]; ?></strong> cannot be deleted.</h2>
<?php			return;
			}

			$new_roles = bb_get_option( 'role_manager_roles' );
			$role = $new_roles[$_GET['role']][0];
			unset( $new_roles[$_GET['role']] );
			bb_update_option( 'role_manager_roles', $new_roles );
?>
<h2>Role <strong><?php echo $role; ?></strong> deleted.</h2>
<?php		break;
		case 'edit':
			$_roles = bb_get_option( 'role_manager_roles' );
			if ( !isset( $_roles[$_GET['role']] ) )
				return;

			$role  = $_roles[$_GET['role']];
			$caps  = array_keys( array_filter( $role[1] ) );
			$roles = array_filter( $caps, 'role_manager_is_possible_role' );
			$caps  = array();

			foreach ( array_keys($_POST) as $cap ) {
				if ( role_manager_is_possible_cap($cap) ) {
					$caps[] = $cap;
				}
			}

			$new_role = array( $role[0], array_fill_keys( array_merge( $roles, $caps ), true ) );

			if ( $new_role != $role ) {
				$_roles[$_GET['role']] = $new_role;
				bb_update_option( 'role_manager_roles', $_roles );
			}
?>
<h2>Role <strong><?php echo $role[0]; ?></strong> updated.</h2>
<?php		break;
		case 'create':
			$role_name = role_manager_sanitize_role( $_POST['role'] );

			if ( role_manager_role_exists( $role_name ) ) {
				printf( __( 'The role %s already exists.', 'role-manager' ), $_POST['role'] );
				return;
			}
			$old_roles = bb_get_option( 'role_manager_default' );
			if ( $_POST['based_on'] == 'blank' )
				$new_role = array( $_POST['role'], array() );
			else
				$new_role = array( $_POST['role'], $old_roles[$_POST['based_on']][1] );

			$roles = bb_get_option( 'role_manager_roles' );
			$roles[$role_name] = $new_role;
			bb_update_option( 'role_manager_roles', $roles );
?>
<h2>Role <strong><?php echo $_POST['role']; ?></strong> created.</h2>
<?php
			break;
	}
}



function role_manager_admin_show_create_role( $role ) {
	$templates = role_manager_get_possible_roles();
?>
<h2>Create role <?php echo $role; ?></h2>
<form class="settings" action="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'submit', 'submit' => 'create' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>" method="post">
<fieldset>
<div>
	<label for="based_on">Base role on:</label>
	<div>
		<select id="based_on" name="based_on" class="select">
<?php foreach ( $templates as $template ) { ?>
			<option value="<?php echo $template[1]; ?>"><?php echo $template[0]; ?></option>
<?php } ?>
			<option value="blank" selected="selected">Start with a blank slate</option>
		</select>
	</div>
</div>
<div>
	<label for="role">Role name:</label>
	<div>
		<input id="role" name="role" type="text" class="text long" />
	</div>
</div>
</fieldset>
<fieldset class="submit">
	<input type="submit" class="submit" value="Create role" />
<?php bb_nonce_field( 'role-manager_create' ); ?>
</fieldset>
</form>
<?php
}



function role_manager_admin_show_role( $_role ) {
	global $bb_roles;

	$_role = role_manager_sanitize_role( $_role );

	if ( !role_manager_role_exists( $_role ) ) {
?>
<h2>Error: Role <strong><?php echo $_role; ?></strong> does not exist.</h2>
<?php	return;
	}

	$role = array( $bb_roles->role_names[$_role], $bb_roles->get_role( $_role )->capabilities );
?>
<h2>Editing role <strong><?php echo attribute_escape( $role[0] ); ?></strong></h2>
<form method="post" action="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'submit', 'submit' => 'edit', 'role' => $_role ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<table class="widefat">
<thead>
<tr>
	<th>Grant</th>
	<th>Description</th>
</tr>
</thead>
<tbody>
<?php

$caps        = array_filter( $role[1] );
$all_caps    = role_manager_get_possible_caps();
$all_roles   = role_manager_get_possible_roles();
$desc_length = max( array_map( 'strlen', $all_caps ) ) + 1;

foreach ( $all_roles as $cap => $desc ) { ?>
<tr<?php alt_class( 'role-manager_caps' ); ?>>
	<td><input type="checkbox"<?php if ( isset( $caps[$cap] ) ) echo ' checked="checked"'; ?> disabled="disabled" /></td>
	<td><strong>Role:</strong> <big><?php echo attribute_escape( $desc[0] ); ?></big></td>
</tr>
<?php }

foreach ( $all_caps as $cap => $desc ) { ?>
<tr<?php alt_class( 'role-manager_caps' ); ?>>
	<td><input type="checkbox"<?php if ( isset( $caps[$cap] ) ) echo ' checked="checked"'; ?> id="<?php echo $cap; ?>" name="<?php echo $cap; ?>" /></td>
	<td><?php echo attribute_escape( $desc ); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<input type="submit" class="submit" value="Save" />
<?php bb_nonce_field( 'role-manager_edit-' . $_role ); ?>
</form>
<?php
}



function role_manager_admin_show_main() {
	global $bb_roles;

	$old_roles = bb_get_option( 'role_manager_default' );

	$names = $bb_roles->role_names;
	ksort( $names );
?>
<h2>Role manager <small>[<a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'create' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">Add new</a>]</small></h2>

<ul>
<?php foreach ( $names as $key => $name ) {
	if ( $key == 'blocked' )
		continue;
?>
	<li style="font-size: 1.5em"<?php alt_class( 'role-manager_roles' ); ?>><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'edit', 'role' => $key ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php echo $name; ?></a>

<?php if ( !isset( $old_roles[$key] ) ) { ?> <small>[<a href="<?php echo attribute_escape( bb_nonce_url( bb_get_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'submit', 'submit' => 'delete', 'role' => $key ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ), 'role-manager_delete-' . $key ) ); ?>">Delete</a>]</small><?php } ?></li>
<?php } ?>
</ul>
<?php
}

?>