<?php
/*
Plugin Name: MySEO
Plugin URI: http://forum.aperto-nota.fr/topic/integration-seo-to-bbpress
Description: Add keywords to be identified by all SEO browsers. It is based on <a href="http://www.woorank.com">Woorank website</a> analysis tools and solve only "In-Site" SEO.<br/> I need more feedback. Vote and let me a message if you are using my plugin.<br/><br/> Thanks.
Changelog: 1.2 --> Add Bing declaration
Author: Thierry HUET
Version: 1.2
Author URI: http://www.aperto-nota.fr
Tags : SEO
*/

	$general_seo_options = array(
		'name' => array(
		'title' => __( 'Site title' ),
		'class' => 'long',
		'note' => __( 'Your title must contains between 10 and 70 characters. Make sure your title is explicit and contains your most important keywords. 
		Be sure that each page has a unique title. 
		Resource: Use this snippet-optimizer to see how your titles and descriptions will look in Google™ search results.' ),
	),
		'seo_lang' => array(
			'title' => __( 'Language' ),
			'type' => 'select',
			'options' => array(
				'en'   => 'English',
				'fr'   => 'French',
				),
	),
	'seo_publisher' => array(
		'title' => __( 'Publisher' ),
		'class' => array('long'),
		'note' => __( 'An entity primarily responsible for making the website.Examples of a publisher include a person, an organization, or a service. Typically, the name of a Creator should be used to indicate the entity' ),
	),	
	'seo_date' => array(
		'title' => __( 'Date' ),
		'class' => array('long'),
		'note' => __( 'Date when the website was on line for the first time.' ),
	),	
	'seo_description' => array(
		'title' => __( 'Description' ),
		'class' => array('long'),
		'note' => __( 'Meta descriptions allow you to influence how your web pages are described and displayed in search results. 
		Your meta description must contains between 70 and 160 characters. Ensure that your meta description is explicit and contains 
		your most important keywords. Also be sure that each page has a unique meta description.' ),
	),
	'seo_keywords' => array(
		'title' => __( 'Keywords' ),
		'note' => __( 'Meta keywords is used to indicate keywords that are supposedly relevant to your website\'s content. 
		However, because search engine spammers have abused this tag, it provides little to no benefit to your search rankings.' ),
	),
		'seo_location' => array(
		'title' => __( 'Site Location' ),
		'class' => 'long',
		'note' => __('Your website is not geotagged. Although Google™ ignores Geo-Meta Tags, the search engine Bing takes them into account.'),
	),
	'seo_analytics' => array(
		'title' => __( 'Google Analytics' ),
		'class' => 'long',
		'note' => __( 'If your website is not monitored by Google™ Analytics. You\'re really missing a great opportunity if you don\'t have this or 
		another analytics tool installed. Google™ Analytics is a free solution that gives you valuable insights into your website traffic. 
		With Google™ Analytics, you can analyze your traffic data in an entirely new way and create higher conversion rates.'),
	),
	'seo_wot' => array(
		'title' => __( 'myWOT Key'),
		'class' => 'long',
		'note' => __( 'If you don\'t have a meta tag, please go to <a href="http://www.mywot.com">myWot.com</a>'),
	),	
	'seo_woorank' => array(
		'title' => __( 'Add Woorank widget on footer' ),
		'type' => 'radio',
		'options' => array(
			'1' => 'Yes',
			'0' => 'No'
		)
	),
	'seo_bing' => array(
		'title' => __( 'Bing Key'),
		'class' => 'long',
		'note' => __( 'If you don\'t have a meta tag, please go to <a href="http://www.bing.com/webmasters">bing.com</a>'),
	)
);

add_action('bb_head', 'ap_add_seo');
add_action('bb_foot', 'ap_add_foot_seo');
add_action( 'bb_admin_menu_generator', 'ap_seo_configuration_page_add' );
add_action( 'ap_seo_configuration_page_pre_head', 'ap_seo_configuration_page_process' );

// Bail here if no keys is set --------------------------------


function ap_seo_configuration_page_add()
{
	// In order to add a menu to the Admin Setting -----------------------------
	bb_admin_add_submenu( __( 'My SEO' ), 'moderate', 'ap_seo_configuration_page', 'options-general.php' );
}

function ap_seo_configuration_page()
{
	global $general_seo_options ;
	?>
	<h2><?php _e( 'My SEO Settings' ); ?></h2>
	<?php
	do_action( 'bb_admin_notices' ); ?>
	You have to choose what you want to put on blank fields. These values will be analyzed by Google, Alexia, Yahoo and so on.<br/> 
	Before saving, go to <a href="http://www.woorank.com">Woorank</a> or another website analysis tool like that to estimate your progress.
	<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'ap_seo_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
		<fieldset>
<?php

foreach ( $general_seo_options as $option => $args ) {
	bb_option_form_element( $option, $args );
} ?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'options-seo-update' ); ?>
			<input type="hidden" name="action" value="update-seo-settings" />
			<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
	</fieldset>
	</form> 
	<?php
}


function ap_seo_configuration_page_process($opti)
{
	global $general_seo_options ;
		
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-seo-settings') {
		bb_check_admin_referer( 'options-seo-update' );

		$goback = remove_query_arg( array( 'invalid-seo', 'updated-seo' ), wp_get_referer() );

		foreach ( $general_seo_options as $option => $args ) 
		{
			if ( $_POST[$option] ) 
			{
				$value = stripslashes_deep( trim( $_POST[$option] ) );
				if ( $value ) 
				{
					if ( ap_seo_verify_key( $value ) ) 
					{
						bb_update_option( $option, $value );
					} else 
					{
						$goback = add_query_arg( 'invalid-seo', 'true', $goback );
						bb_safe_redirect( $goback );
						exit;
					}
				} else 
				{
					bb_delete_option( $option );
				}
			} else 
			{
				bb_delete_option( $option );
			}
		}

		$goback = add_query_arg( 'updated-seo', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['updated-seo'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	if ( !empty( $_GET['invalid-seo'] ) ) {
		bb_admin_notice( __( '<strong>The data you attempted to enter is invalid. Reverting to previous setting.</strong>' ), 'error' );
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';	
}	

function ap_seo_verify_key($key)
{
	// Not yet defined ---------------------------------
	return 1 ;
}

function ap_add_foot_seo()
{
	if (bb_get_option('seo_woorank'))
	{
		$addweb = substr(bb_get_option('uri'), 7,99);
		$addweb = substr($addweb, 0,-1);
		$mycode = "<div id='footer' role='contentinfo'>\n" ;
		$mycode .="<a href='http://www.woorank.com/en/www/forum.aperto-nota.fr' target='blank' title='WooRank of ".$addweb."'>\n" ;
		$mycode .="<img src='http://www.woorank.com/en/widget/".$addweb."/m' alt='WooRank of ".$addweb."' width='120' height='60' style='border:0;' />\n";
		$mycode .="</a>\n" ;
		$mycode .="</div>\n" ;
	$mycode .= "<script type='text/javascript'>\n";
	$mycode .= "\tvar _gaq = _gaq || [];\n" ;
	$mycode .= "\t_gaq.push(['_setAccount', '".bb_get_option('seo_analytics')."']);\n" ;
	$mycode .= "\t_gaq.push(['_trackPageview']);\n" ;
	$mycode .= "\t(function() {\n" ;
	$mycode .= "\t\tvar ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n";
   $mycode .= "\t\tga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n";
   $mycode .= "\t\tvar s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n";
	$mycode .= "\t})();\n";
	$mycode .= "</script>\n" ;		
		echo $mycode ;
	}
}
	
function ap_add_seo()
{
	
	global $general_seo_options ;
	
	// -- Code SEO --------------------
	$mycode  = "<!-- Implantation du code SEO/Woorank (c) - v1.1 - http://www.aperto-nota.fr --->\n" ;
	$mycode .= "\t<meta name='language' content='".bb_get_option('seo_lang')."'>\n" ;
	$mycode .= "\t<meta name='wot-verification' content='".bb_get_option('seo_wot')."'>\n" ;
	$mycode .= "\t<meta name='msvalidate.01' content='".bb_get_option('seo_bing')."'>\n" ;	
	$mycode .= "\t<meta name='dc.language' content='".bb_get_option('seo_lang')."'>\n" ;
	$mycode .= "\t<meta name='dc.publisher' content='".bb_get_option('seo_publisher')."'>\n" ;
	$mycode .= "\t<meta name='dc.date' content='".bb_get_option('seo_date')."'>\n" ;
	$mycode .= "\t<meta name='description' content='".bb_get_option('seo_description')."'>\n" ;
	$mycode .= "\t<meta name='keywords' content='".bb_get_option('seo_keywords')."'>\n" ;	
	$mycode .= "\t<meta name='geo.placename' content='".bb_get_option('seo_location')."'>\n" ;	
	$mycode .= "<!-- Fin de l'implantation -------------------------->\n";
	 
	// -- Fin code --------------------------
		echo $mycode ;
}

?>