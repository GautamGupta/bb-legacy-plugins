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

	$status = array(
		0 => 'Draft',
		1 => 'Published'
	);

	foreach ($query as $rk) {
		echo '
			<tr>
				<td>'.$rk->page_id.'</td>
				<td>'.$rk->page_title.'</td>
				<td>'.$rk->page_date.'</td>
				<td>'.$status[$rk->page_status].'</td>
				<td><a href="admin-base.php?plugin=pages_panel&act=edit&id='.$rk->page_id.'">Edit</a> | <a href="admin-base.php?plugin=pages_panel&act=delete&id='.$rk->page_id.'">Delete</a> | <a href="'.bb_get_option('uri').'/page.php?page_id='.$rk->page_id.'">View</a></td>
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
				<h2>Manage Static Pages <small>(<a class="submit" href="admin-base.php?plugin=pages_panel&act=add">Create new page</a>)</small></h2>

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
	
	<img src="http://astateofmind.eu/uploads/donation.gif" style="margin-right:10px" border="0" align="left" />
	
	Do you like this plugin? Do you find it useful? If so, please donate few dollars so I could keep develop this plugin and others further and further. Even the smallest help is greatly appreciated for a student in Poland ;). <br /><br />
	
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
	
	<p>Want to know what I'm developing right now? <a href="http://twitter.com/t_thion/">Follow me on Twitter</a>, ignore 90% of stuff and learn a lot you will ;). And thank you for using my plugin!</p>
	
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

			<h2>Add new page</h2>
			
			<form class="settings" method="post">
			
				<fieldset>
					<div>
						<label for="page_title">
							Page title			</label>
						<div>
							<input name="page_title" id="page_title" type="text" class="text long" />
							<p>Page slug will be created automatically (don't worry, it's not useful for anything yet).</p>
						</div>
					</div>
					
					<div>
						<label for="page_order">
							Page order			</label>
						<div>
							<input name="page_order" id="page_order" type="text" class="text long" />
						</div>
					</div>
					
					<div>
						<label for="page_content">
							Page content			</label>
						<div>
							<textarea name="page_content" id="page_content" rows="20" cols="80" ></textarea>
						</div>
					</div>

				<input type="hidden" name="page_status" value="1" />
				<input type="hidden" name="page_id" value="<?php echo $_GET['id']; ?>" />
				</fieldset>
				
				<input type="submit" class="submit" name="add_page" value="<?php _e('Add page', 'add_page') ?>" />
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
			
			<form class="settings" method="post">
			
				<fieldset>
					<div>
						<label for="page_title">
							Page title			</label>
						<div>
							<input name="page_title" id="page_title" type="text" class="text long" value="<?php echo "$title"; ?>" />
							<p>Page slug will be created automatically (don't worry, it's not useful for anything yet).</p>
						</div>
					</div>
					
					<div>
						<label for="page_order">
							Page order			</label>
						<div>
							<input name="page_order" id="page_order" type="text" class="text long" value="<?php echo "$order"; ?>" size="10" />
						</div>
					</div>
					
					<div>
						<label for="page_content">
							Page content			</label>
						<div>
							<textarea name="page_content" id="page_content" rows="20" cols="80" ><?php echo "$content"; ?></textarea>
						</div>
					</div>

				<input type="hidden" name="page_status" value="1" />
				<input type="hidden" name="page_id" value="<?php echo $_GET['id']; ?>" />
				</fieldset>
				
				<input type="submit" class="submit" name="save_page" value="<?php _e('Save page', 'save_page') ?>" />
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