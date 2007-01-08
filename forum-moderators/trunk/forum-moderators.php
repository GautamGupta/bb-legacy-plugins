<?
/*
Plugin Name: Forums Moderators
Plugin URI: http://www.adityanaik.com/projects/plugins/bb-forum-moderators/
Description: Give forum specific moderator priviledges
Author: Aditya Naik
Version: 1.0
Author URI: http://www.adityanaik.com/

Install Instructions:
- If you don't have a /my-plugins/ directory in your bbpress installaltion, create it on the same level as config.php.
- Menu can be access from Admin > User > Forum Moderators

Version History:
1.0 	: Initial Release

Please note that the plugin will disable all priviledges for moderators and will need to be given 
specific priviledges to each forum from the admin menu.

*/
function forum_moderators_add_admin_page() {
	global $bb_submenu;
	$bb_submenu['users.php'][] = array(__('Forum Moderators'), 'use_keys', 'forum_moderators_admin_page');
}

function forum_moderators_admin_page() {
	$bb_user_search = new BB_Forum_Moderators_Search($_REQUEST['usersearch'], $_REQUEST['userspage']);
	$bb_user_search->display();
}

function forum_moderators_process_post() {
	if ($_POST['mod_user_id']) {
		foreach($_POST['mod_user_id'] as $mod) {
			$user_obj = new BB_User( $mod);
			$user_obj->set_role('moderator');
			bb_update_usermeta( $mod, 'forum_moderator', $_POST['mod_forums'][$mod] );
			//print_r($_POST['mod_forums'][$mod]);
		}
	}
}

add_action( 'bb_admin-header.php','forum_moderators_process_post');
add_action( 'bb_admin_menu_generator', 'forum_moderators_add_admin_page' );



add_filter('bb_user_has_cap','forum_moderators_process_capacities',10,2);

function forum_moderators_process_capacities($allcaps, $caps){
	global $bb_current_user, $forum,$topic_id;
	
	if (!$bb_current_user || in_array('keymaster',$bb_current_user->roles) || in_array('administrator',$bb_current_user->roles)) 
		return $allcaps;
	if (empty($forum)) {
		$topic = get_topic($topic_id);
		$forum = get_forum($topic->forum_id);
	}
	
	$forum_moderator = ((!empty($bb_current_user->data->forum_moderator)) ? $bb_current_user->data->forum_moderator : array());
	
	$filtering_caps = array(
		'manage_topics' ,	
		'edit_closed' ,		
		'edit_deleted' ,		
		'browse_deleted' ,	
		'edit_others_tags' ,	
		'edit_others_topics' ,	
		'manage_posts' ,		
		'ignore_edit_lock' ,	
		'edit_others_posts' 
	);

	if (!empty($forum) && !array_key_exists($forum->forum_id,$forum_moderator ) && array_intersect($filtering_caps,$caps)) {
		
		foreach($filtering_caps as $filtering_cap) {
			unset($allcaps[$filtering_cap]);
		}
	}
	
	return $allcaps;
}

class BB_Forum_Moderators_Search {
	var $results;
	var $search_term;
	var $page;
	var $raw_page;
	var $users_per_page = 50;
	var $first_user;
	var $last_user;
	var $query_limit;
	var $query_from_where;
	var $total_users_for_query = 0;
	var $search_errors;

	function BB_Forum_Moderators_Search ($search_term = '', $page = '') { // constructor
		$this->search_term = $search_term;
		$this->raw_page = ( '' == $page ) ? false : (int) $page;
		$this->page = (int) ( '' == $page ) ? 1 : $page;

		$this->prepare_query();
		$this->query();
		$this->prepare_vars_for_template_usage();
		$this->do_paging();
	}

	function prepare_query() {
		global $bbdb;
		$this->first_user = ($this->page - 1) * $this->users_per_page;
		$this->query_limit = 'LIMIT ' . $this->first_user . ',' . $this->users_per_page;
		if ( $this->search_term ) {
			$searches = array();
			$search_sql = 'AND (';
			foreach ( array('user_login', 'user_nicename', 'user_email', 'user_url', 'display_name') as $col )
				$searches[] = $col . " LIKE '%$this->search_term%'";
			$search_sql .= implode(' OR ', $searches);
			$search_sql .= ')';
		}
		$this->query_from_where = "FROM $bbdb->users WHERE 1=1 $search_sql";
	}

	function query() {
		global $bbdb;
		$this->results = $bbdb->get_col('SELECT ID ' . $this->query_from_where . $this->query_limit);
		
		$keymasters = get_ids_by_role( 'keymaster');
		$administrators = get_ids_by_role( 'administrator');
		$this->results = array_diff($this->results, $keymasters , $administrators );
		
		
		if ( $this->results )
			$this->total_users_for_query = $bbdb->get_var('SELECT COUNT(ID) ' . $this->query_from_where); // no limit
		else
			$this->search_errors = new WP_Error('no_matching_users_found', __('No matching users were found!'));
	}

	function prepare_vars_for_template_usage() {
		$this->search_term = stripslashes($this->search_term); // done with DB, from now on we want slashes gone
	}

	function do_paging() {
		global $bb_current_submenu;
		if ( $this->total_users_for_query > $this->users_per_page ) { // have to page the results
		$pagenow = bb_get_admin_tab_link($bb_current_submenu);
			$this->paging_text = paginate_links( array(
				'total' => ceil($this->total_users_for_query / $this->users_per_page),
				'current' => $this->page,
				'prev_text' => '&laquo; Previous Page',
				'next_text' => 'Next Page &raquo;',
				'base' => $pagenow . ( false === strpos($pagenow, '?') ? '?%_%' : '&amp;%_%' ),
				'format' => 'userspage=%#%',
				'add_args' => array( 'usersearch' => urlencode($this->search_term) )
			) );
		}
	}

	function get_results() {
		return (array) $this->results;
	}

	function page_links() {
		echo $this->paging_text;
	}

	function results_are_paged() {
		if ( $this->paging_text )
			return true;
		return false;
	}

	function is_search() {
		if ( $this->search_term )
			return true;
		return false;
	}

	function display( $show_search = true, $show_email = false ) {
		global $bb_roles;
		$r = '';
		// Make the user objects
		foreach ( $this->get_results() as $user_id ) {
			$tmp_user = new BB_User($user_id);
			$roles = $tmp_user->roles;
			$role = array_shift($roles);
			$roleclasses[$role][$tmp_user->data->user_login] = $tmp_user;
		}
		
		if ( isset($this->title) )
			$title = $this->title;
		elseif ( $this->is_search() )
			$title = sprintf(__('Users Matching "%s" by Role'), wp_specialchars( $this->search_term ));
		else
			$title = __('User List by Role');
		$r .= "<h2>$title</h2>\n";

		if ( $show_search ) {
			$r .= "<form action='' method='post' name='search' id='search'>\n\t<p>";
			$r .= "\t\t<input type='text' name='usersearch' id='usersearch' value='" . wp_specialchars( $this->search_term, 1) . "' />\n";
			$r .= "\t\t<input type='submit' value='" . __('Search for users &raquo;') . "' />\n\t</p>\n";
			$r .= "</form>\n\n";
		}

		if ( is_wp_error( $this->search_errors ) ) {
			$r .= "<div class='error'>\n";
			$r .= "\t<ul>\n";
			foreach ( $this->search_errors->get_error_messages() as $message )
				$r .= "\t\t<li>$message</li>\n";
			$r .= "\t</ul>\n</div>\n\n";
		}

		$forums = get_forums();
		if ( $this->get_results() ) {
			$colspan = 3;

			$r .= '<h3>' . sprintf(__('%1$s &#8211; %2$s of %3$s shown below'), $this->first_user + 1, min($this->first_user + $this->users_per_page, $this->total_users_for_query), $this->total_users_for_query) . "</h3>\n";

			if ( $this->results_are_paged() )
				$r .= "<div class='user-paging-text'>\n" . $this->paging_text . "</div>\n\n";

			$r .= "<form method='post'><table class='widefat'>\n";
			foreach($roleclasses as $role => $roleclass) {
				ksort($roleclass);
				$r .= "\t<tr>\n";
				if ( !empty($role) )
					$r .= "\t\t<th colspan='$colspan'><h3>{$bb_roles->role_names[$role]}</h3></th>\n";
				else
					$r .= "\t\t<th colspan='$colspan'><h3><em>" . __('Users with no role in these forums') . "</h3></th>\n";
				$r .= "\t</tr>\n";
				$r .= "\t<tr class='thead'>\n";
				$r .= "\t\t<th>" . __('Add/Update') . "</th>\n";
				$r .= "\t\t<th>" . __('Username') . "</th>\n";
				$r .= "\t\t<th>" . __('Actions') . "</th>\n";
				$r .= "\t</tr>\n\n";

				$r .= "<tbody id='role-$role'>\n";
				foreach ( (array) $roleclass as $user_object ) {
					$forum_moderator = ((!empty($user_object->data->forum_moderator)) ? $user_object->data->forum_moderator : array());
					
					$r .= "\t\n";
					$r .= "\t\t<tr id='user-$user_object->ID'>\n";
					$r .= "\t\t\t<td>";
					$r .= "<input type='checkbox' name='mod_user_id[$user_object->ID]' value='$user_object->ID' " . ( (!empty($forum_moderator)) ? 'checked' : '' ) . "/>";
					$r .= "</td>\n";
					$r .= "\t\t\t<td><a href='" . get_user_profile_link( $user_object->ID ) . "'>" . get_user_name( $user_object->ID ) . "</a></td>\n";
					
					$r .= "\t\t\t<td>\n";
					foreach($forums as $forum) {
						$r .= "\t\t\t\t<p><input type='checkbox' name='mod_forums[$user_object->ID][$forum->forum_id]' value='Y' " . ((array_key_exists($forum->forum_id, $forum_moderator)) ? 'checked' : '' ) . " /> $forum->forum_name</p>\n";
					}
					$r .= "\t\t\t\t<input type='submit' name='submit' value='Update' />\n";
					$r .= "\t\t\t</td>\n";
					$r .= "\t\t</tr>\n";
					$r .= "\t\n";
				}
				$r .= "</tbody>\n";
			}
			$r .= "</table></form>\n\n";

		 	if ( $this->results_are_paged() )
				$r .= "<div class='user-paging-text'>\n" . $this->paging_text . "</div>\n\n";
		}
		echo $r;
	}

}

function bb_forum_moderator_user_row( $user_id) {
	$user = bb_get_user( $user_id );
	$r  = "\t<tr id='user-$user_id'" . get_alt_class("user-$role") . ">\n";
	$r .= "\t\t<td>$user_id</td>\n";
	$r .= "\t\t<td><a href='" . get_user_profile_link( $user_id ) . "'>" . get_user_name( $user_id ) . "</a></td>\n";
	if ( $email )
		$r .= "\t\t<td><a href='mailto:$user->user_email'>$user->user_email</a></td>\n";
	$r .= "\t\t<td>$user->user_registered</td>\n";
	$r .= "\t\t<td><a href='" . get_profile_tab_link( $user_id, 'edit' ) . "'>Edit</a></td>\n\t</tr>";
	return $r;
}


?>