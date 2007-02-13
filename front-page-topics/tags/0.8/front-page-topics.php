<?php
/*
Plugin Name: Front Page Topics
Plugin URI: http://bbpress.org/forums/topic/65#post-333
Description: Changes the number of topics displafed on the front page only.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.8

Requires at least: 0.8
Tested up to: 0.8
*/

function front_page_topics( $num ) {
	if ( ( !$array = bb_get_option( 'front_page_topics' ) ) || !$loc = get_bb_location() )
		return $num;
	if ( isset($array[$loc]) && $array[$loc] > 0 )
		return $array[$loc];
	return $num;
}

function front_page_topics_admin_menu() {
	global $bb_submenu;
	$bb_submenu['site.php'][] = array(__('Front Page Topics'), 'use_keys', 'front_page_topics_admin_page');
}

function front_page_topics_admin_page() {
	$allowed = front_page_topics_allowed();
	if ( !$array = bb_get_option( 'front_page_topics' ) )
		$array = array();
?>
	<h2><?php _e('Items per Page', 'front_page_topics'); ?></h2>
	<form action="" method="post">
	<p><?php _e('Enter the number of items you want to appear on the following types of pages.', 'front-page-topics' ); ?></p>
	<p><?php _e('If you enter <code>0</code>, bbPress will use the default number (as set in your <code>config.php</code> file).', 'front-page-topics'); ?></p>
	<table>
<?php
	foreach ( $allowed as $page => $mess ) : ?>
		<tr>
			<th scope="row"><label for="fpt-<?php echo $page; ?>"><?php echo $mess; ?></label></th>
			<td><input type="text" name="fpt[<?php echo $page; ?>]" id="fpt-<?php echo $page; ?>" value="<?php echo isset($array[$page]) ? $array[$page] : 0; ?>" /></td>
		</tr>
<?php	endforeach; ?>
	</table>
	<p class="submit">
		<?php bb_nonce_field( 'frontpagetopics' ); ?>
		<input type="submit" value="<?php _e('Submit &raquo;'); ?>" />
	</p>
	</form>
<?php }
	
function front_page_topics_admin_page_pre_head() {
	$allowed = front_page_topics_allowed();
	if ( isset($_POST['fpt']) ) :
		$array = array();
		foreach ( array_keys($allowed) as $page )
			$array[$page] = (int) $_POST['fpt'][$page];
		bb_update_option( 'front_page_topics', $array );
		bb_admin_notice( __('Front Page Topics options saved.', 'front-page-topics') );
	endif;
}

function front_page_topics_allowed() {
	return array(
			'front-page' => __('Front Page', 'front-page-topics'),
			'forum-page' => __('Forum Pages', 'front-page-topics'),
			'tag-page' => __('Tag Pages', 'front-page-topics'),
			'topic-page' => __('Topic Pages', 'front-page-topics'),
			'feed-page' => __('Feeds', 'front-page-topics'),
			'profile-page' => __('Profile Pages', 'front-page-topics'),
	);
}	

add_action( 'bb_get_option_page_topics', 'front_page_topics' );

add_action( 'bb_admin_menu_generator', 'front_page_topics_admin_menu' );
add_action( 'front_page_topics_admin_page_pre_head', 'front_page_topics_admin_page_pre_head' );

?>
