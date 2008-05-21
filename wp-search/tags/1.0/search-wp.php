<?php
/*
Plugin Name: WP Search
Plugin URI: http://www.adityanaik.com/projects/plugins/wp-search-from-bbpress/
Description: Execute search for wordpress posts when you search in bbpress
Author: Aditya Naik
Version: 1.0
Author URI: http://www.adityanaik.com/

Install Instructions: 
- If you don't have a /my-plugins/ directory in your bbpress installaltion, create it on the same level as config.php.
- It's recommened that you only modify the files in your local theme folder /my-templates/.
- the results are contained in an array names $wp_posts.
- In the bbpress template for search i.e. search.php, loop through the $wp_posts array and display the posts. 
- A standard sample display can be obtained by the function bb_wp_search_default_display();

*/ 

function bb_search_wp($q ) {
	
	global $wp_posts,$bbdb, $bb;
	
	if ($bb->wp_table_prefix)	$wp_posts = $bbdb->get_results("SELECT * FROM " . $bb->wp_table_prefix . "posts WHERE (post_title LIKE '%$q%') OR (post_content LIKE '%$q%')");
		
}

add_filter( 'do_search', 'bb_search_wp');

function bb_wp_post_link($row) {
	global $bb;

	if ($bb->wp_table_prefix)	return '<a href="' . $bb->wp_home . '/?p=' . $row->ID . '">' . $row->post_title . '</a>';
	
}

function bb_wp_search_default_display() {
	global $wp_posts;

	if ( $wp_posts ) : ?>
		<h2><?php _e('Blog Posts')?></h2>
		<ol class="results">
			<?php foreach ($wp_posts as $wp_post) :?>
				<li>
					<h4><?php echo bb_wp_post_link($wp_post); ?></h4>
					<p><small><?php _e('Posted') ?> <?php echo date(__('F j, Y, h:i A'), $wp_post->post_date); ?></small></p>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif;


}

?>
