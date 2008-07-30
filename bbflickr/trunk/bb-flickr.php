<?php
/*
Plugin Name: bbFlickr
Plugin URI: http://astateofmind.eu/freebies/bbflickr/
Description: Allows you to integrate the photos from a flickr rss feed into your bbPress forum. Based on flickrRSS, original script for Wordpress by Dave Kellam (http://eightface.com/wordpress/flickrrss/)
Version: 0.1
Author: F.Thion
Author URI: http://astateofmind.eu
*/

require_once('magpierss/rss_fetch.inc');

function get_flickrRSS() {

	// the function can accept up to seven parameters, otherwise it uses option panel defaults 	
  	for($i = 0 ; $i < func_num_args(); $i++) {
    	$args[] = func_get_arg($i);
    	}
  	if (!isset($args[0])) $num_items = bb_get_option('flickrRSS_display_numitems'); else $num_items = $args[0];
  	if (!isset($args[1])) $type = bb_get_option('flickrRSS_display_type'); else $type = $args[1];
  	if (!isset($args[2])) $tags = trim(bb_get_option('flickrRSS_tags')); else $tags = trim($args[2]);
  	if (!isset($args[3])) $imagesize = bb_get_option('flickrRSS_display_imagesize'); else $imagesize = $args[3];
  	if (!isset($args[4])) $before_image = stripslashes(bb_get_option('flickrRSS_before')); else $before_image = $args[4];
  	if (!isset($args[5])) $after_image = stripslashes(bb_get_option('flickrRSS_after')); else $after_image = $args[5];
  	if (!isset($args[6])) $id_number = stripslashes(bb_get_option('flickrRSS_flickrid')); else $id_number = $args[6];
  	if (!isset($args[7])) $set_id = stripslashes(bb_get_option('flickrRSS_set')); else $set_id = $args[7];

	// get the feeds
	if ($type == "user") { $rss_url = 'http://api.flickr.com/services/feeds/photos_public.gne?id=' . $id_number . '&tags=' . $tags . '&format=rss_200'; }
	elseif ($type == "favorite") { $rss_url = 'http://api.flickr.com/services/feeds/photos_faves.gne?id=' . $id_number . '&format=rss_200'; }
	elseif ($type == "set") { $rss_url = 'http://api.flickr.com/services/feeds/photoset.gne?set=' . $set_id . '&nsid=' . $id_number . '&format=rss_200'; }
	elseif ($type == "group") { $rss_url = 'http://api.flickr.com/services/feeds/groups_pool.gne?id=' . $id_number . '&format=rss_200'; }
	elseif ($type == "community" || $type == "public") { $rss_url = 'http://api.flickr.com/services/feeds/photos_public.gne?tags=' . $tags . '&format=rss_200'; }
	else { echo "flickrRSS probably needs to be setup"; }

	# get rss file
	$rss = @ fetch_rss($rss_url);

	if ($rss) {
    	$imgurl = "";
    	# specifies number of pictures
		$items = array_slice($rss->items, 0, $num_items);

	    # builds html from array
    	foreach ( $items as $item ) {
       	 if(preg_match('<img src="([^"]*)" [^/]*/>', $item['description'],$imgUrlMatches)) {
            	$imgurl = $imgUrlMatches[1];
 
         	## change image size         
           	if ($imagesize == "square") {
             	$imgurl = str_replace("m.jpg", "s.jpg", $imgurl);
           	} elseif ($imagesize == "thumbnail") {
             $imgurl = str_replace("m.jpg", "t.jpg", $imgurl);
           	} elseif ($imagesize == "medium") {
             $imgurl = str_replace("_m.jpg", ".jpg", $imgurl);
           	}
           
           #check if there is an image title (for html validation purposes)
           if($item['title'] !== "") $title = htmlspecialchars(stripslashes($item['title']));
           else $title = "Flickr photo";          
           
           $url = $item['link'];
	
	       preg_match('<http://farm[0-9]{0,3}\.static.flickr\.com/\d+?\/([^.]*)\.jpg>', $imgurl, $flickrSlugMatches);
	       $flickrSlug = $flickrSlugMatches[1];
	       
	       # cache images 
	       if ($useImageCache) {
                      
               # check if file already exists in cache
               # if not, grab a copy of it
               if (!file_exists("$fullPath$flickrSlug.jpg")) {   
                 if ( function_exists('curl_init') ) { // check for CURL, if not use fopen
                    $curl = curl_init();
                    $localimage = fopen("$fullPath$flickrSlug.jpg", "wb");
                    curl_setopt($curl, CURLOPT_URL, $imgurl);
                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
                    curl_setopt($curl, CURLOPT_FILE, $localimage);
                    curl_exec($curl);
                    curl_close($curl);
                   } else {
                 	$filedata = "";
                    $remoteimage = fopen($imgurl, 'rb');
                  	if ($remoteimage) {
                    	 while(!feof($remoteimage)) {
                         	$filedata.= fread($remoteimage,1024*8);
                       	 }
                  	}
                	fclose($remoteimage);
                	$localimage = fopen("$fullPath$flickrSlug.jpg", 'wb');
                	fwrite($localimage,$filedata);
                	fclose($localimage);
                 } // end CURL check
                } // end file check
                # use cached image
                print $before_image . "<a href=\"$url\" title=\"$title\"><img src=\"$cachePath$flickrSlug.jpg\" alt=\"$title\" /></a>" . $after_image;
            } else {
                # grab image direct from flickr
                print $before_image . "<a href=\"$url\" title=\"$title\"><img src=\"$imgurl\" alt=\"$title\" /></a>" . $after_image;      
            } // end use imageCache
       } // end pregmatch
     } // end foreach
     
     echo '<h4 class="powered_by_bbflickr"><a href="http://astateofmind.eu/freebies/bbflickr/" title="bbFlickr - Flickr plugin for bbPress">powered by bbFlickr</a></h4>';
  } // end if($rss)
} # end get_flickrRSS() function

function flickrRSS_subpanel() {
     if (isset($_POST['save_flickrRSS_settings'])) {
       $option_flickrid = $_POST['flickr_id'];
       $option_tags = $_POST['tags'];
       $option_set = $_POST['set'];
       $option_display_type = $_POST['display_type'];
       $option_display_numitems = $_POST['display_numitems'];
       $option_display_imagesize = $_POST['display_imagesize'];
       $option_before = $_POST['before_image'];
       $option_after = $_POST['after_image'];
       $option_useimagecache = $_POST['use_image_cache'];
       $option_imagecacheuri = $_POST['image_cache_uri'];
       $option_imagecachedest = $_POST['image_cache_dest'];
       bb_update_option('flickrRSS_flickrid', $option_flickrid);
       bb_update_option('flickrRSS_tags', $option_tags);
       bb_update_option('flickrRSS_set', $option_set);
       bb_update_option('flickrRSS_display_type', $option_display_type);
       bb_update_option('flickrRSS_display_numitems', $option_display_numitems);
       bb_update_option('flickrRSS_display_imagesize', $option_display_imagesize);
       bb_update_option('flickrRSS_before', $option_before);
       bb_update_option('flickrRSS_after', $option_after);
       bb_update_option('flickrRSS_use_image_cache', $option_useimagecache);
       bb_update_option('flickrRSS_image_cache_uri', $option_imagecacheuri);
       bb_update_option('flickrRSS_image_cache_dest', $option_imagecachedest);
       ?> <div class="updated"><p>flickrRSS settings saved</p></div> <?php
     }

	?>

		<h2>bbFlickr Settings</h2>
		
		<form method="post">
		<table class="widefat">
		
		<thead>
			<tr><th width="170">Option</th>	<th>Setting</th> </tr>
		</thead>
		
		<tbody>
		 <tr valign="top">
		  <th scope="row">ID Number</th>
	      <td><input name="flickr_id" type="text" id="flickr_id" value="<?php echo bb_get_option('flickrRSS_flickrid'); ?>" size="20" />
        		Use the <a href="http://idgettr.com">idGettr</a> to find your user or group id.</p></td>
         </tr>
         <tr valign="top">
          <th scope="row">Display</th>
          <td>
        	<select name="display_type" id="display_type">
        	  <option <?php if(bb_get_option('flickrRSS_display_type') == 'user') { echo 'selected'; } ?> value="user">user</option>
        	  <option <?php if(bb_get_option('flickrRSS_display_type') == 'set') { echo 'selected'; } ?> value="set">set</option>
        	  <option <?php if(bb_get_option('flickrRSS_display_type') == 'favorite') { echo 'selected'; } ?> value="favorite">favorite</option>
		      <option <?php if(bb_get_option('flickrRSS_display_type') == 'group') { echo 'selected'; } ?> value="group">group</option>
		      <option <?php if(bb_get_option('flickrRSS_display_type') == 'community') { echo 'selected'; } ?> value="community">community</option>
		    </select>
		 	items using 
        	<select name="display_numitems" id="display_numitems">
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '1') { echo 'selected'; } ?> value="1">1</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '2') { echo 'selected'; } ?> value="2">2</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '3') { echo 'selected'; } ?> value="3">3</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '4') { echo 'selected'; } ?> value="4">4</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '5') { echo 'selected'; } ?> value="5">5</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '6') { echo 'selected'; } ?> value="6">6</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '7') { echo 'selected'; } ?> value="7">7</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '8') { echo 'selected'; } ?> value="8">8</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '9') { echo 'selected'; } ?> value="9">9</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '10') { echo 'selected'; } ?> value="10">10</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '11') { echo 'selected'; } ?> value="11">11</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '12') { echo 'selected'; } ?> value="12">12</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '13') { echo 'selected'; } ?> value="13">13</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '14') { echo 'selected'; } ?> value="14">14</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '15') { echo 'selected'; } ?> value="15">15</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '16') { echo 'selected'; } ?> value="16">16</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '17') { echo 'selected'; } ?> value="17">17</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '18') { echo 'selected'; } ?> value="18">18</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '19') { echo 'selected'; } ?> value="19">19</option>
		      <option <?php if(bb_get_option('flickrRSS_display_numitems') == '20') { echo 'selected'; } ?> value="20">20</option>
		      </select>
            <select name="display_imagesize" id="display_imagesize">
		      <option <?php if(bb_get_option('flickrRSS_display_imagesize') == 'square') { echo 'selected'; } ?> value="square">square</option>
		      <option <?php if(bb_get_option('flickrRSS_display_imagesize') == 'thumbnail') { echo 'selected'; } ?> value="thumbnail">thumbnail</option>
		      <option <?php if(bb_get_option('flickrRSS_display_imagesize') == 'small') { echo 'selected'; } ?> value="small">small</option>
		      <option <?php if(bb_get_option('flickrRSS_display_imagesize') == 'medium') { echo 'selected'; } ?> value="medium">medium</option>
		    </select>
            images</p>
           </td> 
         </tr>
         <tr valign="top">
		  <th scope="row">Set</th>
          <td><input name="set" type="text" id="set" value="<?php echo bb_get_option('flickrRSS_set'); ?>" size="40" /> Use number from the set url</p>
         </tr>
         <tr valign="top">
		  <th scope="row">Tags</th>
          <td><input name="tags" type="text" id="tags" value="<?php echo bb_get_option('flickrRSS_tags'); ?>" size="40" /> Comma separated, no spaces</p>
         </tr>
         <tr valign="top">
          <th scope="row">HTML Wrapper</th>
          <td><label for="before_image">Before Image:</label> <input name="before_image" type="text" id="before_image" value="<?php echo htmlspecialchars(stripslashes(bb_get_option('flickrRSS_before'))); ?>" size="10" />
        	  <label for="after_image">After Image:</label> <input name="after_image" type="text" id="after_image" value="<?php echo htmlspecialchars(stripslashes(bb_get_option('flickrRSS_after'))); ?>" size="10" />
          </td>
         </tr>
        </tbody>
         </table>

			<h3>Cache Settings</h3>
		<p>This allows you to store the images on your server and reduce the load on Flickr. Make sure the plugin works without the cache enabled first.</p>
		<table class="form-table">
         <tr valign="top">
          <th scope="row">URL</th>
          <td><input name="image_cache_uri" type="text" id="image_cache_uri" value="<?php echo bb_get_option('flickrRSS_image_cache_uri'); ?>" size="50" />
          <em>http://yoursite.com/cache/</em></td>
         </tr>
         <tr valign="top">
          <th scope="row">Full Path</th>
          <td><input name="image_cache_dest" type="text" id="image_cache_dest" value="<?php echo bb_get_option('flickrRSS_image_cache_dest'); ?>" size="50" /> 
          <em>/home/path/to/wp-content/flickrrss/cache/</em></td>
         </tr>
		 <tr valign="top">
		  <th scope="row" colspan="2" class="th-full">
		  <input name="use_image_cache" type="checkbox" id="use_image_cache" value="true" <?php if(bb_get_option('flickrRSS_use_image_cache') == 'true') { echo 'checked="checked"'; } ?> />  
		  <label for="use_image_cache">Enable the image cache</label></th>
		 </tr>
        </table>

        <div class="submit">
           <input type="submit" name="save_flickrRSS_settings" value="<?php _e('Save Settings', 'save_flickrRSS_settings') ?>" />
        </div>
        </form>
        
	<h3>About</h3>
	<p>bbFlickr plugin is based on <a href="http://eightface.com/wordpress/flickrrss/">flickrRSS</a> plugin for Wordpress, originaly created by Dave Kellam. Even so, if you like it and you understand it's not so easy even to modify someones code, you can donate some bucks :). </a></p>
	
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="wojciech.usarzewicz@gmail.com">
	<input type="hidden" name="item_name" value="bbFlickr Donation">
	<input type="hidden" name="item_number" value="bbFlickr Donation">
	<input type="hidden" name="no_shipping" value="0">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD" />
	
	Type donation amount: $ <input type="text" name="amount" value="1" />
	
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	
	<input type="submit" name="submit" value="Donate with PayPal!" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
	</form>
	
<?php } // end flickrRSS_subpanel()

function flickrRSS_admin_menu() {
	bb_admin_add_submenu(__('bbFlickr Settings'), 'administrate', 'flickrRSS_subpanel');
}

add_action( 'bb_admin_menu_generator', 'flickrRSS_admin_menu' );
add_action('plugins_loaded', 'get_flickrRSS');
?>