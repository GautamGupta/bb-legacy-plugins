<?php

require('../../bb-load.php');

/// from php.net/htmlspecialchars
function jschars($str)
{
    $str = mb_ereg_replace("\\\\", "\\\\", $str);
    $str = mb_ereg_replace("\"", "\\\"", $str);
    $str = mb_ereg_replace("'", "\\'", $str);
    $str = mb_ereg_replace("\r\n", "\\n", $str);
    $str = mb_ereg_replace("\r", "\\n", $str);
    $str = mb_ereg_replace("\n", "\\n", $str);
    $str = mb_ereg_replace("\t", "\\t", $str);
    $str = mb_ereg_replace("<", "\\x3C", $str); // for inclusion in HTML
    $str = mb_ereg_replace(">", "\\x3E", $str);
    return $str;
}

if (bb_is_user_logged_in() && isset($_POST['quoted_id'])){
	$quoted_id = (int) $_POST['quoted_id'];
	bb_check_admin_referer('quote-' . $quoted_id);
	if ($quoted_id <= 0)
		bb_die("What are you doing??");
	
	$quoted_post = bb_get_quoted_post($quoted_id);
	if ($quoted_post === false || empty($quoted_post))
		bb_die("What are you doing??");
		
	$quoted_post = jschars($quoted_post) . "\\n";
	
	die("document.getElementById('post_content').value += '$quoted_post';");

} else 
	bb_die("What are you doing??");

?>