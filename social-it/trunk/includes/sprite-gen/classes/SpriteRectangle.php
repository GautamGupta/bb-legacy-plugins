<?php 
class SpriteRectangle{

  public $width;
  public $height;
  public $left;
  public $top;
  public $right;
  public $bottom;

  public function SpriteRectangle($left, $top, $right, $bottom){
  
    $this->left = $left;
    $this->top = $top;
    $this->right = $right;
    $this->bottom = $bottom;
    $this->width = $this->right - $this->left;
    $this->height = $this->bottom - $this->top;
  }
  
  public function willFit(SpriteImage $spriteImage){
    SpriteConfig::debug("willFit :".$spriteImage->getWidth()." <= ".$this->width.") && (".$spriteImage->getHeight()." <= ".$this->height."))");
    return (($spriteImage->getWidth() <= $this->width) && ($spriteImage->getHeight() <= $this->height));
  }
  
  public function willFitPerfectly(SpriteImage $spriteImage){
    SpriteConfig::debug('Perfect Fit: '.$spriteImage->getWidth().' '.$spriteImage->getHeight());
    return (($spriteImage->getWidth() == $this->width) && ($spriteImage->getHeight() == $this->height));
  }
  
  public function grow($x=100, $y=100){
    $this->right += $x;
    $this->bottom += $y;
    SpriteConfig::debug('Growing : '.$this->right.' '.$this->bottom);
    $this->width = $this->right - $this->left;
    $this->height = $this->bottom - $this->top;
  }
  
  public function __toString(){
    return 'l:'.$this->left.' t:'.$this->top.' r:'.$this->right.' b:'.$this->bottom;
  }
  
  public function update($left, $top, $right, $bottom){
    $this->left = $left;
    $this->top = $top;
    $this->right = $right;
    $this->bottom = $bottom;
    $this->width = $this->right - $this->left;
    $this->height = $this->bottom - $this->top;
  }
}