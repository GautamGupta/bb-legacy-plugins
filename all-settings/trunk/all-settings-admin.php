<?php

function all_settings() {

if ( !bb_current_user_can('use_keys') )
	bb_die(__('Cheatin&#8217; uh?'));

	?>

  <h2><?php _e('All Settings'); ?></h2>
  <form name="form" action="<?php echo bb_get_admin_tab_link("all_settings"); ?>" method="post" id="all-settings">
    
  <?php bb_nonce_field('all-settings') ?>
  <input type="hidden" name="action" value="update" />
  <table class="widefat">
<?php
global $cache_options, $db_options, $bb_options;
$options=all_settings_options();

foreach ( (array) $options as $option) :
	$disabled = '';
	$bb_config=false;
	if ($option->option_value != $cache_options[$option->option_name]->option_value && $option->option_value != $db_options[$option->option_name]->option_value) {$bb_config=true;}
	$option->option_name = attribute_escape($option->option_name);
	if ( is_serialized($option->option_value) ) {
		if ( is_serialized_string($option->option_value)) {
			// this is a serialized string, so we should display it
			$value = maybe_unserialize($option->option_value);
			$options_to_update[] = $option->option_name;
			$class = 'all-settings';
		} else {
			$value = 'SERIALIZED DATA';
			$disabled = ' disabled="disabled"';
			$class = 'all-settings disabled';
		}
	} elseif (is_array($option->option_value) || is_object($option->option_value) ) {		
			$value = 'SERIALIZED DATA';
			$disabled = ' disabled="disabled"';
			$class = 'all-settings disabled';		
	} elseif (is_bool($option->option_value)) {
			$value = ($option->option_value) ? "true" : "false";
			$disabled = ' disabled="disabled"';
			$class = 'all-settings disabled';	
	} else {
		$value = $option->option_value;
		$options_to_update[] = $option->option_name;
		$class = 'all-settings';
	}
	echo "
<tr>
	<th scope='row'><label for='$option->option_name'>$option->option_name</label></th>
<td>";

	if (strpos($value, "\n") !== false) echo "<textarea class='$class' name='$option->option_name' id='$option->option_name' cols='30' rows='5'>" . wp_specialchars($value) . "</textarea>";
	else echo "<input class='$class' type='text' name='$option->option_name' id='$option->option_name' size='30' value='" . attribute_escape($value) . "'$disabled />";
	
	if ($bb_config) {echo " (<small>bb-config.php</small>) ";}

	echo "</td>
</tr>";
endforeach;
?>
  </table>
<?php $options_to_update = implode(',', $options_to_update); ?>
<p class="submit"><input type="hidden" name="page_options" value="<?php echo $options_to_update; ?>" /><input type="submit" name="Update" value="<?php _e('Save Changes') ?>" /></p>

<p><small>(bb-config.php) indicates option is either currently set in bb-config.php or might only be changeable from there via $bb->option=value;</small></p> 
  
  </form>

<?php
}

function all_settings_options() {
global $bb, $bbdb, $bb_topic_cache, $cache_options, $db_options, $bb_options;

bb_cache_all_options();
foreach ((array) $bb_topic_cache[0] as $name=>$value) {$cache_options[$name]->option_name=$name; $cache_options[$name]->option_value=$value;}

$options=$bbdb->get_results("SELECT meta_key as option_name, meta_value as option_value FROM $bbdb->topicmeta WHERE topic_id = 0 ORDER BY option_name");
foreach ((array) $options as $option) {$db_options[$option->option_name]->option_name=$option->option_name; $db_options[$option->option_name]->option_value=$option->option_value;}

foreach ($bb as $name => $value) {$bb_options[$name]->option_name=$name; $bb_options[$name]->option_value=$value;}

if ( version_compare(PHP_VERSION, '5.0', '>') ) {$constants=get_defined_constants(true); $constants=$constants['user'];}  // to do							

$options = array_merge($cache_options, $db_options, $bb_options);		// merge options from everywhere

// print_r($options);

unset($options['topic_id']);	// messy

return $options;
}

function all_settings_process_post() {
if (empty($_POST) || $_POST['action']!="update") {return;}

if ( !bb_current_user_can('use_keys') )
	bb_die(__('Cheatin&#8217; uh?'));
	bb_check_admin_referer('all-settings');

	$any_changed = 0;
	
	// print_r($_POST); exit();

	if ( !$_POST['page_options'] ) {
		foreach ( (array) $_POST as $key => $value) {
			if ( !in_array($key, array('_wpnonce', '_wp_http_referer')) )
				$options[] = $key;
		}
	} else {
		$options = explode(',', stripslashes($_POST['page_options']));
	}

	if ($options) {
		$compare=all_settings_options();
			
		foreach ($options as $option) {
			$option = trim($option);
			$value = $_POST[$option];
			if(!is_array($value))	$value = trim($value);
			$value = stripslashes_deep($value);
			if ($compare[$option]->option_value!=$value) {bb_update_option($option, $value); $any_changed++;}
		}
	}

	bb_admin_notice("<b>All Settings: $any_changed ".__('updated.')."</b>"); 

	// $goback = add_query_arg('updated', 'true', wp_get_referer());  wp_redirect($goback);  break;

}
add_action( 'bb_admin-header.php','all_settings_process_post');

?>