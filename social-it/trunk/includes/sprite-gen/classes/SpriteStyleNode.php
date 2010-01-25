<?php
class SpriteStyleNode{
  protected $style;
  protected $class;
  protected $background_image;
  protected $background_position;
  protected $width;
  protected $height;
  protected $backgroundNode;
  
  public function SpriteStyleNode($spriteImage, $class, $backgroundNode = null, $background_image = null){
    if($spriteImage){
      $this->background_position = array();
      $position = $spriteImage->getPosition();
      $this->background_position['left'] = -1 * $position->left;
      $this->background_position['top']  = -1 * $position->top;
      $this->width = $spriteImage->getWidth();
      $this->height = $spriteImage->getHeight();
    }
    $this->class = $class;
    $this->background_image = $background_image;
    $this->backgroundNode = $backgroundNode;
  }
  
  public function image_tag(array $params = array()){
    
    $transImage = SpriteConfig::get('transparentImagePath');
    $tag = '';
    
    if(@$params['inline']){
      $tag = '<img src="'.$transImage.'" style="'.$this->renderStyleWithBackground($params).'"/>'."\n";
    }
    else{
      $tag = '<img src="'.$transImage.'" class="'.$this->renderClass().'"/>'."\n";
    }
    return $tag;
  }
  
  public function getBackgroundImage(){
    return $this->background_image;
  }
  public function renderBackgroundPosition(array $params = array()){
    $augmentX = (isset($params['augmentX']))?($params['augmentX']):(0);
    $augmentY = (isset($params['augmentY']))?($params['augmentY']):(0);
    
    if(isset($params['align'])){
      switch($params['align']){
        case 'left':{
          $left = 'left';
          $top = $this->background_position['top'].'px';
          break;
        }
        case 'right':{
          $left = 'right';
          $top = $this->background_position['top'].'px';
          break;
        }
        case 'bottom':{
          $left = $this->background_position['left'].'px';
          $top = 'bottom';
          break;
        }
        case 'top':{
          $left = $this->background_position['left'].'px';
          $top = 'top';
          break;
        }
      }
    }
    else{
      $left = ($this->background_position['left'] + $augmentX).'px';
      $top = ($this->background_position['top'] + $augmentY).'px';
    }
    if($this->background_position){
      //return 'background-position: '.$left.' '.$top.' ; ';
      /* Here edited by Gautam */
      return 'background-position: '.$left.' bottom !important;';
    }
    return '';
  }
  public function renderBackground(array $params = array()){
    if($this->backgroundNode){
      return $this->backgroundNode->renderBackground($params);
    }
    else{
      $background = (isset($params['background']))?($params['background']):('no-repeat');
      return 'background: url(\''.$this->background_image.'\') '.$background.';';
    }
  }
  public function renderWidth(){
    if($this->width){
      return 'width: '.$this->width.'px;';
    }
    return '';
  }
  public function renderHeight(){
    if($this->height){
      //return 'height: '.$this->height.'px ; ';
      /* Here edited by Gautam */
      return 'height: 29px;';
    }
    return '';
  }
  public function renderSize(){
    //return $this->renderWidth().' '.$this->renderHeight();
    /* Here edited by Gautam */
    return '';
  }
  public function renderImageClass(){
    return $this->class.' ';
  }
  public function renderBgClass(){
    if($this->backgroundNode){
      return $this->backgroundNode->renderImageClass().' ';
    }
    return '';
  }
  public function renderClass(){
    $class = '';
    if($this->backgroundNode){
      $class .= $this->backgroundNode->renderImageClass();
    }
    $class .= $this->renderImageClass();
    return $class;
  }
  public function renderStyle(array $params=array()){

      if(!$this->backgroundNode){
        return $this->renderBackground($params);
      }
      else{
        if(@$params['inline']){
          return $this->renderBackground($params).$this->renderBackgroundPosition($params).$this->renderSize();
        }
        else{

          return $this->renderBackgroundPosition($params).$this->renderSize();
        }
      }
  }
  public function renderStyleWithBackground(array $params=array()){
    if(@$params['inline']){
      return $this->renderBackground($params).$this->renderBackgroundPosition($params).$this->renderSize();
    }
    else{
      //return $this->renderCssWithBackground($params);
    }
  }
  public function renderCss(array $params=array()){
    /* Here edited by Gautam */
    if($this->backgroundNode){
      return $this->class.' {'.$this->renderStyle($params).'} '.$this->class.':hover {background-position: '.$this->background_position['left'].'px top !important;}'."\n";
    }else{
      return $this->class.' {'.$this->renderStyle($params).'}';
    }
  }
  /*public function renderCssWithBackground(array $params=array()){
    return '.'.$this->class.' {'.$this->renderStyleWithBackground($params).'} ';
  }
  public function renderCssOnlyBackground(array $params=array()){
    return '.'.$this->class.' {'.$this->renderBackground($params).'} ';
  }*/
}
?>