<?php
/*
	Everything admin related
*/

function ud_add_admin_page() {
	if (function_exists('bb_admin_add_submenu')) {
		bb_admin_add_submenu(__('User Directory'), 'use_keys', 'ud_admin_page');
	}
}

function ud_admin_page() {
?>
	<h3>User Directory Admin</h3>
	<p>Currently registered columns:</p>
<table class="widefat"><thead>
<tr>
<th>Column</th>
<th>Header Handler</th>
<th>Data Handler</th>
</tr>
</thead><tbody>
<?php
    $columnNames = ud_get_column_names();    
    $columnHeaderRenderers = ud_get_column_header_renderers();
    $columnRenderers = ud_get_column_renderers();
    $i = 0;
	foreach( $columnNames as $columnName ) {
		$headerRenderer = $columnHeaderRenderers[$columnName];
		$validHeader = function_exists($headerRenderer);
		$columnRenderer = $columnRenderers[$columnName];
		$validColumn = function_exists($columnRenderer);
?><tr <?php echo ($i % 2 == 0) ? '' : 'class="alt"' ?>>
<td><?php echo $columnName; ?></td>
<td><?php echo ($validHeader ? '' : '<span style="color: red;"><strike>').$headerRenderer.($validHeader ? '' : '</strike><br/>function not found</span>'); ?></td>
<td><?php echo ($validColumn ? '' : '<span style="color: red;"><strike>').$columnRenderer.($validColumn ? '' : '</strike><br/>function not found</span>'); ?></td>
</tr>
<?php
	$i++;
	}	
?>
</tbody>
</table>

<?php 
	$data = ud_get_data_filters();
	if (!isset($data) || count($data) == 0) { ?>
		<p>No data filters currently registered.</p>
<?php	
	} else { ?>
	<p>Currently registered data filters:</p>
<table class="widefat"><thead>
<tr>
<th>Filtername</th>
<th>Data Handler</th>
</tr>
</thead><tbody>
<?php
    $i = 0;
	foreach( array_keys($data) as $key ) {
		$filter = $data[$key];
		$valid = function_exists($filter);
?>
<tr <?php echo ($i % 2 == 0) ? '' : 'class="alt"' ?>>
<td><?php echo $key; ?></td>
<td><?php echo ($valid ? '' : '<span style="color: red;"><strike>').$filter.($valid ? '' : '</strike><br/>function not found</span>'); ?></td>
</tr>
<?php
	$i++;
	}	
?>
</tbody>
</table>
<?php
}

// end of admin panel method
}



?>