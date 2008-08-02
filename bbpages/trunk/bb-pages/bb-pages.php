<?php
/*
Plugin Name: bbPages
Plugin URI: http://astateofmind.eu/freebies/bbpages/
Description: Allows you to create static pages within your bbPress forum.
Author: F.Thion
Author URI: http://astateofmind.eu
Version: 0.0.1

license: GPL 
donate: http://astateofmind.eu/about/support/
*/

function get_page_id() {
	if ( isset ( $_GET['page_id'] ) ) :
		$page_id = $_GET['page_id'];
		return $page_id;
	endif;
}

function isset_id() {
	if ( $_GET['page_id'] > 0 ) :
		return true;
	else:
		return false;
	endif;
}

function page_exist() {
	global $bbdb, $bb;
	$id = get_page_id();
	
	$table_name = $bbdb->prefix . "pages";	$query = $bbdb->get_results("SELECT page_id FROM $table_name WHERE page_id=".$id."");

	if ( $query == true ) :
		return true;
	else:
		return false;
	endif;
}

function get_page_title() {
	global $bbdb, $bb, $page_id;
	$id = get_page_id();
	$table_name = $bbdb->prefix . "pages";
		$query = $bbdb->get_results("SELECT page_title FROM $table_name WHERE page_id=".$id."");

	foreach ($query as $rk) {
		return $rk->page_title;
	}
}

function get_page_slug() {
	global $bbdb, $bb, $page_id, $page;
	$id = get_page_id();
	$table_name = $bbdb->prefix . "pages";
		$query = $bbdb->get_results("SELECT page_slug FROM $table_name WHERE page_id=".$id."");

	foreach ($query as $rk) {
		return $rk->page_slug;
	}
}

function get_page_content() {
	global $bbdb, $bb, $page_id;
	$id = get_page_id();
	$table_name = $bbdb->prefix . "pages";
		$query = $bbdb->get_results("SELECT page_content FROM $table_name WHERE page_id=".$id."");

	foreach ($query as $rk) {
		return $rk->page_content;
	}
}

function list_pages() {
	global $bbdb, $bb;
	
	$table_name = $bbdb->prefix . "pages";
		$query = $bbdb->get_results("SELECT * FROM $table_name ORDER BY page_order ASC");

	foreach ($query as $rk) {
		echo '
			<tr>
				<td>'.$rk->page_id.'</td>
				<td>'.$rk->page_title.'</td>
				<td>'.$rk->page_date.'</td>
				<td>'.$rk->page_status.'</td>
				<td><a href="admin-base.php?plugin=pages_panel&act=edit&id='.$rk->page_id.'">Edit</a> | <a href="admin-base.php?plugin=pages_panel&act=delete&id='.$rk->page_id.'">Delete</a></td>
			</tr>
		';
	}
}

function pages_panel() {
	$action = $_REQUEST['act'];

	switch($action)
	{
		default:
			?>
				<h2>Manage Static Pages</h2>

				<a href="admin-base.php?plugin=pages_panel&act=add">Create new page</a>

				<table class="widefat">
				<thead>
					<tr>
						<th style='width:5%;'>ID</th>
						<th style='width:45%;'>Title</th>
						<th style='width:20%;'>Creation Date</th>
						<th style='width:10%;'>Status</th>
						<th style='width:20%;'>Actions</th>
					</tr>
				</thead>

				<tbody>
					<?php echo list_pages(); ?>
				</tbody>
				</table>
				
	<h3>Please support the developer</h3>
	
	Do you like this plugin? Do you find it useful? Please donate few dollars so I could keep developing it further and further, and add a lot of great functions such as MultiViews support or WYSIWYG editor. Even the smallest help is greatly appreciated!
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="wojciech.usarzewicz@gmail.com">
	<input type="hidden" name="item_name" value="bbPages Donation">
	<input type="hidden" name="item_number" value="bbPages Donation">
	<input type="hidden" name="no_shipping" value="0">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD" />
	
	Type donation amount: $ <input type="text" name="amount" value="1" />
	
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	
	<input type="submit" name="submit" value="Donate with PayPal!" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
	</form>
	
			<?php	
		break;
		
		case "add":
			global $bbdb, $bb;		
		
			if (isset($_POST['add_page'])) 
			{
				$page_title = $_POST['page_title'];
				$page_slug = bb_sanitize_with_dashes( "$page_title", "" );
				
				$page_content = $_POST['page_content'];
				$page_order = $_POST['page_order'];
				$page_status = $_POST['page_status'];
				
				$table_name = $bbdb->prefix . "pages";
				$query = "INSERT INTO " . $table_name .
            " (page_id, page_date, page_content, page_title, page_status, page_slug, page_order) " .
            "VALUES (0, NOW(), '".$page_content."', '".$page_title."', '".$page_status."', '".$page_slug."', '".$page_order."')";
				
				$bbdb->query($query);
		
				?><div class="updated"><p>Page has been saved.</p></div> <?php
			}
			?>

			<form method="post">
				<fieldset><legend>Page title (slug will be created automatically)</legend>
				<input name="page_title" id="page_title" type="text" class="text-input" value="" size="50" /></fieldset>

				<fieldset><legend>Page content</legend>
				<textarea name="page_content" id="page_content" rows="20" cols="80" ></textarea></fieldset>
			
				<fieldset><legend>Page order (type numbers here)</legend>
				<input name="page_order" id="page_order" type="text" class="text-input" value="" size="10" /></fieldset>

				<fieldset><legend>Save page as:</legend>
				<select name="page_status" id="page_status" onChange="this.options[this.selectedIndex].value">
					<option value="0">Draft</option>
					<option value="1">Published</option>
				</select></fieldset>
				
				<input type="submit" name="add_page" value="<?php _e('Add page', 'add_page') ?>" />
			</form>
			
			<?php
		break;		
		
		case "edit":
			global $bbdb, $bb;		
	
			if (isset($_POST['save_page'])) {
			
				$page_id = $_POST['page_id'];
				$page_title = $_POST['page_title'];
				
				$page_content = $_POST['page_content'];
				$page_order = $_POST['page_order'];
				$page_status = $_POST['page_status'];
			
				$table_name = $bbdb->prefix . "pages";
				$query = "UPDATE ".$table_name." SET page_content='".$page_content."', page_title='".$page_title."', page_status='".$page_status."', page_order='".$page_order."' WHERE page_id='".$page_id."'";
				
				$bbdb->query($query);
			
			?> <div class="updated"><p>Page updated...</p></div> <?php
			}
			
			$table_name = $bbdb->prefix . "pages";
			$query = $bbdb->get_results("SELECT * FROM $table_name WHERE page_id=".$_GET['id']."");

			foreach ($query as $rk) {
				$id = $rk->page_id;
				$title = $rk->page_title;
				$content = $rk->page_content;
				$order = $rk->page_order;
				$status = $rk->page_status;
			}
			
			?>
			<h2>Edit page</h2>
			
			<form method="post">
				<fieldset><legend>Page title (slug will be created automatically)</legend>
				<input name="page_title" id="page_title" type="text" class="text-input" value="<?php echo "$title"; ?>" size="50" /></fieldset>

				<fieldset><legend>Page content</legend>
				<textarea name="page_content" id="page_content" rows="20" cols="80" ><?php echo "$content"; ?></textarea></fieldset>
			
				<fieldset><legend>Page order (type numbers here)</legend>
				<input name="page_order" id="page_order" type="text" class="text-input" value="<?php echo "$order"; ?>" size="10" /></fieldset>

				<fieldset><legend>Save page as:</legend>
				<select name="page_status" id="page_status" onChange="this.options[this.selectedIndex].value">
					<option value="0">Draft</option>
					<option value="1">Published</option>
				</select></fieldset>
				
				<input type="hidden" name="page_id" value="<?php echo $_GET['id']; ?>" />
				
				<input type="submit" name="save_page" value="<?php _e('Save page', 'save_page') ?>" />
			</form>
			
			<?php
		break;
		
		case "delete":
			global $bbdb, $bb;
			$id = $_GET['id'];
			
			$table_name = $bbdb->prefix . "pages";
			$query = $bbdb->get_results("DELETE FROM $table_name WHERE page_id=".$_GET['id']."");
		
    		$bbdb->query($query);
			
			?> <div class="updated"><p>Page deleted...</p></div> <?php
		break;
	}
}

function pages_admin_menu() {
	bb_admin_add_menu(__('Manage Pages'), 'administrate', 'pages_panel');
}

function bbpages_install() {
	global $bbdb;
	$table_name = $bbdb->prefix . "pages";
	
	$bbdb->query("CREATE TABLE IF NOT EXISTS $table_name (
		page_id mediumint(9) NOT NULL AUTO_INCREMENT,
		page_date datetime,
		page_content text,
		page_title varchar(255),
		page_status int(1),
		page_slug varchar(255),
		page_order int(11),
		PRIMARY KEY (page_id)
		)");
}

bb_register_activation_hook( __FILE__,  'bbpages_install');
add_action( 'bb_admin_menu_generator', 'pages_admin_menu' );

?>