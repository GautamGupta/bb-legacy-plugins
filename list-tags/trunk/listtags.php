<?php
/*
Plugin Name: ListTags
Plugin URI: http://www.jmnieto.com
Description: Display a span list for click tags
Author: jmnieto
Author URI: http://jmnieto.com/
Version: 1
*/
/* use:
Activate the plugin
The list of tags show in new post you can click to add.

To show in topic tags put <?php <?php  if (function_exists('list_tags_form')) {list_tags_form();} ?>
in tag-form.php bottom of <input type="submit" name="Submit" id="tagformsub" value="<?php echo attribute_escape( __('Add &raquo;') ); ?>" />
	
*/
//Modify if you like temrs of Wordpress
$integrateWP = true;	
/*

############## Ajax ##############

	/**
	 * Display a span list for click tags or a javascript collection for autocompletion script !
	 *
	 * @param string $format
	 */
	function ajaxLocalTags( ) {
		global  $integrateWP;
		echo '<script type="text/javascript">collection = [';

			global $wpdb,$bbdb;

			// Get all terms of Wordpress
				if ($integrateWP) {
					$WPterms = $wpdb->get_col("
						SELECT DISTINCT name
						FROM {$wpdb->terms} AS t
						INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
						WHERE tt.taxonomy = 'post_tag'	
					");
				}
			// Get all terms of BBpress
				$BBtags = $bbdb->get_col( "
					SELECT DISTINCT raw_tag 
					FROM $bbdb->tags 
					WHERE tag_count <> 0 
					ORDER BY raw_tag ASC
				" );
					
				if ($integrateWP) {
					$terms = array_merge($WPterms, $BBtags); //merge the two arrays				
				} else { //only terms of BBpress
					$terms = $BBtags;//array terms = array terms of BBpress
				}
					
					
					$terms = array_unique($terms);// unique terms 
					asort($terms);//sort

						$flag = false;
						foreach ( (array) $terms as $term ) {		
							//$term = stripslashes($term);
							if ( $flag === false) {
								echo '"'.str_replace('"', '\"', $term).'"';
								$flag = true;
							} else {
								echo ', "'.str_replace('"', '\"', $term).'"';
							}
						}
				
				// Clean memory
				$terms = array();
				unset($terms, $term);
	
			echo '];</script>';
	}


	/**
	 * Show the list of tags.
	 *
	 */
	function showListTags( $id = 'old_tags_input' ) {
		if ( ((int) wp_count_terms('post_tag', 'ignore_empty=true')) == 0 ) { // If no tags => exit !
			return;
		}
		?>
		<script type="text/javascript" src="<?php  echo bb_get_option( 'uri' ) ?>my-plugins/listtags/inc/js/listtags.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo bb_get_option( 'uri' ) ?>my-plugins/listtags/inc/css/listtags.css" />
		<?php if ( 'rtl' == get_bloginfo( 'text_direction' ) ) : ?>
			<link rel="stylesheet" type="text/css" href="<?php echo bb_get_option( 'uri' ) ?>my-plugins/listtags/inc/css/listtags-rtl.css" />
		<?php endif; ?>
		<script type="text/javascript">
		// <![CDATA[
			jQuery(document).ready(function() {
				if ( document.getElementById('<?php echo ( empty($id) ) ? 'old_tags_input' : $id; ?>') ) {
					var tags_input = new BComplete('<?php echo ( empty($id) ) ? 'old_tags_input' : $id; ?>');
					tags_input.setData(collection);
				}
			});
		// ]]>
		</script>
		<?php
	}	

function list_tags( $args = '' ) {
	showListTags('tags-input'); 
}
/*funtion to call from form of tag-post.php*/
function list_tags_form( $args = '' ) {
	showListTags('tag'); 
}

add_action('post_form','list_tags');
//add_action('tag_form','list_tags_form');//Dont work
add_action('bb_head','ajaxLocalTags');

?>
