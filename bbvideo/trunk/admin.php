<?php
/*
BBVideo Plugin v0.24 admin template
http://www.naden.de/blog/bbvideo-bbpress-video-plugin
*/

if( isset( $_POST[ 'cmd' ] ) )
{
  $this->SaveOptions(
    array(
      'embed' => intval( $_POST[ 'embed' ] ),
      'download' => intval( $_POST[ 'download' ] )
    )
  );
}
?>
<h2>bbVideo v<?php print( $this->version ); ?> - <?php _e( 'Settings' ) ?></h2>
<div>
<strong><a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin">Plugin Homepage</a></strong>
</div>

<form method="post" action="">
<p>
<h4>Embed:</h4>
<label for="embed">Zeige Embed Optionen unter den Videos. / Show embed field below the videos.</label>
<p>
<input type="radio" name="embed" value="1" <?php print( $this->options[ 'embed' ] == 1 ? ' checked="checked"' : '' ); ?>/> Ja
<input type="radio" name="embed" value="0" <?php print( $this->options[ 'embed' ] == 0 ? ' checked="checked"' : '' ); ?>/> Nein
</p>
</p>
<p>
<h4>Download:</h4>
<label for="download">Zeige Download-Link unter den Videos. / Show download link field below the videos.</label>
<p>
<input type="radio" name="download" value="1" <?php print( $this->options[ 'download' ] == 1 ? ' checked="checked"' : '' ); ?>/> Ja
<input type="radio" name="download" value="0" <?php print( $this->options[ 'download' ] == 0 ? ' checked="checked"' : '' ); ?>/> Nein
</p>
</p>

<p class="submit"><input type="submit" name="cmd" value="speichern / save" /></p>

</form> 

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