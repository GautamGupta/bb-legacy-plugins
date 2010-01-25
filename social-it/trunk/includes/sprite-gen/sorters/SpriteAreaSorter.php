<?php

/*class SpriteAreaSorter extends SpriteAbstractSorter{
  public function addImage(array $registry, SpriteImage $spriteImage){
    $area = $spriteImage->getArea();
    $newRegistry = array();

    foreach($registry as $key=>$sp){
      $count ++;
      if($area > $sp->getArea()){
        $newRegistry[$spriteImage->getPath()] = $spriteImage;
        $newRegistry = array_merge($newRegistry, $registry);
        break;
      }
      $newRegistry[$sp->getPath()] = $sp;
      unset($registry[$key]);
      
      if($sp == end($registry)){
        $newRegistry[$spriteImage->getPath()] = $spriteImage;  
      }
    }
    return $newRegistry;
  }
}*/

class SpriteAreaSorter implements SpriteSorter{
  public static function sort(SpriteSprite &$sprite){
    $sprite->uasort('SpriteAreaSorterCompare');
  }
}
function SpriteAreaSorterCompare(SpriteImage $sp1, SpriteImage $sp2){
  if($sp1->getArea() == $sp2->getArea()){
    return 0;
  }
  return ($sp1->getArea() > $sp2->getArea())?(-1):(1);
}

?>