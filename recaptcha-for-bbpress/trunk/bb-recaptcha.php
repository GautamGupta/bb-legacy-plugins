<?php
/*
Plugin Name: reCAPTCHA for bbPress
Description:  Add reCAPTCHA human test to registration page.
Version: 1.1
Author: Dmitry Chestnykh
Author URI: http://www.codingrobots.com

License: 

Copyright (C) 2008, Dmitry Chestnykh  All rights reserved.

* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.


Inspired by:  Human Test for bbPress by _ck_.
              http://bbpress.org/plugins/topic/77

*/ 

// ---- Start editing here ----
//
//  Insert your values here.
//  (You can get API keys from http://recaptcha.net/api/getkey)
//
define('RECAPTCHA_PUBLIC_KEY', "your public API key here");
define('RECAPTCHA_PRIVATE_KEY', "your private API key here");
//
// ---- Stop editing here ----


require_once('recaptchalib.php');

function bb_recaptcha_is_register_page() 
{	
    return (bb_find_filename($_SERVER['PHP_SELF']) == "register.php");
    // PHP_SELF is already fixed by bb-settings.php
}

// Print challenge
function bb_recaptcha_print_challenge()
{
    if (!bb_recaptcha_is_register_page())
        return;

    global $publickey;
	
    echo "<script type='text/javascript'>var RecaptchaOptions = { theme : 'white', lang : 'en' , tabindex : 5 };</script>";
    echo '<fieldset><legend>'.__("Please prove you are human").'</legend>';
    echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
    echo '</fieldset>';
} 


// Check response to challenge
function bb_recaptcha_check() 
{
    if (!bb_recaptcha_is_register_page() || !$_POST)
        return;

    global $privatekey;

    $resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);
    if (!$resp || !$resp->is_valid) {
		bb_get_header();
		echo "<h2 id='register' class='error'>".__('Error')."</h2><p class='error'>".__("Sorry, you failed to pass human test. Please <a href='register.php'>go back and try again</a>!");
		bb_get_footer();
		exit;				
    }
} 

// Register callbacks
add_action('extra_profile_info', 'bb_recaptcha_print_challenge', 11);
add_action('bb_send_headers', 'bb_recaptcha_check');
