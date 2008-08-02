<?php bb_get_header(); ?>

<?php if ( isset_id() ) : ?><?php if ( page_exist() ) : // Do not delete this ?>

<div class="top_box">
	<h2><?php echo get_page_title(); ?></h2>
	<div class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php echo get_page_title(); ?></div>
</div>

<div class="post-content">
	<?php echo get_page_content(); ?>
</div>

<?php else: // If there is no page with id=X in database ?>

<div class="notice_box"><div class="notice_content">404 - I'm sorry, but this page does not exist.</div></div>

<?php endif; ?>
<?php else: // If page ID is not specified or it equals 0 ?>

<div class="notice_box"><div class="notice_content">Page is not specified.</div></div>

<?php endif; ?>

<?php bb_get_footer(); ?>