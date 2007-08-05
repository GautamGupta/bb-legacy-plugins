<?php
/*
Plugin Name: report post
Description:  allows members to report a post to admin/moderators 
Plugin URI:  http://CKon.wordpress.com
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.11
*/ 

/* 
instructions:  install, activate and put  <? report_post_link(); ?> in your post.php template where you want the link to be seen
optional in stylesheet:  a.report_post {color:red;}  

todo: 
1. don't let them report more than once on a post - or more than too many times per minute/hour
2. auto-delete post if more than x reports from different members
3. auto-post report into a specified moderator's forum #
4. maybe ajax xmlhttp call instead of real form post so there's no page movement
5. it's technically possible to alert a browing mod with a popup directing to the reported post, no email needed

don't allow reports on moderators
don't allow reports from members less than x days old

security: check if user is in the right topic for the post being reported

history:
0.10	: first public beta release
0.11	: translation hooks added
*/

function report_post_link($post_id=0) { 
if (bb_current_user_can('participate') ) :
	$post_id= get_post_id( $post_id ); 
	if (get_post_author_id($post_id) != bb_get_current_user_info( 'id' )) {
		echo '<a class=report_post title="report post to moderator" href="#post-'.$post_id.'" onClick="report_post('.$post_id.');return false;">'.__("Report").'</a>';
	}
endif;
}
function post_report_link($post_id=0) {report_post_link($post_id);}  // just an alias for typos

function report_post_form() {
if (bb_current_user_can('participate')) :
if (isset($_POST['report_post_id']) && isset($_POST['report_post_reason'])) {
	$message=__("Thank you for the report. A moderator has been notified.");
	echo '<scr'.'ipt type="text/javascript">alert("'.$message.'");</scr'.'ipt>';
	$post_id=intval($_POST['report_post_id']);
	// todo: custom response if invalid id, problem sending email - maybe flush output buffer so member gets alert faster
	$to = bb_get_option('admin_email');
	$from = $to;
	$subject = __("post reported by member for moderation");
	$headers ="From: ".$from;
	$message ="report by:  ".bb_get_current_user_info( 'name' )." (".bb_get_current_user_info( 'id' ).")  email: ".bb_get_current_user_info( 'email' )."\r\n\r\n";  
	$message.="report: ".wordwrap(strip_tags(substr($_POST['report_post_reason'],0,255)),70)."\r\n\r\n".get_post_link($post_id)."\r\n";
	$message.="post by: ". get_post_author($post_id)."\r\n";     // add "member since", total posts, blah blah		
	$message.="\r\n\r\nReport Trace:\r\n";
	$message.="IP:    ".$_SERVER['REMOTE_ADDR']."\r\n";
	$message.="Host:  ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."\r\n";   // useful but can add a few seconds
 	$message.="Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
 	$message.="Refer: ". $_REQUEST['refer']."\r\n";
 	$message.="URL:   http://".$_SERVER['HTTP_HOST'].$GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"]."\r\n";  			
	mail( $to, $subject, $message,$headers,"-odb");	  // odq = queue only
	// qmail_queue($to, $from, $subject, $message, "");
}
if (is_topic()) {
$instructions=__("Please enter a short but descriptive reason why a moderator needs to review this post:");
$canceled=__("report cancelled, incomplete description");
echo '<form method="POST" name="report_post_form" id="report_post_form" style="display:none;visibility:hidden"><input type=hidden name="report_post_id"><input type=hidden name="report_post_reason"></form>';
echo '<scr'.'ipt type="text/javascript">
function report_post(post_id) {
var report_post_reason = prompt("'.$instructions.'", "");
if (report_post_reason && report_post_reason.length>9) {
document.report_post_form.report_post_id.value=post_id;
document.report_post_form.action="#post-"+post_id;
document.report_post_form.report_post_reason.value=report_post_reason;
document.report_post_form.submit();
} else {alert("'.$canceled.'"); }
}
</scr'.'ipt>';
}
endif;
}
add_action('bb_foot', 'report_post_form');

?>