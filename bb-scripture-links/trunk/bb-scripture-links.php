<?php
/*
Plugin Name: bb-Scripture-Links
Version: 1.0
Plugin URI: http://blog.wittmania.com/bb-scripture-links
Description: Changes Bible references to hyperlinks to the text at BibleGateway.com
Author: Mike Wittmann
Author URI: http://blog.wittmania.com

NOTE:  Based entirely on the Scripturizer plugin for Wordpress, by:
Dean Peters, ported by Glen Davis, updates by LaurenceO.com
http://www.healyourchurchwebsite.com/

All I did was change some variables, delete whatever code wasn't relevant to bbpress,
and cleaned out some of the functionality to make it more streamelined for most users.

The original plugin can be found here:  http://dev.wp-plugins.org/wiki/Scripturizer
*/

// Change this variable to set the default translation for your forum
$scripturizer_default_translation = 'ESV'; //see list below for abbreviations

$scripturizer_translations=array(
	'NIV'=>'New International Version',
	'NASB'=>'New American Standard Bible',
	'MSG'=>'The Message',
	'AMP'=>'Amplified Bible',
	'NLT'=>'New Living Translation',
	'KJV'=>'King James Version',
	'ESV'=>'English Standard Version',
	'CEV'=>'Contemporary English Version',
	'NKJV'=>'New King James Version',
	'KJ21'=>'21st Century King James Version',
	'ASV'=>'American Standard Version',
	'YLT'=>"Young's Literal Translation",
	'Darby'=>'Darby Translation',
	'NLV'=>'New Life Version',
	'HCSB'=>'Holman Christian Standard Bible',
	'NIRV'=>"New International Readers' Version",
	'NIV-UK'=>'New International Version (British Edition)',
	);
	
$new_window = 1; //Change to 1 if you want the link to open in a new window. 0 defaults to same window	

//END CUSTOMIZATION!!! ----------------------------------------------------------------------------------

function scripturize($text = '',$bible = NULL) {
global $scripturizer_default_translation;
	if (!isset($bible)) {
		$bible = $scripturizer_default_translation;
	}
	
    // skip everything within a hyperlink, a <pre> block, a <code> block, or a tag
    // we skip inside tags because something like <img src="nicodemus.jpg" alt="John 3:16"> should not be messed with
	$anchor_regex = '<a\s+href.*?<\/a>';
	$pre_regex = '<pre>.*<\/pre>';
	$code_regex = '<code>.*<\/code>';
	$other_plugin_regex= '\[bible\].*\[\/bible\]'; // for the ESV Wordpress plugin (out of courtesy)
	$other_plugin_block_regex='\[bibleblock\].*\[\/bibleblock\]'; // ditto
	$tag_regex = '<(?:[^<>\s]*)(?:\s[^<>]*){0,1}>'; // $tag_regex='<[^>]+>';
	$split_regex = "/((?:$anchor_regex)|(?:$pre_regex)|(?:$code_regex)|(?:$other_plugin_regex)|(?:$other_plugin_block_regex)|(?:$tag_regex))/i";
// $split_regex = "/((?:$anchor_regex)|(?:$pre_regex)|(?:$code_regex)|(?:$tag_regex))/i";	
	$parsed_text = preg_split($split_regex,$text,-1,PREG_SPLIT_DELIM_CAPTURE);
	$linked_text = '';

  while (list($key,$value) = each($parsed_text)) {
      if (preg_match($split_regex,$value)) {
         $linked_text .= $value; // if it is an HTML element or within a link, just leave it as is
      } else {
        $linked_text .= scripturizeAddLinks($value,$bible); // if it's text, parse it for Bible references
      }
  }

  return $linked_text;
}

function scripturizeAddLinks($text = '',$bible = NULL) {
global $scripturizer_translations, $scripturizer_default_translation;

	if (!isset($bible)) {
		$bible = $scripturizer_default_translation;
	}
	
    $volume_regex = '1|2|3|I|II|III|1st|2nd|3rd|First|Second|Third';

    $book_regex  = 'Genesis|Exodus|Leviticus|Numbers|Deuteronomy|Joshua|Judges|Ruth|Samuel|Kings|Chronicles|Ezra|Nehemiah|Esther';
    $book_regex .= '|Job|Psalms?|Proverbs?|Ecclesiastes|Songs? of Solomon|Song of Songs|Isaiah|Jeremiah|Lamentations|Ezekiel|Daniel|Hosea|Joel|Amos|Obadiah|Jonah|Micah|Nahum|Habakkuk|Zephaniah|Haggai|Zechariah|Malachi';
    $book_regex .= '|Mat+hew|Mark|Luke|John|Acts?|Acts of the Apostles|Romans|Corinthians|Galatians|Ephesians|Phil+ippians|Colossians|Thessalonians|Timothy|Titus|Philemon|Hebrews|James|Peter|Jude|Revelations?';

	// I split these into two different variables from Dean's original Perl code because I want to be able to have an optional period at the end of just the abbreviations

    $abbrev_regex  = 'Gen|Ex|Exo|Lev|Num|Nmb|Deut?|Josh?|Judg?|Jdg|Rut|Sam|Ki?n|Chr(?:on?)?|Ezr|Neh|Est';
    $abbrev_regex .= '|Jb|Psa?|Pr(?:ov?)?|Eccl?|Song?|Isa|Jer|Lam|Eze|Dan|Hos|Joe|Amo|Oba|Jon|Mic|Nah|Hab|Zeph?|Hag|Zech?|Mal';
    $abbrev_regex .= '|Mat+|Mr?k|Lu?k|Jh?n|Jo|Act|Rom|Cor|Gal|Eph|Col|Phil?|The?|Thess?|Tim|Tit|Phile|Heb|Ja?m|Pe?t|Ju?d|Rev';

    $book_regex='(?:'.$book_regex.')|(?:'.$abbrev_regex.')\.?';

    $verse_regex="\d{1,3}(?::\d{1,3})?(?:\s?(?:[-&,]\s?\d+))*";

	// non Bible Gateway translations are all together at the end to make it easier to maintain the list
	$translation_regex = implode('|',array_keys($scripturizer_translations)); // makes it look like 'NIV|KJV|ESV' etc

	// note that this will be executed as PHP code after substitution thanks to the /e at the end!
    $passage_regex = '/(?:('.$volume_regex.')\s)?('.$book_regex.')\s('.$verse_regex.')(?:\s?[,-]?\s?((?:'.$translation_regex.')|\s?\((?:'.$translation_regex.')\)))?/e';

    $replacement_regex = "scripturizeLinkReference('\\0','\\1','\\2','\\3','\\4','$bible')";

    $text=preg_replace($passage_regex,$replacement_regex,$text);

    return $text;
}

function scripturizeLinkReference($reference='',$volume='',$book='',$verse='',$translation='',$user_translation='') {
global $scripturizer_translations, $scripturizer_default_translation;

    if ($volume) {
       $volume = str_replace('III','3',$volume);
	   $volume = str_replace('Third','3',$volume);   
       $volume = str_replace('II','2',$volume);
	   $volume = str_replace('Second','2',$volume);      
       $volume = str_replace('I','1',$volume);
	   $volume = str_replace('First','1',$volume);      
       $volume = $volume{0}; // will remove st,nd,and rd (presupposes regex is correct)
    }
	
	//catch an obscure bug where a sentence like "The 3 of us went downtown" triggers a link to 1 Thess 3
	if (!strcmp(strtolower($book),"the") && $volume=='' ) {
		return $reference;
	}

   if(!$translation) {
         if (!$user_translation) {
             $translation = $scripturizer_default_translation;
         } else {
             $translation = $user_translation;
         }
   } else {
       $translation = trim($translation,' ()'); // strip out any parentheses that might have made it this far
   }

   // if necessary, just choose part of the verse reference to pass to the web interfaces
   // they wouldn't know what to do with John 5:1-2, 5, 10-13 so I just give them John 5:1-2
   // this doesn't work quite right with something like 1:5,6 - it gets chopped to 1:5 instead of converted to 1:5-6
   if ($verse) {
       $verse = strtok($verse,',& ');
   }

	//Build the title, depending on if the version begins with "The"
	$trans_full_name = $scripturizer_translations[$translation];
	if (preg_match('/The/',$trans_full_name)) {
		$title = 'View ' . $reference . ' in ' . $trans_full_name;
		}else{
			$title = 'View ' . $reference . ' in the ' . $trans_full_name;
	}
	//Build the link...
	if ($new_window == 1) $target = '_blank';
	$link = "http://biblegateway.com/bible?version=$translation&amp;passage=";
	$link = sprintf('<a href="%s%s" title="%s" target="%s" >%s</a>',$link,htmlentities(urlencode(trim("$volume $book $verse"))),$title, $target, trim($reference));
	
return $link;
}

function scripturizePost($post_ID) {
	global $bbdb, $bb_post;
	
	$post_id = get_post_id();
	
	$tableposts=$bbdb->posts;
	
    $postdata=$bbdb->get_row("SELECT * FROM $tableposts WHERE post_id={$post_ID}");

    $content = scripturize($postdata->post_text);

    $bbdb->query("UPDATE $tableposts SET post_text = '$content' WHERE post_id={$post_ID}");
    
    return $post_ID;
}

##### ADD ACTIONS AND FILTERS

//Dynamically changes the text upon delivery from the server
	add_filter('post_text','scripturize', 1);
?>