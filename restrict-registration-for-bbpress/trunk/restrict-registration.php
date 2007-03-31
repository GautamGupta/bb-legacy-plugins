<?php
/*
Plugin Name: Restrict registration
Plugin URI: http://www.network.net.au/bbpress/plugins/restrict-registration/restrict-registration.latest.zip
Description: Limits registration to email addresses from specific domains
Author: Sam Bauers
Version: 1.0
Author URI: http://www.network.net.au/

Version History:
1.0 	: Initial Release
*/

/*
Restrict registration for bbPress version 1.0
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/



// A comma separated list of email address domains that are allowed to register
$restrict_registration_to_domains = 'example.net,example.org';



$restrict_registration_active = false;
if ($restrict_registration_to_domains) {
	restrict_registration_clean_domains();
	if ($restrict_registration_to_domains_array = restrict_registration_explode_domains()) {
		$restrict_registration_active = true;
	}
}

if ($restrict_registration_active) {
	add_filter('bb_verify_email', 'restrict_registration_to_domain');
	add_filter('get_profile_info_keys', 'restrict_registration_email_label');
}

function restrict_registration_clean_domains()
{
	global $restrict_registration_to_domains;
	
	$clean = strtolower($restrict_registration_to_domains);
	$clean = preg_replace('|(\s)*\,(\s)*|', ', ', $clean);
	
	$restrict_registration_to_domains = $clean;
}

function restrict_registration_explode_domains()
{
	global $restrict_registration_to_domains;
	
	$domains = split(', ', $restrict_registration_to_domains);
	
	preg_grep('|^[\-0-9a-z]+\.[\-0-9a-z]+$|', $domains);
	
	if (count($domains)) {
		return $domains;
	} else {
		return false;
	}
}

function restrict_registration_to_domain($email)
{
	global $restrict_registration_to_domains_array;
	
	if ($email) {
		$match = false;
		foreach ($restrict_registration_to_domains_array as $domain) {
			if (preg_match('|@' . preg_quote($domain) . '$|i', $email)) {
				$match = true;
				break;
			}
		}
		if (!$match) {
			$email = false;
		}
	}
	
	return $email;
}

function restrict_registration_email_label($profile_info_keys)
{
	global $restrict_registration_to_domains;
	
	$profile_info_keys['user_email'] = array(
		1,
		__('Email') . '<span style="font-weight:normal;"> - ' . $restrict_registration_to_domains . ' ' . __('only') . '</span>'
	);
	
	return $profile_info_keys;
}
?>