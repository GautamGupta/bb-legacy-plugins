<?php
class SpriteDefaultPacker extends SpriteAbstractPacker{
  
  public static function pack(SpriteSprite &$sprite){
    $root = new SpriteDefaultPackingNode();
    $root->setRectangle(self::getBoundingBox($sprite));

    foreach($sprite as &$spriteImage){
      $root->insert($spriteImage);
    }
  }
  
  protected static function getBoundingBox(SpriteSprite $sprite){
    $bbSize = ($box = SpriteConfig::get('boundingBoxSize'))?($box):(10000);
    
    if($sprite->getRepeatable()){
      if(strtolower($sprite->getRepeatable()) == 'x'){
        return new SpriteRectangle(0,0,$sprite->getLongestWidth(), $bbSize);
      }
      else{
        return new SpriteRectangle(0,0,$bbSize, $sprite->getLongestHeight());
      }
    }
    return ($sprite->getLongestWidth() > $sprite->getLongestHeight())?
      (new SpriteRectangle(0,0,$sprite->getLongestWidth(), $bbSize)):
      (new SpriteRectangle(0,0,$bbSize, $sprite->getLongestHeight()));
  }
}



?>