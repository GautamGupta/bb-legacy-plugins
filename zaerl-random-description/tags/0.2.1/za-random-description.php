<?php
/*
Plugin Name: zaerl Random Description
Plugin Description: generate a random site description from a list of sentences
Version: 0.2.1
Plugin URI: http://www.zaerl.com
Author: zaerl
Author URI: http://www.zaerl.com

zaerl Random Description: random description for bbPress
Copyright (C) 2010  Francesco Bigiarini

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

*/
	
define('ZA_RANDOM_DESC_VERSION', '0.2');
define('ZA_RANDOM_DESC_ID', 'za-random-description');
define('ZA_RANDOM_DESC_NAME', 'zaerl Random Description');

$za_rd_settings;
	
function za_rd_initialize()
{
	bb_load_plugin_textdomain(ZA_RANDOM_DESC_ID, dirname(__FILE__) . '/languages');
	
	global $za_rd_settings;
	$za_rd_settings = bb_get_option('za_random_description');

	if(empty($za_rd_settings)) $za_rd_settings = array();
}

add_action('bb_init', 'za_rd_initialize');

function za_rd_sentences($description)
{
	global $za_rd_settings;
	
	if(!empty($za_rd_settings))
	{
		$ret = $za_rd_settings[array_rand($za_rd_settings)];

		if(!empty($ret)) return str_ireplace('%description%', $description, $ret);
	}
	
	return $description;
}

add_filter('bb_option_description', 'za_rd_sentences');

function za_rd_configuration_page()
{
	global $za_rd_settings;
?>
<h2><?php
/* Translators: %s is replaced by the program name */
printf(__('%s Settings', ZA_RANDOM_DESC_ID), ZA_RANDOM_DESC_NAME);
?></h2>
<?php do_action('bb_admin_notices'); ?>

<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', 
	array('plugin' => 'za_rd_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
	<fieldset>
<?php

	if(!empty($za_rd_settings))
	{
		$count = count($za_rd_settings);
		$r = __('Remove this description');
		$d = __('Delete');

		for($i = 0; $i < $count; ++$i)
		{
			$uri_hide = esc_url(bb_nonce_url(bb_get_uri('bb-admin/admin-base.php',
				array('plugin' => 'za_rd_configuration_page', 'i' => $i),
				BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN),
				'delete-description'));

			bb_option_form_element("za_rd_description_$i", array(
				/* Translators: %u is a cardinal number */
				'title' => sprintf(__('Random Description %u', ZA_RANDOM_DESC_ID), $i + 1),
				'value' => $za_rd_settings[$i],
				'after' => "[<a href=\"$uri_hide\" title=\"$r\">$d</a>]"
			));
		}
	
		echo '<hr class="settings" />';
	}
	
	bb_option_form_element('za_rd_add_new', array(
		'title' => __('Add New Description', ZA_RANDOM_DESC_ID),
		//'value' => 'ciao',
		//'after' => '<strong>ciao!</strong>'
		'note' => __('Add a new description. <code>%description%</code> is replaced by the value "tagline" specified on bb-admin/options-general.php.', ZA_RANDOM_DESC_ID),
	));
?>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field('options-za-rd-update'); ?>
		<input type="hidden" name="action" value="update-za-rd-settings" />
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', ZA_RANDOM_DESC_ID) ?>" />
	</fieldset>
</form>
<?php
}

function za_rd_configuration_page_add()
{
	bb_admin_add_submenu(ZA_RANDOM_DESC_NAME, 'administrate', 'za_rd_configuration_page', 'options-general.php');
}

add_action('bb_admin_menu_generator', 'za_rd_configuration_page_add');

function za_rd_configuration_page_process()
{
	global $za_rd_settings;
	$changed = FALSE;
	
	if('post' == strtolower($_SERVER['REQUEST_METHOD']) &&
		$_POST['action'] == 'update-za-rd-settings')
	{
		bb_check_admin_referer('options-za-rd-update');

		$goback = remove_query_arg(array('za-rd-updated', 'i'),
			wp_get_referer());

		if(empty($za_rd_settings))
		{
			$s = stripslashes_deep(trim($_POST['za_rd_add_new']));

			if(!empty($s))
			{
				$goback = add_query_arg('za-rd-updated', 'true', $goback);
				$za_rd_settings = array($s);
				
				bb_update_option('za_random_description', $za_rd_settings);
			}
			
			bb_safe_redirect($goback);
			exit;
		}
		
		$nv = array();

		for($i = 0; $i >= 0; ++$i)
		{
			if(!isset($_POST["za_rd_description_$i"])) break;

			$s = stripslashes_deep(trim($_POST["za_rd_description_$i"]));
			if(!empty($s)) $nv[] = $s;
		}
		
		if(!empty($_POST['za_rd_add_new'])) $nv[] = stripslashes_deep(trim($_POST['za_rd_add_new']));
		
		$za_rd_settings = $nv;

		bb_update_option('za_random_description', $za_rd_settings);
		$goback = add_query_arg('za-rd-updated', 'true', $goback);
		bb_safe_redirect($goback);
		exit;
	}

	if(!empty($_GET['za-rd-updated']))
		bb_admin_notice(__('<strong>Settings saved.</strong>', ZA_RANDOM_DESC_ID));
	elseif(isset($_GET['i']))
	{
		bb_check_admin_referer('delete-description');
		
		$goback = remove_query_arg(array('za-rd-updated', 'i'),
			wp_get_referer());
		
		global $bb_current_user;
		$i = $_GET['i'];

		if(!$bb_current_user->has_cap('administrate'))
		{
			wp_redirect(bb_get_uri(null, null, BB_URI_CONTEXT_HEADER));
			exit;
		}
		 
		if(!is_numeric($i) || empty($za_rd_settings) || $i < 0 ||
			$i >= count($za_rd_settings))
			bb_die(__('Wrong Request.', ZA_RANDOM_DESC_ID));

		array_splice($za_rd_settings, $i, 1);

		if(empty($za_rd_settings)) bb_delete_option('za_random_description');
		else bb_update_option('za_random_description', $za_rd_settings);
		
		$goback = add_query_arg('za-rd-updated', 'true', $goback);
		bb_safe_redirect($goback);
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}

add_action('za_rd_configuration_page_pre_head', 'za_rd_configuration_page_process');

?>