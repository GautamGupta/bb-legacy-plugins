<?php
class SpriteLongestDimensionSorter implements SpriteSorter{
  public static function sort(SpriteSprite &$sprite){
    $sprite->uasort('SpriteLongestDimesionSorterCompare');
  }
}
function SpriteLongestDimesionSorterCompare(SpriteImage $sp1, SpriteImage $sp2){
    if($sp1->getLongestDimension() == $sp2->getLongestDimension()){
      return 0;
    }
    return ($sp1->getLongestDimension() > $sp2->getLongestDimension())?(-1):(1);
  }
?>