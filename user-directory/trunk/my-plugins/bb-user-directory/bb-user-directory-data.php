<?php
/*
	Everything data access, filtering and pagination
*/

function ud_format_pagination($current, $pagecount) {
    global $SELF;
    $pagination = '';
    
    if ($current > 1) {
        $pagination = '<a href="'.$SELF.'?page='.($current-1).'">&laquo;&nbsp;Prev</a>&nbsp;|&nbsp;';
    } else {
        $pagination = '&laquo;&nbsp;Prev&nbsp;|&nbsp;';
    }
    
    $pagination .= 'Page '.$current.' of '.$pagecount;
    
    if ($current < $pagecount) {
        $pagination .= '&nbsp;|&nbsp;<a href="'.$SELF.'?page='.($current+1).'">Next&nbsp;&raquo;</a>';
    } else {
        $pagination .= '&nbsp;|&nbsp;Next&nbsp;&raquo;';
    }
    
    return $pagination;
}

function ud_get_max_pages() {
	global $bbdb;
	
	$usersPerPage = 20;
	$usercount = $bbdb->get_var('SELECT count(*) FROM '.
		ud_get_user_table_name().' WHERE user_status = 0');

	return ceil($usercount / $usersPerPage);
}

function ud_user_list($current = 1, $orderBy = 'user_login') {
	global $bbdb, $dataFilters;
	
	$usersPerPage = 20;
	$users = $bbdb->get_results(
	  'SELECT * FROM '.
	  ud_get_user_table_name().
	  ' WHERE user_status = 0 ORDER BY '.$orderBy.
	  ' limit '.($usersPerPage * ($current - 1)).','.$usersPerPage);

	if (isset($dataFilters)) {
		foreach(array_keys($dataFilters) as $key) {
			$filter = $dataFilters[$key];
			if (function_exists($filter)) {
				$users = call_user_func($filter, $users);
			}
		}
	}

	return $users;
}

function ud_get_user_table_name() {
	global $bbdb, $bb;
	$table = '';
	if ( $bb->wp_table_prefix ) {
		$table = $bb->wp_table_prefix."users";
	} else {
		$table = $bbdb->users;
	}
	return $table;
}

function ud_register_data_filter($filterName, $filter) {
	global $dataFilters;
	$dataFilters[$filterName] = $filter;
}

function ud_get_data_filters() {
	global $dataFilters;
	return $dataFilters;
}

?>