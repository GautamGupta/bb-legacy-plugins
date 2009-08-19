<?php
/*
Plugin Name: Campaign Monitor Sync
Plugin URI: http://www.mariasadventures.com/campaign-monitor-sync
Description: When a new user registers, they can choose to be added to one of your Campaign Monitor mailing lists. The user can subscribe and unsubscribe to the mailing list on their Edit Profile page. An admin page allows the admin to provide their Campaign Monitor API key, and choose the mailing list that new users should be added to. The admin can also turn new user subscriptions on/off.
Author: Maria Cheung
Author URI: http://www.mariacheung.com/
Version: 1.0
*/

// Instantiate a new CampaignMonitor object
function campaign_monitor_connect_bb() {
	// Get the API keys from the DB
	$options = bb_get_option('campaign_monitor_sync_bb_options');
	if (empty($options['api_key'])) {
		return false;
	}
	
	require_once('CMBase.php');
	$campaign_monitor = new CampaignMonitor($options['api_key'], $options['client_api_key'], $options['campaign_api_key'], $options['list_api_key']);
	
	return $campaign_monitor;
}

function campaign_monitor_sync_bb_add_user_to_list($user_email, $user_name=null) {
	if ($campaign_monitor = campaign_monitor_connect_bb()) {
		// This is the actual call to the method, passing email address, name.
		$result = $campaign_monitor->subscriberAddAndResubscribe($user_email, $user_name);
	}
}

function campaign_monitor_sync_bb_remove_user_from_list($user_email) {
	if ($campaign_monitor = campaign_monitor_connect_bb()) {
		// This is the actual call to the method, passing email address, name.
		$result = $campaign_monitor->subscriberUnsubscribe($user_email);
	}
}

function campaign_monitor_sync_bb_check_user_subscribed($user_email) {
	if ($campaign_monitor = campaign_monitor_connect_bb()) {
		return $campaign_monitor->subscribersGetIsSubscribed($user_email);
	}
}


add_action( 'bb_admin_menu_generator', 'campaign_monitor_sync_bb_add_admin_page' );
// Add the menu item for the admin page, under the Users section
function campaign_monitor_sync_bb_add_admin_page() {
	bb_admin_add_submenu(__('Campaign Monitor Sync'), 'moderate', 'campaign_monitor_sync_bb_admin_page', 'users.php');
}

// Draw the actual Campaign Monitor Sync admin page
function campaign_monitor_sync_bb_admin_page() {
	$options = bb_get_option('campaign_monitor_sync_bb_options');
	
	if (!empty($options['api_key'])) {
		$clients = array();
		if ($campaign_monitor = campaign_monitor_connect_bb()) {
			$clients = $campaign_monitor->userGetClients();
		}
	}
	
	// Try to connect using the API key and show error if there is a problem
	?>
	<h2>Campaign Monitor Settings</h2>
	<p>Please enter your Campaign Monitor API key here. You must then choose a Client and the Mailing List to use. Newly registered users will be added to the chosen Mailing List in Campaign Monitor.</p><br />
	
	<?php if (empty($options['api_key']) || empty($options['client_api_key']) || empty($options['list_api_key'])) { ?>
		<p style="color: red;">NOTE: New users will not be added to a mailing list, because none has been chosen. </p>
	<?php } ?>
	
	<form class="options" method="post">
		<fieldset>
			<label for="campaign_monitor_sync_bb_api_key">API Key</label>
			<div>
				<input type="text" size="33" name="campaign_monitor_sync_bb[api_key]" id="campaign_monitor_sync_bb_api_key" value="<?php echo $options['api_key']; ?>"/>
			</div>
			
			<?php if (!empty($clients['anyType']['Client'])) { 
				if (isset($clients['anyType']['Client']['ClientID'])) {
					$clients['anyType']['Client'] = array($clients['anyType']['Client']);
				} 
				?>
				
				<!-- Draw the Client drop-down -->
				<label for="campaign_monitor_sync_bb_client_api_key">Which Client?</label>
				<div>
					<select name="campaign_monitor_sync_bb[client_api_key]" id="campaign_monitor_sync_bb_client_api_key">
						<option id="" value="">select</option>
						
						<?php foreach ($clients['anyType']['Client'] as $client) { 
							$selected = ($options['client_api_key'] == $client['ClientID'] ? 'selected="selected"' : ''); ?>
							<option <?php echo $selected; ?> 
								id="<?php echo $client['ClientID']; ?>" value="<?php echo $client['ClientID']; ?>">
									<?php echo $client['Name']; ?>
							</option>
						<?php } ?>
						
					</select>
				</div>
				
				<?php foreach ($clients['anyType']['Client'] as $client) { 
					$lists = $campaign_monitor->clientGetLists($client['ClientID']); 
					
					if (isset($lists['anyType']['List']['ListID'])) {
						$lists['anyType']['List'] = array($lists['anyType']['List']);
					}
					
					if (!empty($lists)) { ?>
							<!-- Draw the List drop-downs for each Client -->
							<div class="campaign_monitor_sync_bb_list" id="client_<?php echo $client['ClientID']; ?>_lists">
								<label for="campaign_monitor_sync_bb_list_api_key">Which Mailing List?</label>
							
								<div>
									<select name="campaign_monitor_sync_bb[list_api_key][<?php echo $client['ClientID']; ?>]" id="campaign_monitor_sync_bb_list_api_key">
										<option id="" value="">select</option>
										
										<?php foreach ($lists['anyType']['List'] as $list) {
											$selected = ($options['list_api_key'] == $list['ListID'] ? 'selected="selected"' : ''); ?>
											<option <?php echo $selected; ?> 
												id="<?php echo $list['ListID']; ?>" value="<?php echo $list['ListID']; ?>">
												<?php echo $list['Name']; ?>
											</option>
										<?php } ?>

									</select>
								</div>
							</div>
					<?php } ?>
					
					
				<?php } ?>
			<?php } ?>
			
			<label for="campaign_monitor_sync_bb_subscribe_new_users">Allow new users to subscribe?</label>
			<div>
				<input type="checkbox" value="1" name="campaign_monitor_sync_bb[campaign_monitor_sync_bb_subscribe_new_users]" id="campaign_monitor_sync_bb_subscribe_new_users" <?php echo (isset($options['campaign_monitor_sync_bb_subscribe_new_users']) && $options['campaign_monitor_sync_bb_subscribe_new_users'] == 1 ? 'checked="checked"' : ''); ?> />
			</div>
			
			<?php if (!empty($clients['anyType']['Message'])) { ?>
				<p style="color: red;"><?php echo $clients['anyType']['Message']; ?></p>
			<?php } ?>
			
			<br /><br />
			
	       	<p class="submit"><input type="submit" name="campaign_monitor_sync_bb_options" value="Update Options" /></p>
		</fieldset>
	</form>
	
	<script type="text/javascript">
		jQuery(document).ready( function() {
			jQuery("select#campaign_monitor_sync_bb_client_api_key").bind("change", function(){
				campaign_monitor_change_lists();
			});
			
			function campaign_monitor_change_lists() {
				jQuery("div.campaign_monitor_sync_bb_list").hide();
				jQuery("div#client_" + jQuery("select#campaign_monitor_sync_bb_client_api_key :selected").attr("id") + "_lists").show();
			}
			
			campaign_monitor_change_lists();
		});
	
	</script>
	<?php	
}

// Process the admin page
add_action('campaign_monitor_sync_bb_admin_page_pre_head','campaign_monitor_sync_bb_admin_page_process');
function campaign_monitor_sync_bb_admin_page_process() {
	// Update the settings in the DB
	if (isset($_POST['campaign_monitor_sync_bb'])) {
		if ($_POST['campaign_monitor_sync_bb']) {
			$_POST['campaign_monitor_sync_bb']['list_api_key'] = $_POST['campaign_monitor_sync_bb']['list_api_key'][$_POST['campaign_monitor_sync_bb']['client_api_key']];
		}
		$options = ($_POST['campaign_monitor_sync_bb']) ? $_POST['campaign_monitor_sync_bb'] : array() ;
		
		bb_update_option('campaign_monitor_sync_bb_options', $options);
	}
}

// Add an extra user profile option, to let user unsubscribe from Campaign Monitor list
add_filter( 'get_profile_info_keys', 'campaign_monitor_sync_bb_add_user_profile_option');
function campaign_monitor_sync_bb_add_user_profile_option($options) {
	global $bb_current_user;
	
	// Add the opt-in checkbox, if the admin has allowed users to subscribe
	$admin_options = bb_get_option('campaign_monitor_sync_bb_options');

	if (isset($admin_options['campaign_monitor_sync_bb_subscribe_new_users']) && $admin_options['campaign_monitor_sync_bb_subscribe_new_users'] == 1) {
	
		// Check first if the user is subscribed via API, and update user meta option accordingly.
		// Because the user can subscribe directly from Campaign Monitor (via newsletter unsubscribe link) without updating the meta option in bbPress
		$subscribed = campaign_monitor_sync_bb_check_user_subscribed($bb_current_user->user_email);

		// Make sure the meta option is the same as in Campaign Monitor
		bb_update_usermeta( $bb_current_user->ID, 'cms_subscribe_mailing_list', (strtolower($subscribed['anyType']) == 'true' ? 1 : 0));
	
		// Add the extra option to the form
		$options['cms_subscribe_mailing_list'] = array(0, 'Subscribe to Mailing List?', 'checkbox', 1);
	}
	return $options;
}

// When the user edits their profile, subscribe/unsubscribe from the mailing list
add_action( 'profile_edited', 'campaign_monitor_sync_bb_update_subscription');
add_action( 'register_user', 'campaign_monitor_sync_bb_update_subscription');
function campaign_monitor_sync_bb_update_subscription($user_id) {
	// Find the user details
	if ( !$user = bb_get_user( $user_id ) ) {
		return false;
	}
	
	if ($campaign_monitor = campaign_monitor_connect_bb()) {
		// Subscribe
		if ($user->cms_subscribe_mailing_list == 1) {
			$result = $campaign_monitor->subscriberAddAndResubscribe($user->user_email, $user->user_nicename);
		} else {
		// Unsubscribe
			$result = $campaign_monitor->subscriberUnsubscribe($user->user_email);
		}
	}	
}
?>