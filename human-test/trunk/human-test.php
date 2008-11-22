<?php
/*
Plugin Name: Human Test for bbPress
Plugin URI:  http://bbpress.org/plugins/topic/77
Description:  uses various methods to exclude bots from registering (and eventually posting) on bbPress
Version: 0.8.0
Author: _ck_
Author URI: http://bbshowcase.org

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/ 

add_action('bb_init', 'human_test_check',99);	// block check and init sessions if needed
add_action('post_form_pre_post', 'human_test_post',99);	// new post
add_action( 'extra_profile_info', 'human_test_registration',11); // registration

function human_test_location() {	// 0.8.3 cannot determine certain locations, none can see bb-post.php
$resource=array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']);
foreach ($resource as $name ) {if (false!==strpos($name, '.php')) {return bb_find_filename($name);}} 
return false;
}

function human_test_question() {
	$compare=$_SESSION['HUMAN_TEST'];	// grab correct answer from pre-stored session data	
	$xht=rand(2,$compare-1); 
	$yht=$compare-$xht;
	$question=__("How much does")." ".$xht." + ".$yht." = ";	
	$string = htmlentities($question, HTML_ENTITIES);     
	$string = preg_split("//", $string, -1, PREG_SPLIT_NO_EMPTY);
	$ord = 0;  for ( $i = 0; $i < count($string); $i++ ) {$ord = ord($string[$i]);          $string[$i] = '&#' . $ord . ';'; }
	return implode('',$string);	// entity encode entire question - needs human browser to decode
}

function human_test_post() {
if (bb_is_user_logged_in()) {return;}	// change to anon user detection
	$question=human_test_question();
	echo '<p><script language="JavaScript" type="text/javascript">document.write("'.$question.'");</script>';	// write question with javascript
	echo '<noscript><i>'.__("registration requires JavaScript").'</i></noscript>';	// warn no-script users 
	echo '<input name="human_test" type="text" id="human_test" size="15" maxlength="140" value="" autocomplete="off" /> ';  // answer field
	echo '('.__('required').')';
	echo '<input type="hidden" name = "'.session_name().'" value = "'.session_id().'" /></p>';	// improved session support without cookies or urls
} 

function human_test_registration() {
if (human_test_location()!="register.php") {return;}  //  only display on register.php and hide on profile page
	$question=human_test_question();
	echo '<fieldset><legend>'.__("Please prove you are human").'</legend><table width="100%"><tr class="required"><th scope="row" nowrap>';
	echo '<script language="JavaScript" type="text/javascript">document.write("'.$question.'");</script>';	// write question with javascript
	echo '<noscript><i>'.__("registration requires JavaScript").'</i></noscript>';	// warn no-script users 
	echo '</th><td width="72%"><input name="human_test" type="text" id="human_test" size="30" maxlength="140" value="" autocomplete="off" />';	// answer field
	echo '<input type="hidden" name = "'.session_name().'" value = "'.session_id().'" />';	// improved session support without cookies or urls
	echo '</td></tr></table></fieldset>';
} 

/*
global $page, $topic, $forum;
if ( isset($forum->forum_is_category) && $forum->forum_is_category ) {return;}
$add = topic_pages_add();	
$last_page = get_page_number( ( isset($topic->topic_posts) ? $topic->topic_posts : 0 ) + $add );	
if ( ( is_topic() && bb_current_user_can( 'write_post', $topic->topic_id ) && $page == $last_page ) || ( !is_topic() && bb_current_user_can( 'write_topic', isset($forum->forum_id) ? $forum->forum_id : 0 ) ) ) {
*/

function human_test_check() {
$location=human_test_location();
if (!($location=='register.php' || (!bb_is_user_logged_in() && (!empty($_GET['new']) || $location=='bb-post.php' || $location=='topic.php' || $location=='forum.php')) )) {return;}
    
	// one way or another we're gonna need sessions for now
	if (!isset($_SESSION)) {
	// @session_cache_limiter('nocache');	// "nocache" destroys form data with back button - "public" preserves form values when hitting back	
	@ini_set('session.use_trans_sid', false);
	@ini_set("url_rewriter.tags","");
	@session_start();	// sent with headers - errors masked with @ if sessions started previously - which it actually has to be for the following to 
	}
	if ($_POST || isset($_POST['human_test'])) {
		$human_test =  stripslashes_deep($_POST['human_test']);		
		$compare = $_SESSION['HUMAN_TEST'];
		$_SESSION['HUMAN_TEST']=md5(rand());	// destroy answer even when successful to prevent re-use
		
		if ($human_test !=$compare) {				
			// echo $human_test." - ".$compare; exit();	// debug
			// bb_die(__("Humans only please").". ".__("If you are not a bot").", <a href='register.php'>".__("please go back and try again")."</a>.");
			bb_send_headers();
			bb_get_header();
			echo "<br clear='both' /><h2 id='register' style='margin-left:2em;'>".__("Error")."</h2><p align='center'><font size='+1'>".
			__("Humans only please").". ".__("If you are not a bot").", <br />
			".__("please go back and try again").".
			</font></p><br />";
			bb_get_footer();
			exit;				
			// todo: limit registration attempts through session count
		} else {	
			 // passed test		
			$_SESSION['HUMAN_TEST']=rand(3,10);	// set ANOTHER answer, just in case there's a problem and they go back
			
			/*	previous destroyed session - unfortunately cannot do this because there still might be a problem  
			// stop session entirely to make server a little faster 
			$_SESSION = array();	// void session
			if (isset($_COOKIE[session_name()])) {@setcookie(session_name(), session_id(), 1, '/');}	// kill session cookie if any
			@session_destroy();		// this might affect other plugins that want sessions always, so maybe check other data before killing
			*/
		}
	} else {	
	$_SESSION['HUMAN_TEST']=rand(3,10);	// set answer: random math range between 3 and 10 (adjutable but recommended limit)
	}
} 

?>