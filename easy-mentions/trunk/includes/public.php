<?php

/**
 * @package Easy Mentions
 * @subpackage Public Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/easy-mentions/
 */

/**
 * Links the users in the posts
 *
 * @see bp_activity_at_name_filter() BuddyPress Function
 * 
 * @param $content The content to be parsed
 */
function em_do_linking( $content ){
	global $em_plugopts;
	
        preg_match_all( '/[@]+([A-Za-z0-9-_]+)/', $content, $usernames );

        if ( !$usernames = array_unique( $usernames[1] ) ) /* Make sure there's only one instance of each username */
                return $content;

        foreach( (array)$usernames as $username ) {
                if ( !$user = bb_get_user( $username, array( 'by' => 'login' ) ) ){ //check #1, by username
			if ( !$user = bb_get_user( $username, array( 'by' => 'nicename' ) ) ) //check #2, by nicename
				continue;
                }

                /* Increase the number of new @ mentions for the user - maybe later */
                /*$new_mention_count = (int)bb_get_usermeta( $user_id, 'em_mention_count' );
                bb_update_usermeta( $user_id, 'em_mention_count', $new_mention_count + 1 );*/
		
		if ( 'website' == $em_plugopts['link-to'] ) {
			if ( !$link = $user->user_url )
				$link = get_user_profile_link( $user->ID );
		} else {
			$link = get_user_profile_link( $user->ID );
		}
		
		if ( $link )
			$content = str_replace( "@$username", "@<a href='" . $link . "'>$username</a>", $content ); //should we add rel='nofollow'?
        }

        return $content;
}

/**
 * Parse the text and insert username and post link in place of %%USERNAME%% and %%POSTLINK%%
 *
 * @param $text The text to be parsed
 * @param $username Username
 * @param $post_link The link of the post
 */
function em_parse_text( $text, $username, $post_link ){
	if ( !$text )
		return;
	
	$text = str_replace( "%%USERNAME%%", $username, $text );
	$text = str_replace( "%%POSTLINK%%", $post_link, $text );
	
	return $text;
}

/**
 * Add reply link below each post
 *
 * @param $post_links Array of the links
 * @param $args Array of args
 */
function em_reply_link( $post_links, $args ) {
	global $em_plugopts;
	
	if ( $em_plugopts['reply-link'] == 1 && $em_plugopts['reply-text'] && bb_is_topic() && topic_is_open() && ( bb_is_user_logged_in() || ( function_exists( 'bb_is_login_required' ) && !bb_is_login_required() ) ) ){ /* Check if link is needed */
		$text		= em_parse_text( $em_plugopts['reply-text'], get_post_author(), get_post_link() );
		$js		= "var ema=document.getElementById('post_content');var emb=ema.value;if(emb!='')emb+='\\n\\n';ema.value=emb+'" . $text . "\\n\\n';ema.focus();void(0);";
		$post_links[]	= $args['before_each'] . '<a class="reply_link" style="cursor:pointer" onclick="' . $js . '">' . __( 'Reply', 'easy-mentions' ) . '</a>' . $args['after_each'];
	}
	
        return $post_links;
}

add_filter( 'post_text', 'em_do_linking', -999, 1 ); /* Do Linking */
add_filter( 'bb_post_admin', 'em_reply_link', 11, 2 ); /* Add reply link */
