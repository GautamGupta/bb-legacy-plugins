<?php
/*
Plugin Name: Super Search
Plugin URI:  http://bbpress.org/plugins/topic/super-search
Description:  radically improves the search in bbPress with many advanced options
Version: 0.0.2
Author: _ck_
Author URI: http://bbshowcase.org
Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/ 

add_action( 'do_search', 'super_search_init');
if (isset($_GET['search'] ) || isset($_GET['q'] ) || isset($_GET['SuperSearchUsers'])) {add_action('bb_init','super_search_init',200);}
function super_search_init() {require('super-search-init.php');}	// only loads code if search requested
?>