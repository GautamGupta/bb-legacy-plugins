<?php

class SpriteDefaultPackingNode extends SpriteAbstractPackingNode{

  public function __construct(){
     parent::__construct();
  }
  
  public function insert(SpriteImage &$spriteImage){

    if(!$this->isLeaf()){
      //SpriteConfig::debug('Not a leaf');
      $newNode = $this->child[0]->insert($spriteImage);
      if($newNode != NULL){
        return $newNode;
      }
      //SpriteConfig::debug('Not a Leaf: Inserting Node into rectangle :'.$this->child[1]->rect);
      return $this->child[1]->insert($spriteImage);
    }
    else{
      if($this->spriteImage != NULL){
       // SpriteConfig::debug('Node found');
        return NULL;
      }
      
      if(!($this->rect->willFit($spriteImage))){
        //SpriteConfig::debug('Will not fit');
        return NULL;
      }
      if($this->rect->willFitPerfectly($spriteImage)){
        SpriteConfig::debug('Fits perfectly'.$spriteImage);
        $spriteImage->setPosition($this->rect);
        $this->setSpriteImage($spriteImage);
        //$spriteImage->display($this->rect);
        return $this;
      }
      
      SpriteConfig::debug('Adding children');
      
      $this->child[0] = new SpriteDefaultPackingNode();
      $this->child[1] = new SpriteDefaultPackingNode();
      
      $dw = $this->rect->width - $spriteImage->getWidth();
      $dh = $this->rect->height - $spriteImage->getHeight();
      
      if ($dw > $dh){
        $this->child[0]->rect = new SpriteRectangle($this->rect->left, $this->rect->top, $this->rect->left + $spriteImage->getWidth(), $this->rect->bottom);
        $this->child[1]->rect = new SpriteRectangle($this->rect->left + $spriteImage->getWidth(), $this->rect->top, $this->rect->right, $this->rect->bottom);
      }
      else{
        $this->child[0]->rect = new SpriteRectangle($this->rect->left, $this->rect->top, $this->rect->right, $this->rect->top + $spriteImage->getHeight());
        $this->child[1]->rect = new SpriteRectangle($this->rect->left, $this->rect->top+$spriteImage->getHeight(), $this->rect->right, $this->rect->bottom);
      }
      //$this->child[0]->setSpriteImage($spriteImage);
      SpriteConfig::debug('Inserting Node into rectangle :'.$this->child[0]->rect);
      $newNode = $this->child[0]->insert($spriteImage);
      return $newNode;
    }
    
  }
  
}

?>