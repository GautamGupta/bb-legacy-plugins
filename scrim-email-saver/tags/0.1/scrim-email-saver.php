<?php
/*
 Plugin Name: Scr.im Email Saver
 Plugin URI: http://gaut.am/bbpress/plugins/scrim-email-saver/
 Description: The plugin filters your forum's posts for email IDs and converts them into <a href="http://scr.im/">Scr.im</a> links so that there IDs are not picked up by bots.
 Author: Gautam Gupta
 Author URI: http://gaut.am/
 Version: 0.1
*/

/**
 * @license GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html
 */

/** Version */
define( 'SES_VER', '0.1' );

/**
 * Save the Emails!
 *
 * @param string $content The content to be processed
 *
 * @uses WP_Http To make the remote call
 *
 * @return string The processed content
 */
function ses_save_emails( $content ) {
	/* Get on with the business, match all the emails */
	preg_match_all( '/\b[.0-9a-z_+-]+@[.0-9a-z_+-]+\.[0-9a-z]{2,}\b/i', $content, $emails );
	//'/\b[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b/' --> used by another plugin OR '#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i' --> used by make_clickable (above is combined)
	
	if ( !$emails = array_filter( array_map( 'sanitize_email', (array) $emails[0] ) ) ) /* Sanitize all the emails and then filter for null/false values */
		return $content; /* No emails? Then return the content! */
	
	foreach ( $emails as $email ) {		
		/* Ok, we are in, now call the scrim api and request for generating the scrim code
		   WP_Http does a great job for us by taking all the tensions and applying all the possible methods ;) */
		$scrim = wp_remote_retrieve_body( /* Check if any errors are there */
				wp_remote_post( /* Make the call */
					'http://scr.im/xml/',
					array(
						'body'		=> array( 'email' => $email ),
						'user-agent'	=> 'Scrim Email Saver bbPress Plugin v' . SES_VER  /* Brand our plugin by user agent :P */
					)
				)
			);
		if ( !$scrim || strpos( $scrim, '<scrim>' ) === false ) /* Call failed? No scrim? Go to the next email please! */
			continue;
		
		/* Preg match the scrim, not parsing the XML so that there is no PHP 5 requirement */
		preg_match( '/<scrim>([a-z0-9]+)<\/scrim>/i', $scrim, $code ); // Should we match upper-case letters?
		if ( !$code = $code[1] ) /* No code? Go to the next email please! */
			continue;
		
		$url = 'http://scr.im/' . $code; /* Make the URL */
		
		/* All done, now replace the actual mail with the URL
		   We dont need <a href> or rel=nofollow because the make_clickable filter on post_text does that for us :D */
		$content = str_replace( $email, $url, $content );
	}
	
	/* Finally, return the content */
	return $content;
}

/* Hook the pre_post to ses_save_emails at priority -9 and 1 parameter. We avoid post_text filter to prevent WP_Http calls everytime */
add_filter( 'pre_post', 'ses_save_emails', -9, 1 );
