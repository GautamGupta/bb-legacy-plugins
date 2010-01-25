<?php
/*
 Functions PHP File for
 Social It Plugin
 (for bbPress) by www.gaut.am
*/

function socialit_change_plus_apos($content){
	$content = str_replace('+','%20',$content);
	$content = str_replace("&#8217;","'",$content);
	return $content;
}

//get current page rss link, code taken from functions.bb-template.php in bb-includes, posts' rss prefered instead of topics'
function socialit_get_current_rss_link(){
	switch (bb_get_location()) {
		case 'profile-page':
			if ( $tab = isset($_GET['tab']) ? $_GET['tab'] : bb_get_path(2) )
				if ($tab != 'favorites')
					break;
			
			$feed = get_favorites_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'topic-page':
			$feed = get_topic_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'tag-page':
			if (bb_is_tag()) {
				$feed = bb_get_tag_posts_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			}
			break;
		
		case 'forum-page':
			$feed = bb_get_forum_posts_rss_link(0, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'front-page':
			$feed = bb_get_posts_rss_link(BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
		
		case 'view-page':
			global $bb_views, $view;
			if ($bb_views[$view]['feed']) {
				$feed = bb_get_view_rss_link(null, BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			}
			break;
		default:
			$feed = bb_get_posts_rss_link(BB_URI_CONTEXT_LINK_ALTERNATE_HREF + BB_URI_CONTEXT_BB_FEED);
			break;
	}
	return $feed;
}

//gets current URL, returns string, taken from Support Forum Plugin
function socialit_get_current_url(){
	$schema = 'http://';
	if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		$schema = 'https://';
	}
	if ($querystring = $_SERVER['QUERYSTRING']) {
		$querystring = ltrim($querystring, '?&');
		$querystring = rtrim($querystring, '&');
		if ($querystring) {
			$querystring = '?' . $querystring;
		}
	}
	$uri = $schema . $_SERVER['HTTP_HOST'] . rtrim($_SERVER['REQUEST_URI'], '?&') . $querystring;
	return $uri;
}

function socialit_get_fetch_url() {
	global $socialit_plugopts;
	
	$perms = trim(socialit_get_current_url());
	
	//check if the link is already genereted or not, if yes, then return the link 
 	$fetch_url = trim($socialit_plugopts['shorturls'][md5($perms)]); 
 	if($fetch_url)
 	        return $fetch_url;

	$url_more = "";
	$method = 'GET';
	$POST_data = array();
	 
	// Which short url service should be used?
	switch ( $socialit_plugopts['shorty'] ) {
		case 'tiny':
			$first_url = "http://tinyurl.com/api-create.php?url=".$perms;
			break;
		case 'snip':
			$first_url = "http://snipr.com/site/getsnip";
			$method = 'POST';
			$POST_data = array( "snipformat" => "simple", "sniplink" => rawurlencode($perms), "snipuser" => $socialit_plugopts['shortyapi']['snip']['user'], "snipapi" => $socialit_plugopts['shortyapi']['snip']['key'] );
			break;
		case 'cligs':
			$first_url = "http://cli.gs/api/v1/cligs/create?url=".urlencode($perms)."&appid=socialit";
			if ($socialit_plugopts['shortyapi']['cligs']['chk'] == 1) //if user custom options are set
				$first_url .= "&key=".$socialit_plugopts['shortyapi']['cligs']['key'];
			break;
		case 'supr':
			$first_url = "http://su.pr/api/simpleshorten?url=".$perms;
			if($socialit_plugopts['shortyapi']['supr']['chk'] == 1) //if user custom options are set
				$first_url .= "&login=".$socialit_plugopts['shortyapi']['supr']['user']."&apiKey=".$socialit_plugopts['shortyapi']['supr']['key'];
			break;
		case 'bitly':
			$first_url = "http://api.bit.ly/shorten?version=2.0.1&longUrl=".$perms."&history=1&login=".$socialit_plugopts['shortyapi']['bitly']['user']."&apiKey=".$socialit_plugopts['shortyapi']['bitly']['key']."&format=json";
			break;
		case 'trim':
			if($socialit_plugopts['shortyapi']['trim']['chk'] == 1){ //if user custom options are set
				$first_url = "http://api.tr.im/api/trim_url.json?url=".$perms."&username=".$socialit_plugopts['shortyapi']['trim']['user']."&password=".$socialit_plugopts['shortyapi']['trim']['pass'];
			}else{
				$first_url = "http://api.tr.im/api/trim_simple?url=".$perms;
			}
			break;
		case 'tinyarrow':
			$first_url = "http://tinyarro.ws/api-create.php?";
			if($socialit_plugopts['shortyapi']['tinyarrow']['chk'] == 1) //if user custom options are set
				$first_url .= "&userid=".$socialit_plugopts['shortyapi']['tinyarrow']['user'];
			$first_url .= "&url=".$perms; //url has to be last param in tinyarrow
			break;
		case 'slly':
			$first_url = "http://sl.ly/?module=ShortURL&file=Add&mode=API&url=".$perms;
			break;
		case 'e7t': //e7t.us no longer exists, this only here for backwards compatibility
			$first_url = "http://b2l.me/api.php?alias=&url=".$perms;
			$socialit_plugopts['shorty'] = 'b2l';
			bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts);
			break;
		case 'b2l': //goto default
		default:
			$first_url = "http://b2l.me/api.php?alias=&url=".$perms;
			break;
	}
	
	// retrieve the shortened URL
	$fetch_url = socialit_nav_browse($first_url, $method, $POST_data);
	
	if ($fetch_url) { // remote call made and was successful
		if($socialit_plugopts['shorty'] == "trim" && $socialit_plugopts['shortyapi']['trim']['chk'] == 1){
			$fetch_array = json_decode($fetch_url, true);
			$fetch_url = $fetch_array['url'];
		}elseif($socialit_plugopts['shorty'] == "bitly"){
			$fetch_array = json_decode($fetch_url, true);
			$fetch_url = $fetch_array['results'][$perms]['shortUrl'];
		}
	
		$fetch_url = trim($fetch_url);
		$socialit_plugopts['shorturls'][md5($perms)] = $fetch_url;
		bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts); // update values for future use
	} else { //return the permalink, getting the short url was not successful
		$fetch_url = $perms;
	}
	
	return $fetch_url;
}

function bookmark_list_item($name, $opts = array()) {
	global $socialit_plugopts, $socialit_bookmarks_data;

	$url = $socialit_bookmarks_data[$name]['baseUrl'];
	foreach ($opts as $key=>$value) {
		$url=str_replace(strtoupper($key), $value, $url);
	}
	
	return sprintf(
		'<li class="%s"><a href="%s" rel="%s"%s title="%s">%s</a></li>',
		$name,
		$url,
		$socialit_plugopts['reloption'],
		$socialit_plugopts['targetopt']=="_blank"?' class="external"':'',
		$socialit_bookmarks_data[$name]['share'],
		$socialit_bookmarks_data[$name]['share']
	);
}

//shows an option to the topic creator/mod+ whether to hide the social bookmarking menu on the particular topic or not
function socialit_hide_show($parts){
	$topic = get_topic(get_topic_id());
	if($topic && bb_current_user_can('delete_topic', $topic->topic_id)){
		if(bb_get_topicmeta($topic->topic_id, 'hide_socialit') == 'true'){
			$display = esc_html( __('Show Social It Menu', 'socialit') );
			$uri = socialit_get_current_url()."?socialit_hide_show=1&shs_opt=1&tid=".$topic->topic_id;
		} else {
			$display = esc_html( __('Hide Social It Menu', 'socialit') );
			$uri = socialit_get_current_url()."?socialit_hide_show=1&shs_opt=2&tid=".$topic->topic_id;
		}
		$uri = esc_url(bb_nonce_url($uri, 'socialit_hide_show_'.$topic->topic_id));
		$parts[] = '[<a href="' . $uri . '">' . $display . '</a>]';
		return $parts;
	}
	return $parts;
}

//does appropiate action for the above function
function socialit_hide_show_do(){
	if(bb_is_topic() && $_GET['socialit_hide_show'] == "1" && isset($_GET['shs_opt']) && isset($_GET['tid']) && bb_current_user_can('moderate')){
		$topic = get_topic($_GET['tid']);
		if(bb_verify_nonce($_GET['_wpnonce'], 'socialit_hide_show_'.$topic->topic_id)){
			if($_GET['shs_opt'] == "2"){
				bb_update_topicmeta($topic->topic_id, 'hide_socialit', 'true');
			}else{
				bb_delete_topicmeta($topic->topic_id, 'hide_socialit');
			}
		}else{
			_e('Sorry, but that could not be done.', 'socialit');
			exit;
		}
		wp_redirect(get_topic_link($topic->topic_id));
	}
}

function get_socialit( $post_content = '' ) {
	global $socialit_plugopts, $public_tags, $socialit_is_mobile, $socialit_is_bot;
	
	$topic = get_topic( get_topic_id() );
	if( class_exists('Support_Forum') && $topic ){ //compatibility with Support Forum plugin for bbPress
		$support_forum = new Support_Forum();
		if( $support_forum->isActive() && in_array( $topic->forum_id, $support_forum->enabled ) ){
			if( ( $socialit_plugopts['sfpnonres'] == "no" && $support_forum->getTopicStatus() == "no" ) || ( $socialit_plugopts['sfpres'] == "no" && $support_forum->getTopicStatus() == "yes" ) || ( $socialit_plugopts['sfpnonsup'] == "no" && $support_forum->getTopicStatus() == "mu" ) )
				return;
		}
	}
	
	if($socialit_plugopts['mobile-hide'] == 'yes' && ($socialit_is_mobile || $socialit_is_bot))
		return;
	
	if( bb_is_topic() && bb_get_topicmeta( $topic->topic_id, 'hide_socialit' ) == true )
		return;
	
	if( bb_is_topic() ){
		$perms = urlencode( get_topic_link() );
		$title = get_topic_title();
		$socialit_content = get_post_text( $post_content );
		
		// Grab post tags for Twittley tags. If there aren't any, use default tags set in plugin options page
		$get_tags = bb_get_topic_tags(get_topic_id());
		if ($get_tags){
			foreach($get_tags as $tag) {
				$keywords = $keywords.$tag->name.',';
			}
		}
	}else{
		$perms = socialit_get_current_url(); 
		$title = bb_get_title();
		$feedperms = strtolower( $perms );
		$socialit_content = bb_get_option( 'description' );
	}
	if ( strlen( $title ) >= 80 ) {
		$short_title = urlencode( substr( $title, 0, 80 ) . "[..]" );
	}else{
		$short_title = urlencode( $title );
	}
	
	$title = urlencode( $title );
	$site_name = bb_get_option( 'name' );
	$socialit_content = urlencode( substr( strip_tags( strip_shortcodes( $socialit_content ) ),0,300 ) );
	$socialit_content = socialit_change_plus_apos( $socialit_content );
	$mail_subject = socialit_change_plus_apos( $title );
	$post_summary = stripslashes( $socialit_content );
	if ( !empty( $keywords ) ) {
		$d_tags = $keywords;
	}else{
		$d_tags = $socialit_plugopts['defaulttags'];
	}
	
	$y_cat = $socialit_plugopts['ybuzzcat'];
	$y_med = $socialit_plugopts['ybuzzmed'];
	$t_cat = $socialit_plugopts['twittcat'];
	$short_url = socialit_get_fetch_url();
	$current_rss_link = socialit_get_current_rss_link();
	
	// Temporary fix for bug that breaks layout when using NextGen Gallery plugin
	if( (strpos( $post_summary, '[') || strpos($post_summary, ']' ) ) )
		$post_summary = "";

	if( (strpos( $socialit_content, '[') || strpos($socialit_content,']' ) ) )
		$socialit_content = "";

	
	// select the background
	if ( $socialit_plugopts['bgimg-yes'] == 'yes' ){
		switch( $socialit_plugopts['bgimg'] ){
			case 'sexy':
				$bgchosen = ' social-it-bg-sexy';
				break;
			case 'caring':
				$bgchosen = ' social-it-bg-caring';
				break;
			case 'care-old':
				$bgchosen = ' social-it-bg-caring-old';
				break;
			case 'care-love':
				$bgchosen = ' social-it-bg-caring-love';
				break;
			case 'care-wealth':
				$bgchosen = ' social-it-bg-caring-wealth';
				break;
			case 'care-enjoy':
				$bgchosen = ' social-it-bg-caring-enjoy';
				break;
			case 'care-german':
				$bgchosen = ' social-it-bg-caring-german';
				break;
			default:
				$bgchosen = '';
				break;
		}
	}else{
		$bgchosen = '';
	}
	
	$style = ($socialit_plugopts['autocenter']) ? '' : ' style="'.__($socialit_plugopts['xtrastyle']).'"';
	if ( bb_is_feed() ) $style=''; // do not add inline styles to the feed.
	$expand = $socialit_plugopts['expand'] ? ' social-it-expand' : '';
	
	switch( $socialit_plugopts['autocenter'] ){
		case 1:
			$autocenter = ' social-it-center';
			break;
		case 2:
			$autocenter = ' social-it-spaced';
			break;
		default:
			$autocenter = '';
			break;
	}
	
	//write the menu
	$socials = "\n\n".'<!-- Start Of Code Generated By Social It Plugin By www.gaut.am -->'."\n".'<div class="social-it'.$expand.$autocenter.$bgchosen.'"'.$style.'><ul class="socials">';
	foreach ( $socialit_plugopts['bookmark'] as $name ) {
		switch ( $name ) {
			case 'socialit-twitter':
				$socials .= bookmark_list_item( $name, array(
					'post_by' => ( $socialit_plugopts['twittid'] ) ? "(via+@".$socialit_plugopts['twittid'].")" : '',
					'short_title' => $short_title,
					'fetch_url' => $fetch_url,
				) );
				break;
			case 'socialit-identica':
				$socials .= bookmark_list_item( $name, array(
					'short_title' => $short_title,
					'fetch_url' => $fetch_url,
				) );
				break;
			case 'socialit-mail':
				$socials .= bookmark_list_item( $name, array(
					'title' => $mail_subject,
					'post_summary' => $post_summary,
					'permalink' => $perms,
				) );
				break;
			case 'socialit-tomuse':
				$socials .= bookmark_list_item( $name, array(
					'title' => $mail_subject,
					'post_summary' => $post_summary,
					'permalink' => $perms,
				) );
				break;
			case 'socialit-diigo':
				$socials .= bookmark_list_item( $name, array(
					'socialit_teaser' => $socialit_content,
					'permalink' => $perms,
					'title' => $title,
				) );
				break;
			case 'socialit-linkedin':
				$socials .= bookmark_list_item( $name, array(
					'post_summary' => $post_summary,
					'site_name' => $site_name,
					'permalink' => $perms,
					'title' => $title,
				) );
				break;
			case 'socialit-comfeed':
				$socials .= bookmark_list_item( $name, array(
					'permalink' => $current_rss_link,
				) );
				break;
			case 'socialit-yahoobuzz':
				$socials .= bookmark_list_item( $name, array(
					'permalink' => $perms,
					'title' => $title,
					'yahooteaser' => $socialit_content,
					'yahoocategory' => $y_cat,
					'yahoomediatype' => $y_med,
				) );
				break;
			case 'socialit-twittley':
				$socials .= bookmark_list_item( $name, array(
					'permalink' => urlencode( $perms ),
					'title' => $title,
					'post_summary' => $post_summary,
					'twitt_cat' => $t_cat,
					'default_tags' => $d_tags,
				) );
				break;
			case 'socialit-tumblr':
				$socials .= bookmark_list_item( $name, array(
					'permalink' => urlencode( $perms ),
					'title' => $title,
				) );
				break;
			default:
				$socials .= bookmark_list_item( $name, array(
					'post_summary' => $post_summary,
					'permalink' => $perms,
					'title' => $title,
				) );
				break;
		}
	}
	$socials .= '</ul><div style="clear:both;"></div></div><!-- End Of Code Generated By Social It Plugin By www.gaut.am -->'."\n\n";
	return $socials;
}

// This function is what allows people to insert the menu wherever they please rather than above/below a post...
function selfserv_socialit() {
	echo get_socialit();
}

//write the <head> code
function socialit_public() {
	if( bb_is_topic() && bb_get_topicmeta( get_topic_id(), 'hide_socialit' ) == true )
		return;
	
	global $socialit_plugopts;
	$surl = (!is_null($socialit_plugopts['custom-css'])) ? $socialit_plugopts['custom-css'] : SOCIALIT_PLUGPATH.'css/style.css'; // If custom css, generated by sprite
	$surl = ($socialit_plugopts['custom-mods'] == 'yes') ? bb_get_option('uri').'socialit-mods/css/style.css' : $surl;
	wp_enqueue_style( 'social-it', $surl, false, SOCIALIT_VER, 'all' );
	if( $socialit_plugopts['expand'] || $socialit_plugopts['autocenter'] || $socialit_plugopts['targetopt']=='_blank' ){
		$surl = ($socialit_plugopts['custom-mods'] == 'yes') ? bb_get_option('uri').'socialit-mods/' : SOCIALIT_PLUGPATH;
		$infooter = ($socialit_plugopts['scriptInFooter'] == '1') ? true : false;
		wp_enqueue_script( 'social-it', $surl."js/social-it-public.js", array('jquery'), SOCIALIT_VER, $infooter );
	}
	
}

function socialit_insert_in_post( $post_content ) {
	global $socialit_plugopts;
	// decide whether or not to generate the bookmarks.
	$istopic = bb_is_topic();
	if ( ( $istopic && $socialit_plugopts['topic'] == 1 ) || ( bb_is_feed() && $socialit_plugopts['feed'] == 1 ) && ( bb_get_topicmeta( get_topic_id(), 'hide_socialit' ) != "true" ) ){ //socials should be generated and added
		$post_id_fc = get_post_id(); //post id for check
		if( bb_is_first( $post_id_fc ) )
			$post_content .= get_socialit( $post_content );
	}
	return $post_content;
}

//add actions/filters
add_action('wp_print_scripts', 'socialit_public', 997); //public css
if(bb_is_topic() && $_GET['socialit_hide_show'] == "1")
	add_action('bb_init', 'socialit_hide_show_do', 997); //do the function of hide/show socialit
add_filter('post_text', 'socialit_insert_in_post', 997); //to insert social it automatically below the first post of every topic
add_filter('bb_topic_admin', 'socialit_hide_show', 6); //to show the option to the admin whether to show the bookmark menu on a particular topic