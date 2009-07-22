<?php
/*
Plugin Name: Hot Tags Plus
Description:  Creates advanced hot tag heat maps with time & forum filters, colors and caching for performance.
Plugin URI:  
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2
*/

$hot_tags_plus['cache']=false;						  // caching switch, set true to turn on, false for off - caching is strongly recommened on live sites
$hot_tags_plus['cache_dir']="/home/example/hot-tags-plus/"; 	  // make this above the web-root and chmod 777
$hot_tags_plus['cache_time']=0;					 	 // not implimented yet, cache for time instead of immediate when tags added/deleted

$hot_tags_plus['related']=true;	 					 // show related tags in cloud during mouse-over

$hot_tags_plus['style']="
#hottags, .frontpageheatmap {line-height:220%; *line-height:250%;}
#hottags a font, .frontpageheatmap a font {padding:0 3px; margin: -3px -2px; }
#hottags a:hover font, .frontpageheatmap a:hover font {position:relative; z-index:255; color:red;}
.HTP_RELATED font {background:#eee;}
";

 /*       stop editing here         */

$hot_tags_plus['prefix']=$bb_table_prefix.(crc32(bb_get_option('uri'))+4294967296);
 
add_action('hot_tags_plus', 'hot_tags_plus');
if ($hot_tags_plus['style']) {
add_action('bb_head','hot_tags_plus_head');
}
if (empty($hot_tags_plus['cache_time'])) {
add_action('bb_tag_added', 'hot_tags_plus_delete', 10,3);
add_action('bb_tag_created', 'hot_tags_plus_delete', 10,2);
add_action('bb_pre_tag_removed', 'hot_tags_plus_delete', 10,3);
add_action('bb_remove_topic_tags', 'hot_tags_plus_delete', 10,2);
}

function hot_tags_plus($args = '') { echo get_hot_tags_plus($args); }

function get_hot_tags_plus($args = '' ) {
	global $hot_tags_plus, $bbdb; $a = array();	
		
	$defaults = array( 'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'limit' => 45, 'format' => 'flat', 
				'minimum' => 0, 'maximum' => 0, 'forums' => 0, 'since' => 0, 'sort' => 0, 'colors' => 0 );
	$args = wp_parse_args( $args, $defaults );
	if ($args['colors']===0) {$args['colors']=array('24244C','600000','C00000');}
	
	if ( 1 < $fn = func_num_args() ) {   // legacy
		$args['smallest'] = func_get_arg(0);
		$args['largest']  = func_get_arg(1);
		$args['unit']     = 2 < $fn ? func_get_arg(2) : $unit;
		$args['limit']    = 3 < $fn ? func_get_arg(3) : $limit;
	}
	
	if ($hot_tags_plus['cache']) {
		$filename="";
		foreach ($args as $key=>$value) {if (is_array($value)) {$value=implode("_",$value);} $filename .= "_".$key."_".$value;}	
		$filename=$hot_tags_plus['cache_dir'].$hot_tags_plus['prefix'].bb_slug_sanitize($filename);
		if (file_exists($filename)) {return file_get_contents($filename);}
	}

	extract($args, EXTR_SKIP);	 	// turn arguments into strings

	// $tags = bb_get_top_tags( false, $limit );	// print " <!-- "; print_r($tags); print " --> ";  // diagnostic
		
	$limit=intval($limit);
	if (empty($since)) {$since="";}
	else {
		if (intval($since)<99999) { $since=strtotime($since." GMT"); } 
		$since=" AND topic_time >= '".gmdate('Y-m-d H:i:s',intval($since))."' ";		
	}
	if (empty($forums)) {$forums="";}
	else {
		if (is_array($forums)) {foreach ($forums as $key=>$value) {$forums[$key]=intval($value);}} else {$forums=intval($forums);}
		$forums=" AND forum_id IN (".implode(',',(array) $forums).") ";
	} 
	if (empty($maximum)) {$maximum=""; $having="";}
	else { $maximum=" tcount <= ".intval($maximum); $having=" HAVING $maximum ";}
	if (empty($minimum)) {$minimum="";}
	else { $minimum=" tcount >= ".intval($minimum); $having=($having ? " AND " : " HAVING ").$minimum;}
	
	if (defined('BACKPRESS_PATH')) {
	$query="SELECT COUNT( relationships.term_taxonomy_id ) AS tcount, terms.name AS raw_tag, terms.slug AS tag, terms.term_id AS id
			FROM $bbdb->term_relationships AS relationships
			LEFT JOIN $bbdb->term_taxonomy AS taxonomy ON relationships.term_taxonomy_id = taxonomy.term_taxonomy_id
			LEFT JOIN $bbdb->terms AS terms ON taxonomy.term_taxonomy_id = terms.term_id
			LEFT JOIN $bbdb->topics AS topics ON object_id = topic_id
			WHERE topic_status = 0 $forums $since
			GROUP BY relationships.term_taxonomy_id $having ORDER BY tcount DESC, topic_time DESC LIMIT $limit";
			//	    WHERE term_taxonomy_id IN ($ids) 
			//		    AND term_taxonomy_id NOT IN (".$related_topics['exclude_tags'].") 
			// AND object_id NOT IN (".$related_topics['exclude_topics'].") 
	} else {
	$query="SELECT COUNT(relationships.tag_id) as tcount, terms.raw_tag AS raw_tag, terms.tag AS tag,terms.tag_id AS id
	                 	FROM $bbdb->tagged AS relationships
	                 	LEFT JOIN $bbdb->tags AS terms ON relationships.tag_id = terms.tag_id		    	
	                 	LEFT JOIN $bbdb->topics AS topics ON relationships.topic_id = topics.topic_id
			WHERE topic_status = 0 $forums $since
			GROUP BY relationships.tag_id $having ORDER BY tcount DESC, topic_time DESC LIMIT $limit";
			// 	 WHERE tag_id IN ($ids) 
		    	// 	 AND tag_id NOT IN (".$related_topics['exclude_tags'].") 
		    	// 	 AND topic_id NOT IN (".$related_topics['exclude_topics'].")  
	}
	$tags=$bbdb->get_results($query); 	
	if (empty($tags)) {return '';}	
		 
	if ( bb_get_option('mod_rewrite') ) { $path = bb_get_option('uri' )."tags/"; } else { $path = bb_get_option('uri' )."tags.php?tag=";}
	foreach ( (array) $tags as $tag ) {
		$ids{$tag->raw_tag} = $tag->id;
		$counts{$tag->raw_tag} = $tag->tcount;				
		$taglinks{$tag->raw_tag} = apply_filters( 'bb_get_tag_link', $path.$tag->tag, $tag->tag, 1 );	   //  $taglinks{$tag->raw_tag} = bb_get_tag_link( $tag->tag );		
	}

	$min_count = min($counts);
	$spread = max($counts) - $min_count;
	if ( $spread <= 0 ) { $spread = 1; }
	$fontspread = $largest - $smallest;
	if ( $fontspread <= 0 ) { $fontspread = 1; }
	$fontstep = $fontspread / $spread;

	if ($sort=="alphabetical") {	
		uksort($counts, 'strnatcasecmp');
	} elseif ( $sort=="highest" || $sort=="count" || $sort=="counts" || $sort=="numeric" ) {
		arsort($counts, SORT_NUMERIC);
	} elseif ( $sort=="raw" ) { 	// do nothing - use db sort
	} else { do_action_ref_array( 'sort_tag_heat_map', array(&$counts) ); }
	
	if ($colors!==0) {
		if (is_array($colors) && count($colors)>1) {$gradient = hot_tags_plus_gradient($colors,$spread);} 	 // calculate color ranges		max($counts)
		else {  
			if (is_array($colors)) {$colors=reset($colors);}
			for ($i = 0; $i <= $spread ; $i++) { $gradient[$i]=$colors;}
		}
	}

	foreach ( $counts as $tag => $count ) {
		$id=($hot_tags_plus['related'] ? " id='tag_".$ids[$tag]."'" : "");
		$taglink = attribute_escape($taglinks{$tag});		
		$tag = str_replace(' ', '&nbsp;', wp_specialchars( $tag ));
		$a[] = "<a rel='tag'$id href='$taglink' title='" . attribute_escape( sprintf( __('%d topics'), $count ) ) . "'><font style='font-size: " .
			round( $smallest + ( ( $count - $min_count ) * $fontstep ) ,4). "$unit;'"
			. ($colors ? " color='#".$gradient[$count-$min_count]."'" : "")
			.">$tag</font></a>";
	}

	switch ( $format ) :
		case 'array' : 	$r =& $a; break;
		case 'list' : $r = "<ul class='bb-tag-heat-map'>\n\t<li>". join("</li>\n\t<li>", $a)."</li>\n</ul>\n"; 	break;
		default : $r = join("\n", $a); break;
	endswitch;
	
	if ($hot_tags_plus['related']) {

	if (defined('BACKPRESS_PATH')) {
	$query="SELECT id, GROUP_CONCAT(DISTINCT CAST( related_id AS CHAR )) AS related FROM (
			SELECT t2.term_taxonomy_id  AS id, t1.term_taxonomy_id  AS related_id
			FROM $bbdb->term_relationships AS t1
			LEFT JOIN $bbdb->term_relationships AS t2 ON ( t1.object_id = t2.object_id )			
			LEFT JOIN $bbdb->term_taxonomy AS t3 ON ( t1.term_taxonomy_id = t3.term_taxonomy_id )			
			WHERE t2.term_taxonomy_id  IN (".implode(',',$ids).") 
			ORDER BY id,related_id ASC
			) AS tags WHERE id!=related_id GROUP BY id";		 //  maybe WHERE t3.	term_id IN ?  taxonomy_id is not really = term_id
	} else {	
	$query="SELECT id, GROUP_CONCAT(DISTINCT CAST( related_id AS CHAR )) AS related FROM (
			SELECT t2.tag_id AS id, t1.tag_id AS related_id
			FROM $bbdb->tagged AS t1
			LEFT JOIN $bbdb->tagged AS t2 ON ( t1.topic_id = t2.topic_id )
			LEFT JOIN $bbdb->tags AS t3 ON ( t1.tag_id = t3.tag_id )
			WHERE t2.tag_id IN (".implode(',',$ids).") 
			ORDER BY id,related_id ASC
			) AS tags WHERE id!=related_id GROUP BY id";						
	}
	$results=$bbdb->get_results($query);

	if (!empty($results) && is_array($results) && count($results)) {
		$HTP_related="\n";		
		foreach ($results as $result) {$HTP_related.="HTP_related[$result->id]=[$result->related];\n";}
		$r .= <<< HTPJSEOF
		
		<script type="text/javascript" defer="defer">
		var HTP_related=new Array();
		if (window.attachEvent) {window.attachEvent('onload', HTP_init);} 
		else if (window.addEventListener) {window.addEventListener('load', HTP_init, false);} 
		else {document.addEventListener('load', HTP_init, false);}		
		
		function HTP_init() {			
			$HTP_related
			var tlinks=document.links.length;
			for (var i=0;i<tlinks;i++) {		
				if (document.links[i].id.substring(0,4)=="tag_") {
					// document.links[i].className="related";
					document.links[i].onmouseover=HTP_mouse;
					document.links[i].onmouseout=HTP_mouse;					
				}
			}
		}
		
		function HTP_mouse(e) {
			if (e) {if (e.target) {obj=e.target;} else {obj=e;}} else if (window.event) {e=window.event; if (e.target) {obj=e.target;} else {obj=e.srcElement;}} 			
			if (obj.nodeName=="FONT") {obj=obj.parentNode;}
			if (e.type=="mouseover") {var tclass="HTP_RELATED";}
			else if (e.type=="mouseout") {var tclass="";}
			else {return;}			
			var id=obj.id.substring(4);			
			if (HTP_related[id]) {		
				for (var i=0;i<=HTP_related[id].length;i++) {
					var tag=document.getElementById("tag_"+HTP_related[id][i]);
					if (tag) {tag.className=tclass;}
				}
			}
		}
		
		</script>
HTPJSEOF;

	}  // previous line MUST be blank because of heredoc
	}
	
	if ($hot_tags_plus['cache']) {
		$current=get_current_user();  if (!($current && !in_array($current,array("nobody","httpd","apache","root")) && strpos(__FILE__,$current))) {$current="";}
		$x=posix_getuid (); if (0 == $x && $current) {$org_uid = posix_get_uid(); $pw_info = posix_getpwnam ($current); $uid = $pw_info["uid"];  posix_setuid ($uid);}
		$fh=@fopen($filename,"wb"); if ($fh) {@fwrite($fh,$r); fclose($fh);}
		if ($org_uid) {posix_setuid($org_uid);}
	}	
return $r;
}

function hot_tags_plus_delete($x='',$y='',$z='') {global $hot_tags_plus; $files=glob($hot_tags_plus['cache_dir'].$hot_tags_plus['prefix']."*"); foreach($files as $fn) {@unlink($fn);}} 

function hot_tags_plus_head() {
global $hot_tags_plus;
echo '<style type="text/css">'.$hot_tags_plus['style'].'</style>';
}

function hot_tags_plus_gradient($hex_array, $steps) {
	$hex_array= (array) $hex_array;
	$tot = count($hex_array);
	$gradient = array();
	$steps++;
	$fixend = 2;
	$passages = $tot-1; if ($passages<1) {$passages=1;}
	$stepsforpassage = floor($steps/$passages);
	$stepsremain = $steps - ($stepsforpassage*$passages);

   	for ($pointer = 0; $pointer < $tot-1 ; $pointer++) { 
       		$hexstart = $hex_array[$pointer];
       		$hexend = $hex_array[$pointer + 1];

       		if ($stepsremain > 0) {
           			if ($stepsremain--) {$stepsforthis = $stepsforpassage + 1;}
       		} else {$stepsforthis = $stepsforpassage; if ($stepsforthis == 0) {$stepsforthis = 0.001;}}
      		
   	    	if ($pointer > 0) { $fixend = 1; }
   
		$start['r'] = hexdec(substr($hexstart, 0, 2));	$start['g'] = hexdec(substr($hexstart, 2, 2));	$start['b'] = hexdec(substr($hexstart, 4, 2));
		$end['r'] = hexdec(substr($hexend, 0, 2));		$end['g'] = hexdec(substr($hexend, 2, 2));		$end['b'] = hexdec(substr($hexend, 4, 2));
 		$step['r'] = ($start['r']-$end['r'])/($stepsforthis);	$step['g'] = ($start['g']-$end['g'])/($stepsforthis);	$step['b'] = ($start['b']-$end['b'])/($stepsforthis);
   
       		for($i = 0; $i <= $stepsforthis-$fixend; $i++) { 
			$rgb['r'] = floor($start['r'] - ($step['r'] * $i));	$rgb['g'] = floor($start['g'] - ($step['g'] * $i));	$rgb['b'] = floor($start['b'] - ($step['b'] * $i)); 
			$hex['r'] = sprintf('%02x', ($rgb['r']));	$hex['g'] = sprintf('%02x', ($rgb['g']));		$hex['b'] = sprintf('%02x', ($rgb['b']));	 
           			$gradient[] = strtoupper(implode(NULL, $hex));
       		}
   	} 
   	$gradient[] = $hex_array[$tot-1]; 
return $gradient;
}

?>