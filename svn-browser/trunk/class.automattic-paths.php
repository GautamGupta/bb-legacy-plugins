<?php

if ( !defined( 'AUTOMATTIC_PATHS__TEMP_DIR' ) )
	define( 'AUTOMATTIC_PATHS__TEMP_DIR', null );

if ( !defined( 'AUTOMATTIC_PATHS__FIND_PATH' ) )
	define( 'AUTOMATTIC_PATHS__FIND_PATH', trim(`which find`) );
if ( !defined( 'AUTOMATTIC_PATHS__GREP_PATH' ) )
	define( 'AUTOMATTIC_PATHS__GREP_PATH', trim(`which grep`) );

class Automattic_Paths {

	// Find files matching file_pattern and optionally having search_patter
	// 'find $root -type f -iname $file_pattern'
	// 'find $root -type f -iname $file_pattern | xargs grep -li $search_pattern'
	// Should work even for files with spaces in them
	// Sorted by path length, natural sort (finds files with least depth first)
	function search( $root, $file_pattern, $search_pattern = false, $all_non_dirs = false ) {
		if ( !$root = realpath( $root ) )
			return false;

		if ( !$file_pattern )
			return false;

		$root = escapeshellarg( $root );

		$file_pattern2 = '';
		if ( '*' == substr($file_pattern, 0, 1) ) {
			$file_pattern2 = escapeshellarg( ".$file_pattern" );
			$file_pattern  = '\\( -iname ' . escapeshellarg( $file_pattern ) . " -o -iname $file_pattern2 \\)";
		} else {
			$file_pattern = '-iname ' . escapeshellarg( $file_pattern );
		}

		$type = $all_non_dirs ? '-not -type d' : '-type f';

		$find_exec = AUTOMATTIC_PATHS__FIND_PATH . " $root $type $file_pattern";

		exec( $find_exec, $find_output, $find_return );

		if ( 0 != $find_return )
			return array();

		if ( $search_pattern ) {
			$search_pattern = escapeshellarg( $search_pattern );
			$grep_exec =  AUTOMATTIC_PATHS__GREP_PATH . " -li $search_pattern %s";

			$files = array();
			foreach ( $find_output as $file ) {
				$grep_file = exec( sprintf( $grep_exec, escapeshellarg( $file ) ), $grep_output, $grep_return );
				if ( $grep_file && 0 == $grep_return )
					$files[] = $grep_file;
			}

			usort( $files, array( __CLASS__, '_sort' ) );
			return $files;
		}

		usort( $find_output, array( __CLASS__, '_sort' ) );
		return $find_output;
	}

	// Finds all dirs: find $root -type d -not \( -name ".*" -prune \) -maxdepth $depth
	// Sorted by path length, natural sort (finds files with least depth first)
	function dirs( $root, $depth = 1, $no_dots = false ) {
		if ( !$root = realpath($root) )
			return false;

		$root = escapeshellarg( $root );
		$depth = (int) $depth;

		$find_exec = AUTOMATTIC_PATHS__FIND_PATH . " $root -type d -not -name '.'";
		if ( $no_dots )
			$find_exec .= " -not \( -name '.*' -prune \)";

		if ( $depth > 0 ) 
			$find_exec .= " -maxdepth $depth";

		exec( $find_exec, $find_output, $find_return );

		if ( 0 != $find_return )
			return false;

		unset($find_output[0]); // Get rid of root dir

		usort( $find_output, array( __CLASS__, '_sort' ) );

		return $find_output;
	}

	// Sort by path length
	function _sort( $a, $b ) {
		$slash_a = substr_count( $a, '/' );
		$slash_b = substr_count( $b, '/' );

		if ( $slash_a == $slash_b )
			return strnatcmp( $a, $b );

		return ( $slash_a < $slash_b ) ? -1 : 1;
	}


	// Takes an array of paths ( bob/one/two/, fred/hey/, bob/blue/ )
	// Groups them by cutting them off according to glob like rules in roots ( */ )
	// Returns grouped paths( bob/, fred/ : bob/one/two/ and bob/blue both get cut to bob/ )
	//
	// $return == 'parts' means returned paths will be arrays consisting of the * matches
	function group( $paths, $roots = '/', $return = 'paths' ) {
		$roots = array_map( array( __CLASS__, 'trailing_slash_it' ), (array) $roots );
		if ( 'parts' == $return )
			$roots = array_map( array( __CLASS__, '_regexify' ), $roots, $roots ); // lame hack
		else
			$roots = array_map( array( __CLASS__, '_regexify' ), $roots );
		$paths = array_map( array( __CLASS__, 'trailing_slash_it' ), (array) $paths );

		$out = array();
		if ( 'parts' == $return ) {
			foreach ( $paths as $path ) {
				foreach ( $roots as $root ) {
					if ( !preg_match( "#^$root#", $path, $matches ) )
						continue;
					unset( $matches[0] );
					$out[] = array_values($matches);
					break;
				}
			}
		} else {
			$roots_s = join( '|', $roots );
			foreach ( $paths as $path ) {
				if ( !preg_match( "#^($roots_s)(.*?)?\$#", $path, $matches ) )
					continue;
				$out[$matches[1]] = true;
			}
			$out = array_keys($out);
			$out = array_map( array( __CLASS__, 'trailing_slash_it' ), $out );
			usort( $out, array( __CLASS__, '_sort' ) );
		}

		return $out;
	}

	function trailing_slash_it( $string ) {
		$string = rtrim( $string );
		$string = rtrim( $string, '/' );
		return "$string/";
	}

	function _regexify( $string, $group = false ) {
		$string = preg_quote( $string, '#' );
		return str_replace( '\*', $group ? '([^/]*)' : '[^/]*', $string );
	}

	// Not recursive
	function mkdir( $dir ) {
		if ( is_dir( $dir ) )
			return true;
		if ( file_exists( $dir ) )
			return false;

		if ( '/' != substr( $dir, 0, 1 ) )
			return false;

		$parent = dirname( $dir );
		if ( $dir == $parent )
			return false;

		$mode = fileperms( $parent );
		$old_umask = umask(0);
		$mkdir = mkdir( $dir, $mode );
		umask( $old_umask );
		return $mkdir;
	}

	// Creates a temporary directory.  You are responsible for removing it later with rm_temp_dir()
	function temp_dir( $prefix ) {
		$temp = tempnam( AUTOMATTIC_PATHS__TEMP_DIR, $prefix );
		unlink( $temp );
		if ( Automattic_Paths::mkdir( $temp ) )
			return "$temp/";
		return false;
	}

	// Removes a temporary directory created with temp_dir()
	function rm_temp_dir( $dir ) {
		static $temp_dir = false;
		if ( !$temp_dir ) { // Find where temp_dir() crates the directories
			$temp = tempnam( AUTOMATTIC_PATHS__TEMP_DIR, 'temp' );
			$temp_dir = dirname( $temp ) . '/';
			unlink( $temp );
		}

		// Can only rm dirs in temp
		if ( 0 !== strpos( $dir, $temp_dir ) || $dir == $temp_dir )
			return false;

		// Delete files
		foreach ( Automattic_Paths::search( $dir, '*', false, true ) as $file )
			unlink($file);

		$subs = Automattic_Paths::dirs( $dir, -1 );
		$subs = array_reverse( $subs );

		// Delete subs
		foreach ( $subs as $sub )
			rmdir( $sub );

		// Delete dir
		return rmdir($dir);
	}
}

?>
