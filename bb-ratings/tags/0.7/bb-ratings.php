<?php
/*
Plugin Name: bbRatings
Plugin URI: http://bbpress.org/#
Description: Allows users to rate topics on a 1-5 star scale.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.7
*/

/* Template Functions */

function bb_rating( $topic_id = 0 ) {
	global $topic;
	if ( $topic_id )
		$topic = get_topic( $topic_id );
	bb_display_rating( $topic->avg_rating );
}

function bb_rating_count( $topic_id = 0 ) {
	global $topic;
	if ( $topic_id )
		$topic = get_topic( $topic_id );
	echo is_array($topic->rating) ? count($topic->rating) : 0;
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
		<div class="star star-rating select" style="width: <?php echo ( 85 * $rating / 5 ); ?>px"></div>
<?php for ( $r = 5; $r > 0; $r-- ) : ?>
		<div class="star star<?php echo $r; ?> select"><a href="<?php echo bb_nonce_url( add_query_arg( 'rate', $r ), 'rate-topic_' . $topic->topic_id ); ?>" title="<?php echo $title_array[$r]; ?>"><img src="<?php bb_option( 'uri' ); echo BBPLUGINDIR; ?>/star.gif" /></a></div>
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

function bb_top_topics() {
	global $bbdb, $page, $bb_last_countable_query;

	$limit = bb_get_option('page_topics');
        if ( 1 < $page )
                $limit = ($limit * ($page - 1)) . ", $limit";

	$bb_last_countable_query = "SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key = 'avg_rating' ORDER BY meta_value DESC LIMIT $limit";

	if ( !$top = (array) $bbdb->get_col( $bb_last_countable_query) )
		return get_latest_topics();

	$top = join(',', $top);
	$topics = $bbdb->get_results("SELECT * FROM $bbdb->topics WHERE topic_id IN ($top) AND topic_status = 0");
	return bb_append_meta( $topics, 'topic' );
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

function bb_do_rating() {
	global $topic, $bb_current_user;

	bb_enqueue_script( 'bb_rating', bb_get_option( 'uri' ) . BBPLUGINDIR . '/bb-ratings.js', array('wp-ajax') );

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

	if ( is_array($topic->rating) )
		$topic->rating[$user_id] = $rating;
	else
		$topic->rating = array($user_id => $rating);

	$avg = (int) round(array_sum($topic->rating) / count($topic->rating));
	bb_update_topicmeta( $topic_id, 'rating', $topic->rating );
	bb_update_topicmeta( $topic_id, 'avg_rating', $avg );

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
		$bbdb->query("DELETE FROM $bbdb->topic_meta WHERE topic_id = '$topic_id' AND meta_key = 'avg_rating'");
	}
}

function bb_display_rating( $rating, $current_user = false ) { ?>
	<div class="star-holder">
		<div class="star star-rating<?php if ( $current_user ) echo ' select'; ?>" style="width: <?php echo ( 85 * $rating / 5 ); ?>px"></div>
<?php for ( $r = 5; $r > 0; $r-- ) : ?>
		<div class="star star<?php echo $r; ?>"><img src="<?php bb_option( 'uri' ); echo BBPLUGINDIR; ?>/star.gif" /></div>
<?php endfor; ?>
	</div>
<?php
}

function bb_rating_stylesheet() {
	if ( !is_topic() )
		return;
	echo "<link rel='stylesheet' href='" . bb_get_option( 'uri' ) . BBPLUGINDIR . "/bb-ratings.css' type='text/css' />\n";
}

add_action( 'bb_topic.php_pre_db', 'bb_do_rating' );
add_action( 'bb_head', 'bb_rating_stylesheet' );

add_action( 'bb_ajax_rate-topic', 'bb_do_ajax_rating' );

add_action( 'bb_delete_topic', 'bb_rating_bb_delete_topic', 10, 3 );

?>
