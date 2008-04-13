<?php
// **************************
// *      Ziplib 0.3.1      *
// *    Created by: Seven   *
// **************************
//
// Simple set of functions to create a zipfile
// according to the PKWare specifications, which
// can be found on:
// http://www.pkware.com/products/enterprise/white_papers/
// ©1989-2003, PKWARE Inc.

// functions in here start with zl, which comes from ZipLib ;)

// requires dostime.php
$settings['offset'] = 1; // Holland ;)

// *********************
// * Conversion helper *
// * Created by: Seven *
// *********************

// Since ZIP-files like using Dostime, i've written this small helper function
// set, making it easier to read and write Dostime

// clear documentation about Dostime:
// www.vsft.com/hal/dostime.htm
// thanks Vilma Software! :)


// format according to MSDN:
// 5 bytes for seconds divided by 2, 6 bytes for minutes and 5 bytes for the hours :)

// write 16bit dostime output

function dostime_get($offset) {

	// take about 2 kilograms of hours
	$doshour = gmdate("G") + 1;
	$doshour = $doshour + $offset;

		// we won't be worrying about the date, that'll be the next function ;)
	if($doshour > 23) $doshour = $doshour - 24;
	if($doshour < 0) $doshour = $doshour + 24;

	$doshour = $doshour * pow(2,11);

	// mix it with about 250 grams of minutes
	$dosminute = gmdate("i") * pow(2,5);

	// then add a pinch of seconds
	$dossecond = round(gmdate("s") / 2);

	// mix them together and whack them in the oven for about 20 minutes
	$dostime = $doshour+$dosminute+$dossecond;

	// and it's ready to be served! :)
	return $dostime;

	// "whack" is a registered trademark of Jamie Oliver. All rights reserved.
};

// format according to MSDN:
// 5 bytes for days, 4 for month, 7 for years from 1980... can go for 128 years then, so warn me in 2108 ;)

function dosdate_get($offset) {
	// date
	$dosyear = (gmdate("Y") - 1980);
	$dosmonth = gmdate("m");
	$dosday = gmdate("j");

	// checking if date is valid
	// starting with... is the day too high after applying offset?
	if(gmdate("G") + $offset > 23) {
		$dosday++;
		if($dosday > date("t")) {
			$dosday = 1;
			$dosmonth++;
			if($dosmonth > 12) {
				$dosmonth = 1;
				$dosyear++;
			};
		};
	};

	// then, is the day too low after applying offset?
	if(gmdate("G") + 1 + $offset < 0) { // +1 to fix the erm... standard -1 offset this pc has... strange thou :p
		$dosday = $dosday - 1;
		if($dosday < 1) {
			// ok, little helper array, containing the months that have 30 days:)
			$dirtydays = array(4,6,9,11);
			if(in_array($dirtydays,$dosmonth - 1)) { 	// is it one month after one of the feared months, added in the array above?
				$dosday = 30;
			} elseif ($dosmonth == 3) { 			// is it march then?
				$dosday = 28+date("L");
			} else {					// then the month before this one must have 31 days :)
				$dosday = 31;
			};

			$dosmonth --;
			if($dosmonth < 1){
				$dosmonth == 12;
				$dosyear --; // i aint checking this one, we're not creating files b4 1980 anyway ;)
			};
		};
	};

	// wow, that took me some thinking, let's go to an easier part, returning!
	$dosyear = $dosyear * pow(2,9);
	$dosmonth = $dosmonth * pow(2,5);
	return $dosyear+$dosmonth+$dosday;
}

// Now this process must be reversed aswell. I think the most easy method for this is just returning an array with data.

function dostime_return($dostime) {
	$dostime = decbin(ascii2dec($dostime)); //looks nasty, but hey, it works ;)
	$dostime = str_pad($dostime,16,"0",STR_PAD_LEFT);

	// retreiving the needed data... 5-6-5 was the format
	// *** Warning! *** Waarschuwing! *** Achtung! ***
	// I don't know if this works on little endian machines the way it works on big-endian ones
	// So let's hope for the best

	$return['hours'] = substr($dostime,0,5);
	$return['minutes'] = substr($dostime,5,6);
	$return['seconds'] = substr($dostime,11,5);

	unset($dostime);

	// now processing the info to the right format
	$return['hours'] = bindec($return['hours']);
	$return['minutes'] = bindec($return['minutes']);
	$return['seconds'] = bindec($return['seconds']) * 2;
	return $return;
}

// this is mostly a copy of dostime_return
function dosdate_return($dosdate) {
	$dosdate = decbin(ascii2dec($dosdate)); //looks nasty, but hey, it works ;)
	$dosdate = str_pad($dosdate,16,"0",STR_PAD_LEFT);

	// retreiving the needed data... 5-4-7 was the format
	// *** Warning! *** Waarschuwing! *** Achtung! ***
	// I don't know if this works on little endian machines the way it works on big-endian ones
	// So let's hope for the best

	$return['year'] = substr($dosdate,0,7);
	$return['month'] = substr($dosdate,7,4);
	$return['day'] = substr($dosdate,11,5);

	unset($dosdate);

	// now processing the info to the right format
	$return['day'] = bindec($return['day']);
	$return['month'] = bindec($return['month']);
	$return['year'] = bindec($return['year']) + 1980;
	return $return;
}

// Also useful is this ascii2dec convertor, will be a well used conversion when reading a zipfile
// simple but powerful :)

function ascii2dec($input) {
	$end = strlen($input);
	$multiplier = 1;
	for($i=0; $i < $end; $i++) {
		$output = $output + (ord($input[$i]) * $multiplier); // I think Max wants some credit for this [$i] method
		$multiplier = $multiplier * 256;
	}
	unset ($input);
	return $output;
}

// Extension to content-type header conversion.
function ext2cth($filename) {
	$filename = explode(".",$filename);
	$extension = array_pop($filename);

	// I kinda need a gigantic array for this, i'll do this for now by including this array and setting a little var so I know it's
	// been included

	if(!$types_is_included){
		require ("./zip-filetypes.php");
		$types_is_included = TRUE;
	}

	$extension = strtolower($extension);
	$filetype = $type[$extension];
	if(empty($filetype)) {
		$filetype = $type['default'];
	}
	return $filetype;	
}

class Ziplib {

var $archive;
var $archive_fileinfo = array();
var $archive_filecount;
var $compr_lvl_last;

function zl_compress($data,$level = "",$type = "") {
	if($type != "g" && $type != "b" && $type != "n") {
		// Darnit, they forgot to set the type. Assuming gZip if any compression
		if($level >= 1 && $level <= 9) $type = "g";
		elseif($level > 9) die("Compression level set too high");
		else $type = "n";
	}
		
	if($type == "g") {
		$this->compr_lvl_last = 8;
		RETURN gzdeflate($data,$level);
	} elseif($type == "b") {
		$this->compr_lvl_last = 12;
		RETURN bzcompress($data,$level);
	} else {
		$this->compr_lvl_last = 0;
		RETURN $data;
	}
}

function zl_add_file($data,$filename,$comp = "") {
	global $settings;
	// if we already created a file, we'll make sure it'll be there in the coming zipfile ;)

	// first, checking some data
	if(strlen($filename) > pow(2,16)-1) die("Filename $filename too long"); // ooh, dirty... dieing, will change later
	if(strlen($data) > pow(2,32)-1) die("File $filename larger then 2GB, cannot continue"); // another one, naughty me ;)

	// $comp has a special format. the first character tells me about the compression, the second one represents the level
	if(strlen($comp) == 1) {
		// they still use the old method, assuming gzip
		
		$comp_type = "n";
		$comp_lvl = 0;
		if($comp >= 1 && $comp <= 9) {
			$comp_type = "g";
			$comp_lvl = $comp;
		}
	} else {
		$comp_lvl = 5;
		$comp_type = "n";
		// hmmm, the new method. Is it valid?
		if ($comp[0] == "b" OR $comp[0] == "g" OR $comp[0] == "n") $comp_type = $comp[0];
		if (preg_match("/[0-9]/i",$comp[1])) $comp_lvl = $comp[1];
	}

	// ok, let's get this bitch tidy:
	// first adding a file
	$compressed = $this->zl_compress($data,$comp_lvl,$comp_type);
	$uncompressed = strlen($data);

	$newfile = "\x50\x4b\x03\x04";				// Header
	$newfile .="\x00\x00";					// Version needed to extract
	$newfile .="\x00\x00";					// general purpose bit flag
	$newfile .=pack("v",$this->compr_lvl_last);		// compression method
	$newfile .=pack("v",dostime_get($settings['offset']));			// last mod file time
	$newfile .=pack("v",dosdate_get($settings['offset']));			// last mod file date
	$newfile .=pack("V",crc32($data));			// CRC32
	$newfile .=pack("V",strlen($compressed));		// compressed filesize
	$newfile .=pack("V",$uncompressed);			// uncompressed filesize
	$newfile .=pack("v",strlen($filename));			// length of filename
	$newfile .="\x00\x00";					// some sort of extra field ;)
	$newfile .=$filename;
	$newfile .=$compressed;

	$this->archive .= $newfile;


	// some 'statistics' for this file ;)
	$this->archive_filecount++;
	$idf = $this->archive_filecount - 1;
	$this->archive_fileinfo[$this->archive_filecount]['comp_type'] = $this->compr_lvl_last;
	$this->archive_fileinfo[$this->archive_filecount]['size'] = strlen($data);
	$this->archive_fileinfo[$this->archive_filecount]['size_comp'] = strlen($compressed);
	$this->archive_fileinfo[$this->archive_filecount]['pkg_size'] = strlen($newfile);
	if(!empty($this->archive_fileinfo[$idf]['local_stats_pointer'])) {
		$this->archive_fileinfo[$this->archive_filecount]['local_stats_pointer'] = 
		$this->archive_fileinfo[$idf]['local_stats_pointer'] +
		$this->archive_fileinfo[$idf]['pkg_size'] + 1; // HACKERDIEHACK! only way to get local_stats_pointer to '0' (for now) in zl_pack
	} else {
		$this->archive_fileinfo[$this->archive_filecount]['local_stats_pointer'] = 1;
	}
	$this->archive_fileinfo[$this->archive_filecount]['name'] = $filename;
	$this->archive_fileinfo[$this->archive_filecount]['crc32'] = crc32($data);
	unset($file,$compressed); // to avoid having data in our memory double ;)
	RETURN TRUE;
}

 function zl_pack($comment) {
	global $settings;
	if(strlen($comment) > pow(2,16)-1) die("Comment too long"); // that's 3

	// now the central directory structure start
	for($x=1;$x <= $this->archive_filecount;$x++) {
		$file_stats = $this->archive_fileinfo[$x];
		$cdss .= "\x50\x4b\x01\x02";			// Header
		$cdss .="\x00\x00";				// version made by
		$cdss .="\x00\x00";				// version needed to extract
		$cdss .="\x00\x00";				// general purpose bit flag
		$cdss .=pack("v",$file_stats['comp_type']);	// compression method
		$cdss .=pack("v",dostime_get($settings['offset']));		// last mod file time
		$cdss .=pack("v",dosdate_get($settings['offset']));		// last mod file date
		$cdss .=pack("V",$file_stats['crc32']);		// CRC32
		$cdss .=pack("V",$file_stats['size_comp']);	// compressed size
		$cdss .=pack("V",$file_stats['size']);		// uncompressed size
		$cdss .=pack("v",strlen($file_stats['name']));	// file name length
		$cdss .="\x00\x00";				// extra field length
		$cdss .="\x00\x00";				// file comment length
		$cdss .="\x00\x00";				// disk number start
		$cdss .="\x00\x00";				// internal file attributes
		$cdss .="\x00\x00\x00\x00";			// external file attributes
		$cdss .=pack("V",$file_stats['local_stats_pointer']-$x);	// relative offset of local header
										// aka: The local_stats_pointer hack: part 2, see above
		$cdss .=$file_stats['name'];
	}

	// and final, the ending central directory structure! "WHOO HOOW!" (©Blur, 1998)
	$cdse = "\x50\x4b\x05\x06";			// Header
	$cdse .="\x00\x00";				// number of this disk
	$cdse .="\x00\x00";				// number of the disk with the start of the central directory
	$cdse .=pack("v",$this->archive_filecount);	// total number of entries in the central directory on this disk
	$cdse .=pack("v",$this->archive_filecount);	// total number of entries in the central directory
	$cdse .=pack("V",strlen($cdss));		// size of the central directory
	$cdse .=pack("V",strlen($this->archive));	// offset of start of central directory with respect to the starting disk number
	$cdse .=pack("v",strlen($comment));		// .ZIP file comment length
	$cdse .=$comment;
	
	return $this->archive.$cdss.$cdse;
}

 function zl_index_file($file) {
	$fp = @fopen($file,"rb");
	// ok, as we don't know what the exact position of everything is, we'll have to "guess" using the default sizes
	//and set values in the zipfile. Basicly this means I have to go through the entire file, could take some time.
	//Let's see if I can create an algorithm powerful enough.
	if(!$fp) die("File empty");
	$continue = 1;
	$file_count = 0;

	while($continue) {
		$content = fread($fp,30);
		$id = substr($content,0,4);
		if ($id == "\x50\x4b\x03\x04") {
			// the method used is quite simple, load a file in the memory, and walk through several parts of it using substr
			// As the PKZip format uses mostly fixed sizes for information, this isn't too hard to implement
			// First I want everything tested, before I start giving this function a nice place in the class
			$temp[$file_count]['file-size'] = ascii2dec(substr($content,18,4));
			$temp[$file_count]['filename-size'] = ascii2dec(substr($content,26,2));
			$temp[$file_count]['compression-type'] = ascii2dec(substr($content,8,2));
			$temp[$file_count]['crc'] = ascii2dec(substr($content,14,4));
			$temp[$file_count]['dostime'] = dostime_return(substr($content,10,2));
			$temp[$file_count]['dosdate'] = dosdate_return(substr($content,12,2));

			$temp[$file_count]['filename'] = fread($fp,$temp[$file_count]['filename-size']);

			// As the Zip format does not include Content type headers, I'll create a nice little array with 
			// extension/content type, and a small function to retreive it
			$temp[$file_count]['file-type'] = ext2cth($temp[$file_count]['filename']);
			$temp[$file_count]['content'] = fread($fp,$temp[$file_count]['file-size']);

			if ($temp[$file_count]['compression-type'] != 0 AND $temp[$file_count]['compression-type'] != 8 AND $temp[$file_count]['compression-type'] != 12) {
				$temp[$file_count]['lasterror'] = "Compression type not supported";
			} else {
				if($temp[$file_count]['compression-type'] == 8) {
					$temp[$file_count]['content'] = gzinflate($temp[$file_count]['content']);
				} elseif ($temp[$file_count]['compression-type'] == 12) {
					$temp[$file_count]['content'] = bzdecompress($temp[$file_count]['content']);
				}
				$verify = crc32($temp[$file_count]['content']);
				if ($verify != $temp[$file_count]['crc']) {
					$temp[$file_count]['lasterror'] = "CRC did not match, possibly this zipfile is damaged";
				}
			}
			$file_count++;
		} else {
			$continue = 0;	
		}

	}
	fclose($fp);
	unset($fp,$content,$file_count);
	return $temp;
}
}
?>
