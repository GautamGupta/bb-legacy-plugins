<?php
/*******************************************************************
*	PLAIN_TEXT :
*	Enabling this option will show the "PLAIN TEXT" link with your 
*	code boxes to allow users to get your code as 
*	un-hilited Plain Text. 
*	Values: true or false
*******************************************************************/
$use_plain_text = true;

/*******************************************************************
*	SHOW_LANG_NAME :
*	Enabling this option will show the NAME of the LANGUAGE 
*	whose code is hilited in the code box.
*	Values: true or false
*******************************************************************/
$show_lang_name = true;

/*******************************************************************
*	LINE_NUMBERS :
*	Enabling this option will show the Line Numbers in the 
*	code boxes. 
*	With Line Numbers, the code looks good & is easy to refer and 
*	debug.
*	Values: true or false
*******************************************************************/
$show_line_numbers = true;

/*******************************************************************
*	FANCY_NUMBERS :
*	Enabling this option will show the Line Numbers in the code 
*	boxes with alternate colours. They look cool on the code boxes.
*	Values: true or false
*******************************************************************/
$use_fancy_numbers = true;

/*******************************************************************
*	IG_LINE_COLOUR_1 :
*	This is the first colour for the line-numbers of the code boxes.
*	This is only useful if you have selected FANCY LINE NUMBERS above.
*******************************************************************/
$line_color_1 = "#3A6A8B";

/*******************************************************************
*	IG_LINE_COLOUR_1 :
*	This is the second colour for the line-numbers of the code boxes.
* 	This is only useful if you have selected FANCY LINE NUMBERS above.
*******************************************************************/
$line_color_2 = "#26536A";

/*******************************************************************
*
* STOP EDIT HERE UNLESS YOU KNOW WHAT YOU ARE DOING
*
*******************************************************************/

define('IG_PLAIN_TEXT', $use_plain_text);
define('IG_SHOW_LANG_NAME', $show_lang_name);
define('IG_LINE_NUMBERS', $show_line_numbers);
define('IG_FANCY_NUMBERS', $use_fancy_numbers);
define('IG_LINE_COLOUR_1', $line_color_1);
define('IG_LINE_COLOUR_2', $line_color_2);
?>