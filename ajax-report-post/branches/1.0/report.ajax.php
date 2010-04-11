<?php

if ( !isset( $_POST['postId'] ) )
	die('');

require('../../bb-load.php');

if ( bb_current_user_can('participate') ){
	$msg = $report_options['bbrp_success_msg'];
	if (isset($_POST['postId']) && isset($_POST['comment'])) {
		$post_id=intval($_POST['postId']);
		
		global $bbdb, $report_options;
		$data = array(
			'postId' => $post_id,
			'report_option' => bbrp_addBreakStringSlashes($_POST['option']),
			'reporter_comment' => bbrp_addBreakStringSlashes($_POST['comment']),
			'reporter_ip' => $_SERVER['REMOTE_ADDR'],
			'reported_date' => date('Y-m-d H:i:s'),
			'status' => 1
		);
		if (isset($_COOKIE['bbrp-report-post-'.$post_id]) && $_COOKIE['bbrp-report-post-'.$post_id] === 'true') {
			$msg = $report_options['bbrp_already_reported_msg'];
		} else {
			$cookieExpireTime = time () + 60 * 60 * 24;
			if ($bbdb->insert( $bbdb->prefix.'custom_reports', $data )) {
				setcookie('bbrp-report-post-'.$post_id, 'true', $cookieExpireTime, '/');
			} else {
				$msg = $report_options['bbrp_error_msg'];
			}
			
			$to=bb_get_option('from_email'); if (!$to) {$to=bb_get_option('admin_email');}
			$from =  $to;
			$subject = __("post reported by member for moderation");
			$headers ="From: ".$from;
			$message ="Reported by:  ".bb_get_current_user_info( 'name' )." (".bb_get_current_user_info( 'id' ).")  email: ".bb_get_current_user_info( 'email' )."\r\n\r\n";
			if (isset($_POST['option']) && !empty($_POST['option'])) {
				$message.="Report type: ".stripslashes($_POST['option'])."\r\n";
			}
			$message.="Report: ".wordwrap(strip_tags(substr(stripslashes($_POST['comment']),0,255)),70)."\r\n\r\n".get_post_link($post_id)."\r\n";			
			$message.="post by: ". get_post_author($post_id)."\r\n";
			$message.="\r\n\r\nReport Trace:\r\n";
			$message.="IP:    ".$_SERVER['REMOTE_ADDR']."\r\n";
			$message.="Host:  ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."\r\n";
		 	$message.="Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
		 	$message.="Refer: ". $_REQUEST['refer']."\r\n";
		 	$message.="URL:   http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\r\n";  			
			mail( $to, $subject, $message,$headers);
		}
		echo $msg;exit;
	}

} else 
	die('');