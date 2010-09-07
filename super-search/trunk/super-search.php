<?php
/*
Plugin Name: Super Search
Plugin URI:  http://bbpress.org/plugins/topic/super-search
Description:  radically improves the search in bbPress with many advanced options
Version: 0.0.3
Author: _ck_
Author URI: http://bbshowcase.org
Donate: http://bbshowcase.org/donate/
*/ 

add_action( 'do_search', 'super_search_init',256);
if (isset($_GET['search'] ) || isset($_GET['q'] ) || isset($_GET['SuperSearchUsers'])) {add_action('bb_init','super_search_init',256);}
function super_search_init() {require('super-search-init.php');}	// only loads code if search requested
?>