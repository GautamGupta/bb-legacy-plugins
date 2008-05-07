<?php
/*
Plugin Name: bbRusToLat
Plugin URI: http://bbpress.ru/downloads/plugins/#bb-rus-to-lat
Description: This plugin convert cyrillic characters in tag to latin characters. Very usefull for russian-speaking users of bbPress. This plugin is based on plugin rus-to-lat for WordPress' post title.
Author: Alex
Contributor: Alex
Author URI: http://bbpress.ru/
Version: 0.31
*/ 

$original = array(
   "Ґ"=>"G","Ё"=>"YO","Є"=>"E","Ї"=>"YI","І"=>"I",
   "і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"#","є"=>"e",
   "ї"=>"yi","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
   "Д"=>"D","Е"=>"E","Ж"=>"ZH","З"=>"Z","И"=>"I",
   "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
   "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
   "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
   "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"'","Ы"=>"YI","Ь"=>"",
   "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
   "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
   "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
   "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"'",
   "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
  );
 
function bb_sanitize_text_with_translit($text) {
	global $original;

	return strtr($text, $original);
}

add_action('pre_sanitize_with_dashes', 'bb_sanitize_text_with_translit', 0);
?>
