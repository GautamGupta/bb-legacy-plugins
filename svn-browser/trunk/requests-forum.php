<?php

global $svn_tracker;

bb_get_header();

?>

<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a><?php bb_forum_bread_crumb(); ?></h3>

<?php $svn_tracker->admin->requests_page(); ?>

<?php post_form( 'Add Your Plugin' ); ?>

<?php bb_get_footer(); ?>
