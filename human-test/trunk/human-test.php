<?php
/*
Plugin Name: Human Test for bbPress
Description:  uses various methods to exclude bots from registering (and eventually posting) on bbPress
Plugin URI:  http://bbpress.org/plugins/topic/77
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.06

todo:
"negative fields" that are hidden and supposed to remain blank but spam bots try to fill, therefore fail
optionally write questions in captcha-like graphics (tricks spammers to enter graphic as code instead of answer)
optionally notify admin of failed registration

history:
0.01	first public release - hard-coded and only can test for 2+2=4 (obviously improvements coming soon)
0.05	now generates random numbers for addition between 3 and 10 (and uses sessions on registration page only)
0.06	minor logic bug fix to prevent multiple attempts against same answer
*/ 

function human_register_page() {	// determines if we're actually on register.php and returns true/false
foreach ( array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']) as $name )
if ( false !== strpos($name, '.php') ) $file = $name;
if (bb_find_filename( $file )=="register.php") {return true;} else {return false;}
}

function human_registration_test() {
if (human_register_page()) :  //  if (bb_current_user_can( 'administrate' ) )  :
	$xht=rand(2,$_SESSION['HUMAN_TEST']-1); 
	$yht=$_SESSION['HUMAN_TEST']-$xht;
	$question="How much does ".$xht." + ".$yht." = ";
	$string = htmlentities($question, HTML_ENTITIES);     $string = preg_split("//", $string, -1, PREG_SPLIT_NO_EMPTY);
	$ord = 0;     for ( $i = 0; $i < count($string); $i++ ) {$ord = ord($string[$i]);          $string[$i] = '&#' . $ord . ';'; }
	$question=implode('',$string);

	echo '<fieldset><legend>'.__("Please prove you are human").'</legend><table width="100%"><tr class="required"><th scope="row"  width=199>';
	echo '<script language="JavaScript" type="text/javascript">document.write("'.$question.'");</script>';
	echo '<noscript><i>registration requires JavaScript</i></noscript>';
	echo '</th><td align=left><input name="human_test" type="text" id="human_test" size="30" maxlength="140" value="" /></td></tr></table></fieldset>';
endif;
} add_action( 'extra_profile_info', 'human_registration_test');

function human_test_check() {
if (human_register_page()) : 
	if ($_POST || isset($_POST['human_test'])) {
		$human_test =  stripslashes_deep($_POST['human_test']);
		$compare = $_SESSION['HUMAN_TEST'];
		$_SESSION['HUMAN_TEST']=md5(rand());	// destroy answer even when successful to prevent re-use
		if ($human_test !=$compare) {				
			bb_die(__("Humans only please").". ".__("If you are not a bot").", <a href='register.php'>".__("please go back and try again")."</a>.");				
		}
	} else {
	@session_start();	// sent with headers - errors masked with @ if sessions started by another plugin
	$_SESSION['HUMAN_TEST']=rand(3,10);	// set answer: random math range between 3 and 10 (adjutable but recommended limit)
	}
endif;
} add_action('bb_send_headers', 'human_test_check');	


?>