<?php
/*
Plugin Name: BBPress Tim's Youtube Plugin
Plugin URI: http://www.directwebsolutions.nl
Description: Makes it possible for users to comment with [youtube]movie-url[/youtube]. This makes migrations from other CMS-systems to BBpress possible.
Author: Tim Boormans, Direct Web Solutions
Author URI: http://www.directwebsolutions.nl
Version: v1.0
License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
Date: 31 August 2010
Written for BBpress version: 1.0.2
*/

// choose where this module should be triggered and with what function
add_filter('post_text', 'tims_youtube_transform');
add_filter('pm_text', 'tims_youtube_transform');

function tims_youtube_transform($message) {
	// First find all youtube tags on the output page
	if(preg_match_all('/\[youtube\]([^\[]{1,})\[\/youtube\]/i', $message, $matches)) {
		/*
		$matches = Array (
			[0] => Array (
				[0] => [youtube]http://www.youtube.com/watch?v=eWv8SULLWqg[/youtube]
			)
			[1] => Array (
				[0] => http://www.youtube.com/watch?v=eWv8SULLWqg
			)
		)
		*/
		
		// Then foreach found tag check in which format it is given and generate a Youtube HTML Player object.
		foreach($matches[1] as $match) {
			/*
			$match = http://www.youtube.com/watch?v=eWv8SULLWqg
			*/
			if(preg_match('/http:\/\/www\.youtube\.com\/watch\?v=([^\&]{1,})/i', $match, $v_match)) {
				/*
				$v_match = Array (
					[0] => http://www.youtube.com/watch?v=eWv8SULLWqg
					[1] => eWv8SULLWqg
				)
				*/
				$message = str_replace(	'[youtube]'.$match.'[/youtube]', /* the found match in the $message */
										'<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.$v_match[1].'?fs=1&amp;hl=nl_NL"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/'.$v_match[1].'?fs=1&amp;hl=nl_NL" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="350"></embed></object>', /* replacement html youtube object */
										$message); /* the html output buffer */
				
			} elseif(preg_match('/http:\/\/www\.youtube\.com\/v\/([^\&]{1,})\?(.*)/i', $match, $v_match)) {
				/*
				$v_match = Array (
					[0] => http://www.youtube.com/v/eWv8SULLWqg?fs=1&amp;hl=nl_NL
					[1] => eWv8SULLWqg
					[2] => fs=1&amp;hl=nl_NL
				)
				*/
				$message = str_replace(	'[youtube]'.$match.'[/youtube]', /* the found movie in the $message */
										'<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.$v_match[1].'?'.$v_match[2].'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/'.$v_match[1].'?'.$v_match[2].'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="350"></embed></object>', /* replacement html youtube object */
										$message); /* the html output buffer */
			}
		}
	}
	
	return $message;
}
?>