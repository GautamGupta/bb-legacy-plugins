		<div class="threadauthor">
			 <?php bb_myavatars(get_post_author_id()); ?><br/>
			  <strong><?php post_author_link(); ?></strong><br />			 
			  <small><?php post_author_title(); ?></small>
		</div>
		
		<div class="threadpost">
			<div class="post"><?php post_text(); ?></div>
			<div class="poststuff"><?php printf( __('Posted %s ago'), bb_get_post_time() ); ?> <a href="<?php post_anchor_link(); ?>">#</a> <?php post_ip_link(); ?> <?php post_edit_link(); ?> <?php post_delete_link(); ?> <?php pm_user_link(get_post_author_id()); ?></div>
		</div>
 		
