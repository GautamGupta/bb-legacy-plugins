<?php

class Sprite{
  public static function process(){
    SpriteTemplateRegistry::preProcessTemplates();       
    SpriteImageRegistry::processSprites();
    SpriteTemplateRegistry::processTemplates();    
    SpriteStyleRegistry::processCss();
  }
  
  
  public static function registerTemplate($relTemplatePath, $outputName = null, $outputPath = null){
    SpriteTemplateRegistry::registerTemplate($relTemplatePath, $outputName, $outputPath);
  }
  
  public static function getStyleNode($path){
    return SpriteStyleRegistry::getStyleNode($path);
  }
  
  public static function style($path, array $params = array()){
    return ($node = SpriteStyleRegistry::getStyleNode($path))?($node->renderStyle($params)):('');
  }
  
  public static function ppRegister($path, array $params = array()){
    SpriteImageRegistry::register($path, $params);
  }
  
  public static function ppStyle($path, array $params = array()){
    //SpriteImageRegistry::register($path, @$params['name'], @$params['imageType']);
    SpriteImageRegistry::register($path, $params);
    return "<?php echo Sprite::style('".$path."',".self::arToStr($params)."); ?>";
  }
  
  public static function styleWithBackground($path, array $params = array()){
    return ($node = SpriteStyleRegistry::getStyleNode($path))?($node->renderStyleWithBackground($params)):('');
  }

  public static function image_tag($path, $params = array()){
    return ($node = SpriteStyleRegistry::getStyleNode($path))?($node->image_tag($params)):('');
  }
  
  public static function styleClass($path){
    return ($node = SpriteStyleRegistry::getStyleNode($path))?($node->renderClass($params)):('');
  }
  public static function getAllCssInclude(){
    return SpriteStyleRegistry::getAllCssInclude();
  }
  
  public static function getCssInclude($spriteName, $imageType = null){
    return SpriteStyleRegistry::getCssInclude($spriteName, $imageType = null);
  }




  protected static function arToStr($array, $depth = 0){
    if($depth > 0){
      $tab = implode('', array_fill(0, $depth, "\t"));
    }
    
    $text.="array(\n";
    $count=count($array);
    $x =0 ;
    foreach ($array as $key=>$value){
       $x++;
       if (is_array($value)){
         if(substr($text,-1,1)==')')    $text .= ',';
         $text.=$tab."\t".'"'.$key.'"'." => ".self::arToStr($value, $depth+1);
         if ($count!=$x) $text.=",\n";
         continue;
       }
    
       $text.=$tab."\t"."\"$key\" => \"$value\""; 
       if ($count!=$x) $text.=",\n";
    }
    
    $text.="\n".$tab.")\n";
    if(substr($text, -4, 4)=='),),')$text.='))';
    return $text;
  }

}
?>