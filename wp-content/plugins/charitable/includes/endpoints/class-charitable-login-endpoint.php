<?php
/**
 * Login endpoint.
 *
 * @version   1.5.0
 * @package   Charitable/Classes/Charitable_Login_Endpoint
 * @author    Eric Daams
 * @copyright Copyright (c) 2017, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Login_Endpoint' ) ) :

	/**
	 * Charitable_Login_Endpoint
	 *
	 * @since 1.5.0
	 */
	class Charitable_Login_Endpoint extends Charitable_Endpoint {

		/* @var string */
		const ID = 'login';

		/**
		 * Return the endpoint ID.
		 *
		 * @since  1.5.0
		 *
		 * @return string
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Add rewrite rules for the endpoint.
		 *
		 * @since 1.5.0
		 */
		public function setup_rewrite_rules() {}

		/**
		 * Return the endpoint URL.
		 *
		 * @global WP_Rewrite $wp_rewrite
		 * @since  1.5.0
		 *
		 * @param  array $args Mixed arguments.
		 * @return string
		 */
		public function get_page_url( $args = array() ) {
			$page = charitable_get_option( 'login_page', 'wp' );
			$url  = 'wp' == $page ? wp_login_url() : get_permalink( $page );
			return $url;
		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *		 
		 * @since  1.5.0
		 *
		 * @global WP_Post $post
		 *
		 * @param  array $args Mixed arguments.
		 * @return boolean
		 */
		public function is_page( $args = array() ) {
			global $post, $wp_query;

			$page = charitable_get_option( 'login_page', 'wp' );

			if ( 'wp' == $page ) {
				return wp_login_url() == charitable_get_current_url();
			}

			if ( is_object( $post ) ) {
				return $page == $post->ID;
			}

			return false;
		}
	}

endif;
