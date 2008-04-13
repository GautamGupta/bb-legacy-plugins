<?php

if ( !defined( 'AUTOMATTIC_README__PATH' ) )
	define( 'AUTOMATTIC_README__PATH', dirname( __FILE__ ) . '/readme-parser/parse-readme.php' );

require( dirname( __FILE__ ) . '/class.bbpress-svn-tracker.php' );

class bbPress_Plugins_Tracker extends bbPress_SVN_Tracker {
	var $valid_sections  = array('description', 'installation', 'faq', 'screenshots', 'other_notes' );
	var $readme_cache = array();
	var $plugin_files_cache = array();

	function init() {
		parent::init();

		add_action( 'bb_parse_query', array(&$this, 'bb_parse_query'), 100 );
		add_filter( 'get_recent_user_threads_where', array(&$this, 'get_recent_user_threads_where') );
		add_filter( 'get_recent_user_threads_join', array(&$this, 'get_recent_user_threads_join') );

		bb_deregister_view( 'no-replies' );
		bb_deregister_view( 'untagged' );
	}

	function bb_parse_query( &$query ) {
		// BB_Query args part of $this->get_recent_user_threads_where() below
		if ( 'get_recent_user_threads' == $query->query_id ) {
			$query->query_vars['order_by'] = 't.topic_time';
			$query->query_vars['meta_key'] = 'contributors';
		}
	}

	function get_recent_user_threads_where( $where ) {
		return preg_replace_callback( '#t.topic_poster\s+=\s+[\1"]?(\d+)[\'"]?#', array(&$this, 'get_recent_user_threads_where_callback'), $where );
	}

	function get_recent_user_threads_join( $join ) {
		return " LEFT $join";
	}

	function get_recent_user_threads_where_callback( $matches ) {
		if ( !$user = bb_get_user( $matches[1] ) )
			return $matches[0];

		$_user = "%\"$user->user_login\"%";
		return "( $matches[0] " . $this->db->prepare( "OR meta_value LIKE %s", $_user ) . ' )';
	}

	function pull_triggers( $triggers, $types = false ) {
		// remove pre_post filters
		$pre_post_filters = $GLOBALS['wp_filter']['pre_post'];
		$GLOBALS['wp_filter']['pre_post'] = array();

		add_filter( 'pre_post',  array(&$this, 'pre_post'),  -1, 3 );

		$revision = parent::pull_triggers( $triggers, $types );

		// put pre_post_filters back
		$GLOBALS['wp_filter']['pre_post'] = $pre_post_filters;

		return $revision;
	}

	function root_trigger( $root_rel, $changed_paths = 'all' ) {
		if ( 'all' === $changed_paths || isset($changed_paths['all_in']) || preg_grep( '#/screenshot-#', (array) $changed_paths ) )
			$this->generate_screenshots( $root_rel );
		parent::root_trigger( $root_rel, $changed_paths );
	}

	function get_data( $root_rel ) {
		// normalize
		$root_rel = $this->path_rel( $root_rel );

		if ( !$slug = $this->get_root( $root_rel ) )
			return false;
		$slug = trim( $slug, '/' );

		$this->error_log( __FUNCTION__, $slug );

		extract( $this->get_stable_tag_dir( $root_rel ), EXTR_PREFIX_ALL, 'stable' );
		// $stable_tag, $stable_tag_dir, $stable_tag_subdir ^^^

		$readme = $this->parse_readme_in( $root_rel . $stable_tag_dir );
		$plugin_files = $this->get_plugin_files( $this->create_export( $root_rel . $stable_tag_dir ) );

		if ( !$readme && !$plugin_files )
			return false;

		$this->error_log( __FUNCTION__, "stable tag: $stable_tag   stable_tag_dir: $stable_tag_dir   stable_tag_subdir: $stable_tag_subdir" );

		// Some plugin roots come bundled with multiple plugins.  If this root does, use the name in the readme.txt to find the main plugin
		$readme_name = Automattic_Readme::user_sanitize( strtolower( $readme['name'] ) );

		// Get the plugin header data from the most likely plugin file
		// Only search in what will be included in the zip (stable_tag_subdir)
		$plugin_data = array();
		$plugin_depth = 1000;
		foreach ( $plugin_files as $plugin_file ) {
			$this_plugin_depth = substr_count( $plugin_file, '/' );
			if ( $plugin_data && $plugin_depth < $this_plugin_depth )
				break;
			$plugin_depth = $this_plugin_depth;
			$plugin_data = $this->get_plugin_data( $plugin_file );
			if ( $plugin_data && $readme_name == Automattic_Readme::user_sanitize( strtolower( $plugin_data['name'] ) ) )
				break;
		}
		if ( !$plugin_data )
			$plugin_data = array();

		if ( !$plugin_data && !$readme )
			return false;

		// Normalize plugin name
		$plugin_data['name'] = htmlspecialchars_decode( $plugin_data['name'], ENT_QUOTES );
		$plugin_data['name'] = Automattic_Readme::sanitize_text( $plugin_data['name'] );

		// Start building the return value
		$data = array_merge( $plugin_data, $readme, compact( 'stable_tag', 'stable_tag_dir', 'stable_tag_subdir' ) );

		// An array of all repository tag dir names
		$repo_tags = array();
		if ( $tags_ls = $this->svn->ls( $this->svn_url . $root_rel . 'tags/', 'type=dir' ) ) // ls is cached - don't worry
			foreach ( (array) $tags_ls as $tag_dir )
				$repo_tags[] = $this->sanitize_filename( rtrim( $tag_dir, '/' ) );

		if ( $repo_tags )
			$data['repo_tags'] = $repo_tags;

		// If no description from readme, generate one from plugin header
		if ( !$data['short_description'] && $plugin_data['description'] ) {
			$desc = str_replace(array('<p>', '</p>'), "\n", $plugin_data['description']);
			$data['sections']['description'] = Automattic_Readme::filter_text( $desc, true );
			$short_desc_filtered = Automattic_Readme::sanitize_text( $desc );
			if ( 150 > $short_desc_length = strlen( $short_desc_filtered ) )
				$data['is_truncated'] = 1;
			$data['short_description'] = substr($short_desc_filtered, 0, 150);
		}

		$data['is_excerpt'] = (int) $data['is_excerpt'];
		$data['is_truncated'] = (int) $data['is_truncated'];

		// Make sure readme files don't have the example plugin name as their plugin name
		$bad_name = strtolower(str_replace(' ', '', $data['name']));
		if ( in_array($bad_name, array('pluginname', 'bbratings')) && $plugin_data['name'] )
			$data['name'] = $plugin_data['name'];


		// We want to store both for search purposes
		$data['plugin_name'] = $plugin_data['name'];
		$data['readme_name'] = $readme['name'];
		$data['plugin_san']  = sanitize_with_dashes( $plugin_data['name'] );
		$data['readme_san']  = sanitize_with_dashes( $readme['name'] );

		$return = new stdClass;

		$return->topic_title = $data['name'];

		// Set topic time if
		//	New topic
		//	Old topic, but new version if different than the old version
		if ( ( !$topic = get_topic( $slug ) ) || !isset($topic->version) || $topic->version != $data['version'] )
			$return->time_dir = $data['stable_tag_dir'] . $data['stable_tag_subdir'];
		else // Old topic, but new version is the same as the old version: don't update topic time
			$return->time_dir = false;

		$return->post_text = $this->construct_content( $data, $slug );

		$return->meta = array();
		foreach( array( 'stable_tag', 'stable_tag_dir', 'stable_tag_subdir', 'is_excerpt', 'is_truncated', 'plugin_name', 'plugin_san', 'readme_name', 'readme_san' ) as $meta )
			$return->meta[$meta] = $data[$meta];

		$return->maybe_meta = array();
		foreach ( array( 'contributors', 'version', 'uri', 'author', 'author_uri', 'repo_tags', 'requires', 'tested', 'donate_link' ) as $meta ) {
			if ( isset($data[$meta]) && $data[$meta] )
				$return->maybe_meta[$meta] = $data[$meta];
			else
				$return->maybe_meta[$meta] = false;
		}

		$return->tags = $data['tags'];

		$return->available_sections = array_keys( $data['sections'] );
		if ( isset($data['remaining_content']) && $data['remaining_content'] )
			$return->available_sections[] = 'other_notes';

		return $return;
	}

	function get_stable_tag_dir( $root_rel ) {
		$tag = 'trunk';
		$tag_dir = 'trunk/';
		$tag_subdir = '';

		do {
			// Get readme file from trunk
			if ( $readme = $this->parse_readme_in( $root_rel . 'trunk/' ) ) {
				extract( $this->get_stable_tag_dir_using( $root_rel . 'trunk/' ) );
				break;
			}

			if ( $this->get_plugin_files( $this->create_export( $root_rel . 'trunk/' ) ) ) { // no readme, but we do have plugin files.  Use trunk.
				$readme = array();
				break;
			}

			// Look in tags.
			if ( !$tags = $this->svn->ls( $this->svn_url . $root_rel . 'tags/', 'type=dir' ) )
				break;

			natsort( $tags );
			
			$natsort_tag = array_pop( $tags );

			if ( !$readme = $this->parse_readme_in( $root_rel . "tags/$natsort_tag" ) ) {
				if ( $this->get_plugin_files( $this->create_export( $root_rel . "tags/$natsort_tag" ) ) ) { // no readme, but we do have plugin files.  Use this tag.
					$tag = trim( $natsort_tag, '/' );
					$tag_dir = "tags/$natsort_tag";
					$tag_subdir = '';
				}
				break;
			}

			extract( $this->get_stable_tag_dir_using( $root_rel . "tags/$natsort_tag" ) );
		} while(0);

		return compact( array( 'tag', 'tag_dir', 'tag_subdir' ) );
	}

	function get_stable_tag_dir_using( $path_rel ) {
		// normalize
		$path_rel = $this->path_rel( $path_rel );

		if ( !$this->create_export( $path_rel ) )
			return false;

		$tag = 'trunk';
		$tag_dir = 'trunk/';
		$tag_subdir = '';

		if ( !$readme = $this->parse_readme_in( $path_rel ) )
			return compact( 'tag', 'tag_dir', 'tag_subdir' );

		if ( isset($readme['stable_tag']) ) {
			$readme['stable_tag'] = trim( $readme['stable_tag'], " \t/\\" );
			$stable_tag_dir = escapeshellcmd($readme['stable_tag']); // The full directory
			$stable_tag = preg_replace( '#/.*#', '', $stable_tag_dir ); // Just the tag name
			$stable_tag_subdir = trim( substr( $stable_tag_dir, strlen( "$stable_tag/" ) ), '/' );

			$root_rel = $this->get_root( $path_rel );

			// Trunk and subdir specified
			if ( 'trunk' == $stable_tag ) {
				$path_abs = $this->create_export( $root_rel . 'trunk/' );
				if ( $path_abs && $stable_tag_subdir && is_dir( $path_abs . $stable_tag_subdir ) )
					$tag_subdir =  $this->path_rel( $stable_tag_subdir );

			// Tag doesn't exist, use trunk, don't use a subdir
			} elseif ( !$path_abs = $this->create_export( $root_rel . "tags/$stable_tag" ) ) { // [sic]

			// Tag exists, no subdir specified.
			// Tag exists, but not the specified subdir.  Use root tag dir.
			} elseif ( ( !$stable_tag_subdir ) || ( $stable_tag_subdir && !is_dir( $path_abs . $stable_tag_subdir ) ) ) {
				$tag = $stable_tag;
				$tag_dir = "tags/$stable_tag/";
				$tag_subdir = '';

			// Tag exists and so does subdir
			} else {
				$tag = $stable_tag;
				$tag_dir = "tags/$stable_tag/";
				$tag_subdir = $this->path_rel( $stable_tag_subdir );
			}
		}

		$this->error_log( __FUNCTION__, "tag: $tag   tag_dir: $tag_dir   tag_subdir: $tag_subdir" );

		return compact( 'tag', 'tag_dir', 'tag_subdir' );
	}

	function parse_readme_in( $path_rel ) {
		// normalize
		$path_rel = $this->path_rel( $path_rel );

		if ( isset($this->readme_cache[$path_rel]) )
			return $this->readme_cache[$path_rel];

		if ( !class_exists( 'Automattic_Readme' ) )
			require( AUTOMATTIC_README__PATH );

		$readme = false;

		do {
			if ( !$export = $this->create_export( $path_rel ) )
				break;

			$readme = array();
			if ( !$readme_files = Automattic_Paths::search( $export, 'readme.txt' ) )
				break;

			$readme_file = $readme_files[0];

			$parser = new Automattic_Readme;

			$this->error_log( __FUNCTION__, "[readme] $readme_file" );

			if ( !$readme = $parser->parse_readme( $readme_file ) )
				break;

			// Map some readme data to different names
			foreach ( array( 'tested_up_to' => 'tested', 'requires_at_least' => 'requires') as $was => $is ) {
				$readme[$is] = $readme[$was];
				unset($readme[$was]);
			}

			if ( isset($readme['sections']['frequently_asked_questions']) ) {
				$readme['sections']['faq'] = $readme['sections']['frequently_asked_questions'];
				unset($readme['sections']['frequently_asked_questions']);
			}
		} while(0);

		$this->readme_cache[$path_rel] = $readme;

		return $readme;
	}

	function get_plugin_files( $path_abs ) {
		if ( !$path_abs )
			return array();

		if ( isset( $this->plugin_files_cache[$path_abs] ) )
			return $this->plugin_files_cache[$path_abs];

		if ( 50 < count($this->plugin_files_cache) )
			$this->plugin_files_cache = array();

		$this->plugin_files_cache[$path_abs] = Automattic_Paths::search( $path_abs, '*.php', '^[[:space:]*#/]*plugin name:' );
		return $this->plugin_files_cache[$path_abs];
	}

	function construct_content( $data, $slug ) {
		$content = Automattic_Readme::sanitize_text( $data['short_description'] ) . "\n\n";
		if ( !$data['sections'] && !$data['remaining_content'] )
			return $content;

		foreach ( $this->valid_sections as $section ) {
			if ( !isset($data['sections'][$section]) || !$data['sections'][$section] )
				continue;

			if ( 'screenshots' == $section && $data['screenshots'] ) {
				$exts = array(
					'.png' => 'imagecreatefrompng',
					'.gif' => 'imagecreatefromgif',
					'.jpg' => 'imagecreatefromjpeg',
					'.jpeg' => 'imagecreatefromjpeg'
				);

				$images = array();
				$image_list = '';

				foreach ( (array) Automattic_Paths::search( $this->download_path . "$slug/", 'screenshot-*' ) as $file ) {
					if ( !preg_match('#/screenshot-(\d+)(\.[^.]+)$#', $file, $matches ) )
						continue;
					if ( !isset( $exts[$matches[2]] ) )
						continue;

					$key = (int) $matches[1];

					if ( isset($images[$key]) )
						continue;

					$image = basename($file);

					if ( !isset($data['screenshots'][$key - 1]) )
						continue;

					$desc = $data['screenshots'][$key - 1];

					$image_list .= "\t<li>\n";
					$image_list .= "\t\t<img class='screenshot' src='$image' alt='$slug screenshot $key' />\n";
					$image_list .= "\t\t<p>$desc</p>\n";
					$image_list .= "\t</li>\n";
				}

				if ( !$image_list )
					continue;

				$content .= "<!--plugin-data-$section-->\n";
				$content .= "<ol class='screenshots'>\n";
				$content .= $image_list;
				$content .= "</ol>\n\n";

			} elseif ( $c = trim($data['sections'][$section]) ) {

				$content .= "<!--plugin-data-$section-->\n";
				$content .= "$c\n\n";

			}
		}

		if ( isset($data['remaining_content']) && $c = trim($data['remaining_content']) ) {
			$content .= "<!--plugin-data-other_notes-->\n";
			$content .= $c;
		}

		return trim($content);
	}

	function generate_screenshots( $root_rel ) {
		$slug = $this->get_root( $root_rel );
		if ( !$slug = trim( $slug, '/' ) )
			return false;

		if ( !Automattic_Paths::mkdir( $this->download_path . "$slug/" ) )
			return false;

		extract( $this->get_stable_tag_dir( $root_rel ), EXTR_SKIP );
		// $tag, $tag_dir, $tag_subdir

		// Delete old screenshots
		foreach ( (array) Automattic_Paths::search( $this->download_path . "$slug/", 'screenshot-*', false, true ) as $file ) {
			if ( !preg_match('/\.[^.]+$/', $file, $matches ) )
				continue;
			if ( !isset( $exts[$matches[0]] ) )
				continue;

			unlink( $file );
		}

		$exts = array(
			'.png' => 'imagecreatefrompng',
			'.gif' => 'imagecreatefromgif',
			'.jpg' => 'imagecreatefromjpeg',
			'.jpeg' => 'imagecreatefromjpeg'
		);

		if ( !$export = $this->create_export( $root_rel . $tag_dir ) )
			return false;
		if ( !$readme = $this->parse_readme_in( $root_rel . $tag_dir ) )
			return false;

		if ( !isset($readme['screenshots']) || !$readme['screenshots'] )
			return array();

		$i = 0;
		$images = array();
		foreach ( $readme['screenshots'] as $ss ) {
			$i++;
			$img = false;

			foreach ( (array) Automattic_Paths::search( $export, "screenshot-$i.*" ) as $file ) {
				if ( !preg_match('/\.[^.]+$/', $file, $matches ) )
					continue;
				if ( !isset( $exts[$matches[0]] ) )
					continue;

				// Validate the image file if we can
				if ( is_callable( $exts[$matches[0]] ) ) {
					if ( !$image_resource = call_user_func( $exts[$matches[0]], $file ) ) {
						$this->error_log( __FUNCTION__, "[image] BAD!: $slug " . basename($file) );
						continue;
					}
					imagedestroy( $image_resource );
				}

				$img = basename($file);

				// Copy screenshot to accessible place
				if ( !copy( $file, $this->download_path . "$slug/$img" ) ) {
					$img = false;
				} else {
					$this->error_log( __FUNCTION__, "[image] $slug $img" );
					break;
				}
			}

			if ( $img )
				$images[$i] = $img;
		}

		return $images;
	}

	/* Template functions */
	function template( $data, $args = '' ) {
		global $topic;

		$defaults = array( 'before' => '', 'after' => '', 'return' => false, 'format' => '' );
		if ( $args && is_string($args) && false === strpos($args, '=') )
			$args = array( 'format' => $args );
		elseif ( is_bool($args) )
			$args = array( 'return' => $args );

		$args = wp_parse_args( $args, $defaults );

		extract($args);

		$r = '';

		switch ( $data ) :
		case 'sneaky_link' :
			if ( 1 < count($this->get_template( 'contributors' )) ) {
				if ( is_array($format) )
					$args['format'] = $format['plugin_link'];
				return $this->template( 'plugin_link', $args );
			}
			if ( is_array($format) )
				$args['format'] = $format['author_link'];
			return $this->template( 'author_link', $args );
			break;
		case 'plugin_link' :
			$format = $format ? $format : 'Plugin Homepage  &#187;';
			if ( $uri = $this->get_template( 'uri' ) )
				$r = "<a href='$uri'>$format</a>";
			break;
		case 'author_link' :
			$format = $format ? $format : $this->get_template( 'author' );
			if ( $author_uri = $this->get_template( 'author_uri' ) )
				$r = "<a href='$author_uri'>$format</a>";
			break;
		case 'contributors_links' :
			global $topic;
			$contributors = $this->get_template( 'contributors' );
			foreach ( $contributors as $c ) {
				if ( !$user = bb_get_user( $c ) ) {
					$r .= "$c "; // "<a href='" . bb_get_option( 'uri' ) . "profile/$c'>$c</a> ";
					continue;
				}
				if ( $user->ID == $topic->topic_poster )
					continue; 
				$r .= "<a href='" . get_user_profile_link( $user->ID ) . "'>$c</a> ";
			}
			$r = trim($r);
			break;
		case 'author_contributors_links' :
			global $topic;
			$contributors = $this->get_template( 'contributors' );
			$r = array();
			$user = false;
			foreach ( $contributors as $c ) {
				if ( !$user = bb_get_user( $c ) ) {
					$r[] = $c; // "<a href='" . bb_get_option( 'uri' ) . "profile/$c'>$c</a>";
					continue;
				}
				$r[] = "<a href='" . get_user_profile_link( $user->ID ) . "'>$c</a>";
			}
			if ( empty($r) || 1 == count($r) && $topic->topic_poster == $user->ID ) {
				$author_uri = get_user_profile_link( $topic->topic_poster );
				if ( !$author = $this->get_template( 'author' ) )
					$author = $topic->topic_poster_name;
				$r = array( "<a href='$author_uri'>$author</a>" );
			}
			if ( $before && is_array($before) )
				$before = 1 < count($r) ? $before[1] : $before[0];
			$r = join(', ', $r);
			break;
		case 'donate_link' :
			$format = $format ? $format : 'Donate to this plugin  &#187';
			if ( $url = $this->get_template( 'donate_link' ) )
				$r = "<a href='$url'>$format</a>";
			break;
		case 'trac_url' :
			$slug = $this->get_template( 'slug' );
			$r = $this->trac_url . $slug;
			break;
		default :
			if ( $parent = parent::template( $data, array_merge( $args, array( 'return' => true ) ) ) ) {
				if ( !$return )
					echo $parent;
				return $parent;
			}

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
			case 'uri' :
			case 'author_uri' :
			case 'donate_link' :
				if ( !$topic->$data )
					return;
				return bb_fix_link( $topic->$data );
				break;
			case 'slug' :
			case 'stable_tag' :
			case 'stable_tag_dir' :
			case 'stable_tag_subdir' :
			case 'version' :
			case 'author' :
			case 'requires' :
			case 'tested' :
				if ( !$topic->$data )
					return;
				return attribute_escape( $topic->$data );
				break;
			case 'dir_name' :
				$r = $topic->stable_tag_dir . $topic->stable_tag_subdir;
				return attribute_escape( $r );
			case 'contributors' :
				if ( !$topic->contributors )
					return array();
				return $topic->contributors;
				break;
			case 'short_description' :
				$post = bb_get_first_post( $topic->topic_id );
				return $this->post_text( $post->post_text, 'short_description' );
				break;
			default :
				if ( $parent = parent::get_template( $data ) )
					return $parent;
				return;
				break;
		endswitch;
	}

	function get_plugin_data( $file ) {
		require_once( BBPATH . 'bb-admin/admin-functions.php' );
		$this->error_log( __FUNCTION__, $file );
		return bb_get_plugin_data( $file );
	}

}

if (!function_exists("htmlspecialchars_decode")) {
	function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT) {
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	}
}

?>
