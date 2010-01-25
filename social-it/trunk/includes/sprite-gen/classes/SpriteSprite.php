<?php
class SpriteSprite extends ArrayObject implements SpriteHashable{
  protected $spriteName;
  protected $type;
  protected $spriteImages;
  protected $longestWidth;
  protected $longestHeight;
  protected $largestArea;
  protected $totalArea;
  protected $repeatable;

  public function __construct($spriteName, $type){

    $this->spriteImages = array();
    $this->spriteName = $spriteName;
    $this->type = strtolower($type);
    parent::__construct($this->spriteImages, ArrayObject::ARRAY_AS_PROPS);
  }
  public function append($spriteImage){
    if (!($spriteImage instanceof SpriteImage)){ 
      throw new SpriteException( 'You can only add SpriteImages to this Sprite' ); 
    }
    
    $this->offsetSet(null, $spriteImage);
    $this->updateMaximums($spriteImage);
  } 
  public function offsetSet($index, $spriteImage){    
    if (!($spriteImage instanceof SpriteImage)){
      throw new Exception( 'You can only add SpriteImages to this Sprite' );
    }

    $index = ($index)?($index):($spriteImage->getKey());
    parent::offsetSet($index, $spriteImage);

    $sorterclass = SpriteConfig::get('sorter');
    call_user_func($sorterclass.'::sort',$this);

    $this->updateMaximums($spriteImage);
    $this->updateRepeatable($spriteImage);
  }
  
  public function getType(){
    return $this->type;
  }
  
  public function getName(){
    return $this->spriteName;
  }
  
  public function getRepeatable(){
    return $this->repeatable;
  }
  
  public function add(SpriteImage $spriteImage){
    
  //  $sorterclass::addImage($this, $spriteImage);
   // $this->spriteImages[$spriteImage->getKey()] = $spriteImage;
  }
	
	public function getLongestWidth(){
	 return $this->longestWidth;
	}
	
	public function getLongestHeight(){
	 return $this->longestHeight;
	}
	
	public function getLongestDimension(){
	 return ($this->longestWidth > $this->longestHeight)?($this->longestWidth):($this->longestHeight);
	}
	
	public function getTotalArea(){
	 return $this->totalArea;
	}
	
	public function getHash(){
	 return md5(serialize($this).SpriteConfig::getHash().$this->getType());
	}
	
	public function getImageExtension(){
	 return ($this->getType() == SpriteConfig::PNG8)?(SpriteConfig::PNG):($this->getType());
	}
	
	public function getFilename(){
    return $this->getHash().'.'.$this->getImageExtension();
	}
	
	public function getRelativePath(){
    return SpriteConfig::get('relImageOutputDirectory').'/'.$this->getFilename();
	}
	
	public function getKey(){
    $spriteName = ($this->spriteName)?($this->spriteName.'-'):('');
    return $spriteName.$this->type;
	}
	
	public function prepareSprite(){
	 foreach($this as $spriteImage){
	   $spriteImage->updateAlignment(array('longestWidth'=>$this->longestWidth, 'longestHeight'=>$this->longestHeight, 'totalArea'=>$this->totalArea));
	 }
	}
	
	protected function updateMaximums($spriteImage){
	 $this->longestWidth   = ($spriteImage->getWidth() > $this->longestWidth)?($spriteImage->getWidth()):($this->longestWidth);
	 $this->longestHeight  = ($spriteImage->getHeight() > $this->longestHeight)?($spriteImage->getHeight()):($this->longestHeight);
	 $this->largestArea    = ($spriteImage->getArea() > $this->largestArea)?($spriteImage->getArea()):($this->largestArea);
	 $this->totalArea     += $spriteImage->getArea();
	}
	protected function updateRepeatable($spriteImage){
	 $params = $spriteImage->getParams();
	 if(isset($params['repeat'])){
	   $this->repeatable = $params['repeat'];
	 }
	}
}
?>