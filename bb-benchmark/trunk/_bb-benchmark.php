<?php
/*
Plugin Name: bb-benchmark 
Plugin URI: http://bbpress.org/plugins/topic/65
Description: Prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators. Inspired by Jerome Lavigne's Query Diagnostics for WordPress.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.2.4

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/

Instructions: 
1. into your bb-config.php put:  define('SAVEQUERIES', true);   
2. install plugin   (no settings or template editing required)
3. do a "view source" on any bbpress page to see hidden results at bottom (visible to logged-in administrators only)
*/

bb_benchmark_template_timer('bb-settings.php','bb-settings.php');
$hooks=array('bb_template','bb_underscore_plugins_loaded','bb_plugins_loaded','bb_init','bb_send_headers','bb_index.php_pre_db','bb_index.php','bb_head','bb_foot','bb_admin_footer','bb_shutdown');
foreach ($hooks as $hook) {add_action($hook,'bb_benchmark_template_timer',1,2);}
add_action('bb_shutdown', 'bb_benchmark_output',999);

function bb_benchmark_output() {
	if (!bb_current_user_can('administrate')) {return;}

	$timer_stop=bb_timer_stop(0);	
	global $bb_benchmark_time, $bb_benchmark_queries, $bb_benchmark_files, $bbdb;	 
	$totalsize=$previous_time=$previous_queries=$previous_files=0;
	
	if (defined('BB_IS_ADMIN') && BB_IS_ADMIN) {echo '<div style="margin-top:-0.9em;text-align:center;">'.$bbdb->num_queries." queries </div>";}
        	
    	echo "\n<!-- \n === benchmark & query results === \n\n"; 
	    	    	  		   	    	       	
        	if (function_exists('shell_exec')  && $test=@shell_exec('uptime')) {echo $test."\n";}
	elseif (function_exists('sys_getloadavg')) {
	$test=sys_getloadavg(); if (is_array($test)) {foreach (array_keys($test) as $key) {$test[$key]=round($test[$key],2);} echo "load average: ".implode(", ",$test)."\n";}}  
        	
	if (function_exists('memory_get_peak_usage') && function_exists('memory_get_usage')) {
		$peak=round((memory_get_peak_usage()/1024)/1024,3);
		$current=round((memory_get_usage()/1024)/1024,3);	
		echo " == memory usage == \n\n";
		if (!empty($peak)) {echo str_pad("peak:",9).sprintf('%1.3f ',$peak)."MB\n";}
		if (!empty($current)) {echo str_pad("current:",9).sprintf('%1.3f ',$current)."MB\n";}	
		echo "\n";
	} else {
	/*  for windows - to do
    	$output = array(); exec('tasklist /FI "PID eq '.getmypid().'" /FO LIST', $output ); $peak=preg_replace( '/[^0-9]/', '', $output[5] ) * 1024;
    	*/
    	}
        	
	echo " == execution time == \n\n";
        	echo "total page time: ".sprintf('%1.3f ',round($timer_stop,4))."seconds\n\n";        
        	
	echo "time to reach each section in seconds | difference | queries | files\n"
		."--------------------------------------------------------------------\n";
	foreach ($bb_benchmark_time as $bb_btime=>$ignore) {
		echo str_pad(' '.$bb_btime,30)
		.$bb_benchmark_time[$bb_btime]
		.str_pad(intval($bb_benchmark_time[$bb_btime]*1000-$previous_time*1000),9,' ',STR_PAD_LEFT)." ms"
		.str_pad(($bb_benchmark_queries[$bb_btime]-$previous_queries ? $bb_benchmark_queries[$bb_btime] : '.'),9,' ',STR_PAD_LEFT)
		.str_pad(($bb_benchmark_files[$bb_btime]-$previous_files ? $bb_benchmark_files[$bb_btime] : '.'),9,' ',STR_PAD_LEFT)
		.($previous_files && $bb_benchmark_files[$bb_btime]-$previous_files ? ' ('.($bb_benchmark_files[$bb_btime]-$previous_files).')' : '')
		."\n";
		$previous_time=$bb_benchmark_time[$bb_btime];
		$previous_queries=$bb_benchmark_queries[$bb_btime];
		$previous_files=$bb_benchmark_files[$bb_btime];
	}   echo "\n";
      	      	
	if (!SAVEQUERIES) :

	echo " == mysql queries == \n\n";	
	echo "total query count: ".$bbdb->num_queries." \n\n";        		
	echo "To see full bb-benchmark results here put the following line into your bb-config.php\ndefine('SAVEQUERIES', true);\n\n --> ";

	else :
	
	$test=$bbdb->queries; $ktest=array_keys($test); $qtotal=0; $qslowkey=0;
	foreach ($ktest as $key) {$qtotal+=$test[$key][1]; if ($test[$key][1]>$test[$qslowkey][1]) {$qslowkey=$key;} }
	echo "total render time: ".(round($timer_stop-$qtotal,4))." seconds (query time subtracted)\n\n";
	
	echo " == mysql queries == \n\n";
	echo "total query count: ".$bbdb->num_queries." \n";  
	echo "total query time: ".round($qtotal,4)." seconds \n\n";         	        	
       	echo "slowest query # ".($qslowkey+1)." : ".round($test[$qslowkey][1],4)." seconds\n".$test[$qslowkey][0]."\n\n";
			
	echo " == mysql queries used == \n\n";		
	
	$test=$bbdb->queries; 
	foreach ($test as $key=>$discard) {
		echo " # ".($key+1)." : ".round($test[$key][1],4)." seconds".(isset($test[$key][2]) ? "     function ".$test[$key][2] : "")."\n".preg_replace("/\-\-/","- - ",$test[$key][0])."\n\n";
	}
			
	echo " === executed files === \n\n";
	$included_files = get_included_files();
	foreach ($included_files as $filename) {
	$size=filesize($filename); $totalsize+=$size;
    	echo str_pad(str_replace(BB_PATH,'',$filename),60).sprintf('%6.2f ',round($size/1024,2))."Kb\n";
	} echo "\n".str_pad(count($included_files).' files',60).sprintf('%6.2f ',round($totalsize/1024,2))."Kb\n";


	if (function_exists('getrusage') && $rusage=getrusage()) {
		$rusage['ru_utime']=$rusage['ru_utime.tv_sec']*1e6+$rusage['ru_utime.tv_usec']; unset($rusage['ru_utime.tv_sec']); unset($rusage['ru_utime.tv_usec']);
		$rusage['ru_stime']=$rusage['ru_stime.tv_sec']*1e6+$rusage['ru_stime.tv_usec']; unset($rusage['ru_stime.tv_sec']); unset($rusage['ru_stime.tv_usec']);
		$legend=array(
		'ru_utime'=>'microseconds spent executing in user mode',
		'ru_stime'=>'microseconds spent executing in system mode',
		'ru_minflt'=>'memory page retrieve faults that did not require I/O activity',
		'ru_majflt'=>'memory page retrieve faults that required I/O activity',
		'ru_nswap'=>'times the process was swapped out of memory',
		'ru_nvcsw'=>'times the process voluntarily "gave up" its time slice on the CPU while waiting',
		'ru_nivcsw'=>'times the process was forced to "give up" its time slice on the CPU (priority override)'
		);
		echo "\n == resource usage == \n\n";
		foreach ($rusage as $ru_name=>$ru_value) {
			if (empty($ru_value)) {continue;}
			foreach ($legend as $key=>$description) {
				if (strpos($ru_name,$key)!==false) {		
					echo str_pad($ru_value,9,' ',STR_PAD_LEFT).' '.str_pad($key,10).'- '.$description."\n";
					break;
				}
			}	
		}
	}
		
	echo "\n-->\n";
		
	endif;

}

// global $bb_current_user,$bbdb;  $capabilities=$bbdb->prefix."capabilities"; if ($bb_current_user->data->$capabilities['keymaster']) :

function bb_benchmark_template_timer($template='',$file='') {
	global $bb_current_user; static $can_administrate;
	if (isset($can_administrate)) {if (!$can_administrate) {return $template;}} 
	elseif (isset($bb_current_user->ID)) {$can_administrate=bb_current_user_can('administrate');}
	
	global $bb_benchmark_time, $bb_benchmark_queries, $bb_benchmark_files, $bb_timestart,  $bbdb, $wp_actions,$wp_current_filter;
	if (empty($file) || is_array($file) || strpos($file,'.php')===false) {
		$file = empty($wp_current_filter) ? end($wp_actions) : current($wp_current_filter);
	}
	// $bb_benchmark_time[$file] = // bb_timer_stop(0);  // array_sum(explode(" ",microtime()));
	
	$mtime = explode(' ', microtime(1)); (double)$mtime = (double)$mtime[0] + (isset($mtime[1]) ? (double)$mtime[1] : 0);
	$bb_benchmark_time[$file] = sprintf ("%6.3f",$mtime - $bb_timestart);  //  ." | ". $time;
	$bb_benchmark_queries[$file]=$bbdb->num_queries;
	$bb_benchmark_files[$file]=count(get_included_files());
	
	return $template;
}

function bb_benchmark_get_caller() {		// not used here yet - just for reminder/storage
	if ( !is_callable('debug_backtrace') ) {return '';}		// requires PHP 4.3+			

	$bt = debug_backtrace(); $caller = ''; $tail= '';

	foreach ( $bt as $trace ) {
		if ( @$trace['class'] == __CLASS__ )
			continue;
		elseif ( strtolower(@$trace['function']) == 'call_user_func_array' )
			continue;
		elseif ( strtolower(@$trace['function']) == 'apply_filters' )
			continue;
		elseif ( strtolower(@$trace['function']) == 'do_action' )
			continue;
		elseif ( strtolower(@$trace['function']) == 'query' ) {$tail=" (query) ".$tail; continue;}
		elseif ( strtolower(@$trace['function']) == 'bb_query' ) {$tail=" (bb_query) ".$tail; continue;}
		elseif ( strtolower(@$trace['function']) == 'bb_append_meta' )  {$tail=" (bb_append_meta) ".$tail; continue;}

		$caller = $trace['function'].$tail;
		break;
	}
		return $caller;
}


?>