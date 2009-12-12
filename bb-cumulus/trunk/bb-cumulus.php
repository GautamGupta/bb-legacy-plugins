<?php
/*
Plugin Name: bb Cumulus
Plugin URI: http://gaut.am/bbpress/plugins/bb-cumulus/
Description: Flash based Tag Cloud for bbPress
Version: 1.0-beta
Author: Gautam Gupta
Author URI: http://gaut.am/
*/

/*	
	Copyright 2009, Gautam Gupta (email: admin@gaut.am)

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Create Text Domain For Translations
load_plugin_textdomain('bb-cumulus', '/my-plugins/bb-cumulus/languages/');

//defines
define('BBCUM_OPTIONS','bbCumulus');
define('BBCUM_VER','1.0-beta');
define('BBCUM_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/');

//initially set the options
$bbcum_plugopts = bb_get_option(BBCUM_OPTIONS);
if(!$bbcum_plugopts){
	//add defaults to an array
	$bbcum_plugopts = array(
		'width' => '150',
		'height' => '200',
		'tcolor' => 'ffffff',
		'tcolor2' => 'ffffff',
		'hicolor' => 'ffffff',
		'bgcolor' => '333333',
		'speed' => '100',
		'trans' => 0,
		'distr' => 1,
		//'args' => '',
		'compmode' => 0,
		'showbbtags' => 1,
	);
	bb_update_option(BBCUM_OPTIONS, $bbcum_plugopts);
}

// add the actions & filters
add_action('bb_admin_menu_generator', 'bb_cumulus_menu_link', -996);
add_filter('tag_heat_map', 'bb_cumulus_createflashcode', 997, 1);

// add the admin page
function bb_cumulus_menu_link() {
	if (function_exists('bb_admin_add_submenu')) {
		bb_admin_add_submenu( __( 'bb Cumulus', 'bb-cumulus' ), 'administrate', 'bb_cumulus_options', 'options-general.php' );
	}
}

// template function
function bb_cumulus_insert( $args = '' ){
	global $bbcum_plugopts;	
	$r = wp_parse_args($args, $bbcum_plugopts);
	bb_tag_heat_map($r);
}

// piece together the flash code
function bb_cumulus_createflashcode( $tagcloud = '' ){
	// get the options
	global $bbcum_plugopts;
	$options = $bbcum_plugopts;
	/*
	if($options['args']){
		$defaults = array( 'smallest' => 8, 'largest' => 22, 'limit' => 40 );
		$thisarg = wp_parse_args( $options['args'], $defaults );
		if(array_diff_assoc($thisarg, $args) && !$args['format'] && !$args['unit']){
			bb_tag_heat_map($options['args']);
			//print_r($thisarg);
			//print_r($args);
			return;
		}
	}
	*/
	$soname = "so";
	$divname = "bbcumuluscontent";	
	$tagcloud = urlencode( str_replace( "&nbsp;", " ", $tagcloud ) );
	// get some paths
	$movie = BBCUM_PLUGPATH."tagcloud.swf";
	$path = BBCUM_PLUGPATH;
	// add random seeds to so name and movie url to avoid collisions and force reloading (needed for IE)
	$soname .= rand(0,9999999);
	$movie .= '?r=' . rand(0,9999999);
	$divname .= rand(0,9999999);
	// write flash tag
	if( $options['compmode'] != 1 ){
		$flashtag = '<!-- SWFObject embed by Geoff Stearns geoff@deconcept.com http://blog.deconcept.com/swfobject/ -->';	
		$flashtag .= '<script type="text/javascript" src="'.$path.'swfobject.js"></script>';
		$flashtag .= '<div id="'.$divname.'">';
		if( $options['showbbtags'] == 1 ){ $flashtag .= '<p>'; } else { $flashtag .= '<p style="display:none;">'; };
		// alternate content
		$flashtag .= urldecode($tagcloud);		
		$flashtag .= '</p><p>Flash tag cloud requires <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> 9 or better.</p></div>';
		$flashtag .= '<script type="text/javascript">';
		$flashtag .= 'var '.$soname.' = new SWFObject("'.$movie.'", "tagcloudflash", "'.$options['width'].'", "'.$options['height'].'", "9", "#'.$options['bgcolor'].'");';
		if( $options['trans'] == 1 ){
			$flashtag .= $soname.'.addParam("wmode", "transparent");';
		}
		$flashtag .= $soname.'.addParam("allowScriptAccess", "always");';
		$flashtag .= $soname.'.addVariable("tcolor", "0x'.$options['tcolor'].'");';
		$flashtag .= $soname.'.addVariable("tcolor2", "0x' . ($options['tcolor2'] == "" ? $options['tcolor'] : $options['tcolor2']) . '");';
		$flashtag .= $soname.'.addVariable("hicolor", "0x' . ($options['hicolor'] == "" ? $options['tcolor'] : $options['hicolor']) . '");';
		$flashtag .= $soname.'.addVariable("tspeed", "'.$options['speed'].'");';
		$distr = ($options['distr']) ? "true" : "false";
		$flashtag .= $soname.'.addVariable("distr", "'.$distr.'");';
		$flashtag .= $soname.'.addVariable("mode", "'.$options['mode'].'");';
		// put tags in flashvar
		$flashtag .= $soname.'.addVariable("tagcloud", "'.urlencode('<tags>') . $tagcloud . urlencode('</tags>').'");';
		$flashtag .= $soname.'.write("'.$divname.'");';
		$flashtag .= '</script>';
	} else {
		$flashtag = '<object type="application/x-shockwave-flash" data="'.$movie.'" width="'.$options['width'].'" height="'.$options['height'].'">';
		$flashtag .= '<param name="movie" value="'.$movie.'" />';
		$flashtag .= '<param name="bgcolor" value="#'.$options['bgcolor'].'" />';
		$flashtag .= '<param name="AllowScriptAccess" value="always" />';
		if( $options['trans'] == 1 ){
			$flashtag .= '<param name="wmode" value="transparent" />';
		}
		$flashtag .= '<param name="flashvars" value="';
		$flashtag .= 'tcolor=0x'.$options['tcolor'];
		$flashtag .= '&amp;tcolor2=0x'.$options['tcolor2'];
		$flashtag .= '&amp;hicolor=0x'.$options['hicolor'];
		$flashtag .= '&amp;tspeed='.$options['speed'];
		$distr = ($options['distr']) ? "true" : "false";
		$flashtag .= '&amp;distr='.$distr;
		$flashtag .= '&amp;mode='.$options['mode'];
		// put tags in flashvar
		$flashtag .= '&amp;tagcloud='.urlencode('<tags>') . $tagcloud . urlencode('</tags>');
		$flashtag .= '" />';
		// alternate content
		$flashtag .= '<p>'.urldecode($tagcloud).'</p>';
		$flashtag .= '<p>Flash Tag Cloud requires <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> 9 or better.</p>';
		$flashtag .= '</object>';
	}
	return $flashtag;
}

// options page
function bb_cumulus_options() {
	global $bbcum_plugopts;
	$settings = $newoptions = $bbcum_plugopts;
	// if submitted, process results
	if ( $_POST['bb_cumulus_submit'] == 1 ) {
		bb_check_admin_referer( 'bbcum-save-chk' );
		$newoptions['width'] = strip_tags(stripslashes($_POST['width']));
		$newoptions['height'] = strip_tags(stripslashes($_POST['height']));
		$newoptions['tcolor'] = strip_tags(stripslashes($_POST['tcolor']));
		$newoptions['tcolor2'] = strip_tags(stripslashes($_POST['tcolor2']));
		$newoptions['hicolor'] = strip_tags(stripslashes($_POST['hicolor']));
		$newoptions['bgcolor'] = strip_tags(stripslashes($_POST['bgcolor']));
		$newoptions['trans'] = $_POST['trans'];
		$newoptions['speed'] = strip_tags(stripslashes($_POST['speed']));
		//$newoptions['distr'] = ($_POST['distr'] == 1) ? "true" : "false";
		$newoptions['distr'] = $_POST['distr'] == 1;
		//$newoptions['args'] = strip_tags(stripslashes($_POST['args']));
		$newoptions['compmode'] = $_POST['compmode'];
		$newoptions['showbbtags'] = $_POST['showbbtags'];
	}
	// any changes? save!
	if ( $settings != $newoptions ) {
		$settings = $newoptions;
		bb_update_option(BBCUM_OPTIONS, $settings);
		bb_admin_notice( __( 'The options were successfully saved!', 'bb-cumulus') );
	}
	
	$options = array(
		'width' => array(
			'title' => __( 'Width of the Flash Tag Cloud', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'note' => __( 'Width in Pixels (250 or more is recommended)', 'bb-cumulus' ),
			'value' => $settings['width'] ? $settings['width'] : ''
		),
		'height' => array(
			'title' => __( 'Height of the Flash Tag Cloud', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'note' => __( 'Height in Pixels (Ideally around 3/4 of the Width)', 'bb-cumulus' ),
			'value' => $settings['height'] ? $settings['height'] : ''
		),
		'speed' => array(
			'title' => __( 'Rotation Speed', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'note' => __( 'Speed (Percentage, Default is 100)', 'bb-cumulus' ),
			'value' => $settings['speed'] ? $settings['speed'] : ''
		),
		'trans' => array(
			'title' => __( 'Use Transparent Mode?', 'bb-cumulus' ),
			'note' => __( 'Switches on Flash\'s \'wmode-transparent\' Setting', 'bb-cumulus' ),
			'type' => 'checkbox',
			'value' => $settings['trans'] ? '1' : '0',
			'options' => array(
				1 => __( 'Yes', 'bb-cumulus' )
			)
		),
		'distr' => array(
			'title' => __( 'Places Tags at Equal Intervals instead of Random?', 'bb-cumulus' ),
			'type' => 'checkbox',
			'value' => $settings['distr'] ? '1' : '0',
			'options' => array(
				1 => __( 'Yes', 'bb-cumulus' )
			),
			'after' => '<div style="clear:both;"</div><strong>'.__( 'The below fields should be 6 Character Hex Colour Values without the # Prefix (Eg. - 000000 for Black, ffffff for White, etc.)', 'bb-cumulus' ).'</strong>'
		),
		'tcolor' => array(
			'title' => __( 'Colour of the Tags', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'value' => $settings['tcolor'] ? $settings['tcolor'] : ''
		),
		'tcolor2' => array(
			'title' => __( 'Optional 2nd Colour for Gradient', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'value' => $settings['tcolor2'] ? $settings['tcolor2'] : ''
		),
		'hicolor' => array(
			'title' => __( 'Optional Highlight Colour', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'value' => $settings['hicolor'] ? $settings['hicolor'] : ''
		),
		'bgcolor' => array(
			'title' => __( 'Background Colour', 'bb-cumulus' ),
			'class' => array( 'short' ),
			'value' => $settings['bgcolor'] ? $settings['bgcolor'] : ''//,
			//'after' => '<div style=\"clear:both;\"></div><strong>'.__( 'Below are Advanced Options. Please leave this setting empty unless you know what you\'re doing.', 'bb-cumulus' ).'</strong>'
		),
		/*'args' => array(
			'title' => __( 'BB Tag Cloud Parameters', 'bb-cumulus' ),
			'class' => array( 'long' ),
			'note' => __( 'Parameter String for bb_tag_heat_map() Function', 'bb-cumulus' ),
			'after' => "<div style=\"clear:both;\"></div><strong>".__('Example Uses', 'bb-cumulus')."</strong><br />number=20 - ".__('Limit the Number of Tags to 20', 'bb-cumulus')."<br />smallest=5&largest=50 - ".__('Specify Custom Font Sizes', 'bb-cumulus')."<br /><br /><strong>".__('Known issues', 'bb-cumulus')."</strong><ul><li>".__('Currently, the \'unit\', parameter is not supported.', 'bb-cumulus')."</li><li>".__('Setting \'format\' to anything but \'flat\' will cause the plugin to fail.', 'bb-cumulus')."</li></ul>",
			'value' => $settings['args'] ? $settings['args'] : ''
		),*/
		'compmode' => array(
			'title' => __( 'Use compatibility mode?', 'bb-cumulus' ),
			'note' => __( 'Enabling this option switches the plugin to a different way of embedding Flash into the page. Use this if your page has markup errors or if you\'re having trouble getting bb-Cumulus to display correctly.', 'bb-cumulus' ),
			'value' => $settings['compmode'] ? '1' : '0',
			'type' => 'checkbox',
			'options' => array(
				1 => __( 'Yes', 'bb-cumulus' )
			)
		),
		'showbbtags' => array(
			'title' => __( 'Show the Regular HTML Tag Cloud?', 'bb-cumulus' ),
			'note' => __( 'Un-hides the regular HTML Tag Cloud that may appear for a Second or so before it is replaced by the Flash one. Turn this on if SEO and/or non-flash users are a major concern for you.', 'bb-cumulus' ),
			'type' => 'checkbox',
			'value' => $settings['showbbtags'] ? '1' : '0',
			'options' => array(
				1 => __( 'Yes', 'bb-cumulus' )
			)
		)
	);
	?>
	<h2><?php _e( 'bb-Cumulus Options', 'bb-cumulus' ); ?></h2>
	<?php do_action( 'bb_admin_notices' ); ?>
	<form method="post" class="settings">
		<fieldset>
			<?php
			foreach ( $options as $option => $args ) {
				bb_option_form_element( $option, $args );
			}
			?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'bbcum-save-chk' ); ?>
			<input type="hidden" name="bb_cumulus_submit" value="1"></input>
			<input class="submit" type="submit" name="submit" value="<?php _e( 'Save Changes', 'bb-cumulus' ); ?>" />
		</fieldset>
		<p><?php _e('Happy with the plugin? Why not', 'bb-cumulus'); ?> <a href="http://gaut.am/donate/bbC/"><?php _e('buy the author a cup of coffee or two', 'bb-cumulus'); ?></a> <?php _e('or get him something from his', 'bb-cumulus'); ?> <a href="http://gaut.am/wishlist/"><?php _e('wishlist', 'bb-cumulus'); ?></a>?</p>
	</form>
<?php
}
?>