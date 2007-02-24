<?php bb_get_header(); ?>
<?php if ($bb_current_user->ID == $pmmessage->id_sender || $bb_current_user->ID == $pmmessage->id_receiver) : ?>
<h3 class="bbcrumb"><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <a href="<?php pm_mess_link(); ?>"><?php _e('Private Messages'); ?></a> &raquo; <?php echo $pmmessage->pmtitle;?></h3>

<ol id="thread">

<?php $thisuser = bb_get_user($pmmessage->id_sender); $thatuser = bb_get_user($pmmessage->id_receiver); ?>
<li><div class="threadauthor">
	 <?php bb_myavatars($pmmessage->id_sender); ?><br/>
	<div class="postauthor"><?php echo "<a href=\"".get_user_link($pmmessage->id_sender)."\">".bb_display_name($thisuser->user_login,$pmmessage->id_sender)."</a>"; ?></div>
	<div class="postauthortype"><?php echo "<a href=\"".get_user_profile_link($pmmessage->id_sender)."\">".get_user_type($pmmessage->id_sender)."</a>"; ?></div>
	<!-- <div class="postcount"><?php // echo "Post Count: ".get_post_count($pmmessage->id_sender); ?></div> -->
	<!-- <?php // echo '<img src="' . get_avatar_loc( $pmmessage->id_sender ) . '">'; ?> -->
</div>

<div class="threadpost">
	<div class="post"><?php echo pm_text($pmmessage); ?></div>
	<div class="poststuff"><?php _e('Sent:'); ?> <?php echo get_pm_time( $pmmessage->created_on ); ?> <?php _e('To '); ?> <?php echo $thatuser->user_login; ?></div>
</div></li>

<li>
<?php if ($bb_current_user->ID != $pmmessage->id_sender) { pm_reply_form("Reply", $pmmessage); } ?>
</li>
</ol>
<?php else : echo "You can't look at this message!  It doesn't belong to you!"; endif; ?>

<?php bb_get_footer(); ?>