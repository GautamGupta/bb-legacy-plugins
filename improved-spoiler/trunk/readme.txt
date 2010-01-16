=== Improved Spoiler ===
Tags: html, spoiler
Contributors: Nerieru, Nightgunner5
Requires at least: 0.9
Tested up to: 1.02
Stable Tag: 0.1
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=11219165


Allows your users to use spoiler tags with an optional title. [spoiler][/spoiler] and if you want to add a title [spoiler=title][/spoiler]

== Description ==

This plugin is based on the plugin spoiler-bar. It allows the user to hide a text from plain sight, the user then simply needs to hover over it to see the hidden text.

So you're probably curious as to what is actually improved?
- users can add titles to their spoilers
- users don't need to highlight the text, but just hover over it with their mouse
- usability has gone up this way, and because it now describes what a user should do.

Nightgunner5 was kind enough to help me out with the title part.

== Installation ==

1. Upload improved-spoiler.php to your /my-plugins/ folder.
2. Add the following to your theme's css file.
	/* Spoiler mod css */
		.spoiler {color:black;background: black;}
		.spoiler:hover {color:black;background:transparent;}
		.spoilertitle{background: #ddd; font-weight: bold;}
		.spoilersmall{font-size: 8px;}
	/* end spoiler mod css */
3. Activate the plugin.

== Frequently Asked Questions ==

= How do I integrate with bbCode Buttons? =
<a href="http://bbpress.org/plugins/topic/bbcode-buttons/">BBcode Buttons</a> are the buttons for BBcode (rather than HTML, glad you followed me there).

<strong>In BBcode-buttons.php</strong>

Find: 
`BBcodeButtons.push(new BBcodeButton('ed_li','LI','[*]','[/*]','l','','list item'));";`

Add below:
`BBcodeButtons.push(new BBcodeButton('ed_spoiler','SPOILER','[spoiler]','[/spoiler]','r','','spoiler'));";`

<strong>In BBcode-buttons.js</strong>

= In my theme I can't see the spoiler even if I'm hovering over it, how do I fix this? =

That's easy, in the code you added to the style.css file of your theme change
.spoiler:hover{color:black;background:transparent;}
to
.spoiler:hover{color:white;background:transparent;}

You can change white to any colour code you want the text to be when you hover over it.