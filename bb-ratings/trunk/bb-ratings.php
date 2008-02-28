<?php
/*
Plugin Name: bbRatings
Plugin URI: http://bbpress.org/#
Description: Allows users to rate topics on a 1-5 star scale.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.8.4
*/

/* Template Functions */

function bb_get_rating_plugin_version() { return '0.8.3'; }

function bb_rating( $topic_id = 0 ) {
	global $topic;
	if ( $topic_id )
		$topic = get_topic( $topic_id );
	bb_display_rating( $topic->avg_rating );
}

function bb_rating_count( $topic_id = 0, $show_zero = false ) {
	global $topic;
	if ( $topic_id )
		$_topic = get_topic( $topic_id );
	else
		$_topic =& $topic;
	echo is_array($_topic->rating) ? count($_topic->rating) : ( $show_zero ? 0 : '' );
}

function bb_rating_dingus() {
	global $topic;
	if ( !bb_current_user_can( 'write_posts' ) )
		return;
	$rating = bb_get_current_user_rating();
	$title_array = array(
		1 => '* Poor',
		2 => '** Works',
		3 => '*** Good',
		4 => '**** Great',
		5 => '***** Fantastic!'
	); ?>
	<div id="rate-response"></div>
	<div class="star-holder select">
		<div class="star star-rating select" style="width: <?php echo ( 100 * $rating / 5 ); ?>px"></div>
<?php for ( $r = 5; $r > 0; $r-- ) : ?>
		<div class="star star<?php echo $r; ?> select"><a href="<?php echo bb_nonce_url( add_query_arg( array('rate' => $r, 'topic_id' => $topic->topic_id) ), 'rate-topic_' . $topic->topic_id ); ?>" title="<?php echo $title_array[$r]; ?>"><img src="<?php echo bb_path_to_url( dirname(__FILE__) . '/star.gif'); ?>" /></a></div>
<?php endfor; ?>
	</div>
<?php
}

function bb_current_user_rating() {
	if ( $r = bb_get_current_user_rating() )
		bb_display_rating( $r, true );
}

function bb_user_rating( $user_id = 0 ) {
	global $bb_post, $bb_current_user;
	if ( $r = bb_get_user_rating( $user_id ) )
		bb_display_rating( $r, ( $user_id ? $user_id : $bb_post->poster_id ) == $bb_current_user->ID );
}

/* Query Functions */

function bb_top_topics( $number = 0 ) {
	$top_topics_query = bb_view_query( 'top-rated', array( 'per_page' => $number ) );
	return $top_topics_query->results;
}

function bb_get_current_user_rating() {
	global $bb_current_user, $topic;
	if ( isset($bb_current_user->data->rating[$topic_id]) )
		return $bb_current_user->data->rating[$topic_id];
	elseif ( isset($topic->rating[$bb_current_user->ID]) )
		return $topic->rating[$bb_current_user->ID];
	return false;
}

function bb_get_user_rating( $user_id = 0 ) {
	global $bb_post, $topic;

	$user = bb_get_user( $user_id ? $user_id : $bb_post->poster_id );
	if ( isset($user->rating[$topic->topic_id]) )
		return $user->rating[$topic->topic_id];
	elseif ( isset($topic->rating[$user->ID]) )
		return $topic->rating[$user->ID];
	return false;
}

/* Backend Functions */

function bb_rating_init() {
	$query_args = array( 'meta_key' => 'avg_rating', 'order_by' => '0 + tm.meta_value' );
	if ( version_compare( bb_get_option( 'version' ), '0.8.4-z', '<' ) )
		$query_args['meta_value'] = '>0';
	bb_register_view( 'top-rated', __('Highest Rated', 'bb-rating'), $query_args );
}

function bb_do_rating() {
	global $topic, $bb_current_user;

	bb_enqueue_script( 'bb_rating', bb_path_to_url( dirname(__FILE__) . '/bb-ratings.js' ), array('wp-ajax'), bb_get_rating_plugin_version() );

	if ( !isset($_GET['rate']) )
		return;
	if ( !bb_current_user_can( 'write_posts' ) )
		return;
	bb_check_admin_referer( 'rate-topic_' . $topic->topic_id );
	bb_rate_topic( $topic->topic_id, $bb_current_user->ID, $_GET['rate'] );
	wp_redirect( remove_query_arg( array('rate', '_wpnonce') ) );
}

function bb_do_ajax_rating() {
	global $bb_current_user;
	$id = (int) $_POST['id'];
	$rate = (int) $_POST['rate'];

	$rate = bb_rate_topic( $id, $bb_current_user->ID, $rate );

	$x = new WP_Ajax_Response( array(
		'what' => 'rating',
		'id' => $id,
		'data' => $rate
	) );
	$x->send();
	exit;
}

function bb_rate_topic( $topic_id, $user_id, $rating ) {
	global $bb_table_prefix;

	$topic_id = (int) $topic_id;
	$user_id = (int) $user_id;
	$rating = (int) $rating;
	if ( !$topic = get_topic( $topic_id ) )
		return new WP_Error( 'rating', __('Topic not found.', 'bb-rating') );
	if ( !$user = bb_get_user( $user_id ) )
		return new WP_Error( 'rating', __('User not found.', 'bb-rating') );
	if ( 1 > $rating || 5 < $rating )
		return new WP_Error( 'rating', __('Invalid rating.', 'bb-rating') );

	if ( !is_array($topic->rating) || !isset($topic->rating[$user_id]) ) {
		$total_votes = bb_get_option( 'bb_ratings_total_votes' ) + 1;
		bb_update_option( 'bb_ratings_total_votes', $total_votes );
	}

	if ( is_array($topic->rating) )
		$topic->rating[$user_id] = $rating;
	else
		$topic->rating = array($user_id => $rating);

	$avg = number_format(array_sum($topic->rating) / count($topic->rating), 2);
	bb_update_topicmeta( $topic_id, 'rating', $topic->rating );
	bb_update_topicmeta( $topic_id, 'avg_rating', $avg );
	bb_update_topicmeta( $topic_id, 'rating_score', ( $avg - 3 ) * count($topic->rating) );

	if ( is_array($user->rating) )
		$user->rating[$topic_id] = $rating;
	else
		$user->rating = array($topic_id => $rating);

	bb_update_usermeta( $user_id, $bb_table_prefix . 'rating', $user->rating );
	return $rating;
}

function bb_rating_bb_delete_topic( $topic_id, $new_status, $old_status ) {
	global $bbdb;
	if ( !$topic = get_topic( $topic_id ) )
		return false;

	if ( 0 == $new_status && is_array($topic->rating) ) {
		$avg = (int) round(array_sum($topic->rating) / count($topic->rating));
		bb_update_topicmeta( $topic_id, 'avg_rating', $avg );
	} elseif ( 0 != $new_status ) {
		bb_delete_topicmeta( $topic_id, 'avg_rating' );
	}
}

function bb_display_rating( $rating, $current_user = false ) { ?>
	<div class="star-holder">
		<div class="star star-rating<?php if ( $current_user ) echo ' select'; ?>" style="width: <?php echo ( 100 * $rating / 5 ); ?>px"></div>
<?php for ( $r = 5; $r > 0; $r-- ) : ?>
		<div class="star star<?php echo $r; ?>"><img src="<?php echo bb_path_to_url( dirname(__FILE__) . '/star.gif' ); ?>" /></div>
<?php endfor; ?>
	</div>
<?php
}

function bb_rating_stylesheet() {
	echo "<link rel='stylesheet' href='" . bb_path_to_url( dirname(__FILE__) . '/bb-ratings.css' ) . "' type='text/css' />\n";
}

function bb_rating_add_recount_list() {
	global $recount_list;
	$recount_list[] = array('bb-ratings', __('Recount Ratings', 'bb-rating'), 'bb_rating_recount');
	return;
}

function bb_rating_recount() {
	global $bbdb;
	if ( isset($_POST['bb-ratings']) && 1 == $_POST['bb-ratings'] ):
		$total = 0;
		echo "\t<li>\n";
		if ( $topics = (array) $bbdb->get_col("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key = 'rating'") ) :
			echo "\t\t" . __('Recounting ratings...', 'bb-rating') . "<br />\n";
			foreach ( $topics as $topic_id ) :
				$topic = get_topic( $topic_id );
				$total += $count = count($topic->rating);
				bb_update_topicmeta( $topic_id, 'rating_score', ( $topic->avg_rating - 3 ) * $count );
			endforeach;
			bb_update_option( 'bb_ratings_total_votes', $total );
		endif;
		echo "\t\t" . __('Done recounting ratings.', 'bb-rating');
		echo "\n\t</li>";
	endif;
}

add_action( 'bb_init', 'bb_rating_init' );

add_action( 'bb_topic.php_pre_db', 'bb_do_rating' );
add_action( 'bb_head', 'bb_rating_stylesheet' );

add_action( 'bb_ajax_rate-topic', 'bb_do_ajax_rating' );

add_action( 'bb_delete_topic', 'bb_rating_bb_delete_topic', 10, 3 );

add_action( 'bb_recount_list', 'bb_rating_add_recount_list' );
?>
