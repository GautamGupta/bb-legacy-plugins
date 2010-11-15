<?php
/*
Plugin Name: WebPurify Profanity Filter
Plugin URI: http://www.webpurify.com/bbpress-plugin.php
Description: Uses the powerful WebPurify Profanity Filter Web Service to replaces profane words in user comments with '*' character.
Author: WebPurify
Version: 1.0
Author URI: http://www.webpurify.com/
*/


function bb_webpurify_verify_key( $key )
{
		$checkurl = "http://api1.webpurify.com/services/rest/?method=webpurify.live.check&api_key=".$key."&text=validate";	
		$response = simplexml_load_file($checkurl,'SimpleXMLElement', LIBXML_NOCDATA);
	
		if ( $response['stat'][0] != 'fail' ) {
			return true;
		} else {
			return false;
		}
}


function bb_wp_configuration_page()
{
?>
<h2><?php _e( 'WebPurify Settings' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bb_wp_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<p><?php printf( __( 'WebPurify will replace profane word in posts with a "*". You can manage your black and white lists by going to <a href="%s">WebPurify</a> and logging in to your account.' ), 'http://www.webpurify.com/' ); ?></p>

<?php
	$after = '';
	if ( false !== $key = bb_get_option( 'webpurify_key' ) ) {
		if ( bb_webpurify_verify_key( $key ) ) {
			$after = __( 'This key is valid' );
		} else {
			bb_delete_option( 'webpurify_key' );
		}
	}

	bb_option_form_element( 'webpurify_key', array(
		'title' => __( 'WebPurify License Key' ),
		'attributes' => array( 'maxlength' => 35 ),
		'after' => $after,
		'note' => sprintf( __( 'If you don\'t have a WebPurify License Key, you can get one at <a href="%s">webpurify.com</a>' ), 'http://www.webpurify.com/' )
	) );

?>

	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'options-webpurify-update' ); ?>
		<input type="hidden" name="action" value="update-webpurify-settings" />
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
	</fieldset>
</form>
<?php
}

function bb_wp_configuration_page_add()
{
	bb_admin_add_submenu( __( 'WebPurify' ), 'moderate', 'bb_wp_configuration_page', 'options-general.php' );
}
add_action( 'bb_admin_menu_generator', 'bb_wp_configuration_page_add' );

function bb_wp_configuration_page_process()
{
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-webpurify-settings') {
		bb_check_admin_referer( 'options-webpurify-update' );

		$goback = remove_query_arg( array( 'invalid-webpurify', 'updated-webpurify' ), wp_get_referer() );

		if ( $_POST['webpurify_key'] ) {
			$value = stripslashes_deep( trim( $_POST['webpurify_key'] ) );
			if ( $value ) {
				if ( bb_webpurify_verify_key( $value ) ) {
					bb_update_option( 'webpurify_key', $value );
				} else {
					$goback = add_query_arg( 'invalid-webpurify', 'true', $goback );
					bb_safe_redirect( $goback );
					exit;
				}
			} else {
				bb_delete_option( 'webpurify_key' );
			}
		} else {
			bb_delete_option( 'webpurify_key' );
		}

		$goback = add_query_arg( 'updated-webpurify', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['updated-webpurify'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	if ( !empty( $_GET['invalid-webpurify'] ) ) {
		bb_admin_notice( __( '<strong>The key you attempted to enter is invalid. Reverting to previous setting.</strong>' ), 'error' );
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}
add_action( 'bb_wp_configuration_page_pre_head', 'bb_wp_configuration_page_process' );

// Bail here if no key is set
if ( !bb_get_option( 'webpurify_key' ) ) {
	return;
}


function bb_wp_check_post( $post_text )
{
	global $bb_current_user;
	global $bb_wp_pre_post_status;

    $API_KEY = bb_get_option( 'webpurify_key' );
    $params = array(
      'api_key' => $API_KEY,
      'method' => 'webpurify.live.replace',
      'text' => $post_text,
      'replacesymbol' => '*'
    );


    $encoded_params = array();

    foreach ($params as $k => $v){
        $encoded_params[] = urlencode($k).'='.urlencode($v);
    }

#
# call the API and decode the response
#
    $url = "http://api1.webpurify.com/services/rest/?".implode('&', $encoded_params);

	$response = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
    $post_text = $response->text;
	

	return $post_text;
}
add_action( 'pre_post', 'bb_wp_check_post', 1 );


function bb_wp_check_title( $topic_title )
{
	global $bb_current_user;
	global $bb_wp_pre_topic_title_status;

    $API_KEY = bb_get_option( 'webpurify_key' );
    $params = array(
      'api_key' => $API_KEY,
      'method' => 'webpurify.live.replace',
      'text' => $topic_title,
      'replacesymbol' => '*'
    );


    $encoded_params = array();

    foreach ($params as $k => $v){
        $encoded_params[] = urlencode($k).'='.urlencode($v);
    }

#
# call the API and decode the response
#
    $url = "http://api1.webpurify.com/services/rest/?".implode('&', $encoded_params);

	$response = simplexml_load_file($url,'SimpleXMLElement', LIBXML_NOCDATA);
    $topic_title = $response->text;
	

	return $topic_title;
}
add_action( 'pre_topic_title', 'bb_wp_check_title', 1 );





?>