<?php
class SpriteCache{
  
  protected static $cacheArray;
  
  public static function needsCreation($absFile){

    $cacheTime = SpriteConfig::get('cacheTime') * 60;
    
    if(!is_array(self::$cacheArray)){
      self::$cacheArray = array();
    }
    
    self::$cacheArray[] = $absFile;
    
    //If Cache time is 0 always create
    if(!SpriteConfig::get('cacheTime')){
      return true;
    }
  
    if(file_exists($absFile)){
      return (time() - $cacheTime < filemtime($file))?(false):(true);
    }
    return true;
  }
    
  public static function updateCache(){
    $cacheTime = SpriteConfig::get('cacheTime') * 60;
    
    $tmplFiles  = SpriteImageRegistry::buildFileList(SpriteConfig::get('relTmplOutputDirectory'));
    $imageFiles = SpriteImageRegistry::buildFileList(SpriteConfig::get('relImageOutputDirectory'));
    
    $files = array_merge($tmplFiles, $imageFiles);
    foreach($files as $file){
      if(file_exists($file)){
        if(time() - $cacheTime > filemtime($file)){
          unlink($file);
        }
      }
    }
    
  }
}
?>