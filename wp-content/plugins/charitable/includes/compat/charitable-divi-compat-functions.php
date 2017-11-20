<?php
/**
 * Functions to improve compatibility with Divi.
 *
 * @package     Charitable/Functions/Compatibility
 * @version     1.4.17
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * If Divi and Yoast SEO are active, we end up with all sorts of problems related to Divi
 * trying to display the shortcodes in the admin. Exclude all our shortcodes.
 *
 * @since   1.5.0
 *
 * @param   string[] $shortcodes
 * @return  string[]
 */
function charitable_divi_compat_admin_excluded_shortcodes( $shortcodes ) {
	if ( class_exists( 'WPSEO_Options' ) ) {
		$shortcodes = array_merge( $shortcodes, array(
			'campaigns',
			'donation_receipt',
			'charitable_my_donations',
			'charitable_login',
			'charitable_registration',
			'charitable_profile',
			'charitable_submit_campaign',
			'charitable_my_campaigns',
			'charitable_creator_donations',
			'campaign_updates',
			'charitable_donors',
			'charitable_donation_form',
		) );
	}

	return $shortcodes;
}

add_filter( 'et_pb_admin_excluded_shortcodes', 'charitable_divi_compat_admin_excluded_shortcodes' );
