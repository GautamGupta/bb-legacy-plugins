<?php
abstract class SpriteAbstractPackingNode{
  
  protected $spriteImage;
  protected $child;
  public $rect;
  
  public function __construct(){
    $this->child = array();
  }
  
  public function isLeaf(){
    return !(count($this->child));
  }
  
  public function setSpriteImage(SpriteImage &$si){
    $this->spriteImage = $si;
  }
  public function setRectangle(SpriteRectangle $sr){
    $this->rect = $sr;
  }
  /*public function &getSpriteImage(){
    return $this->spriteImage;
  }*/
  /*public function getRectangle(){
    return $this->rect;
  }*/
  public function getChildren(){
    return $this->child;
  }
  abstract public function insert(SpriteImage &$spriteImage);
}
?>