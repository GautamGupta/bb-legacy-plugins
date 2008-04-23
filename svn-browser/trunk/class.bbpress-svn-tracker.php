<?php

require( dirname( __FILE__ ) . '/class.automattic-svn-tracker.php' );

class bbPress_SVN_Tracker extends Automattic_SVN_Tracker {
	var $valid_sections = array('description');
	var $extra_sections = array('download');
	var $old_topic_time = false;

	var $live_forum     = BBPRESS_SVN_TRACKER__LIVE_FORUM;

	function bbPress_SVN_Tracker( &$db, $svn_url, $svn_admin_class = false ) {
		$this->__construct( $db, $svn_url, $svn_admin_class );
		register_shutdown_function( array(&$this, '__destruct') );
	}

	function __construct( &$db, $svn_url, $svn_admin_class = false ) {
		parent::__construct( $db, $svn_url, $svn_admin_class );
		if ( $this->admin )
			array_push( $this->extra_sections, 'admin' );

		$this->valid_sections = array_merge( $this->valid_sections, $this->extra_sections );
	}

	function init() {
		global $argc, $argv;

		add_action( 'bb_repermalink_result', array(&$this, 'bb_repermalink_result'), 10, 2 );

		add_filter( 'bb_user_has_cap', array(&$this, 'bb_user_has_cap'), 100, 3 );

		add_filter( 'post_text', array(&$this, 'post_text'), -1, 2 );
		add_filter( 'pre_post',  array(&$this, 'pre_post'),  -1, 3 );
		add_action( 'bb_new_post',  array(&$this, 'bb_new_post'),  -1 );
		add_action( 'bb_update_post',  array(&$this, 'bb_new_post'),  -1 );

		bb_register_view( 'new', 'Newest', array( 'order_by' => 'topic_start_time', 'sticky' => 'all', 'open' => 1 ) );
		bb_register_view( 'updated', 'Recently Updated', array( 'order_by' => 'topic_time', 'sticky' => 'all', 'open' => 1 ) );
		bb_register_view( 'popular', 'Most Popular', array( 'meta_key' => 'downloads', 'order_by' => '0 + tm.meta_value', 'sticky' => 'all', 'open' => 1 ) );

		if ( isset($argc) && $argc ) {
			if ( !bb_is_user_logged_in() && $user = bb_get_user( AUTOMATTIC_SVN_TRACKER__SVN_USER ) ) {
				bb_set_current_user( $user->ID );
			}

			if ( 1 < $argc && 'update' == $argv[1] ) {
				if ( 2 == $argc ) // php bb-load.php update
					return $this->process_changes();

				if ( 'all' == $argv[2] )
					return $this->process_all();

				// php bb-load.php update slug
				return $this->pull_triggers( array( $argv[2] ), array( 'roots' => true, 'paths_in_roots' => true ) );
			}
		}

		if ( is_front() )
			$GLOBALS['bb']->page_topics = 6;

		if ( $this->admin && is_callable( array($this, 'init') ) )
			$this->admin->init();
	}

	function path_trigger( $path_rel, $changed_paths = 'all' ) {
		$this->generate_zip( $path_rel );
		parent::path_trigger( $path_rel, $changed_paths );
	}

	function root_trigger( $root_rel, $changed_paths = 'all' ) {
		if ( $this->is_master )
			$this->save_data( $root_rel );
		parent::root_trigger( $root_rel, $changed_paths );
	}

	function bb_user_has_cap( $all_caps, $caps, $args ) {
		switch ( $args[0] ) :
		case 'write_topic' :
			$forum_id = $args[2];
			if ( $forum_id == $this->live_forum )
				unset($all_caps['write_topics']);
			break;
		endswitch;
		return $all_caps;
	}

	function pull_triggers( $triggers, $types = false ) {
		if ( !is_array( $types ) )
			$types = array( 'paths' => true, 'roots' => true );

		if ( $this->admin && !$old_id = bb_get_current_user_info( 'id' ) ) {
			$user = bb_get_user( AUTOMATTIC_SVN_TRACKER__SVN_USER );
			bb_set_current_user( $user->ID );
		}

		$revision = parent::pull_triggers( $triggers, $types );

		if ( !$old_id )
			bb_set_current_user( 0 );

		return $revision;
	}

	function get_data( $root_rel ) {
		return false;
	}

	function save_data( $root_rel ) {
		// normalize
		$root_rel = $this->path_rel( $root_rel );

		if ( !$slug = $this->get_root( $root_rel ) )
			return false;
		$slug = trim( $slug, '/' );

		$this->error_log( __FUNCTION__, $slug );

		if ( !$data = $this->get_data( $root_rel ) )
			return false;

		// Topic
		if ( $topic = get_topic( $slug ) ) {
			$topic_id = (int) $topic->topic_id;
			// Update Name
			$update_topic = array( 'topic_id' => $topic_id, 'topic_title' => $data->topic_title, 'topic_open' => 1 );
			if ( $data->time_dir ) { // Update Time
				$update_topic['topic_time'] = gmdate( 'Y-m-d H:i:s', $this->get_last_time( $root_rel . $data->time_dir ) );
				$this->error_log( __FUNCTION__, "[topic] $data->topic_title: $topic_id" );
			}
			bb_insert_topic( $update_topic );
		} else {
			$new_topic = array(
				'topic_title' => $data->topic_title,
				'topic_slug' => $slug,
				'forum_id' => $this->live_forum,
				'topic_start_time' => gmdate( 'Y-m-d H:i:s', $this->get_first_time( $root_rel . $data->time_dir ) ),
				'topic_time' => gmdate( 'Y-m-d H:i:s', $this->get_last_time( $root_rel . $data->time_dir ) ),
				'topic_open' => 1
			);
			if ( !$topic_id = bb_insert_topic( $new_topic ) )
				return false;
			$this->error_log( __FUNCTION__,  "(new) [topic] $data->topic_title: $topic_id" );
		}

		if ( $post = bb_get_first_post( $topic_id ) ) {
			$post_id = (int) $post->post_id;
			// update content
			bb_insert_post( array( 'post_id' => $post_id, 'post_text' => $data->post_text ) );
			$this->error_log( __FUNCTION__, "[content] $slug: $topic_id" );
		} else {
			// add new post
			$post_id = bb_insert_post( array( 'topic_id' => $topic_id, 'post_text' => $data->post_text ) );
			$this->error_log( __FUNCTION__, "(new) [content] $slug: $topic_id" );
		}

		// Tags
		if ( $data->tags ) {
			bb_remove_topic_tags( $topic_id );
			bb_add_topic_tags( $topic_id, $this->db->escape_deep( $data->tags ) );
		}

		// We provide
		foreach ( (array) $data->meta as $key => $value )
			bb_update_topicmeta( $topic_id, $key, $value );

		bb_update_topicmeta( $topic_id, 'available_sections', $data->available_sections );

		// They Provide
		foreach ( (array) $data->maybe_meta as $key => $value ) {
			if ( false !== $value )
				bb_update_topicmeta( $topic_id, $key, $value );
			else
				bb_delete_topicmeta( $topic_id, $key );
		}
	}

	// We use topic_time as svn_updated_time
	function pre_post( $content, $post_id, $topic_id ) {
		$topic = get_topic( $topic_id );
		$this->old_topic_time = $topic->topic_time;
		return $content;
	}

	function bb_new_post( $post_id ) {
		if ( $this->old_topic_time && ( $post = bb_get_post( $post_id ) ) && $topic = get_topic( $post->topic_id ) ) {
			$old_topic_time = $this->old_topic_time;
			$this->old_topic_time = false;
			if ( $topic->forum_id == $this->live_forum ) {
				bb_insert_topic( array( 'topic_id' => $topic->topic_id, 'topic_time' => $old_topic_time ) );
			}
		}
		$this->old_topic_time = false;
	}

	// Do we parse the post text or show something special?
	function post_text( $content, $post_id ) {
		$post = bb_get_post( $post_id );
		if ( $post->forum_id != $this->live_forum )
			return $content;
		if ( 1 != $post->post_position )
			return $content;

		$show = is_topic() ? $this->which_post_part() : false;

		return $this->_post_section( $show, $content, $post_id );
	}
		
	// Whatchoo lookin' at?
	function which_post_part( $show = '_current' ) {
		global $page;

		if ( '_current' == $show ) {
			do {
				if ( isset($_GET['show']) ) {
					$show = $_GET['show'];
					break;
				}

				$topic = get_topic( get_topic_id() );

				$i = 0; $part = true;
				while( $part && $part !== $topic->topic_slug )
					$part = get_path($i++);
				$show = get_path($i);

				if ( 'page' == $show ) {
					if ( !$page = get_path( ++$i ) )
						$page = 1;
					$show = 'description';
				} elseif ( !$show ) {
					$show = 'description';
				}

			} while(0);
		}

		if ( $show = $this->valid_section( $show ) )
			return $show;

		return false;
	}

	function valid_section( $show ) {
		if ( in_array($show, $this->valid_sections) || false !== strpos( $show, '.zip' ) )
			return $show;
		return false;
	}

	// Parses $content based on $show
	function _post_section( $show, $content, $post_id ) {
		if ( 'download' == $show )
			return $this->download_page();
		elseif ( 'admin' == $show && $this->admin && $this->admin->current_user_can_admin() )
			return $this->admin->admin_page();
		
		$post = bb_get_post( $post_id );

		$content = preg_split('/<!--plugin-data-([a-z_]*)-->/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
		
		if ( ( $pos = array_search($show, $content) ) && $c = trim($content[$pos + 1]) )
			return $c;

		return trim($content[0]);
	}

	function bb_repermalink_result( $url, $location ) {
		if ( 'topic-page' != $location || !bb_get_option( 'mod_rewrite' ) )
			return $url;

		list($url, $anchor) = explode( '#', $url );
		if ( isset($anchor) && $anchor )
			$anchor = "#$anchor";

		list($url, $query) = explode( '?', $url );
		if ( isset($query) && $query )
			$query = "?$query";

		$topic = get_topic( get_topic_id() );
		
		if ( ( !$show = $this->which_post_part() ) || 'description' == $show )
			return "$url$query$anchor";

		if ( in_array($show, (array) $topic->available_sections) || in_array($show, $this->extra_sections) ) {
			return "$url$show/$query$anchor";
		}

		if ( false !== strpos( $show, '.zip' ) )
			$this->serve_zip_file( $show );

		return "$url$query$anchor";
	}

	/* Template functions */
	function template( $data, $args = '' ) {
		global $topic;

		$defaults = array( 'before' => '', 'after' => '', 'return' => false, 'format' => '' );
		if ( $args && is_string($args) && false === strpos($args, '=') )
			$args = array( 'format' => $args );
		elseif ( is_bool($args) )
			$args = array( 'return' => $args );
		extract(wp_parse_args( $args, $defaults ), EXTR_SKIP);

		$r = '';

		switch ( $data ) :
		case 'download_link' :
			if ( !$stable_tag = $this->get_template( 'stable_tag' ) )
				return;

			if ( $this->download_url )
				$url = $this->download_url;
			else
				$url = get_topic_link();

			if ( !isset($version) || !$version )
				$version = $stable_tag;

			if ( 'trunk' == $version ) {
				$zip = $topic->topic_slug;
			} else { // Normalize file name
				if ( '.' == substr( $version, 0, 1 ) )
					$version = "0$version";
				if ( '.' == substr( $version, -1 ) )
					$version = "$version0";

				$zip = $topic->topic_slug . '.' . $this->sanitize_filename( $version );
			}

			$url .= $zip;

			if ( !$format )
				$format = 'Download';

			$r = "<a href='$url.zip'>$format</a>";
			break;
		case 'downloads' :
			$r = number_format( $this->get_template( 'downloads' ) );
			break;
		default :
			$r = $this->get_template( $data );
			break;
		endswitch;
		if ( $r ) {
			if ( !$return )
				echo $before . $r . $after;
			return $before . $r . $after;
		}
	}

	function get_template( $data ) {
		global $topic;
		switch ( $data ) :
			case 'downloads' :
				if ( !$topic->$data )
					return;
				return attribute_escape( $topic->$data );
				break;
			case 'repo_tags' :
				if ( !$topic->repo_tags )
					return array();
				natcasesort($topic->repo_tags);
				return array_reverse( $topic->repo_tags );
				break;
			default :
				return;
				break;
		endswitch;
	}

	function list_sections() {
		global $topic;

		$current = $this->which_post_part();
	?>

		<ul id="sections">

	<?php
		foreach( $this->valid_sections as $section ) :
			if ( 'download' == $section )
				continue;
			$class  = "section-$section";
			$class .= $section == $current ? ' current' : '';
			if ( 'admin' == $section && $this->admin && !$this->admin->current_user_can_admin() )
				continue;
			if ( 'admin' != $section && !( in_array($section, (array) $topic->available_sections) || in_array($section, $this->extra_sections) ) )
				continue;
	?>

			<li class="<?php echo $class; ?>">
				<a href="<?php echo $this->section_url( $section ); ?>"><?php echo $this->section_title( $section ); ?></a>
			</li>

	<?php endforeach; ?>

		</ul>

	<?php
	}

	function section_url( $section, $topic_id = 0 ) {
		$section = $this->which_post_part( $section );
		if ( bb_get_option( 'mod_rewrite' ) ) {
			if ( 'description' == $section )
				return rtrim( get_topic_link( $topic_id ), '/' ) . '/';
			return rtrim( get_topic_link( $topic_id ), '/' ) . "/$section/";
		} else {
			if ( 'description' == $section )
				return remove_query_arg( 'show', get_topic_link( $topic_id ) );
			return add_query_arg( 'show', $section, get_topic_link( $topic_id ) );
		}
	}

	function section_title( $section ) {
		return ucwords( str_replace( '_', ' ', $section ) );
	}

	function download_page() {
		ob_start();
		global $topic;

	?>

	<h3>Current Version</h3>
	<p class="unmarked-list">

	<?php
		$current = $this->get_template( 'stable_tag' );
		$version = $topic->version ? $topic->version : $current;
		$this->template( 'download_link', array( 'format' => $version, 'version' => $version ) );
	?>

	</p>

	<?php if ( $versions = $this->get_template( 'repo_tags' ) ) : ?>

	<h3>Other Versions</h3>
	<ul class="unmarked-list">

	<?php foreach ( $versions as $version ) : if ( $current == $version ) continue; ?>

		<li><?php $this->template( 'download_link', array( 'format' => $version, 'version' => $version ) ); ?></li>

	<?php endforeach; if ( 'trunk' != $current ) : ?>

		<li><?php $this->template( 'download_link', array( 'format' => 'Development Version', 'version' => 'trunk' ) ); ?></li>

	<?php endif; ?>

	</ul>

	<?php endif;
		return ob_get_clean();
	}

	function get_current_revision() {
		$current_revision = (int) bb_get_option( 'svn_r_' . str_replace( '.', '_', php_uname( 'n' ) ) );

		if ( $this->is_master ) {
			$master_revision = (int) bb_get_option( 'svn_r__master' ); // double underscore intentional
			$current_revision = min( $current_revision, $master_revision );
		}
		return $current_revision;
	}

	function set_current_revision( $revision ) {
		if ( !$revision = (int) $revision )
			return false;

		bb_update_option( 'svn_r_' . str_replace( '.', '_', php_uname( 'n' ) ), $revision );
		if ( $this->is_master )
			bb_update_option( 'svn_r__master', $revision );

		return $revision;
	}		

	function process_all( $via = 'local', $types = false ) {
		if ( !is_array($types) )
			$types = array( 'roots' => 'grouped', 'paths_in_roots' => true );

		if ( $revision = parent::process_all( $via, $types ) )
			$this->set_current_revision( $revision );
	}

	function get_all_roots( $via = 'local' ) {
		if ( 'local' !== $via )
			return parent::get_all_roots( $via );

		global $bbdb;

		// Should be more generic and take into account $root_def
		$root_rels = $bbdb->get_col( $bbdb->prepare(
			"SELECT topic_slug FROM $bbdb->topics WHERE forum_id = %d ORDER BY topic_id",
			$this->live_forum
		) );

		return array_map( create_function( '$a', 'return "$a/";'), $root_rels );
	}

	function process_changes( $types = false ) {
		$current_revision = $this->get_current_revision();

		if ( !$revision = parent::process_changes( $current_revision + 1, 'HEAD', $types ) )
			return false;

		if ( !is_bool( $revision ) ) // could be: true
			$this->set_current_revision( $revision );

		return $revision;
	}

	function zip_file_served( $topic_id ) {
		if ( $topic = get_topic( $topic_id ) )
			bb_update_topicmeta( $topic->topic_id, 'downloads', intval($topic->downloads) + 1 );
	}
}

?>
