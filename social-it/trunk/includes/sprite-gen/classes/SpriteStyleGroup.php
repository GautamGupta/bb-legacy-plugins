<?php
class SpriteStyleGroup extends ArrayObject implements SpriteHashable{
  protected $spriteStyleNodes;
  protected $sprite;
  protected $backgroundStyleNode;
  
  public function __construct(SpriteSprite &$sprite){
    $this->spriteStyleNodes = array();
    $this->sprite = $sprite;
    parent::__construct($this->spriteStyleNodes, ArrayObject::ARRAY_AS_PROPS);
    
    //$this->backgroundStyleNode = new SpriteStyleNode(null, 'sprite'.md5($this->sprite->getRelativePath()), null, $this->sprite->getRelativePath());
    /* Here edit by Gautam */
    $this->backgroundStyleNode = new SpriteStyleNode(null, '@import "style.css";'."\n".'div.social-it ul.socials li', null, $this->sprite->getRelativePath());
    foreach($this->sprite as $spriteImage){
      $this->addStylesToGroup($spriteImage);
    }
  }
  
  public function getBackgroundStyleNode(){
    return $this->backgroundStyleNode;
  }
  
  public function getStyleNode($path){
    return parent::offsetGet($path);
  }
  
  public function getRelativePath(){
    return SpriteConfig::get('relTmplOutputDirectory').'/'.$this->getFilename();
  }
  
  public function getFilename(){
    return $this->getHash().'.css';
  }
  
  public function getHash(){
    return md5(serialize($this));
  }
  
  public function getCss(){
    $css = $this->getBackgroundStyleNode()->renderCss()."\n";
    foreach($this as $styleNode){
      $css .= $styleNode->renderCss();
    }
    return $css;
  }
  
  protected function addStylesToGroup(SpriteImage $spriteImage){
     parent::offsetSet($spriteImage->getKey(), new SpriteStyleNode($spriteImage, $spriteImage->getCssClass(), $this->getBackgroundStyleNode(), $this->sprite->getRelativePath()));
  }
  
}
