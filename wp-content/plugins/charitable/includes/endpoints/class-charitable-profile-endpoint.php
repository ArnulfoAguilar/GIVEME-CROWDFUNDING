<?php
/**
 * profile endpoint.
 *
 * @version     1.5.0
 * @package     Charitable/Classes/Charitable_Profile_Endpoint
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Profile_Endpoint' ) ) :

	/**
	 * Charitable_Profile_Endpoint
	 *
	 * @abstract
	 * @since   1.5.0
	 */
	class Charitable_Profile_Endpoint extends Charitable_Endpoint {

		/**
		 * @var     string
		 */
		const ID = 'profile';

		/**
		 * Return the endpoint ID.
		 *
		 * @since   1.5.0
		 *
		 * @return 	string
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Return the endpoint URL.
		 *
		 * @global 	WP_Rewrite $wp_rewrite
		 * @since   1.5.0
		 *
		 * @param 	array      $args
		 * @return  string
		 */
		public function get_page_url( $args = array() ) {

			$page = charitable_get_option( 'profile_page', false );

			if ( ! $page ) {
				return '';
			}

			return get_permalink( $page );

		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *
		 * @global  WP_Post $post
		 * @since   1.5.0
		 *
		 * @param 	array   $args
		 * @return  boolean
		 */
		public function is_page( $args = array() ) {

			global $post;

			$page = charitable_get_option( 'profile_page', false );

			return false == $page || is_null( $post ) ? false : $page == $post->ID;

		}
	}

endif;
