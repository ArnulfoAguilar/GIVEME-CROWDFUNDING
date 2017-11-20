<?php
/**
 * Main class for setting up the Charitable Recipients Addon, which is programatically activated by child themes.
 *
 * @package     Charitable/Classes/Charitable_Recipients
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Recipients' ) ) :

	/**
	 * Charitable_Recipients
	 *
	 * @since   1.0.0
	 */
	class Charitable_Recipients implements Charitable_Addon_Interface {

		/**
		 * Responsible for creating class instances.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public static function load() {
			$object = new Charitable_Recipients();

			do_action( 'charitable_recipients_addon_loaded', $object );
		}

		/**
		 * Create class instance.
		 *
		 * @since   1.0.0
		 */
		private function __construct() {
			$this->load_dependencies();
			$this->attach_hooks_and_filters();
		}

		/**
		 * Include required files.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		private function load_dependencies() {
			require_once( 'charitable-recipients-functions.php' );
			require_once( 'class-charitable-recipient-types.php' );
		}

		/**
		 * Set up hooks and filter.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		private function attach_hooks_and_filters() {
		}

		/**
		 * Activate the addon.
		 *
		 * @since   1.0.0
		 *
		 * @return  boolean Whether the add-on was activated.
		 */
		public static function activate() {
			if ( 'charitable_activate_addon' !== current_filter() ) {
				return false;
			}

			self::load();

			return true;
		}
	}

endif;
