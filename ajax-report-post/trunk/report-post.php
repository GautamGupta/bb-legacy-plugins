<?php
/*
Plugin Name: Ajax Report Post
Description:  Allows members to report a post to admin/moderators 
Plugin URI:  
Author: jomontvm
Version: 0.1.0

license: GPLv3
*/

add_action('bb_init', 'report_post_initialize');

function report_post_initialize() {
	global $report_options;
	load_plugin_textdomain( 'report-post', BB_PLUGIN_DIR . 'ajax-report-post' );
	
	$defaultOptions = array(
		'bbrp_link_text' => 'Report',
		'bbrp_option_label' => 'Report as:',
		'bbrp_textarea_msg' => 'Reason for reporting (optional):',
		'bbrp_options' => '
Invalid content
Abusive words
Irrelevent to topic',
		'bbrp_success_msg' => 'The post has been reported. Thank you.',
		'bbrp_error_msg' => 'There was an error reporting this post. Please try again later.',
		'bbrp_already_reported_msg' => 'You have already reported this post'		
	);
	
	$report_options = array(
		'bbrp_link_text' => (trim( bb_get_option('bbrp_link_text') ) ) ? trim( bb_get_option('bbrp_link_text') ) : $defaultOptions['bbrp_link_text'],
		'bbrp_option_label' => (trim( bb_get_option('bbrp_option_label') ) ) ? trim( bb_get_option('bbrp_option_label') ) : $defaultOptions['bbrp_option_label'],
		'bbrp_textarea_msg' => (trim( bb_get_option('bbrp_textarea_msg') ) ) ? trim( bb_get_option('bbrp_textarea_msg') ) : $defaultOptions['bbrp_textarea_msg'],
		'bbrp_options' => (trim( bb_get_option('bbrp_options') ) ) ? trim( bb_get_option('bbrp_options') ) : $defaultOptions['bbrp_options'],
		'bbrp_success_msg' => (trim( bb_get_option('bbrp_success_msg') ) ) ? trim( bb_get_option('bbrp_success_msg') ) : $defaultOptions['bbrp_success_msg'],
		'bbrp_error_msg' => (trim( bb_get_option('bbrp_error_msg') ) ) ? trim( bb_get_option('bbrp_error_msg') ) : $defaultOptions['bbrp_error_msg'],
		'bbrp_already_reported_msg' => (trim( bb_get_option('bbrp_already_reported_msg') ) ) ? trim( bb_get_option('bbrp_already_reported_msg') ) : $defaultOptions['bbrp_already_reported_msg'],
	);
}

function bbrp_report_link() {
	if ( !bb_is_topic() )
		return false;
		
	global $page, $topic, $bb_post, $report_options;
	
	if ( !$topic || !topic_is_open( $bb_post->topic_id ) || !bb_is_user_logged_in() || !bb_current_user_can('participate') ) 
		return false;
	
	$post_id = get_post_id();
	
	$post_author_id=get_post_author_id($post_id);  // to do, exclude all admin/moderators
	if ($post_author_id != bb_get_current_user_info( 'id' ) && $post_author_id!="1") {
		echo '&nbsp;<a class="report_post" id="report-'.$post_id.'" title="'.$title.'" href="javascript:void(0);" onClick="showReportForm('.$post_id.');return false;">'.$report_options['bbrp_link_text'].'</a>&nbsp;';
	}
	return apply_filters( 'bbrp_report_link', $link );
}

add_filter('bb_post_admin', 'bbrp_report_post_link');
function bbrp_report_post_link($post_links) {
        if ( $link = bbrp_report_link() )
                $post_links[] = $link;
        return $post_links;
}

function bbrp_trim_sentence($str, $numChars = 0, $force = 0, $from = 0) {
	mb_internal_encoding ( "UTF-8" );
	$strLength = mb_strlen ( $str );
	if ((mb_strlen ( $str ) <= $numChars) && ! $force) {
		return $str;
	}
	if ($numChars == 0) {
		$numChars = mb_strlen ( $str );
	}
	$str = mb_substr ( $str, $from, $numChars );
	$pos = mb_strrpos ( $str, ' ' );
	if (! empty ( $pos )) {
		$str = mb_substr ( $str, 0, $pos );
	}
	if ($strLength > $numChars) {
		$str .= '...';
	}
	return $str;
}

function bbrp_addBreakStringSlashes($string){
	$string = str_replace(array("\\r\\n", "\\r", "\\n"), '<br />', $string);
	return stripslashes($string);
}

/// Prints JS header.

add_action('bb_init', 'bbrp_report_print_js');
add_action('bb_head', 'bbrp_report_header_js', 100);

function bbrp_report_print_js() {
	bb_enqueue_script('jquery');
}

function bbrp_report_header_js() {
	global $report_options;
	if ( bb_is_topic() && bb_current_user_can('participate')  && !bb_is_topic_edit() ) {
		$action_url = bb_nonce_url( BB_PLUGIN_URL . 'ajax-report-post/report.ajax.php');
		$options = split("\r\n", addslashes($report_options['bbrp_options']));
		$optionsHtml = '';
		if (!empty($options)) {
			$optionsHtml .= '<li><label>'.addslashes($report_options['bbrp_option_label']).'</label>';
			$optionsHtml .= '<select name="report_option">';
			foreach ($options as $opt) {
				if (!empty($opt)) {
					$optionsHtml .= '<option value="'.$opt.'">'.$opt.'</option>';
				}
			}
			$optionsHtml .= '</select></li>';
		}
		?>
		<style type="text/css">
			.response_text { color: #777; text-transform: none; margin-top:5px; }
			.report-post { text-transform: none; margin-top:5px; }
			#thread ul.report-form{margin:0; padding:10px 0 0 0;}
			#thread ul.report-form li{margin:0 0 5px 0; padding:0 0 5px 0; border-top:none; list-style:none;}
			ul.report-form li label{margin-right:15px;}
			ul.report-form li select{width:200px;}
		</style>		
		<script type="text/javascript" language="javascript">
		function report_post(postId, eId) {
			var actionUrl = '<?php echo $action_url; ?>';
			var option = jQuery('#'+eId+' select[name=report_option]').val();
			var comment = jQuery('#'+eId+' textarea[name=report_post_reason]').val();
			jQuery.post(
				actionUrl,
				{postId:postId, option:option, comment:comment},
				function (msg) {
					jQuery('#'+eId).val('');
					jQuery('#report_post_'+postId).remove();
					jQuery('#response_text_'+postId).html(msg);					
				}
			);
		}
		
		function showReportForm(post_id) {
			if (jQuery('div#report_post_'+post_id).length <= 0) {				
				if (readCookie('bbrp-report-post-'+post_id) == 'true') {
					jQuery('p#response_text_'+post_id).remove();
					reportHtml = '<div id="report_post_'+post_id+'" class="report-post" style="display:none;"><p class="response_text" id="response_text_'+post_id+'"><?php echo addslashes($report_options['bbrp_already_reported_msg']); ?></p></div>';
				} else {
					var reportHtml = '<div id="report_post_'+post_id+'" class="report-post" style="display:none"><ul class="report-form">';
					reportHtml += '<?php echo $optionsHtml ?>';
					reportHtml += '<li><label><?php echo addslashes($report_options['bbrp_textarea_msg']); ?></label>';
					reportHtml += '<textarea rows="4" cols="60" name="report_post_reason"></textarea></li><li><input type="button" onclick="report_post('+post_id+', \'report_post_'+post_id+'\');return false;" value="Submit report" /></li></ul></div>';
					reportHtml += '<p class="response_text" id="response_text_'+post_id+'"></p>';
				}
				
				jQuery('a#report-'+post_id).parent('div').append(reportHtml);
				jQuery('div#report_post_'+post_id).slideToggle();
			} else {
				jQuery('div#report_post_'+post_id).slideToggle();
			}
		}
		
		function readCookie(name) {
			var nameEQ = name + "=";
			var ca = document.cookie.split(';');
			for(var i=0;i < ca.length;i++) {
				var c = ca[i];
				while (c.charAt(0)==' ') c = c.substring(1,c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
			}
			return null;
		}

		</script>
		<?php 
		
	}
}

// Add filters for the admin area
add_action( 'bb_admin_menu_generator', 'bbrp_report_admin_page_add' );

function bbrp_report_admin_page_add () {
	bb_admin_add_menu( __( 'Reports', 'report-post' ), 'manage_options', 'report_manager', '', '', 'bb-menu-report');
	bb_admin_add_submenu( __( 'Reports', 'report-post' ), 'manage_options', 'report_manager&action=list', 'report_manager' );
	bb_admin_add_submenu( __( 'Settings', 'report-post' ), 'manage_options', 'report_manager&action=settings', 'report_manager' );
}

function report_manager() {
	
	?>
	<script type="text/javascript" language="javascript">
		jQuery('li.bb-menu').removeClass('bb-menu-current');
		jQuery('li#bb-menu-report').addClass('bb-menu-current').addClass('bb-menu-open');
		<?php 
		if ($_GET['action'] == 'settings') { ?>
			jQuery('li#bb-menu-report div.bb-menu-sub-wrap ul li.bb-menu-sub:last-child').addClass('bb-menu-sub-current');
		<?php
		} else { ?>
			jQuery('li#bb-menu-report div.bb-menu-sub-wrap ul li.bb-menu-sub:first-child').addClass('bb-menu-sub-current');
		<?php
		} ?>
	</script>
	<?php
	
	require_once dirname( __FILE__ ) . '/report-admin.php';
	switch ( $_GET['action'] ) {
		case 'del':
			delete_report( $_GET['id'], $_GET['post_id'] );
			break;
		case 'view':
			view_reports( $_GET['post_id'] );
			break;
		case 'settings':
			report_settings();
			break;
		case 'list':
			report_list();
			break;
		default:
			report_list();
	}
}


# Install

function report_post_upgrade_schema( $schema )
{
	global $bbdb;
	$table_name = $bbdb->prefix.'custom_reports';
	$schema['customreports'] = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
			`postID` INT( 11 ) NOT NULL ,
			`report_option` VARCHAR( 50 ) NOT NULL ,
			`reporter_comment` TEXT NOT NULL ,
			`reporter_ip` VARCHAR( 20 ) NOT NULL ,
			`reported_date` DATETIME NOT NULL ,
			`status` TINYINT( 1 ) NOT NULL DEFAULT '1',
			PRIMARY KEY ( `id` )
			);";

	return $schema;
}

add_filter( 'bb_schema_pre_charset', 'report_post_upgrade_schema' );

function report_post_install() {
	global $bbdb;

	require_once( BB_PATH . 'bb-admin/includes/functions.bb-upgrade.php' );
	require_once( BB_PATH . 'bb-admin/includes/defaults.bb-schema.php' );
	require_once( BACKPRESS_PATH . 'class.bp-sql-schema-parser.php' );
	$delta = BP_SQL_Schema_Parser::delta( $bbdb, $bb_queries );
	
	if ( is_array( $delta ) ) {
		$log = $delta;
	} else {
		$log = array( 'messages' => array(), 'errors' => array() );
	}

	$log['messages'] = array_filter( $log['messages'] );
	$log['errors'] = array_filter( $log['errors'] );

	return $log;
}
bb_register_plugin_activation_hook(__FILE__,'report_post_install');



?>