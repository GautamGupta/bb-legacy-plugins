<?php

class Automattic_SVN_Admin {
	var $tracker;

	function Automattic_SVN_Admin( &$tracker ) {
		$this->__construct( $tracker);
		register_shutdown_function( array(&$this, '__destruct') );
	}

	function __construct( &$tracker ) {
		$this->tracker =& $tracker;

		if ( $this->tracker->db ) {
			array_push( $this->tracker->db->tables, 'svn_access' );
			$this->tracker->db->svn_access = $this->tracker->db->prefix . 'svn_access';
		}
	}

	function __destruct() {
	}

	function create_tables() {
		if ( !$this->tracker->db || !defined( 'AUTOMATTIC_SVN_TRACKER__CREATE_TABLES' ) )
			return false;

		if ( $this->tracker->db->has_cap( 'collation', $this->tracker->db->forums ) ) {
			if ( ! empty($this->tracker->db->charset) )
				$charset_collate = "DEFAULT CHARACTER SET {$this->tracker->db->charset}";
			if ( ! empty($this->tracker->db->collate) )
				$charset_collate .= " COLLATE {$this->tracker->db->collate}";
		}
		
		$bb_queries = "
CREATE TABLE {$this->tracker->db->svn_access} (
  path varchar(255)  NOT NULL default '',
  user varchar(200) NOT NULL default '',
  access tinytext NOT NULL default '',
  UNIQUE KEY path_user (path,user(20)),
  KEY user (user,path(50))
) $charset_collate;
";
		require_once( BBPATH . 'bb-admin/upgrade-functions.php' );
		return bb_dbDelta( trim($bb_queries) );
	}

	/* SVN Access */
	// Load from db
	function load_svn_access( $path = false ) {
		if ( !$this->tracker->db )
			return false;

		if ( false === $path )
			return $this->_load_svn_access();

		$path = trim( (string) $path );
		$path = trim( $path, '/' );
		$path = "/$path/dirname";
		$_path = $path;

		do {
			$path = dirname( $path );
			$this->_load_svn_access( $path );
		} while( '/' != $path );

		if ( $_path )
			return $this->svn_access[$_path];
		return true;
	}

	function _load_svn_access( $path = false ) {
		if ( false === $path ) {
			$svn_access = (array) $this->tracker->db->get_results( "SELECT * FROM {$this->tracker->db->svn_access}" );
		} else {
			if ( isset($this->svn_access[$path]) )
				return;
			$svn_access = (array) $this->tracker->db->get_results( $this->tracker->db->prepare( "SELECT * FROM {$this->tracker->db->svn_access} WHERE `path` = %s", $path ) );
		}

		if ( empty($this->svn_access) )
			$this->svn_access = array();

		foreach ( $svn_access as $svn_access ) {
			if ( !isset($this->svn_access[$svn_access->path]) )
				$this->svn_access[$svn_access->path] = array();
			$this->svn_access[$svn_access->path][$svn_access->user] = $svn_access->access;
		}
		if ( $path && !isset($this->svn_access[$path]) )
			$this->svn_access[$path] = array();
	}

	// Save to db
	function save_svn_access( $purge = false ) {
		if ( !$this->tracker->db )
			return false;
		if ( $purge )
			$this->tracker->db->query( "TRUNCATE {$this->tracker->db->svn_access}" );

		foreach ( $this->svn_access as $path => $users )
			foreach ( $users as $user => $access )
				$this->add_svn_access( $user, $path, $access, false );

		return true;
	}


	// Reads svn access from file
	// Understands [/path], not [repo:/path]
	function parse_svn_access( $file ) {
		if ( file_exists( $file ) )
			$lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		elseif ( is_string($file) )
			$lines = preg_split( '#[\r\n]+#', $file, -1, PREG_SPLIT_NO_EMPTY );
		elseif ( is_array($file) )
			$lines =& $file;
		else
			return false;

		$this->svn_access = array();

		$current_path = '/';
		foreach ( $lines as $line ) {
			if ( !$line = trim($line) )
				continue;

			if ( preg_match( '|\[/(.*?)\]|', $line, $matches ) ) {
				$current_path = trim($matches[1]);
				$current_path = trim($current_path, '/');
				$current_path = "/$current_path";
				continue;
			}

			if ( !preg_match( '|([^=\s]+)\s*=\s*(rw?)?|i', $line, $matches ) )
				continue;

			$user = $matches[1];
			$access = isset($matches[2]) && $matches[2] ? $matches[2] : '';

			if ( !isset($this->svn_access[$current_path]) )
				$this->svn_access[$current_path] = array();
			$this->svn_access[$current_path][$user] = $access;
		}
                return $this->svn_access;
	}

	// Writes svn access to file
	function write_svn_access( $file_name ) {
		$this->load_svn_access();

		if ( empty($this->svn_access) )
			return false;

		$svn_access = '';
		foreach ( $this->svn_access as $slug => $users ) {
			$slug = ltrim( $slug, '/' );
			$svn_access .= "\n[/$slug]\n";

			foreach ( $users as $user => $access )
				$svn_access .= "$user = $access\n";
		}

		if ( !$svn_access = trim($svn_access) )
			return false;

		if ( !$f = @fopen( $file_name, 'w' ) )
			return false;

		$r = false;
		flock($f, LOCK_EX);
		if ( false !== fwrite($f, $svn_access) )
			$r = true;
		fclose($f);
		return true;
	}

	// Adds one line to
	function add_svn_access( $user, $path, $access, $_cache = true ) {
		$path = trim( (string) $path );
		$path = trim( $path, '/' );
		$path = "/$path";
		$user = trim( (string) $user );
		$access = (string) $access;
		if ( !in_array( $access, array( 'r', 'w', 'rw', '' ) ) )
			return false;

		if ( $this->tracker->db ) {
			$sql = $this->tracker->db->prepare(
				"INSERT INTO {$this->tracker->db->svn_access} ( `path`, `user`, `access` ) VALUES ( %s, %s, %s )
				ON DUPLICATE KEY UPDATE `access` = '%3\$s'",
				$path, $user, $access
			);

			if ( !$r = $this->tracker->db->query( $sql ) )
				return false;
		}

		if ( $_cache ) {
			if ( empty($this->svn_access) )
				$this->svn_access = array();
			if ( empty($this->svn_access[$path]) )
				$this->svn_access[$path] = array();

			$this->svn_access[$path][$user] = $access;
		}

		return $access;
	}

	// Deletes one line
	function del_svn_access( $user, $path ) {
		$path = trim( (string) $path );
		$path = trim( $path, '/' );
		$path = "/$path";
		$user = trim( (string) $user );

		unset($this->svn_access[$path][$user]);

		return $this->tracker->db ?
			$this->tracker->db->query( $this->tracker->db->prepare( "DELETE FROM {$this->tracker->db->svn_access} WHERE `path` = %s and `user` = %s", $path, $user ) ) :
			true;
	}

	// Assumes a user always has more specific auth than * does
	function user_can_access_path( $user, $path, $_access = 'r' ) {
		$user = (string) $user;
		$path = trim( (string) $path );
		$path = trim( $path, '/' );
		$path = "/$path";
		$_access = (string) $_access;
		$user_access = '';

		$this->load_svn_access( $path ); // Can be combined with the following loop by using _load_svn_access instead

		do {
			if ( isset($this->svn_access[$path][$user]) ) {
				$user_access = $this->svn_access[$path][$user];
				break;
			}

			if ( isset($this->svn_access[$path]['*']) ) {
				$user_access = $this->svn_access[$path]['*'];
				break;
			}
			$_path = dirname($path);
		} while( ($_path != $path) && ($path = $_path) );

		if ( false !== strpos( $user_access, $_access ) )
			return true;
		return false;
	}

	function user_can_write_path( $user, $path ) {
		return $this->user_can_access_path( $user, $path, 'w' );
	}
}

?>
