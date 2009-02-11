<?php
/****************************************************************************
Plugin Name: BB Scrippets
Plugin URI: http://scrippets.org
Description: Modifies screenplay format text for inclusion in web pages. Based on the scrippet concept and original code by <a href="http://johnaugust.com">John August</a>.
Author: Nima Yousefi
Author URI: http://equinox-of-insanity.com
Version: 1.2

This plugin uses the function found in the file "scrippetize.php" to create the
formatted HTML.
****************************************************************************/

require('scrippetize.php');

add_filter('post_text', 'build_scrippet', 1, 1);
add_action('bb_head', 'add_scrippet_css');
add_action('bb_admin_menu_generator', 'add_scrippets_admin_panel');

function build_scrippet($text) { 
    $settings    = bb_get_option('scrippet_options');
    $wrap_before = '';
    $wrap_after  = '';

    if($settings['border_style'] == 'Drop Shadow') {
        $wrap_before = "<div class=\"scrippet-shadow\">\n<div class=\"inner-shadow\">\n"; 
        $wrap_after  = "</div>\n</div>\n";
    }
    $text = scrippetize($text, $wrap_before, $wrap_after);  // see scrippetize.php for details
    return $text;
}


// Options & Admin Stuff
$default_options  = array('width' => '400', 'bg_color' => '#FFFFFC', 'text_color' => '#000000', 'border_style' => 'Simple', 'alignment' => 'Left');

if(!bb_get_option('scrippet_options')) {
    bb_update_option('scrippet_options', $default_options); // create the defaults
}

function add_scrippet_css($u) {
    // add the base CSS
    echo '<link rel="stylesheet" type="text/css" href="' . bb_get_option('uri') . '/bb-plugins/bb-scrippets/scrippets.css">' . "\n";
    
    // now modify CSS if necessary
    $settings = bb_get_option('scrippet_options');
    echo "\n\n<style>\n";
    echo "div.scrippet {\n";
    echo "\twidth: {$settings['width']}px;\n";
    echo "\tbackground-color: " . $settings['bg_color'] . ";\n";
    echo "\tcolor: {$settings['text_color']};\n";
    
    if($settings['alignment'] == 'Center' && $settings['border_style'] != 'Drop Shadow') {
        echo "\tmargin: 0 auto 16px auto !important;";
    }
    
    
    echo "}\n</style>\n\n";   // close the div.scrippet CSS block
        
    if($settings['border_style'] == 'Drop Shadow') {
        $shadow_width = $settings['width'] + 50;
        echo '<link rel="stylesheet" type="text/css" href="' . bb_get_option('uri') . '/bb-plugins/bb-scrippets/scrippet_shadow.css">' . "\n";
        echo "<style>\n";
        echo "div.scrippet {\n\tmargin-left: 0 !important; \n}\n\n";
        echo "div.inner-shadow {\n\tbackground-color: {$settings['bg_color']} !important; \n}\n\n";
        echo "div.scrippet-shadow {\n\twidth: {$shadow_width}px;\n";
        if($settings['alignment'] == 'Center') {
            echo "\tmargin: 0 auto;\n";
        }
        echo "}\n</style>\n\n";
    }

    echo "<!--[if IE]>\n<style>";
    echo "div.scrippet { margin-bottom: 0px !important; }\n";
    echo "</style>\n<![endif]-->\n\n";
    
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {   // need to modify the font to work with the iPhone
        echo "<style>\n\t.scrippet p { font: 7px/9px Courier, 'Courier New', monospace !important; }\n</style>\n\n";
    }

    if (stristr($_SERVER['HTTP_USER_AGENT'], 'Windows')) {   // need to modify the font to work better on Windows
        echo "<style>\n\t.scrippet p { font-family: 'Courier New', monospace !important; }\n</style>\n\n";
    }
    
}

function scrippets_options_panel() {
    global $default_options;
    $settings = bb_get_option('scrippet_options');
    $cs_home  = bb_get_option('uri') . '/bb-plugins/bb-scrippets/colorselector';
    ?>
    <script type="text/javascript" charset="utf-8">
        function reset_fields() {
            var form = document.getElementById('scrippets_form');
            form.width.value = '<?php echo $default_options['width'] ?>';
            form.bg_color.value = '<?php echo $default_options['bg_color'] ?>';
            form.text_color.value = '<?php echo $default_options['text_color'] ?>';
            form.border_style.selectedIndex = '<?php echo $default_options['border_style'] ?>';
            form.alignment.selectedIndex = '<?php echo $default_options['alignment'] ?>';
        }
        
        // set the values for the colorselector
        var CROSSHAIRS_LOCATION = '<?php echo $cs_home; ?>/crosshairs.png';
        var HUE_SLIDER_LOCATION = '<?php echo $cs_home; ?>/h.png';
        var HUE_SLIDER_ARROWS_LOCATION = '<?php echo $cs_home; ?>/position.png';
        var SAT_VAL_SQUARE_LOCATION = '<?php echo $cs_home; ?>/sv.png';
    </script>
    <script type="text/javascript" src="<?php echo bb_get_option('uri') ?>/bb-plugins/bb-scrippets/colorselector.js"></script>
    <div class="wrap">
        <h2>Scrippets Options</h2>
        <p>These are the Scrippet options that you can modify. If you'd like to return the
            options to their default state click the reset button in the bottom right corner of the page.</p>
        <form action="<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>" method="post" id="scrippets_form">
            <input type="hidden" name="action" value="save_options" />
            <table class="form-table">
                <tr align="top">
                    <th scope="row"><label for="width">Width of the Scippet box</label></th>
                    <td colspan="3"><input type="text" name="width" value="<?php echo $settings['width']; ?>" id="width" style="width:120px;"/> Default: <b><i><?php echo $default_options['width'] ?></i></b><br/>
                        Defines the width of the Scrippet box in pixels. </td>
                </tr>
                <tr align="top">
                    <th scope="row"><label for="border_style">Border Style</label></th>
                    <td colspan="3"><select name="border_style" style="width:120px;">
                            <option name="Simple" <?php if($settings['border_style'] == 'Simple') { echo 'selected'; } ?>>Simple</option>
                            <option name="Drop Shadow" <?php if($settings['border_style'] == 'Drop Shadow') { echo 'selected'; } ?>>Drop Shadow</option>
                        </select> Default: <b><i><?php echo $default_options['border_style'] ?></i></b><br/></td>
                </tr>
                <tr align="top">
                    <th scope="row"><label for="alignment">Alignment</label></th>
                    <td colspan="3"><select name="alignment" style="width:120px;">
                            <option name="Left" <?php if($settings['alignment'] == 'Left') { echo 'selected'; } ?>>Left</option>
                            <option name="Center" <?php if($settings['alignment'] == 'Center') { echo 'selected'; } ?>>Center</option>
                        </select> Default: <b><i><?php echo $default_options['alignment'] ?></i></b><br/>
                        The alignment of the Scrippet box on the page. </td>
                </tr>
                
                <tr align="top">
                    <th scope="row"><label for="bg_color">Background color</label></th>
                    <td><input type="text" name="bg_color" value="<?php echo $settings['bg_color']; ?>" id="bg_color" class="color" size="8"/><br/>
                        Default: <b><i><?php echo $default_options['bg_color'] ?></i></b>.</td>
                    <th scope="row"><label for="text_color">Text Color</label></th>
                    <td><input type="text" name="text_color" value="<?php echo $settings['text_color'] ?>" id="text_color" class="color" size="8"/><br/>
                        Default: <b><i><?php echo $default_options['text_color'] ?></i></b></td>
                </tr>
                
            </table>

            <p class="submit">
                <input type="submit" value="Save Changes" style="float: left;">
                <input type="submit" onclick="javascript:reset_fields();" value="Reset to Original Options" style="float:right;">
                <p style="clear:both;"></p>
            </p>
        </form>
    </div>
    <?php
}

function add_scrippets_admin_panel() {
    bb_admin_add_submenu('Scrippets', 'use_keys', 'scrippets_options_panel');
    //bb_admin_add_submenu('Scrippets', 'Scrippets', 8, __FILE__, 'scrippets_options_panel');
    //bb_admin_add_submenu(__('Akismet Configuration'), 'use_keys', 'bb_ksd_configuration_page');
}

function scrippets_save_options() {
    // Get all the options from the $_POST
    $scrippet_options['width']          = $_POST['width'];
    $scrippet_options['bg_color']       = $_POST['bg_color'];
    $scrippet_options['text_color']     = $_POST['text_color'];
    $scrippet_options['border_style']   = $_POST['border_style'];
    $scrippet_options['alignment']      = $_POST['alignment'];
    
    bb_update_option('scrippet_options', $scrippet_options);
}

if ($_POST['action'] == 'save_options'){
	scrippets_save_options();
}

?>