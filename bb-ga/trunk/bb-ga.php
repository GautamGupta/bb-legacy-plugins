<?php
/*
Plugin Name: bb-ga
Plugin URI: http://bbpress.org/plugins/topic/bb-ga/
Description: add latest Google Analytics code to your bbpress head。
Author: yexingzhe
Author URI: http://bbpcn.net
Version: 0.1.5
Installation:
1.change the line $ga_web_id="UA-xxxxxx-xx"; to your Google Analytics  Web Property ID .
2. Add entire folder `bb-ga` to bbPress' `my-plugins/` directory and activate.
*/

add_filter('bb_head', 'google_analytics_for_bbpress');
$ga_web_id="UA-xxxxxx-xx";//change this to your Google Analytics Web Property ID。
function google_analytics_for_bbpress(){
?>
<!-- Google Analytics Begin-->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo $ga_web_id;?>']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<!-- Google Analytics End -->
<?php
}
?>