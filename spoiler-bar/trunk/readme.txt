=== Spoiler Tags ===
Tags: html, spoiler
Contributors: ipstenu
Requires at least: Unknown
Tested up to: 1.0alpha2
Stable Tag: 0.1

Allow users to include &lt;spoiler&gt;&lt;/spoiler&gt; tags in their posts.

== Description ==

With this plugin, users can spoiler bar text so it will show up hidden. Users will then have to highlight the text to see the spoilers

== Installation ==

Create the folder `spoiler-tags` in the bbPress' `my-plugins/` directory.

Upload the files

Add .spoiler {} to your CSS file

EXAMPLE:
.spoiler
{
        background: #000000;
        color: #000000;
}

== Integration with QuickTags==

If you want to integrate this with quick tags (http://www.40annibuttati.it/comment-quicktags-for-bbpress/)

=== PHP CHANGES ===

Find:
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

Add below:

// Added for spoiler tags
	edButtons.push(
		new edButton(
			'ed_spoiler'
			,'SPOILER'
			,'<spoiler>'
			,'</spoiler>'
			,'s'
		)
	);

=== JAVASCRIPT CHANGES===

Find:
		new edButton(
			'ed_img'
			,'IMG'
			,''
			,''
			,'m'
			,-1
		)
	); // special case


Add below:
		new edButton(
			'ed_spoiler'
			,'SPOILER'
			,'<spoiler>'
			,'</spoiler>'
			,'s'
		)
	);
