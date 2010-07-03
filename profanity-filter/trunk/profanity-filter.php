<?php
/*
Plugin Name: Profanity Filter
Plugin URI: http://nightgunner5.wordpress.com/tag/profanity-filter/
Version: 0.2.1-dev-2
Description: Changes profanity to stars, cartoon swears, or an administrator-specified word using the Double Metaphone algorithm.
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
*/

// The last version of Profanity filter in which the API changed
define( 'PROFANITY_FILTER_API_VERSION', '0.2.1-dev-2' );

function profanity_filter_parse_args( $args = '' ) {
	$args = wp_parse_args( $args, wp_parse_args( bb_get_option( 'profanity_filter' ), array(
		'words'               => array( __( 'cabbage', 'profanity-filter' ) ),
		'secondary_words'     => array( __( 'soup', 'profanity-filter' ) ),
		'whitelist'           => array( __( 'soap', 'profanity-filter' ) ),
		'type'                => 'cartoon', // replace, char, cartoon
		'replacement'         => __( '!@#$%^&*', 'profanity-filter' ),
		'last_update'         => 0,
		'allow_subscriptions' => false,
		'send_data_words'     => false,
		'send_data_secondary' => false,
		'send_data_whitelist' => false,
		'subscriptions'       => array() // url => array( subscribed, secret, last_seen, data => array( words, secondary_words, whitelist ) )
	) ) );

	static $profanity_filter_lastmod;

	if ( !isset( $profanity_filter_lastmod ) )
		$profanity_filter_lastmod = filemtime( __FILE__ );

	$args['last_update'] = max( $profanity_filter_lastmod, $args['last_update'] );

	$profanity_filter_cache_args[$_args] = $args;

	return $args;
}

function profanity_filter_prepare( $string, $wordOnly = false ) {
	$ret = '/';

	if ( $wordOnly )
		$ret .= '(?<!\p{L})';
	else
		$ret .= '\p{L}*';

	$ret .= '((<[^>]+>)?';
	foreach ( str_split( $string ) as $notfirst => $char ) {
		if ( $notfirst )
			$ret .= '(<[^>]+>)?';
		$ret .= preg_quote( $char, '/' );
	}
	$ret .= '(<[^>]+>)?)';

	if ( $wordOnly )
		$ret .= '(?!\p{L})';
	else
		$ret .= '\p{L}*';

	return $ret . '/i';
}

function profanity_filter_prepare_secondary( $string ) {
	return profanity_filter_prepare( $string, true );
}

function profanity_filter_replace( $found ) {
	global $profanity_filter_settings;

	$fullword = strip_tags( $fullhtmlword = array_shift( $found ) );
	if ( profanity_filter_on_whitelist( $profanity_filter_settings['whitelist'], $fullword ) )
		return $fullhtmlword;

	$len = strlen( $word = strip_tags( array_shift( $found ) ) );

	switch ( $profanity_filter_settings['type'] ) {
		case 'cartoon':
			$string = substr( str_shuffle( str_repeat( $profanity_filter_settings['replacement'], $len ) ), 0, $len );
			break;
		case 'char':
			$string = substr( str_repeat( $profanity_filter_settings['replacement'], ceil( $len / strlen( $profanity_filter_settings['replacement'] ) + 1 ) ), mt_rand( 0, strlen( $profanity_filter_settings['replacement'] ) - 1 ), $len );
			break;
		case 'replace':
		default:
			if ( is_array( $profanity_filter_settings['replacement'] ) ) {
				global $profanity_filter_words;
				$string = $profanity_filter_settings['replacement'][$profanity_filter_words[$word]];
			} else {
				$string = $profanity_filter_settings['replacement'];
			}
	}

	$ret = array_shift( $found );
	$ret .= array_shift( $found );

	$ret .= esc_html( $string );

	$ret .= implode( '', $found );

	return $ret;
}

function profanity_filter_on_whitelist( $whitelist, $word ) {
	$word = preg_replace( '/[^\p{L}]+/S', '', strtolower( $word ) );

	if ( !$word )
		return false;

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

	global $profanity_filter_words;

	$options = profanity_filter_parse_args( $args );
	$words = array_unique( $options['words'] );
	$secondary_words = array_unique( $options['secondary_words'] );
	$whitelist = array_unique( $options['whitelist'] );

	foreach ( $options['subscriptions'] as $data ) {
		if ( $data['subscribed'] ) {
			foreach ( array( 'words', 'secondary_words', 'whitelist' ) as $key ) {
				if ( !empty( $data['data'][$key] ) && is_array( $data['data'][$key] ) )
					$$key = array_merge( $$key, $data['data'][$key] );
			}
		}
	}

	$_sentence = strip_tags( $text );
	$sentence = double_metaphone_2( $_sentence );

	$sentence['positions'][] = strlen( $_sentence );

	$cabbageFound = array();
	foreach ( array_map( 'double_metaphone_2', $words ) as $pos => $word ) {
		if ( !$word['primary'] )
			continue;

		while ( true ) {
			$_cabbageStart = strpos( $sentence['primary'], $word['primary'] );
			if ( $_cabbageStart === false )
				break;

			$sentence['primary'] = substr( $sentence['primary'], 0, $_cabbageStart ) . str_repeat( ' ', strlen( $word['primary'] ) ) . substr( $sentence['primary'], $_cabbageStart + strlen( $word['primary'] ) );

			$cabbageStart = $sentence['positions'][$_cabbageStart];
			$cabbageLen = $sentence['positions'][$_cabbageStart + strlen( $word['primary'] )] - $cabbageStart;

			while ( $cabbageLen > $sentence['positions'][$_cabbageStart + strlen( $word['primary'] ) - 1] - $cabbageStart && ( !trim( substr( $_sentence, $cabbageStart + $cabbageLen - 2, 1 ) ) || !preg_match( '/^\p{L}$/S', substr( $_sentence, $cabbageStart + $cabbageLen - 1, 1 ) ) || strpos( substr( $_sentence, $cabbageStart, $cabbageLen ), "\n" ) !== false ) ) {
				$cabbageLen--;
			}

			while ( $cabbageStart > $sentence['positions'][$_cabbageStart - 1] + 1 && ( !trim( substr( $_sentence, $cabbageStart - 2, 1 ) ) || doublemetaphone_is_vowel( strtoupper( $_sentence ), $cabbageStart - 1 ) ) ) {
				$cabbageStart--;
				$cabbageLen++;
			}

			$cabbage = substr( $_sentence, $cabbageStart, $cabbageLen );
			$cabbageFound[] = $cabbage;
			$profanity_filter_words[$cabbage] = $pos;
		}
	}
	$cabbageFound = array_unique( $cabbageFound );

	$soupFound = array();
	foreach ( array_map( 'double_metaphone_2', $secondary_words ) as $pos => $word ) {
		if ( !$word['primary'] )
			continue;
	
		while ( true ) {
			$_soupStart = strpos( $sentence['primary'], $word['primary'] );
			if ( $_soupStart === false )
				break;

			$sentence['primary'] = substr( $sentence['primary'], 0, $_soupStart ) . str_repeat( ' ', strlen( $word['primary'] ) ) . substr( $sentence['primary'], $_soupStart + strlen( $word['primary'] ) );

			$soupStart = $sentence['positions'][$_soupStart];
			$soupLen = $sentence['positions'][$_soupStart + strlen( $word['primary'] )] - $soupStart;

			while ( $soupLen > $sentence['positions'][$_soupStart + strlen( $word['primary'] ) - 1] - $soupStart && ( !trim( substr( $_sentence, $soupStart + $soupLen - 2, 1 ) ) || !preg_match( '/^\p{L}$/S', substr( $_sentence, $soupStart + $soupLen - 1, 1 ) ) || strpos( substr( $_sentence, $soupStart, $soupLen ), "\n" ) !== false ) ) {
				$soupLen--;
			}

			while ( $soupStart > $sentence['positions'][$_soupStart - 1] + 1 && ( !trim( substr( $_sentence, $soupStart - 2, 1 ) ) || doublemetaphone_is_vowel( strtoupper( $_sentence ), $soupStart - 1 ) ) ) {
				$soupStart--;
				$soupLen++;
			}

			$soup = substr( $_sentence, $soupStart, $soupLen );
			$soupFound[] = $soup;
			$profanity_filter_words[$soup] = $pos;
		}
	}
	$soupFound = array_unique( $soupFound );

	global $profanity_filter_settings;
	$profanity_filter_settings = $options + compact( 'words', 'secondary_words', 'whitelist' );

	$censored = preg_replace_callback( array_map( 'profanity_filter_prepare', $cabbageFound ), 'profanity_filter_replace', $text );
	$censored = preg_replace_callback( array_map( 'profanity_filter_prepare_secondary', $soupFound ), 'profanity_filter_replace', $censored );

	return $censored;
}

function profanity_filter_filter( $text, $id = 0, $args = '' ) {
	$profanity_filter_options = profanity_filter_parse_args( $args );

	switch ( current_filter() ) {
		case 'bb_xmlrpc_prepare_topic':
		case 'get_topic_title':
			if ( md5( $text ) == bb_get_topicmeta( get_topic_id( $id ), 'profanity_filter_unfiltered' ) )
				if ( bb_get_topicmeta( get_topic_id( $id ), 'profanity_filter_date' ) > $profanity_filter_options['last_update'] )
					if ( $clean_text = bb_get_topicmeta( get_topic_id( $id ), 'profanity_filter_filtered' ) )
						return $clean_text;
			break;
		case 'bb_xmlrpc_prepare_post':
		case 'get_post_text':
			if ( md5( $text ) == bb_get_postmeta( get_post_id( $id ), 'profanity_filter_unfiltered' ) )
				if ( bb_get_postmeta( get_post_id( $id ), 'profanity_filter_date' ) > $profanity_filter_options['last_update'] )
					if ( $clean_text = bb_get_postmeta( get_post_id( $id ), 'profanity_filter_filtered' ) )
						return $clean_text;
			break;
		case 'bb_xmlrpc_prepare_topic_tag':
		case 'wp_get_object_terms':
	}

	$clean_text = profanity_filter_censor( $text, $args );

	switch ( current_filter() ) {
		case 'bb_xmlrpc_prepare_topic':
		case 'get_topic_title':
			bb_update_topicmeta( get_topic_id( $id ), 'profanity_filter_date', time() );
			bb_update_topicmeta( get_topic_id( $id ), 'profanity_filter_unfiltered', md5( $text ) );
			bb_update_topicmeta( get_topic_id( $id ), 'profanity_filter_filtered', $clean_text );
			break;
		case 'bb_xmlrpc_prepare_post':
		case 'get_post_text':
			bb_update_postmeta( get_post_id( $id ), 'profanity_filter_date', time() );
			bb_update_postmeta( get_post_id( $id ), 'profanity_filter_unfiltered', md5( $text ) );
			bb_update_postmeta( get_post_id( $id ), 'profanity_filter_filtered', $clean_text );
			break;
		case 'bb_xmlrpc_prepare_topic_tag':
		case 'wp_get_object_terms':
	}

	return $clean_text;
}

function profanity_filter_user( $name, $id, $args = '' ) {
	$profanity_filter_options = profanity_filter_parse_args( $args );

	$keyprefix = 'profanity_filter_' . current_filter() . '_';

	if ( bb_get_usermeta( $id, $keyprefix . 'unfiltered' ) == md5( $name ) &&
		bb_get_usermeta( $id, $keyprefix . 'date' ) > $profanity_filter_options['last_update'] &&
		( $clean_text = bb_get_usermeta( $id, $keyprefix . 'filtered' ) ) )
		return $clean_text;

	$clean_text = profanity_filter_censor( $name, $args );

	bb_update_usermeta( $id, $keyprefix . 'unfiltered', md5( $name ) );
	bb_update_usermeta( $id, $keyprefix . 'date', time() );
	bb_update_usermeta( $id, $keyprefix . 'filtered', $clean_text );

	return $clean_text;
}

function profanity_filter_xmlrpc( $data, $_data ) {
	switch ( current_filter() ) {
		case 'bb_xmlrpc_prepare_post':
			$data['post_text'] = profanity_filter_filter( $data['post_text'], $_data['post_id'] );
			break;
		case 'bb_xmlrpc_prepare_topic':
			$data['topic_title'] = profanity_filter_filter( $data['topic_title'], $_data['topic_id'] );
			break;
		case 'bb_xmlrpc_prepare_topic_tag':
			$data['topic_tag_name'] = profanity_filter_filter( $data['topic_tag_name'], $_data['tag_id'] );
			break;
	}
	unset( $data['profanity_filter_date'], $data['profanity_filter_unfiltered'], $data['profanity_filter_filtered'] );

	return $data;
}

function profanity_filter_tags( $terms ) {
	global $bb_log;

	foreach ( $terms as &$term ) {
		if ( $term->taxonomy != 'bb_topic_tag' )
			continue;

		$term->name = profanity_filter_filter( $term->name, $term->term_id );
	}

	return $terms;
}

function profanity_filter_activate() {
	add_filter( 'get_topic_title', 'profanity_filter_filter', 9, 2 );
	add_filter( 'get_post_text', 'profanity_filter_filter', 9, 2 );
	add_filter( 'get_user_name', 'profanity_filter_user', 9, 2 );
	add_filter( 'get_user_display_name', 'profanity_filter_user', 9, 2 );
	add_filter( 'wp_get_object_terms', 'profanity_filter_tags', 9 );
	add_filter( 'bb_xmlrpc_prepare_post', 'profanity_filter_xmlrpc', 9, 2 );
	add_filter( 'bb_xmlrpc_prepare_topic', 'profanity_filter_xmlrpc', 9, 2 );
	add_filter( 'bb_xmlrpc_prepare_topic_tag', 'profanity_filter_xmlrpc', 9, 2 );
	add_filter( 'tag_heat_map', 'profanity_filter_censor', 9 );
}

function profanity_filter_deactivate() {
	remove_filter( 'get_topic_title', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'get_post_text', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'get_user_name', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'get_user_display_name', 'profanity_filter_filter', 9, 2 );
	remove_filter( 'wp_get_object_terms', 'profanity_filter_tags', 9 );
	remove_filter( 'bb_xmlrpc_prepare_post', 'profanity_filter_xmlrpc', 9, 2 );
	remove_filter( 'bb_xmlrpc_prepare_topic', 'profanity_filter_xmlrpc', 9, 2 );
	remove_filter( 'bb_xmlrpc_prepare_topic_tag', 'profanity_filter_xmlrpc', 9, 2 );
	remove_filter( 'tag_heat_map', 'profanity_filter_censor', 9 );
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
	add_filter( 'pre_bb_topic_tag_slug', 'profanity_filter_filter_slug', 9 );
}

function profanity_filter_slug_deactivate() {
	remove_filter( 'bb_slug_sanitize', 'profanity_filter_filter_slug', 9 );
	remove_filter( 'bb_user_nicename_sanitize', 'profanity_filter_filter_slug', 9 );
	remove_filter( 'pre_bb_topic_tag_slug', 'profanity_filter_filter_slug', 9 );
}

profanity_filter_slug_activate();
add_action( 'pre_permalink', 'profanity_filter_slug_deactivate' );
add_action( 'post_permalink', 'profanity_filter_slug_activate' );

function profanity_filter_filter_tags() {
	if ( bb_is_tag() ) {
		global $tag, $profanity_filter_tag_name;
		$profanity_filter_tag_name = $tag->name;
		$tag->name = profanity_filter_filter( $tag->name, $tag->term_id );

		add_action( 'pre_post_form', 'profanity_filter_uncensor_tags' );
		add_action( 'post_post_form', 'profanity_filter_recensor_tags' );
	}
}

function profanity_filter_uncensor_tags() {
	global $tag, $profanity_filter_tag_name;
	$tag->name = $profanity_filter_tag_name;
}

function profanity_filter_recensor_tags() {
	global $tag;
	$tag->name = profanity_filter_filter( $tag->name, $tag->term_id );
}

add_action( 'post_permalink', 'profanity_filter_filter_tags' );

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

		<div id="option-allow-subscriptions">
			<label for="type"><?php _e( 'Allow subscribers', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<select id="allow-subscriptions" name="allow_subscriptions">
					<option value="1"<?php if ( $options['allow_subscriptions'] ) echo ' selected="selected"'; ?>><?php _e( 'Yes', 'profanity-filter' ); ?></option>
					<option value="0"<?php if ( !$options['allow_subscriptions'] ) echo ' selected="selected"'; ?>><?php _e( 'No', 'profanity-filter' ); ?></option>
				</select>
				<br/><input type="checkbox" class="checkbox" id="send_data_words" name="send_data_words"<?php if ( $options['send_data_words'] ) echo ' checked="checked"'; ?> /> <?php _e( 'Share words', 'profanity-filter' ); ?>
				<br/><input type="checkbox" class="checkbox" id="send_data_secondary" name="send_data_secondary"<?php if ( $options['send_data_secondary'] ) echo ' checked="checked"'; ?> /> <?php _e( 'Share secondary words', 'profanity-filter' ); ?>
				<br/><input type="checkbox" class="checkbox" id="send_data_whitelist" name="send_data_whitelist"<?php if ( $options['send_data_whitelist'] ) echo ' checked="checked"'; ?> /> <?php _e( 'Share whitelist', 'profanity-filter' ); ?>
				<p><?php _e( 'Starting with Profanity Filter 0.2.1, you can share your words, secondary words, and whitelist with others.', 'profanity-filter' ); ?></p>
			</div>
		</div>
	</fieldset>

	<fieldset class="submit">
		<?php bb_nonce_field( 'profanity-filter' ); ?>
		<input type="hidden" name="action" value="update" />
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Save Changes', 'profanity-filter' ); ?>" />
	</fieldset>
</form>

<div class="settings">
	<h3><?php _e( 'Subscriptions', 'profanity-filter' ); ?></h3>
	<p><?php _e( 'Highlighted rows are subscribed to you. You are not subscribed to rows highlighted in red.', 'profanity-filter' ); ?></p>

	<?php profanity_filter_subscription_info(); ?>
</div>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'profanity_filter_admin' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<legend><?php _e( 'Add a subscription', 'profanity-filter' ); ?></legend>

		<div id="option-url">
			<label for="url"><?php _e( 'URL', 'profanity-filter' ); ?></label>
			<div class="inputs">
				<input type="text" class="text long" id="url" name="url" />
				<p><?php _e( 'Enter the URL of the Profanity Filter endpoint you\'d like to subscribe to. Usually, this is the front page of a forum.', 'profanity-filter' ); ?></p>
			</div>
		</div>
	</fieldset>

	<fieldset class="submit">
		<?php bb_nonce_field( 'profanity-filter-subscribe' ); ?>
		<input type="hidden" name="action" value="subscribe" />
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Subscribe', 'profanity-filter' ); ?>" />
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

			foreach ( array( 'allow_subscriptions', 'send_data_words', 'send_data_secondary', 'send_data_whitelist' ) as $key )
				$options[$key] = !empty( $_POST[$key] );

			$options['last_update'] = time();

			if ( $options['allow_subscriptions'] )
				wp_schedule_single_event( time(), 'profanity_filter_update' );

			bb_update_option( 'profanity_filter', $options );

			$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
			bb_safe_redirect( $goback );
			exit;
		} else if ( $_POST['action'] == 'subscribe' ) {
			bb_check_admin_referer( 'profanity-filter-subscribe' );

			$options = profanity_filter_parse_args();

			$exists = isset( $options['subscriptions'][$_POST['url']] );
			$secret = $exists ? $options['subscriptions'][$_POST['url']]['secret'] : bb_generate_password( 32, false );

			if ( !$exists )
				$options['subscriptions'][$_POST['url']]['secret'] = $secret;
			$options['subscriptions'][$_POST['url']]['subscribed'] = true;

			bb_update_option( 'profanity_filter', $options );

			$response = profanity_filter_request( $_POST['url'], 'subscribe', array( 'secret' => $secret ) );

			if ( is_array( $response ) ) {
				$options['subscriptions'][$_POST['url']]['data']      = $response;
				$options['subscriptions'][$_POST['url']]['last_seen'] = time();
				$options['last_update'] = time();
				bb_update_option( 'profanity_filter', $options );

				$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
				bb_safe_redirect( $goback );
				exit;
			}

			if ( $exists )
				$options['subscriptions'][$_POST['url']]['subscribed'] = false;
			else
				unset( $options['subscriptions'][$_POST['url']] );
			bb_update_option( 'profanity_filter', $options );

			if ( is_wp_error( $response ) && $response->get_error_code() != 'httperr' )
				bb_die( '<strong>' . __( 'The endpoint returned failure:', 'profanity-filter' ) . '</strong> <code>' . esc_html( $response->get_error_code() ) . '</code> - ' . esc_html( $response->get_error_message( $response->get_error_code() ) ) );

			bb_die( __( 'There was an error parsing the endpoint\'s response. Make sure you entered the URL correctly.', 'profanity-filter' ) );
		}
	} else {
		if ( $_GET['action'] == 'unsubscribe' ) {
			$url = $_GET['unsubscribe'];
			bb_check_admin_referer( 'profanity-filter-unsubscribe-' . $url );

			$options = profanity_filter_parse_args();
			if ( !empty( $options['subscriptions'][$url]['subscriber'] ) )
				$options['subscriptions'][$url]['subscribed'] = false;
			else
				unset( $options['subscriptions'][$url] );
			$options['last_update'] = time();
			bb_update_option( 'profanity_filter', $options );

			profanity_filter_request( $url, 'unsubscribe' );

			$goback = bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'profanity_filter_admin', 'updated' => 'true' ), BB_URI_CONTEXT_HEADER + BB_URI_CONTEXT_BB_ADMIN );
			bb_safe_redirect( $goback );
			exit;
		}
	}
}
add_action( 'profanity_filter_admin_pre_head', 'profanity_filter_admin_parse' );

function profanity_filter_subscription_info() {
	$options = profanity_filter_parse_args(); ?>
<style type="text/css">
table.widefat tr.subscribed-both td { background-color: #ed8; }
table.widefat tr.subscribed-both.alt td { background-color: #fe9; }
</style>
<table class="widefat">
<thead>
<tr>
	<th><?php _e( 'Endpoint', 'profanity-filter' ); ?></th>
	<th><?php _e( 'Words', 'profanity-filter' ); ?></th>
	<th><?php _e( 'Secondary words', 'profanity-filter' ); ?></th>
	<th><?php _e( 'Whitelist', 'profanity-filter' ); ?></th>
</tr>
</thead>
<tfoot>
<tr>
	<th><?php _e( 'Endpoint', 'profanity-filter' ); ?></th>
	<th><?php _e( 'Words', 'profanity-filter' ); ?></th>
	<th><?php _e( 'Secondary words', 'profanity-filter' ); ?></th>
	<th><?php _e( 'Whitelist', 'profanity-filter' ); ?></th>
</tr>
</tfoot>
<tbody>
<?php
	foreach ( $options['subscriptions'] as $url => $subscription ) { ?>
<tr<?php alt_class( 'profanity-filter-subscriptions', $subscription['subscribed'] ? $subscription['subscriber'] ? 'subscribed-both' : '' : 'deleted' ); ?>>
	<td class="user">
		<span class="row-title"><a href="<?php echo esc_html( $url ); ?>"><?php echo esc_html( $url ); ?></a></span>
		<div><span class="row-actions"><?php printf( __( 'Last seen <span title="%1$s">%2$s ago</span>.', 'profanity-filter' ), str_replace( '+00:00', 'Z', gmdate( DATE_ATOM, $subscription['last_seen'] ) ), bb_since( $subscription['last_seen'] ) ); ?></span>&nbsp;</div>
		<?php if ( $subscription['subscribed'] ) { ?><div><span class="row-actions"><a href="<?php
		echo esc_attr( bb_nonce_url( bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'profanity_filter_admin', 'action' => 'unsubscribe', 'unsubscribe' => $url ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ), 'profanity-filter-unsubscribe-' . $url ) );
		?>"><?php _e( 'Unsubscribe', 'profanity-filter' ); ?></a></span>&nbsp;</div><?php } ?>
	</td>
<?php if ( $subscription['subscribed'] ) { ?>
	<td><?php
if ( isset( $subscription['data']['words'] ) && is_array( $subscription['data']['words'] ) ) {
	echo implode( '<br/>', array_map( 'esc_html', $subscription['data']['words'] ) );
} else {
	_e( '<em>Not shared.</em>', 'profanity-filter' );
}
	?></td>
	<td><?php
if ( isset( $subscription['data']['secondary_words'] ) && is_array( $subscription['data']['secondary_words'] ) ) {
	echo implode( '<br/>', array_map( 'esc_html', $subscription['data']['secondary_words'] ) );
} else {
	_e( '<em>Not shared.</em>', 'profanity-filter' );
}
	?></td>
	<td><?php
if ( isset( $subscription['data']['whitelist'] ) && is_array( $subscription['data']['whitelist'] ) ) {
	echo implode( '<br/>', array_map( 'esc_html', $subscription['data']['whitelist'] ) );
} else {
	_e( '<em>Not shared.</em>', 'profanity-filter' );
}
	?></td>
<?php } else { ?><td></td><td></td><td></td><?php } ?>
</tr>

<?php
	} ?>
</tbody>
</table>
<?php
}

function profanity_filter_get_shared_data() {
	$options = profanity_filter_parse_args();

	return array(
		'words'           => $options['send_data_words'] ? $options['words'] : false,
		'secondary_words' => $options['send_data_secondary'] ? $options['secondary_words'] : false,
		'whitelist'       => $options['send_data_whitelist'] ? $options['whitelist'] : false
	);
}

function profanity_filter_update() {
	if ( function_exists( 'ini_get' ) && function_exists( 'set_time_limit' ) && !ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	$options = profanity_filter_parse_args();

	$data = serialize( profanity_filter_get_shared_data() );

	foreach ( $options['subscriptions'] as $url => $subscription ) {
		if ( !$subscription['subscriber'] )
			continue;

		$response = profanity_filter_request( $url, 'update', array( 'profanity_filter_data' => $data ) );

		if ( $response === true )
			continue;

		if ( $response === false ) {
			if ( !$options['subscriptions'][$url]['subscribed'] )
				unset( $options['subscriptions'][$url] );
			else
				$options['subscriptions'][$url]['subscriber'] = false;
			continue;
		}

		// invalid can mean they changed the secret somehow or that they deleted us without telling.
		if ( is_wp_error( $response ) && $response->get_error_code() == 'invalid' ) { // They (sob) forgot about me! (sob)
			if ( !$options['subscriptions'][$url]['subscribed'] )
				unset( $options['subscriptions'][$url] );
			else
				$options['subscriptions'][$url]['subscriber'] = false;
			continue;
		}
	}

	bb_update_option( 'profanity_filter', $options );
}
add_action( 'profanity_filter_update', 'profanity_filter_update' );

function profanity_filter_request( $url, $mode, $params = null, $secret = null ) {
	$args = array(
		'user-agent' => apply_filters( 'http_headers_useragent', backpress_get_option( 'wp_http_version' ) ) . ' (Profanity Filter/' . PROFANITY_FILTER_API_VERSION . ')'
	);

	$options = profanity_filter_parse_args();

	if ( !$secret )
		$secret = $options['subscriptions'][$url]['secret'];

	if ( !$secret )
		return;

	$_params = array();
	if ( is_array( $params ) ) {
		foreach ( $params as $k => $v ) {
			if ( substr( $k, 0, 17 ) != 'profanity_filter_' )
				$k = 'profanity_filter_' . $k;

			$_params[$k] = $v;
		}
	}

	$time = time();

	$url = add_query_arg( array(
		'profanity_filter_key'       => sha1( $secret . ':' . $time ),
		'profanity_filter_timestamp' => $time,
		'profanity_filter_url'       => bb_get_uri(),
		'profanity_filter_subscribe' => $mode,
		'profanity_filter_version'   => PROFANITY_FILTER_API_VERSION
	) + $_params, $url );

	$request = wp_remote_get( $url );
	if ( is_wp_error( $request ) )
		return $request;

	$_response = wp_remote_retrieve_body( $request );
	if ( $_response == serialize( false ) )
		return false;
	if ( false !== $response = unserialize( $_response ) ) {
		if ( !is_wp_error( $response ) || $response->get_error_code() != 'synchronize' )
			return $response;
	} else {
		return new WP_Error( 'httperr', __( 'Could not unserialize response data.', 'profanity-filter' ), $_response );
	}

	$time = $response->get_error_data( 'synchronize' );
	$url = add_query_arg( array(
		'profanity_filter_key'       => sha1( $secret . ':' . $time ),
		'profanity_filter_timestamp' => $time
	), $url );

	$request = wp_remote_get( $url );
	if ( is_wp_error( $request ) )
		return $request;

	$_response = wp_remote_retrieve_body( $request );
	if ( $_response == serialize( false ) )
		return false;
	if ( false !== $response = unserialize( $_response ) )
		return $response;
	return new WP_Error( 'httperr', __( 'Could not unserialize response data.', 'profanity-filter' ), $_response );
}

function profanity_filter_parse_subscribe() {
	header( 'Content-Type: text/plain; charset=utf-8' );

	$_GET = stripslashes_deep( $_GET );

	foreach ( array( 'profanity_filter_version', 'profanity_filter_url', 'profanity_filter_timestamp' ) as $param )
		if ( empty( $_GET[$param] ) )
			exit( serialize( new WP_Error( 'missingparam', sprintf( __( 'Missing parameter: %s', 'profanity-filter' ), $param ), $param ) ) );

	$options = profanity_filter_parse_args();

	if ( $_GET['profanity_filter_version'] != PROFANITY_FILTER_API_VERSION )
		exit( serialize( new WP_Error( 'wrongversion', sprintf( __( 'The versions do not match. Found %1$s, but expected %2$s.', 'profanity-filter' ), $_GET['profanity_filter_version'], PROFANITY_FILTER_API_VERSION ), PROFANITY_FILTER_API_VERSION ) ) );

	if ( $_GET['profanity_filter_timestamp'] < time() - 3600 || $_GET['profanity_filter_timestamp'] > time() + 3600 )
		exit( serialize( new WP_Error( 'synchronize', __( 'Timestamp is invalid. Please synchronize.', 'profanity-filter' ), time() ) ) );

	if ( $_GET['profanity_filter_subscribe'] == 'subscribe' ) {
		if ( !$options['allow_subscriptions'] )
			exit( serialize( new WP_Error( 'nosub', __( 'Profanity Filter subscriptions are disabled on this forum.', 'profanity-filter' ) ) ) );
	} else {
		if ( empty( $_GET['profanity_filter_key'] ) )
			exit( serialize( new WP_Error( 'missingparam', sprintf( __( 'Missing parameter: %s', 'profanity-filter' ), 'profanity_filter_key' ), 'profanity_filter_key' ) ) );

		if ( !isset( $options['subscriptions'][$_GET['profanity_filter_url']] ) )
			exit( serialize( new WP_Error( 'invalid', sprintf( __( 'Unknown URL: %s', 'profanity-filter' ), $_GET['profanity_filter_url'] ), 'profanity_filter_url' ) ) );

		if ( $_GET['profanity_filter_key'] != sha1( $options['subscriptions'][$_GET['profanity_filter_url']]['secret'] . ':' . $_GET['profanity_filter_timestamp'] ) )
			exit( serialize( new WP_Error( 'invalid', __( 'Invalid key', 'profanity-filter' ), 'profanity_filter_key' ) ) );

		$options['subscriptions'][$_GET['profanity_filter_url']]['last_seen'] = time();
		bb_update_option( 'profanity_filter', $options );
	}

	switch ( $_GET['profanity_filter_subscribe'] ) {
		case 'subscribe':
			if ( empty( $_GET['profanity_filter_secret'] ) )
				exit( serialize( new WP_Error( 'missingparam', sprintf( __( 'Missing parameter: %s', 'profanity-filter' ), 'profanity_filter_secret' ), 'profanity_filter_secret' ) ) );

			if ( $_GET['profanity_filter_url'] != bb_get_uri() && profanity_filter_request( $_GET['profanity_filter_url'], 'verify', array( 'requested' => $_SERVER['REMOTE_ADDR'] ), $_GET['profanity_filter_secret'] ) === true ) {
				$options['subscriptions'][$_GET['profanity_filter_url']]['secret'] = $_GET['profanity_filter_secret'];
				$options['subscriptions'][$_GET['profanity_filter_url']]['subscriber'] = true;
				bb_update_option( 'profanity_filter', $options );
				exit( serialize( profanity_filter_get_shared_data() ) );
			}
			exit( serialize( new WP_Error( 'invalid', sprintf( __( 'Invalid URL: %s', 'profanity-filter' ), $_GET['profanity_filter_url'] ), 'profanity_filter_url' ) ) );

		case 'verify': // We already verified the request.
			exit( serialize( (boolean) $options['subscriptions'][$_GET['profanity_filter_url']]['subscribed'] ) );

		case 'update':
			if ( !$options['subscriptions'][$_GET['profanity_filter_url']]['subscribed'] )
				exit( serialize( false ) );

			if ( $_GET['profanity_filter_data'] != serialize( false ) && false === ( $data = unserialize( $_GET['profanity_filter_data'] ) ) && false === ( $data = unserialize( stripslashes( $_GET['profanity_filter_data'] ) ) ) )
				exit( serialize( new WP_Error( 'invalid', __( 'Could not unserialize data', 'profanity-filter' ), 'profanity_filter_data' ) ) );

			$options['subscriptions'][$_GET['profanity_filter_url']]['data'] = $data;
			$options['last_update'] = time();
			bb_update_option( 'profanity_filter', $options );

			exit( serialize( true ) );

		case 'unsubscribe':
			if ( !$options['subscriptions'][$_GET['profanity_filter_url']]['subscribed'] ) {
				unset( $options['subscriptions'][$_GET['profanity_filter_url']] );
			} else {
				$options['subscriptions'][$_GET['profanity_filter_url']]['subscriber'] = false;
			}
			bb_update_option( 'profanity_filter', $options );

			exit( serialize( true ) );

		default:
			exit( serialize( new WP_Error( 'invalid', sprintf( __( 'Unknown request type: %s', 'profanity-filter' ), $_GET['profanity_filter_subscribe'] ), 'profanity_filter_subscribe' ) ) );
	}
}

if ( !empty( $_GET['profanity_filter_subscribe'] ) )
	profanity_filter_parse_subscribe();
