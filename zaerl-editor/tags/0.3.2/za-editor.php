<?php
/*
Plugin Name: zaerl Editor
Plugin URI: http://www.zaerl.com
Description: A better editor for bbPress
Version: 0.3.2
Author: Francesco Bigiarini
Author URI: http://www.zaerl.com

zaerl Editor: a better editor for bbPress
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

This software contains portions of code taken from phpMyAdmin 3.3.1
which is licensed under the GNU General Public License, version 2. You
can find further informations at this page: http://www.phpmyadmin.net/home_page/license.php

*/
	
define('ZA_EDITOR_VERSION', '0.3.2');
define('ZA_EDITOR_ID', 'za-editor');
define('ZA_EDITOR_NAME', 'zaerl Editor');

$za_ed_settings;
	
function za_ed_initialize()
{
	bb_load_plugin_textdomain(ZA_EDITOR_ID, dirname(__FILE__) . '/languages');
	
	global $za_ed_settings;

	$za_ed_settings = bb_get_option('za_editor');

	if(empty($za_ed_settings))
	{
		$za_ed_settings['selector'] = 'textarea#post_content';
		$za_ed_settings['custom_style'] = ".za_button { border: 1px solid #ccc; margin: 2px; min-width: 20px; background-color: transparent; }
.za_button:hover { border: 1px solid black; }
#za_button_italic { font-style:italic; }
#za_button_bold { font-weight:bold; }
#za_button_link { color: #2e6e15; }";
	}

	add_action('bb_topic.php', 'za_ed_header_js_pre');
	add_action('bb_edit-post.php', 'za_ed_header_js_pre');
	add_action('bb_forum.php', 'za_ed_header_js_pre');
	add_action('bb_front-page.php', 'za_ed_header_js_pre_front');	
}

add_action('bb_init', 'za_ed_initialize');

function za_ed_header_js_pre_front()
{
	if(isset($_GET['new']) && '1' == $_GET['new'])
		za_ed_header_js_pre(true);
}

function za_ed_header_js_pre($force)
{
	global $bb_current_user;
	
	if(!$bb_current_user) return;
	
	if($force == false)
	{
		$it = FALSE;
		$cu_can_wp = $bb_current_user->has_cap('write_posts');
		
		if(bb_is_topic() && $cu_can_wp)
		{
			global $topic, $page;

			$add = topic_pages_add();
			$last_page = get_page_number($topic->topic_posts + $add);
		
			if($last_page == $page) $it = true;
		}
		
		if($it == FALSE && bb_is_topic_edit() && $cu_can_wp) $it = true;
		
		global $forum;
		
		if($it == FALSE && isset($forum) && !$forum->forum_is_category &&
			$bb_current_user->has_cap('write_topics'))
			$it = true;
	} else $it = true;
	
	if($it == true)
	{
		bb_enqueue_script('jquery');
		add_action('bb_head', 'za_ed_header_js', 100);
	}
}

function za_ed_header_js()
{
	global $za_ed_settings;

	if($za_ed_settings['custom_style'])
		echo "<style type=\"text/css\">\n", $za_ed_settings['custom_style'], '</style>';
?>

<script type="text/javascript">//<![CDATA[
	
	var za_buttons = new Array();
	
	function za_button(id, display, tagStart, tagEnd, title)
	{
		this.id = id;
		this.display = display;
		this.tagStart = tagStart;
		this.tagEnd = tagEnd;
		this.title = title;
	}
<?php

	$tags = bb_allowed_tags();

	if(isset($tags['strong']))
	{
		echo "za_buttons.push(new za_button('bold' ,'strong' ,'<strong>' ,'</strong>' ,'";
		/* Translators: bold (text) */
		echo esc_js(__('Bold', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['em']))
	{
		echo "za_buttons.push(new za_button('italic', 'em', '<em>', '</em>', '";
		/* Translators: italic (text) */
		echo esc_js(__('Italic', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['a']))
	{
		echo "za_buttons.push(new za_button('link', 'a', '', '', '";
		echo esc_js(__('Hyperlink', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['img']))
	{
		echo "za_buttons.push(new za_button('img', 'img', '', '', '";
		/* Translators: "image" refers to jpg/png or such files */
		echo esc_js(__('Image', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['ul']))
	{
		echo "za_buttons.push(new za_button('ul', 'ul', '<ul>\\n   <li>', '</li>\\n</ul>', '";
		/* Translators: the HTML list tag ul */
		echo esc_js(__('Unordered list', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['ol']))
	{
		echo "za_buttons.push(new za_button('ol', 'ol', '<ol>\\n   <li>', '</li>\\n</ol>', '";
		/* Translators: the HTML list tag ol */
		echo esc_js(__('Ordered list', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['li']))
	{
		echo "za_buttons.push(new za_button('li', 'li', '   <li>', '</li>', '";
		/* Translators: the HTML list tag li */
		echo esc_js(__('List item', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['blockquote']))
	{
		echo "za_buttons.push(new za_button('block', 'quote', '<blockquote>', '</blockquote>', '";
		/* Translators: verb, mention or refer to someone article */
		echo esc_js(__('Quote', ZA_EDITOR_ID)), "'));\n";
	}

	if(isset($tags['code']))
	{
		echo "za_buttons.push(new za_button('code', 'code', '`', '`', '";
		/* Translators: noun, like in "software source code". */
		echo esc_js(__('Code', ZA_EDITOR_ID)), "'));";
	} ?>

	jQuery(document).ready(function()
	{
		jQuery('<?php echo $za_ed_settings['selector']; ?>').before('<div id="za_toolbar"></div>');
		var za_t = jQuery("#za_toolbar");
		
<?php
		// BUG: bbPress 1.0.2 Kakumei theme. `edit-form.php' template miss the `for' attribute for `post_content' label
		if(bb_is_topic_edit() && bb_get_option('version') == '1.0.2')
		{
			//$at = bb_get_option('bb_active_theme');
			
			//if(!$at || $at == 'core#kakumei-blue')
			echo "\t\tza_t.parent().attr({'for': 'post_content'})";
		}
?>
		
		for (i = 0; i < za_buttons.length; i++)
		{
			var za_i = za_buttons[i];
			var za_b = jQuery('<input type="button" class="za_button" />').attr({ id: 'za_button_' + za_i.id, value: za_i.display, title: za_i.title, name: 'za_button_' + za_i.id});

			if(za_i.id == 'img') za_b.bind('click', function(event) { za_add_img() } );
			else if(za_i.id == 'link') za_b.bind('click', function(event) { za_add_link() } );
			else
			{
				za_b.bind('click', {index: i}, function(event)
				{
					jQuery("textarea#post_content").surroundCaret(za_buttons[event.data.index].tagStart,
						za_buttons[event.data.index].tagEnd);
				});
			}
			
			za_t.append(za_b);
		}});
		
	function za_add_link()
	{
		var URL = prompt("<?php echo esc_js(__('Insert the URL', ZA_EDITOR_ID)) ?>", "http://");
		if(URL) jQuery("textarea#post_content").surroundCaret('<a href="' + URL + '">', '</a>');
	}
	
	function za_add_img()
	{
		var URL = prompt("<?php echo esc_js(__('Insert the image URL', ZA_EDITOR_ID)) ?>", "http://");
		if(URL) jQuery("textarea#post_content").surroundCaret('<img src="' + URL + '" alt="', '" />');	
	}
	
	jQuery.fn.surroundCaret = function(leftValue, rightValue)
	{
		return this.each(function()
		{
			if(document.selection)
			{
				this.focus();
				var sel = document.selection.createRange();
				sel.text = leftValue + sel.text + rightValue;
				this.focus();
			} else if (this.selectionStart || this.selectionStart == '0')
			{
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var cursorPos = endPos;
				var scrollTop = this.scrollTop;
				
				this.value = this.value.substring(0, startPos) + leftValue
					+ this.value.substring(startPos, endPos) + rightValue
					+ this.value.substring(endPos, this.value.length);
				cursorPos += leftValue.length;

				this.focus();
				this.selectionStart = cursorPos;
				this.selectionEnd = cursorPos;
				this.scrollTop = scrollTop;
			} else
			{
				this.value = leftValue + this.value + rightValue;
			}
		});
	};
//]]></script>
<?php
}

function za_ed_configuration_page()
{
	global $za_ed_settings;
?>
<h2><?php /* Translators: %s is replaced by the program name */ printf(__('%s Settings', ZA_EDITOR_ID), ZA_EDITOR_NAME); ?></h2>
<?php do_action('bb_admin_notices'); ?>

<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', 
	array('plugin' => 'za_ed_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
	<fieldset>
<?php

	bb_option_form_element('za_ed_selector', array(
		'title' => /* Translators: see http://www.w3.org/TR/CSS2/selector.html for the meaning of `CSS selector'. Usually this is not translated */
			__('CSS selector', ZA_EDITOR_ID),
		'value' => $za_ed_settings['selector'],
		'note' => /* Translators: %s is replaced by the program name. See http://www.w3.org/TR/CSS2/selector.html for the meaning of `selector' */
			sprintf(__('%s inject the toolbar before elements that match a CSS selector. You can modify the default selector but do change this value only if you know what you are doing.', ZA_EDITOR_ID), ZA_EDITOR_NAME)
	));
?>
		<div id="option-za-editor-style">
			<div class="label"><?php /* Translators: acronym of Cascading Style Sheet */ _e('Custom CSS', ZA_EDITOR_ID) ?></div>
			<div class="inputs">
				<textarea name='za_ed_custom_style' id='za-editor-style' rows="10" cols="50"><?php echo $za_ed_settings['custom_style']; ?></textarea>
				<p><?php _e('Custom style for the editor toolbar', ZA_EDITOR_ID); ?></p>
			</div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field('options-za-editor-update'); ?>
		<input type="hidden" name="action" value="update-za-editor-settings" />
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', ZA_EDITOR_ID) ?>" />
	</fieldset>
</form>
<?php
}

function za_ed_configuration_page_add()
{
	bb_admin_add_submenu(ZA_EDITOR_NAME, 'moderate', 'za_ed_configuration_page', 'options-general.php');
}

add_action('bb_admin_menu_generator', 'za_ed_configuration_page_add');

function za_ed_configuration_page_process()
{
	global $za_ed_settings;
	$changed = FALSE;

	if('post' == strtolower($_SERVER['REQUEST_METHOD']) &&
		$_POST['action'] == 'update-za-editor-settings')
	{
		bb_check_admin_referer('options-za-editor-update');

		$goback = remove_query_arg(array('za-editor-invalid-selector',
			'za-editor-void-selector', 'za-editor-updated'), wp_get_referer());

		if(isset($_POST['za_ed_use_emoticons']))
		{
			$val = (bool)$_POST['za_ed_use_emoticons'];
			
			if($za_ed_settings['use_emoticons'] != val)
			{
				$za_ed_settings['use_emoticons'] = val;
				$changed = true;
			}		
		}

		if(isset($_POST['za_ed_custom_style']))
		{
			$val = $_POST['za_ed_custom_style'];

			if($val && $val != $za_ed_settings['custom_style'])
			{
				$za_ed_settings['custom_style'] = stripslashes_deep($val);
				$changed = true;
			}
		}
		
		if(isset($_POST['za_ed_selector']))
		{
			$value = stripslashes_deep(trim($_POST['za_ed_selector']));
		
			if($value)
			{
				$za_ed_settings['selector'] = stripslashes_deep($value);
				$changed = true;
			} else
			{
				$goback = add_query_arg('za-editor-void-selector', 'true', $goback);
				bb_safe_redirect($goback);
				exit;
			}
		}
		
		if($changed == true) bb_update_option('za_editor', $za_ed_settings);

		$goback = add_query_arg('za-editor-updated', 'true', $goback);
		bb_safe_redirect($goback);
		exit;
	}

	if(!empty($_GET['za-editor-updated']))
		bb_admin_notice(__('<strong>Settings saved.</strong>', ZA_EDITOR_ID));
	else if(!empty($_GET['za-editor-invalid-selector']))
	{
		/* Translators: see http://www.w3.org/TR/CSS2/selector.html for the meaning of `selector'. Usually this is not translated */
		bb_admin_notice(('<strong>The selector you have specified is invalid</strong>'), 'error');
	} else if(!empty($_GET['za-editor-void-selector']))
	{
		/* Translators: see http://www.w3.org/TR/CSS2/selector.html for the meaning of `selector'. Usually this is not translated */
		bb_admin_notice(('<strong>The selector you have specified is void</strong>'), 'error');
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}

add_action('za_ed_configuration_page_pre_head', 'za_ed_configuration_page_process');

?>