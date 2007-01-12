=== bbPress-Mobile ===
Tags: rating, rate, vote
Contributors: Trent
Requires at least: 0.73
Tested up to: 0.74
Stable Tag: 0.7.3

This plugin automatically creates mobile edition for common mobile devices.

== Description ==

== Installation ==

1. Copy bb-mobile.php into your /my-plugins/ directory
2. Copy mobile.css into your /my-templates/ directory
3. If you don't already have a header.php in /my-templates/, copy header.php from /bb-templates/ and place it in /my-templates/
4. Edit header.php in a text editor changing:

	`<link rel="stylesheet" href="<?php bb_stylesheet_uri(); ?>" type="text/css" />`

To:

`<!--  BB-Mobile Plugin -->
<?php if ( mobile_check() ) : ?>
<?php $stylesheet = (bb_get_option('uri') . 'my-templates/mobile.css'); ?>
	<link rel="stylesheet" href="<?php echo $stylesheet; ?>" type="text/css" />
<?php else : ?>
	<link rel="stylesheet" href="<?php bb_stylesheet_uri(); ?>" type="text/css" />
<?php endif; ?>
<!-- End of BB-Mobile Plugin -->`

5. Navigate a mobile phone of PDA device to your site!

Note:   If you are using an internationalized version of bbPress, you have to also do step 6!

6. If internationalized,  also change:

	`<link rel="stylesheet" href="<?php bb_stylesheet_uri( 'rtl' ); ?>" type="text/css" />`

To:

`<!--  BB-Mobile Plugin -->
<?php if ( mobile_check() ) : ?>
<?php $stylesheet = (bb_get_option('uri') . 'my-templates/mobile.css'); ?>
	<link rel="stylesheet" href="<?php echo $stylesheet; ?>" type="text/css" />
<?php else : ?>
	<link rel="stylesheet" href="<?php bb_stylesheet_uri( 'rtl' ); ?>" type="text/css" />
<?php endif; ?>
<!-- End of BB-Mobile Plugin -->`

== Frequently Asked Questions ==

If you need to change the way the page looks on a mobile, just edit mobile.css to your hearts content!

More information at: 
http://bbpress.org/forums/topic/467

Trent Adams