<?php
/*
Plugin Name: Show Top Posters
Plugin URI: http://www.gospelrhys.co.uk/bbpress-plugin-show-top-posters
Description:  Readers with the most postes are displayed on your bbpress forum, with their names (linked to their website if they provided one). Based on the <a href="http://www.pfadvice.com/wordpress-plugins/show-top-commentators/" target="_blank">Show Top Commenters</a> plugin for Wordpress by <a href="http://www.savingadvice.com" target="_blank">Nate Sanden</a>
Version: 1.2
Author: Rhys Wynne
Author URI: http://www.gospelrhys.co.uk

Installation Instructions:
http://www.pfadvice.com/wordpress-plugins/show-top-commentators/#install

Help Forum: http://www.gospelrhys.co.uk/forum 

Shameless Begging: If you like this plugin, I would appreciate a linkback to http://www.gospelrhys.co.uk, and I'm sure Nate would like a link to either http://www.savingadvice.com or http://www.pfadvice.com on your blog.


*/

$ns_options = array(
                    "reset" => "monthly", //reset hourly, daily, weekly, monthly, yearly, all
                    "limit"  => 10, //maximum number of commentator's to show
                    "filter_urls" => "www.pornforall.com", //commma seperated list of full or partial URL's (www.badsite.com,etc)
                    "none_text" => "None yet!", //if there are no commentators, what should we display?
                    "make_links" => 1, //link the commentator's name to thier website? (1) or Profile? (2)
					"pretty_permalinks" => 1, //do you have "pretty permalinks" for your profile (1)? (http://www.example.com/profile/username) or not (2)? (http://www.example.com/profile.php?id=1) 
                    "name_limit" => 28, //maximum number of characters a commentator's name can be, before we cut it off with an ellipse
                    "start_html" => "<li>",
                    "end_html"   => "</li>",
					"show_posts" => 1, //Shows the amount of posts people have made or just list their name.
                   );

//first we need to format options so they are useable
$ns_options = ns_format_options($ns_options);

function ns_substr_ellipse($str, $len) {
   if(strlen($str) > $len) {
      $str = substr($str, 0, $len-3) . "...";
   }
   return $str;
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
   if($ns_options["pretty_permalinks"] == 2) {
   $url = "profile.php?id=" . $user; 
   }
   return $url;
}

function show_top_posters() {

   global $bbdb, $ns_options;

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
	   ORDER BY num_posts DESC LIMIT $ns_options[limit]
   ");

   if(is_array($commenters)) {
	   foreach ($commenters as $k) {
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
	      echo $ns_options["start_html"];
	      
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
