=== Spoiler Tags ===
Tags: html, spoiler
Contributors: ipstenu, _ck_
Requires at least: 0.9
Tested up to: 1.0
Stable Tag: 0.4
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5227973


Allow users to include &lt;spoiler&gt;&lt;/spoiler&gt; tags in their posts.

== Description ==

With this plugin, users can spoiler bar text so it will show up hidden. Users will then have to highlight the text to see the spoilers.

As of v.3, thanks to _ck_, it has been updated to work with the <a href="http://bbpress.org/plugins/topic/bbcode-lite/">BBCode plugin</a> as well!
== Installation ==

Create the folder `spoiler-tags` in the bbPress' `my-plugins/` directory and upload the files.

Add .spoiler {} to your CSS file

EXAMPLE:
`.spoiler
{
        background: #000000;
        color: #000000;
}`

== Frequently Asked Questions ==

= How can I change the colors? =

Straight CSS, baby!

`
.spoiler
{
        background: #000000;
        color: #000000;
}
`

= How can I integrate with QuickTags =

<a href="http://bbpress.org/plugins/topic/quicktags-4-bbpress/">Quick Tags</a> are those neat-o buttons that let users click to add HTML.

<strong> In comment_QT_4_bbpress.php </strong>

Find:
`
	edButtons.push(
		new edButton(
			'ed_img'
			,'IMG'
			,''
			,''
			,'m'
			,-1
		)
	);`

Add below:

`// Added for spoiler tags
	edButtons.push(
		new edButton(
			'ed_spoiler'
			,'SPOILER'
			,'<spoiler>'
			,'</spoiler>'
			,'s'
		)
	);`

<strong>In js_quicktags.js</strong>

Find:
`
		new edButton(
			'ed_img'
			,'IMG'
			,''
			,''
			,'m'
			,-1
		)
	); // special case
`

Add below:
`
		new edButton(
			'ed_spoiler'
			,'SPOILER'
			,'<spoiler>'
			,'</spoiler>'
			,'s'
		)
	);
`

= How do I integrate with bbCode Buttons? =
<a href="http://bbpress.org/plugins/topic/bbcode-buttons/">BBcode Buttons</a> are the buttons for BBcode (rather than HTML, glad you followed me there).

<strong>In BBcode-buttons.php</strong>

Find: 
`BBcodeButtons.push(new BBcodeButton('ed_li','LI','[*]','[/*]','l','','list item'));";`

Add below:
`BBcodeButtons.push(new BBcodeButton('ed_spoiler','SPOILER','[spoiler]','[/spoiler]','r','','spoiler'));";`

<strong>In BBcode-buttons.js</strong>

There's probably some change here, but I'm not sure what as I don't use BBCode.
