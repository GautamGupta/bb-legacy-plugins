<?php
class SpriteConfig{
  const JPG   = 'jpg';
  const GIF   = 'gif';
  const PNG   = 'png';
  const PNG8  = 'png8';
  
  protected static $config;
  
  public static function debug($message){
    if(self::get('debug')){
      echo $message."<br/>"."\n";
    }
  }
  public static function get($data){
    if(!is_array(self::$config)){
      self::$config = Spyc::YAMLLoad(SPRITE_CONFIG_FILE);
    }
    return @(self::$config[$data]);
  }
  public static function set($name, $value){
    if(!is_array(self::$config)){
      self::$config = Spyc::YAMLLoad(SPRITE_CONFIG_FILE);
    }
    self::$config[$name] = $value;
  }
  public static function getHash(){
    return md5(serialize(self::$config));
  }
}
?>