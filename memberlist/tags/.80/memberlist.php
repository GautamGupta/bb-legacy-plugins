<?php bb_get_header(); ?>
<!-- Memberlist plugin can be downloaded at http://faq.rayd.org/memberlist/ -->
<h3 class="bbcrumb"><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <?php _e('Member List') ?></h3>

<div id="mlist">

<h2><?php _e('Member List'); ?> (<?php echo bb_mlist_count_users(); ?> users)</h2>
<form method="get" action=""><select name="usercount" type="submit" onchange='this.form.submit()'><option value="10" <?php mlist_is_selected($usercount, 10); ?>>10 users per page</option><option value="25" <?php mlist_is_selected($usercount, 25); ?>>25 users per page</option><option value="50" <?php mlist_is_selected($usercount, 50); ?>>50 users per page</option><option value="100" <?php mlist_is_selected($usercount, 100); ?>>100 users per page</option></select></form><br>
Page(s): <?php get_mlist_pages($currentpage,$usercount); ?>
<form method="post" action="">
<table id="forumlist">
	<tr>
		<th><a href="<?php bb_mlist_get_name_order_link(); ?>"><?php _e('User Name'); ?></a></th>
		<th><?php _e('Home Page'); ?></th>
		<th><a href="<?php bb_mlist_get_date_order_link(); ?>"><?php _e('Join Date'); ?></a></th>
		<!-- <th><a href="<?php //bb_mlist_get_postcount_order_link(); ?>"><?php //_e('Posts'); ?></a></th> -->
		<?php if (bb_current_user_can('edit_users')): ?>
			<th><?php _e('Delete User'); ?></th>
		<?php endif; ?>
	</tr>
	
	<?php foreach ( $memberresult as $member ): ?>
	<tr<?php topic_class(); ?>>
		<td><center><?php echo "<a href=\"".get_user_profile_link($member->ID)."\">".$member->user_login."</a>"; ?><br><?php echo get_user_type($member->ID); ?></center></td>
		<td><?php echo "<a href=\"".$member->user_url."\">".$member->user_url."</a>"; ?></td>
		<td><center><?php echo get_mlist_time($member->user_registered); ?></center></td>
		<!-- <td><center><?php //echo get_post_count($member->ID); ?></center></td> -->
		<?php if (bb_current_user_can('edit_users')) : ?>
			<td><center><label><input type="checkbox" name="todel[]" value="<?php echo $member->ID;?>"/> Delete?</label></center></td>
		<?php endif; ?>
	</tr>
	<?php endforeach; ?>
	
</table>
<?php if (bb_current_user_can('edit_users')) : ?>
	<p class="submit">
	  <input type="submit" name="Submit" value="Delete User(s) &raquo;" />
	</p>
<?php endif; ?>
</form>

</div>
<?php bb_get_footer(); ?>