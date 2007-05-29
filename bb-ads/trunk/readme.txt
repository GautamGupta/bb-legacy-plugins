=== BB-Ads ===
Tags: ads
Contributors: wittmania
Requires at least: 0.8
Tested up to: 0.8
Stable Tag: 1.0

BB-Ads allows you to place custom ads/messages (random or specified) throughout your forum.

== Description ==

BB-Ads allows you to place custom ads/messages throughout your forum.  When the
function is called it will serve a random file (php, html, etc.) from a pre-defined directory.
Or, you can use the function to call a specific ad/message file.  You can also use the
example files to serve up a different ad depending on the type of page (front, topic, etc.)
the ad/message will appear on.

== Installation ==

1.  Upload bb-ads.php to your /my-plugins/ directory.
2.  Create this sub-directory: /my-plugins/bb-ads-files/
3.  Upload your ad/message files into this sub-directory. 
		*See below for detailed instructions
4.  Add the code to call the function wherever you want the ads displayed.
		*See below for detailed instructions

== Configuration ==

Adding additional file types:

	If you'd like to enable additional file types other than .htm, .html, and
	.php, just add additional lines in bb-ads-php.  Just be sure your mime-type
	definition is correct!
	
	For example:
	
	Image Files:
		$extList['gif'] = 'image/gif';
		$extList['jpg'] = 'image/jpeg';
		$extList['png'] = 'image/png';
	
== Usage ==

Placement:

	You can call the function anywhere in your bbpress site using the following:
	
	<?php bb_ads('any'); ?> 
	
	To display a specific file, just add the exact file name instead of 'any':
	
	<?php bb_ads('sample_file.php'); ?>
	
	Either of these should work anywhere on your bb, including the front page,
	profile pages, and forum pages.
	
	To add a message/ad on a topics page, between posts:
		1.  Open topics.php in your template's directory.
		2.  Find this section:

			<?php foreach ($posts as $bb_post) : $del_class = post_del_class(); ?>
			<li id="post-<?php post_id(); ?>"<?php alt_class('post', $del_class); ?>>
			<?php bb_post_template(); ?>
			</li>
			<?php endforeach; ?>

			This is known as the post loop.  It executes until it runs out of posts
			to display.
			
			AFTER the closing </li> tag, but BEFORE the <?php endforeach; ?>, insert
			the following lines:
			
				<!-- START bb-ads placement -->
				<?php if (!isset($postnum)) { $postnum = 1; }
			
				//Where should we put the ad? (will appear AFTER the assigned post #)
				$showad1 = 3; 
			
				if ($postnum == $showad1 ) { 
					bb_ads('any'); }
			
				$postnum++; ?>
				<!-- END bb-ads placement -->

			NOTE: You can change the value of $showad1 depending on where you want
			the message to be displayed.  If your topics don't get a lot of replies,
			you may want to set it to 1.
			
			You could also change the code to display a specific ad, but it would
			show the exact same message on every single topic.  So, it is probably
			best just to leave it as 'any'.
			
Creating Ad Files:

	The .zip file includes two sample ads.  You can use them as a starting point if
	you'd like.
	
	If you are comfortable with PHP, you might want to use .php files because of the
	wide range of options they provide.  If not, you might just stick with .html files.
	
	To style your ads, wrap them in a div and assign the div a class, which you can
	define in your CSS file.
	
	Example:
	<div class="really_sweet_ad">Really sweet ad body here</div>
	
	You can also assign characteristics to the div inside the file itself if you don't
	want to modify your CSS file.
	
	Example:
	<div align="center" style="background-color:#999999; padding: 10px;">
	Centered ad with a dark gray background and 10px padding on each side</div>
	
	You can add links, images, and all kinds of things inside these files, especially
	if you use PHP files for all of your ads.

	The first sample file (ad1.php) also contains the syntax you would use to display
	a specific ad depending on what page it is displayed on.  This will enable you to
	further customize the ads depending on their context.
	
== To Do ==
	
	*This is a rough but functional plugin, not much more than a proof of concept really.
	Plenty of revising left to do...
	
	*Find a better way to define the folder so the user can set a custom base folder
	to pull the ads from.
		
	*Much, much more!