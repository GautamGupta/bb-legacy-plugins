<?php
if ( !defined( 'AUTOMATTIC_SVN__SVN_PATH' ) )
	define( 'AUTOMATTIC_SVN__SVN_PATH', trim(`which svn`) );

class Automattic_SVN {
	var $log_cache = array();
	var $ls_cache = array();
	var $requests = 0;

	function Automattic_SVN( $default_args = null ) {
		$defaults = array(
			'ignore-externals' => true,
			'non-interactive' => true,
			'no-auth-cache' => true,
			'username' => 'user',
			'password' => 'pass',
			'xml' => true
		);

		$this->defaults = wp_parse_args( $default_args, $defaults );
	}

	// Returns most recent exec output (array of lines)
	function _last_output( $_output = null ) {
		static $output;
		if ( !is_null($_output) )
			$output = $_output;
		return $output;
	}

	// Returns most recent exit code
	function _last_return( $_return = null ) {
		static $return;
		if ( !is_null($_return) )
			$return = $_return;
		return $return;
	}

	// Compiles exec args
	function _exec_args( $args, $possible_args ) {
		$keys = array_intersect( array_keys($args), $possible_args );

		$exec_args = '';

		foreach ( $keys as $key ) {
			if ( is_bool( $args[$key] ) ) {
				if ( !$args[$key] )
					continue;
				$value = '';
			} else {
				$value = escapeshellarg( (string) $args[$key] );
			}
			$exec_args .= " --$key $value ";
		}

		return trim( $exec_args );
	}

	// Execs svn command and returns true if exit code is 0
	function _return_return( $command, $paths, $args, $possible_args ) {
		$defaults = array(
			'ignore-externals' => true,
			'non-interactive' => true,
			'no-auth-cache' => true,
			'username' => 'user',
			'password' => 'pass',
			'xml' => true
		);

		$args = wp_parse_args( $args, isset($this->defaults) ? $this->defaults : $defaults );
		$exec_args = Automattic_SVN::_exec_args( $args, $possible_args );

		$paths = (array) $paths;

		$command = escapeshellarg( (string) $command );
		$paths = array_map( 'escapeshellarg', $paths );
		$path = join( ' ', $paths );

		$exec = AUTOMATTIC_SVN__SVN_PATH . " $command $path $exec_args";

		exec( $exec, $out, $return );
		$this->requests++;

		Automattic_SVN::_last_output( $out );
		Automattic_SVN::_last_return( $return );

		return 0 == $return;
	}

	// Execs svn command and returns output (array of lines)
	function _return_output( $command, $path, $args, $possible_args ) {
		Automattic_SVN::_return_return( $command, $path, $args, $possible_args );
		return Automattic_SVN::_last_output();
	}

	// Execs svn command and returns revision number (int)
	function _return_revision( $command, $path, $args, $possible_args ) {
		if ( !Automattic_SVN::_return_return( $command, $path, $args, $possible_args ) )
			return false;

		$out = Automattic_SVN::_last_output();
		$last_line = array_pop($out);
		preg_match( '/\d+/', $last_line, $matches );
		return (int) $matches[0];
	}


	/* The SVN Commands
	 * $path is a working copy path.
	 * $target is either a working copy path or a repo url
	 *
	 * ${command}_args are the arguments that that command can accept.
	 * Specify true/false for those orgs that do not take a value.
	 * Specify value for those args that take a value.
	 *
	 * Automattic_SVN:blame( $path, array( 'revision' => 5, 'verbose' => true ) );
	 */

	// Returns true on success
	function add( $path, $args = null ) {
		$add_args = array(
			'targets',
			'non-recursive',
			'quiet',
			'config-dir',
			'force',
			'no-ignore',
			'auto-props',
			'no-auto-props'
		);

		return Automattic_SVN::_return_return( 'add', $path, $args, $add_args );
	}

	// Returns blame output as array of lines
	function blame( $target, $args = null ) {
		$blame_args = array(
			'revision',
			'verbose',
			'incremental',
			'xml',
			'extensions',
			'force',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		$default_args = array( 'xml' => false );
		$args = wp_parse_args( $args, $default_args );

		return Automattic_SVN::_return_output( 'blame', $target, $args, $blame_args );
	}

	// Returns file as array of lines
	function cat( $target, $args = null ) {
		$cat_args = array(
			'revision',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		return Automattic_SVN::_return_output( 'cat', $target, $args, $cat_args );
	}

	// Returs revision # on success
	function checkout( $target, $absolute_path, $args = null ) {
		$checkout_args = array(
			'revision',
			'quiet',
			'non-recursive',
			'username',
			'password',
			'no-outh-cache',
			'non-interactive',
			'config-dir',
			'ignore-externals'
		);


		return Automattic_SVN::_return_revision( 'checkout', array($target, $absolute_path), $args, $checkout_args );
	}

	// Returns true on success
	function cleanup( $path, $args = null ) {
		$clean_up_args = array(
			'diff3-cmd',
			'config-dir'
		);

		return Automattic_SVN::_return_return( 'cleanup', $path, $args, $cleanup_args );
	}

	// Returns new revision number on success
	function commit( $path, $message, $args = null ) {
		$args = wp_parse_args( $args );
		$args['message'] = $message;

		$commit_args = array(
			'quiet',
			'non-recursive',
			'targets',
			'no-unlock',
			'message',
			'file',
			'force-log',
		//	'editor-cmd',
			'encoding',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		return Automattic_SVN::_return_revision( 'commit', $path, $args, $commit_args );
	}

	// Not implemented
	function copy() {
		$copy_args = array(
			'revision',
			'quiet',
			'message',
			'file',
			'force-log',
		//	'editor-cmd',
			'encoding',
			'username',
			'password',
			'no-auth-coche',
			'non-interactive',
			'config-dir'
		);

		return false;
	}

	// Returns true on success
	// Careful! if $target is a repo URL, it will actually commit the change.
	function delete( $target, $args = null ) {
		$delete_args = array(
			'force',
			'quiet',
			'targets',
			'message',
			'file',
			'force-log',
		//	'editor-cmd',
			'encoding',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		return Automattic_SVN::_return_return( 'delete', $target, $args, $delete_args );
	}

	function export( $target, $absolute_path, $args = null ) {
		$export_args = array(
			'revision',
			'quiet',
			'non-recursive',
			'force',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir',
			'native-eol',
			'ignore-externals'
		);

		return Automattic_SVN::_return_revision( 'export', array($target, $absolute_path), $args, $export_args );
	}

	function import( $path, $url, $args = null ) {
		$import_args = array(
			'quiet',
			'non-recursive',
			'auto-props',
			'no-auto-props',
			'message',
			'file',
			'force-log',
		//	'editor-cmd',
			'encoding',
			'no-ignore',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		return Automattic_SVN::_return_revision( 'import', array($path, $url), $args, $import_args );
	}

	// Returns info as string if key not specified.  Returns key value if key specified;
	function info( $target, $key = false, $args = null ) {
		$info_args = array(
			'revision',
			'recursive',
			'targets',
			'incremental',
			'xml',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		if ( $key ) {
			$args = wp_parse_args( $args );
			$args['xml'] = true;
		} else {
			$default_args = array( 'xml' => false );
		}

		if ( isset($args['xml']) && $args['xml'] )
			$args['verbose'] = false;

		$args = wp_parse_args( $args, $default_args );

		$out = trim( join( "\n", Automattic_SVN::_return_output( 'info', $target, $args, $info_args ) ) );

		if ( !$key )
			return trim($out);

		switch ( $key ) :
		case 'date' :
		case 'author' :
		case 'root' :
		case 'url' :
		case 'uuid' :
		case 'schedule' :
			preg_match( "#<$key>(.*?)</$key>#", $out, $matches );
			if ( 'date' == $key ) {
				if ( 0 >= $date = strtotime($matches[1]) )
					$date = parse_w3cdtf( $matches[1] );
				return $date;
			}
			return $matches[1];
			break;
		case 'current-revision' :
			$key = 'revision';
		case 'revision' :
		case 'kind' :
			preg_match( "#<entry\s+.*?$key=(['\"])(.*?)\\1#s", $out, $matches );
			return $matches[2];
			break;
		case 'last-changed-revision' :
			preg_match( "#<commit\s+.*?revision=(['\"])(.*?)\\1#s", $out, $matches );
			return $matches[2];
			break;
		endswitch;
		return false;
	}

	function _ls_files( $path ) {
		return '/' != substr( $path, -1 );
	}
	function _ls_dirs( $path ) {
		return '/' == substr( $path, -1 );
	}

	// Returns array of file names
	function ls( $target, $args = null ) {
		$default_args = array( 'xml' => false, 'type' => 'all' );
		$args = wp_parse_args( $args, $default_args );
		$not_cacheable = false !== $args['xml'] || array_diff( array_keys( $args ), array_keys( $default_args ) );

		$type = $args['type'];
		unset($args['type']);

		if ( !$not_cacheable && isset($this) && isset($this->ls_cache[$target]) ) {
			$return =& $this->ls_cache[$target];
		} else {
			$list_args = array(
				'revision',
				'verbose',
				'recursive',
				'incremental',
				'xml',
				'username',
				'password',
				'no-auth-cache',
				'non-interactive',
				'config-dir'
			);

			$return = Automattic_SVN::_return_output( 'list', $target, $args, $list_args );

			if ( !$not_cacheable && isset($this) ) {
				if ( 50 < count($this->ls_cache) )
					$this->flush_ls_cache();
				$this->ls_cache[$target] = $return;
			}
		}

		if ( 'file' === $type )
			return array_filter( $return, array( 'Automattic_SVN', '_ls_files' ) );
		elseif ( 'dir' === $type )
			return array_filter( $return, array( 'Automattic_SVN', '_ls_dirs' ) );
	
		return $return;
	}

	/* Returns array of objects keyed by revision number.  Each object has
	 * date: (int)
	 * author: (string)
	 * message: (string)
	 * paths: (array).  Keys are the path names, values are concatenation of all actions associated with that path in log (M, A, D, ...);
	 */
	function log( $target, $args = null ) {
		$args = wp_parse_args( $args );
		$args['xml'] = true;
		$args['verbose'] = true;

		$log_args = array(
			'revision',
			'quiet',
			'verbose',
			'targets',
			'stop-on-copy',
			'incremental',
			'xml',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir',
			'limit'
		);

		$out = join( "\n", Automattic_SVN::_return_output( 'log', $target, $args, $log_args ) );

		$entries = preg_split( '#</logentry>#', $out );

		$logs = array();
		foreach ( $entries as $entry ) {
			$log = array();
			if ( !preg_match( '#<date>(.*?)</date>#', $entry, $matches ) )
				continue;
			if ( 0 >= $log['date'] = strtotime($matches[1]) )
				$log['date'] = parse_w3cdtf( $matches[1] );

			preg_match( '#<author>(.*?)</author>#', $entry, $matches );
			$log['author'] = $matches[1];

			preg_match( '#<msg>(.*?)</msg>#s', $entry, $matches );
			$log['message'] = $matches[1];

			preg_match( '#<logentry\s+.*?revision=([\'"])(.*?)\1#s', $entry, $matches );
			$revision = (int) $matches[2];

			$log['paths'] = array();
			preg_match( '#<paths>(.*?)</paths>#s', $entry, $matches );
			$path_entries = preg_split( '#</path>#', $matches[1] );
			foreach ( $path_entries as $path_entry ) {
				if ( !preg_match( '#<path\s+.*action=([\'"])(.*?)\1.*?>(.*)$#s', $path_entry, $matches ) )
					continue;
				$path = trim( $matches[3] );
				$path = ltrim( $path, '/' );
				$action = $matches[2];
				if ( isset($log['paths'][$path]) )
					$log['paths'][$path] .= "-$action";
				else
					$log['paths'][$path] = $action;
			}

			$logs[$revision] = (object) $log;
			if ( isset($this) )
				$this->log_cache[$revision] = $log[$revision];
		}

		return $logs;
	}

	function mkdir( $url, $args = null ) {
		$mkdir_args = array(
			'quiet',
			'message',
			'file',
			'force-log',
		//	'editor-cmd',
			'encoding',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir'
		);

		return Automattic_SVN::_return_revision( 'mkdir', $url, $args, $mkdir_args );
	}

	// Returns revision # on success
	function update( $path, $args = null ) {
		$up_args = array(
			'revision',
			'non-recursive',
			'quiet',
			'diff3-cmd',
			'username',
			'password',
			'no-auth-cache',
			'non-interactive',
			'config-dir',
			'ignore-externals'
		);

		return Automattic_SVN::_return_revision( 'up', $path, $args, $up_args );
	}

	function flush_ls_cache( $path = null ) {
		if ( !isset($this) )
			return;
		if ( is_null($path) )
			$this->ls_cache = array();
		else
			unset($this->ls_cache[$path]);
	}

}

/* WP Conveniences */
if ( !function_exists( 'wp_parse_args' ) ) :
function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}
endif;

if ( !function_exists( 'wp_parse_str' ) ) :
function wp_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array ); // parse_str() adds slashes if magicquotes is on.  See: http://php.net/parse_str
	$array = apply_filters( 'wp_parse_str', $array );
}
endif;

if ( !function_exists( 'stripslashes_deep' ) ) :
function stripslashes_deep($value) {
	 $value = is_array($value) ?
		 array_map('stripslashes_deep', $value) :
		 stripslashes($value);

	 return $value;
}
endif;

if ( !function_exists( 'apply_filters' ) ) :
function apply_filters( $tag, $string ) {
	return $string;
}
endif;

if ( !function_exists('parse_w3cdtf') ) :

function parse_w3cdtf ( $date_str ) {

	# regex to match wc3dtf
	$pat = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";

	if ( preg_match( $pat, $date_str, $match ) ) {
		list( $year, $month, $day, $hours, $minutes, $seconds) =
			array( $match[1], $match[2], $match[3], $match[4], $match[5], $match[7]);

		# calc epoch for current date assuming GMT
		$epoch = gmmktime( $hours, $minutes, $seconds, $month, $day, $year);

		$offset = 0;
		if ( $match[11] == 'Z' ) {
			# zulu time, aka GMT
		}
		else {
			list( $tz_mod, $tz_hour, $tz_min ) =
				array( $match[8], $match[9], $match[10]);

			# zero out the variables
			if ( ! $tz_hour ) { $tz_hour = 0; }
			if ( ! $tz_min ) { $tz_min = 0; }

			$offset_secs = (($tz_hour*60)+$tz_min)*60;

			# is timezone ahead of GMT?  then subtract offset
			#
			if ( $tz_mod == '+' ) {
				$offset_secs = $offset_secs * -1;
			}

			$offset = $offset_secs;
		}
		$epoch = $epoch + $offset;
		return $epoch;
	}
	else {
		return -1;
	}
}
endif;

?>
