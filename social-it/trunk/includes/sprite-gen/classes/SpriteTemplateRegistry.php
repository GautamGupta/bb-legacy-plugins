<?php
class SpriteTemplateRegistry{
  protected static $registry = array();
  
  public static function registerTemplate($relTemplatePath, $outputName, $outputPath = null){
    $absPath = SpriteConfig::get('rootDir').$relTemplatePath;
    if(file_exists($absPath)){
      self::$registry[] = new SpriteTemplate($relTemplatePath, $outputName, $outputPath);  
    }
    else{
      throw new SpriteException($absPath.' - This template file does not exist');
    }
  }
  
  public static function getTemplate($tmplName){
  
  }
  
  public static function preProcessTemplates(){
    //Do some directory checks
    if(count(self::$registry)){
      if(!is_dir(SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory'))){
        throw new SpriteException(SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory').' - this is not a valid directory');
      }
      if(!is_writable(SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory'))){
        throw new SpriteException(SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory').' - this directory is not writable');
      }
      if(is_array(self::$registry)){
        foreach(self::$registry as $template){
          if(SpriteCache::needsCreation(SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory').'/'.$template->getPreprocessName())){
            call_user_func(SpriteConfig::get('parser').'::parse', $template);
            self::preprocess($template);     
          }
        }
      }
    }
  }
  protected static function preprocess($template){
    $inputFile  = SpriteConfig::get('rootDir').$template->getRelativePath();
    $outputFile = SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory').'/'.$template->getPreprocessName();
    if(file_exists($outputFile)){
      unlink($outputFile);
    }
    
    ob_start();
    require_once($inputFile);
    $output = ob_get_clean();
    $processedString = preg_replace(array('`\[\?php`si','`\?\]`si') , array('<?php', '?>'),$output);
    if(file_put_contents($outputFile, $processedString) === false){
      throw new SpriteException($outputFile.' - could not write preprocess file.');
    }
    return;
  }
  
  public static function processTemplates(){
    if(is_array(self::$registry)){
      foreach(self::$registry as $template){
        if(SpriteCache::needsCreation(SpriteConfig::get('rootDir').$template->getRelOutputPath())){
          self::process($template);     
        }
      }
    }
  }
  
  protected static function process($template){
    $inputFile  = SpriteConfig::get('rootDir').SpriteConfig::get('relPreprocessorDirectory').'/'.$template->getPreprocessName();
    $outputFile = SpriteConfig::get('rootDir').$template->getRelOutputPath();
    if(file_exists($outputFile)){
      unlink($outputFile);
    }
    
    ob_start();
    require_once($inputFile);
    $output = ob_get_clean();
    if(SpriteConfig::get('deletePreprocess')){
      unlink($inputFile);
    }
    
    if(file_put_contents($outputFile, $output) === false){
      throw new SpriteException($outputFile.' - could not write preprocess file.');
    }
    return;
  }
}