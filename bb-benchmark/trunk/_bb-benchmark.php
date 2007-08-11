<?php
/*
Plugin Name: bb-benchmark 
Plugin URI: http://bbpress.org/plugins/topic/65
Description: Prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators. Based on Jerome Lavigne's Query Diagnostics for WordPress.
Author: _ck_
Author URI: http://CKon.WordPress.com
Version: 0.15
*/
/* 
INSTRUCTIONS:

1. put  define('SAVEQUERIES', true);   into your config.php
2. install plugin  (leading underscore in filename means it auto-activates, auto-loads in bbPress)
3. do a "view source" on any bbpress page to see hidden results at bottom (visible to administrators only)

History:

0.10	: first public release
0.11	: improved load detection for php in safe mode or without shell access if using PHP5
0.12	: visual output cleanup & reminder to add "define('SAVEQUERIES', true);" 
0.13	: breakdown by template pre-load
0.14	: additional sections timed (plugins loaded)
0.15	: switched to auto-load plugin (leading underscore) to better time main plugins loading
*/

function bb_benchmark_output() {
if (bb_current_user_can( 'administrate' ) )  : 
		$timer_stop=bb_timer_stop(0);	
		global $bbdb;	        	
        	
    	echo "<!-- \n === benchmark & query results === \n\n"; 
	    	    	  		   	    	       	
        	if (function_exists("shell_exec")  && $test=@shell_exec("uptime")) {echo $test."\n\n";}
	elseif (function_exists("sys_getloadavg")) {
	$test=sys_getloadavg(); if (is_array($test)) {foreach (array_keys($test) as $key) {$test[$key]=round($test[$key],2);} echo "load average: ".implode(", ",$test)."\n\n";}}  
        	
        	echo "total page time: ".round($timer_stop,4)." seconds.\n\n";        
        	
	echo "time to reach each section: \n"; global $bb_benchmark_time;
	foreach (array_keys($bb_benchmark_time) as $bb_btime) {
	echo "   ".$bb_btime." = ".$bb_benchmark_time[$bb_btime]."\n";
	}   echo "\n";     	
        	
	if (!SAVEQUERIES) :
	
	echo "total query count: ".$bbdb->num_queries." \n\n";        		
	echo "To see full bb-benchmark results here put the following line into your config.php\ndefine('SAVEQUERIES', true);\n\n -->";

	else :
	
	$test=$bbdb->queries; foreach (array_keys($test) as $key) {$qtotal+=$test[$key][1]; if ($test[$key][1]>$test[$qslowkey][1]) {$qslowkey=$key;} }
	echo "time to render page: ".(round($timer_stop-$qtotal,4))." seconds (query time subtracted)\n\n";
	echo "total query count: ".$bbdb->num_queries." \n\n";        		
	echo "total query time: ".round($qtotal,4)." seconds \n\n";         	        	
       	echo "slowest call was # ".($qslowkey+1)." : ".round($test[$qslowkey][1],4)." seconds\n".$test[$qslowkey][0]."\n\n";
			
	if (phpversion() >5.0 && function_exists(memory_get_peak_usage()) && function_exists(memory_get_usage())) {
	echo "\n\n === memory usage === \n";
	echo "peak memory ".memory_get_peak_usage()." \n\n";
	echo "total memory ".memory_get_usage()." \n\n"; 
	}
	
	// echo " === resource usage === \n\n";
	// print_r (getrusage());		
	
	echo " === mysql queries used === \n\n";		
	
	// print_r($bbdb->queries);	
	$test=$bbdb->queries; foreach (array_keys($test) as $key) {echo " # ".($key+1)." : ".round($test[$key][1],4)." seconds\n".$test[$key][0]."\n\n";}
			
	echo "-->";
		
	endif;

endif;	
}
add_action('bb_foot', 'bb_benchmark_output');

// global $bb_current_user;  if ($bb_current_user->data->bb_capabilities['keymaster']) :

function bb_benchmark_template_timer($template='',$file='') {
global $bb_benchmark_time;
if (!$file) {$test=intval(count($bb_benchmark_time)); 
	switch ($test) {
		case 0:$file="bb_underscore_plugins_loaded";break;
		case 1:$file="bb_plugins_loaded";break;
		case 2:$file="bb_init";break;
		case 3:$file="bb_index.php_pre_db";break;
		case 4:$file="bb_index.php";break;
	}
}
// $bb_benchmark_time[$file] = // bb_timer_stop(0);  // array_sum(explode(" ",microtime()));
global $bb_timestart, $timeend;
$mtime = explode(' ', microtime());
$timeend = $mtime[1] + $mtime[0];
$bb_benchmark_time[$file] = sprintf ("%6.3f",$timeend - $bb_timestart);
return $template;
}
add_filter( 'bb_template','bb_benchmark_template_timer',1,2);
add_action( 'bb_underscore_plugins_loaded', 'bb_benchmark_template_timer' );
add_action( 'bb_plugins_loaded', 'bb_benchmark_template_timer' );
add_action( 'bb_init', 'bb_benchmark_template_timer' );
add_action( 'bb_index.php_pre_db', 'bb_benchmark_template_timer' );
add_action( 'bb_index.php', 'bb_benchmark_template_timer' );

?>