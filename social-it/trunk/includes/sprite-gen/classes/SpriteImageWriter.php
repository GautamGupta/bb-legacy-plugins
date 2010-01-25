<?php
class SpriteImageWriter{
  
  public static function writeImages(SpriteSprite &$sprite){
    $filePath = $sprite->getRelativePath();
    
    $imgSize = self::getImageSize($sprite);
   
              
    if(SpriteCache::needsCreation($filePath)){
      switch($sprite->getType()){
        case SpriteConfig::JPG :{
          SpriteImageWriter::handleJpg($sprite, $imgSize, $filePath);
          break;
        }
        case SpriteConfig::GIF :{
          SpriteImageWriter::handleGif($sprite, $imgSize, $filePath);
          break;
        }
        case SpriteConfig::PNG :{
          SpriteImageWriter::handlePng24($sprite, $imgSize, $filePath);
          break;
        }
        case SpriteConfig::PNG8 :{
          SpriteImageWriter::handlePng24($sprite, $imgSize, $filePath);
          break;
        }
      }
    }  
  }
  
  protected static function getImageSize(SpriteSprite $sprite){
    $imgSize = new SpriteRectangle(0,0,0,0);
    foreach($sprite as $spriteImage){
      $position = $spriteImage->getPosition();
      $fullLength = $position->left + $spriteImage->getWidth();
      if($imgSize->width < $fullLength) $imgSize->update(0, 0, $fullLength, $imgSize->bottom);
      $fullHeight = $position->top + $spriteImage->getHeight();
      if($imgSize->height < $fullHeight) $imgSize->update(0, 0, $imgSize->right, $fullHeight);
    }
    return $imgSize;
  }
  
  protected static function writeImageFile($image, $path){
    $path = SpriteConfig::get('rootDir').$path;        
    $fh = fopen($path, "w+" );
    fwrite( $fh, $image );
    fclose( $fh );
  }
  
  protected static function handleJpg(SpriteSprite $sprite, $imgSize, $filePath){
    $properties = SpriteConfig::get('imageProperties');
    $quality = $properties['jpgQuality'];
    
    $spriteHolder = imagecreatetruecolor($imgSize->right, $imgSize->bottom);
    foreach($sprite as $spriteImage){
      $tempImage = imagecreatefromjpeg($spriteImage->getPath());
      $position = $spriteImage->getPosition();
      $margin = $spriteImage->getMargin();
      imagecopy($spriteHolder, $tempImage, $position->left + $margin->left, $position->top + $margin->top, 0, 0, $spriteImage->getOriginalWidth(), $spriteImage->getOriginalHeight());
      imagedestroy($tempImage);
    }
    ob_start();
    imagejpeg($spriteHolder, null, $quality);
    $spriteOutput = ob_get_clean();
    imagedestroy($spriteHolder);
    SpriteImageWriter::writeImageFile($spriteOutput, $filePath);
  }
    
  protected static function handleGif(SpriteSprite $sprite, $imgSize, $filePath){
    $spriteHolder = imagecreatetruecolor($imgSize->right, $imgSize->bottom);
    imagealphablending($spriteHolder, false);
    imagesavealpha($spriteHolder,true);
    $transparent = imagecolorallocatealpha($spriteHolder, 255, 255, 255, 127);
    imagefilledrectangle($spriteHolder, 0, 0, $imgSize->right, $imgSize->bottom, $transparent);
    imagecolortransparent  ( $spriteHolder, $transparent);
    foreach($sprite as $spriteImage){
      $tempImage = imagecreatefromgif($spriteImage->getPath());      
      $position = $spriteImage->getPosition();
      $margin = $spriteImage->getMargin();
      imagecopy($spriteHolder, $tempImage, $position->left + $margin->left, $position->top + $margin->top, 0, 0, $spriteImage->getOriginalWidth(), $spriteImage->getOriginalHeight());
      imagedestroy($tempImage);
    }

    ob_start();
    imagegif($spriteHolder);
    $spriteOutput = ob_get_clean();
    imagedestroy($spriteHolder);
    SpriteImageWriter::writeImageFile($spriteOutput, $filePath);
  }
  
  protected static function handlePng24(SpriteSprite $sprite, $imgSize, $filePath){
    $spriteHolder = imagecreatetruecolor($imgSize->right, $imgSize->bottom);
    /*$trans_color = imagecolorallocatealpha($sprite, 0, 0, 0, 127);
    imagesavealpha($sprite, true);
    imagealphablending($sprite, true);
    imagefill($sprite, 0, 0, $trans_color);*/
    imagealphablending($spriteHolder, false);
    imagesavealpha($spriteHolder,true);
    $transparent = imagecolorallocatealpha($spriteHolder, 255, 255, 255, 127);
    imagefilledrectangle($spriteHolder, 0, 0, $imgSize->right, $imgSize->bottom, $transparent);
    
    foreach($sprite as $spriteImage){
      $tempImage = imagecreatefrompng($spriteImage->getPath());
      $position = $spriteImage->getPosition();      
      $margin = $spriteImage->getMargin();
      imagecopy($spriteHolder, $tempImage, $position->left + $margin->left, $position->top + $margin->top, 0, 0, $spriteImage->getOriginalWidth(), $spriteImage->getOriginalHeight());
      imagedestroy($tempImage);
    }
    
    ob_start();
    imagepng($spriteHolder);
    $spriteOutput = ob_get_clean();
    imagedestroy($spriteHolder);
    SpriteImageWriter::writeImageFile($spriteOutput, $filePath);
  }
  protected static function handlePng8(SpriteSprite $sprite, $imgSize, $filePath){
    
    $spriteHolder = imagecreatetruecolor($imgSize->right, $imgSize->bottom);
    //$trans_color = imagecolorallocatealpha($sprite, 0, 0, 0, 127);
    //imagesavealpha($sprite, true);
    //imagealphablending($sprite, true);
    //imagefill($sprite, 0, 0, $trans_color);
    
    foreach($sprite as $spriteImage){
      $tempImage = imagecreatefrompng($spriteImage->getPath());
      $position = $spriteImage->getPosition();      
      $margin = $spriteImage->getMargin();
      imagecopy($spriteHolder, $tempImage, $position->left + $margin->left, $position->top + $margin->top, 0, 0, $spriteImage->getOriginalWidth(), $spriteImage->getOriginalHeight());
      imagedestroy($tempImage);
    }
    
    //And now convert it back to PNG-8
    $png8 = imagecreate($imgSize->right, $imgSize->bottom);
    //imagesavealpha($png8, true);
    //imagealphablending($png8, true);
    //imagefill($png8, 0, 0, $trans_color);

    imagecopy($png8,$spriteHolder,0,0,0,0,$imgSize->right,$imgSize->bottom);
    imagedestroy($spriteHolder);
    
    ob_start();
    imagepng($png8);
    $spriteOutput = ob_get_clean();
    imagedestroy($png8);
    SpriteImageWriter::writeImageFile($spriteOutput, $filePath);
  }
}

?>