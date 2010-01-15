<?php
/*
Plugin Name: Burning Tags
Description: Display only tags from topics that were posted to this month in the tag cloud.
Version: 0.1
Plugin URI: http://nightgunner5.wordpress.com/tag/burning-tags
Author: Ben L. (Nightgunner5)
Author URI: http://nightgunner5.wordpress.com/
*/

function burning_tags_filter( $r, $tags, $args ) {
	global $bbdb;

	if ( !$tags )
		return;

	$date = date( 'Y-m-d H:i:s', time() - 2592000 ); // 1 month

	extract($args, EXTR_SKIP);

	$tags = $bbdb->get_results( "SELECT `t`.*, COUNT(*) as `count`, SUM(`to`.`topic_posts`) AS `posts` FROM `{$bbdb->term_relationships}` as `tr`

LEFT JOIN `{$bbdb->term_taxonomy}` as `tt`
ON `tr`.`term_taxonomy_id` = `tt`.`term_taxonomy_id`

LEFT JOIN `{$bbdb->terms}` as `t`
ON `t`.`term_id` = `tt`.`term_id`

LEFT JOIN `{$bbdb->topics}` as `to`
ON `to`.`topic_id` = `tr`.`object_id`

WHERE `to`.`topic_time` > '{$date}'
AND `tt`.`taxonomy` = 'bb_topic_tag'

GROUP BY `t`.`term_id`
ORDER BY `tt`.`count` DESC
LIMIT {$limit}" );

	if ( !$tags )
		return;

	foreach ( (array)$tags as $tag ) {
		$topics{$tag->name} = $tag->count;
		$counts{$tag->name} = $tag->count + log10( $tag->posts );
		$taglinks{$tag->name} = bb_get_tag_link( $tag->slug );
	}

	$min_count = min($counts);
	$spread = max($counts) - $min_count;
	if ( $spread <= 0 )
		$spread = 1;
	$fontspread = $largest - $smallest;
	if ( $fontspread <= 0 )
		$fontspread = 1;
	$fontstep = $fontspread / $spread;

	do_action_ref_array( 'sort_tag_heat_map', array( &$counts ) );

	$a = array();

	foreach ( $counts as $tag => $count ) {
		$taglink = esc_attr( $taglinks{$tag} );
		$topic_count = $topics{$tag};
		$tag = str_replace( ' ', '&nbsp;', esc_html( $tag ) );
		$fontsize = round( $smallest + ( ( $count - $min_count ) * $fontstep ), 1 );
		$a[] = "<a href='$taglink' title='" . esc_attr( sprintf( _n( '%d topic', '%d topics', $topics{$tag}, 'burning-tags' ), $topic_count ) ) . "' rel='tag' style='font-size:$fontsize$unit;'>$tag</a>";
	}

	switch ( $format ) {
		case 'array':
			$r =& $a;
			break;
		case 'list':
			$r = "<ul class='bb-tag-heat-map'>\n\t<li>";
			$r .= join( "</li>\n\t<li>", $a );
			$r .= "</li>\n</ul>\n";
			break;
		default:
			$r = join( "\n", $a );
			break;
	}

	return $r;
}
if ( !bb_is_tags() )
	add_filter( 'bb_get_tag_heat_map', 'burning_tags_filter', 10, 3 );
