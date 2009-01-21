<?php bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; Members </h3>

<?php 
    $pagecount = ud_get_max_pages();
    $current = $_GET['page'];
    if (!isset($current)) {
        $current = 1;
    } else if ($current > $pagecount) {
        $current = $pagecount;
    }
    $users = ud_user_list($current); 
?>
<table id="forumlist">
<thead><tr><?php if (function_exists('bb_get_pm_link')) { echo '<th>&nbsp;</th>'; } ?><th>User</th><th>Website</th><th>Registered</th></tr></thead>
<tbody>
<?php foreach ( $users as $user ) { ?>
<tr<?php topic_class(); ?>>
<?php if (function_exists('bb_get_pm_link')) {
	echo '<td align="center">';
	ud_tiny_user_pm_link($user->ID);
	echo "</td>\n";
} ?>
<td><?php echo '<a href="'.get_user_profile_link($user->ID).'">'.(empty($user->display_name) ? $user->user_nicename : $user->display_name).'</a>'; ?></td>
<td><?php
if (isset($user->user_url) && strlen($user->user_url) > 7) {
	echo '<a href="' . $user->user_url . '" rel="nofollow">' . $user->user_url . '</a>'; 
} else {
	echo '&nbsp;';
} 
?></td>
<td><?php echo $user->user_registered; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<div id="pagination"><?php echo ud_format_pagination($current, $pagecount); ?></div>

<?php bb_get_footer();?>


