<?php

$siteurl = addslashes(urldecode($_GET['bloginfo']));

?>

function quote_user_click(post_id, wp_nonce_url){
	var mysack = new sack("<?php echo $siteurl; ?>my-plugins/ajaxed-quote/" + wp_nonce_url);
	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar("quoted_id", post_id);
	mysack.onError = function() { alert('Ajax Error, please try again later.'); };
	mysack.runAJAX();
	return true;
}