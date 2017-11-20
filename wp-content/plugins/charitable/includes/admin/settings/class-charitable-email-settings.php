<?php
/**
 * Charitable Email Settings UI.
 *
 * @package     Charitable/Classes/Charitable_Email_Settings
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Email_Settings' ) ) :

	/**
	 * Charitable_Email_Settings
	 *
	 * @final
	 * @since   1.0.0
	 */
	final class Charitable_Email_Settings {

		/**
		 * The single instance of this class.
		 *
		 * @var Charitable_Email_Settings|null
		 */
		private static $instance = null;

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @since  1.2.0
		 *
		 * @return Charitable_Email_Settings
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Create object instance.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Returns all the payment email settings fields.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function add_email_fields() {
			if ( ! charitable_is_settings_view( 'emails' ) ) {
				return array();
			}

			return array(
				'section' => array(
					'title'     => '',
					'type'      => 'hidden',
					'priority'  => 10000,
					'value'     => 'emails',
					'save'      => false,
				),
				'section_emails' => array(
					'title'     => __( 'Available Emails', 'charitable' ),
					'type'      => 'heading',
					'priority'  => 5,
				),
				'emails' => array(
					'title'     => false,
					'callback'  => array( $this, 'render_emails_table' ),
					'priority'  => 7,
				),
				'section_email_general' => array(
					'title'     => __( 'General Email Settings', 'charitable' ),
					'type'      => 'heading',
					'priority'  => 10,
				),
				'email_from_name' => array(
					'title'     => __( '"From" Name', 'charitable' ),
					'type'      => 'text',
					'help'      => __( 'The name of the email sender.', 'charitable' ),
					'priority'  => 12,
					'default'   => get_option( 'blogname' ),
				),
				 'email_from_email' => array(
					'title'     => __( '"From" Email', 'charitable' ),
					'type'      => 'email',
					'help'      => __( 'The email address of the email sender. This will be the address recipients email if they hit "Reply".', 'charitable' ),
					'priority'  => 14,
					'default'   => get_option( 'admin_email' ),
				 ),
			);
		}

		/**
		 * Add settings for each individual payment email.
		 *
		 * @since  1.0.0
		 *
		 * @return array[]
		 */
		public function add_individual_email_fields( $fields ) {
			foreach ( charitable_get_helper( 'emails' )->get_available_emails() as $email ) {
				$email = new $email;
				$key   = 'emails_' . $email->get_email_id();

				/**
				 * Filter the email fields.
				 *
				 * This filter is primarily useful for adding settings to multiple emails.
				 * If you only want to add fields to one particular email, use this hook instead:
				 *
				 * charitable_settings_fields_emails_email_{email_id}
				 *
				 * @see   Charitable_Email::email_settings
				 *
				 * @since 1.0.0
				 *
				 * @param array            $settings Email settings.
				 * @param Charitable_Email $email    The `Charitable_Email` instance.
				 */
				$fields[ $key ] = apply_filters( 'charitable_settings_fields_emails_email', $email->email_settings(), $email );
			}

			return $fields;
		}

		/**
		 * Add email keys to the settings groups.
		 *
		 * @since  1.0.0
		 *
		 * @param  string[] $groups
		 * @return string[]
		 */
		public function add_email_settings_dynamic_groups( $groups ) {
			foreach ( charitable_get_helper( 'emails' )->get_available_emails() as $email_key => $email_class ) {
				if ( ! class_exists( $email_class ) ) {
					continue;
				}

				$groups[ 'emails_' . $email_key ] = apply_filters( 'charitable_settings_fields_emails_email', array(), new $email_class );
			}

			return $groups;
		}

		/**
		 * Display table with emails.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function render_emails_table( $args ) {
			charitable_admin_view( 'settings/emails', $args );
		}

		/**
		 * Checks whether we're looking at an individual email's settings page.
		 *
		 * @since  1.0.0
		 *
		 * @return boolean
		 */
		private function is_individual_email_settings_page() {
			return isset( $_GET['edit_email'] );
		}

		/**
		 * Returns the helper class of the email we're editing.
		 *
		 * @since  1.0.0
		 *
		 * @return Charitable_Email|false
		 */
		private function get_current_email_class() {
			$email = charitable_get_helper( 'emails' )->get_email( $_GET['edit_email'] );

			return $email ? new $email : false;
		}
	}

endif;
