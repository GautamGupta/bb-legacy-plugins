<?php

function bbpm_mark_all_read() {
	global $bbdb;

	$threads = $bbdb->get_results( 'SELECT `object_id`,`meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type`=\'bbpm_thread\' AND `meta_key`=\'to\'' );
	$userthreads = array();
	$threadids = array();

	foreach ( $threads as $thread ) {
		$userthreads[$thread_object_id] = explode( ',', trim( $thread->meta_value, ',' ) );
		$threadids[] = $thread->object_id;
	}

	$threads = $bbdb->get_results( 'SELECT `object_id`,`meta_value` FROM `' . $bbdb->meta . '` WHERE `object_type`=\'bbpm_thread\' AND `meta_key`=\'last_message\'' );

	print_r( $threads );

	foreach ( $threads as $thread ) {
		foreach ( $userthreads[$thread->object_id] as $user ) {
			bb_update_usermeta( $user, 'bbpm_last_read_' . $thread->object_id, $thread->meta_value );
		}
	}
}
add_action( 'bb_init', 'bbpm_mark_all_read' );

?>