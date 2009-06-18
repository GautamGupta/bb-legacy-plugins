<?php
/*
Plugin Name: BB Clickcha
Plugin URI: http://clickcha.com/
Description: The one-click CAPTCHA.
Author: iDope
Version: 0.3
Author URI: http://clickcha.com/
*/


// Add admin menu for settings
add_action('bb_admin_menu_generator', 'clickcha_add_option_page');

function clickcha_add_option_page() {
    // Add a new submenu under options:
    //add_options_page('Clickcha', 'Clickcha', 'edit_themes', 'clickcha', 'clickcha_options_page');
	bb_admin_add_submenu(__('Clickcha'), 'use_keys', 'clickcha_options_page');
}

function clickcha_options_page() {
	if(isset($_POST['clickcha-action-savekeys'])) {
		bb_update_option('clickcha-public-key',$_POST['clickcha-public-key']);
		bb_update_option('clickcha-private-key',$_POST['clickcha-private-key']);
		echo "<div id='message' class='updated fade'><p>Clickcha settings saved.</p></div>";
    }
	else if(isset($_POST['clickcha-action-getkeys'])) {
		$response=file_get_contents('http://api.clickcha.com/getkeys?url='.urlencode($_POST['clickcha-url']).'&email='.urlencode($_POST['clickcha-email']));
		$result = get_submatch('|<result>(.+)</result>|i', $response);
		if(!empty($result)) {
			$public_key = get_submatch('|<publickey>([\w\-]+)</publickey>|', $result);
			$private_key = get_submatch('|<privatekey>([\w\-]+)</privatekey>|', $result);
			if(empty($public_key) || empty($private_key)) {
				echo "<div id='message' class='error fade'><p>Unable to get Clickcha API keys ($result).</p></div>";
			} else {
				bb_update_option('clickcha-public-key',$public_key);
				bb_update_option('clickcha-private-key',$private_key);
				echo "<div id='message' class='updated fade'><p>Clickcha API keys successfully saved. Clickcha is now active!</p></div>";
			}
		}
		else {
			echo "<div id='message' class='error fade'><p>Unable to get Clickcha API keys. Please contact developer@clickcha.com if this problem persists.</p></div><pre>$response</pre>";
		}
    }
	$public_key = bb_get_option('clickcha-public-key');
	$private_key = bb_get_option('clickcha-private-key');
	if(empty($public_key) || empty($private_key)) {
		echo "<div id='message' class='error fade'><p>Clickcha is not yet active. Enter Clickcha API keys below to make it work.</p></div>";
	}
    ?>
	<div class="wrap"><h2>Clickcha Settings</h2>
	<form name="site" action="" method="post" id="clickcha-form">

	<div>
	<fieldset>
	<legend><b><?php _e('Clickcha API Keys') ?></b></legend>

	<table>
		<tr>
			<td style="width: 100px"><label for="clickcha-public-key">Public Key:</label></td>
			<td style="width: 350px"><input name="clickcha-public-key" id="clickcha-public-key" value="<?php echo attribute_escape($public_key); ?>" type="text" size="25" /></td>
			<td style="width: 440px" rowspan="4">
				<table style="border: 1px solid #777; padding-left: 5px; width: 100%">
					<tr>
						<th colspan="2">Get your free Clickcha API keys.</th>
					</tr>
					<tr>
						<td style="width: 100px"><label for="clickcha-url">URL:</label></td>
						<td style="width: 340px"><input name="clickcha-url" id="clickcha-url" value="<?php bb_form_option('uri'); ?>" type="text" size="25" /> (required)</td>
					</tr>
					<tr>
						<td><label for="clickcha-email">Email:</label></td>
						<td><input name="clickcha-email" id="clickcha-email" value="<?php echo attribute_escape(bb_get_current_user_info( 'email' )); ?>" type="text" size="25" /></td>
					</tr>
					<tr>
						<td colspan="2" class="setting-description">We will not share your email address or spam you. It will be only used to send you API keys and occasional service updates.</td>
					</tr>
					<tr>
						<td colspan="2" class="submit"><input name="clickcha-action-getkeys" id="clickcha-action-getkeys" value="Get Keys" type="submit" /></td>
					</tr>
				</table>				
			</td>
		</tr>
		<tr>
			<td><label for="clickcha-private-key">Private Key:</label></td>
			<td><input name="clickcha-private-key" id="clickcha-private-key" value="<?php echo attribute_escape($private_key); ?>" type="text" size="25" /></td>
		</tr>
		<tr>
			<td colspan="2" class="setting-description">Note: API keys <strong>are</strong> case sensitive.</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td class="submit"><input name="clickcha-action-savekeys" id="clickcha-action-savekeys" type="submit" style="font-weight: bold;" value="Save Settings" /></td>
		</tr>
	</table>
	
	</fieldset>
	</div>
	</form>
	<small><a href="http://clickcha.com/">Clickcha - The One-click Captcha</a></small>
	</div>
	<?php
}


// add clickcha to the form
add_action('post_form', 'clickcha_form',10);	// post
add_action('extra_profile_info', 'clickcha_form',10); // registration
function clickcha_form() {
	if(bb_current_user_can('moderate')) return;
	$public_key = bb_get_option('clickcha-public-key');
	if(empty($public_key)) {
		echo "<div id='message' class='error fade'><p>Clickcha is not yet active. Please enter Clickcha API keys in settings.</p></div>";
	}
	else {
?>
	<style type="text/css">
	p.submit input {display: none;}
	input#clickcha {height: 100px; width: 200px; border: 0; margin: 0; padding: 0; display: inline;}
	</style>
	<input type="hidden" name="clickcha_token" id="clickchatoken" value="">
	<input type="image" name="clickcha" id="clickcha" alt="Clickcha - The One-click Captcha" src="">
	<br /><small><noscript><strong>Note:</strong> JavaScript is required to post comments.</noscript><?php // Please support this plugin by leaving the link intact ?><a href="http://clickcha.com/">Clickcha - The One-click Captcha</a></small>
	<script type="text/javascript">
		function clickcha_token(token) {
			document.getElementById('clickchatoken').value = token;
			document.getElementById('clickcha').src = 'http://api.clickcha.com/challenge?key=<?php echo $public_key; ?>&token=' + token;
		}
		function clickcha_get_token() {
			var e = document.createElement('script');
			e.src = 'http://api.clickcha.com/token?output=json&key=<?php echo $public_key; ?>&rnd=' + Math.random();
			e.type= 'text/javascript';
			document.getElementsByTagName('head')[0].appendChild(e); 
		}
		clickcha_get_token();
		// Firefox's bfcache workaround
		window.onpageshow = function(e){ if (e.persisted) clickcha_get_token(); };
	</script>
<?php
	}
}

// verify clickcha
add_action('bb_init', 'clickcha_form_post');
//add_filter('bb_get_option_throttle_time','');
function clickcha_form_post() {
	// Ignore trackbacks
	if(clickcha_verification_required()) {
		if(!isset($_POST['clickcha_x']) || !isset($_POST['clickcha_y'])) {
			bb_die("You did not click on the Clickcha image. Please <a href='javascript:history.back(1)'>go back</a> and try again.");
		}
		$public_key = bb_get_option('clickcha-public-key');
		$private_key = bb_get_option('clickcha-private-key');
		if(empty($public_key) || empty($private_key)) {
			echo "<p>Clickcha is not yet active. Please enter Clickcha API keys in settings.</p>";
		}
		else {
			$response=file_get_contents('http://api.clickcha.com/verify?key='.$public_key.'&token='.$_POST['clickcha_token'].'&private_key='.$private_key.'&x='.$_POST['clickcha_x'].'&y='.$_POST['clickcha_y']);
			$result = get_submatch('|<result>(\w+)</result>|', $response);
			if(!empty($result)) {
				if($result!='PASSED') {
					bb_die("Clickcha verification failed ($result). Please <a href='javascript:history.back(1)'>go back</a> and try again.");
				}
			}
			else {
				bb_die('Unable to verify Clickcha. Please contact the webmaster if this problem persists.'.$result);
			}
		}
	}
}

function clickcha_verification_required() {
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST) && !bb_current_user_can('moderate')) {
		return preg_match('/\b(?:bb-post|register)\.php\b/i',$_SERVER['PHP_SELF']);
	}
	return false;
}
function get_submatch($pattern, $subject, $submatch=1) {
	if(preg_match($pattern, $subject, $match)) {
		return $match[$submatch];
	}
}
?>
