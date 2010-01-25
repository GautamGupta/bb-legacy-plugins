<?php 
class SpriteDefaultCssParser implements SpriteAbstractParser{

  public static function parse($relFilePath){
   $absPath = SpriteConfig::get('rootDir');
    if(!file_exists($absPath)){
      throw new SpriteException($absPath.' : file does not exist');
    }
    
    $contents = file_get_contents($absPath);
    return self::parseFile($contents);
  }
  
  protected static function parseFile($contents){
    return null;
  }
}
?>