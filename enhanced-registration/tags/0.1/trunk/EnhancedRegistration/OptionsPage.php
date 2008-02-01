<?php
/*
 * Copyright 2007 Yu-Jie Lin
 * 
 * This file is part of Enhanced Registration.
 * 
 * Cite this is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 * 
 * Cite this is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * Author: Yu-Jie Lin
 * Creation Date: 2007-11-26T13:10:02+0800
 */

function EROptions() {
	$options = bb_get_option('EROptions');

    if (isset($_POST['manage'])) {
        switch($_POST['manage']) {
        case __('Deactivate Plugin', ER_DOMAIN):
            $plugin_file = dirname(bb_plugin_basename(__FILE__)) . '/EnhancedRegistration.php';
            wp_redirect(str_replace('&#038;', '&', bb_nonce_url("plugins.php?action=deactivate&plugin=$plugin_file", "deactivate-plugin_$plugin_file")) . '&by=plugin');
            break;
            }
        }
	elseif (isset($_POST['UpdateOptions'])){
		switch($_POST['UpdateOptions']) {
		case __('Save', ER_DOMAIN):
			$newOptions = array();
			$hours = floor($_POST['autoDeleteUnactivatedOver']);
			if ($hours >= 0)
				$newOptions['autoDeleteUnactivatedOver'] = $hours;
			else
				echo '<div class="error"><p>', $hours, ' is not a valid number for deleting!</p></div>';
			$newOptions['sendReport'] = $_POST['sendReport'];
			$options = array_merge($options, $newOptions);
			bb_update_option('EROptions', $options);
			echo '<div class="updated"><p>', __('Options have been updated!', ER_DOMAIN),  '</p></div>';
			break;
		case __('Reset', ER_DOMAIN):
			$options = array_merge($options, ERGetDefaultOptions());
			bb_update_option('EROptions', $options);
			echo '<div class="updated"><p>' . __('Options have been reseted!', ER_DOMAIN) . '</p></div>';
			break;
			}
		}
	// Render options page
?>
	<h2><?php _e('Enhanced Registration Options', ER_DOMAIN); ?></h2>
		<h3><?php _e('About this plugin', ER_DOMAIN); ?></h3>
		<div>
		<ul>
			<li><a href="http://trac-hg.assembla.com/llbbsc/wiki/bbER"><?php _e("Plugin's Website", ER_DOMAIN); ?></a></li>
			<li><a href="http://www.livibetter.com/it/forum/llbb-small-creations"><?php _e('Get Support', ER_DOMAIN); ?></a> - <?php _e('Ask questions, submit feedbacks', ER_DOMAIN); ?></li>
			<li><a href="http://www.livibetter.com/"><?php _e("Author's Website", ER_DOMAIN); ?></a></li>
		</ul>
		</div>

		<h3><?php _e('Statistics', ER_DOMAIN); ?></h3>
		<div>
			<table><tbody>
				<tr>
					<td><?php _e('Number of users have been deleted by using ER:', ER_DOMAIN); ?></td>
					<td><?php echo (int) $options['deletedUnactivatedCount']; ?></td>
				</tr>
				<tr>
					<td><?php _e('Number of users have not done first-time login:', ER_DOMAIN); ?></td>
					<td><?php echo ERGetUnactivatedUserCount(); ?></td>
				</tr>
				<tr>
					<td><?php _e('Last time ran auto tasks:', ER_DOMAIN); ?></td>
					<td><?php echo (isset($options['lastRun'])) ? gmdate('r', $options['lastRun']) : 'Never'; ?></td>
				</tr>
				<tr>
					<td><?php _e('Last time sent the report:', ER_DOMAIN); ?></td>
					<td><?php echo (isset($options['lastSent'])) ? gmdate('r', $options['lastSent']) : 'Never'; ?></td>
				</tr>	
			</tbody></table>
		</div>

        <h3><?php _e('Management', ER_DOMAIN); ?></h3>
        <div>
            <form method="post" action="">
                <p>
                    <input type="submit" name="manage" value="<?php _e('Deactivate Plugin', ER_DOMAIN); ?>" style="font-weight:bold;"/>
                    <small><?php _e('Be careful! This will remove all your settings for this plugin! If you don\'t want to lose settings, please use Plugins page to deactivate this plugin.', ER_DOMAIN); ?></small>
                </p>
            </form>
        </div>

		<h3><?php _e('Options', ER_DOMAIN); ?></h3>
		<div>
			<form method="post" action="">
				<table><tbody>
					<tr>
						<td><?php _e('How many hours after registered that users have to do first-time login, or will be deleted?', ER_DOMAIN); ?></td>
						<td>
							<input type="text" name="autoDeleteUnactivatedOver" value="<?php echo $options['autoDeleteUnactivatedOver']; ?>" size="5"/> <?php _e('Positive integer number, or 0 for disabling auto deletion.', ER_DOMAIN); ?>
						</td>
					</tr>
					<tr>
						<td><label for="sendReport"><?php _e("How frequent should ER send reports to admin's email (assigned in config.php)?", ER_DOMAIN); ?></label></td>
						<td>
							<select name="sendReport" id="sendReport">
								<option value=""><?php _e('Never', ER_DOMAIN); ?></option>
								<option <?php if($options['sendReport']=='hourly') echo 'selected'; ?> value="hourly"><?php _e('Hourly', ER_DOMAIN); ?></option>
								<option <?php if($options['sendReport']=='daily') echo 'selected'; ?> value="daily"><?php _e('Daily', ER_DOMAIN); ?></option>
							</select>
						</td>
					</tr>
				<tbody></table>
				<div class="submit">
					<input type="submit" name="UpdateOptions" value="<?php _e('Save', ER_DOMAIN); ?>" style="font-weight:bold;"/>
					<input type="submit" name="UpdateOptions" value="<?php _e('Reset', ER_DOMAIN); ?>" style="font-weight:bold;"/>  
				</div>
			</form>
		</div>

<?php
	}
?>
