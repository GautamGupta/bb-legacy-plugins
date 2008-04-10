=== Enhanced Tag Heat Map ===
Contributors: so1o
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=so1oonnet%40gmail%2ecom&item_name=Aditya%20Naik%20for%20bbPress%20Plugin%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: tag, tags, related, heat map
Requires at least: 0.9
Tested up to: 0.9.0.1
Stable tag: 1.0.2

Enhances the tag heat map to highlight the related tags on mouse over

== Description ==

Enhanced Tag Heat Map replaces the existing tag heat map to highlight related
tags for each of the tag on mouse over. The option can set the options for 
the tag heat maps like the smallest size of font, largest size of font and 
unit for the size of font and number of tags in the heat map.

Currently the plugin works with the themes which use the tag heat map which 
comes with kakumei theme. i.e. The themes which have the 'hottags' div 
around the tag heat map. In the future versions of the plugin, this would be 
a configurable option. 

= Themes Compatibility =

The themes which have the 'hottags' div like the one from the kakumei theme 
should be ready to work out of the box. if your theme does not have a div 
around the heat map with that name - add one :). in the future versions 
this name would be configurable.  

== Installation ==

1. Upload the files to the `/wp-content/plugins` directory - prefereably in a seperate directory like 'enhanced-tag-heat-map'
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the admin page under the plugins menu and setup the options to match your theme.

== Version History ==

Version History
* 1.0
  * Initial Release
* 1.0.1
  * remove calls to firebug console
* 1.0.2
  * removed hooks to wordpress
* 1.1.0
  * update the getTagHeatMapRelatedTagsScript function make only one call to database
* 1.1.1
  * removed highlights of self on tag mouse over


