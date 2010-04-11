<?php 
function report_settings ($data=array()) {
	global $report_options;
	$msg = '';	
	if (isset($_POST) && !empty($_POST)) {
		foreach ( (array) $_POST as $option => $value ) {
			if ( !in_array( $option, array( '_wpnonce', '_wp_http_referer', 'action', 'submit' ) ) ) {
				$option = trim( $option );
				$value = is_array( $value ) ? $value : trim( $value );
				$value = stripslashes_deep( $value );
				if ( $option == 'uri' && !empty( $value ) ) {
					$value = rtrim( $value, " \t\n\r\0\x0B/" ) . '/';
				}
				if ( $value ) {
					bb_update_option( $option, $value );
					$msg = '<div class="updated" id="message"><p><strong>Settings saved.</strong></p></div>';
				} else {
					bb_delete_option( $option );
				}
			}
			
		}
		
	}
	
?>
<h2><?php _e('Settings', 'report-post'); ?></h2>
<?php echo $msg; ?>
<form action="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'settings' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>" method="post" class="settings">
	<fieldset>
		<legend>Link Display Options</legend>
		<div id="option-name">
			<label for="name">Report Post Link text:</label>
			<div class="inputs">
				<input type="text" class="text" name="bbrp_link_text" value="<?php echo $report_options['bbrp_link_text']; ?>" />
			</div>
		</div>
		<div id="option-name">
			<label for="name">Reporting option text:</label>
			<div class="inputs">
				<input type="text" class="text" name="bbrp_option_label" value="<?php echo $report_options['bbrp_option_label']; ?>" />
			</div>
		</div>
		<div id="option-description">
			<label for="description">Text to display above reporter comment box:</label>
			<div class="inputs">
				<input type="text" class="text" value="<?php echo $report_options['bbrp_textarea_msg']; ?>" size="70" id="bbrp_textarea_msg" name="bbrp_textarea_msg">
			</div>
		</div>
		<div id="option-name">
			<label for="name">Options:</label>
			<div class="inputs">
				<textarea rows="5" cols="30" name="bbrp_options"><?php echo $report_options['bbrp_options']; ?></textarea>
				<p>One per line</p>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Report Success & Error Message Options</legend>
		<p>When reporting a post, specify the various messages a user might see.</p>
		<div id="option-uri">
			<label for="uri">Success Message:</label>
			<div class="inputs">
				<input type="text" class="text" value="<?php echo $report_options['bbrp_success_msg']; ?>" size="70" id="bbrp_success_msg" name="bbrp_success_msg">
			</div>
		</div>
		<div id="option-from-email">
			<label for="from-email">Error Message:</label>
			<div class="inputs">
				<input type="text" class="text" value="<?php echo $report_options['bbrp_error_msg']; ?>" size="70" id="bbrp_error_msg" name="bbrp_error_msg">
			</div>
		</div>		
		<div id="option-from-email">
			<label for="from-email">Already Reported Message::</label>
			<div class="inputs">
				<input type="text" class="text" value="<?php echo $report_options['bbrp_already_reported_msg']; ?>" size="70" id="bbrp_already_reported_msg" name="bbrp_already_reported_msg">
			</div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<input type="submit" value="Save Changes" name="submit" class="submit">
	</fieldset>
</form>

<?php
}

function report_list () {
	
	global $bbdb;
	
	// Calculate Paggination
	$p = (int) isset($_GET['p']) && is_numeric($_GET['p'])? $_GET['p'] : 1;
	$limit= 20;
	$offset = ($limit * ($p - 1));
	
	// Calculate Pages
	$total_records = $bbdb->get_var("SELECT COUNT(DISTINCT postID) FROM `{$bbdb->prefix}custom_reports` WHERE `status` = 1");	
	$pages = ceil($total_records / $limit);
	
	$reported_posts = $bbdb->get_results( "SELECT DISTINCT postID FROM `{$bbdb->prefix}custom_reports` WHERE `status` = 1 LIMIT $limit OFFSET $offset" );
?>
<style type="text/css">
	.pages {
		display:block;
		margin:20px 0;
		overflow:auto;
	}
	.pages ul {
		float:right;
		list-style:none outside none;
		margin:0;
		padding:0;
	}
	.pages li.pageinfo {
		background-color:#FFFFFF;
		padding:3px 5px;
	}
	.pages li {
		display:inline;
		float:left;
		margin:1px;
		padding:0;
	}
	.pages li.current {
		background:none repeat scroll 0 0 #E8E8E8;
		border:1px solid #CCCCCC;
		color:#666666;
		padding:3px 5px;
	}
	</style>
<h2 class="first"><?php _e('Reported posts', 'report-post'); ?><span class="subtitle"></span></h2>
<?php 
	if (!empty($reported_posts)) {		
?>
<table class="widefat">
<thead>
	<tr>
		<th style="width: 80%;">Post Title</th>
		<th style="width: 20%;"># Reports</th>
	</tr>
</thead>

<tfoot>
	<tr>
		<th style="width: 80%;">Post Title</th>
		<th style="width: 20%;"># Reports</th>
	</tr>
</tfoot>

<tbody id="role-inactive">
<?php
	$count = 0;
	foreach ($reported_posts as $post) {
		$count++;
		$i = $count % 2;
		$postData = bb_get_post($post->postID);		
		?>
		<tr id="post-<?php echo $post->postID; ?>"<?php if ($i == 0) { echo ' class="alt"'; } ?>>
			<td class="user"><span class="row-title"><a href="<?php bb_uri( '/edit.php?view=all&id='.$postData->post_id, array(), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php echo bbrp_trim_sentence($postData->post_text, 80); ?></a></span></td>
			<td><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'view', 'post_id' => $postData->post_id ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">View Reports</a>&nbsp;<a href="<?php bb_uri( '/edit.php?view=all&id='.$postData->post_id, array(), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">Edit</a></td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>

<div class="tablenav bottom">
<div class="tablenav-pages"><span class="displaying-pages"></span><div class="clear"></div></div>
</div><div class="clear"></div>
<?php
	} else {
		echo 'No reports found.';
	}
	if($pages > 1) {
	?>
    <div class="pages">
    	<ul>
        	<li class="pageinfo">Pages: </li>
            <?php 
			for($i=1; $i <= $pages; $i++): 
				if($i == $p)
				{?>
                <li class="current"><?php echo $i;?></li>
				<?php 
				continue;
				}
			?>
        	<li><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'list', 'p' => $i ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php echo $i;?></a></li>
            <?php
			endfor;
			?>
        </ul>
    </div>
    <?php 
	}
}

function view_reports ($post_id) {
	global $bbdb;
	$postData = bb_get_post($post_id);
	
	// Calculate Paggination
	$p = (int) isset($_GET['p']) && is_numeric($_GET['p'])? $_GET['p'] : 1;
	$limit= 20;
	$offset = ($limit * ($p - 1));
	
	// Calculate Pages
	$total_records = $bbdb->get_var("SELECT COUNT(*) FROM `{$bbdb->prefix}custom_reports` WHERE `postId` = '{$post_id}' AND `status` = 1");
	$pages = ceil($total_records / $limit);
		
	$reports = $bbdb->get_results( "SELECT * FROM `{$bbdb->prefix}custom_reports` WHERE `postId` = '{$post_id}' AND `status` = 1 ORDER BY `reported_date` DESC LIMIT $limit OFFSET $offset" );

	
?>
	<style type="text/css">
	.pages {
		display:block;
		margin:20px 0;
		overflow:auto;
	}
	.pages ul {
		float:right;
		list-style:none outside none;
		margin:0;
		padding:0;
	}
	.pages li.pageinfo {
		background-color:#FFFFFF;
		padding:3px 5px;
	}
	.pages li {
		display:inline;
		float:left;
		margin:1px;
		padding:0;
	}
	.pages li.current {
		background:none repeat scroll 0 0 #E8E8E8;
		border:1px solid #CCCCCC;
		color:#666666;
		padding:3px 5px;
	}
	</style>
	<h2>Reports</h2>
	<?php 
	if (!empty($reports)) {
	?>
	<table cellspacing="0" class="widefat post fixed">
		<thead>
		<tr>
			<th style="width: 10%;" scope="col">Report Type</th>
			<th style="width: 40%;" scope="col">Reporter's Comment</th>
			<th style="width: 20%;" scope="col">Date Reported</th>
			<th style="width: 10%;" scope="col">Reporter's IP</th>
			<th style="width: 20%;" scope="col"># Reports</th>
		</tr>
		</thead>

		<tbody>
			<?php
			$count = 0;
			foreach ($reports as $report) {
				$count++;
				$i = $count % 2;
			?>
			<tr valign="top"<?php if ($i == 0) { echo ' class="alt"'; } ?> id="report-<?php echo $report->id; ?>">
				<td><?php echo $report->report_option; ?></td>
				<td>
					<ul style="font-size: 11px;">
						<li><?php if (!empty($report->reporter_comment)) { echo nl2br($report->reporter_comment); } else { echo 'NIL'; } ?></li>
					</ul>
				</td>
				<td><?php echo $report->reported_date; ?></td>
				<td><a target="_blank" href="http://www.dnsstuff.com/tools/ipall/?tool_id=67&amp;token=&amp;toolhandler_redirect=0&amp;ip=<?php echo $report->reporter_ip; ?>"><?php echo $report->reporter_ip; ?></a></td>
				<td><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'del', 'id' => $report->id, 'post_id' => $post_id ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">Delete</a></td>
			</tr>
			<?php
			}
			?>
	</tbody>

	<tfoot>
	<tr>
		<th style="width: 10%;" scope="col">Report Type</th>
		<th style="width: 40%;" scope="col">Reporter's Comment</th>
		<th style="width: 20%;" scope="col">Date Reported</th>
		<th style="width: 10%;" scope="col">Reporter's IP</th>
		<th style="width: 20%;" scope="col"># Reports</th>
	</tr>
	</tfoot>
</table>
<?php
	} else {
		echo 'No reports found';
	}
	if($pages > 1) {
	?>
    <div class="pages">
    	<ul>
        	<li class="pageinfo">Pages: </li>
            <?php 
			for($i=1; $i <= $pages; $i++): 
				if($i == $p)
				{?>
                <li class="current"><?php echo $i;?></li>
				<?php 
				continue;
				}
			?>
        	<li><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'view', 'post_id' => $post_id, 'p' => $i ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php echo $i;?></a></li>
            <?php
			endfor;
			?>
        </ul>
    </div>
    <?php 
	}
}

function delete_report ($id, $post_id) {
	global $bbdb;
	$prepared_query = $bbdb->prepare(
		"DELETE FROM `{$bbdb->prefix}custom_reports` WHERE `id` = %d;",
		(int) $id
	);
	$bbdb->query( $prepared_query );
	$reportCount = $bbdb->get_var( "SELECT COUNT(*) FROM `{$bbdb->prefix}custom_reports` WHERE `postId` = '{$post_id}' AND `status` = 1" );
	if ($reportCount > 0) {
	?>
		<p><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'view', 'post_id' => $post_id ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">< Back to Reports for the post</a></p>
	<?php 
	} else {
	?>
		<p><a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'report_manager', 'action' => 'list' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">< Back to Reports</a></p>
	<?php
	}
	?>
	<h2>The report has been deleted.</h2>
<?php
}