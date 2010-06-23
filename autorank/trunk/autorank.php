<?php
/*
Plugin Name: AutoRank
Plugin URI: http://nightgunner5.wordpress.com/tag/autorank/
Description: Give users an automated score based on the posts they make.
Version: 0.1.2-dev
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
*/

global $autorank;
$autorank = array(
	'use_db'              => true,
	'show_score'          => true,
	'show_stats'          => true,
	'show_rank'           => true,
	'show_rank_page'      => false,
	'rank_replaces_title' => false,
	'post_default_score'  => 0.1,
	'post_modifier_first' => 0.1,
	'post_modifier_word'  => 0.02,
	'post_modifier_char'  => 0.0005,
	'post_modifier_forum' => array(
		/* id => multiplier, */
	),
	'text_score'          => __( 'Score:', 'autorank' ),
	'text_reqscore'       => __( 'Required score:', 'autorank' ),
	'ranks'               => array(
		/* minumum score => 'name',
		 *           OR
		 * minimum score => array( 'name', 'color' ), */
		 1 => __( 'Beginner',      'autorank' ),
		10 => __( 'Junior',        'autorank' ),
		25 => __( 'Senior',        'autorank' ),
		50 => __( 'Distinguished', 'autorank' ),
	)
);

/* Display */
function autorank_start_post() {
	global $autorank;
	$autorank['in_post'] = true;
}

function autorank_end_post() {
	global $autorank;
	$autorank['in_post'] = false;
}

function autorank_modify_title( $title, $post_id ) {
	$autorank = autorank_get_settings();

	if ( empty( $autorank['in_post'] ) )
		return $title;

	if ( !$user = bb_get_user( get_post_author_id( $post_id ) ) )
		return $title;

	$user_score = $user->autorank_score;

	$score = '';
	if ( $autorank['show_score'] )
		$score = '<br />' . esc_html( $autorank['text_score'] ) . ' <span title="' . bb_number_format_i18n( $user_score, 6 ) . '">' . bb_number_format_i18n( floor( $user_score ) ) . '</span>';

	$rank = '';
	$rank_replaces_title = $autorank['rank_replaces_title'];
	if ( $autorank['show_rank'] ) {
		list( $user_rank, $rank_score ) = autorank_get_rank( $user );

		if ( $user_rank != '' ) {
			if ( $rank_replaces_title ) {
				$_member = 'Member';
				$_member = __( $_member );

				$rank_replaces_title = get_user_title( $user ) == $_member;

				if ( $rank_replaces_title ) {
					$_link = bb_get_option( 'name_link_profile' ) ? get_user_link( get_post_author_id( $post_id ) ) : get_user_profile_link( get_post_author_id( $post_id ) );
					$_link = '<a href="' . esc_attr( $_link ) . '">' . $_member . '</a>';

					if ( strpos( $title, $_link ) === false ) {
						$rank_replaces_title = false;
					}
				}
			}

			if ( $rank_replaces_title ) {
				$rank = '<a href="' . get_user_profile_link( $user ) . '" title="' . $autorank['text_reqscore'] . ' ' . bb_number_format_i18n( $rank_score ) . '">' . $user_rank . '</a>';

				$title = str_replace( $_link, $rank, $title );
				$rank = '';
			} else {
				$rank = '<span title="' . sprintf( __( 'Required score: %s', 'autorank' ), bb_number_format_i18n( $rank_score ) ) . '">' . $user_rank . '</span><br />';
			}
		}
	}

	return $rank . $title . $score;
}

add_action( 'bb_post.php', 'autorank_start_post' );
add_action( 'bb_after_post.php', 'autorank_end_post' );

add_filter( 'post_author_title', 'autorank_modify_title', 11, 2 );
add_filter( 'post_author_title_link', 'autorank_modify_title', 11, 2 );

function autorank_stats_left() {
	$autorank = autorank_get_settings();
	if ( !$autorank['show_stats'] )
		return;

	global $bbdb;
	$total_score = $bbdb->get_var( "SELECT SUM( CAST( `meta_value` AS DECIMAL(20,10) ) ) FROM `$bbdb->usermeta` WHERE `meta_key` = 'autorank_score'" ); ?>
	<dt><?php _e( 'Total Score', 'autorank' ); ?></dt>
	<dd><strong title="<?php echo bb_number_format_i18n( $total_score, 6 ); ?>"><?php echo bb_number_format_i18n( floor( $total_score ) ); ?></strong></dd>
<?php }
add_action( 'bb_stats_left', 'autorank_stats_left' );

function autorank_stats_right() {
	$autorank = autorank_get_settings();
	if ( !$autorank['show_stats'] )
		return;

	global $bbdb;
	$highest_scoring_members = $bbdb->get_results( "SELECT `user_id`, `meta_value` FROM `$bbdb->usermeta` WHERE `meta_key` = 'autorank_score' ORDER BY CAST( `meta_value` AS DECIMAL(20,10) ) DESC LIMIT 10" ); ?>
	<h3><?php _e( 'Highest Scoring Members', 'autorank' ); ?></h3>
	<ol>
<?php foreach ( $highest_scoring_members as $member ) { ?>
		<li><a href="<?php user_profile_link( $member->user_id ); ?>"><?php echo get_user_display_name( $member->user_id ); ?></a> - <span title="<?php echo bb_number_format_i18n( $member->meta_value, 6 ); ?>"><?php echo bb_number_format_i18n( floor( $member->meta_value ) ); ?></span></li>
<?php } ?>
	</ol>
<?php }
add_action( 'bb_stats_right', 'autorank_stats_right' );

function autorank_profile_menu() {
	$autorank = autorank_get_settings();

	// This is almost a hack. not_play_nice is a capability that prevents
	// the user from logging in, so only the correct user can see their
	// rank info.
	if ( $autorank['show_rank_page'] )
		bb_add_profile_tab( __( 'Ranks', 'autorank' ), '', 'not_play_nice', 'autorank_profile_page' );
}
add_action( 'bb_profile_menu', 'autorank_profile_menu' );

function autorank_profile_page() {
	global $bb_current_user; ?>

<table id="latest" style="margin: 0;">
	<thead>
	<tr>
		<th><?php _e( 'Rank', 'autorank' ); ?></th>
		<th><?php _e( 'Required Score', 'autorank' ); ?></th>
		<th><?php _e( 'Estimated Posts Remaining', 'autorank' ); ?></th>
	</tr>
	</thead>

	<tbody>
<?php $average_post_score = autorank_get_average_post_score( $bb_current_user->ID );

$autorank = autorank_get_settings();

foreach ( $autorank['ranks'] as $score => $rank ) { ?>
	<tr<?php if ( $bb_current_user->autorank_score >= $score ) echo ' class="sticky"'; ?>>
		<td<?php if ( is_array( $rank ) ) echo ' style="color: ' . esc_attr( $rank[1] ) . ';"'; ?>><?php if ( is_array( $rank ) ) echo esc_html( $rank[0] ); else echo esc_html( $rank ); ?></td>
		<td><?php echo round( $score, 6 ); ?></td>
		<td><?php echo max( ceil( round( $score - $bb_current_user->autorank_score, 6 ) / $average_post_score ), 0 ); ?></td>
	</tr>
<?php } ?>
	</tbody>
</table>
<?php }

/* Scoring */
function autorank_recount() {
	global $bbdb;

	$user_ids = $bbdb->get_col( "SELECT `ID` FROM `$bbdb->users`" );

	foreach ( $user_ids as $id ) {
		$user_score = 0;
		$posts = bb_cache_posts( $bbdb->prepare( "SELECT `post_id` FROM `$bbdb->posts` WHERE `poster_id` = %d AND `post_status` = 0", $id ), true );

		foreach ( $posts as $post ) {
			$user_score += autorank_get_post_score( $post );
		}

		bb_update_usermeta( $id, 'autorank_score', $user_score );
	}

	return __( 'All scores recounted.', 'autoscore' );
}

function autorank_recount_add() {
	global $recount_list;

	$recount_list[] = array( 'autorank', __( 'Re-score all posts.', 'autorank' ), 'autorank_recount' );
}
add_action( 'bb_recount_list', 'autorank_recount_add' );

bb_register_plugin_activation_hook( __FILE__, 'autorank_recount' );

function autorank_get_post_score( $post_id, $forum_modify = true ) {
	$autorank = autorank_get_settings();

	if ( isset( $post_id->post_id ) )
		$post = $post_id;
	else
		$post = bb_get_post( get_post_id( $post_id ) );

	$post_score = $autorank['post_default_score'];

	if ( $post->post_position )
		$post_score += $autorank['post_modifier_first'];

	$words = count( preg_split( '/\s+/', strip_tags( $post->post_text ) ) );
	$chars = strlen( preg_replace( '/[^\p{L}\p{N}]+/', '', strip_tags( $post->post_text ) ) );

	if ( $words > 0 )
		$post_score += log( $words ) * log( $words ) * $autorank['post_modifier_word'];
	if ( $chars > 0 )
		$post_score += log( $chars ) * log( $chars ) * $autorank['post_modifier_char'];

	if ( $forum_modify && isset( $autorank['post_modifier_forum'][$post->forum_id] ) )
		$post_score *= $autorank['post_modifier_forum'][$post->forum_id];

	return max( $post_score, 0 );
}

function autorank_update_score_bbpostphp( $post_id ) {
	if ( !$post_author = bb_get_user( get_post_author_id( $post_id ) ) )
		return;

	$post_score = autorank_get_post_score( $post_id );

	bb_update_usermeta( $post_author->ID, 'autorank_score', (double) $post_author->autorank_score + $post_score );
}
add_action( 'bb-post.php', 'autorank_update_score_bbpostphp' );

function autorank_update_score_bbmovetopic( $topic_id, $new_forum, $old_forum ) {
	$autorank = autorank_get_settings();

	$old_forum_modifier = isset( $autorank['post_modifier_forum'][$old_forum] ) ? $autorank['post_modifier_forum'][$old_forum] : 1;
	$new_forum_modifier = isset( $autorank['post_modifier_forum'][$new_forum] ) ? $autorank['post_modifier_forum'][$new_forum] : 1;

	if ( $old_forum_modifier == $new_forum_modifier )
		return;

	$score_modifiers = array();
	$posts = get_thread( $topic_id, array( 'per_page' => -1 ) );
	foreach ( $posts as $post ) {
		$score = autorank_get_post_score( $post->post_id, false );

		if ( $score == 0 )
			continue;

		$score_modifiers[$post->poster_id] -= $score * $old_forum_modifier;
		$score_modifiers[$post->poster_id] += $score * $new_forum_modifier;
	}

	foreach ( $score_modifiers as $user => $score ) {
		bb_update_usermeta( $user, 'autorank_score', bb_get_usermeta( $user, 'autorank_score' ) + $score );
	}
}
add_action( 'bb_move_topic', 'autorank_update_score_bbmovetopic', 10, 3 );

function autorank_update_score_bbdeletepost( $post_id, $new_status, $old_status ) {
	if ( $new_status == $old_status )
		return;
	if ( !$author = bb_get_post( $post_id )->poster_id )
		return;

	if ( $new_status == 0 ) {
		bb_update_usermeta( $author, 'autorank_score', bb_get_usermeta( $author, 'autorank_score' ) + autorank_get_post_score( $post_id ) );
	} elseif ( $old_status == 0 ) {
		bb_update_usermeta( $author, 'autorank_score', bb_get_usermeta( $author, 'autorank_score' ) - autorank_get_post_score( $post_id ) );
	}
}
add_action( 'bb_delete_post', 'autorank_update_score_bbdeletepost', 10, 3 );

function autorank_update_score_bbupdatepost( $post_id ) {
	$autorank = autorank_get_settings();

	if ( !isset( $autorank['old_post_cache'][$post_id] ) )
		return;

	if ( !$author = $autorank['old_post_cache'][$post_id]->poster_id )
		return;

	bb_update_usermeta( $author, 'autorank_score', bb_get_usermeta( $author, 'autorank_score' ) + autorank_get_post_score( $post_id ) - autorank_get_post_score( $autorank['old_post_cache'][$post_id] ) );
}
add_action( 'bb_update_post', 'autorank_update_score_bbupdatepost' );

function autorank_update_score_bbupdatepost_prepost( $text, $id ) {
	global $autorank;

	$autorank['old_post_cache'][$id] = bb_get_post( $id );

	return $text;
}
add_filter( 'pre_post', 'autorank_update_score_bbupdatepost_prepost', 10, 2 );

/**
 * Catch topic deletion and update scores as required.
 *
 * Unfortunately, there's no effective way to catch the
 * deletion before it hits the posts, so we need to do a
 * semi-recount.
 */
function autorank_update_score_bbdeletetopic( $topic_id ) {
	$users = bb_post_author_cache( get_thread( $topic_id, array( 'per_page' => -1 ) ) );

	foreach ( $users as $user ) {
		$user_score = 0;
		$posts = bb_cache_posts( $bbdb->prepare( "SELECT `post_id` FROM `$bbdb->posts` WHERE `poster_id` = %d AND `post_status` = 0", $user->ID ), true );

		foreach ( $posts as $post ) {
			$user_score += autorank_get_post_score( $post );
		}

		bb_update_usermeta( $user->ID, 'autorank_score', $user_score );
	}
}
add_action( 'bb_delete_topic', 'autorank_update_score_bbdeletetopic' );

/* Ranking */
function autorank_get_rank( $user_id ) {
	$user_rank = '';
	$rank_score = 0;

	$user = bb_get_user( bb_get_user_id( $user_id ) );
	$user_score = (double) $user->autorank_score;

	$autorank = autorank_get_settings();
	ksort( $autorank['ranks'] );

	foreach ( $autorank['ranks'] as $requirement => $rank ) {
		if ( $requirement > $user_score )
			break;

		$user_rank = $rank;
		$rank_score = $requirement;
	}

	if ( is_array( $user_rank ) ) {
		$user_rank = '<span style="color: ' . esc_attr( $user_rank[1] ) . '">' . esc_html( $user_rank[0] ) . '</span>';
	} else {
		$user_rank = esc_html( $user_rank );
	}

	return array( $user_rank, $rank_score );
}

/* Util */
function &autorank_get_settings() {
	global $autorank;

	if ( ( !isset( $autorank['use_db'] ) || $autorank['use_db'] ) && empty( $autorank['grabbed_db'] ) ) {
		$autorank = wp_parse_args( bb_get_option( 'autorank' ), $autorank );
		$autorank['use_db'] = true;
		$autorank['grabbed_db'] = true;
	}

	return $autorank;
}

/**
 * Get the average post score by selecting up to 100 random posts
 * from the database, computing their scores, and averaging them.
 *
 * @param $user_id int The user ID to limit the search to, or 0 for any posts in the database.
 */
function autorank_get_average_post_score( $user_id = 0 ) {
	global $bbdb;

	if ( $user_id = bb_get_user_id( $user_id ) )
		$posts = bb_cache_posts( $bbdb->prepare( "SELECT `post_id` FROM `$bbdb->posts` WHERE `post_status` = 0 AND `poster_id` = %d ORDER BY RAND() LIMIT 100", $user_id ), true );
	else
		$posts = bb_cache_posts( "SELECT `post_id` FROM `$bbdb->posts` WHERE `post_status` = 0 ORDER BY RAND() LIMIT 100", true );

	$total = 0;
	foreach ( $posts as $post ) {
		$total += autorank_get_post_score( $post );
	}

	return $total / count( $posts );
}


/* Admin */
if ( bb_is_admin() ) {
	require_once dirname( __FILE__ ) . '/autorank-admin.php';
}
