<?php
/*
Plugin Name: Enhanced Tag Heat Map
Plugin URI: http://www.adityanaik.com/projects/plugins/enhanced-tag-heat-map/
Description: Enhanced Tag Heat Map replaces the tag heat map to highlight related tags for each of the tag on mouse over.
Author: Aditya Naik
Version: 1.1.0
Author URI: http://www.adityanaik.com/

Version History
1.0	: Initial Release
1.0.1 : remove calls to firebug console
1.0.2 : removed hooks to wordpress
1.1.0 : update the getTagHeatMapRelatedTagsScript function make only one call to database
*/

/**
 * Class for enhanced tag heat map class
 * 
 * @author	Aditya Naik
 * @version 1.0 
 */
class enhancedTagHeatMap {
	
	/**
	 * number of tags in the heat map
	 * 
	 * @var int
	 */
	var $limit = 45;
	
	/**
	 * smallest size in the heat map
	 * 
	 * @var	int 
	 */
	var $smallest = 8;
	
	/**
	 * largest size in the heat map
	 * 
	 * @var int 
	 */
	var $largest = 22;
	
	/**
	 * unit of size for the heat map
	 * 
	 * @var string
	 */
	var $unit = 'pt';
	
	/**
	 * foreground color for related hover link
	 * 
	 * @var string
	 */
	var $related_foreground = '#ffffff';
	
	/**
	 * foreground color for related hover link
	 * 
	 * @var string
	 */
	var $related_background = '#990000';
	
	/**
	 * options from the database
	 * 
	 * @var array 
	 */
	var $options_retrieved = false;
	
	/**
	 * tags
	 * 
	 * @var array 
	 */
	var $tags = array();
	
	/**
	 * constructor
	 * 
	 * @author	Aditya Naik
	 * @version	1.0
	 * @return	void 
	 */
	function enhancedTagHeatMap() {
		if (version_compare(bb_get_option('version'), '0.9-z', '<')) {
			error_log(__('This version of the "Enhanced Tag Heat Map" plugin requires bbPress version 0.9'));
			exit();
			return;
		}
		
		$this->refreshOptions();
		$this->tags = bb_get_top_tags( false, $this->limit);
	}
	
	/**
	 * get the options from the database
	 * 
	 * @author	Aditya Naik
	 * @version 1.0
	 * @return 	array 
	 */
	public function getOptions() {
		return bb_get_option('enhanced_tag_heat_map');	
	}
	
	/**
	 * put the options from the database
	 * 
	 * @author	Aditya Naik
	 * @version 1.0
	 * @return 	array 
	 */
	public function putOptions() {
		$options = array();
		$options['smallest'] = $this->smallest;
		$options['largest'] = $this->largest;
		$options['limit'] = $this->limit;
		$options['unit'] = $this->unit;
		$options['related_foreground'] = $this->related_foreground;
		$options['related_background'] = $this->related_background;
		bb_update_option('enhanced_tag_heat_map',$options);	
	}
	
	/**
	 * if the database option is not retrieved, retrieve it;
	 * @return 
	 */
	private function refreshOptions() {
		$options = $this->getOptions();
		if (!$options) {
			$this->setDefaultHeatMapParams();
			$this->putOptions();
		} else {
			if (isset($options['smallest'])) $this->smallest = $options['smallest'];
			if (isset($options['largest'])) $this->largest = $options['largest'];
			if (isset($options['unit'])) $this->unit = $options['unit'];
			if (isset($options['limit'])) $this->limit = $options['limit'];
			if (isset($options['related_foreground'])) $this->related_foreground = $options['related_foreground'];
			if (isset($options['related_background'])) $this->related_background = $options['related_background'];
		}
	}
	
	/**
	 * get foreground color for related link hover
	 * 
	 * @author	Aditya Naik
	 * @version	1.0
	 * @return	string
	 */
	public function getRelatedForegroundColor() {
		return $this->related_foreground;
	}
	
	/**
	 * set foreground color for related link hover
	 * 
	 * @author	Aditya Naik
	 * @version	1.0
	 * @return	string
	 */
	public function setRelatedForegroundColor($related_foreground) {
		$this->related_foreground = $related_foreground;
	}
	
	/**
	 * get background color for related link hover
	 * 
	 * @author	Aditya Naik
	 * @version	1.0
	 * @return	string
	 */
	public function getRelatedBackgroundColor() {
		return $this->related_background;
	}
	
	/**
	 * set background color for related link hover
	 * 
	 * @author	Aditya Naik
	 * @version	1.0
	 * @return	string
	 */
	public function setRelatedBackgroundColor($related_background) {
		$this->related_background = $related_background;
	}
	
	/**
	 * get smallest size options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	int 
	 */
	public function getSmallestSize() {
		return (int)$this->smallest;
	}
	
	/**
	 * set smallest size options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	void 
	 */
	public function setSmallestSize($smallest) {
		$this->smallest = (int)$smallest;
	}
	
	/**
	 * get largest size options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	int 
	 */
	public function getLargestSize() {
		return (int)$this->largest;
	}
	
	/**
	 * set largest size options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	void 
	 */
	public function setLargestSize($largest) {
		$this->largest = (int)$largest;
	}
	
	/**
	 * get size unit options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	string 
	 */
	public function getUnit() {
		return $this->unit;
	}
	
	/**
	 * set size unit options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	void 
	 */
	public function setUnit($unit) {
		$this->unit = $unit;
	}
	
	/**
	 * get limit options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	int 
	 */
	public function getLimit() {
		return (int)$this->limit;
	}
	
	/**
	 * set limit options for the plugin
	 * 
	 * @author	Aditya naik
	 * @version	1.0
	 * @return	void 
	 */
	public function setLimit($limit) {
		$this->limit = (int)$limit;
	}
	
	/**
	 * set the parameters
	 * 
	 * @author	Aditya Naik
	 * @return 	void
	 * @param 	$args 	arguments as query parameters - smallest, largest, unit, limit
	 */
	public function setDefaultHeatMapParams() {

		$defaults = array( 'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'limit' => 45, 'related_foreground' => '#FFFFFF', 'related_background' => '#990000');
		extract($defaults, EXTR_SKIP);

		$this->smallest = $smallest;
		$this->largest = $largest;
		$this->unit = $unit;
		$this->limit = $limit;
		$this->related_foreground = $related_foreground;
		$this->related_background = $related_background;
	}
	
	/**
	 * Creates a tag heat map
	 * 
	 * @author	Aditya Naik
	 * @version	1.0
	 * @return	string 
	 */
	public function getTagHeatMap($smallest = 8, $largest = 22, $unit = 'pt', $limit = 45, $format = 'flat') {
		
		$smallest = (int)$this->smallest;
		$largest = (int)$this->largest;
		$unit = $this->unit;
		$limit = (int)$this->limit;
		
		$tags = $this->tags;
		
		foreach ( (array) $tags as $k => $tag ) {
			$counts{$tag->raw_tag} = $tag->tag_count;
			$taglinks{$tag->raw_tag} = bb_get_tag_link( $tag->tag );
			$ids{$tag->raw_tag} = $k;
		}
		
		$min_count = min($counts);
		$spread = max($counts) - $min_count;
		
		if ( $spread <= 0 )
			$spread = 1;
		$fontspread = $largest - $smallest;
		if ( $fontspread <= 0 )
			$fontspread = 1;
		
		$fontstep = $fontspread / $spread;
		
		do_action_ref_array( 'sort_tag_heat_map', array(&$counts) );
	
		$a = array();
	
		foreach ( $counts as $t => $count ) {
			$tag = $tags[$ids[$t]];
			$taglink = attribute_escape(bb_get_tag_link( $tag->tag ));
			$tag_name = str_replace(' ', '&nbsp;', wp_specialchars( $t ));
			
			$a[] = "<a id='tag_" . $tag->tag_id . "' href='$taglink' title='" . attribute_escape( sprintf( __('%d topics'), $tag->tag_count ) ) . "' class='aajax tagheata' rel='tag' style='font-size: " .
				( $smallest + ( ( $tag->tag_count  - $min_count ) * $fontstep ) )
				. "$unit;'>$tag_name</a>";
		}
		$r = join("\n", $a);
		return $r;		
	} 
	
	/**
	 * Print script and style for the enhanced heat map
	 * The function will be added to the action bb_head or wp_head
	 * 
	 * @author	Aditya Naik
	 * @version	1.0.1
	 * @return 	void
	 */
	function getTagHeatMapScript() {
		?>
		<script type="text/javascript">
			function ajaxifyTags() {
				if ($('hottags')) {
					links = document.getElementsByClassName( 'aajax');
					var tag_relation = new Array();
					<?php
					$this->getTagHeatMapRelatedTagsScript();
					?>
					$A(links).each(function(link) {
						link.onmouseover = function() {
							var tag_id = link.id;
							var related_tag_id = eval(tag_relation[tag_id.substring(4)]);
							if (related_tag_id) {
								related_tag_id.each(function(tag) {
									e = eval($(tag));
									if (e) 
										e.addClassName('related-tag');
								});
							}
						};
						
						link.onmouseout = function() {
							var tag_id = link.id;
							var related_tag_id = eval(tag_relation[tag_id.substring(4)]);
							if (related_tag_id) {
								related_tag_id.each(function(tag) {
									e = eval($(tag));
									if (e) 
										e.removeClassName('related-tag');
								});
							}
						};
						
					});
				}
			}
			Event.observe(window, 'load', ajaxifyTags);
	
		</script>
		<style>
			a.related-tag {
				background-color: <?php echo $this->getRelatedBackgroundColor();?>;
				color: <?php echo $this->getRelatedForegroundColor();?>;
			}
					
		</style>
		<?php
	}	

	/**
	 * Creates javascript for the related tags for each of the tag in the tag heat map
	 * 
	 * @author	Aditya Naik
	 * @version	1.1
	 * @return 	void
	 */
	function getTagHeatMapRelatedTagsScript() {
		global $bbdb;
		$tags = $this->tags;
		
		$related_tags_result = $bbdb->get_results( "SELECT t.tag_id as t, tt.tag_id as rt FROM $bbdb->tagged t JOIN $bbdb->tagged AS tt  ON (t.topic_id = tt.topic_id)"  );
		$related_tags = array();
		if ($related_tags_result)
		foreach($related_tags_result as $r ) {
			if (isset($related_tags[$r->t])) {
				$related_tags[$r->t] = array_merge($related_tags[$r->t], array($r->rt));
			} else {
				$related_tags[$r->t] = array($r->rt);
			}
		}
		
		foreach($tags as $tag) {
			?>	
		tag_relation[<?=$tag->tag_id; ?>] = new Array(<?php
				$a_r_tags = array();
				if (count($related_tags[$tag->tag_id]) > 0): foreach($related_tags[$tag->tag_id] as $r_tag) {
					$a_r_tags[] = "'tag_" . $r_tag . "'";
				}
				echo "" . join ($a_r_tags, ",");
				$a_r_tags = null;
				endif;
			?>);<?php
		}
		
		echo "\n";
	}
	
}

/**
 * Override function for the tag heat map
 *
 * @author  Aditya Naik
 * @version 1.0
 * @return 	string
 **/
function show_enhanced_tag_heat_map() {
	global $ethm; 
	if(!$ethm)
		$ethm = new enhancedTagHeatMap();
	
	return $ethm->getTagHeatMap();
}

// Filter to replace the current heat map
add_filter('tag_heat_map','show_enhanced_tag_heat_map');

// Add script to the head if user is loading it in bbpress
bb_enqueue_script('prototype');
add_action('bb_head','enhanced_tag_heat_map_script');

/**
 * Print script and style for the enhanced heat map
 * The function will be added to the action bb_head or wp_head
 * 
 * @author	Aditya Naik
 * @version	1.0
 * @return 	void
 */
function enhanced_tag_heat_map_script() {
	global $ethm; 
	if(!$ethm)
		$ethm = new enhancedTagHeatMap();
	
	return $ethm->getTagHeatMapScript();
}

global $ethm;
if(!$ethm)
	$ethm = new enhancedTagHeatMap();
	
// Don't bother with admin interface unless we are loading an admin page
if (!BB_IS_ADMIN) {
	return;
}

// Add filters for the admin area
add_action('bb_admin_menu_generator', 'enhanced_tag_heat_map_admin_page_add');
add_action('bb_admin-header.php', 'enhanced_tag_heat_map_admin_page_process');
if($_GET['plugin']=='enhanced_tag_heat_map_admin_page') {
	if($_GET['reset']=='true') {
		add_action('bb_admin_notices','show_enhanced_tag_heat_map_reset_notice');
	} else if ($_GET['updated']==true) {
		add_action('bb_admin_notices','show_enhanced_tag_heat_map_update_notice');
	}
}

/**
 * Adds in menu item to the admin page
 *
 * @author	Aditya Naik
 * @version	10
 * @return	void
 **/
function enhanced_tag_heat_map_admin_page_add() {
	bb_admin_add_submenu(__('Enhanced Tag Heat Map'), 'use_keys', 'enhanced_tag_heat_map_admin_page');
}

/**
 * Creates an admin page for the plugin
 * 
 * @author	Aditya Naik
 * @version	1.0
 * @return	void
 */
function enhanced_tag_heat_map_admin_page() {
	global $ethm;
	if (!$ethm)
		$ethm = new enhancedTagHeatMap();
	?>
	<h2>Enhanced Tag Heat Map Options</h2>
	<form method="post" class="options">
		<fieldset>
			<label>Smallest size</label>
			<div>
				<input type="text" name="ethm_smallest" value="<?php echo $ethm->getSmallestSize(); ?>" />
			</div>
			<label>Largest size</label>
			<div>
				<input type="text" name="ethm_largest" value="<?php echo $ethm->getLargestSize(); ?>" />
			</div>
			<label>Unit of size</label>
			<div>
				<select name="ethm_unit">
					<option value="pt" <?php echo ($ethm->getUnit() == 'pt') ? 'selected' : '' ; ?> >points (pt)</option>
					<option value="px" <?php echo ($ethm->getUnit() == 'px') ? 'selected' : '' ; ?> >pixels (px)</option>
					<option value="em" <?php echo ($ethm->getUnit() == 'em') ? 'selected' : '' ; ?> >element multiple (em)</option>
				</select>
			</div>
			<label>Limit of tags in the heat map</label>
			<div>
				<input type="text" name="ethm_limit" value="<?php echo $ethm->getLimit(); ?>" />
			</div>
			<input type="hidden" name="action" value="update" />
		</fieldset>
		<fieldset>
			<legend>Color for the related items</legend>
			<label>Foreground Color</label>
			<div>
				<input type="text" name="ethm_related_foreground" value="<?php echo $ethm->getRelatedForegroundColor(); ?>" />
			</div>
			<label>Background Color</label>
			<div>
				<input type="text" name="ethm_related_background" value="<?php echo $ethm->getRelatedBackgroundColor(); ?>" />
			</div>
		</fieldset>
		<fieldset>
			<div class="spacer">
				<input type="submit" name="submit" value="<?php _e('Update Settings &raquo;') ?>" />
			</div>
		</fieldset>
	</form>
	<form method="post" class="options">
		<fieldset>
			<legend>Reset Options</legend>
			<input type="hidden" name="action" value="reset" />
			<div class="">
				<input type="submit" name="submit" value="<?php _e('Reset Settings &raquo;') ?>" />
			</div>
		</fieldset>
	</form>
	<?php
}

/**
 * Process the post from the admin page
 * 
 * @author	Aditya Naik
 * @version	1.0
 * @return 	void
 */
function enhanced_tag_heat_map_admin_page_process() {
	
	global $ethm;
	if (!$ethm)
		$ethm = new enhancedTagHeatMap();
	
	$goback = remove_query_arg('updated',wp_get_referer());
	$goback = remove_query_arg('reset',$goback);
	
	if ($_POST['action'] == 'update') {
		
		$options = bb_get_option('enhanced_tag_heat_map');
		
		if(isset($_POST['ethm_smallest'])) {
			$ethm->setSmallestSize((int) $_POST['ethm_smallest']); 
		}
		
		if(isset($_POST['ethm_largest'])) {
			$ethm->setLargestSize((int) $_POST['ethm_largest']); 
		}
		
		if(isset($_POST['ethm_unit'])) {
			$ethm->setUnit($_POST['ethm_unit']); 
		}
		
		if(isset($_POST['ethm_limit'])) {
			$ethm->setLimit((int) $_POST['ethm_limit']); 
		}
		
		if(isset($_POST['ethm_related_foreground'])) {
			$ethm->setRelatedForegroundColor($_POST['ethm_related_foreground']); 
		}
		
		if(isset($_POST['ethm_related_background'])) {
			$ethm->setRelatedBackgroundColor($_POST['ethm_related_background']); 
		}
		
		$ethm->putOptions(); 
		$goback = add_query_arg('updated', 'true', $goback);
		wp_redirect($goback);
	    break;
	} else if($_POST['action'] == 'reset') {
		
		$ethm->setDefaultHeatMapParams(); 
		$ethm->putOptions(); 
		$goback = add_query_arg('reset', 'true', $goback);
		wp_redirect($goback);
	    break;
	}
	
}

/**
 * show the reset notice when the options are reset
 * 
 * @author	Aditya Naik
 * @version	1.0
 * @return 	void 
 */
function show_enhanced_tag_heat_map_reset_notice() {
	?>
	<div id="message" class="updated fade"><p><strong><?php _e('Settings Reset to default values.') ?></strong></p></div>
	<?php
}

/**
 * show update notice when the options are updated
 * 
 * @author	Aditya Naik
 * @version 1.0
 * @return 	void
 */
function show_enhanced_tag_heat_map_update_notice() {
	?>
	<div id="message" class="updated fade"><p><strong><?php _e('Settings Updated.') ?></strong></p></div>
	<?php
}
?>