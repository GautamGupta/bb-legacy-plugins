<?php
/*
Plugin Name: WangGuard
Plugin URI: http://www.wangguard.com
Description: <strong>Stop Sploggers</strong>. It is very important to use <a href="http://www.wangguard.com" target="_new">WangGuard</a> at least for a week, reporting your site's unwanted users as sploggers from the Users panel. WangGuard will learn at that time to protect your site from sploggers in a much more effective way. WangGuard protects each web site in a personalized way using information provided by Administrators who report sploggers world-wide, that's why it's very important that you report your sploggers to WangGuard. The longer you use WangGuard, the more effective it will become.
Version: 1.1.3
Author: WangGuard
Author URI: http://www.wangguard.com
License: GPL2
*/

/*  Copyright 2010  WangGuard (email : info@wangguard.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WANGGUARD_VERSION', '1.1.4');

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

//Which file are we are getting called from?
$wuangguard_parent = basename($_SERVER['SCRIPT_NAME']);


$wangguard_is_network_admin = function_exists("is_multisite");
if ($wangguard_is_network_admin)
	$wangguard_is_network_admin = is_multisite();


include_once 'wangguard-xml.php';
include_once 'wangguard-core.php';

$wangguard_api_key = wangguard_get_option('wangguard_api_key');





/********************************************************************/
/*** CONFIG BEGINS ***/
/********************************************************************/
include_once 'wangguard-conf.php';
include_once 'wangguard-queue.php';
include_once 'wangguard-wizard.php';
/********************************************************************/
/*** CONFIG ENDS ***/
/********************************************************************/

















/********************************************************************/
/*** ADD & VALIDATE SECURITY QUESTIONS ON REGISTER BEGINS ***/
/********************************************************************/

// for wp regular
add_action('register_form','wangguard_register_add_question');
add_action('register_post','wangguard_signup_validate',10,3);

// for buddypress 1.1 only
add_action('bp_before_registration_submit_buttons', 'wangguard_register_add_question_bp11');
add_action('bp_signup_validate', 'wangguard_signup_validate_bp11' );

// for wpmu and (buddypress versions before 1.1)
add_action('signup_extra_fields', 'wangguard_register_add_question_mu' );
add_filter('wpmu_validate_user_signup', 'wangguard_wpmu_signup_validate_mu');


//*********** WPMU ***********
//Adds a security question if any exists
function wangguard_register_add_question_mu($errors) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . "wangguardquestions";

	//Get one random question from the question table
	$qrs = $wpdb->get_row("select * from $table_name order by RAND() LIMIT 1");

	if (!is_null($qrs)) {
		$question = $qrs->Question;
		$questionID = $qrs->id;

		$html = '
			<label for="wangguardquestansw">' . $question . '</label>';
		echo $html;

		if ( $errmsg = $errors->get_error_message('wangguardquestansw') ) {
			echo '<p class="error">'.$errmsg.'</p>';
		}

		$html = '
			<input type="text" name="wangguardquestansw" id="wangguardquestansw" class="wangguard-mu-register-field" value="" maxlength="50" />
			<input type="hidden" name="wangguardquest" value="'.$questionID.'" />
		';
		echo $html;
	}
}

//Validates security question
function wangguard_wpmu_signup_validate_mu($param) {
	global $wangguard_bp_validated;

	//BP1.1+ calls the new BP filter first (wangguard_signup_validate_bp11) and then the legacy MU filters (this one), if the BP new 1.1+ filter has been already called, silently return
	if ($wangguard_bp_validated)
		return $param;

	$answerOK = wangguard_question_repliedOK();

	$errors = $param['errors'];

	//If at least a question exists on the questions table, then check the provided answer
	if (!$answerOK)
	    $errors->add('wangguardquestansw', addslashes( __('<strong>ERROR</strong>: The answer to the security question is invalid.', 'wangguard')));
	else {

		$reported = wangguard_is_email_reported_as_sp($param['user_email'] , $_SERVER['REMOTE_ADDR']);

		if ($reported) 
			$errors->add('user_email',  addslashes( __('<strong>ERROR</strong>: Banned by WangGuard <a href="http://www.wangguard.com/faq" target="_new">Is a mistake?</a>.', 'wangguard')));
	}
	return $param;
}
//*********** WPMU ***********




//*********** BP1.1+ ***********
//Adds a security question if any exists
function wangguard_register_add_question_bp11(){
	global $wpdb;

	$table_name = $wpdb->base_prefix . "wangguardquestions";

	//Get one random question from the question table
	$qrs = $wpdb->get_row("select * from $table_name order by RAND() LIMIT 1");

	if (!is_null($qrs)) {
		$question = $qrs->Question;
		$questionID = $qrs->id;

		$html = '
			<div id="wangguard-bp-register-form" class="register-section">
			<label for="wangguardquestansw">' . $question . '</label>';
		echo $html;

		do_action( 'bp_wangguardquestansw_errors' );
		
		$html = '
			<input type="text" name="wangguardquestansw" id="wangguardquestansw" value="" maxlength="50" />
			<input type="hidden" name="wangguardquest" value="'.$questionID.'" />
			</div>
		';
		echo $html;
	}
}

//Validates security question
function wangguard_signup_validate_bp11() {
	global $bp;
	global $wangguard_bp_validated;

	$wangguard_bp_validated = true;

	$answerOK = wangguard_question_repliedOK();

	//If at least a question exists on the questions table, then check the provided answer
	if (!$answerOK)
		$bp->signup->errors['wangguardquestansw'] = addslashes (__('<strong>ERROR</strong>: The answer to the security question is invalid.', 'wangguard'));
	else {

		$reported = wangguard_is_email_reported_as_sp($_REQUEST['signup_email'] , $_SERVER['REMOTE_ADDR']);

		if ($reported)
			$bp->signup->errors['signup_email'] = addslashes (__('<strong>ERROR</strong>: Banned by WangGuard <a href="http://www.wangguard.com/faq" target="_new">Is a mistake?</a>.', 'wangguard'));
	}
}
//*********** BP1.1+ ***********





//*********** WP REGULAR ***********
//Adds a security question if any exists
function wangguard_register_add_question(){
	global $wpdb;

	$table_name = $wpdb->base_prefix . "wangguardquestions";

	//Get one random question from the question table
	$qrs = $wpdb->get_row("select * from $table_name order by RAND() LIMIT 1");

	if (!is_null($qrs)) {
		$question = $qrs->Question;
		$questionID = $qrs->id;

		$html = '
			<div width="100%">
			<p>
			<label style="display: block; margin-bottom: 5px;">' . $question . '
			<input type="text" name="wangguardquestansw" id="wangguardquestansw" class="input wpreg-wangguardquestansw" value="" size="20" maxlength="50" tabindex="26" />
			</label>
			<input type="hidden" name="wangguardquest" value="'.$questionID.'" />
			</p>
			</div>
		';
		echo $html;
	}
}


//Validates security question
function wangguard_signup_validate($user_name , $user_email,$errors){
	$answerOK = wangguard_question_repliedOK();

	//If at least a question exists on the questions table, then check the provided answer
	if (!$answerOK)
		$errors->add('wangguard_error',__('<strong>ERROR</strong>: The answer to the security question is invalid.', 'wangguard'));
	else {

		$reported = wangguard_is_email_reported_as_sp($_REQUEST['user_email'] , $_SERVER['REMOTE_ADDR'] , true);

		if ($reported)
			$errors->add('wangguard_error',__('<strong>ERROR</strong>: Banned by WangGuard <a href="http://www.wangguard.com/faq" target="_new">Is a mistake?</a>.', 'wangguard'));
	}
}
//*********** WP REGULAR ***********





//Verify the email against WangGuard service
//$callingFromRegularWPHook regular WP hook sends true on this param
function wangguard_is_email_reported_as_sp($email , $clientIP , $callingFromRegularWPHook = false) {
	global $wpdb;
	global $wangguard_api_key;
	global $wangguard_user_check_status;

	if (empty ($wangguard_api_key))
		return false;

	$wangguard_user_check_status = "not-checked";

	$response = wangguard_http_post("wg=<in><apikey>$wangguard_api_key</apikey><email>".$email."</email><ip>".$clientIP."</ip></in>", 'query-email.php');
	$responseArr = XML_unserialize($response);

	wangguard_stats_update("check");

	if ( is_array($responseArr)) {
		if (($responseArr['out']['cod'] == '10') || ($responseArr['out']['cod'] == '11')) {
			wangguard_stats_update("detected");
			return true;
		}
		else {
			if ($responseArr['out']['cod'] == '20')
				$wangguard_user_check_status = 'checked';
			elseif ($responseArr['out']['cod'] == '100')
				$wangguard_user_check_status = 'error:' . __('Your WangGuard API KEY is invalid.', 'wangguard');
			else
				$wangguard_user_check_status = 'error:'.$responseArr['out']['cod'];
		}
	}

	return false;
}



//Verify the security question, used from the WP, WPMU and BP validation functions
function wangguard_question_repliedOK() {
	global $wpdb;

	$table_name = $wpdb->base_prefix . "wangguardquestions";

	//How many questions are created?
	$questionCount = $wpdb->get_col("select count(*) as q from $table_name");

	$answerOK = true;

	//If at least a question exists on the questions table, then check the provided answer
	if ($questionCount[0]) {
		$questionID = intval($_REQUEST['wangguardquest']);
		$answer = $_REQUEST['wangguardquestansw'];

		$qrs = $wpdb->get_row( $wpdb->prepare("select * from $table_name where id = %d" , $questionID));
		if (!is_null($qrs)) {
			if (mb_strtolower( $_REQUEST['wangguardquestansw'] ) == mb_strtolower( $qrs->Answer ) ) {
				$wpdb->query( $wpdb->prepare("update $table_name set RepliedOK = RepliedOK + 1 where id = %d" , $questionID ) );
			}
			else {
				$answerOK = false;
				$wpdb->query( $wpdb->prepare("update $table_name set RepliedWRONG = RepliedWRONG + 1 where id = %d" , $questionID ) );
			}
		}
		else {
			$answerOK = false;
			$wpdb->query( $wpdb->prepare("update $table_name set RepliedWRONG = RepliedWRONG + 1 where id = %d" , $questionID ) );
		}
	}

	return $answerOK;
}

/********************************************************************/
/*** ADD & VALIDATE SECURITY QUESTIONS ON REGISTER ENDS ***/
/********************************************************************/








/********************************************************************/
/*** USER REGISTATION & DELETE FILTERS BEGINS ***/
/********************************************************************/
// user register and delete actions
add_action('user_register','wangguard_plugin_user_register');
add_action('bp_complete_signup','wangguard_plugin_bp_complete_signup');
add_action('bp_core_activated_user','wangguard_bp_core_activated_user' , 10 , 3);
add_action('wpmu_activate_user','wangguard_wpmu_activate_user' , 10 , 3);

add_action('delete_user','wangguard_plugin_user_delete');
add_action('wpmu_delete_user','wangguard_plugin_user_delete');
add_action('make_spam_user','wangguard_make_spam_user');
add_action('bp_core_action_set_spammer_status','wangguard_bp_core_action_set_spammer_status' , 10 , 2);


//Save the status of the verification upon BP signsups
function wangguard_plugin_bp_complete_signup() {
	global $wpdb;
	global $wangguard_user_check_status;
	
	$table_name = $wpdb->base_prefix . "wangguardsignupsstatus";

	//delete just in case a previous record from a user which didn't activate the account is there
	$wpdb->query( $wpdb->prepare("delete from $table_name where signup_username = '%s'" , $_POST['signup_username']));

	//Insert the new signup record
	$wpdb->query( $wpdb->prepare("insert into $table_name(signup_username , user_status , user_ip) values ('%s' , '%s' , '%s')" , $_POST['signup_username'] , $wangguard_user_check_status , $_SERVER['REMOTE_ADDR'] ) );
}


//Account activated on BP
function wangguard_bp_core_activated_user($userid, $key, $user) {
	global $wpdb;
	global $wangguard_api_key;
	global $wangguard_user_check_status;

	wangguard_plugin_user_register($userid);
}

//Account activated on WPMU
function wangguard_wpmu_activate_user($userid, $password, $meta) {
	global $wpdb;
	global $wangguard_api_key;
	global $wangguard_user_check_status;

	wangguard_plugin_user_register($userid);
}

//Save the status of the verification against WangGuard service upon user registration
function wangguard_plugin_user_register($userid) {
	global $wpdb;
	global $wangguard_user_check_status;


	if (empty ($wangguard_user_check_status)) {
		$user = new WP_User($userid);
		$table_name = $wpdb->base_prefix . "wangguardsignupsstatus";

		//if there a status on the signups table?
		$user_status = $wpdb->get_var( $wpdb->prepare("select user_status from $table_name where signup_username = '%s'" , $user->user_login));

		//delete the signup status
		$wpdb->query( $wpdb->prepare("delete from $table_name where signup_username = '%s'" , $user->user_login));

		//If not empty, overrides the status with the signup status
		if (!empty ($user_status))
			$wangguard_user_check_status = $user_status;
	}


	$table_name = $wpdb->base_prefix . "wangguarduserstatus";

	$user_status = $wpdb->get_var( $wpdb->prepare("select ID from $table_name where ID = %d" , $userid));
	if ($user_status == null)
		//insert the new status
		$wpdb->query( $wpdb->prepare("insert into $table_name(ID , user_status , user_ip) values (%d , '%s' , '%s')" , $userid , $wangguard_user_check_status , $_SERVER['REMOTE_ADDR'] ) );
	else
		//update the new status
		$wpdb->query( $wpdb->prepare("update $table_name set user_status = '%s' where ID = %d" , $wangguard_user_check_status , $userid  ) );
}


//Delete the status of a user from the WangGuard status tracking table
function wangguard_plugin_user_delete($userid) {
	global $wpdb;

	$user = new WP_User($userid);
	
	//delete the signup status
	$table_name = $wpdb->base_prefix . "wangguardsignupsstatus";
	$wpdb->query( $wpdb->prepare("delete from $table_name where signup_username = '%s'" , $user->user_login));
	
	//delete the user status
	$table_name = $wpdb->base_prefix . "wangguarduserstatus";
	$wpdb->query( $wpdb->prepare("delete from $table_name where ID = %d" , $userid ) );
	
	//delete the user from the moderation queue
	$table_name = $wpdb->base_prefix . "wangguardreportqueue";
	$wpdb->query( $wpdb->prepare("delete from $table_name where ID = %d" , $userid ) );
	
	//delete the user reports from the moderation queue
	$table_name = $wpdb->base_prefix . "wangguardreportqueue";
	$wpdb->query( $wpdb->prepare("delete from $table_name where reported_by_ID = %d" , $userid ) );
}


//User has been reported as spam, send to WangGuard
function wangguard_make_spam_user($userid) {
	global $wpdb;

	//flag a user
	//get the recordset of the user to flag
	$wpusersRs = $wpdb->get_col( $wpdb->prepare("select ID from $wpdb->users where ID = %d" , $userid ) );

	wangguard_report_users($wpusersRs , "email" , false);
}

function wangguard_bp_core_action_set_spammer_status($userid , $is_spam) {
	if ($is_spam)
		wangguard_make_spam_user ($userid);
}
/********************************************************************/
/*** USER REGISTATION & DELETE FILTERS ENDS ***/
/********************************************************************/







/********************************************************************/
/*** AJAX FRONT HANDLERS BEGINS ***/
/********************************************************************/
add_action('wp_head', 'wangguard_ajax_front_setup');
add_action('wp_ajax_wangguard_ajax_front_handler', 'wangguard_ajax_front_callback');
function wangguard_ajax_front_setup() {
	global $wuangguard_parent;
	
	if (!is_user_logged_in()) return;?>
<script type="text/javascript" >
	
if (typeof ajaxurl == 'undefined')
	ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
else if (ajaxurl == undefined)
	ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
	
jQuery(document).ready(function() {
	jQuery(".wangguard-user-report").click(function() {
		if (!confirm('<?php echo addslashes(__("Do you confirm to report the user?" , "wangguard"))?>')) 
			return;
		
		var userID = jQuery(this).attr("rel");
		
		if ((userID == undefined) || (userID == '')) {
			userID = 0;
			
			//BP profile button doesn't allow to add a rel attr to the button so we store it in tne class field
			var tmpClass = jQuery(this).attr("class");
			var matches = tmpClass.match(/wangguard-user-report-id-(\d+)/);
			if (matches != null)
				userID = matches[1];
		}
		
		data = {
			action	: 'wangguard_ajax_front_handler',
			object	: 'user',
			wpnonce	: '<?php echo wp_create_nonce("wangguardreport")?>',
			userid	: userID
		};
		jQuery.post(ajaxurl, data, function(response) {
			if (response=='0') {
				alert('<?php echo addslashes(__('The user was reported.', 'wangguard'))?>');
				jQuery(".wangguard-user-report[rel='"+userID+"']").fadeOut();
				jQuery(".wangguard-user-report-id-"+userID).fadeOut();
			}
		});
	});
	
	
	jQuery(".wangguard-blog-report").click(function() {
		if (!confirm('<?php echo addslashes(__("Do you confirm to report the blog and authors?" , "wangguard"))?>')) 
			return;
		
		var blogID = jQuery(this).attr("rel");
		
		if ((blogID == undefined) || (blogID == '')) {
			blogID = 0;
			
			//BP profile button doesn't allow to add a rel attr to the button so we store it in tne class field
			var tmpClass = jQuery(this).attr("class");
			var matches = tmpClass.match(/wangguard-blog-report-id-(\d+)/);
			if (matches != null)
				blogID = matches[1];
		}
		
		data = {
			action	: 'wangguard_ajax_front_handler',
			object	: 'blog',
			wpnonce	: '<?php echo wp_create_nonce("wangguardreport")?>',
			blogid	: blogID
		};
		jQuery.post(ajaxurl, data, function(response) {
			if (response=='0') {
				alert('<?php echo addslashes(__('The blog was reported.', 'wangguard'))?>');
				jQuery(".wangguard-blog-report").fadeOut();
			}
		});
	});
});</script>
<?php
}

function wangguard_is_user_reported($userid) {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "wangguardreportqueue";
	$Count = $wpdb->get_col( $wpdb->prepare("select count(*) as q from $table_name where ID = %d" , $userid) );
	return $Count[0] > 0;
}
function wangguard_is_blog_reported($blogid) {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "wangguardreportqueue";
	$Count = $wpdb->get_col( $wpdb->prepare("select count(*) as q from $table_name where blog_id = %d" , $blogid) );
	return $Count[0] > 0;
}

function wangguard_ajax_front_callback() {
	global $wpdb;
	if (!is_user_logged_in()) return;

	//add user ID or blog ID to the 
	$object = $_REQUEST['object'];
	$nonce = $_REQUEST['wpnonce'];
	if ( !wp_verify_nonce( $nonce, 'wangguardreport' ) )
		die();

	$thisUserID = get_current_user_id();
	
	if ($object == "user") {
		$userid = (int)$_REQUEST['userid'];
		if (empty ($userid)) die();
		if (wangguard_is_user_reported($userid)) die("0");

		$user_object = new WP_User($userid);

		//do not add admins as reported
		if ( wangguard_is_admin($user_object) ) die("0");
		
		$table_name = $wpdb->base_prefix . "wangguardreportqueue";
		$wpdb->query( $wpdb->prepare("insert into $table_name(ID , blog_id , reported_by_ID) values (%d , NULL , %d)" , $userid , $thisUserID ) );
		echo "0";
	}
	elseif ($object == "blog") {
		$blogid = (int)$_REQUEST['blogid'];
		if (empty ($blogid)) die();
		if (wangguard_is_blog_reported($blogid)) die("0");

		$isMainBlog = false;
		if (isset ($current_site)) {
			$isMainBlog = ($blogid != $current_site->blog_id); // main blog not a spam !
		}
		elseif (defined("BP_ROOT_BLOG")) {
			$isMainBlog = ( 1 == $blogid || BP_ROOT_BLOG == $blogid );
		}
		else
			$isMainBlog = ($blogid == 1);

		
		//do not report main blog
		if ($isMainBlog) die("0");

		
		$table_name = $wpdb->base_prefix . "wangguardreportqueue";
		$wpdb->query( $wpdb->prepare("insert into $table_name(ID , blog_id , reported_by_ID) values (NULL , %d , %d)" , $blogid , $thisUserID ) );
		echo "0";
	}
	
	die();
}
/********************************************************************/
/*** AJAX FRONT HANDLERS ENDS ***/
/********************************************************************/






/********************************************************************/
/*** AJAX ADMIN HANDLERS BEGINS ***/
/********************************************************************/
add_action('admin_head', 'wangguard_ajax_setup');
add_action('wp_ajax_wangguard_ajax_handler', 'wangguard_ajax_callback');
add_action('wp_ajax_wangguard_ajax_recheck', 'wangguard_ajax_recheck_callback');
add_action('wp_ajax_wangguard_ajax_questionadd', 'wangguard_ajax_questionadd');
add_action('wp_ajax_wangguard_ajax_questiondelete', 'wangguard_ajax_questiondelete');

function wangguard_ajax_setup() {
	global $wuangguard_parent;
	
	if (!current_user_can('level_10')) return;
?>

<script type="text/javascript" >
var wangguardBulkOpError = false;

jQuery(document).ready(function($) {
	jQuery("a.wangguard-splogger").click(function() {
		var userid = jQuery(this).attr("rel");
		wangguard_report(userid , false);
	});

	jQuery("a.wangguard-splogger-blog").click(function() {
		var blogid = jQuery(this).attr("rel");
		wangguard_report_blog(blogid , false);
	});

	function wangguard_report(userid , frombulk) {
		var confirmed = true;
		<?php if (wangguard_get_option ("wangguard-expertmode")!='1') {?>
			if (!frombulk)
				confirmed = confirm('<?php echo addslashes(__('Do you confirm to flag this user as Splogger? This operation is IRREVERSIBLE and will DELETE the user.', 'wangguard'))?>');
		<?php }?>

		if (confirmed) {
			data = {
				action	: 'wangguard_ajax_handler',
				scope	: 'email',
				userid	: userid
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response=='0') {
					alert('<?php echo addslashes(__('The selected user couldn\'t be found on the users table.', 'wangguard'))?>');
				}
				else if (response=='-1') {
					wangguardBulkOpError = true;
					alert('<?php echo addslashes(__('Your WangGuard API KEY is invalid.', 'wangguard'))?>');
				}
				else if (response=='-2') {
					wangguardBulkOpError = true;
					alert('<?php echo addslashes(__('There was a problem connecting to the WangGuard server. Please check your server configuration.', 'wangguard'))?>');
				}
				else {
					<?php if ($wuangguard_parent == 'edit.php') {?>
					document.location = document.location;
					<?php }
					else {?>
					jQuery('td span.wangguardstatus-'+response).parent().parent().fadeOut();
					<?php }?>
				}
			});
		}
	}





	function wangguard_report_blog(blogid) {
		var confirmed = true;
		<?php if (wangguard_get_option ("wangguard-expertmode")!='1') {?>
			confirmed = confirm('<?php echo addslashes(__('Do you confirm to flag this blog\'s author(s) as Splogger(s)? This operation is IRREVERSIBLE and will DELETE the user(s).', 'wangguard'))?>');
		<?php }?>

		if (confirmed) {
			data = {
				action	: 'wangguard_ajax_handler',
				scope	: 'blog',
				blogid	: blogid
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response=='0') {
					alert('<?php echo addslashes(__('The selected blog couldn\'t be found.', 'wangguard'))?>');
				}
				else if (response=='-1') {
					wangguardBulkOpError = true;
					alert('<?php echo addslashes(__('Your WangGuard API KEY is invalid.', 'wangguard'))?>');
				}
				else if (response=='-2') {
					wangguardBulkOpError = true;
					alert('<?php echo addslashes(__('There was a problem connecting to the WangGuard server. Please check your server configuration.', 'wangguard'))?>');
				}
				else {
					jQuery('tr#blog-'+blogid).fadeOut();
					
					var users = response.split(",");
					for (i=0;i<=users.length;i++)
						jQuery('td span.wangguardstatus-'+users[i]).parent().parent().fadeOut();
				}
			});
		}
	}

	
	
	jQuery(".wangguard-queue-remove-blog").click(function() {
		if (!confirm('<?php echo addslashes(__("Do you confirm to remove the blog from the Moderation Queue?" , "wangguard"))?>')) 
			return;
		
		var blogID = jQuery(this).attr("rel");
		
		data = {
			action	: 'wangguard_ajax_handler',
			scope	: 'queue_blog_remove',
			wpnonce	: '<?php echo wp_create_nonce("wangguardreport")?>',
			blogid	: blogID
		};
		jQuery.post(ajaxurl, data, function(response) {
			if (response=='0') {
				jQuery("tr#blog-"+blogID).fadeOut();
			}
		});
	});


	
	jQuery(".wangguard-queue-remove-user").click(function() {
		if (!confirm('<?php echo addslashes(__("Do you confirm to remove the user from the Moderation Queue?" , "wangguard"))?>')) 
			return;
		
		var userID = jQuery(this).attr("rel");
		
		data = {
			action	: 'wangguard_ajax_handler',
			scope	: 'queue_user_remove',
			wpnonce	: '<?php echo wp_create_nonce("wangguardreport")?>',
			userid	: userID
		};
		jQuery.post(ajaxurl, data, function(response) {
			if (response=='0') {
				jQuery("tr#user-"+userID).fadeOut();
			}
		});
	});






	jQuery("a.wangguard-domain").click(function() {

		var confirmed = true;
		<?php if (wangguard_get_option ("wangguard-expertmode")!='1') {?>
			confirmed = confirm('<?php echo addslashes(__('Do you confirm to flag this user domain as Splogger? This operation is IRREVERSIBLE and will DELETE the users that shares this domain.', 'wangguard'))?>');
		<?php }?>

		if (confirmed) {
			data = {
				action	: 'wangguard_ajax_handler',
				scope	: 'domain',
				userid	: jQuery(this).attr("rel")
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response=='0') {
					alert('<?php echo addslashes(__('The selected user couldn\'t be found on the users table.', 'wangguard'))?>');
				}
				else if (response=='-1') {
					alert('<?php echo addslashes(__('Your WangGuard API KEY is invalid.', 'wangguard'))?>');
				}
				else if (response=='-2') {
					alert('<?php echo addslashes(__('There was a problem connecting to the WangGuard server. Please check your server configuration.', 'wangguard'))?>');
				}
				else {
					var users = response.split(",");
					for (i=0;i<=users.length;i++)
						jQuery('td span.wangguardstatus-'+users[i]).parent().parent().fadeOut();
				}
			});
		}
	});

	<?php 
	global $wuangguard_parent;
	if (($wuangguard_parent == 'ms-users.php') || ($wuangguard_parent == 'wpmu-users.php') || ($wuangguard_parent == 'users.php')) {?>
	jQuery(document).ajaxError(function(e, xhr, settings, exception) {
		alert('<?php echo addslashes(__('There was a problem connecting to your WordPress server.', 'wangguard'))?>');
	});
	<?php }?>


	jQuery("a.wangguard-recheck").click(function() {
		var userid = jQuery(this).attr("rel");
		wangguard_recheck(userid);
	});

	function wangguard_recheck(userid) {
		data = {
			action	: 'wangguard_ajax_recheck',
			userid	: userid
		};

		jQuery.post(ajaxurl, data, function(response) {
			if (response=='0') {
				alert('<?php echo addslashes(__('The selected user couldn\'t be found on the users table.', 'wangguard'))?>');
			}
			else if (response=='-1') {
				wangguardBulkOpError = true;
				alert('<?php echo addslashes(__('Your WangGuard API KEY is invalid.', 'wangguard'))?>');
			}
			else if (response=='-2') {
				wangguardBulkOpError = true;
				alert('<?php echo addslashes(__('There was a problem connecting to the WangGuard server. Please check your server configuration.', 'wangguard'))?>');
			}
			else {
				jQuery('td span.wangguardstatus-'+userid).fadeOut(500, function() {
					jQuery(this).html(response);
					jQuery(this).fadeIn(500);
				})
			}
		});
	}


	jQuery("a.wangguard-delete-question").live('click' , function() {

		<?php if (wangguard_get_option ("wangguard-expertmode")=='1') {?>
			var confirmed = true;
		<?php }
		else {?>
			var confirmed = confirm('<?php echo addslashes(__('Do you confirm to delete this question?.', 'wangguard'))?>');
		<?php }?>

		if (confirmed) {
			var questid	= jQuery(this).attr("rel");
			data = {
				action	: 'wangguard_ajax_questiondelete',
				questid	: questid
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response!='0') {
					jQuery("#wangguard-question-"+questid).slideUp("fast");
				}
			});
		}
	});


	
	jQuery("#wangguardnewquestionbutton").click(function() {
		jQuery("#wangguardnewquestionerror").hide();

		var wgq = jQuery("#wangguardnewquestion").val();
		var wga = jQuery("#wangguardnewquestionanswer").val();
		if ((wgq=='') || wga=='') {
			jQuery("#wangguardnewquestionerror").slideDown();
			return;
		}
		
		data = {
			action	: 'wangguard_ajax_questionadd',
			q		: wgq,
			a		: wga
		};
		jQuery.post(ajaxurl, data, function(response) {
			if (response!='0') {
				var newquest = '<div class="wangguard-question" id="wangguard-question-'+response+'">';
				newquest += '<?php echo addslashes(__("Question", 'wangguard'))?>: <strong>'+wgq+'</strong><br/>';
				newquest += '<?php echo addslashes(__("Answer", 'wangguard'))?>: <strong>'+wga+'</strong><br/>';
				newquest += '<a href="javascript:void(0)" rel="'+response+'" class="wangguard-delete-question"><?php echo addslashes(__('delete question', 'wangguard'))?></a></div>';
				
				jQuery("#wangguard-new-question-container").append(newquest);

				jQuery("#wangguardnewquestion").val("");
				jQuery("#wangguardnewquestionanswer").val("");
			}
			else if (response=='0') {
				jQuery("#wangguardnewquestionerror").slideDown();
			}
		});
	});



	<?php
	global $wuangguard_parent;
	if (($wuangguard_parent == 'ms-users.php') || ($wuangguard_parent == 'wpmu-users.php') || ($wuangguard_parent == 'users.php')) {?>
		var wangguard_bulk = '';
		wangguard_bulk += '<input style="margin-right:15px" type="button" class="button-secondary action wangguardbulkcheckbutton" name="wangguardbulkcheckbutton" value="<?php echo addslashes(__('Bulk check Sploggers' , 'wangguard')) ?>">';
		wangguard_bulk += '<input type="button" class="button-secondary action wangguardbulkreportbutton" name="wangguardbulkreportbutton" value="<?php echo addslashes(__('Bulk report Sploggers' , 'wangguard')) ?>">';
		jQuery("div.tablenav div.alignleft:first").append(wangguard_bulk);
		jQuery("div.tablenav div.alignleft:last").append(wangguard_bulk);

		jQuery('input.wangguardbulkcheckbutton').live('click' , function () {
			var userscheck;
			userscheck = jQuery('input[name="users[]"]:checked');

			//Checkboxes name varies thru WP screens (users.php / ms-users.php / wpmu-users.php) and versions
			if (userscheck.length == 0)
				userscheck = jQuery('input[name="allusers[]"]:checked');

			//Checkboxes name varies thru WP screens (users.php / ms-users.php / wpmu-users.php) and versions
			if (userscheck.length == 0)
				userscheck = jQuery('th.check-column input[type="checkbox"]:checked');

			wangguardBulkOpError = false;

			userscheck.each(function() {

					if (wangguardBulkOpError) {
						return;
					}

					wangguard_recheck(jQuery(this).val());
			});

		});

		jQuery('input.wangguardbulkreportbutton').live('click' , function () {

			if (!confirm('<?php _e('Do you confirm to flag the selected users as Sploggers? This operation is IRREVERSIBLE and will DELETE the users.' , 'wangguard')?>'))
				return;

			var userscheck;
			userscheck = jQuery('input[name="users[]"]:checked');

			//Checkboxes name varies thru WP screens (users.php / ms-users.php / wpmu-users.php) and versions
			if (userscheck.length == 0)
				userscheck = jQuery('input[name="allusers[]"]:checked');

			//Checkboxes name varies thru WP screens (users.php / ms-users.php / wpmu-users.php) and versions
			if (userscheck.length == 0)
				userscheck = jQuery('th.check-column input[type="checkbox"]:checked');


			wangguardBulkOpError = false;

			userscheck.each(function() {

					if (wangguardBulkOpError) {
						return;
					}

					wangguard_report(jQuery(this).val() , true);
			});

			//document.location = document.location;
		});
	<?php }?>
});
</script>
<?php
}



function wangguard_ajax_callback() {
	global $wpdb;

	if (!current_user_can('level_10')) die();
	
	$userid = intval($_POST['userid']);
	$scope = $_POST['scope'];
	
	
	switch ($scope) {
		case "queue_blog_remove":
			//remove blog from queue
			$blogid = intval($_POST['blogid']);
			$table_name = $wpdb->base_prefix . "wangguardreportqueue";
			$wpdb->query( $wpdb->prepare("delete from $table_name where blog_id = '%d'" , $blogid ) );
			echo "0";
			break;
		
		
		case "queue_user_remove":
			//remove user from queue
			$table_name = $wpdb->base_prefix . "wangguardreportqueue";
			$wpdb->query( $wpdb->prepare("delete from $table_name where ID = '%d'" , $userid ) );
			echo "0";
			break;
		
		
		case "domain":
			//flag domain
			$userDomain = new WP_User($userid);
			$domain = wangguard_extract_domain($userDomain->user_email);
			$domain = '%@' . str_replace(array("%" , "_"), array("\\%" , "\\_"), $domain);

			//get the recordset of the users to flag
			$wpusersRs = $wpdb->get_col( $wpdb->prepare("select ID from $wpdb->users where user_email LIKE '%s'" , $domain ) );
			echo wangguard_report_users($wpusersRs , $scope);
			break;
		
		
		case "blog":
			//flag domain
			$blogid = intval($_POST['blogid']);
			$blog_prefix = $wpdb->get_blog_prefix( $blogid );
			$authors = $wpdb->get_results( "SELECT user_id, meta_value as caps FROM $wpdb->users u, $wpdb->usermeta um WHERE u.ID = um.user_id AND meta_key = '{$blog_prefix}capabilities'" );
			$authorsArray = array();
			foreach( (array)$authors as $author ) {
				$caps = maybe_unserialize( $author->caps );
				if ( isset( $caps['subscriber'] ) || isset( $caps['contributor'] ) ) continue;
				
				$authorsArray[] = $author->user_id;
			}
			
			echo wangguard_report_users($authorsArray , "email");
			
			break;
		
		
		default:
			//flag a user
			//get the recordset of the user to flag
			$wpusersRs = $wpdb->get_col( $wpdb->prepare("select ID from $wpdb->users where ID = %d" , $userid ) );
			echo wangguard_report_users($wpusersRs , $scope);
			break;
	}

	die();
}

function wangguard_ajax_questionadd() {
	global $wpdb;

	if (!current_user_can('level_10')) die();

	$q = trim($_POST['q']);
	$a = trim($_POST['a']);


	if (get_magic_quotes_gpc()) {
		$q = stripslashes($q);
		$a = stripslashes($a);
	}

	if (empty ($q) || empty ($a)) {
		echo "0";
		die();
	}

	$table_name = $wpdb->base_prefix . "wangguardquestions";
	$wpdb->insert( $table_name , array( 'Question'=>$q  , "Answer"=>$a) , array('%s','%s') );

	echo $wpdb->insert_id;
	die();
}

function wangguard_ajax_questiondelete() {
	global $wpdb;

	if (!current_user_can('level_10')) die();

	$questid = intval($_POST['questid']);

	$table_name = $wpdb->base_prefix . "wangguardquestions";
	$wpdb->query( $wpdb->prepare("delete from $table_name where id = %d" , $questid) );

	echo $questid;
	die();
}

function wangguard_ajax_recheck_callback() {
	global $wpdb;
	global $wangguard_api_key;

	if (!current_user_can('level_10')) die();

	$userid = intval($_POST['userid']);

	$valid = wangguard_verify_key($wangguard_api_key);
	if ($valid == 'failed') {
		echo "-2";
		die();
	} 
	else if ($valid == 'invalid') {
		echo "-1";
		die();
	}

	$user_object = new WP_User($userid);
	if (empty ($user_object->user_email)) {
		echo "0";
		die();
	}

	if ( wangguard_is_admin($user_object) ) {
		echo '<span class="wangguard-status-no-status wangguardstatus-'.$userid.'">'. __('No status', 'wangguard') .'</span>';
		die();
	}

	$user_check_status = wangguard_verify_user($user_object);

	if ($user_check_status == "reported") {
		echo '<span class="wangguard-status-splogguer">'. __('Reported as Splogger', 'wangguard') .'</span>';
	}
	elseif ($user_check_status == "checked") {
		echo '<span class="wangguard-status-checked">'. __('Checked', 'wangguard') .'</span>';
	}
	elseif (substr($user_check_status,0,5) == "error") {
		echo '<span class="wangguard-status-error">'. __('Error', 'wangguard') . " - " . substr($user_check_status,6) . '</span>';
	}
	else
		return '<span class="wangguard-status-not-checked">'. __('Not checked', 'wangguard') .'</span>';

	die();
}
/********************************************************************/
/*** AJAX ADMIN HANDLERS ENDS ***/
/********************************************************************/




/********************************************************************/
/*** BP FRONTEND REPORT BUTTONS BEGINS ***/
/********************************************************************/
function wangguard_bp_report_button($id = '', $type = '') {

	if (!is_user_logged_in())
		return;
	
	if ( !$type && !is_single() )
		$type = 'activity';
	elseif ( !$type && is_single() )
		$type = 'blogpost';


	if ( $type == 'activity' ) :

		$activity = bp_activity_get_specific( array( 'activity_ids' => bp_get_activity_id() ) );

		if ( $activity_type !== 'activity_liked' ) :
			$user_id = $activity['activities'][0]->user_id;
			$user_object = new WP_User($user_id);
			if (empty ($user_object->ID)) return;
			if (!wangguard_is_admin($user_object)) :

				if ( true || !bp_like_is_liked( bp_get_activity_id(), 'activity' ) ) : ?>
				<a href="javascript:void(0)" class="fav wangguard-user-report" rel="<?php echo $user_object->ID;?>" title="<?php echo __('Report user', 'wangguard'); ?>"><?php echo  __('Report user', 'wangguard');?></a>
				<?php endif;
			endif;
		endif;

	elseif ( $type == 'blogpost' ) :
		global $post;
		if (empty ($post->post_author)) return;

		$user_id = $post->post_author;
		$user_object = new WP_User($user_id);
		if (empty ($user_object->ID)) return;
		if (!wangguard_is_admin($user_object)) :
			if (true || !bp_like_is_liked( $id, 'blogpost' ) ) : ?>

				<div class="activity-list"><div class="activity-meta"><a href="javascript:void(0)" class="fav wangguard-user-report" rel="<?php echo $user_object->ID;?>" title="<?php echo __('Report user', 'wangguard'); ?>"><?php echo  __('Report user', 'wangguard');?></a></div></div>

			<?php endif;
		endif;
		
	endif;
}
if (wangguard_get_option ("wangguard-enable-bp-report-btn")==1) {
	add_filter( 'bp_activity_entry_meta', 'wangguard_bp_report_button' );
	add_action( 'bp_before_blog_single_post', 'wangguard_bp_report_button' );
}

function wangguard_bp_report_button_header() {
	global $bp;
	if (!$bp) return;
	$user_object = new WP_User($bp->displayed_user->id);
	if (empty ($user_object->ID)) return;
	if (wangguard_is_admin($user_object)) return;
	echo bp_get_button( array(
		'id'                => 'wangguard_report_user',
		'component'         => 'wangguard_report_user',
		'must_be_logged_in' => true,
		'block_self'        => true,
		'wrapper_id'        => 'wangguard_report_user-button',
		'link_href'         => "javascript:void(0)",
		'link_class'        => 'wangguard-user-report wangguard-user-report-id-' . $user_object->ID,
		'link_title'        => __('Report user', 'wangguard'),
		'link_text'         => __('Report user', 'wangguard')
	) );
}
if (wangguard_get_option ("wangguard-enable-bp-report-btn")==1)
	add_action( 'bp_member_header_actions',    'wangguard_bp_report_button_header' , 20 );
/********************************************************************/
/*** BP FRONTEND REPORT BUTTONS ENDS ***/
/********************************************************************/



/********************************************************************/
/*** ADMIN BAR REPORT BEGIN ***/
/********************************************************************/
function wangguard_add_bp_admin_bar_menus() {
	global $current_blog , $wangguard_is_network_admin;

	if (!is_user_logged_in())
		return;
	

	$urlFunc = "admin_url";
	if ($wangguard_is_network_admin && function_exists("network_admin_url"))
		$urlFunc = "network_admin_url";

	
	if (function_exists("is_super_admin"))
		$showAdmin = is_super_admin();
	else
		$showAdmin = current_user_can('level_10');

	
	$queueEnabled = (wangguard_get_option("wangguard-enable-bp-report-blog") == 1) || (wangguard_get_option ("wangguard-enable-bp-report-btn")==1);
	
	// This is a blog, render a menu with links to all authors
	if ($showAdmin) {
		echo '<li id="wangguard-report-menu"><a href="'. $urlFunc( "admin.php?page=" . ($queueEnabled ? "wangguard_queue" : "wangguard-key-config") ).'">';
		_e('WangGuard', 'wangguard');
		echo '</a>';
		echo '<ul class="wangguard-report-menu-list">';

		if ( $current_blog && (wangguard_get_option("wangguard-enable-bp-report-blog") == 1) ) {
			if (BP_ROOT_BLOG != $current_blog->blog_id) {
			echo '<li>';
			echo '<a href="javascript:void(0)" class="wangguard-blog-report" rel="'.$current_blog->blog_id.'">';
			echo __('Report blog and author', 'wangguard') . '</a>';
			echo '</li>';
			}
		}

		
		if ($queueEnabled) {
			echo '<li>';
			echo '<a href="'.$urlFunc( "admin.php?page=wangguard_queue" ).'">';
			echo __('Moderation queue', 'wangguard') . '</a>';
			echo '<div class="admin-bar-clear"></div>';
			echo '</li>';
		}
		echo '<li>';
		echo '<a href="'.$urlFunc( "admin.php?page=wangguard_wizard" ).'">';
		echo __('Wizard', 'wangguard') . '</a>';
		echo '<div class="admin-bar-clear"></div>';
		echo '</li>';
		echo '<li>';
		echo '<a href="'.$urlFunc( "admin.php?page=wangguard-key-config" ).'">';
		echo __('Configuration', 'wangguard') . '</a>';
		echo '<div class="admin-bar-clear"></div>';
		echo '</li>';

		echo '</ul>';
		echo '</li>';
	}
	else {
		if ( $current_blog && (wangguard_get_option("wangguard-enable-bp-report-blog") == 1) ) {
			if (BP_ROOT_BLOG != $current_blog->blog_id) {
				echo '<li id="wangguard-report-menu-noop">';
				echo '<a href="javascript:void(0)" class="wangguard-blog-report" rel="'.$current_blog->blog_id.'">';
				echo __('Report blog and author', 'wangguard') . '</a>';
				echo '</a>';
				echo '</li>';
			}
		}
	}

}
add_action('bp_adminbar_menus', 'wangguard_add_bp_admin_bar_menus' , 10 );

function wangguard_add_wp_admin_bar_menus() {
	global $wp_admin_bar , $current_blog , $current_site , $wangguard_is_network_admin;

	if (!is_user_logged_in())
		return;
	
	$urlFunc = "admin_url";
	if ($wangguard_is_network_admin && function_exists("network_admin_url"))
		$urlFunc = "network_admin_url";

	$isMainBlog = false;
	if (defined("BP_ROOT_BLOG")) {
		$isMainBlog = ( 1 == $current_blog->blog_id || BP_ROOT_BLOG == $current_blog->blog_id );
	}
	else
		$isMainBlog = ($current_blog->blog_id == 1);
	
	$showReport = !$isMainBlog && (wangguard_get_option ("wangguard-enable-bp-report-blog")==1);

	$queueEnabled = (wangguard_get_option("wangguard-enable-bp-report-blog") == 1) || (wangguard_get_option ("wangguard-enable-bp-report-btn")==1);
	
	if (function_exists("is_super_admin"))
		$showAdmin = is_super_admin();
	else
		$showAdmin = current_user_can('level_10');
	
	if ($showAdmin) {
		$wp_admin_bar->add_menu( array( 'id' => 'wangguard-admbar-splog', 'title' => __( 'WangGuard', 'wangguard' ), 'href' => $urlFunc( "admin.php?page=" . ($queueEnabled ? "wangguard_queue" : "wangguard-key-config") ) ) );

		if ($showReport)
			$wp_admin_bar->add_menu( array( 'parent' => 'wangguard-admbar-splog', 'id' => "wangguard-admbar-report-blog", 'meta'=>array("class"=>"wangguard-blog-report wangguard-blog-report-id-".$current_blog->blog_id ), 'title' => __('Report blog and author', 'wangguard'), 'href' => '#' ) );

		if ($queueEnabled)
			$wp_admin_bar->add_menu( array( 'parent' => 'wangguard-admbar-splog', 'id' => "wangguard-admbar-queue", 'title' => __('Moderation queue', 'wangguard'), 'href' => $urlFunc( "admin.php?page=wangguard_queue" ) ) );
		
		$wp_admin_bar->add_menu( array( 'parent' => 'wangguard-admbar-splog', 'id' => "wangguard-admbar-wizard", 'title' => __('Wizard', 'wangguard'), 'href' => $urlFunc( "admin.php?page=wangguard_wizard" ) ) );
		$wp_admin_bar->add_menu( array( 'parent' => 'wangguard-admbar-splog', 'id' => "wangguard-admbar-settings", 'title' => __('Configuration', 'wangguard'), 'href' => $urlFunc( "admin.php?page=wangguard-key-config" ) ) );
	}
	elseif ($showReport) {
		$wp_admin_bar->add_menu( array( 'id' => "wangguard-admbar-report-blog", 'meta'=>array("class"=>"wangguard-blog-report wangguard-blog-report-id-".$current_blog->blog_id ), 'title' => __('Report blog and author', 'wangguard'), 'href' => '#' ) );
	}

}

add_action('admin_bar_menu', 'wangguard_add_wp_admin_bar_menus', 100 );
/********************************************************************/
/*** ADMIN BAR REPORT BEGIN ***/
/********************************************************************/





/********************************************************************/
/*** ADMIN GROUP MENU BEGINS ***/
/********************************************************************/
function wangguard_add_admin_menu() {
	if ( !is_super_admin() )
		return false;

	global $menu, $admin_page_hooks, $_registered_pages , $wpdb;

	$params = array(
		'page_title' => __( 'WangGuard', 'wangguard' ),
		'menu_title' => __( 'WangGuard', 'wangguard' ),
		'access_level' => 10,
		'file' => 'wangguard-key-config',
		'function' => 'wangguard_conf',
		'position' => 20
	);

	extract( $params, EXTR_SKIP );

	$file = plugin_basename( $file );

	$admin_page_hooks[$file] = sanitize_title( $menu_title );

	$hookname = get_plugin_page_hookname( $file, '' );
	if (!empty ( $function ) && !empty ( $hookname ))
		add_action( $hookname, $function );

	do {
		$position++;
	} while ( !empty( $menu[$position] ) );

	$menu[$position] = array ( $menu_title, $access_level, $file, $page_title, 'menu-top ' . $hookname, $hookname, $icon_url );

	$_registered_pages[$hookname] = true;

	$countSpan = "";
	$table_name = $wpdb->base_prefix . "wangguardreportqueue";
	$Count = $wpdb->get_col( "select count(*) as q from $table_name" );
	if ($Count[0] > 0)
		$countSpan = '<span class="update-plugins" ><span class="pending-count">'.$Count[0].'</span></span>';
	
	
	$queueEnabled = (wangguard_get_option("wangguard-enable-bp-report-blog") == 1) || (wangguard_get_option ("wangguard-enable-bp-report-btn")==1);
	
	add_submenu_page( 'wangguard-key-config', __( 'Configuration', 'wangguard'), __( 'Configuration', 'wangguard' ), 'manage_options', 'wangguard-key-config', 'wangguard_conf' );
	
	if ($queueEnabled) 
		add_submenu_page( 'wangguard-key-config', __( 'Moderation Queue', 'wangguard'), __( 'Moderation Queue', 'wangguard' ) . $countSpan, 'manage_options', 'wangguard_queue', 'wangguard_queue' );
	
	add_submenu_page( 'wangguard-key-config', __( 'Wizard', 'wangguard'), __( 'Wizard', 'wangguard' ), 'manage_options', 'wangguard_wizard', 'wangguard_wizard' );
}


if (!$wangguard_is_network_admin)
	add_action( 'admin_menu', 'wangguard_add_admin_menu' );
else
	add_action( 'network_admin_menu', 'wangguard_add_admin_menu' );
/********************************************************************/
/*** ADMIN GROUP MENU ENDS ***/
/********************************************************************/

?>