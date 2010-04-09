<?php
/*
Plugin Name: Easy Video Embed
Plugin URI: http://www.bbpressplugin.com/easy-video-embed-plugin/
Description: Easily embed Videos from various tube like sites in BBPress posts using video BBCode tags
Author: Idan Shechter
Author URI: http://www.bbpressplugin.com
Version: 0.1b
*/


	
	class BBPressPluginEasyVideoEmbed {

 
  // plugin version   
  var $version;
  
  // plugin options  
  var $eve_options;
  
  // plugin id for filter hooks 
  var $wp_filter_id;
  
  // global index for embedding 
  var $index;
  
  //
  // constructor
  function BBPressPluginEasyVideoEmbed(){
    
	
    $this->version = '0.1';    
    $this->wp_filter_id = 'easyvideoembed';
	$this->index = 0;
    
    $this->eve_options = bb_get_option( 'easyvideoembed_options' );
  
    if( is_null( $this->eve_options ) )
    {
      $this->SaveOptions( array( 'youtube-width' => 450, 'youtube-height' => 366, 'glumbert-width' => 448, 'glumbert-height' => 336, 'googlevideo-width' => 400, 'metacafe-width' => 400, 'metacafe-height' => 345,  'myvideo-width' => 470,  'myvideo-height' => 285, 'yahoo-width' => 512, 'yahoo-height' => 322, 'liveleak-width' => 450, 'liveleak-height' => 370, 'myspace-width' => 425, 'myspace-height' => 360, 'revver-width' => 480, 'revver-height' => 392, 'vimeo-width' => 400, 'vimeo-height' => 225, 'guba-width' => 375, 'guba-height' => 360, 'gamevideos-width' => 486, 'gamevideos-height' => 412,   'gametrailers-width' => 480, 'gametrailers-height' => 392, 'tudou-width' => 520, 'tudou-height' => 450, 'collegehumor-width' => 480,  'collegehumor-height' => 360, 'min5-width' => 560,  'min5-height' => 345, 'author-credit' => 'on'),  false );
	 
    }
	//
	
	
	 if( !array_key_exists( 'youtube-width', $this->eve_options ) ) {	
      $this->eve_options[ 'youtube-width' ] = 450;
	 }
	 if( !array_key_exists( 'youtube-height', $this->eve_options ) ) {
	  $this->eve_options[ 'youtube-height' ] = 366;
	 }
	 if( !array_key_exists( 'glumbert-height', $this->eve_options ) ) {
	   $this->eve_options[ 'glumbert-height' ] = 336;
	 }
	 if( !array_key_exists( 'glumbert-width', $this->eve_options ) ) {
	   $this->eve_options[ 'glumbert-width' ] = 448;
	 }
	 if( !array_key_exists( 'googlevideo-width', $this->eve_options ) ) {
	   $this->eve_options[ 'googlevideo-width' ] = 400;
	 }
	 if( !array_key_exists( 'googlevideo-height', $this->eve_options ) ) {
	  $this->eve_options[ 'googlevideo-height' ] = 326;
	 }
	 if( !array_key_exists( 'metacafe-width', $this->eve_options ) ) {
	   $this->eve_options[ 'metacafe-width' ] = 400;
	 }
	 if( !array_key_exists( 'metacafe-height', $this->eve_options ) ) {
	   $this->eve_options[ 'metacafe-height' ] = 345;
	 }
	 if( !array_key_exists( 'myvideo-width', $this->eve_options ) ) {
	   $this->eve_options[ 'myvideo-width' ] = 470;
	 }
	 if( !array_key_exists( 'myvideo-height', $this->eve_options ) ) {
	   $this->eve_options[ 'myvideo-height' ] = 285;
	 }
	 if( !array_key_exists( 'yahoo-width', $this->eve_options ) ) {
	  $this->eve_options[ 'yahoo-width' ] = 512;
	 }
	 if( !array_key_exists( 'yahoo-height', $this->eve_options ) ) {
	   $this->eve_options[ 'yahoo-height' ] = 322;
	 }
	 if( !array_key_exists( 'liveleak-width', $this->eve_options ) ) {
	   $this->eve_options[ 'liveleak-width' ] = 450;
	 }
	 if( !array_key_exists( 'liveleak-height', $this->eve_options ) ) {
	   $this->eve_options[ 'liveleak-height' ] = 370;
	 }
	 if( !array_key_exists( 'myspace-width', $this->eve_options ) ) {
	   $this->eve_options[ 'myspace-width' ] = 425;
	 }
	 if( !array_key_exists( 'myspace-height', $this->eve_options ) ) {
	   $this->eve_options[ 'myspace-height' ] = 360;
	 }
     if( !array_key_exists( 'revver-width', $this->eve_options ) ) {
	   $this->eve_options[ 'revver-width' ] = 480;
	 }
	 if( !array_key_exists( 'revver-height', $this->eve_options ) ) {
	   $this->eve_options[ 'revver-height' ] = 392;
	 }
	 if( !array_key_exists( 'vimeo-width', $this->eve_options ) ) {
	  $this->eve_options[ 'vimeo-width' ] = 400;
	 }
	 if( !array_key_exists( 'vimeo-height', $this->eve_options ) ) {
	  $this->eve_options[ 'vimeo-height' ] = 225;
	 }
	 if( !array_key_exists( 'guba-width', $this->eve_options ) ) {
	  $this->eve_options[ 'guba-width' ] = 375;
	 }
	 if( !array_key_exists( 'guba-height', $this->eve_options ) ) {
	  $this->eve_options[ 'guba-height' ] = 360;
	 }
	 if( !array_key_exists( 'gamevideos-width', $this->eve_options ) ) {
	  $this->eve_options[ 'gamevideos-width' ] = 486;
	 }
	 if( !array_key_exists( 'gamevideos-height', $this->eve_options ) ) {
	  $this->eve_options[ 'gamevideos-height' ] = 412;
	 }
	 if( !array_key_exists( 'gametrailers-width', $this->eve_options ) ) {
	   $this->eve_options[ 'gametrailers-width' ] = 480;
	 }
	 if( !array_key_exists( 'gametrailers-height', $this->eve_options ) ) {
	   $this->eve_options[ 'gametrailers-height' ] =392;
	 }
	 if( !array_key_exists( 'tudou-width', $this->eve_options ) ) {
	    $this->eve_options[ 'tudou-width' ] = 520;
	 }
	 if( !array_key_exists( 'tudou-height', $this->eve_options ) ) {
	    $this->eve_options[ 'tudou-height' ] = 450;
	 }
	 if( !array_key_exists( 'collegehumor-width', $this->eve_options ) ) {
	   $this->eve_options[ 'collegehumor-width' ] = 480;
	 }
	 if( !array_key_exists( 'collegehumor-height', $this->eve_options ) ) {
	  $this->eve_options[ 'collegehumor-height' ] = 360;
	 }
	 if( !array_key_exists( 'min5-width', $this->eve_options ) ) {
	  $this->eve_options[ 'min5-width' ] = 560;
	 }
	 if( !array_key_exists( 'min5-height', $this->eve_options ) ) {
	   $this->eve_options[ 'min5-height' ] = 345;
	 }
	 if( !array_key_exists( 'author-credit', $this->eve_options ) ) {
	   $this->eve_options[ 'author-credit' ] = 'on';
	 }
	
    	 
    
	   
	  
	 
    
	
	// content filter only on topics
      if( is_topic() )
      {
        // header stuff
        //add_action( 'bb_head', array( &$this, 'AddHeader' ) );
        // add content filter
       
		add_filter('post_text', 'ReplaceVideoBBCode');
		add_action('bb_foot', 'AddAuthorCreditInline');
		
      }
      else
      {
        /// add admin panel
        add_action( 'bb_admin_menu_generator', array( &$this, 'AddAdminPage' ) );
      }
	  
	  
	
  }
  
  
   function AddAdminPage()
  {
	  global $bb_menu;

	  $bb_menu[ 70 ] = array( __( 'easyVideoEmbed' ), 'use_keys', 'EasyVideoEmbedAdminPage' );
  }
  
   function SaveOptions( $eve_options, $verbose = true )
	  {
		foreach( $eve_options as $key => $value )
		{
		  $this->eve_options[ $key ] = $value;
		  
		}
        
		bb_update_option( 'easyvideoembed_options', $this->eve_options );
		
		if( $verbose )
		{
		  print( __( '<div class="updated">Settings Saved</div>' ) );
		}
	  }
	
	   function AdminPage()
	  {
		  global $bbdb, $bb, $bb_table_prefix;
         
		@include_once( dirname( __FILE__ ) . '/admin.php' );
	  }
	  
	  function ReturnPostTextAfterVideoEmbed($post_text)
	  {
	   
   
      
	
	 
	      // --------- YOUTUBE --------------------------------
   //http://www.youtube.com/watch?v=1shNS-udcGQ
   
	 
   $post_text = preg_replace('#\[video\]http:\/\/([a-zA-Z0-9]+\.|)youtube\.com\/.*v=([a-zA-Z0-9_-]+).*\[/video\]#i', '<object type="application/x-shockwave-flash" style="width:'.((string)$this->eve_options['youtube-width']).'px; height:'.((string)$this->eve_options['youtube-height']).'px;" data="http://www.youtube.com/v/\\2"><param name="movie" value="http://www.youtube.com/v/\\1" /><param name="wmode" value="transparent" /></object>',$post_text); 
   
    // --------- GLUMBERT --------------------------------
    //http://www.glumbert.com/media/newmooninaminute
   $post_text = preg_replace('#\[video\]http:\/\/([a-zA-Z0-9]+\.|)glumbert\.com\/media\/([a-zA-Z0-9]+)\[/video\]#i', '<object width="'.((string)$this->eve_options['glumbert-width']).'" height="'.((string)$this->eve_options['glumbert-height']).'"><param name="movie" value="http://www.glumbert.com/embed/\\2"></param><param name="wmode" value="opaque"><param name="allowFullScreen" value="true" /></param><embed src="http://www.glumbert.com/embed/\\2" type="application/x-shockwave-flash" wmode="transparent" allowFullScreen="true" width="'.((string)$this->eve_options['glumbert-width']).'" height="'.((string)$this->eve_options['glumbert-height']).'"></embed></object>',$post_text);
   
      
    // --------- GOOGLE VIDEO --------------------------------
    //http://video.google.it/videoplay?docid=4177639740570119244
   $post_text = preg_replace('#\[video\]http://video\.google\.(.+).+docid=([a-zA-Z0-9-]+).\[/video\]#i', '<embed id="VideoPlayback" style="width:'.((string)$this->eve_options['googlevideo-width']).'px;height:'.((string)$this->eve_options['googlevideo-height']).'px" allowFullScreen="true" flashvars="fs=true" src="http://video.google.com/googleplayer.swf?docid=\\2" type="application/x-shockwave-flash"></embed>',$post_text); 

	// --------- METACAFE --------------------------------
	//http://www.metacafe.com/watch/4401097/touching_home_movie_trailer/
	 $post_text = preg_replace('#\[video\]http:\/\/([a-zA-Z0-9]+\.|)metacafe\.com\/watch\/(.*)\/\[/video\]#i', '<embed src="http://www.metacafe.com/fplayer/\\2.swf" width="'.((string)$this->eve_options['metacafe-width']).'" height="'.((string)$this->eve_options['metacafe-height']).'" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>',$post_text); 
	 	
	
	// --------- MYVIDEO.AT --------------------------------
	//http://www.myvideo.at/watch/7446478
	 $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)myvideo.(.+)/watch/([a-zA-Z0-9-]+)\[/video\]#i', '<object style="width:'.((string)$this->eve_options['myvideo-width']).'px;height:'.((string)$this->eve_options['myvideo-width']).'px;" width="470" height="285"><param name="movie" value="http://www.myvideo.at/movie/\\2"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param><embed src="http://www.myvideo.at/movie/\\2" width="470" height="285" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>',$post_text); 
	 
  
	// --------- Yahoo! videos --------------------------------
	//http://video.yahoo.com/watch/123932/630275
	 $post_text = preg_replace('#\[video\]http://video\.yahoo\.com/watch/([0-9]+)/([0-9]+)\[/video\]#i', '<object width="'.((string)$this->eve_options['yahoo-width']).'" height="'.((string)$this->eve_options['yahoo-height']).'"><param name="movie" value="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" VALUE="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="id=\\2&vid=\\1&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/v/v0/w363/\\1_320_240.jpeg&embed=1" /><embed src="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" type="application/x-shockwave-flash" width="'.((string)$this->eve_options['yahoo-width']).'" height="'.((string)$this->eve_options['yahoo-height']).'" allowFullScreen="true" AllowScriptAccess="always" bgcolor="#000000" flashVars="id=\\2&vid=\\1&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/v/v0/w363/123932_320_240.jpeg&embed=1" ></embed></object>',$post_text); 
	 
	 		 
		 
    // --------- Myspace --------------------------------
	//http://vids.myspace.com/index.cfm?fuseaction=vids.individual&videoid=100010550
	   $post_text = preg_replace('#\[video\]http://vids\.myspace\.com.*?videoID=([^&]*)\[/video\]#i', '<object width="'.((string)$this->eve_options['myspace-width']).'px" height="'.((string)$this->eve_options['myspace-height']).'px" ><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m=\\1,t=1,mt=video"/><embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m=\\1,t=1,mt=video" width="'.((string)$this->eve_options['myspace-width']).'" height="'.((string)$this->eve_options['myspace-height']).'" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent"></embed></object>',$post_text); 
	   

	 // --------- LiveLeak.com --------------------------------
	 //http://www.liveleak.com/view?i=621_1270344144
	   $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)liveleak\.com/view.*i=([a-zA-Z0-9_]+)\[/video\]#i', '<object width="'.((string)$this->eve_options['liveleak-width']).'" height="'.((string)$this->eve_options['liveleak-height']).'"><param name="movie" value="http://www.liveleak.com/e/\\2"></param><param name="wmode" value="transparent"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.liveleak.com/e/\\2" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" width="'.((string)$this->eve_options['liveleak-width']).'" height="'.((string)$this->eve_options['liveleak-height']).'"></embed></object>',$post_text); 
	   
	 
	 // --------- Revver.com --------------------------------
	 //http://www.revver.com/video/62077/how-to-talk-like-a-pirate/
	   $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)revver\.com\/video\/(.*?)\/(.*?)\/\[/video\]#i', '<script src="http://flash.revver.com/player/1.0/player.js?mediaId:\\2;width:'.((string)$this->eve_options['revver-width']).';height:'.((string)$this->eve_options['revver-height']).';" type="text/javascript"></script>',$post_text); 
	   
	   
	// --------- Vimeo --------------------------------	
	//http://www.vimeo.com/10655199
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)vimeo\.com.*[/=:]([0-9]+)\[/video\]#i', '<object width="'.((string)$this->eve_options['vimeo-width']).'" height="'.((string)$this->eve_options['vimeo-height']).'"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=\\2&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=\\2&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="'.((string)$this->eve_options['vimeo-width']).'" height="'.((string)$this->eve_options['vimeo-height']).'"></embed></object>',$post_text); 
	  
	// --------- Guba --------------------------------	
	//http://www.guba.com/watch/3000690702/Trololo-Cat-Awesometown
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)guba.com\/watch\/([a-zA-Z0-9]+)\/(.*)\[/video\]#i', '<embed src="http://www.guba.com/static/f/player__v12735.swf?isEmbeddedPlayer=true&bid=\\2" quality="best" bgcolor="#FFFFFF" menu="true" width="'.((string)$this->eve_options['guba-width']).'px" height="'.((string)$this->eve_options['guba-height']).'px" name="root" id="root" align="middle" scaleMode="noScale" allowScriptAccess="never" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>',$post_text); 
	  
	  
	// --------- GameVideos --------------------------------	
	//http://gamevideos.1up.com/video/id/28757
	  $post_text = preg_replace('#\[video\]http://gamevideos\.1up\.com/video/id/([0-9]+)\[/video\]#i',  '<object id="flashObj" width="'.((string)$this->eve_options['gamevideos-width']).'" height="'.((string)$this->eve_options['gamevideos-height']).'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,47,0"><param name="movie" value="http://gamevideos.1up.com/swf/gamevideos11.swf?embedded=1&amp;fullscreen=1&amp;autoplay=0&amp;src=http://gamevideos.1up.com/video/videoListXML?id=\\1&amp;adPlay=true" /><param name="bgcolor" value="#FFFFFF" /><param name="flashVars" value="videoId=75828060001&linkBaseURL=http%3A%2F%2Fgamevideos.1up.com%2Fvideo%2Fid%2F28757&playerID=22881388001&domain=embed&" /><param name="base" value="http://admin.brightcove.com" /><param name="seamlesstabbing" value="false" /><param name="allowFullScreen" value="true" /><param name="swLiveConnect" value="true" /><param name="allowScriptAccess" value="always" /><embed src="http://gamevideos.1up.com/swf/gamevideos11.swf?embedded=1&amp;fullscreen=1&amp;autoplay=0&amp;src=http://gamevideos.1up.com/video/videoListXML?id=\\1&amp;adPlay=true" bgcolor="#FFFFFF" flashVars="embedded=1&amp;fullscreen=1&amp;autoplay=0&amp;src=http://gamevideos.1up.com/video/videoListXML?id=\\1&amp;adPlay=true" base="http://admin.brightcove.com" name="flashObj" width="'.((string)$this->eve_options['gamevideos-width']).'" height="'.((string)$this->eve_options['gamevideos-height']).'" seamlesstabbing="false" type="application/x-shockwave-flash" allowFullScreen="true" swLiveConnect="true" allowScriptAccess="always" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed></object>',$post_text); 

	  
	    
	// --------- GameTrailers --------------------------------	
	//http://www.gametrailers.com/video/campaign-walkthrough-crackdown-2/64097
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)gametrailers\.com\/video\/(.*)\/(.*)\[/video\]#i', '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="gtembed" width="'.((string)$this->eve_options['gametrailers-width']).'" height="'.((string)$this->eve_options['gametrailers-height']).'">	<param name="allowScriptAccess" value="sameDomain" /> <param name="allowFullScreen" value="true" /> <param name="movie" value="http://www.gametrailers.com/remote_wrap.php?mid=\\3"/><param name="quality" value="high" /> <embed src="http://www.gametrailers.com/remote_wrap.php?mid=\\3" swLiveConnect="true" name="gtembed" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.((string)$this->eve_options['gametrailers-width']).'" height="'.((string)$this->eve_options['gametrailers-height']).'"></embed> </object>',$post_text); 
	  
	 
	  
	// --------- tudou.com single --------------------------------	
	//http://www.tudou.com/programs/view/-rFAmBat9D4/
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)tudou\.com\/programs\/view\/([a-zA-Z0-9]+)\/\[/video\]#i', '<object width="'.((string)$this->eve_options['tudou-width']).'" height="'.((string)$this->eve_options['tudou-height']).'"><param name="movie" value="http://www.tudou.com/v/\\2"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><param name="wmode" value="opaque"></param><embed src="http://www.tudou.com/v/\\2" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque" width="'.((string)$this->eve_options['tudou-width']).'" height="'.((string)$this->eve_options['tudou-height']).'"></embed></object>',$post_text); 
	  
	// --------- tudou.com playlist --------------------------------	
	//http://www.tudou.com/playlist/playindex.do?lid=8294366
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)tudou\.com\/playlist\/playindex.do\?lid=([a-zA-Z0-9]+)\[/video\]#i', '<object width="'.((string)$this->eve_options['tudou-width']).'" height="'.((string)$this->eve_options['tudou-height']).'"><param name="movie" value="http://www.tudou.com/v/\\2"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><param name="wmode" value="opaque"></param><embed src="http://www.tudou.com/v/\\2" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque" width="'.((string)$this->eve_options['tudou-width']).'" height="'.((string)$this->eve_options['tudou-height']).'"></embed></object>',$post_text);
	  
	  // --------- Collegehumor --------------------------------	
	//http://www.collegehumor.com/video:1931612
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)collegehumor\.com.+video[:=]([0-9]+)\[/video\]#i', '<object type="application/x-shockwave-flash" data="http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=\\2&fullscreen=1" width="'.((string)$this->eve_options['collegehumor-width']).'" height="'.((string)$this->eve_options['collegehumor-height']).'" ><param name="allowfullscreen" value="true"/><param name="wmode" value="transparent"/><param name="allowScriptAccess" value="always"/><param name="movie" quality="best" value="http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=\\2&fullscreen=1"/><embed src="http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=\\2&fullscreen=1" type="application/x-shockwave-flash" wmode="transparent"  width="'.((string)$this->eve_options['collegehumor-width']).'" height="'.((string)$this->eve_options['collegehumor-height']).'"  allowScriptAccess="always"></embed></object>',$post_text);
	  
	 // --------- 5min.com --------------------------------	
	//http://www.5min.com/Video/Learn-About-Easter-38363995
	  $post_text = preg_replace('#\[video\]http://([a-zA-Z0-9]+\.|)5min\.com/[vV]ideo/[a-zA-Z0-9-]+-([0-9]+)\[/video\]#i', '<object width="'.((string)$this->eve_options['min5-width']).'" height="'.((string)$this->eve_options['min5-height']).'" id="FiveminPlayer" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000">
<param name="allowfullscreen" value="true"/><param name="allowScriptAccess" value="always"/><param name="movie" value="http://embed.5min.com/38363995/"/><param name="wmode" value="window" /><embed name="FiveminPlayer" src="http://embed.5min.com/38363995/" type="application/x-shockwave-flash" width="'.((string)$this->eve_options['min5-width']).'" height="'.((string)$this->eve_options['min5-height']).'" allowfullscreen="true" allowScriptAccess="always" wmode="window"></embed></object>',$post_text);
	  
	
    return $post_text;
	  }
	
  // put here
  
}


	
	
	

  
//add_filter('post_text', 'ReplaceVideoBBCode');


function EasyVideoEmbedAdminPage( )
{
  global $BBPressPluginEasyVideoEmbed;

  $BBPressPluginEasyVideoEmbed->AdminPage();

} /// end func
  
  
function AddAuthorCreditInline(){
	echo "<p style='text-align:center;'><br />Video embedded using <a href='http://www.bbpressplugin.com/easy-video-embed-plugin/' target='_blank'>Easy Video Embed plugin</a><p>";
}

function ReplaceVideoBBCode($post_text) {

  
  
	
	//echo (string)$this->eve_options[ 'youtube-width' ];
     
	global $BBPressPluginEasyVideoEmbed;
    return $BBPressPluginEasyVideoEmbed->ReturnPostTextAfterVideoEmbed($post_text);
	//(string)$eve_options[ 'youtube-width' ];
	 
	
    }
	
	
	
/// init plugin
if( !isset( $BBPressPluginEasyVideoEmbed ) || is_null( $BBPressPluginEasyVideoEmbed ) )
{
  $BBPressPluginEasyVideoEmbed = new BBPressPluginEasyVideoEmbed();
}
?>
