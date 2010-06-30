<?php
/*
Plugin Name: Profanity Filter
Plugin URI: http://nightgunner5.wordpress.com/tag/profanity-filter/
Version: 0.2
Description: Why the $&$% did I make this @&#$ing profanity filter?
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
*/

global $profanity_filter_cache_args;
$profanity_filter_cache_args = array();

function profanity_filter_parse_args( $args = '' ) {
	global $profanity_filter_cache_args;
	$_args = serialize( $args );
	if ( isset( $profanity_filter_cache_args[$_args] ) )
		return $profanity_filter_cache_args[$_args];

	$args = wp_parse_args( $args, wp_parse_args( bb_get_option( 'profanity_filter' ), array(
		'words' => array( __( 'cabbage', 'profanity-filter' ) ),
		'secondary_words' => array( __( 'soup', 'profanity-filter' ) ),
		'whitelist' => array( __( 'soap', 'profanity-filter' ) ),
		'type' => 'cartoon', // replace, char, cartoon
		'replacement' => __( '!@#$%^&*', 'profanity-filter' ),
		'last_update' => 0
	) ) );

	$args['last_update'] = max( bb_offset_time( filemtime( __FILE__ ) ), $args['last_update'] );

	$profanity_filter_cache_args[$_args] = $args;

	return $args;
}

function profanity_filter_prepare( $string, $wordOnly = false ) {
	$ret = '/';
	if ( $wordOnly )
		$ret .= '(?<!\p{L})';
	$ret .= '(<[^>]+>)?';
	foreach ( str_split( $string ) as $notfirst => $char ) {
		if ( $notfirst )
			$ret .= '(<[^>]+>)?';
		$ret .= preg_quote( $char, '/' );
	}
	$ret .= '(<[^>]+>)?';
	if ( $wordOnly )
		$ret .= '(?!\p{L})';
	return $ret . '/i';
}

function profanity_filter_prepare_secondary( $string ) {
	return profanity_filter_prepare( $string, true );
}

function profanity_filter_replace( $found ) {
	global $profanity_filter_settings;

	$len = strlen( strip_tags( array_shift( $found ) ) );

	switch ( $profanity_filter_settings['type'] ) {
		case 'cartoon':
			$string = substr( str_shuffle( str_repeat( $profanity_filter_settings['replacement'], $len ) ), 0, $len );
			break;
		case 'char':
			$string = substr( str_repeat( $profanity_filter_settings['replacement'], ceil( $len / strlen( $profanity_filter_settings['replacement'] ) + 1 ) ), mt_rand( 0, strlen( $profanity_filter_settings['replacement'] ) - 1 ), $len );
			break;
		case 'replace':
		default:
			$string = $profanity_filter_settings['replacement'];
	}

	$ret = array_shift( $found );

	$ret .= esc_html( $string );

	$ret .= implode( '', $found );

	return $ret;
}

function profanity_filter_on_whitelist( $whitelist, $word ) {
	$word = preg_replace( '/[^\p{L}]+/S', '', strtolower( $word ) );

	foreach ( $whitelist as $entry ) {
		$entry = preg_replace( '/[^\p{L}]+/S', '', strtolower( $entry ) );

		if ( !$entry )
			continue;

		if ( strpos( $entry, $word ) !== false )
			return true;
		if ( strpos( $word, $entry ) !== false )
			return true;
	}

	return false;
}

function profanity_filter_censor( $text, $args = '' ) {
	if ( !function_exists( 'double_metaphone_2' ) )
		require_once dirname( __FILE__ ) . '/doublemetaphone.php';

	$options = profanity_filter_parse_args( $args );

	$_sentence = strip_tags( $text );
	$sentence = double_metaphone_2( $_sentence );

	$sentence['positions'][] = strlen( $_sentence );

	$cabbageFound = array();
	foreach ( array_map( 'double_metaphone_2', $options['words'] ) as $word ) {
		while ( true ) {
			$_cabbageStart = -1;
			//do {
				$_cabbageStart = strpos( $sentence['primary'], $word['primary'], $_cabbageStart + 1 );
				if ( $_cabbageStart === false )
					break;
			//} while ( substr( $sentence['secondary'], $_cabbageStart, strlen( $word['primary'] ) ) != $word['secondary'] );

			//if ( $_cabbageStart === false )
			//	break;

			$sentence['primary'] = substr( $sentence['primary'], 0, $_cabbageStart ) . str_repeat( ' ', strlen( $word['primary'] ) ) . substr( $sentence['primary'], $_cabbageStart + strlen( $word['primary'] ) );

			$cabbageStart = $sentence['positions'][$_cabbageStart];
			$cabbageLen = $sentence['positions'][$_cabbageStart + strlen( $word['primary'] )] - $cabbageStart;

			while ( $cabbageLen > $sentence['positions'][$_cabbageStart + strlen( $word['primary'] ) - 1] - $cabbageStart && ( substr( $_sentence, $cabbageStart + $cabbageLen - 2, 1 ) == ' ' || !preg_match( '/^\p{L}$/S', substr( $_sentence, $cabbageStart + $cabbageLen - 1, 1 ) ) ) ) {
				$cabbageLen--;
			}

			$cabbage = substr( $_sentence, $cabbageStart, $cabbageLen );
			if ( !profanity_filter_on_whitelist( $options['whitelist'], $cabbage ) )
				$cabbageFound[] = $cabbage;
		}
	}
	$cabbageFound = array_unique( $cabbageFound );

	$soupFound = array();
	foreach ( array_map( 'double_metaphone_2', $options['secondary_words'] ) as $word ) {
		while ( true ) {
			$_soupStart = -1;
			//do {
				$_soupStart = strpos( $sentence['primary'], $word['primary'], $_soupStart + 1 );
				if ( $_soupStart === false )
					break;
			//} while ( substr( $sentence['secondary'], $_soupStart, strlen( $word['primary'] ) ) != $word['secondary'] );

			//if ( $_soupStart === false )
			//	break;

			$sentence['primary'] = substr( $sentence['primary'], 0, $_soupStart ) . str_repeat( ' ', strlen( $word['primary'] ) ) . substr( $sentence['primary'], $_soupStart + strlen( $word['primary'] ) );

			$soupStart = $sentence['positions'][$_soupStart];
			$soupLen = $sentence['positions'][$_soupStart + strlen( $word['primary'] )] - $soupStart;

			while ( $soupLen && !preg_match( '/^\p{L}$/S', substr( $_sentence, $soupStart + $soupLen - 1, 1 ) ) ) {
				$soupLen--;
			}

			$soup = substr( $_sentence, $soupStart, $soupLen );
			if ( !profanity_filter_on_whitelist( $options['whitelist'], $soup ) )
				$soupFound[] = $soup;
		}
	}
	$soupFound = array_unique( $soupFound );

	global $profanity_filter_settings;
	$profanity_filter_settings = $options;

	$censored = preg_replace_callback( array_map( 'profanity_filter_prepare', $cabbageFound ), 'profanity_filter_replace', $text );
	$censored = preg_replace_callback( array_map( 'profanity_filter_prepare_secondary', $soupFound ), 'profanity_filter_replace', $censored );

	return $censored;
}

function profanity_filter_filter( $text, $id = 0, $args = '' ) {
	$profanity_filter_options = profanity_filter_parse_args( $args );

	switch ( current_filter() ) {
		case 'get_topic_title':
			if ( $text == bb_get_topicmeta( get_topic_id( $id ), 'profanity_filter_unfiltered' ) )
				if ( bb_get_topicmeta( get_topic_id( $id ), 'profanity_filter_date' ) > $profanity_filter_options['last_update'] )
					if ( $clean_text = bb_get_topicmeta( get_topic_id( $id ), 'profanity_filter_filtered' ) )
						return $clean_text;
			break;
		case 'get_post_text':
			if ( $text == bb_get_postmeta( get_post_id( $id ), 'profanity_filter_unfiltered' ) )
				if ( bb_get_postmeta( get_post_id( $id ), 'profanity_filter_date' ) > $profanity_filter_options['last_update'] )
					if ( $clean_text = bb_get_postmeta( get_post_id( $id ), 'profanity_filter_filtered' ) )
						return $clean_text;
			break;
	}

	$clean_text = profanity_filter_censor( $text, $args );

	switch ( current_filter() ) {
		case 'get_topic_title':
			bb_update_topicmeta( get_topic_id( $id ), 'profanity_filter_date', current_time( 'timestamp' ) );
			bb_update_topicmeta( get_topic_id( $id ), 'profanity_filter_unfiltered', $text );
			bb_update_topicmeta( get_topic_id( $id ), 'profanity_filter_filtered', $clean_text );
			break;
		case 'get_post_text':
			bb_update_postmeta( get_post_id( $id ), 'profanity_filter_date', current_time( 'timestamp' ) );
			bb_update_postmeta( get_post_id( $id ), 'profanity_filter_unfiltered', $text );
			bb_update_postmeta( get_post_id( $id ), 'profanity_filter_filtered', $clean_text );
			break;
	}

	return $clean_text;
}

function profanity_filter_user( $name, $id, $args = '' ) {
	$profanity_filter_options = profanity_filter_parse_args( $args );

	$keyprefix = 'profanity_filter_' . current_filter() . '_';

	if ( bb_get_usermeta( $id, $keyprefix . 'unfiltered' ) == $name &&
		bb_get_usermeta( $id, $keyprefix . 'date' ) > $profanity_filter_options['last_update'] &&
		( $clean_text = bb_get_usermeta( $id, $keyprefix . 'filtered' ) ) )
		return $clean_text;

	$clean_text = profanity_filter_censor( $name, $args );

	bb_update_usermeta( $id, $keyprefix . 'unfiltered', $name );
	bb_update_usermeta( $id, $keyprefix . 'date', current_time( 'timestamp' ) );
	bb_update_usermeta( $id, $keyprefix . 'filtered', $clean_text );

	return $clean_text;
}

function profanity_filter_activate() {
	add_filter( 'get_topic_title', 'profanity_filter_filter', 9, 2 );
	add_filter( 'get_post_text', 'profanity_filter_filter', 9, 2 );
	add_filter( 'get_user_name', 'profanity_filter_user', 9, 2 );
	add_filter( 'get_user_display_name', 'profanity_filter_user', 9, 2 );
}

function profanity_filter_deactivate() {
	remove_filter( 'get_topic_title', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'get_post_text', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'get_user_name', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'get_user_display_name', 'profanity_filter_filter', 9, 2 );
}

if ( !defined( 'DOING_CRON' ) )
	profanity_filter_activate();
add_action( 'pre_edit_form', 'profanity_filter_deactivate' );
add_action( 'post_edit_form', 'profanity_filter_activate' );

function profanity_filter_filter_slug( $slug ) {
	if ( false !== $filtered = wp_cache_get( $slug, 'profanity_filter_slug' ) )
		return $filtered;

	$filtered = profanity_filter_censor( $slug, array(
		'type' => 'replace',
		'replacement' => ''
	) );
	$filtered = preg_replace( '/--+/', '-', trim( $filtered, '-' ) );

	wp_cache_add( $slug, $filtered, 'profanity_filter_slug' );

	return $filtered;
}

function profanity_filter_slug_activate() {
	add_filter( 'bb_slug_sanitize', 'profanity_filter_filter_slug', 9 );
	add_filter( 'bb_user_nicename_sanitize', 'profanity_filter_filter_slug', 9 );
}

function profanity_filter_slug_deactivate() {
	remove_filter( 'bb_slug_sanitize', 'profanity_filter_filter_slug', 9 );
	remove_filter( 'bb_user_nicename_sanitize', 'profanity_filter_filter_slug', 9 );
}

profanity_filter_slug_activate();
add_action( 'pre_permalink', 'profanity_filter_slug_deactivate' );
add_action( 'post_permalink', 'profanity_filter_slug_activate' );

function profanity_filter_admin() {
	if ( !empty( $_GET['updated'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	$options = profanity_filter_parse_args(); ?>
<h2><?php _e( 'Profanity Filter', 'profanity-filter' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'profanity_filter_admin' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<div id="option-words">
			<label for="words"><?php _e( 'Primary words', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<textarea id="words" name="words"><?php echo esc_html( implode( "\n", $options['words'] ) ); ?></textarea>
				<p><?php _e( 'Put primary profane words here, each on their own line. These will be replaced even if they are part of another word.', 'profanity-filter' ); ?></p>
			</div>
		</div>

		<div id="option-secondary-words">
			<label for="secondary-words"><?php _e( 'Secondary words', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<textarea id="secondary-words" name="secondary_words"><?php echo esc_html( implode( "\n", $options['secondary_words'] ) ); ?></textarea>
				<p><?php _e( 'Put secondary profane words here, each on their own line, to avoid a <a href="http://en.wikipedia.org/wiki/Scunthorpe_problem">clbuttic mistake</a>.', 'profanity-filter' ); ?></p>
			</div>
		</div>

		<div id="option-whitelist">
			<label for="whitelist"><?php _e( 'Whitelist', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<textarea id="whitelist" name="whitelist"><?php echo esc_html( implode( "\n", $options['whitelist'] ) ); ?></textarea>
				<p><?php _e( 'If innocent words are being censored unfairly, put them here, each on their own line, and the filter will skip over them.', 'profanity-filter' ); ?></p>
			</div>
		</div>

		<div id="option-replacement">
			<label for="replacement"><?php _e( 'Replacement', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<input type="text" class="text long" id="replacement" name="replacement" value="<?php echo esc_attr( $options['replacement'] ); ?>" />
				<p><?php _e( 'Profanity will be replaced with this using one of several methods, depending on your choice for the replacement type.', 'profanity-filter' ); ?></p>
			</div>
		</div>

		<div id="option-type">
			<label for="type"><?php _e( 'Replacement type', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<select id="type" name="type">
<?php foreach ( array( 'replace' => __( 'Replace', 'profanity-filter' ), 'char' => __( 'Character', 'profanity-filter' ), 'cartoon' => __( 'Cartoon', 'profanity-filter' ) ) as $value => $title ) { ?>
					<option value="<?php echo $value; ?>"<?php if ( $options['type'] == $value ) echo ' selected="selected"'; ?>><?php echo $title; ?></option>
<?php } ?>
				</select>
				<p><strong><?php _e( 'Replace', 'profanity-filter' ); ?></strong> &ndash; <?php _e( 'Replace profane words with a constant value.', 'profanity-filter' ); ?><br/>
					<strong><?php _e( 'Character', 'profanity-filter' ); ?></strong> &ndash; <?php _e( 'Replace profane words with a string of characters in the order they appear in the replacement field. For multi-character sequences, this may start at any point in the sequence.', 'profanity-filter' ); ?><br/>
					<strong><?php _e( 'Cartoon', 'profanity-filter' ); ?></strong> &ndash; <?php _e( 'Replace profane words with characters randomly selected from the replacement value.', 'profanity-filter' ); ?></p>
			</div>
		</div>
	</fieldset>

	<fieldset class="submit">
		<?php bb_nonce_field( 'profanity-filter' ); ?>
		<input type="hidden" name="action" value="update" />
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Save Changes', 'profanity-filter' ); ?>" />
	</fieldset>
</form>
<?php }

function profanity_filter_admin_add() {
	bb_admin_add_submenu( __( 'Profanity Filter', 'profanity-filter' ), 'manage_options', 'profanity_filter_admin', 'options-general.php' );
}
add_action( 'bb_admin_menu_generator', 'profanity_filter_admin_add' );

function profanity_filter_admin_parse() {
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
		if ( $_POST['action'] == 'update' ) {
			bb_check_admin_referer( 'profanity-filter' );

			$options = profanity_filter_parse_args();

			$options['words'] = array_map( 'trim', explode( "\n", stripslashes( $_POST['words'] ) ) );
			$options['secondary_words'] = array_map( 'trim', explode( "\n", stripslashes( $_POST['secondary_words'] ) ) );
			$options['whitelist'] = array_map( 'trim', explode( "\n", stripslashes( $_POST['whitelist'] ) ) );

			if ( in_array( $_POST['type'], array( 'replace', 'char', 'cartoon' ) ) )
				$options['type'] = $_POST['type'];
			$options['replacement'] = stripslashes( $_POST['replacement'] );

			$options['last_update'] = current_time( 'timestamp' );

			bb_update_option( 'profanity_filter', $options );

			$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
			bb_safe_redirect( $goback );
			exit;
		}
	}
}
add_action( 'profanity_filter_admin_pre_head', 'profanity_filter_admin_parse' );