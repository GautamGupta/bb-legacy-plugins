<?php

if ( !defined( 'AUTOMATTIC_SVN_TRACKER__ZIPLIB_PATH' ) )
	define( 'AUTOMATTIC_SVN_TRACKER__ZIPLIB_PATH', dirname( __FILE__ ) . '/zip-lib.php' );
if ( !defined( 'AUTOMATTIC_SVN_TRACKER__SVN_USER' ) )
	define( 'AUTOMATTIC_SVN_TRACKER__SVN_USER', 'user' );
if ( !defined( 'AUTOMATTIC_SVN_TRACKER__SVN_PASSWORD' ) )
	define( 'AUTOMATTIC_SVN_TRACKER__SVN_PASSWORD', 'pass' );
if ( !defined( 'AUTOMATTIC_SVN_TRACKER__TRAC_URL' ) )
	define( 'AUTOMATTIC_SVN_TRACKER__TRAC_URL', false );

require( 'class.automattic-svn.php' );
require( 'class.automattic-paths.php' );

class Automattic_SVN_Tracker {
	var $db;
	var $svn_url;

	var $download_path = AUTOMATTIC_SVN_TRACKER__DOWNLOAD_PATH;
	var $download_url  = AUTOMATTIC_SVN_TRACKER__DOWNLOAD_URL;
	var $trac_url      = AUTOMATTIC_SVN_TRACKER__TRAC_URL;

	var $is_master;
	var $admin = false;
	var $svn;
	var $export_cache = array();

	// Roots are like some/thing/ (the root of that part of the repository)
	// Paths are like some/thing/tags/2.0/ or some/thing/trunk/ (an actual version of the thing in that part of the repository)

	// Below, we define roots as being any top level directory
	// and paths as being any tag, branch or the trunk of that top level directory

	var $root_def = '*'; /* */
	var $path_defs = array( 'trunk' => '*/trunk/', 'tag' => '*/tags/*/', 'branch' => '*/branches/*/' ); /* */

	function Automattic_SVN_Tracker( &$db, $svn_url, $svn_admin_class = false ) {
		$this->__construct( $db, $svn_url );
		register_shutdown_function( array(&$this, '__destruct') );
	}

	function __construct( &$db, $svn_url, $svn_admin_class = false ) {
		$this->db =& $db;
		$this->svn_url = Automattic_Paths::trailing_slash_it( $svn_url );
		$this->svn = new Automattic_SVN( array( 'username' => AUTOMATTIC_SVN_TRACKER__SVN_USER, 'password' => AUTOMATTIC_SVN_TRACKER__SVN_PASSWORD ) );

		if ( !defined( 'AUTOMATTIC_SVN_TRACKER__MASTER_SERVER' ) || php_uname( 'n' ) == AUTOMATTIC_SVN_TRACKER__MASTER_SERVER )
			$this->is_master = true;

		if ( $svn_admin_class && class_exists( $svn_admin_class ) )
			$this->admin = new $svn_admin_class( $this );
	}

	function __destruct() {
		$r = $this->svn->requests;
//		$this->error_log( "TOTAL SVN REQS: $r" );
		$this->flush_export_cache();
	}

	function get_changes( $old_revision, $new_revision = 'HEAD' ) {
		return $this->svn->log( $this->svn_url, array( 'revision' => "$new_revision:$old_revision" ) );
	}

	function process_changes( $old_revision, $new_revision, $types = false ) {
		if ( !$svn_log = $this->get_changes( $old_revision, $new_revision ) )
			return false;
		return $this->pull_triggers( $svn_log, $types );
	}

	// Try never to use this
	function process_all( $via = 'ls', $types = false ) {
		$latest_revision = $this->svn->info( $this->svn_url, 'revision' );

		if ( !is_array($types) )
			$types = array( 'roots' => 'grouped', 'paths_in_roots' => true );

		$roots = $this->get_all_roots( $via );

		if ( $this->pull_triggers( $roots, $types ) )
			return $latest_revision;
		return false;
	}

	// $triggers: what triggers to pull, can be paths or an svn log output
	// $types: what types of triggers to pull
	function pull_triggers( $triggers, $types = false ) {
		if ( !$triggers )
			return false;

		$triggers = (array) $triggers;

		if ( !is_array( $types ) )
			$types = array( 'revisions' => true, 'paths' => true, 'roots' => true );

		reset($triggers);

		if ( is_object( current($triggers) ) ) { // its an svn log
			$path_rels = array();
			foreach ( $triggers as $rev => $entry ) {
				if ( isset($types['revisions']) && $types['revisions'] )
					$this->revision_trigger( $rev, $entry );
				$path_rels = array_merge( $path_rels, array_keys( $entry->paths ) );
			}
			$return_revision = array_shift( array_keys( $triggers ) );
                } else { // its some paths
			$path_rels =& $triggers;
			$return_revision = true;
		}

		if ( ( isset($types['roots']) && $types['roots'] ) || ( isset($types['paths_in_roots']) && $types['paths_in_roots'] ) ) {
			if ( 'grouped' === $types['roots'] ) // roots are pre-grouped
				$roots =& $path_rels;
			else // find the roots
				$roots = Automattic_Paths::group( $path_rels, $this->root_def );

			if ( isset($types['roots']) && $types['roots'] ) {

				// We pull the triggers of each root and of all paths in each root
				if ( isset($types['paths_in_roots']) && $types['paths_in_roots'] ) {
					foreach ( $roots as $root ) {
						$this->root_trigger( $root );
						$paths = $this->get_all_paths_in( $root );
						foreach ( (array) $paths as $path  )
							$this->path_trigger( $path );
					}

					return $return_revision;
				} else {
					foreach ( $roots as $root ) {
						if ( 'grouped' === $types['roots'] ) {
							$changed_paths = 'all';
						} else {
							$grep = preg_quote( $root, '#' );
							$changed_paths = preg_grep( "#^$grep#", $path_rels );
							if ( $changed_paths && isset($types['path']) && 'grouped' === $types['path'] )
								$changed_paths['all_in'] = true;
						}
						$this->root_trigger( $root, $changed_paths );
					}
				}
			}

		}

		if ( isset($types['paths']) && $types['paths'] ) {
			if ( 'grouped' === $types['paths'] ) // paths are pre-grouped
				$paths =& $path_rels;
			else // find the paths
				$paths = Automattic_Paths::group( $path_rels, $this->path_defs );

			foreach ( $paths as $path ) {
				if ( 'grouped' === $types['paths'] ) {
					$changed_paths = 'all';
				} else {
					$grep = preg_quote( $path, '#' );
					$changed_paths = preg_grep( "#^$grep#", $path_rels );
				}
				
				$this->path_trigger( $path, $changed_paths );
			}
		}

		return $return_revision;
	}

	function revision_trigger( $revision, $log ) {
		if ( function_exists( 'do_action' ) )
			do_action( 'automattic_svn_tracker_revision_trigger', $revision, $log );
	}
		
	function root_trigger( $root_rel, $changed_paths = 'all' ) {
		if ( function_exists( 'do_action' ) )
			do_action( 'automattic_svn_tracker_root_trigger', $root_rel, $changed_paths );
	}
		
	function path_trigger( $path_rel, $changed_paths = 'all' ) {
		if ( function_exists( 'do_action' ) )
			do_action( 'automattic_svn_tracker_path_trigger', $path_rel, $changed_paths );
	}

	function get_root( $path_rel ) {
		if ( !$roots = Automattic_Paths::group( $path_rel , $this->root_def ) )
			return false;
		return $roots[0];
	}

	function get_path( $path_rel ) {
		if ( !$paths = Automattic_Paths::group( $path_rel , $this->path_defs ) )
			return false;
		return $paths[0];
	}

	function get_all_roots( $via = 'ls' ) {
		$root_rels = false;

		switch ( $via ) :
		case 'access' :
			if ( !$this->admin )
				break;

			$this->admin->load_svn_access();
			if ( !$this->admin->svn_access )
				return false;

			$root_rels = array_keys($this->admin->svn_access);
			$root_rels = array_map( create_function( '$a', 'return ltrim($a, "/");'), $root_rels );
			foreach( array_keys( $root_rels, '' ) as $key )
				unset($root_rels[$key]);
			$root_rels = Automattic_Paths::group( $root_rels, $this->root_def );
			break;
		case 'local' :
			break;
		case 'ls' : // potentially expensive
		default :
			$root_rels = $this->_get_svn_paths( $this->root_def );
			break;
		endswitch;

		return $root_rels;
	}
		
	function path_rel( $string ) {
		$string = ltrim( $string );
		$string = ltrim( $string, '/' );
		return Automattic_Paths::trailing_slash_it( $string );
	}

	function get_all_paths_in( $root, $via = 'ls' ) {
		$root = $this->path_rel( $root );

		switch ( $via ) :
		case 'ls' : // potentially expensive
		default :
			$path_rels = array();
			foreach ( $this->path_defs as $def ) {
				$def_paths = $this->_get_svn_paths( $def, $root );
				$path_rels = array_merge( $path_rels, $def_paths );
			}
			break;
		endswitch;

		return $path_rels; //Automattic_Paths::group( $path_rels, $this->path_deps );
	}

	function _get_svn_paths( $def, $base = '' ) { // This function can be very expensive
		$depth = substr_count( $def, '*' ) - 1;

		$path_rels = array( $base );

		$i = 0;
		do {
			$at_depth_paths = array();
			foreach ( $path_rels as $base ) {
				if ( !$these_paths = $this->svn->ls( $this->svn_url . $base, 'type=dir' ) )
					continue;
				foreach ( $these_paths as $this_path )
					$at_depth_paths[] = $base . $this_path;
			}

			$path_rels = $at_depth_paths;

			$i++;
		} while ( $i <= $depth );

		$return = Automattic_Paths::group( $path_rels, $def );

		return $return;
	}

	function create_export( $path_rel, $args = null ) {
		// normalize
		$path_rel = $this->path_rel( $path_rel );

		$key = $path_rel . md5(serialize($args));

		if ( isset($this->export_cache[$key]) )
			return $this->export_cache[$key];

		if ( 50 < count($this->export_cache) )
			$this->flush_export_cache();

		if ( !$temp = Automattic_Paths::temp_dir( 'automattic_svn_' ) )
			return false;

		$this->export_cache[$key] = $temp;

		$args = wp_parse_args( $args, array( 'force' => true ) );

		if ( !$rev = Automattic_SVN::export( $this->svn_url . $path_rel, $temp, $args ) )
			return false;

		$this->error_log( __FUNCTION__, "[export] created: $path_rel = $temp" );

		return $temp;
	}

	function flush_export_cache() {
		foreach ( $this->export_cache as $cache_dir ) {
			Automattic_Paths::rm_temp_dir( $cache_dir );
			$this->error_log( __FUNCTION__, "[export] flushed: $cache_dir" );
		}

		$this->export_cache = array();
	}

	function zip_file_name( $path_rel ) {
		$path_parts = Automattic_Paths::group( $path_rel, $this->path_defs, 'parts' );
		$parts = array();
		foreach ( $path_parts[0] as $part ) { // normalize .version and version. names
			if ( '.' === substr( $part, 0, 1 ) )
				$part = "0$part";
			if ( '.' === substr( $part, -1 ) )
				$part = "$part0";
			$parts[] = $part;
		}
		$file_name = join( '.', $parts );
		$file_name = $this->sanitize_filename( $file_name );
		$this->error_log( __FUNCTION__, "[zip] file name: $file_name" );
		return $file_name;
	}

	function check_files_for_zip( $path_abs ) {
		return true;
	}

	function generate_zip( $directory ) {
		if ( !$this->download_path || !is_dir( $this->download_path ) )
			return false;

		// normalize
		$directory = $this->path_rel( $directory );

		$path_rel = $this->get_path( $directory );

		if ( !$slug = $this->get_root( $path_rel ) )
			return false;
		$slug = trim( $slug, '/' );

		if ( !$export = $this->create_export( $path_rel ) )
			return false;

		if ( !$zip_file = $this->zip_file_name( $path_rel, $directory ) )
			return false;

		if ( !file_exists( $export ) || !$this->check_files_for_zip( $export ) )
			return false;

		if ( !class_exists( 'Ziplib' ) )
			require( AUTOMATTIC_SVN_TRACKER__ZIPLIB_PATH );

		$zipper = new Ziplib;

		$zip_dir = $export;

		if ( $path_rel != $directory )
			$zip_dir .= substr( $directory, strlen($path_rel) );

		$files = Automattic_Paths::search( $zip_dir, '*' );

		foreach ( $files as $file ) {
			$relative_file = substr( $file, strlen($zip_dir) );
			$bits = file_get_contents( $file );
			$zipper->zl_add_file( $bits, "$slug/$relative_file", 'g5' );
		}

		$zip_file = $this->sanitize_filename( $zip_file );

		Automattic_Paths::mkdir( $this->download_path . "$slug/" );

		$fwrite = false;

		if ( $fd = fopen( $this->download_path . "$slug/$zip_file.zip", 'w' ) ) {
			$fwrite = fwrite( $fd, $zipper->zl_pack( "$zip_file packaged: " . date('r') ) );
			fclose( $fd );
		}

		if ( $fwrite )
			$this->error_log( __FUNCTION__, "[zip] created: $zip_file.zip" );
		else
			$this->error_log( __FUNCTION__, "[zip] FAILED: $zip_file.zip" );
        }

	function serve_zip_file( $file_name ) {
		$file_name = $this->sanitize_filename( $file_name );

		do {
			$topic = get_topic( preg_replace( '/\..*$/', '', $file_name ) );

			$abs_file = "{$this->download_path}$topic->topic_slug/$file_name";

			if ( $topic && file_exists( $abs_file ) )
				break;

			status_header( 404 );
			die('404 File not found ');
		} while(0);

		$this->zip_file_served( $topic->topic_id, $abs_file );

		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Cache-control: private');
		header('Content-Description: File Transfer');
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($abs_file) );

		readfile( $abs_file );
		exit;
	}

	function zip_file_served() {
		return true;
	}

	function get_last_time( $path_rel ) {
		$path_rel = $this->path_rel( $path_rel );
		return Automattic_SVN::info( $this->svn_url . $path_rel, 'date' );
	}

	function get_first_time( $path_rel ) {
		$path_rel = $this->path_rel( $path_rel );
		if ( !$log = $this->svn->log( $this->svn_url . $path_rel, array( 'limit' => 1, 'revision' => '1:HEAD' ) ) )
			return false;
		$first_log = array_pop( $log );
		return isset($first_log->date) ? $first_log->date : false;
	}

	function sanitize_filename( $file ) {
		$file = preg_replace( '/[^a-z0-9_.-]/i', '', $file );
		return preg_replace( '/[.]+/', '.', $file );
	}

	function error_log( $function, $message = null ) {
		if ( !isset($_SERVER['argc']) || !$_SERVER['argc'] )
			echo '<pre>';
		echo "$function";
		if ( !is_null( $message ) )
			echo " -> $message";
		echo "\n";
		if ( !isset($_SERVER['argc']) || !$_SERVER['argc'] )
			echo '</pre>';
	}
}

?>
