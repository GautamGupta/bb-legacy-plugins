<?php

/*
Plugin Name: bb-myAvatars
Plugin URI: http://www.purpledragonslair.co.uk
Description: This plugin allows you to add MyBlogLog.com avatars to bbpress posts .
Version: 0.1
Author: Joanne Corless

2006-12-13 "Hey, What about getting rid of Gravatar?"
*/


add_action("bb_head", "ma_header",1);
	
function ma_header() 
{
	echo '<link rel="stylesheet" href="'.bb_get_option( 'uri' ).'my-plugins/bb-myavatar-style.css" type="text/css" media="screen" />'."\n";
}

function bb_myavatars($id,$gravatar = false) 
{
	
// START CONFIGURATION ***
	
	// Link URL when Gravatar is displayed
	$gravatar_URL  		= "http://www.gravatar.com/";

	// Link titles for mybloglog avatars: when avatars exist or not.
	$mybloglog_TITLE 	= "See my profile on MyBlogLog.com!";
	$mybloglog_NO_TITLE = "Get a profile on MyBlogLog.com!";

	// Link title for gravatar
	$gravatar_TITLE  	= "Get a Gravatar";

	// Image shown when no avatar(s) are found, leave blank for the services' default
	$default_IMG		= "http://www.mybloglog.com/buzz/images/pict_none.gif";

	// Disabling this will make everyone see emails in links,
	// and MyBlogLog avatars shown also when no URL is provided.
	$safe_email 		= true;
	
	// END OF CONFIGURATION ***

	$user = bb_get_user( $id );

	// Getting vars (now with Track(Ping)backs!)
	
		$url      = $user->user_url;	    			
		$email	  = $user->user_email;
		$nickname = $user->user_url;
	
	if($email == "")
	{
		// This is a track(ping)back
		// This works only for domains like http://www.myblog.ext
		// It will not work for blogs' address like http://www.mysite.ext/blog 
		$url = explode("/",$url);
		$url = "http://" . $url[2];
		
		// Just for a clean output...
		$nickname = ""; 
	}

	// Base URL for services
	if($url != ""  &&  $url != "http://")
		$mybloglog_URL = "http://www.mybloglog.com/buzz/co_redir.php?t=&amp;href=" . $url . "&amp;n=". $nickname;
	elseif($safe_email)
		$mybloglog_URL = "http://www.mybloglog.com/buzz/co_redir.php?t=";
	else
		$mybloglog_URL = "http://www.mybloglog.com/buzz/co_redir.php?t=&amp;href=mailto:" . $email . "&amp;n=". $nickname;

	// Image URL for services

	if($url != ""  &&  $url != "http://")
		$mybloglog_IMG = "http://pub.mybloglog.com/coiserv.php?href=" . $url . "&amp;n=". $nickname;
	elseif($safe_email)
		$mybloglog_IMG = "http://pub.mybloglog.com/coiserv.php?href=&amp;n=". $nickname;
	else
		$mybloglog_IMG = "http://pub.mybloglog.com/coiserv.php?href=mailto:" . $email . "&amp;n=". $nickname;

	$gravatar_IMG = "http://www.gravatar.com/avatar.php?gravatar_id=". md5($email) . "&amp;size=48&amp;default=".$default_IMG;

?> 
	<a href="<?php echo $mybloglog_URL; ?>" target="_blank" title="<?php echo $mybloglog_TITLE; ?>">
		<img class="MyAvatars" border="0" src="<?php echo $mybloglog_IMG; ?>" onload=" if (this.width > 48) { this.width = 48; this.height = 48; } ; if (this.width < 48) { <?php echo ($gravatar) ? "this.parentNode.href = '" . $gravatar_URL . "'; this.parentNode.title = '" . $gravatar_TITLE . "'; this.src='" . $gravatar_IMG . "';" : "this.parentNode.title = '" . $mybloglog_NO_TITLE . "';"; if(!$gravatar && $default_IMG != "")echo " this.src='". $default_IMG ."';";?> this.onload=void(null); }" alt="MyAvatars 0.2"/>
	</a>
<?php } ?>
