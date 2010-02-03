<?
/*
Plugin Name: Thanks
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/thanks
Description: Empowers users to leave a vote of thanks for posts
Author: Paul Hawke
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.6
*/

$DEFAULTS = array(
	"thanks_output_none" => "", 
	"thanks_output_one" => "# vote of thanks", 
	"thanks_output_many" => "# votes of thanks",
	"thanks_voting" => "Add your vote of thanks",
	"thanks_success" => "Vote received, thanks.",
	"thanks_position" => "after",
	"thanks_voters" => "no",
	"thanks_voters_prefix" => "(",
	"thanks_voters_suffix" => ")",
);

require_once( "thanks-admin.php" );

function thanks_js() {
	$src = BB_PLUGIN_URL.'thanks/thanks-plugin.js';
	wp_register_script('thanks-plugin-js', $src, array('jquery'));
	
	wp_enqueue_script('thanks-plugin-js');
}

function thanks_head() { ?>
<script type="text/javascript"><!--
   	var ajaxThanksUrl = "<?php echo BB_PLUGIN_URL; ?>thanks/ajax-thanks.php";
		var ajaxSuccess = "<?php echo thanks_get_voting_phrase('thanks_success'); ?>";
// -->
</script>
<?php }

function thanks_output_before() {
		$msg = thanks_get_voting_phrase("thanks_position");
		if ($msg == "before") {
			 thanks_output();
		}
}

function thanks_output_after() {
		$msg = thanks_get_voting_phrase("thanks_position");
		if ($msg == "after") {
			 thanks_output();
		}
}

function thanks_output() {
	global $bb_post, $DEFAULTS;

	$logged_in = bb_is_user_logged_in();
	$meta = bb_get_post_meta("thanks", $bb_post->post_id);
	$report_length = 0;
	echo "<div class=\"thanks-output\" id=\"thanks-".$bb_post->post_id."\">";
	if (isset($meta)) {
		$vote_count = count($meta);
		$msg_type = ($vote_count == 0) ? "none" : (($vote_count == 1) ? "one" : "many");
		$msg = thanks_get_voting_phrase("thanks_output_".$msg_type);
		$report_length = strlen($msg);
	  echo str_replace("#", "".$vote_count, $msg);
	  
	  $should_show_voters = thanks_get_voting_phrase("thanks_voters");
	  if ($should_show_voters == "yes") {
		  echo ' '.thanks_get_voting_phrase("thanks_voters_prefix");
		  for ($i=0; $i < count($meta); $i++) {
				$link = get_user_profile_link($meta[$i]);
				$voter = bb_get_user($meta[$i]);
				if ($i > 0) {
					echo ", ";
				}
				echo '<a href="'.$link.'">'.$voter->display_name.'</a>';
		  }
		  echo thanks_get_voting_phrase("thanks_voters_suffix");
		}
	}
	
	if ($logged_in) {
		$user = bb_get_current_user();
		$uid = (int) $user->ID;

		if (!in_array($uid, $meta)) {
			if (isset($meta) && $report_length > 0) {
				echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
			}
			$msg = thanks_get_voting_phrase("thanks_voting");		
			echo "<a class=\"thanks-vote\" user=\"".$uid."\" id=\"".$bb_post->post_id."\">".$msg."</a>";
		}
	}
	echo "</div>";
}

function thanks_get_voting_phrase($phrase) {
	global $DEFAULTS;
	$msg = bb_get_option($phrase);
	if (!isset($msg)) {
		$msg = $DEFAULTS[$phrase];
	}
	return $msg;
}

function thanks_bootstrap( ) {
	add_action('bb_init', 'thanks_js');

	add_action('admin_init', 'thanks_js');
	
	add_action('bb_head', 'thanks_head');

	add_action('bb_admin_head', 'thanks_admin_head');
	
	add_action('bb_admin-header.php', 'thanks_admin_page_process');

	add_action('bb_post.php', 'thanks_output_before');

	add_action('bb_after_post.php', 'thanks_output_after');
	
	add_action('bb_admin_menu_generator', 'thanks_admin_page_add');
}

thanks_bootstrap();

?>
