<?php
/*
Plugin Name: zaerl URL Preview
Plugin Description: Hyperlinks domain name preview in posts
Version: 0.1
Plugin URI: http://www.zaerl.com
Author: zaerl
Author URI: http://www.zaerl.com

zaerl URL Preview: hyperlinks domain name preview in posts.
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
	
define('ZA_UP_VERSION', '0.1');
define('ZA_UP_ID', 'za-url-preview');
define('ZA_UP_NAME', 'zaerl URL Preview');

function za_up_filter($text)
{
	return preg_replace_callback('/<a(?:.+)href(?:\s*)=(?:\s*)(?:"|\')([^"\']+)(?:"|\')(?:.*)>(.+)<\/a>/i', 'za_up_filter_callback', $text);
}

function za_up_filter_callback($matches)
{
	if(trim($matches[1]) == trim($matches[2])) return $matches[0];

	global $za_up_settings;
	$host = @parse_url($matches[1], PHP_URL_HOST);

	if($host !== false)
	{
		$pos = strpos($host, 'www.');
		
		if($pos !== false && $pos == 0 && strpos($host, '.', 4) !== false)
			$short_host = substr($host, 4);
		else $short_host = $host;
		
		$url = str_ireplace(array('%za_short_host%', '%za_host%'), array($short_host, $host), $za_up_settings);

		if(!empty($url)) return "$matches[0] $url";
	}

	return $matches[0];
}

function za_up_configuration_page_add()
{
	bb_admin_add_submenu(ZA_UP_NAME, 'moderate', 'za_up_configuration_page', 'options-general.php');
}

function za_up_initialize()
{
	global $za_up_settings, $bb_current_user;

	bb_load_plugin_textdomain(ZA_UP_ID, dirname(__FILE__) . '/languages');
	$za_up_settings = bb_get_option('za_url_preview');

	if(empty($za_up_settings)) $za_up_settings = '[%za_short_host%]';
	
	if($bb_current_user && $bb_current_user->has_cap('administrate'))
	{
		add_action('bb_admin_menu_generator', 'za_up_configuration_page_add');
		add_action('za_up_configuration_page_pre_head', 'za_up_configuration_page_process');	
	}
	
	add_filter('post_text', 'za_up_filter');
}

add_action('bb_init', 'za_up_initialize');

function za_up_configuration_page()
{
	global $za_up_settings;
?>
<h2><?php
/* Translators: %s is replaced by the program name */
printf(__('%s Settings', ZA_UP_ID), ZA_UP_NAME);
?></h2>
<?php do_action('bb_admin_notices'); ?>

<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', 
	array('plugin' => 'za_up_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
	<fieldset>
<?php
	bb_option_form_element('za_up_preference', array(
		'title' => __('URL Preview Template', ZA_UP_ID),
		'value' => $za_up_settings,
		'note' => __('Specify the template that will be used when displaying URL previews. <code>%za_host%</code> is replaced by the host name, <code>%za_short_host%</code> instead is replaced by the host name minus <strong>www.</strong>. A leading space is automatically added.', ZA_UP_ID)));
?>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field('options-za-up-update'); ?>
		<input type="hidden" name="action" value="update-za-up-settings" />
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', ZA_UP_ID) ?>" />
	</fieldset>
</form>
<?php
}

function za_up_configuration_page_process()
{
	global $za_up_settings;
	
	if('post' == strtolower($_SERVER['REQUEST_METHOD']) &&
		$_POST['action'] == 'update-za-up-settings')
	{
		bb_check_admin_referer('options-za-up-update');

		$goback = remove_query_arg(array('za-up-updated'), wp_get_referer());
		
		if(isset($_POST['za_up_preference']))
		{
			$np = stripslashes_deep($_POST["za_up_preference"]);

			if($za_up_settings != $np)
			{
				$za_up_settings = $np;
				bb_update_option('za_url_preview', $za_up_settings);
			}
		}

		$goback = add_query_arg('za-up-updated', 'true', $goback);
		bb_safe_redirect($goback);
		exit;
	}

	if(!empty($_GET['za-up-updated']))
		bb_admin_notice(__('<strong>Settings saved.</strong>', ZA_RANDOM_DESC_ID));

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}

?>