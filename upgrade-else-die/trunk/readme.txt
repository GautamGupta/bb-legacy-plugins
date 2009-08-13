=== Upgrade Else DIE ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/
Tags: ie6, die, upgrade, ie, microsoft, gautam
Requires at least: 1.0
Tested up to: 1.0.2
Stable tag: 0.1

Aggresive way to tell IE6 users to upgrade OR ELSE!!!

== Description ==

This plugin is not for those who would like to give users the option of upgrading or viewing the site with their outdated browser.
The plugin will detect if the browser is IE6 first. If they are using IE6, the plugin kills loading of your site and displays a warning message.
The message reads as follows:

-----------------------------------------
WARNING!
You are using Internet Explorer 6.0. Due to security risks associated with this outdated browser, as well as its noncompliance with WC3 standards, I have disabled access for all Internet explorer 6.0 users.

I would love to share the content of this site with you, but unless you upgrade your browser, this is impossible. There are too many security risks posed by this outdated browser. Won't you please take a moment and upgrade - it's 100% free!

If you are at your place of employment and do not have access or permission to upgrade your browser, please contact your company's IT department and request an upgraded browser, which will keep your company's information safe and secure. Upgrading will also improve your overall browsing experience!
-----------------------------------------

There will then be two buttons the user can choose: "No Thanks" and "Upgrade". If they choose the "Upgrade" button, they will be taken directly to Microsoft's IE upgrade page, where they can quickly upgrade to the latest version of IE.
However, if they choose "No Thanks" then they will be taken to "CrashIE.com" out of pure spite. CrashIE.com uses a well known bug in IE to crash the browser and cause the user to have to restart it.

The Plugin is inspired from <a href="http://eight7teen.com/upgrade-else-die/">Upgrade Else Die</a> plugin for WordPress by Josh.

== Screenshots ==

1. This is what users viewing your site with Internet Explorer 6 will see with this plugin activated.

== Installation ==

1. Upload the extracted `upgrade-else-die` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. That's it! Now sit back and enjoy sweet vengeance against IE6!

== Frequently Asked Questions ==

= Where's the options page? =

This plugin is quite simple, and therefore doesn't need any configurable options. Just upload the plugin, activate, and rest easy knowing that IE6 is officially banned from your site.

= What do you mean "It will crash their browser"? =

The "No Thanks" button is a link to [CrashIE](http://crashie.com) {don't click that link if you're using IE6}. CrashIE uses a well known bug in IE to their advantage and exploits it with no mercy. The bug is simple, and it's not a "critical" crash. It will simply show the user the ever famous "Internet Explorer has encountered a problem and needs to close" message. After that, they'll have to close the browser and start all over.

== Disclaimer ==

This plugin will crash the user's browser (if they are using IE6) if they click the "No Thanks" button! If you do not wish to bring pain and suffering to Internet Explorer version 6.0, it is recommended that you do not use this plugin.
The plugin will not dipslay the message to users who are viewing your site in IE7 or above.