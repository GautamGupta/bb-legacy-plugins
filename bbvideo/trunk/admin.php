<?php
/*
BBVideo Plugin v0.1 admin template
http://www.naden.de/blog/bbvideo-bbpress-video-plugin
*/
?>
<h2>bbVideo v<?php print( $this->version ); ?> - <?php _e( 'Settings' ) ?></h2>
<div>
<strong><a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin">Plugin Homepage</a></strong>
</div>
<p>
<h4>Supported Video-Portals (<?php print( count( $this->provider ) ); ?>):</h4>
<ul>
<?php

foreach( $this->provider as $k => $provider )
{
	printf( '<li><a href="%s" target="_blank">%s</a></li>', $provider[ 'page_url' ], ucfirst( $k ) );
}
?>
</ul>
</p>

<div align="center"> 
<strong><a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin">Plugin Homepage</a></strong>
</div>