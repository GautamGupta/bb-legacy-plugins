<?php
/*
Plugin Name: Comment Quicktags for bbPress
Plugin URI: http://www.40annibuttati.it/comment-quicktags-for-bbpress/
Description: Inserts a quicktag toolbar on the post topic form. js_quicktags is slightly modified version of Alex King's newer <a href="http://www.alexking.org/blog/2005/07/01/javascript-quicktags-12/">Quicktag.js</a> plugin modified from original found <a href=" http://www.asymptomatic.net/wp-hacks">here</a>.
Version: 1.1
Author: Stefano Aglietti
Author URI: http://www.40annibuttati.it
*/
/*
Comment Quicktags - Inserts a quicktag toolbar on the blog comment form.

Based on Comment Quicktags + plugin written by Dan Cameron (http://www.dancameron.org)

This code is licensed under the MIT License.
http://www.opensource.org/licenses/mit-license.php
Copyright (c) 2005 Owen Winkler

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/*
Comment Quicktags - Inserts a quicktag toolbar on the blog comment form.

*** Directions For Use ***
Copy the CommentQT files into your my-plugins directory.

*** Styling the Toolbar ***
The toolbar CSS id is "#ed_toolbar", so you could add this
to your stylesheet, I use:

#ed_toolbar input
{
	background: #14181B;
	color: white;
	border:2px dashed #323136;
	padding: 0px;
	width: 65px;
}
#ed_toolbar input:hover
{
	background: #323136;
	color: white;
	border:2px dashed #14181B;
	padding: 0px;
	width: 65px;
}
}


*/

if(defined('BBPATH')) :

	function comment_quicktags($unused) {
		$scripturl =  bb_get_option ('uri') . 'my-plugins/js_quicktags.js';
		$thisurl = bb_get_option ('uri') . 'my-plugins/' . basename(__FILE__);
		echo '<script src="' . $scripturl . '" type="text/javascript"></script>' . "\n";
		echo '<script src="' . $thisurl . '" type="text/javascript"></script>' . "\n";
		ob_start('comment_quicktags_ob');
	}

	function comment_quicktags_ob($content) {
		$toolbar = '<script type="text/javascript">edToolbar();</script>';
		$activate = '<script type="text/javascript">var edCanvas = document.getElementById(\'\\2\');</script>';
		$content = preg_replace('/<textarea(.*?)id="([^"]*)"(.*?)>(.*?)<\/textarea>/s', $toolbar . '<textarea\\1id="\\2"\\3>\\4</textarea>'.$activate, $content, PREG_OFFSET_CAPTURE);
		return $content;
	}

	add_action('bb_head', 'comment_quicktags');

else :

?>

var edButtons = new Array();

var extendedStart = edButtons.length;

// below here are the extended buttons
edButtons[edButtons.length] =
new edButton('ed_strong'
,'Bold'
,'<strong>'
,'</strong>'
,'b'
);

edButtons[edButtons.length] =
new edButton('ed_em'
,'Italic'
,'<em>'
,'</em>'
,'i'
);

edButtons[edButtons.length] =
new edButton('ed_link'
,'Link'
,''
,'</a>'
,'a'
); // special case

edButtons[edButtons.length] =
new edButton('ed_block'
,'B-quote'
,'<blockquote>'
,'</blockquote>'
,'q'
);

edButtons[edButtons.length] =
new edButton('ed_pre'
,'Code'
,'`'
,'`'
,'c'
);

edButtons.push(
	new edButton(
		'ed_ol'
		,'OL'
		,'<ol>\n'
		,'</ol>\n\n'
		,'o'
	)
);

edButtons.push(
	new edButton(
		'ed_ul'
		,'UL'
		,'<ul>\n'
		,'</ul>\n\n'
		,'u'
	)
);

edButtons.push(
	new edButton(
		'ed_li'
		,'LI'
		,'\t<li>'
		,'</li>\n'
		,'l'
	)
);

edButtons.push(
		new edButton(
			'ed_img'
			,'IMG'
			,''
			,''
			,'m'
			,-1
		)
	);

<?php
	 endif;
?>