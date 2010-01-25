<?php

class SpriteImage implements SpriteIterable, SpriteHashable {
  
  protected $imgPath;
  protected $relativePath;
  protected $imgType;
  protected $imgExtension;
  protected $sizeArray;
  protected $area;
  protected $fileSize;
  protected $imageInfo;
  protected $position;
  protected $margin;
  protected $params;
  
  public function SpriteImage($path, array $params = array()){
    $this->imgPath = SpriteConfig::get('rootDir').$path;
    $this->relativePath = $path;
    
    if(!($this->fileSize = filesize($this->imgPath))){
      SpriteConfig::debug("file existence problem");
      throw new SpriteException($this->imgPath.' : File does not exist or is size 0');
    }

    if(!($this->sizeArray = getimagesize($this->imgPath, $this->imageinfo))){
      SpriteConfig::debug($this->imgPath."image size read problem");
      throw new SpriteException($this->imgPath.' : Image size could not be read');
    }
    SpriteConfig::debug('bits: '.$this->sizeArray['bits'].' channels:'.@$this->sizeArray['channels'].' mime:'.$this->sizeArray['mime']);
    
    $this->processType();
    if(!$this->sizeArray){
      SpriteConfig::debug('Image size misread');
      throw new SpriteException($this->imgPath.' : Image size could not be read');
    }
    $this->setMargins($params);
    $this->params = $params;
  }
  
  public function getPath(){
    return $this->imgPath;
  }  
  
  public function getRelativePath(){
    return $this->relativePath;
  }
  
  public function getType(){
    return $this->imgType;
  }
  
  public function getWidth(){
    return $this->sizeArray[0] + $this->margin->left + $this->margin->right;
  }
  
  public function getOriginalWidth(){
    return $this->sizeArray[0];
  }
  
  public function getHeight(){
    return $this->sizeArray[1] + $this->margin->top + $this->margin->bottom;
  }
  
  public function getOriginalHeight(){
    return $this->sizeArray[1];
  }
  
  public function getExtension(){
    return $this->imgExtension;
  }
  
  public function getArea(){
    return ($this->sizeArray[0] + $this->margin->left + $this->margin->right) * ($this->sizeArray[1] + $this->margin->top + $this->margin->bottom);
  }
  
  public function getOriginalArea(){
    return $this->getWidth() * $this->getHeight();
  }
  
  public function getSizeArray(){
    return $this->sizeArray;
  }
  
  public function getFileSize(){
    return $this->fileSize;
  }
  
  public function getImageInfo(){
    return $this->imageInfo;
  }
  
  public function getColorDepth(){
    return $this->sizeArray['bits'];
  }
  
  public function getMimeType(){
    return $this->sizeArray['mime'];
  }
  
  public function getPosition(){
    return $this->position;
  }
  
  public function getMargin(){
    return $this->margin;
  }
  
  public function getParams(){
    return $this->params;
  }
  
  public function setPosition(SpriteRectangle $rect){
    $this->position = $rect;
  }
  
  public function isTall(){
    return ($this->getHeight() > $this->getWidth());
  }
  
  public function isWide(){
    return ($this->getWidth() > $this->getHeight());
  }
  
  public function isSquare(){
    return ($this->getWidth() == $this->getHeight());
  }
  
  public function getLongestDimension(){
    return ($this->isTall())?($this->getHeight()):($this->getWidth());
  }
  
  public function getKey(){
    return $this->getRelativePath();
  }
  
  public function __toString(){
    $output = ''."\n";
    $output .= '<li>Path :'.$this->getPath().'</li>'."\n";
    $output .= '<li>Type :'.$this->getType().'</li>'."\n";
    $output .= '<li>Extension :'.$this->getExtension().'</li>'."\n";
    $output .= '<li>FileSize :'.$this->getFileSize().'</li>'."\n";            
    $output .= '<li>Dimension :'.$this->getWidth().'x'.$this->getHeight().'</li>'."\n";
    $output .= ''."\n";
    return $output;
  }
  
  public function getHash(){
    return md5($this->getRelativePath());
  }
  
  public function getCssClass(){
    //return 'sprite'.$this->getHash();
    /* Here edited by Gautam */
    $filename = explode('images/icons/', $this->imgPath); //twitter.png
    $filename = explode('.', $filename[1]); //twitter
    $filename = 'li.socialit-'.$filename[0]; //sexy-twitter
    return $filename;
  }
  
  public function updateAlignment(array $spriteParams = array()){
    if(isset($spriteParams['longestWidth']) && isset($spriteParams['longestHeight'])){
      if(isset($this->params['sprite-align'])){
      switch($this->params['sprite-align']){
        case 'left':{
          $rightMargin = $spriteParams['longestWidth'] - ($this->margin->left + $this->sizeArray[0]);
          $this->margin = new SpriteRectangle($this->margin->left, $this->margin->top, $rightMargin, $this->margin->bottom);
          $this->position = new SpriteRectangle(0,0, $spriteParams['longestWidth'], $this->position->bottom);
          break;
        }
        case 'right':{
          $leftMargin = $spriteParams['longestWidth'] - ($this->margin->right + $this->sizeArray[0]);
          $this->margin = new SpriteRectangle($leftMargin, $this->margin->top, $this->margin->right, $this->margin->bottom);
          $this->position = new SpriteRectangle(0,0, $spriteParams['longestWidth'], $this->position->bottom);
          break;
        }
        case 'top':{
          $bottomMargin = $spriteParams['longestHeight'] - ($this->margin->top + $this->sizeArray[1]);
          $this->margin = new SpriteRectangle($this->margin->left, $this->margin->top, $this->margin->right, $bottomMargin);
          $this->position = new SpriteRectangle(0,0, $this->position->right, $spriteParams['longestHeight']);
          break;
        }
        case 'bottom':{
          $topMargin = $spriteParams['longestHeight'] - ($this->margin->bottom + $this->sizeArray[1]);
          $this->margin = new SpriteRectangle($this->margin->left, $topMargin, $this->margin->right, $this->margin->bottom);
          $this->position = new SpriteRectangle(0,0, $this->position->right, $spriteParams['longestHeight']);
          break;        
        }
      }//end switch
      }
    }
  }
  
  protected function processType(){
    $this->imgExtension = strtolower(pathinfo($this->getPath(), PATHINFO_EXTENSION));
    
    if($this->getExtension() == 'png'){
      //$this->imgType = $this->getExtension().'-'.$this->getColorDepth();
      $this->imgType = $this->getExtension();      
    }
    else{
      $this->imgType = $this->getExtension();
    }
    if(!in_array(strtolower($this->getExtension()), SpriteConfig::get('acceptedTypes'))){
      SpriteConfig::debug('Extension Type Mismatch: '.$this->getExtension());
      throw new SpriteException($this->getExtension().' : is not an acceptable image type.');
    }
  }
  protected function setMargins(array $params = array()){
    //First Handle Margins
    if(isset($params['sprite-margin'])){
      if(is_array($params['sprite-margin'])){
        $this->margin = new SpriteRectangle($params['sprite-margin'][3], $params['sprite-margin'][0], $params['sprite-margin'][1], $params['sprite-margin'][2]);
        $this->position = new SpriteRectangle(0, 0, $this->sizeArray[0] + $this->margin->left + $this->margin->right, $this->sizeArray[1] + $this->margin->top + $this->margin->bottom);
      }
    }
    else{
        $this->margin = new SpriteRectangle(0,0,0,0);
        $this->position = new SpriteRectangle(0, 0, $this->sizeArray[0], $this->sizeArray[1]);
    }
    
  }
}

?>