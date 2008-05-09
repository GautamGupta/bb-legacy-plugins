<?php
/*
Plugin Name: User Languages
Description: Allow users to set their own language. This plugin is based on "User Timezones" plugin.
Plugin URI: http://bbpress.ru/downloads/plugins#user-languages
Version: 0.1
Author: A1ex
Author URI: http://bbpress.ru
*/

$languages = array('English'=>'en_US', 'Russian'=>'ru_RU'); // Specify your languages hear


class User_Language {

	function get_user_language( $user_id ) {
		$user = bb_get_user( (int) $user_id );

		if ( isset( $user->language ) )
			return $user->language;
		return false;
	}

	function bb_get_option_language( $language ) {
		if ( false !== $user_language = $this->get_user_language( bb_get_current_user_info( 'id' ) ) )
			return $user_language;
		return $language;
	}

	function profile_edited( $user_id ) {
		if ( isset($_POST['language']))// && is_numeric($_POST['language']) )
			bb_update_usermeta( $user_id, 'language', $_POST['language'] );
	}

	function extra_profile_info( $user_id ) {
		$user_language = $this->get_user_language( $user_id );
		global $languages;
?>
	<table>
		<tr>
			<th scope="row"><label for="language"><?php _e(Language) ?>:</label></th>
			<td>
			<select name="language" id="language">
					<? foreach($languages as $key => $language):?>
						<option value="<?=$language;?>" <?=($user_language==$language)?"selected='selected'":'';?>>
							<?php
								echo ($key);
							?>
						</option>
					<? endforeach;?>
				</select>
			</td>
		</tr>
	</table>
<?php
	}

}

$user_language_obj = new User_Language();

add_action( 'extra_profile_info', array($user_language_obj, 'extra_profile_info') );
add_action( 'profile_edited', array($user_language_obj, 'profile_edited') );

add_filter( 'locale', array($user_language_obj, 'bb_get_option_language') );

?>
