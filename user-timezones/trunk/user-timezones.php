<?php
/*
Plugin Name: User Timezones
Description: Allow users to set their own timezone.
Plugin URI: http://bbpress.org/plugins/topic/32
Version: 0.1
Author: Michael D Adams
Author URI: http://blogwaffe.com/
*/

class User_Timezone {

	function get_user_offset( $user_id ) {
		$user = bb_get_user( (int) $user_id );

		if ( isset( $user->time_offset ) )
			return $user->time_offset;
		return false;
	}

	function bb_get_option_gmt_offset( $offset ) {
		if ( false !== $user_offset = $this->get_user_offset( bb_get_current_user_info( 'id' ) ) )
			return $user_offset;
		return $offset;
	}

	function profile_edited( $user_id ) {
		if ( isset($_POST['time_offset']) && is_numeric($_POST['time_offset']) )
			bb_update_usermeta( $user_id, 'time_offset', $_POST['time_offset'] );
	}

	function extra_profile_info( $user_id ) {
		$user_offset = $this->get_user_offset( $user_id ); ?>
	<table>
		<tr>
			<th scope="row"><label for="time_offset">Time Zone:</label></th>
			<td><input type="text" name="time_offset" id="time_offset" value="<?php echo $user_offset; ?>" /></td>
		</tr>
	</table>
<?php
	}

}

$user_timezone_obj = new User_Timezone();

add_action( 'extra_profile_info', array($user_timezone_obj, 'extra_profile_info') );
add_action( 'profile_edited', array($user_timezone_obj, 'profile_edited') );

add_filter( 'bb_get_option_gmt_offset', array($user_timezone_obj, 'bb_get_option_gmt_offset') );

?>
