<?php
/**
* @package cSprites
* @copyright (C) 2008 Adrian Mummey, DevRepublik
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Adrian Mummey <amummey@gmail.com>
* cSprites is Free Software
* Link: http://www.mummey.org/2008/12/csprites-a-dynamic-css-sprite-generator-in-php5/
*/

define('SPRITE_ROOT_DIR', realpath(dirname(__FILE__)));
define('SPRITE_CONFIG_FILE', SPRITE_ROOT_DIR.'/config.yml');

require_once('classes/SpriteIterable.php');
require_once('classes/Sprite.php');
require_once('classes/SpriteAbstractPacker.php');
require_once('classes/SpriteAbstractPackingNode.php');
require_once('classes/SpriteAbstractParser.php');
require_once('classes/SpriteCache.php');
require_once('classes/SpriteConfig.php');
require_once('classes/SpriteException.php');
require_once('classes/SpriteHashable.php');
require_once('classes/SpriteImage.php');
require_once('classes/SpriteImageRegistry.php');
require_once('classes/SpriteImageWriter.php');
require_once('classes/SpriteRectangle.php');
require_once('classes/SpriteSorter.php');
require_once('classes/SpriteSprite.php');
require_once('classes/SpriteStyleGroup.php');
require_once('classes/SpriteStyleNode.php');
require_once('classes/SpriteStyleRegistry.php');
require_once('classes/SpriteTemplate.php');
require_once('classes/SpriteTemplateRegistry.php');
require_once('classes/Spyc.php');
require_once('packers/SpriteDefaultPacker.php');
require_once('packers/SpriteDefaultPackingNode.php');
require_once('parsers/SpriteDefaultCssParser.php');
require_once('sorters/SpriteAreaSorter.php');
require_once('sorters/SpriteLongestDimensionSorter.php');

SpriteConfig::set('rootDir', spriteGetWebRoot());

function spriteGetWebRoot(){
  $local= getenv("SCRIPT_NAME");
  $absolute = realpath(basename($local));
  $absolute =str_replace("\\","/",$absolute);
  $fullPath = preg_replace('`'.$local.'`si', '', $absolute, 1);
  return $fullPath;
}

?>