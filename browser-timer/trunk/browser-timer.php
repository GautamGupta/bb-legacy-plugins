<?php
/*
Plugin Name: Browser Timer
Description:  improve your forums by learning how long it really takes for various users to see your bbPress pages
Plugin URI:  http://bbpress.org/plugins/topic/browser-timer
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2
*/
			//  change this to a filename in a directory set to chmod 777
$browsertimer['log']=dirname($_SERVER['DOCUMENT_ROOT']).'/browsertimer/browsertimer.log';  

$browsertimer['filter']=true;		// false / true = remove entries longer than 20 seconds or less than 100ms which are typically bogus

$browsertimer['pages']=false;		// array('front-page','topic-page','view-page');	// work on these pages, set false for all

$browsertimer['geoip']=false;		// false = country lookup off,  or
						// 'ip2c' = download IP2C:   http://admin.firestats.cc/ccount/click.php?id=74
						//   and copy  ip2c.php  and ip-to-country.bin   into the same directory as  browsertimer.php


/*  	       stop editing here 	       */

// todo:  optionally return result to user in their footer,  also maybe a better log analyser

browsertimer();

function browsertimer() {	
	if (isset($_GET['browsertimer'])) {add_action('bb_init','browsertimer_log');}			// bb_init
	if (empty($_GET['bt32'])) {add_action('bb_foot','browsertimer_foot',9999); return;}		// bb_foot
    	ignore_user_abort(true);
    	header('Connection: close');       
	header('Content-Type: image/gif');
	header('Content-Length: 35');	
	echo pack('H*','47494638396101000100800100FFFFFF0000002C00000000010001000002024401003B');
	flush();

	global $browsertimer, $bb_timestart, $bbdb;
	$location=bb_get_location(); if (!empty($browsertimer['pages']) && !in_array($location,$browsertimer['pages'])) {exit;}

	$bt=$_GET['bt32']; if (strlen($bt)<8) {exit;}	
	$crc=substr($bt,0,4); $bt=substr($bt,4);
	if ($crc!=substr('0000'.dechex(crc32($bt.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])+0),-4)) {exit;}
	$bt=hexdec($bt);
	$bt=round($bb_timestart-strtotime(gmdate('Y-m-d').' 00:00:01 +0000'),4)*10000-$bt;
	$bt=round($bt/10000,3);
	if (!empty($browsertimer['filter']) && ($bt<0.1 || $bt>19.9)) {exit;}	
	$bt=sprintf('%01.3f', $bt);

	$date=date('Y-m-d H:i:s');	// or use gmdate instead of server time
	if ($location=='view-page') {$location=isset($_GET['view']) ? $_GET['view'] : get_path();}
	$page=bb_get_uri_page(); if (!empty($page) && (int) $page>1) {$page='-'.$page;} else {$page='';}
	$ip=$_SERVER['REMOTE_ADDR'];
	if ($browsertimer['geoip']=='ip2c') {require_once('ip2c.php');  $ip2c = new ip2country(rtrim(dirname(__FILE__),' /\\').'/ip-to-country.bin'); $cc=$ip2c->get_country($ip); $cc = $cc==false ? '??' : $cc['id2'];}
	elseif ($browsertimer['geoip']=='maxmind') {$cc=$bbdb->get_var("SELECT cc FROM maxmind WHERE start<= inet_aton('$ip') ORDER BY start DESC LIMIT 1");}
	$cc=empty($cc) ? '??' : strtoupper($cc);
	
	$agent=$_SERVER['HTTP_USER_AGENT']; 	// sanitize UA to only interesting stuff
	$agent=preg_replace('/(\bU\;|OfficeLive|iPhone\; CPU|macintosh\;|windows\;|qq.+ [0-9]+\;|compatible;|mozilla|gecko|OfficeLive.+\;|\.NET CLR |KHTML\, like )(\s|\S+)/sim','',$agent);
	$agent=str_replace(array('NT 4.10','NT 4.90','NT 5.0','NT 5.1','NT 5.2','NT 6.0','NT 6.1','NT 6.2'),array('98','ME','2000','XP','XP 64','Vista','7','8'),$agent);
	$agent=preg_replace('/(\))/','; ',$agent);		
	$agent=preg_replace('/[^A-Za-z0-9\s\:\;\!\?\/\\\<\>\.\,\'\"\@\-]/','',$agent);
	$agent=preg_replace('/(\s+)/',' ',$agent);
	$agent=addslashes(substr(trim($agent),0,85));	
	
	$output="$date $ip $cc $bt $location$page $agent\n";		
	if (!empty($browsertimer['log']) && $fh = fopen($browsertimer['log'], 'ab')) {fwrite($fh, $output);fclose($fh);}	
	exit;
}

function browsertimer_foot() {	
	global $browsertimer, $bb_timestart; 
	$location=bb_get_location(); if (!empty($browsertimer['pages']) && !in_array($location,$browsertimer['pages'])) {return;}	
	$bt=dechex(round($bb_timestart-strtotime(gmdate('Y-m-d',time()).' 00:00:01 +0000'),4)*10000);
	$crc=substr('0000'.dechex(crc32($bt.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])+0),-4);
?>
	<script type="text/javascript" defer="defer">			
	function bt32() {var i=new Image(); i.src="<?php echo str_replace('/','"+"/"+"',add_query_arg('bt32',$crc.$bt)); ?>";}
	window.addEventListener ? window.addEventListener("load",bt32,false) : window.attachEvent && window.attachEvent("onload",bt32);
	</script>
<?php
}

function browsertimer_log() {
	if (!bb_current_user_can('administrate')) {return;}
	global $browsertimer;
	if ($f=fopen($browsertimer['log'], "r")) {fseek($f, -16384, SEEK_END); $data = fread($f, 16384); fclose($f);} else {die('log open error');}	
	if (preg_match_all("@(.*?[\r]?\n)@", $data, $lines)) {unset($data); $lines=$lines[0]; $count=count($lines);} else {die('log is empty error');}
	header('refresh:180'); 
	echo "<html><head><title>Browsertimer</title><style>
	body {font-family:Monaco,\"Lucida Console\",monospace;font-size:12px; white-space:pre;}
	.alt {background:#fbfbfb;} .c0{color:black;} .c1{color:#888888;} .c2{color:navy;} .c3{color:blue;} .c4{color:red;} .c5{color:green;} .c6{color:#888888;}
	</style></head><body>";
	while ($count>1) { --$count;
		$line=trim($lines[$count]); $line=explode(' ',$line,7); $line[2]=str_pad($line[2],15);
		if (!isset($line[6])) {$line[6]=''; if (strlen($line[3]>2)) {$line[6]=$line[5]; $line[5]=$line[4]; $line[4]=$line[3]; $line[3]='';}}
		$hot=intval((float) $line[4]*64)+32; if ($hot>255) {$hot=255;} $c4='#'.dechex($hot).'0000';
		$output= $count%2 ? '<div>' : '<div class="alt">';
		foreach ($line as $key=>$segment) {$output.="<span ".($key==4 ? "style='color:$c4'" : "class='c$key'").">$segment</span> ";} 
		echo $output,"</div>";	
	}
	exit;
}

?>