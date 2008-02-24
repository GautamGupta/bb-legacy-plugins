<?php
/*
Plugin Name: BBVideo
Plugin URI: http://www.naden.de/blog/bbvideo-bbpress-video-plugin
Description: <strong>English:</strong> Converts Video-Links in your forum posts to embedded Video-Players.<br /><strong>Deutsch:</strong> Wandelt Video-Links in den Forenbeitr&auml;gen in Video-Player um.<br /><em>Supports: Youtube, Dailymotion, MyVideo Google Video and many <a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin#video-provider" target="_blank">more ...</a></em>
Author: Naden Badalgogtapeh
Author URI: http://www.naden.de/blog
Version: 0.1
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
   * public constructor
   */
  function BBPressPluginBBVideo()
  {
    $this->version = '0.1';

    // include list of video providers
    @include_once( dirname( __FILE__ ) . '/provider.inc.php' );

    // content filter only on topics
    if( is_topic() )
    {
      // add content filter
      add_filter( 'post_text', array( &$this, 'ContentFilter' ), 9 );
    }
    else
    {
      /// add admin panel
      add_action( 'bb_admin_menu_generator', array( &$this, 'AddAdminPage' ) );
    }
  }

  function AdminPage()
  {
	  global $bbdb, $bb, $bb_table_prefix;

    @include_once( dirname( __FILE__ ) . '/admin.php' );
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

  function ContentFilter( $buffer )
  {
	  /// detect all links in the current topic
    @preg_match_all( '|http([s]?)\://(.*)|i', $buffer, $links, PREG_PATTERN_ORDER );
    
    $count = count( $links[ 0 ] );
    $index = 0;
    
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
          
          $url = '<a href="http://www.naden.de/blog/bbvideo-bbpress-video-plugin" style="color:#aaa;text-decoration:underline;">Video Plugin</a>';
          
          if( $index == 0 )
          {          
            $code = "<div style=\"width:{$provider[ 'width' ]}px;\">{$code}<div align=\"right\" style=\"color:#aaa;font-size:80%;\">Get the {$url}</div></div>";
          }

          /// replace link w/ embed-code
          $buffer = str_replace( 
            $link,
            $code, 
            $buffer
          );
          
          unset( $code );
          
          $index ++;
          
          break;
        }
      }
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
