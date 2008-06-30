<?php
/*
Plugin Name: BBVideo
Plugin URI: http://www.naden.de/blog/bbvideo-bbpress-video-plugin
Description: <strong>English:</strong> Converts Video-Links in your forum posts to embedded Video-Players.<br /><strong>Deutsch:</strong> Wandelt Video-Links in den Forenbeitr&auml;gen in Video-Player um.<br /><em>Supports: Youtube, Dailymotion, MyVideo Google Video and many <a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin#video-provider" target="_blank">more ...</a></em>
Author: Naden Badalgogtapeh
Author URI: http://www.naden.de/blog
Version: 0.24
*/

/**
 * class BBPressBBVideo
 */
class BBPressPluginBBVideo
{
  /**
   * list of supported video providers
   */
  var $provider;
  
  /**
   * plugin version
   */
  var $version;
  
  /**
   * plugin options
   */
  var $options;

  /**
   * plugin id for filter hooks
   */
  var $wp_filter_id;

  /**
  * global index for embedding
  */
  var $index;
  
  /**
   * public constructor
   */
  function BBPressPluginBBVideo()
  {
    $this->version = '0.24';
    
    $this->wp_filter_id = 'bbvideo';

	  $this->index = 0;

    $this->options = bb_get_option( 'bbvideo_options' );

    if( is_null( $this->options ) )
    {
      $this->SaveOptions( array( 'embed' => 1 ), false );
    }

    if( !array_key_exists( 'download', $this->options ) )
    {
      $this->options[ 'download' ] = 1;

      $this->SaveOptions( $this->options, false );
    }
    
    $this->provider = array();
    
    // include list of video providers
    if( @include_once( dirname( __FILE__ ) . '/provider.inc.php' ) )
    {
      // content filter only on topics
      if( is_topic() )
      {
        // header stuff
        add_action( 'bb_head', array( &$this, 'AddHeader' ) );
        // add content filter
        add_filter( 'post_text', array( &$this, 'ContentFilter' ), 9 );
      }
      else
      {
        /// add admin panel
        add_action( 'bb_admin_menu_generator', array( &$this, 'AddAdminPage' ) );
      }
    }
  }
  
  function SaveOptions( $options, $verbose = true )
  {
    foreach( $options as $key => $value )
    {
      $this->options[ $key ] = $value;
    }

    bb_update_option( 'bbvideo_options', $this->options );
    
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
  
  function AddHeader()
  {
    if( $this->options[ 'embed' ] == 1 )
    {
      $code = <<<DATA
      <!-- bbVideo Plugin v{$this->version} - http://www.naden.de/blog/bbvideo-bbpress-video-plugin -->
      <script type="text/javascript">
      function bbvideo_embed( sender ) {
        var area = document.getElementById( sender.id + 'embed' );
        if( area ) {
          if( area.style.display == 'normal' || area.style.display == 'block' ) {
            sender.innerHTML = sender.innerHTML.replace( /-/, '+' );
            area.style.display = 'none';
          }
          else {
            sender.innerHTML = sender.innerHTML.replace( /\+/, '-' );
            area.style.display = 'block';
          }
        }
        return( false );
      }
      </script>
      <!-- // bbVideo Plugin -->
DATA;
    
      print( $code );
    
      unset( $code );
    }
  }

  function AddAdminPage()
  {
	  global $bb_menu;

	  $bb_menu[ 60 ] = array( __( 'bbVideo' ), 'use_keys', 'BBPressPluginBBVideoAdminPage' );
  }

  function DisplayProvider( $mask = '<small><a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin#video-provider">%s</a></small>', $pre = '<p>', $post = '</p>' )
  {
    printf( $pre . $mask . $post, implode( ', ', array_keys( $this->provider ) ) );
  }
 
  function GetTitle( $s )
  {
    $magic = 0;
    
    for( $k=0; $k<strlen( $s ); $k++ )
    {
      $magic += ord( $s[ $k ] );
    }

    $titles = array( 'video plugin', 'video plugins', 'video widget', 'video player', 'flash video', 'flash videos' );

    return( @ucwords( $titles[ $magic % count( $titles ) ] ) );
  }

  function ContentFilter( $buffer )
  {
	  /// detect all links in the current topic
    @preg_match_all( '|http([s]?)\://(.*)|i', $buffer, $links, PREG_PATTERN_ORDER );
    
    $count = count( $links[ 0 ] );
    
    foreach( $links[ 0 ] as $link )
    {
      foreach( $this->provider as $k => $provider )
      {
        @preg_match( '|' . $provider[ 'pattern' ] . '|i', $link, $matches );
        
        if( count( $matches ) > 0 )
        {
          /// build embed-code
          $code = str_replace( 
            array( '[ID]', '[HEIGHT]', '[WIDTH]' ), 
            array( $matches[ $provider[ 'index' ] ], $provider[ 'height' ], $provider[ 'width' ] ),
            $provider[ 'code' ]
          );
          
          $url = sprintf( '<a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin" style="color:#aaa;text-decoration:underline;">%s</a>', $this->GetTitle( get_topic_title() ) );
          
          if( $this->options[ 'download' ] == 1 )
          {
            $download = sprintf( '<a style="color: #000;" href="http://www.degrab.de/?url=%s" target="_blank">Video Download</a>', urlencode( $link ) );
          }            

          if( $this->options[ 'embed' ] == 1 )
          {

            $post_title = str_replace( "'", '', get_topic_title() );
            
            $post_link = get_topic_link();
            
            if( $bb->mod_rewrite )
            {
              list( $post_link, ) = explode( '?', get_topic_link() );
            }

            if( isset( $download ) )
            {
              $download = ' | ' . $download;
            }            

            $code = <<<DATA
            <!-- bbVideo Plugin v{$this->version} - http://www.naden.de/blog/bbvideo-bbpress-video-plugin -->
            <div style="width:{$provider[ 'width' ]}px;">{$code}<div>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr><td><a href="" id="bbvideo{$this->index}" onclick="javascript:return(bbvideo_embed(this));" style="color: #000;">[+] Embed the video</a>{$download}</td><td align="right" style="color:#aaa;font-size:80%;">Get the {$url}</td></tr></table>
  
            <div id="bbvideo{$this->index}embed" style="display:none;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr><td width="80">Text-Link:</td><td><input type="text" value="{$post_link}" onclick="javascript:this.focus();this.select();" style="width:100%;" /></td></tr>
            <tr><td width="80">HTML-Link:</td><td><input type="text" value='<a href="{$post_link}">{$post_title}</a>' onclick="javascript:this.focus();this.select();" style="width:100%;" /></td></tr>
            <tr><td width="80">BB-Code:</td><td><input type="text" value="[url={$post_link}]{$post_title}[/url]" onclick="javascript:this.focus();this.select();" style="width:100%;" /></td></tr>
            <tr><td width="80">Embed:</td><td><input type="text" value='<div style="width:{$provider[ 'width' ]}px;">{$code}<div align="right" style="color:#aaa;font-size:80%;">Get the {$url}</div></div>' onclick="javascript:this.focus();this.select();" style="width:100%;" /></td></tr>
            </table>
            </div></div></div>
            <!-- // bbVideo Plugin -->
DATA;
          }
          else
          {
            if( isset( $download ) )
            {
              $download = '<td>' . $download . '</td>';
            }

            $code = <<<DATA
            <div style="width:{$provider[ 'width' ]}px;">{$code}
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>{$download}<td align="right" style="color:#aaa;font-size:80%;">Get the {$url}</td></tr>
            </table>
            </div>
DATA;
          } 

          /// replace link w/ embed-code
          $buffer = str_replace( 
            $link,
            $code, 
            $buffer
          );
          
          unset( $code );
          
          break;
        }
        
      }
	  
	  $this->index ++;

      reset( $this->provider );
    }
    
    return( $buffer );  
  }
  
} // end class BBPressPluginBBVideo


/**
* dirty hack, because it seems to be impossible to call an class method over uri
*/
function BBPressPluginBBVideoAdminPage( )
{
  global $BBPressPluginBBVideo;

  $BBPressPluginBBVideo->AdminPage();

} /// end func


/// init plugin
if( !isset( $BBPressPluginBBVideo ) || is_null( $BBPressPluginBBVideo ) )
{
  $BBPressPluginBBVideo = new BBPressPluginBBVideo();
}

?>
