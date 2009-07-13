<?php
/*
Plugin Name: Show Top Posters
Plugin URI: http://www.gospelrhys.co.uk/plugins/bbpress-plugins/show-top-posters-plugin
Description:  Readers with the most postes are displayed on your bbpress forum, with their names (linked to their website if there is one). Based on the <a href="http://www.pfadvice.com/wordpress-plugins/show-top-commentators/" target="_blank">Show Top Commenters</a> plugin for Wordpress by <a href="http://www.savingadvice.com" target="_blank">Nate Sanden</a>
Version: 1.3
Author: Rhys Wynne
Author URI: http://www.gospelrhys.co.uk
*/

add_action('bb_admin_menu_generator', 'show_top_posters_add_admin_page');
add_action('bb_admin-header.php', 'show_top_poster_admin_page_process');

function show_top_posters_add_admin_page() {
	bb_admin_add_submenu(__('Show Top Posters'), 'use_keys', 'show_top_posters_admin_page');

}

function show_top_posters_admin_page_defaults() {
	if (!bb_get_option('show_top_posters_reset')){
		bb_update_option('show_top_posters_reset', 'monthly');
	}
	if (!bb_get_option('show_top_posters_limit')){
		bb_update_option('show_top_posters_limit', '10');
	}
	if (!bb_get_option('show_top_posters_none_text')){
		bb_update_option('show_top_posters_none_text', 'None Yet!');
	}
	if (!bb_get_option('show_top_posters_make_links')){
		bb_update_option('show_top_posters_make_links', 1);
	}
	if (!bb_get_option('show_top_posters_pretty_permalinks')){
		bb_update_option('show_top_posters_pretty_permalinks', 0);
	}
	if (!bb_get_option('show_top_posters_name_limit')){
		bb_update_option('show_top_posters_name_limit', 28);
	}
	if (!bb_get_option('show_top_posters_start_html')){
		bb_update_option('show_top_posters_start_html', '<li>');
	}
	if (!bb_get_option('show_top_posters_end_html')){
		bb_update_option('show_top_posters_end_html', '</li>');
	}
	if (!bb_get_option('show_top_posters_show_posts')){
		bb_update_option('show_top_posters_show_posts', 1); 
	}	
}


function show_top_posters_admin_page() {

	show_top_posters_admin_page_defaults();

	

?>
	<h2>Show Top Posters </h2>

<?php if (isset ($_POST['submit'])) {
?>
		<div style="background-color:#EDF2EC;border: 1px solid #BAC0C8;padding: 10px;font-weight: bold;">Options Saved</div>

<?php	
	}
?>
	<form method="post">
	<table width="50%"  border="0">
      <tr>
        <td>Reset Counter?</td>
        <td><select name="stp_reset">
		<?php if (bb_get_option('show_top_posters_reset') == 'monthly') {
		 echo '<option value="monthly" selected>Monthly</option><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="all">Never</option><option value="yearly">Yearly</option>';
		}
        if (bb_get_option('show_top_posters_reset') == 'daily') {
		 echo '<option value="monthly">Monthly</option><option value="daily"  selected>Daily</option><option value="weekly">Weekly</option><option value="all">Never</option><option value="yearly">Yearly</option>';
		} 
		        if (bb_get_option('show_top_posters_reset') == 'weekly') {
		 echo '<option value="monthly">Monthly</option><option value="daily">Daily</option><option value="weekly" selected>Weekly</option><option value="all">Never</option><option value="yearly">Yearly</option>';
		} 
	        if (bb_get_option('show_top_posters_reset') == 'all') {
		 echo '<option value="monthly">Monthly</option><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="all"  selected>Never</option><option value="yearly">Yearly</option>';
		} 
	   if (bb_get_option('show_top_posters_reset') == 'yearly') {
		 echo '<option value="monthly">Monthly</option><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="all">Never</option><option value="yearly"  selected>Yearly</option>';
		} 
		 ?>
        </select></td>
      </tr>
      <tr>
        <td>Poster Limit:</td>
        <td><input name="stp_limit" type="text" id="stp_limit" value="<?php echo bb_get_option('show_top_posters_limit'); ?>"></td>
      </tr>
      <tr>
        <td>URL's to Filter:</td>
        <td><textarea name="stp_filter"><?php echo bb_get_option('show_top_posters_filter_urls'); ?></textarea></td>
      </tr>
      <tr>
        <td>Text To Show If There are No Posters: </td>
        <td><input name="stp_noposters" type="text" id="stp_noposters" value="<?php echo bb_get_option('show_top_posters_none_text'); ?>"></td>
      </tr>
      <tr>
        <td>Make Links?</td>
        <td><p><?php 
		
		if (bb_get_option('show_top_posters_make_links') == '1')
        { 
		  echo '<label>
          <input name="stp_make_links" type="radio" value="1" checked>
  To Website</label>
          <br>
		  <label>
          <input name="stp_make_links" type="radio" value="2">
  To Profile</label>
          <br>
          <label>
          <input type="radio" name="stp_make_links" value="0">
  No Links</label>';
  }
  if (bb_get_option('show_top_posters_make_links') == '0')
        { 
		  echo '<label>
          <input name="stp_make_links" type="radio" value="1">
  To Website</label>
          <br>
		  <label>
          <input name="stp_make_links" type="radio" value="2">
  To Profile</label>
          <br>
          <label>
          <input type="radio" name="stp_make_links" value="0" checked>
  No Links</label>';
  }
  if (bb_get_option('show_top_posters_make_links') == '2')
        { 
		  echo '<label>
          <input name="stp_make_links" type="radio" value="1">
  To Website</label>
          <br>
		  <label>
          <input name="stp_make_links" type="radio" value="2" checked>
  To Profile</label>
          <br>
          <label>
          <input type="radio" name="stp_make_links" value="0">
  No Links</label>';
  }
  ?>
          <br>
        </p>          </td>
      </tr>
      <tr>
        <td>Permalink Structure of Your Site</td>
        <td><select name="stp_permalink_structure">
		<?php if (bb_get_option('show_top_posters_pretty_permalinks') == 0) {
		 echo '<option value="0" selected>None .../forums.php?id=1</option><option value="1">Name Based .../forums/first-forum</option>';
		}
        if (bb_get_option('show_top_posters_pretty_permalinks') == 1) {
		 echo '<option value="0">None .../forums.php?id=1</option><option value="1" selected>Name Based .../forums/first-forum</option>';
		} 
	 ?>
        </select></td>
      </tr>
      <tr>
        <td>Name Character Limit</td>
        <td><input name="stp_charlimit" type="text" id="stp_charlimit" value="<?php echo bb_get_option('show_top_posters_name_limit'); ?>"></td>
      </tr>
      <tr>
        <td>Start HTML</td>
        <td><input name="stp_start_html" type="text" id="stp_start_html" value="<?php echo bb_get_option('show_top_posters_start_html'); ?>"></td>
      </tr>
  		<tr>
        <td>Start HTML</td>
        <td><input name="stp_end_html" type="text" id="stp_end_html" value="<?php echo bb_get_option('show_top_posters_end_html'); ?>"></td>
      </tr>
  <tr>
        <td>Show Post Count</td>
        <td><input name="stp_show_posts" type="text" id="stp_show_posts" value="<?php echo bb_get_option('show_top_posters_show_posts'); ?>"></td>
      </tr>
<tr><td colspan="2">
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="90" />
		<input type="hidden" name="action" value="show_top_poster_update" />
	</p></td>
	</tr>
	</table>
	</form>
	
	<div style="clear:both;">&nbsp;</div>
	<h4>Like this? Please Donate!</h4>
	<p>Donations help keep me chugging away at plugins. Donations as much and as little as you want.</p>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="6462319">
<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>

<?php


} 

function show_top_poster_admin_page_process() {
	if (isset ($_POST['submit'])) {
		if ('show_top_poster_update' == $_POST['action']) {
				bb_update_option('show_top_posters_reset', $_POST['stp_reset']);
				bb_update_option('show_top_posters_limit', $_POST['stp_limit']);
				bb_update_option('show_top_posters_filter_urls', $_POST['stp_filter']);
				bb_update_option('show_top_posters_none_text', $_POST['stp_noposters']);
				bb_update_option('show_top_posters_make_links', $_POST['stp_make_links']);
				bb_update_option('show_top_posters_pretty_permalinks', $_POST['stp_permalink_structure']);
				bb_update_option('show_top_posters_name_limit', $_POST['stp_charlimit']);
				bb_update_option('show_top_posters_start_html', $_POST['stp_start_html']);
				bb_update_option('show_top_posters_end_html', $_POST['stp_end_html']);
				bb_update_option('show_top_posters_show_posts', $_POST['stp_show_posts']);
		}
	}
}

//temporary until i can condense this into one query in $commenters
function ns_get_user_url($user) {
   global $bbdb, $ns_options;
	$url = $bbdb->get_var("
	   SELECT user_url, COUNT(user_url) AS user_url_count
	   FROM $bbdb->users
	   WHERE ID = $user
	   $options[filter_urls]
	   GROUP BY user_url
	   ORDER BY user_url_count DESC LIMIT 1
   ");
   return $url;
}

function ns_get_user_profile($user) {
   global $bbdb, $ns_options;
   if($ns_options["pretty_permalinks"] == 1) {
   $url = $bbdb->get_var("
	   SELECT user_login, COUNT(user_login) AS user_id_count
	   FROM $bbdb->users
	   WHERE ID = $user
	   GROUP BY user_login
	   ORDER BY user_id_count DESC LIMIT 1
   ");
   $url = "profile/" . $url; 
   }
   if($ns_options["pretty_permalinks"] == 0) {
   $url = "profile.php?id=" . $user; 
   }
   return $url;
}


function show_top_posters() {

   	global $bbdb; 
  	$ns_options = ns_get_variables();
   $ns_options = ns_format_options($ns_options);


   if($ns_options["reset"] != '') {
      $reset_sql = "DATE_FORMAT(post_time, '$ns_options[reset]') = DATE_FORMAT(CURDATE(), '$ns_options[reset]')";
   } else {
      $reset_sql = "1=1";
   }

	$commenters = $bbdb->get_results("
	   SELECT COUNT(poster_id) AS num_posts, poster_id
	   FROM $bbdb->posts
	   WHERE $reset_sql
	   AND post_status != 1
	   GROUP BY poster_id
	   ORDER BY num_posts DESC LIMIT 10
   ");

   if(is_array($commenters)) {
	   foreach ($commenters as $k) {
	   	 echo $ns_options["start_html"];
	      if($ns_options["make_links"] == 1) {
            $url = ns_get_user_url($k->poster_id);
			if(trim($url) != '')
			{
				echo "<a href='" . $url . "'>";
			}
	      }
		  if($ns_options["make_links"] == 2) {
			$url = ns_get_user_profile($k->poster_id);
			echo "<a href='" . $url . "'>";
		  }
	     
	      
		  $name = $bbdb->get_var("
	   SELECT user_login 
	   FROM $bbdb->users
	   WHERE ID = $k->poster_id");
	   	 echo ns_substr_ellipse($name, $ns_options["name_limit"]);
	      	
		  if(trim($url) != '' && $ns_options["make_links"] == 1) {
	         echo "</a>";
	      }
		  if($ns_options["make_links"] == 2) {
		  	echo "</a>";
		  }
	      if ($ns_options["show_posts"] == 1)
		  {
		  echo " (" . $k->num_posts . ")\n";
	      }
		  echo $ns_options["end_html"] . "\n";
	      unset($url);
	   }
	} else {
      echo $ns_options["start_html"] . $ns_options["none_text"] . $ns_options["end_html"];;
	}

}

function ns_get_variables()
{

$reset = bb_get_option('show_top_posters_reset');
$limit = bb_get_option('show_top_posters_limit');
$filter = bb_get_option('show_top_posters_filter_urls');
$none_text = bb_get_option('show_top_posters_none_text');
$make_links = bb_get_option('show_top_posters_make_links');
$pretty_permalinks = bb_get_option('show_top_posters_pretty_permalinks');
$name_limit = bb_get_option('show_top_posters_name_limit');
$start_html = bb_get_option('show_top_posters_start_html');
$end_html = bb_get_option('show_top_posters_end_html');
$show_posts = bb_get_option('show_top_posters_show_posts'); 

	$variables = array(
                    "reset" => $reset, //reset hourly, daily, weekly, monthly, yearly, all
                    "limit"  => $limit, //maximum number of commentator's to show
                    "filter_urls" => $filter, //commma seperated list of full or partial URL's (www.badsite.com,etc)
                    "none_text" => $none_text, //if there are no commentators, what should we display?
                    "make_links" => $make_links, //link the commentator's name to thier website? (1) or Profile? (2)
					"pretty_permalinks" => $pretty_permalinks, //do you have "pretty permalinks" for your profile (1)? (http://www.example.com/profile/username) or not (2)? (http://www.example.com/profile.php?id=1) 
                    "name_limit" => $name_limit, //maximum number of characters a commentator's name can be, before we cut it off with an ellipse
                    "start_html" => $start_html,
                    "end_html"   => $end_html,
					"show_posts" => $show_posts, //Shows the amount of posts people have made or just list their name.
                   );
	return $variables;
}

function ns_substr_ellipse($str, $len) {
   if(strlen($str) > $len) {
      $str = substr($str, 0, $len-3) . "...";
   }
   return $str;
}

function ns_format_options($options) {
   //$reset needs to turn into %sql format
	if($options["reset"] == "hourly") {
      $options["reset"] = "%Y-%m-%d %H";
   } elseif($options["reset"] == "daily") {
      $options["reset"] = "%Y-%m-%d";
   } elseif($options["reset"] == "weekly") {
      $options["reset"] = "%Y-%w";
   } elseif($options["reset"] == "monthly") {
      $options["reset"] = "%Y-%m";
   } elseif($options["reset"] == "yearly") {
      $options["reset"] = "%Y";
   } elseif($options["reset"] == "all") {
      $options["reset"] = ""; //just use monthly
   } else {
      $options["reset"] = "%Y-%m"; //just use monthly
   }
   //$filter urls needs to be comma seperated with single quotes
   $filter_urls = trim($options["filter_urls"]);
   $filter_urls = explode(",", $filter_urls);
   for($i=0; $i<count($filter_urls); $i++) {
      $new_urls .= " AND user_url NOT LIKE '%" . trim($filter_urls[$i]) . "%'";
   }
   //echo $new_urls;
   $options["filter_urls"] = $new_urls;
   //lets trim $limit just for the hell of it. (you never know)
   $options["limit"] = trim($options["limit"]);
   $options["name_limit"] = trim($options["name_limit"]);
   //$filter_users needs to be comma seperated with single quotes
   $filter_users = trim($options["filter_users"]);
   $filter_users = explode(",", $filter_users);
   for($i=0; $i<count($filter_users); $i++) {
      $new_users[] = "'" . trim($filter_users[$i]) . "'";
   }
   $options["filter_users"] = implode(",", $new_users);
   return $options;
}


?>