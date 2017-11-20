<?php
/**
 * My Donations shortcode class.
 *
 * @version     1.4.0
 * @package     Charitable/Shortcodes/My Donations
 * @category    Class
 * @author      Eric Daams
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_My_Donations_Shortcode' ) ) :

	/**
	 * Charitable_My_Donations_Shortcode class.
	 *
	 * @since   1.4.0
	 */
	class Charitable_My_Donations_Shortcode {

		/**
		 * The callback method for the campaigns shortcode.
		 *
		 * This receives the user-defined attributes and passes the logic off to the class.
		 *
		 * @since   1.4.0
		 *
		 * @param   array $atts User-defined shortcode attributes.
		 * @return  string
		 */
		public static function display( $atts ) {
			$defaults = array(
				'hide_login' => false,
			);

			$args = shortcode_atts( $defaults, $atts, 'charitable_my_donations' );

			ob_start();

			/* If the user is logged out, show the login form. */
			if ( ! is_user_logged_in() ) {

				if ( false == $args['hide_login'] ) {
					echo Charitable_Login_Shortcode::display( array(
						'redirect' => charitable_get_current_url(),
					) );
				}

				return ob_get_clean();
			}

			/* If the user is logged in, show the my donations template. */
			$user     = charitable_get_user( get_current_user_id() );
			$donor_id = $user->get_donor_id();

			if ( false === $donor_id ) {
				$donations = array();
			} else {
				$args = array(
					'output'   => 'posts',
					'orderby'  => 'date',
					'order'    => 'DESC',
					'number'   => -1,
					'donor_id' => $donor_id,
				);

				if ( ! $user->is_verified() ) {
					$args['user_id'] = $user->ID;

					if ( array_key_exists( 'charitable_action', $_GET ) && 'verify_email' == $_GET['charitable_action'] ) {
						$message = __( 'We have sent you an email to confirm your email address.', 'charitable' );
					} else {
						$message = sprintf(
							__( '<a href="%s">Confirm your email address</a> to access your full donation history.', 'charitable' ),
							esc_url( charitable_get_email_verification_link( $user, charitable_get_current_url() ) )
						);
					}

					charitable_get_notices()->add_error( $message );
				}

				$donations = new Charitable_Donations_Query( $args );
			}

			$view_args = array(
				'donations' => $donations,
				'user'      => $user,
			);

			charitable_template( 'shortcodes/my-donations.php', $view_args );

			/**
			 * Filter the output of the shortcode.
			 *
			 * @since 1.4.0
			 *
			 * @param string $output    The default output.
			 * @param array  $view_args The view arguments.
			 * @param array  $args      The query arguments.
			 */
			return apply_filters( 'charitable_my_donations_shortcode', ob_get_clean(), $view_args, $args );
		}
	}

endif;
