<?php
/*
Mini Track Admin functions
*/

// error_reporting(E_ALL);  ini_set("display_errors", 1);

function mini_track_display() {
if (!bb_current_user_can('administrate')) {return;}
global $mini_track, $mini_track_current, $mini_track_options, $bb_current_user;
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";  $output=""; $sort=0; $time=time();

if (isset($_GET['sort'])) {$sort=intval($_GET['sort']);} elseif (isset($mini_track_options['display_sort'])) {$sort=intval($mini_track_options['display_sort']);}
$url=$uri=$bb_uri."?mini_track_display"; if ($sort!=0) {$uri.="&sort=".$sort;};

if (isset($_GET['mini_track_reset'])) {mini_track_activation(); mini_track_init();}
elseif (isset($_GET['mini_track_ban']) && isset($mini_track[$_GET['mini_track_ban']])) {$mini_track[$_GET['mini_track_ban']]->ban=1; $mini_track[$_GET['mini_track_ban']]->ok=0; mini_track_save();}
elseif (isset($_GET['mini_track_unban']) && isset($mini_track[$_GET['mini_track_unban']])) {unset($mini_track[$_GET['mini_track_unban']]->ban); $mini_track[$_GET['mini_track_unban']]->ok=1; mini_track_save();}
bb_send_headers();
echo "<html><head><title>".count($mini_track)." Users Online &laquo; ".bb_get_option('name')."</title>
<meta http-equiv='refresh' content='".$mini_track_options['display_refresh_time'].";url=".$uri."' />
<style>".$mini_track_options['style']."</style>
<style>body {font: 66.66% Arial, san-serif; font-weight:normal; background:#ccc;}  .mini_track {text-align:left; display: inline; background:#eee; padding:1px 5px;}
table {margin-top:1em; border:1px solid #111; font-size:12px; clear:both;} table tr {line-height:200%; background:#fff;} table a {text-decoration:none;}
table td {padding:1px 2px; text-align:center; white-space:nowrap;} table .lasturl {text-align:left;} table th.lasturl {padding-left:5em;} table th span {color:#FFFFCF;}
table tr:hover {background: #d8dcf2;} table tr th, table tr th a {color:black; background: #aaa;  line-height:120%; font-size:15px; padding:2px; text-decoration:none;} 
table tr.alt {background: #eee;} .tiny {font-size:12px;} 
.idle {color:#666;} .inactive {color:#999;} .rbot,.bot {color:red;} .new,.guest {color:green;} .member, .guest, .referer, td.pages {font-size:14px;}
.num {font-family: monospaced; color:navy;} .lasturl div {padding-left: 5px; width:380px; white-space:nowrap; overflow; hidden;} </style> 
<script>window.onload=titlelink; function titlelink() {blank='_blank'; for (i=0;x=document.links[i];++i){if (!x.target) {x.target=blank}; if (!x.title.length) {x.title=x.href;}}};</script>
</head><body><div style='float:right;'>[<a onclick='return confirm(".'"Erase current data?"'.");' href='$bb_uri?mini_track_reset'><small>reset</small></a>]</div>";

mini_track(2);

$th=array(1=>"user",2=>"cc",3=>"ip",4=>"referer",5=>"pages",6=>"time online",7=>"last activity",8=>"status",9=>"last URL");
$extra=array(5=>" class=pages ",9=>" class=lasturl");
if (!$mini_track_options['geoip']) {unset($th[array_search("cc",$th)]);} 
$output.="<br clear=both /><table width='99%' cellpadding=1 cellspacing=1><tr><th>#</th>"; $abssort=abs($sort);
foreach ($th as $cell=>$name) {if ($cell==$abssort) {if ($sort>0) {$name.="&nbsp;<span>&uarr;</span>";} else {$name.= "&nbsp;<span>&darr;</span>";}}
$output.="<th".((isset($extra[$cell])) ? $extra[$cell] : "")."><a target=_self href='?mini_track_display&sort=".(($cell==$abssort)? -$sort : -$cell)."'>$name</a></th>";} 
$output.="</tr>"; echo $output; $output=""; $counter=0;

foreach ($mini_track as $key=>$value) { ++$counter;
$td[0][$counter]=$counter;	// line number (for sorting)

if ($value->id) {$td[1][$counter]="<a class=member href='$profile$value->id'>$value->name</a>";} 	// member profile link
elseif (isset($value->bot)) {$td[1][$counter]="<span class=rbot>".preg_replace("/[^A-Za-z_ ]+?/is","",$mini_track_options['bots'][$value->bot-1])."</span>";} else {$td[1][$counter]="<span class=guest>guest</span>";}

if ($mini_track_options['geoip']) {if ($mini_track_options['flags']) {$td[2][$counter]="<img alt='$value->cc' title='$value->cc' src='".$mini_track_options['flags'].strtolower($value->cc).".png'>";} else {$td[2][$counter]="$value->cc";}} // country

$td[3][$counter]="<a ".(($mini_track_options['debug']) ?" title='$value->debug' " : "")." href='?mini_track_ip=$value->ip'>$value->ip</a>";

if (isset($value->referer)) {$parse_url=parse_url($value->referer);	// referer  // (www[0-9]?\.)?([^.]\.)*([^.]+)(\.[A-Za-z]{2,4})(\.[A-Za-z]{2,4})
	$host=preg_match('#(?:^|\.)([a-z0-9]+\.(?:[a-z]{2,}|[a-z.]{5,6}))$#i', $parse_url['host'], $tmp) ? $tmp[1] : $parse_url['host']; // substr(ereg_replace("^(?:^|\.)([a-z0-9]+\.(?:[a-z]{2,}|[a-z.]{5,6}))$","\\1",$parse_url['host']),0,30); 
	$td[4][$counter]="<a class=referer href='$value->referer'>".$host."</a>";
} else {$td[4][$counter]="&nbsp;";}
 
$td[5][$counter]=intval($value->pages);									// page count
$td[6][$counter]="<span class=num>".($total=ceil(($value->time-$value->seen+1)/60))."</span> ".(($total==1 && $value->pages>1) ? "<span title='".(1+$value->time-$value->seen)." seconds'>minutes</span>" : "minutes");	// total activity time
$td[7][$counter]="<span class=num>".($last=ceil(($time-$value->time+1)/60))."</span> minutes ago"; 		// last activity time

$actual[6][$counter]=$value->time-$value->seen;
$actual[7][$counter]=$time-$value->time;

if (isset($value->ok) && $value->ok && isset($value->bot)) {$td[8][$counter]="<span class=new>verified</span>";}
elseif (isset($value->ban)) {$td[8][$counter]="<span class=bot>banned</span>  [<a href='$uri"."&mini_track_unban=$key'>x</a>]";}
elseif ($value->pages>9 && $bb_current_user->ID!=$value->id) {$td[8][$counter]="<a href='$uri"."&mini_track_ban=$key'>ban?</a>";}
elseif ($value->pages<3 && $total<2 && $last<2) {$td[8][$counter]="<span class=new>new</span>";}
elseif ($last>15) {$td[8][$counter]="<span class=inactive>inactive</span>";}
elseif ($last>5) {$td[8][$counter]="<span class=idle>idle</span>";}
else {$td[8][$counter]="&nbsp;";}

$td[9][$counter]="<div style='overflow:hidden;'><a href='".($url=urldecode($value->url))."'>$url</a></div>";	// last url

}
// sort would go here
if ($sort!=0)  {
if (isset($actual[abs($sort)])) {asort($actual[abs($sort)]); $td[0]=array_keys($actual[abs($sort)]);} else {asort($td[abs($sort)]); $td[0]=array_keys($td[abs($sort)]);}
}
if ($sort<1) {$td[0]=array_reverse($td[0],true);}

$counter=0; 
foreach ($td[0] as $row) { ++$counter;
$output.="<tr".(($counter % 2) ? " class=alt" : "")."><td align=right class=tiny> ".$counter." </td>";		// line number
foreach ($th as $cell=>$name) {$output.="<td".((isset($extra[$cell])) ? $extra[$cell] : "").">".$td[$cell][$row]."</td>";}
$output.="</tr>";
}
echo $output;
echo "</table><br /><p class=tiny align=right>please <a href='http://amazon.com/paypage/P2FBORKDEFQIVM'>donate</a> to continue development of<br />this and other plugins for bbPress by _ck_</p></body></html>";
exit();
}

function knatsort(&$karr){
asort($karr);
return;
    $kkeyarr = array_keys($karr);
    natsort($kkeyarr);
    $ksortedarr = array();
    foreach($kkeyarr as $kcurrkey){
        $ksortedarr[$kcurrkey] = $karr[$kcurrkey];
    }
    $karr = $ksortedarr;
    return true;
    
}

function mini_track_ip() {
if (!bb_current_user_can('administrate') || !$_GET['mini_track_ip']) {return;}
$ip=$_GET['mini_track_ip']; $rdns=gethostbyaddr($ip); if ($rdns==$ip) {$rdns="(no rDNS)";}
bb_send_headers();
echo "<html><pre><h2>IP ".$ip."</h2><h3>".$rdns."</h3>"; 
$data=mini_track_ip_lookup($ip);
if (!isset($data) || !is_array($data)) {echo "<small>reloading...</small><br />"; sleep(1); $data=mini_track_ip_lookup($ip);}  // try a 2nd time before giving up
if (isset($data) && is_array($data)) { 
	foreach ($data as $key=>$value) {
		if (eregi("abuse|tech|nettype|comment|remark|ReferralServer|signature|auth|encryption",$key)===false) {
			if (intval($key)===$key) {echo "$value <br />";} else {echo "$key: <b>$value</b><br />";}
		}
	}
} else {echo "lookup error, <a href='?mini_track_ip=$ip'>try again?</a>";}
exit();
}

function mini_track_ip_lookup($ip,$server=0) {
if (!bb_current_user_can('administrate') || !$_GET['mini_track_ip']) {return;}
// error_reporting(E_ALL);  ini_set("display_errors", 1);	// debug
$host=array('ws.arin.net','wq.apnic.net','www.db.ripe.net','lacnic.net','www.afrinic.net');
$keyword=array('arin.net','apnic.net','ripe.net','lacnic.net','afrinic.net');
$path=array('/whois/?queryinput=','/apnic-bin/whois.pl?searchtext=','/whois/?form_type=simple&searchtext=','/cgi-bin/lacnic/whois?query=','/cgi-bin/whois?form_type=simple&searchtext=');
do {unset($data); $data="";
if ($fp = fsockopen ($host[$server], 80, &$errno, &$errstr, 10)) {
	$request = "GET $path[$server]$ip HTTP/1.0\r\nHost: $host[$server]\r\nUser-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\r\n\r\n"; 
	$page=''; fputs ($fp, $request); while (!feof($fp)) {$page.=fgets ($fp,1024);} fclose ($fp); 	// echo $page; // debug
	preg_match("/\<pre\>(.*)\<\/pre\>/sim",$page,$temp); $lines=explode("\n",strip_tags($temp[0]));	// print_r($temp); // debug
	foreach ($lines as $line) {$line=trim($line); 
		if (!empty($line) && !ereg("^(\#|\%)",$line)) {if (strpos($line,":")) {$temp=explode(":",$line,2); $data[trim($temp[0])] = trim($temp[1]);} else {$data[]=$line;}}
	}
} else {$data['error'] = "$errstr ($errno)\n";}         
$server=0; for ($i = 1; $i <= count($host); $i++){if (isset($data['ReferralServer']) && strpos($data['ReferralServer'],$keyword[$i])){$server=$i;break;}}
} while ($server>0);
return $data;
}

function mini_track_activation() {
global $mini_track, $mini_track_statistics, $mini_track_done; unset($mini_track_done); 
$mini_track=array(); @bb_update_option('mini_track',$mini_track);
$mini_track_statistics=bb_get_option('mini_track_statistics'); 
if (empty($mini_track_statistics)) {$mini_track_statistics=array(); @bb_update_option('mini_track_statistics',$mini_track_statistics);}
}
?>