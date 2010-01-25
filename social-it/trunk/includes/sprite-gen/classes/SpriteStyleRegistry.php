<?php
class SpriteStyleRegistry{
  protected static $registry = array();
  
  public static function addSprite(SpriteSprite &$sprite){
    self::$registry[$sprite->getKey()] = new SpriteStyleGroup($sprite);
  }
  
  public static function processCss(){
    $allCss = '';
    foreach(self::$registry as &$styleGroup){
      $filepath = SpriteConfig::get('rootDir').$styleGroup->getRelativePath();
      $tempCss = $styleGroup->getCss();
              
      file_put_contents($filepath, $tempCss);
      $allCss .= $tempCss;
    }
    file_put_contents(SpriteConfig::get('rootDir').self::getRelativePath(), $allCss);
  } 
  
  public static function getStyleNodes(){
    return self::$registry;
  }
  public static function getStyleNode($path){
    $node = null;
    foreach(self::$registry as $spriteGroup){
      if(isset($spriteGroup[$path])){
        $node = $spriteGroup[$path];
        break;
      }
    }
    return $node;
  }
  
  public static function getCssInclude($spriteName, $imageType = null){
    $tempSprite = new SpriteSprite($spriteName, $imageType);
    if(isset(self::$registry[$tempSprite->getKey()])){
      $sprite = self::$registry[$tempSprite->getKey()];
      return '<link rel="stylesheet" type="text/css" title="'.$sprite->getKey().'" media="all" href="'.$sprite->getRelativePath().'" />'."\n";
    }
    return '';
  }
  
  public static function getAllCssInclude(){
    return '<link rel="stylesheet" type="text/css" title="cSprite CSS" media="all" href="'.self::getRelativePath().'" />'."\n";
  }
  
  public static function getRelativePath(){
    return SpriteConfig::get('relTmplOutputDirectory').'/'.self::getFileName();
  }
  
  public static function getFileName(){
    return self::getHash().'.css';
  }
  
  public static function getHash(){
    return md5(serialize(self::$registry));
  }
}
?>