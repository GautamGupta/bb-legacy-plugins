<?php
/*
Easy Video Embed Plugin v0.1b admin template
http://www.bbpressplugin.com/easy-video-embed-plugin/
*/

if( isset( $_POST[ 'cmd' ] ) )
{
 
  $this->SaveOptions(
    array(
      'youtube-width' => intval( $_POST[ 'youtube-width' ] ),
      'youtube-height' => intval( $_POST[ 'youtube-height' ] ),
	  'glumbert-width' => intval( $_POST[ 'glumbert-width' ] ),
	  'glumbert-height' => intval( $_POST[ 'glumbert-height' ] ),
	  'googlevideo-width' => intval( $_POST[ 'googlevideo-width' ] ),
	  'googlevideo-height' => intval( $_POST[ 'googlevideo-height' ] ),
	  'metacafe-width' => intval( $_POST[ 'metacafe-width' ] ),
	  'metacafe-height' => intval( $_POST[ 'metacafe-height' ] ),
	  'myvideo-width' => intval( $_POST[ 'myvideo-width' ] ),
	  'myvideo-height' => intval( $_POST[ 'myvideo-height' ] ),
	  'yahoo-width' => intval( $_POST[ 'yahoo-width' ] ),
	  'yahoo-height' => intval( $_POST[ 'yahoo-height' ] ),
	  'liveleak-width' => intval( $_POST[ 'liveleak-width' ] ),
	  'liveleak-height' => intval( $_POST[ 'liveleak-height' ] ),
	  'myspace-width' => intval( $_POST[ 'myspace-width' ] ),
	  'myspace-height' => intval( $_POST[ 'myspace-height' ] ),
	  'revver-width' => intval( $_POST[ 'revver-width' ] ),
	  'revver-height' => intval( $_POST[ 'revver-height' ] ),
	  'vimeo-width' => intval( $_POST[ 'vimeo-width' ] ),
	  'vimeo-height' => intval( $_POST[ 'vimeo-height' ] ),
	  'guba-width' => intval( $_POST[ 'guba-width' ] ),
	  'guba-height' => intval( $_POST[ 'guba-height' ] ),
	  'gamevideos-width' => intval( $_POST[ 'gamevideos-width' ] ),
	  'gamevideos-height' => intval( $_POST[ 'gamevideos-height' ] ),
	  'gametrailers-width' => intval( $_POST[ 'gametrailers-width' ] ),
	  'gametrailers-height' => intval( $_POST[ 'gametrailers-height' ] ),
	  'tudou-width' => intval( $_POST[ 'tudou-width' ] ),
	  'tudou-height' => intval( $_POST[ 'tudou-height' ] ),
	  'collegehumor-width' => intval( $_POST[ 'collegehumor-width' ] ),
	  'collegehumor-height' => intval( $_POST[ 'collegehumor-height' ] ),
	  'min5-width' => intval( $_POST[ 'min5-width' ] ),
	  'min5-height' => intval( $_POST[ 'min5-height' ] ),
	  'author-credit' => (string) $_POST[ 'author-credit' ]
    )
  );
  
 
}
?>
<style>
.boldy{
  font-weight:bold;
  }
.smally{
  font-size:0.7em;
  color:gray;
}

.bordy{
  border:solid 1px Orange;
}
 

</style>
<h2>Easy Video Embed v<?php print( $this->version ); ?> - <?php _e( 'Settings' ) ?></h2>
<div>
<strong><a href="http://www.bbpressplugin.com/blog/easy-video-embed-bbpress-video-plugin" target="_blank">Plugin Homepage</a></strong>
</div>


<form method="post" action="">
<p>
<h4>Change Video Size</h4>
<label for="vsize"><span style="color:red;">- Enter only numbers, without the 'px'.<br />- You can change video size for every website video source listed below.<br />- More videos and features will be added on the next version.</span></label><br /><br />


Credit Author: <input type="checkbox" name="author-credit" <?php print( $this->eve_options[ 'author-credit' ] == 'on' ? ' checked="checked"' : '' ); ?> /><br /><br />
<span class="boldy">YouTube Video Size:</span><br />
Width: <input type="text" name="youtube-width" class="bordy" value="<?php print( $this->eve_options['youtube-width' ]); ?>" /> Height: <input type="text" name="youtube-height" class="bordy" value="<?php print( $this->eve_options['youtube-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.youtube.com/" target="_blank">YouTube</a> | Embed like this: [video]http://www.youtube.com/watch?v=1shNS-udcGQ[/video]</span>

<br /><br />

<span class="boldy">Glumbert Video Size:</span><br />
Width: <input type="text" name="glumbert-width" class="bordy" value="<?php print( $this->eve_options['glumbert-width' ]); ?>" /> Height: <input type="text" name="glumbert-height" class="bordy" value="<?php print( $this->eve_options['glumbert-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.glumbert.com/" target="_blank">Glumbert</a> | Embed like this: [video]http://www.glumbert.com/media/newmooninaminute[/video]</span>

<br /><br />
<span class="boldy">Google-Videos Video Size:</span><br />
Width: <input type="text" name="googlevideo-width" class="bordy" value="<?php print( $this->eve_options['googlevideo-width' ]); ?>" /> Height: <input type="text" name="googlevideo-height" class="bordy" value="<?php print( $this->eve_options['googlevideo-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://video.google.com/" target="_blank">Google Videos</a> | Embed like this: [video]http://video.google.it/videoplay?docid=4177639740570119244[/video]</span>

<br /><br />
<span class="boldy">Metacafe Video Size:</span><br />
Width: <input type="text" name="metacafe-width" class="bordy" value="<?php print( $this->eve_options['metacafe-width' ]); ?>" /> Height: <input type="text" name="metacafe-height" class="bordy" value="<?php print( $this->eve_options['metacafe-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.metacafe.com/" target="_blank">Metacafe</a> | Embed like this: [video]http://www.metacafe.com/watch/4401097/touching_home_movie_trailer/[/video]</span>

<br /><br />
<span class="boldy">MyVideo.AT Video Size:</span><br />
Width: <input type="text" name="myvideo-width" class="bordy" value="<?php print( $this->eve_options['myvideo-width' ]); ?>" /> Height: <input type="text" name="myvideo-height" class="bordy" value="<?php print( $this->eve_options['myvideo-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.myvideo.at/" target="_blank">Myvideo.at</a> | Embed like this: [video]http://www.myvideo.at/watch/7446478[/video]</span>

<br /><br />
<span class="boldy">Yahoo!-Video Video Size:</span><br />
Width: <input type="text" name="yahoo-width" class="bordy" value="<?php print( $this->eve_options['yahoo-width' ]); ?>" /> Height: <input type="text" name="yahoo-height" class="bordy" value="<?php print( $this->eve_options['yahoo-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://video.yahoo.com/" target="_blank">Yahoo! Video</a> | Embed like this: [video]http://video.yahoo.com/watch/123932/630275[/video]</span>

<br /><br />
<span class="boldy">LiveLeak Video Size:</span><br />
Width: <input type="text" name="liveleak-width" class="bordy" value="<?php print( $this->eve_options['liveleak-width' ]); ?>" /> Height: <input type="text" name="liveleak-height" class="bordy" value="<?php print( $this->eve_options['liveleak-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.liveleak.com/" target="_blank">LiveLeak</a> | Embed like this: [video]http://www.liveleak.com/view?i=621_1270344144[/video]</span>

<br /><br />
<span class="boldy">Myspace Video Size:</span><br />
Width: <input type="text" name="myspace-width" class="bordy" value="<?php print( $this->eve_options['myspace-width' ]); ?>" /> Height: <input type="text" name="myspace-height" class="bordy" value="<?php print( $this->eve_options['myspace-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://vids.myspace.com/" target="_blank">Myspace Vids</a> | Embed like this: [video]http://vids.myspace.com/index.cfm?fuseaction=vids.individual&videoid=100010550[/video]</span>

<br /><br />
<span class="boldy">Revver Video Size:</span><br />
Width: <input type="text" name="revver-width" class="bordy" value="<?php print( $this->eve_options['revver-width' ]); ?>" /> Height: <input type="text" name="revver-height" class="bordy" value="<?php print( $this->eve_options['revver-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.revver.com/" target="_blank">Revver</a> | Embed like this: [video]http://www.revver.com/video/62077/how-to-talk-like-a-pirate/[/video]</span>

<br /><br />
<span class="boldy">Vimeo Video Size:</span><br />
Width: <input type="text" name="vimeo-width" class="bordy" value="<?php print( $this->eve_options['vimeo-width' ]); ?>" /> Height: <input type="text" name="vimeo-height" class="bordy" value="<?php print( $this->eve_options['vimeo-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.vimeo.com/" target="_blank">Vimeo</a> | Embed like this: [video]http://www.vimeo.com/10655199[/video]</span>

<br /><br />
<span class="boldy">Guba Video Size:</span><br />
Width: <input type="text" name="guba-width" class="bordy" value="<?php print( $this->eve_options['guba-width' ]); ?>" /> Height: <input type="text" name="guba-height" class="bordy" value="<?php print( $this->eve_options['guba-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.guba.com/" target="_blank">Guba</a> | Embed like this: [video]http://www.guba.com/watch/3000690702/Trololo-Cat-Awesometown[/video]</span>

<br /><br />
<span class="boldy">GameVideos Video Size:</span><br />
Width: <input type="text" name="gamevideos-width" class="bordy" value="<?php print( $this->eve_options['gamevideos-width' ]); ?>" /> Height: <input type="text" name="gamevideos-height" class="bordy" value="<?php print( $this->eve_options['gamevideos-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://gamevideos.1up.com/" target="_blank">GameVideos</a> | Embed like this: [video]http://gamevideos.1up.com/video/id/28757[/video]</span>

<br /><br />
<span class="boldy">GameTrailers Video Size:</span><br />
Width: <input type="text" name="gametrailers-width" class="bordy" value="<?php print( $this->eve_options['gametrailers-width' ]); ?>" /> Height: <input type="text" name="gametrailers-height" class="bordy" value="<?php print( $this->eve_options['gametrailers-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.gametrailers.com/" target="_blank">GameTrailers</a> | Embed like this: [video]http://www.gametrailers.com/video/campaign-walkthrough-crackdown-2/64097[/video]</span>

<br /><br />
<span class="boldy">Tudou Video Size:</span><br />
Width: <input type="text" name="tudou-width" class="bordy" value="<?php print( $this->eve_options['tudou-width' ]); ?>" /> Height: <input type="text" name="tudou-height" class="bordy" value="<?php print( $this->eve_options['tudou-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.tudou.com/" target="_blank">Tudou</a> | Embed like this: [video]http://www.tudou.com/programs/view/-rFAmBat9D4/[/video]</span>

<br /><br />
<span class="boldy">Collegehumor Video Size:</span><br />
Width: <input type="text" name="collegehumor-width" class="bordy" value="<?php print( $this->eve_options['collegehumor-width' ]); ?>" /> Height: <input type="text" name="collegehumor-height" class="bordy" value="<?php print( $this->eve_options['collegehumor-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.collegehumor.com/" target="_blank">Collegehumor</a> | Embed like this: [video]http://www.collegehumor.com/video:1931612[/video]</span>

<br /><br />
<span class="boldy">5min Video Size:</span><br />
Width: <input type="text" name="min5-width" class="bordy" value="<?php print( $this->eve_options['min5-width' ]); ?>" /> Height: <input type="text" name="min5-height" class="bordy" value="<?php print( $this->eve_options['min5-height' ]); ?>" /><br />
<span class="smally">site: <a href="http://www.5min.com/" target="_blank">5min</a> | Embed like this: [video]http://www.5min.com/Video/Learn-About-Easter-38363995[/video]</span>


</p>
<br /><br /><br />
<p class="submit"><input type="submit" name="cmd" value="Save" /></p>

</form> 



<div align="center"> 
<hr />
&copy; 2010 <a href="easy-video-embed-bbpress-video-plugin">bbpressplugin.com</a>
<strong></strong><br /><br />
</div>