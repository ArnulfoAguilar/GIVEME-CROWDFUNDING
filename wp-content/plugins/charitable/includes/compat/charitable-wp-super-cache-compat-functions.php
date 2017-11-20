<?php
/**
 * Functions to improve compatibility with WP Super Cache.
 *
 * @package   Charitable/Functions/Compatibility
 * @version   1.5.0
 * @author    Eric Daams
 * @copyright Copyright (c) 2017, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Clear the campaign page cache after a donation is received.
 *
 * @since  1.4.18
 *
 * @param  int $campaign_id The campaign ID.
 * @return void
 */
function charitable_compat_wp_super_cache_clear_campaign_cache( $campaign_id ) {
	/* Set super cache to enabled. */
	$GLOBALS['super_cache_enabled'] = 1;

	if ( ! function_exists( 'wp_cache_post_change' ) ) {
		return;
	}

	/**
	 * In WP Super Cache version 1.4.9, a notice is triggered by wp_cache_post_change().
	 * If the user also has errors set to display, this may prevent the page from loading
	 * altogether. To avoid this, we will suppress errors.
	 */
	if ( ini_get( 'display_errors' ) ) {
		$data = get_plugins();

		if ( version_compare( $data['wp-super-cache/wp-cache.php']['Version'], '1.4.9', '<=' ) ) {
			@wp_cache_post_change( $campaign_id );
			return;
		}
	}

	wp_cache_post_change( $campaign_id );
}

add_action( 'charitable_flush_campaign_cache', 'charitable_compat_wp_super_cache_clear_campaign_cache' );
