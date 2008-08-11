<?php
/**
 * Plugin Name: bbPress Sitemap Generator
 * Plugin Description: A plugin that can Generate an XML SiteMap based on the topics, posts and tags present in a bbPress forum. All users are welcome to improve on it :p
 * Author: Rich Boakes & Frédéric Petit
 * Author URI: http://boakes.org
 * Plugin URI: http://boakes.org/talk/tags/bbpress-sitemap
 * Version: 0.6.1
 * License: GPL2
 * License URI: http://www.gnu.org/copyleft/gpl.html
 */

// the full path the the file that will be written - write access
// is required so set up your user /group privilages correctly or
// remember to "chmod 777 $sitemap_file" before runtime.
$sitemap_file = $_SERVER['DOCUMENT_ROOT']."/bbpress/sitemap.xml";

// written to the generated sitemap file to help identify
// generator and version
$version='0.6.1';
$generator="http://boakes.org/talk/tags/bbpress-sitemap";

// include debug information in the sitemap
$show_debug = false;

// choose one or other of these connection methods
// if you're debugging, use the debug hooks.  In most
// cases you'll want the normal hooks.
add_hooks();
if ($show_debug) add_debug_hooks();

	/**
	 * remove all text from a string from the point where a 
	 * given substring has been spotted
	 */
	function stripAfter($original, $sub_string) {
		$i = strpos($original, $sub_string);
		if ($i==0) return $original;
		return substr($original, 0, $i);
	}
	
	// an exercise for the reader
	function get_frequency_for_topic($topic_id) {
		return "weekly";
	}

	// an exercise for the reader
	function get_priority_for_topic($topic_id) {
		return "0.7";
	}

	/** 
	 * Calculate update frequency of a tag by comparing the time now
	 * with the time of creation.  Then divide that period (the tags's
	 * lifetime) by the number of times the tag has been used.  This 
	 * figure is thus the average lifetime, so a little leway is added
	 * and the frequency is returned as text.
	 */
	function get_frequency_for_tag($tag_id, $tag_time, $tag_count) {
		$lifetime_in_seconds = time() - $tag_time;
		$average_change_period = $lifetime_in_seconds / $tag_count;
//		$average_change_period = $average_change_period / 60; //minutes
		$average_change_period = $average_change_period / 60; //hours
		
		$result[time]=$average_change_period;
		$result[freq]="monthly";
		if ($average_change_period < 240) $result[freq] = "weekly"; // less than 10 days
		if ($average_change_period < 48) $result[freq] = "daily"; // less than 48 hours
		if ($average_change_period < 3) $result[freq] = "hourly"; // less than 3 hours
		return $result;
	}

	/** 
	 * Calculate priority of the tag as a function of the number
	 * of times it's used compared to the total number of uses
	 * of all tags.
	 */
	function get_priority_for_tag($tag_id, $tag_count, $total_tag_count) {
		return $tag_count / $total_tag_count;
	}

	/**
	 * Find all topics in the database and add them to the data that
	 * will be added to the sitemap;
	 */
	function discover_topics() {
  		global $bbdb;
		$topic_query = "SELECT t.topic_id as tid, p.post_time as tim FROM $bbdb->posts p, $bbdb->topics t WHERE p.topic_id = t.topic_id AND post_status = 0 group by t.topic_id ORDER BY p.post_time";
		$matches = $bbdb->get_results($topic_query);

		if ($matches) {
			foreach($matches as $match) {
				$url=stripafter(get_topic_link($match->tid),"?replies");

				$freq=get_frequency_for_topic($match->tid);

				$priority=get_priority_for_topic($match->tid);
				
				$time=strtotime($match->tim);
				$nice_time = date("Y-m-d",$time);
				
				add_row($url, $nice_time, $freq, $priority);
			}
		}
	}

	/**
	 * Find all tags in the database and add them to the data that
	 * will be added to the sitemap;
	 */
	function discover_tags() {
  		global $bbdb;
		$total_tag_count = $bbdb->get_var("SELECT sum(tag_count) FROM $bbdb->tags");
		$total_tag_count = max($total_tag_count, 1);
		$tag_query="SELECT t.tag_id as tid, t.tag as tag, t.tag_count, min(x.tagged_on) as tim FROM $bbdb->tags t, $bbdb->tagged x where t.tag_id = x.tag_id group by t.tag_id ORDER BY t.tag_id;";
		
		$matches = $bbdb->get_results($tag_query);
		$earliest_tag = time();
		if ($matches) {
			foreach($matches as $tag) {
				// tag url
				$url=get_tag_link($tag->tag);

				// tag modification time
				$time=strtotime($tag->tim);
				$nice_time = date("Y-m-d",$time);

				// tag priority
				$priority=get_priority_for_tag($tag->tid, $tag->tag_count, $total_tag_count);

				// tag update frequency
				$freq=get_frequency_for_tag($tag->tid, $time, $total_tag_count);

				add_row($url, $nice_time, $freq[freq], $priority, array("FRQT=".$freq[time], "time=".$time, "nt=".$nice_time));
				$earliest_tag = min($time, $earliest_tag);
			}
		}	
		$tag_page_url = get_tag_page_link();

		// fix for ticket 482 - http://trac.bbpress.org/ticket/482
		if (strpos($tag_page_url, "://") <= 0) {
			$tag_page_url = bb_get_option('domain') . $tag_page_url;
		}

		add_row($tag_page_url, date("Y-m-d",$earliest_tag), "daily", "0.5");

	}

	/**
	 * Manage the file handling bit of writing to the sitemap file
	 */
	function dump_sitemap_file() {
  		global $sitemap_file;
		$f = fopen($sitemap_file, "w");
		dump_sitemap_header($f);
		dump_rows($f);
		dump_sitemap_footer($f);
		fflush($f);
		fclose($f);
	}

	/**
	 * Neatly package up and write the XML header for the file
	 */
	function dump_sitemap_header($handle) {
		global $version, $generator;
		fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>');
		fwrite($handle, '<!-- Generator: '.$generator.' -->');
		fwrite($handle, '<!-- Generator-Version: '.$version.' ('.substr(md5_file(__FILE__),0,4).') -->');
		fwrite($handle, '<!-- Page generated: '.date("Y-m-d H:i:s",time()).' -->');
		fwrite($handle, '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">');
	}

	/**
	 * Neatly package up and write the XML footer for the file
	 */
	function dump_sitemap_footer($handle) {
		fwrite($handle, '</urlset>');
	}

	/**
	 * Add a row of data to the result set that will be written to the file.
	 */
	function add_row($url, $mod, $freq, $priority, $debug = "") {
		global $rows;
		$rows[] = array( url=>$url, mod_time=>$mod, freq=>$freq, priority=>$priority, debug=>$debug );
	}

	/**
	 * Dump every row that's been discovered to the available file handle
	 */
	function dump_rows($handle) {
		global $rows;
		foreach($rows as $row) {
			dump_row( $handle, $row );
		}
		$rows = array();
		unset($rows);
	}
	
	/**
	 * Write out the individual row data
	 */
	function dump_row($handle, $row) {
		global $show_debug;
		$u = parse_url( $row[url] );
		$u2 = $u[scheme] ."://".$u[host].$u[path].urlencode($u[query]).$u[fragment];
		fwrite($handle, "<url>\n");
		fwrite($handle, "	<loc>$u2</loc>\n");
		fwrite($handle, "	<lastmod>$row[mod_time]</lastmod>\n");
		fwrite($handle, "	<changefreq>$row[freq]</changefreq>\n");
		fwrite($handle, "	<priority>$row[priority]</priority>\n");
		fwrite($handle, "</url>\n");
	}


	function sometimes_create_sitemap() {
		// this hook gets fired an awful lot, so to reduce
		// the server workload, there's only a 1 in 10 chance
		// that it will actually do anything.  Most of the time
		// it just returns.
		$n = mt_rand(1, 10);
		if ( ($n % 10) ) {
			create_sitemap();
		}
		
	}
	/**
	 * This is the main function that gets linked to various bb_press
	 * hooks (aka actions).
	 */
	function create_sitemap() {
		// discover what there is to know
		discover_topics();
		discover_tags();
		// write it out
		dump_sitemap_file();
		// finished already!
	}

	/**
	 * BBPress Hooks which the plugin uses to ensure it knows
	 * when there has been an update.
	 */
	function add_hooks() {
		add_filter( 'bb_new_topic', 'sometimes_create_sitemap', 0);
		add_filter( 'bb_update_topic', 'sometimes_create_sitemap', 0);
		add_filter( 'bb_delete_topic', 'sometimes_create_sitemap', 0);

		add_filter( 'bb_new_post', 'sometimes_create_sitemap', 0);
		add_filter( 'bb_update_post', 'sometimes_create_sitemap', 0);
		add_filter( 'bb_delete_post', 'sometimes_create_sitemap', 0);
	}

	/**
	 * this is useful when testing - just add a favourite to fire
	 * the generation - because there's no plugin UI in whcih to
	 * stick a "regenerate" button.
	 */
	function add_debug_hooks() {
		add_filter( 'bb_add_user_favorite', 'create_sitemap', 0);
	}


?>
