=== Browser Timer ===
Tags: _ck_, performance, speed, benchmark, cache, caching, faster, timer
Contributors: _ck_
Requires at least: 0.9
Tested up to: 1.1
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Improve your forums by learning how long it really takes for various users to see your bbPress pages.

== Description ==

Improve your forums by learning how long it really takes for various users to see your bbPress pages.

While my bb-benchmark plugin  will tell you how long it's taking your server to create pages, 
you really have no idea how long it's taking your users to browse your forums and see those pages. 

ie. 200ms on the server side may be 5+ seconds with javascript, iframes, etc. on the visitors side

You may be timing in your own browser, but often you have many things already cached and might use a faster browser 
or live much closer to your server than someone else across the country or the other side of the world.

So now you can know exactly how long it's taking for them to see your pages.

It's very accurate in my testing, within 100ms typically.

Basically it works by using a sort of "round trip" timer.

You can optionally add a free geo-location database and it will tell you what country the visitor is in, 
which  helps to understand why a time may be so high/low. 

== Installation ==

1. Edit the top of the plugin to adjust the four settings as desired, see the FAQ for more details

2. Add the "browser-time.php" file to bbPress "my-plugins/browser-timer/" directory 

3. Optionally upload the two IP2C files for geo-location into the same directory

4. Activate in admin control panel

5. Once properly configured and running, administrators see the log by just adding `?browsertimer` to the forum url.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Frequently Asked Questions ==

= Currently there are only four settings and all but the first are optional, edit the top of the plugin to see them =

* log location
`
$browsertimer['log']='/browsertimer/browsertimer.log';
`
That is where the log is kept. 
Obviously the directory must be chmod 777 on your server.
For that reason I STRONGLY recommend you put it ABOVE the web root.

The default setting will keep it safely above the web root:
` 
$browsertimer['log']=dirname($_SERVER['DOCUMENT_ROOT']).'/browsertimer/browsertimer.log'; 
`
but since it may confuse people, adjust it as you will.

Further explaination:
ie.   
`
/home/username/public_html/
`  
is the webroot on many servers but not all by any means
you could in theory make
`
/home/username/browsertimer/
`
and chmod 777 that directory, and it will work with the default setting.

* filter out bad times
`
$browsertimer['filter']=true;
`		
This setting just throws away entries longer than 20 seconds or less than 100ms which are typically bogus. 
For example Google has a nasty habit now of parsing javascript no matter how obfuscated it may be 
to extract URLs, and sure enough it will follow the browser timer, giving you really high, weird numbers.

* control which pages are timed (or all as default)
`
$browsertimer['pages']=false;	
`
This allows you to control what pages are timed.
I'd leave it alone for now but someone may have specific interest in mind.

* enable geo-location to find and record visitors country
`
$browsertimer['geoip']=false;
`

This is where the geo-location magic happens, it's optional, and will tell you what country the visitor is in with about 90% accuracy.

false means country lookup off

or set it to  `$browsertimer['geoip']='ip2c';`

and download this program called IP2C
http://admin.firestats.cc/ccount/click.php?id=74
extract these two files and put them in the same directory:
`	
ip2c.php  
ip-to-country.bin
`
(note ip-to-country.bin is large, it's one big database)

== Screenshots ==

1. Browser Timer Log - administrator's view
	
== To Do ==

* admin menu ?

== Changelog ==

* 0.0.2 2010-07-17 first public beta release
