<?php bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <?php _e('Private Messages') ?></h3>

<?php if ( $pms || $go ) : ?>

<div id="mlist">

<h2><?php _e('Private Messages'); ?></h2>
<form method="post" action="">
<table id="forumlist">
<tr>
	<th><?php _e('Subject'); ?> &#8212; <?php new_pm(); ?></th>
	<th><?php _e('From'); ?></th>
	<th><?php _e('Date'); ?></th>
	<th><?php _e('Delete'); ?></th>
</tr>

<?php if ( $pms ) : foreach ( $pms as $pm ) : ?>
<tr<?php topic_class(); ?>>
	<td><?php if($pm->seen == 0) { echo "<img src=\"".bb_get_active_theme_uri()."newmail.png\" align=\"top\" />"; } ?><?php echo " <a href=\"".bb_get_pm_link('?id='.$pm->pm_id)."\">".$pm->pmtitle."</a>"; ?></td>
	<td><center><?php echo "<a href=\"".get_user_profile_link($pm->id_sender)."\">".get_user_name($pm->id_sender)."</a>"; ?></center></td>
	<td><center><?php echo get_pm_time($pm->created_on); ?></center></td>
	<td><center><label><input type="checkbox" name="todel[]" value="<?php echo $pm->pm_id;?>"/></label></center></td>
</tr>
<?php endforeach; endif; ?>
</table>

<p class="submit">
  <input type="submit" name="Submit" value="Delete Message(s) &raquo;" />
</p>
</form>

<h2><?php _e('Sent Messages'); ?></h2>
<form method="post" action="">
<table id="forumlist">
<tr>
	<th><?php _e('Subject'); ?></th>
	<th><?php _e('To'); ?></th>
	<th><?php _e('Date'); ?></th>
	<th><?php _e('Delete'); ?></th>
</tr>

<?php if ( $sentpms ) : foreach ( $sentpms as $sentpm ) : ?>
<tr<?php topic_class(); ?>>
	<td><?php if($sentpm->seen == 0) { echo "<img src=\"".bb_get_active_theme_uri()."newmail.png\" align=\"top\" />"; } ?><?php echo " <a href=\"".bb_get_pm_link('?id='.$sentpm->pm_id)."\">".$sentpm->pmtitle."</a>"; ?></td>
	<td><center><?php echo "<a href=\"".get_user_profile_link($sentpm->id_receiver)."\">".get_user_name($sentpm->id_receiver)."</a>"; ?></center></td>
	<td><center><?php echo get_pm_time($sentpm->created_on); ?></center></td>
	<td><center><?php if( is_deletable($sentpm) ) { ?><label><input type="checkbox" name="todel[]" value="<?php echo $sentpm->pm_id;?>"/></label><?php } else { echo "n/a"; } ?></center></td>
</tr>
<?php endforeach; endif; ?>
</table>

<p class="submit">
  <input type="submit" name="Submit" value="Delete Message(s) &raquo;" />
</p>
</form>

<?php else : ?>

<?php pm_post_form(); endif; ?>

</div>

<?php bb_get_footer();?>


