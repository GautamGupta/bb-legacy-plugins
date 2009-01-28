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
    $users = ud_user_list($current, 'user_registered DESC'); 
    
    $columnNames = ud_get_column_names();    
    $columnHeaderRenderers = ud_get_column_header_renderers();
    $columnRenderers = ud_get_column_renderers();
	$displayColumns = ud_sanity_check_columns($columnNames, $columnHeaderRenderers, $columnRenderers);
?>
<table id="forumlist">
<thead><tr><?php
foreach( $displayColumns as $columnName ) {
	echo '<th>';
	echo call_user_func($columnHeaderRenderers[$columnName], $columnName);
	echo '</th>';
}
?></th></tr></thead>
<tbody>
<?php foreach ( $users as $user ) { ?>
<tr<?php topic_class(); ?>><?php
foreach( $displayColumns as $columnName ) {
	echo '<td>';
	echo call_user_func($columnRenderers[$columnName], $columnName, $user);
	echo '</td>';
}
?></tr>
<?php } ?>
</tbody>
</table>
<div id="pagination"><?php echo ud_format_pagination($current, $pagecount); ?></div>

<?php bb_get_footer();?>


