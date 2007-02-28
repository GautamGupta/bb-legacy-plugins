<?php
/*
written by Rimian Perkins 
http://www.freelancewebdeveloper.net.au
This plugin comes without any warrranty.
You may alter this code and/or redistribute it if you wish under the terms of the GPL License
Enjoy!
*/


function ye_olde_counter( ) {
	global $bbdb, $bb_table_prefix;

	$bbdb->query("UPDATE ".$bb_table_prefix."counter SET hits = hits + 1");

	$counter = $bbdb->get_row("SELECT hits FROM ".$bb_table_prefix."counter LIMIT 1");

	echo "Ye olde website counter: <span class=\"counter_number\">" . $counter->hits . "</span>";
}

?>
