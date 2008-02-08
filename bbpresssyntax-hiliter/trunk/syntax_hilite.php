<?php
/*
Plugin Name: BBPress:Syntax Hiliter
Plugin URI: http://www.klr20mg.com/
Feed URI: http://www.klr20mg.com/feed
Description: Syntax Highlighter for various programming languages under BBPRESS, using the <a href='http://qbnz.com/highlighter/' target="_blank">GeSHi</a> engine.<br />This plugin is based on Wordpress <a href='http://blog.igeek.info/wp-plugins/igsyntax-hiliter/'>iG:Syntax Hiliter</a> coded by <a href='http://blog.igeek.info/'>Amit Gupta</a>. 
Version: 0.1 Beta
Author: Enrique Chavez aka Tmeister
Author URI: http://www.klr20mg.com
*/
/*******************************************************************
*
* DON'T EDIT THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING
*
*******************************************************************/

/*******************************************************************
* Version of the Plugin
*******************************************************************/
define('IG_VERSION', "0.1");

/*******************************************************************
* Plugin File Name
*******************************************************************/
define('IG_FILE', "syntax_hilite.php");

/*******************************************************************
* Plaint Text Type
*******************************************************************/
define('IG_PLAIN_TEXT_TYPE', "inbox");

/*******************************************************************
* iGSyntaxHilite Object, autoFormat ON or OFF, code box id, 
* igVersion, igPath
*******************************************************************/
global $igsh, $tOffAutoFmt, $cbId, $igBbPressVersion, $igsyntax_hiliter_path;	
 
/*******************************************************************
* Set autoFormatOff as FALSE to enable auto formatting by default
*******************************************************************/
$tOffAutoFmt = false;

/*******************************************************************
* initialise the Code Box Id
*******************************************************************/

$cbId = 1;

/*******************************************************************
* Version of BBPRESS where the Debug was done
*******************************************************************/
$igBbPressVersion = "0.8.3.1" ;

/*******************************************************************
* Physical path to the directory where geshi directory resides, 
* should end with a /
*******************************************************************/
$ig_geshipath = BBPATH."my-plugins/bbpress_syntax_hilite/";

/*******************************************************************
* URL to the plugin directory
*******************************************************************/
$igsyntax_hiliter_path = bb_get_option('uri')."my-plugins/bbpress_syntax_hilite";

/*******************************************************************
* Include plugin configuration
*******************************************************************/
require_once("{$ig_geshipath}syntax_hilite_config.php");

/*******************************************************************
* include the GeSHi Core
*******************************************************************/
require_once("{$ig_geshipath}geshi.php");

/*******************************************************************
* Start Class 
*******************************************************************/

class iGSyntaxHilite {
	/*******************************************************************
	* Variable to store the path of the GeSHi Language Files
	*******************************************************************/

	var $ig_geshipath = null;
	
	// Class Constructor
	/**
	* @return NOTHING
	* @param $ig_geshipath
	* @desc This is the Constructor of the Class & accepts the path of the language files directory
	*/
	function iGSyntaxHilite($ig_geshipath) {
		$this->ig_geshipath = $ig_geshipath;
	}	// END CONSTRUCTOR iGSyntaxHilite

	// Function for Prefixing the DIV around
	/**
	* @return Starting <DIV> for the CODE BOX
	* @param $hLang, $bId, $bCls
	* @desc This is the function for prefixing the starting portion of the <DIV> code box with the CSS Class & Language Name Set
	*/
	function pFix($hLang='PHP', $bId, $bCls='syntax_hilite') {
		$bBody = "";
		$bId = strtolower($bId);
		if(IG_PLAIN_TEXT) {
			//show the PLAIN TEXT View
			if(IG_PLAIN_TEXT_TYPE=="inbox") {
				$ig_jsPlainTxt = "showPlainTxt";
			} else {
				$ig_jsPlainTxt = "showCodeTxt";
			}
			$bBody .= "<div class=\"igBar\"><span id=\"l{$bId}\"><a href=\"#\" onclick=\"javascript:{$ig_jsPlainTxt}('{$bId}'); return false;\">PLAIN TEXT</a></span></div>";
		}
		if(IG_SHOW_LANG_NAME) {
			$bBody .= "<div class=\"{$bCls}\"><span class=\"langName\">{$hLang}:</span><br /><div id=\"{$bId}\">\n";
		} else {
			$bBody .= "<div class=\"{$bCls}\"><div id=\"{$bId}\">\n";
		}
		return $bBody;
	}	// END pFix

	// Function for Suffixing the DIV
	/**
	* @return Ending <DIV> for the CODE BOX
	* @param $bId
	* @desc This is the function for suffixing the end portion of the <DIV> code box
	*/
	function sFix() {
		$bBody = "\n</div></div><br />";
		return $bBody;
	}	// END sFix

	// Function for Hiliting
	/**
	* @return $hCode
	* @param $mTxt, $mType
	* @desc This Function hilites the Codes
	*/
	function doHilite($mTxt, $mType='html', $sNum=1) {
		global $cbId;
		$sNum = (int) $sNum;
		$sNum = ($sNum<1) ? 1 : $sNum;
		switch($mType) {
			case "as":
				$mType = "actionscript";
				$mTypeShow = "Actionscript";
				break;
			case "cpp":
				$mType = "cpp";
				$mTypeShow = "C++";
				break;
			case "js":
				$mType = "javascript";
				$mTypeShow = "JavaScript";
				break;
			case "csharp":
				$mType = "csharp";
				$mTypeShow = "C#";
				break;
			case "mysql":
				$mType = "mysql";
				$mTypeShow = "MySQL";
				break;
			case "vb":
				$mType = "vb";
				$mTypeShow = "Visual Basic";
				break;
			case "vbnet":
				$mType = "vbnet";
				$mTypeShow = "VB.NET";
				break;
			default:
				$mType = $mType;
				$mTypeShow = strtoupper($mType);
				break;
		}
		if(function_exists("file_exists")) {
			if(file_exists("{$this->ig_geshipath}{$mType}.php")) {
				$igCheckFile = true;
			} else {
				$igCheckFile = false;
			}
		} else {
			$igCheckFile = true;
		}
		$mTxt = clean_pre($mTxt);
		if($igCheckFile) {
			$geshi = new GeSHi(trim($mTxt), $mType, $this->ig_geshipath);
			$geshi->set_header_type(GESHI_HEADER_DIV);
			if(IG_LINE_NUMBERS) {
				if(IG_FANCY_NUMBERS) {
					$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
					$geshi->set_line_style('color:'.IG_LINE_COLOUR_1.';', 'color:'.IG_LINE_COLOUR_2.';', true);
				} else {
					$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
					$geshi->set_line_style('color:'.IG_LINE_COLOUR_1.'; font-weight:bold;', true);
				}
				$geshi->set_numbers_style('color:#800000;', true);
				$geshi->start_line_numbers_at($sNum);		// start Line Number from this number
			} else {
				$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
			}
			$geshi->set_tab_width(4);
			$hCode = $geshi->parse_code();
			$hCode = $this->pFix($mTypeShow, $mType.'-'.$cbId).$hCode.$this->sFix();
			$cbId++;
		} else {
			//return code as it is
			if($sNum>1) {
				$igAppndNum = " num={$sNum}";
			} else {
				$igAppndNum = "";
			}
			$hCode = "[{$mType}{$igAppndNum}]{$mTxt}[/{$mType}]";
		}
		$hCode = str_replace("&amp;lt;", "< ", $hCode);
		$hCode = str_replace("&amp;gt;", " >", $hCode);
		return $hCode;
	}	// END doHilite

}	

/*******************************************************************
* END CLASS iGSyntaxHilite
*******************************************************************/
$igsh = new iGSyntaxHilite($ig_geshipath.'geshi/');

/*******************************************************************
* Hilite CODE
*******************************************************************/
function igSynHilite_code($hCode) {
	global $igsh,$tOffAutoFmt;
	$startTag = strtolower(trim($hCode[1]));
	$inTxt = $hCode[4];
	$pVal = (int) $hCode[3];// get the starting line number
	$hilitedCode = "";
	if(!empty($startTag)) {
		if(strlen($inTxt)>1) 
		{
			$tOffAutoFmt = 1;// if code is there, disable auto formatting
		}
		$pVal = ((empty($pVal)) || ($pVal<1)) ? 1 : $pVal;
		$hilitedCode = $igsh->doHilite($inTxt, $startTag, $pVal);
	}
	return $hilitedCode;
}

/*******************************************************************
* Main function that calls the highlighter
*******************************************************************/
function igSynHilite($inData) {
	$inData = preg_replace_callback('/\[(\w{1,})((?:\s+num+=([0-9]{1,})+)*)\](.+?)\[\/\1\]/ims', 'igSynHilite_code', $inData);		// call code hiliter
	return $inData;
}
/*******************************************************************
* Function for outputting styles
*******************************************************************/
function igSynHilite_header() {
	global $cssStyles, $igsyntax_hiliter_path;
	$hHead = "<link rel=\"stylesheet\" href=\"{$igsyntax_hiliter_path}/css/syntax_hilite_css.css\" type=\"text/css\" media=\"all\" />\n";
	if(IG_PLAIN_TEXT) {
		$hHead .= "	<script language=\"javascript\" type=\"text/javascript\" src=\"{$igsyntax_hiliter_path}/js/syntax_hilite_js.js\"></script>\n";
		$hHead .= "	<script language=\"javascript\" type=\"text/javascript\">\n";
		$hHead .= "	var arrCode = new Array();\n";
		$hHead .= "	</script>\n";
	}
	print($hHead);
}
/*******************************************************************
* Output to the <head> section of the page, Adding the css File
*******************************************************************/
add_action('bb_head', 'igSynHilite_header');

/*******************************************************************
* Add Filter to post_text function 
*******************************************************************/
add_filter('post_text', 'igSynHilite');
?>