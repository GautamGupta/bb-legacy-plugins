<?php

/**
 * @package After the Deadline
 * @subpackage Public Section
 * @category Profile Options
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/**
 * A convienence function to display the HTML for an AtD option
 *
 * @param string $name
 * @param string $value
 * @param array $options
 *
 * @return string The HTML output for the option
 */
function atd_print_option( $name, $value, $options ) {
	// Attribute-safe version of $name
	$attr_name = sanitize_title( $name ); // Using sanitize_title since there's no comparable function for attributes
	?>
	<input type="checkbox" id="atd_<?php echo $attr_name; ?>" name="<?php echo $options['name']; ?>[<?php echo $name; ?>]" value="1" <?php if ( $options[$name] == '1' ) { ?>checked="checked"<?php } ?>> <label for="atd_<?php echo $attr_name; ?>"><?php echo $value; ?></label><br />
	<?php
}

/**
 * Save AtD user options
 *
 * @return void
 */
function atd_profile_update() {
	global $atd_plugopts;
	
	$user = bb_get_current_user();
	if ( !$user || $user->ID == 0 )
		return;
	
	if ( in_array( 'ignoretypes', (array) $atd_plugopts['enableuser'] ) ) {
		if ( is_array( $_POST['atd_ignoretypes'] ) )
			$options['ignoretypes'] = esc_attr( implode( ',', array_keys( $_POST['atd_ignoretypes'] ) ) );
		else
			unset( $options['ignoretypes'] );
	}
	
	if ( in_array( 'ignorealways', (array) $atd_plugopts['enableuser'] ) )
		$options['ignorealways'] = esc_attr( $_POST['atd_ignored_phrases'] );
	
	if ( in_array( 'autoproofread', (array) $atd_plugopts['enableuser'] ) )
		$options['autoproofread'] = ( intval( $_POST['atd_autoproofread'] ) == 1 ) ? 1 : 0;
	
	bb_update_usermeta( $user->ID, ATD_USER_OPTIONS, $options );
}

/**
 * Display the various AtD options
 */
function atd_profile_form() {
	global $atd_plugopts;
	
	$user = bb_get_current_user();
	if ( !$user || $user->ID == 0 )
		return;
	
	$user_options = bb_get_usermeta( $user->ID, ATD_USER_OPTIONS );
	$options_show_types = array( 'name' => 'atd_ignoretypes' );
	if ( $options_raw = $user_options['ignoretypes'] ) { foreach( explode( ',', $options_raw ) as $option ) $options_show_types[$option] = 1; }
	$ignores	= $user_options['ignorealways'];
	$autoproofread	= $user_options['autoproofread'];
	
	?>
</fieldset>
<fieldset>
	<legend><?php _e( 'Proofreading Options', 'after-the-deadline' ); ?></legend>
	<table class="form-table">
	<?php if ( in_array( 'autoproofread', (array) $atd_plugopts['enableuser'] ) ) { ?>
		<tr>
			<th scope="row"><label for="atd_autoproofread"><?php _e( 'Auto Proofread', 'after-the-deadline' ); ?></label></th>
			<td>
				<p><input type="checkbox" id="atd_autoproofread" name="atd_autoproofread" value="1" <?php if ( $autoproofread == 1 ) { ?>checked="checked"<?php } ?>> <label for="atd_autoproofread"><?php _e( 'Automatically proofread content when I forget to do a spellcheck', 'after-the-deadline' ); ?></label></p>
			</td>
		</tr>
	<?php
	}
	
	if ( in_array( 'ignoretypes', (array) $atd_plugopts['enableuser'] ) ) { ?>
		<tr>
			<th scope="row"><label><?php _e( 'Grammar and Styles', 'after-the-deadline' ); ?></label></th>
			<td colspan="2">
				<p><label><?php _e( 'Enable proofreading for the following grammar and style rules when writing posts and pages:', 'after-the-deadline' ); ?></label></p>
			</td>
		</tr>
		<tr>
			<th scope="row"></th>
			<td width="50%">
				<p><?php
					atd_print_option( 'Bias Language',		__( 'Bias Language',		'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Cliches',			__( 'Clich&eacute;s',		'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Complex Expression',		__( 'Complex Phrases',		'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Diacritical Marks',		__( 'Diacritical Marks',	'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Double Negative',		__( 'Double Negatives',		'after-the-deadline' ), $options_show_types );
				?></p>
			</td>
			<td width="50%">
				<p><?php
					atd_print_option( 'Hidden Verbs',		__( 'Hidden Verbs',		'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Jargon Language',		__( 'Jargon',			'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Passive voice',		__( 'Passive Voice',		'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Phrases to Avoid',		__( 'Phrases to Avoid',		'after-the-deadline' ), $options_show_types );
					atd_print_option( 'Redundant Expression',	__( 'Redundant Phrases',	'after-the-deadline' ), $options_show_types );
				?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"></th>
			<td colspan="2">
				<p><?php printf( bb_rel_nofollow( __( '<a href="%s">Learn more</a> about these options.', 'after-the-deadline' ) ), 'http://support.wordpress.com/proofreading/#proofreading-options' ); ?></p>
			</td>
		</tr>
	<?php
	}
	
	if ( in_array( 'ignorealways', (array) $atd_plugopts['enableuser'] ) ) { ?>
		<script type="text/javascript">
		function atd_show_phrases(a){var b=jQuery("#atd_ignores").get(0),c=[];a.sort();for(var d=0;d<a.length;d++)a[d].length>0&&c.push('<span id="atd_'+d+'"><a class="ntdelbutton" href="javascript:atd_unignore(\''+a[d].replace("'","\\'")+"')\">X</a>&nbsp;"+a[d]+"</span>");b.innerHTML=c.length>=1?c.join("<br>"):""} function atd_unignore(a,b){b=jQuery("#atd_ignored_phrases").val().split(/,/g);b=jQuery.map(b,function(c,d){return c==a?null:c});jQuery("#atd_ignored_phrases").val(b.join(","));atd_show_phrases(b);jQuery("#atd_message").show()} function atd_ignore(){var a=jQuery("#atd_ignored_phrases").val().split(/,/g);jQuery.map(jQuery("#atd_add_ignore").val().split(/,\s*/g),function(b,c){a.push(b)});jQuery("#atd_ignored_phrases").val(a.join(","));atd_show_phrases(a);jQuery("#atd_add_ignore").val("");jQuery("#atd_message").show()}function atd_ignore_init(){jQuery("#atd_message").hide();atd_show_phrases(jQuery("#atd_ignored_phrases").val().split(/,/g))}navigator.appName=="Microsoft Internet Explorer"?setTimeout(atd_ignore_init,2500):jQuery(document).ready(atd_ignore_init);
		</script>
		<tr>
			<th scope="row"><label for="atd_ignored_phrases"><?php _e( 'Ignored Phrases', 'after-the-deadline' ); ?></label></th>
			<td colspan="2">
				<input type="hidden" name="atd_ignored_phrases" id="atd_ignored_phrases" value="<?php echo $ignores; ?>">
				<p><?php _e( 'Identify words and phrases to ignore while proofreading your posts and pages:', 'after-the-deadline' ); ?></p>
				<p><input type="text" id="atd_add_ignore" name="atd_add_ignore"> <input type="button" value="<?php _e( 'Add', 'after-the-deadline' ); ?>" onclick="atd_ignore();"></p>
				<div class="tagchecklist" id="atd_ignores"></div>
				<div class="plugin-update-tr" id="atd_message" style="display: none">
					<div class="update-message"><strong><?php _e( 'Be sure to click "Update Profile" at the bottom of the screen to save your changes.', 'after-the-deadline' ); ?></strong></div>
				</div>
			</td>
		</tr>
	<?php }	?>
	</table>
	<?php
}

add_action( 'profile_edited', 'atd_profile_update' );
add_action( 'extra_profile_info', 'atd_profile_form', 999 );
