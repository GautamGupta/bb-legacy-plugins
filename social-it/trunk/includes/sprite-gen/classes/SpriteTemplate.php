<?php
class SpriteTemplate{
  protected $relPath;
  protected $outputName;
  protected $preprocessName;
  protected $outputPath;
  
  public function SpriteTemplate($relPath, $outputName, $outputPath){
    $this->relPath = $relPath;
    $this->outputName = $outputName;
    $this->outputPath = ($outputPath)?($outputPath):(SpriteConfig::get('relTmplOutputDirectory'));
    $this->preprocessName = md5($relPath).'.php';
    if($outputPath){
      if(!is_dir(SpriteConfig::get('rootDir').$outputPath)){
        throw new SpriteException($outputPath.' - this template output path is not a valid directory.');
      }
      if(!is_writable(SpriteConfig::get('rootDir').$outputPath)){
        throw new SpriteException($outputPath.' - this template output path is not writeable.');
      }
    }
  }
  
  public function getRelativePath(){
    return $this->relPath;
  }
  
  public function getOutputName(){
    return $this->outputName;
  }
  
  public function getPreprocessName(){
    return $this->preprocessName;
  }
  public function getRelOutputPath(){
    return $this->outputPath.'/'.$this->outputName;
  }
  
  public function getOutputPath(){
    return $this->outputPath;
  }
  
  public function processTemplate(){
    //$absPath = SpriteConfig::get('rootDir')
  }
  
}
?>