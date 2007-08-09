<?php
/*
Plugin Name: bb-benchmark 
Plugin URI: http://CKon.WordPress.com
Description: Prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators. Based on Jerome Lavigne's Query Diagnostics for WordPress.
Author: _ck_
Author URI: http://CKon.WordPress.com
Version: 0.12
*/
/* 
INSTRUCTIONS:

1. put  define('SAVEQUERIES', true);   into your config.php
2. install & activate plugin   (editing config.php no longer required)
3. do a "view source" on any bbpress page to see hidden results at bottom (visible to administrators only)

History:

0.10	: first public release
0.11	: improved load detection for php in safe mode or without shell access if using PHP5
0.12	: visual output cleanup & reminder to add "define('SAVEQUERIES', true);" 

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
	echo "query count: ".$bbdb->num_queries." \n\n";        		
	
	if (!SAVEQUERIES) :
	
	echo "To see full bb-benchmark results here put the following line into your config.php\ndefine('SAVEQUERIES', true);\n\n -->";

	else :
	
	$test=$bbdb->queries; foreach (array_keys($test) as $key) {$qtotal+=$test[$key][1]; if ($test[$key][1]>$test[$qslowkey][1]) {$qslowkey=$key;} }
	echo "total query time: ".round($qtotal,4)." seconds \n\n";         	
        	echo "page rendering time: ".(round($timer_stop-$qtotal,4))." seconds \n\n";
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

?>