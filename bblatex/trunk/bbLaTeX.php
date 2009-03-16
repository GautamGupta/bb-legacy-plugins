<?php
/*
Plugin Name: bbPress LaTeX
Plugin URI: http://www.math.ntnu.no/~stacey/HowDidIDoThat/bbPress/bbPressLaTeX
Description: Add LaTeX support to bbPress
Version: 0.1
Author: Andrew Stacey
Author URI: http://www.math.ntnu.no/~stacey
*/

/*
History:
  Loosely based on the wordpress plugin 'easy LaTeX'
   by Manoj Thulasidas (www.thulasidas.com)
*/

/*
Copyright (C) 2009 Andrew Stacey

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!class_exists("bbLaTeX")) {
  class bbLaTeX {
    function bbLaTeX() { //constructor
    }
    function init() {
      $this->getAdminOptions();
    }
    //Returns an array of admin options
    function getAdminOptions() {
      $mThemeName = bb_get_option('bb_active_theme') ;
      $mOptions = "bbLaTeX" . $mThemeName ;
      $bbLaTeXAdminOptions =
        array(
	      'cache' => dirname(__FILE__) . '/cache/',
	      'cacheurl' => rtrim(bb_get_option('uri'),"/") . '/' . basename(BB_PLUGIN_DIR) . '/cache/',
	      'latexserver' => 'http://l.wordpress.com/latex.php',
	      'imagefmt' => 'png',
              'text_colour' => '000000',
              'bg_colour' => 'FFFFFF',
	      'transparent' => '1',
              'tag' => 'latex',
              'size' => '0') ;

      $bbLaTeXOptions = bb_get_option($mOptions);
      if (!empty($bbLaTeXOptions)) {
        foreach ($bbLaTeXOptions as $key => $option)
          if ($option) { // blank option means use default
	    $bbLaTeXAdminOptions[$key] = stripslashes($option);
	  }
      }
      bb_update_option($mOptions, $bbLaTeXAdminOptions);
      return $bbLaTeXAdminOptions;
    }

  function saveTex ($toParse)
  {
    // save math code inside <bblatex></bblatex> tags.
    // other filters should be told to ignore text between these tags.

    $bbLaTeXOptions = $this->getAdminOptions();
    $tag = $bbLaTeXOptions['tag'] ;
    $regex = '#\[math\] *(.*?)\[/math\]#si';
    if ($tag == 'tex') $regex = '#\$\$(.*?)\$\$#si';
    if ($tag == 'latex') $regex = '#\\\\\[(.*?)\\\\\]#si';
    return preg_replace($regex, '<bblatex>$1</bblatex>', $toParse);
  }
  
  function parseTex ($toParse)
  {
    $regex = '#<bblatex>(.*?)</bblatex>#si';
    return preg_replace_callback($regex, array(&$this, 'createTex'), $toParse);
  }

  function createTex($toTex)
  {
    $formula_text = $toTex[1];
    $imgtext=false;

    if (substr($formula_text, -1, 1) == "!")
      return "$$".substr($formula_text, 0, -1)."$$";

    if (substr($formula_text, 0, 1) == "!") {
      $imgtext=true;
      $formula_text=substr($formula_text, 1);
    }

    $bbLaTeXOptions = $this->getAdminOptions();

    $formula_hash = md5($formula_text . 
			$bbLaTeXOptions['bg_colour'] .
			$bbLaTeXOptions['text_colour'] .
			$bbLaTeXOptions['size'] .
			$bbLaTeXOptiona['transparent']);

    $formula_filename = 'tex_' . $formula_hash . '.' . $bbLaTeXOptions['imagefmt'];

    $cache_formula_path = rtrim($bbLaTeXOptions['cache'], "/") . '/' . $formula_filename;
    $cache_formula_url = rtrim($bbLaTeXOptions['cacheurl'], "/") . '/' . $formula_filename;

    if ( !is_file($cache_formula_path))
    {
      if ( !is_writable($bbLaTeXOptions['cache']))
	{
	   return '<i>$$' . $formula_text . '$$</i>' ;
	}

      $formula_url =  $bbLaTeXOptions['latexserver'] . '?latex=' . rawurlencode(html_entity_decode($formula_text)) .
        '&bg=' . $bbLaTeXOptions['bg_colour'] .
        '&fg=' . $bbLaTeXOptions['text_colour'] .
        '&s=' . $bbLaTeXOptions['size'];

      $formula_image = imagecreatefrompng($formula_url);

      if ($bbLaTeXOptions['transparent'])
	{
//	$colour = imagecolorallocate($formula_image, hexdec('0x' . $bgColour{0} . $bgColour{1}), hexdec('0x' . $bgColour{2} . $bgColour{3}), hexdec('0x' . $bgColour{4} . $bgColour{5}));
	$colour = imagecolorat($formula_image, 1,1);
	imagecolortransparent($formula_image, $colour);
	}

     imagepng($formula_image, $cache_formula_path, 9);
    }
      $formula_output =  '<img src="' . $cache_formula_url .  '" title="' . $formula_text .
        '" style="vertical-align:-20%;" class="tex" alt="' . $formula_text . '" />' ;

    // returning the image-tag, referring to the image in your cache folder
    if($imgtext) return '<center>' . $formula_output . '</center>' ;
    return $formula_output ;
  }

  }
} //End Class bbLaTeX

if (class_exists("bbLaTeX")) {
  $bb_LaTeX = new bbLaTeX();
  if (isset($bb_LaTeX)) {
 
    add_filter('pre_post', array($bb_LaTeX, 'saveTex'), 50);
    add_filter('post_text', array($bb_LaTeX, 'parseTex'), 52);
    add_action('bb_admin_menu_generator', 'bbLaTeX_admin_page_add');
    add_filter('get_allowed_markup', 'bbLaTeX_allowed_markup', 2);
    add_filter('bb_allowed_tags', 'bbLaTeX_allow_tag');
  }

  function bbLaTeX_allow_tag( $tags ) {
    $tags['bblatex'] = array();
    return $tags;
  }

  function bbLaTeX_allowed_markup ($previous = '') {
    global $bb_LaTeX;
// Remove bblatex from list of _displayed_ allowed tags
    $premarkup = preg_replace('/bblatex/', '', $previous);
    if ($previous) $premarkup = $premarkup . '<br />';
    $bbLaTeXOptions = $bb_LaTeX->getAdminOptions();
    $tag = $bbLaTeXOptions['tag'] ;
    $style = '[math]x^2 + y^2 = 1[/math]';
    if ($tag == 'tex') $style = '$$x^2 + y^2 = 1$$';
    if ($tag == 'latex') $style = '\[x^2 + y^2 = 1\]';
    return $premarkup . 'Uses bbLaTeX.  Write LaTeX syntax as ' . $style;
  }

  function bbLaTeX_admin_page_add() {
    bb_admin_add_submenu(__('bbLaTeX Configuration'), 'use_keys', 'bbLaTeXprintAdminPage');
  }

  //Prints out the admin page
  function bbLaTeXprintAdminPage() {
    global $bb_LaTeX;
    $mThemeName = bb_get_option('bb_active_theme') ;
    $mOptions = "bbLaTeX" . $mThemeName ;
    $bbLaTeXOptions = $bb_LaTeX->getAdminOptions();
  
    if (isset($_POST['bbLaTeX_create_cache'])) {
      if (!@mkdir($bbLaTeXOptions['cache'],0755))
	{
	  ?>
	  <div class="updated"><p><strong><?php _e("Couldn't create cache.  Check settings and permissions and try again.", "bbLaTeX");?></strong></p></div>
	  <?php
	} else {
	
	?>
	<div class="updated"><p><strong><?php _e("Cache successfully created.  Make sure you reset the permissions of the parent directory.", "bbLaTeX");?></strong></p></div>
	<?php
      }
    }

    if (isset($_POST['bbLaTeX_clear_cache'])) {
      if ($dh = @opendir($bbLaTeXOptions['cache'])) {
	while (false !== ($file = readdir($dh))) {
	  if (is_file($bbLaTeXOptions['cache'] . '/' . $file)) {
	    unlink($bbLaTeXOptions['cache'] . '/' . $file);
	  }
	}
      }
      ?>
      <div class="updated"><p><strong><?php _e("Cache cleared.", "bbLaTeX");?></strong></p></div>
      <?php
    }

    if (isset($_POST['update_bbLaTeXSettings'])) {

      foreach(array_keys($bbLaTeXOptions) as $key)
	{
	  if (isset($_POST['bbLaTeX_' . $key])) {
	    $bbLaTeXOptions[$key] = $_POST['bbLaTeX_' . $key];
	  }
	}

      bb_update_option($mOptions, $bbLaTeXOptions);

      ?>
      <div class="updated"><p><strong><?php _e("Settings Updated.", "bbLaTeX");?></strong></p></div>
      <?php
    }

    if (isset($_POST['reset_bbLaTeXSettings'])) {

      foreach(array_keys($bbLaTeXOptions) as $key)
	{
	  $bbLaTeXOptions[$key] = '';
	}
      	  
      bb_update_option($mOptions, $bbLaTeXOptions);
      // Reread from database to regenerate defaults.
      $bbLaTeXOptions = $bb_LaTeX->getAdminOptions();

      ?>
      <div class="updated"><p><strong><?php _e("Settings Reset to Defaults.", "bbLaTeX");?></strong></p></div>
      <?php
    }

    $cache_error_msg = '';
    if (!file_exists($bbLaTeXOptions['cache'])) {
      $cache_error_msg = 'It doesn\'t exist.<br />';
    } else {
      if (! is_writable($bbLaTeXOptions['cache'])) {
	$cache_error_msg .= 'I can\'t write to it.<br />';
      }
      if (! is_readable($bbLaTeXOptions['cache'])) {
	$cache_error_msg .= 'I can\'t read from it.<br />';
      }
    }

    ?>
         
    <div class=wrap>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <h2>bbLaTeX Setup</h2>

    <h3>Instructions</h3>

    <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
    <?php if ($cache_error_msg) {
      ?>
      <li>
      <font color="red">Warning:</font>
      There is a problem with the cache:<br />
      <?php echo $cache_error_msg; ?>
      I need to be able to access the cache directory.
      The simplest and best way is if I (the webserver) create it.
      That way, I can read from and write to it, but no-one else has write access to it.
								   You will still be able to delete the whole directory because you own the parent directory so you will still have \'ultimate\' control over it.<br />
								   In order for me to be able to create the cache directory you will need to change the permissions on the parent directory to 0777 while I do so.
Once the directory has been created you can - and <strong>should</strong> change the permissions of the parent directory back to 0755.
On a U*nx system, use the <code>chmod</code> command to do this:<br />
<code>chmod 0777 directory</code>
      </li>

      <div class="submit">
      <input type="submit" name="bbLaTeX_create_cache" value="<?php _e('Create cache directory', 'bbLaTeX') ?>" /></div><br />
      <?php
    }
    ?>
    <li>
    Bracket your LaTeX formula with the tags. e.g. [math](a+b)^2 = a^2 + b^2 + 2ab[/math] and you will get:
    <br />
    <img src="<? echo  rtrim(bb_get_option('uri'),"/") . '/' . basename(BB_PLUGIN_DIR) . '/bbLaTeX_sample_small.png'; ?>" />
    </li>
    <li>
    Options:
    </li>
    <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
    <li>
    Decide the text and background colour for your equations to match your theme.
						     </li>
    <li>
    Decide the tags to bracket your LaTeX code.
      </li>
    <li>
    Choose the font size for your equations.
				    </li>
    </ul>
    </ul>

<h3>Options (for the <?php echo  ($mThemeName ? $mThemeName : 'default'); ?> theme)</h3>

<h4>Path Configuration</h4>
<table>
<tr>
<td width="15"></td>
<td>
Cache Directory:
</td>
<td>
<input type="text" style="border:0px solid;" value="<?php echo $bbLaTeXOptions['cache']; ?>" name="bbLaTeX_cache" size="60" />
</td>
</tr>
<tr>
<td width="15"></td>
<td>
Cache URL:
</td>
<td>
<input type="text" style="border:0px solid;" value="<?php echo $bbLaTeXOptions['cacheurl']; ?>" name="bbLaTeX_cacheurl" size="60" />
</td>
</tr>
<tr>
<td width="15"></td>
<td>
LaTeX Server Location:
</td>
<td>
<input type="text" style="border:0px solid;" value="<?php echo $bbLaTeXOptions['latexserver']; ?>" name="bbLaTeX_Server" size="60" />
</td>
</tr>
<tr>
<td></td>
<td>
<label for="bbLaTeX_imagefmt">
Image Format:
</td>
<td>
png: <input type="radio" id="bbLaTeX_imagefmt_png" name="bbLaTeX_imagefmt" value="png" <?php if ($bbLaTeXOptions['imagefmt'] == "png") { _e('checked="checked"', "bbLaTeX"); }?> />
gif: <input type="radio" id="bbLaTeX_imagefmt_gif" name="bbLaTeX_imagefmt" value="0" <?php if ($bbLaTeXOptions['imagefmt'] == "gif") { _e('checked="checked"', "bbLaTeX"); }?> />
</label>
</td>
</tr>
</table>

<h4>Colours (to match your theme)</h4>
<table>
<tr>
<td width="15"></td>
<td>
Text Colour:
</td>
<td>
<input type="text" style="border:0px solid;" value="<?php echo $bbLaTeXOptions['text_colour']; ?>" name="bbLaTeX_textColour" size="6" />
</td>
</tr>
<tr>
<td width="15"></td>
<td>
Background Colour:
</td>
<td>
<input type="text" style="border:0px solid;" value="<?php echo $bbLaTeXOptions['bg_colour']; ?>" name="bbLaTeX_bgColour" size="6" />
</td>
</tr>
<tr>
<td width="15"></td>
<td>
<label for="bbLaTeX_transparent">
Transparent Background:
</td>
<td>
On: <input type="radio" id="bbLaTeX_transparent_on" name="bbLaTeX_transparent" value="1" <?php if ($bbLaTeXOptions['transparent'] == "1") { _e('checked="checked"', "bbLaTeX"); }?> />
Off: <input type="radio" id="bbLaTeX_transparent_off" name="bbLaTeX_transparent" value="0" <?php if ($bbLaTeXOptions['transparent'] == "0") { _e('checked="checked"', "bbLaTeX"); }?> />
</label>
</td>
</tr>
</table>

<h4><label for="bbLaTeX_tag">Bracketting Tags</label></h4>
<table>
<tr>
<td width="15"></td>
<td>
<input type="radio" id="bbLaTeX_tag_math" name="bbLaTeX_tag" value="math" <?php if ($bbLaTeXOptions['tag'] == "math") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
[math] ... [/math]
</td>
<td>
phpBB Style
</td>
</tr>

<tr>
<td></td>
<td>
<input type="radio" id="bbLaTeX_tag_tex" name="bbLaTeX_tag" value="tex" <?php if ($bbLaTeXOptions['tag'] == "tex") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
$$ ... $$
</td>
<td>
TeX Style
</td>
</tr>

<tr>
<td></td>
<td>
<input type="radio" id="bbLaTeX_tag_latex" name="bbLaTeX_tag" value="latex" <?php if ($bbLaTeXOptions['tag'] == "latex") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
\[ ... \]
</td>
<td>
LaTeX Style
</td>
</tr>
</table>

<h4><label for="bbLaTeX_size">LaTeX Equation Font Size</label></h4>
<table>
<tr>
<td width="15"></td>
<td>
<input type="radio" id="bbLaTeX_size0" name="bbLaTeX_size" value="0" <?php if ($bbLaTeXOptions['size'] == "0") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
Small
</td>
<td>
<img style="vertical-align:-40%;" src="<? echo  bb_get_option('uri') . '/' . basename(BB_PLUGIN_DIR) . '/' . "/bbLaTeX_sample_small.png"; ?>" />
</td>
</tr>

<tr>
<td></td>
<td>
<input type="radio" id="bbLaTeX_size1" name="bbLaTeX_size" value="1" <?php if ($bbLaTeXOptions['size'] == "1") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
Medium
</td>
<td>
<img style="vertical-align:-40%;" src="<? echo  bb_get_option('uri') . '/' . basename(BB_PLUGIN_DIR) . '/' . "/bbLaTeX_sample_medium.png"; ?>" />
</td>
</tr>

<tr>
<td></td>
<td>
<input type="radio" id="bbLaTeX_size2" name="bbLaTeX_size" value="2" <?php if ($bbLaTeXOptions['size'] == "2") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
Large
</td>
<td>
<img style="vertical-align:-40%;" src="<? echo  bb_get_option('uri') . '/' . basename(BB_PLUGIN_DIR) . '/' . "/bbLaTeX_sample_large.png"; ?>" />
</td>
</tr>

<tr>
<td></td>
<td>
<input type="radio" id="bbLaTeX_size3" name="bbLaTeX_size" value="3" <?php if ($bbLaTeXOptions['size'] == "3") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
X-Large
</td>
<td>
<img style="vertical-align:-40%;" src="<? echo  bb_get_option('uri') . '/' . basename(BB_PLUGIN_DIR) . '/' . "/bbLaTeX_sample_xlarge.png"; ?>" />
</td>
</tr>

<tr>
<td></td>
<td>
<input type="radio" id="bbLaTeX_size4" name="bbLaTeX_size" value="4" <?php if ($bbLaTeXOptions['size'] == "4") { _e('checked="checked"', "bbLaTeX"); }?> />
</td>
<td>
XX-Large
</td>
<td>
<img style="vertical-align:-40%;" src="<? echo  bb_get_option('uri') . '/' . basename(BB_PLUGIN_DIR) . '/' . "/bbLaTeX_sample_xxlarge.png"; ?>" />
</td>
</tr>
</table>

<div class="submit">
<input type="submit" name="bbLaTeX_clear_cache" value="<?php _e('Clear cache', 'bbLaTeX') ?>" /></div><br />

<div class="submit">
<input type="submit" name="reset_bbLaTeXSettings" value="<?php _e('Reset to Defaults', 'bbLaTeX') ?>" /></div><br />

<div class="submit">
<input type="submit" name="update_bbLaTeXSettings" value="<?php _e('Save Changes', 'bbLaTeX') ?>" /></div>
</form>
<br />
<hr />


<h3>Credit</h3>
<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
<li>
<b>bbLaTeX</b> is loosely based on the Wordpress plugin <a href="http://wordpress.org/extend/plugins/easy-latex/" target="_blank">easy LaTeX</a>.  There is also considerable similarity with the <a href="http://wordpress.org/extend/plugins/latex/" target="_blank">LaTeX for Wordpress</a> plugin.
</li>
</ul>

</div>

<?php
}//End function printAdminPage()
} //End 'if class exists'


?>
