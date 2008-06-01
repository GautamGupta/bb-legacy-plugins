<?php
/*
Plugin Name: bb-Lightbox2
Plugin URI: http://bbpress.ru/
Description: Used to overlay images on the current page. Lightbox JS v2.2 by <a href="http://www.huddletogether.com/projects/lightbox2/" title="Lightbox JS v2.2 ">Lokesh Dhakar</a>. This plugin is based on "Lightbox 2" plugin for WordPress.
Version: 0.15
Author: A1ex
Author URI: http://bbpress.ru
*/

/* Select your theme: */
$lightbox_2_theme = "Black";
//$lightbox_2_theme = "Dark Grey";
//$lightbox_2_theme = "Grey";
//$lightbox_2_theme = "White";

$tumb_width  = 100;
$tumb_height = 100;

$tumbs_path = "";
$tumbs_url = "";



// STOP EDITING!!!

function bb_preg_callback2($matches) {
  return '<a href="'.$matches[2].'" rel="lightbox">'.'<img src="'.ImgTumb($matches[2],$matches[4]).'" /></a>';
}

function bb_removeLinks($content) {

  $content = preg_replace_callback('@(<img.*src="(([^>"]*/)(.*[^"]))"[^<]*>)(?!<\/a>)@i', "bb_preg_callback2", $content);
  $content = preg_replace_callback('@(<img.*src=\'(([^>\']*/)(.*[^\']))\'[^<]*>)(?!<\/a>)@i', "bb_preg_callback2", $content);
	
  return $content;
} 

function lightbox_styles() {
    /* The next lines figures out where the javascripts and images and CSS are installed,
    relative to your bbpress server's root: */
global $lightbox_2_theme;
	
    $lightbox_2_theme_path = (dirname(__FILE__)."/Themes");
    $lightbox_style = (bb_get_option('uri')."my-plugins/bb-lightbox2/Themes/".$lightbox_2_theme."/");
    $lightbox_path =  bb_get_option('uri')."my-plugins/bb-lightbox2/bb-lightbox2/";

    /* The xhtml header code needed for lightbox to work: */
	$lightboxscript = "
	<!-- begin lightbox scripts -->
	<script type=\"text/javascript\">
    //<![CDATA[
    document.write('<link rel=\"stylesheet\" href=\"".$lightbox_style."lightbox.css\" type=\"text/css\" media=\"screen\" />');
    //]]>
    </script>
	<script type=\"text/javascript\" src=\"".$lightbox_path."prototype.js\"></script>
	<script type=\"text/javascript\" src=\"".$lightbox_path."scriptaculous.js\"></script>
	<script type=\"text/javascript\" src=\"".$lightbox_path."lightbox.js\"></script>
	<!-- end lightbox scripts -->\n";
	/* Output $lightboxscript as text for our web pages: */
	echo($lightboxscript);
}

function GetImgType($image)
{
 $info = getimagesize($image);

 switch($info[2]) {
  case 1:
    return "gif";
    break;
  case 2:
    return "jpg";
    break;
  case 3:
    return "png";
    break;
  default:
    return false;
 }
}

function ImgTumb($image_path, $image_file)
{
 global $tumb_width, $tumb_height, $tumbs_path, $tumbs_url;

 if(!$tumbs_path) {
   $tumbs_path = BBPATH.'tumbs/';
 }
  if(!$tumbs_url) {
   $tumbs_url = bb_get_option('uri').'tumbs/';
 }
 $tumb_name = 'tumb_'.md5($image_path).'_'.$image_file;
 $tumb_path = $tumbs_path.$tumb_name;
 $tumb_url = $tumbs_url.$tumb_name;
 
 if(!is_file($tumb_path))
 {
  $image = $image_path;
  $this_type = GetImgType($image);
  switch($this_type)
  {
   case "gif":
     // Get new dimensions
     list($image_width, $image_height) = getimagesize($image);
     $ratio = $image_width/$image_height;
     if ($tumb_width/$tumb_height > $ratio) {
       $tumb_width = $tumb_height*$ratio;
     } else {
       $tumb_height = $tumb_width/$ratio;
     }
	 $tumb_image = imagecreatetruecolor($tumb_width, $tumb_height);
   	 $image = imagecreatefromgif($image);
     imagecopyresampled($tumb_image, $image, 0, 0, 0, 0, $tumb_width, $tumb_height, $image_width, $image_height);
     imagegif($tumb_image, $tumb_path);
     break;

   case "jpg":
     // Get new dimensions
     list($image_width, $image_height) = getimagesize($image);
     $ratio = $image_width/$image_height;
     if ($tumb_width/$tumb_height > $ratio) {
       $tumb_width = $tumb_height*$ratio;
     } else {
       $tumb_height = $tumb_width/$ratio;
     }
	 $tumb_image = imagecreatetruecolor($tumb_width, $tumb_height);
	 $image = imagecreatefromjpeg($image);
     imagecopyresampled($tumb_image, $image, 0, 0, 0, 0, $tumb_width, $tumb_height, $image_width, $image_height);
     imagejpeg($tumb_image, $tumb_path);
     break;

   case "png":
     // Get new dimensions
     list($image_width, $image_height) = getimagesize($image);
     $ratio = $image_width/$image_height;
     if ($tumb_width/$tumb_height > $ratio) {
       $tumb_width = $tumb_height*$ratio;
     } else {
       $tumb_height = $tumb_width/$ratio;
     }
     $tumb_image = imagecreatetruecolor($tumb_width, $tumb_height);
     $image = imagecreatefrompng($image);
     imagecopyresampled($tumb_image, $image, 0, 0, 0, 0, $tumb_width, $tumb_height, $image_width, $image_height);
     imagepng($tumb_image, $tumb_path);
     break;
  }
 }
 return $tumb_url;
}

add_action('post_text', 'bb_removeLinks');

/* we want to add the above xhtml to the header of our pages: */
add_action('bb_head', 'lightbox_styles');
?>