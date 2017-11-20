<?php
/**
 * Displays a table of the user's donations, with links to the donation receipts.
 *
 * Override this template by copying it to yourtheme/charitable/shortcodes/my-donations.php
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Account
 * @since   1.4.0
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$user      = array_key_exists( 'user', $view_args ) ? $view_args['user'] : charitable_get_user( get_current_user_id() );
$donations = $view_args['donations'];

/**
 * @hook    charitable_my_donations_before
 */
do_action( 'charitable_my_donations_before', $donations );

if ( empty( $donations ) ) : ?>

	<p><?php _e( 'You have not made any donations yet.', 'charitable' ) ?></p>

<?php else : ?>

	<table class="charitable-creator-donations">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Date', 'charitable' ) ?></th>
				<?php
					/**
					 * Add a column header after the donation date header. Any output should be wrapped in <th></th>.
					 *
					 * @since 1.5.0
					 *
					 * @param object[] $donations An array of donations as a simple object.
					 */
					do_action( 'charitable_my_donations_table_header_after_date', $donations );
				?>
				<th scope="col"><?php _e( 'Campaign', 'charitable' ) ?></th>
				<?php
					/**
					 * Add a column header after the campaign header Any output should be wrapped in <th></th>.
					 *
					 * @since 1.5.0
					 *
					 * @param object[] $donations An array of donations as a simple object.
					 */
					do_action( 'charitable_my_donations_table_header_after_campaigns', $donations );
				?>
				<th scope="col"><?php _e( 'Amount', 'charitable' ) ?></th>
				<?php
					/**
					 * Add a column header after the amount header. Any output should be wrapped in <th></th>.
					 *
					 * @since 1.5.0
					 *
					 * @param object[] $donations An array of donations as a simple object.
					 */
					do_action( 'charitable_my_donations_table_header_after_amount', $donations );
				?>
				<th scope="col"><?php _e( 'Receipt', 'charitable' ) ?></th>
				<?php
					/**
					 * Add a column header after the receipt header. Any output should be wrapped in <th></th>.
					 *
					 * @since 1.5.0
					 *
					 * @param object[] $donations An array of donations as a simple object.
					 */
					do_action( 'charitable_my_donations_table_header_after_receipt', $donations );
				?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $donations as $donation ) : ?>
			<tr>
				<td><?php echo mysql2date( 'F j, Y', get_post_field( 'post_date', $donation->ID ) ) ?></td>
				<?php
					/**
					 * Add a cell after the donation date. Any output should be wrapped in <td></td>.
					 *
					 * @since 1.5.0
					 *
					 * @param object $donation The donation as a simple object.
					 */
					do_action( 'charitable_my_donations_table_after_date', $donation );
				?>
				<td><?php echo $donation->campaigns ?></td>
				<?php
					/**
					 * Add a cell after the list of campaigns. Any output should be wrapped in <td></td>.
					 *
					 * @since 1.5.0
					 *
					 * @param object $donation The donation as a simple object.
					 */
					do_action( 'charitable_my_donations_table_after_campaigns', $donation );
				?>
				<td><?php echo charitable_format_money( $donation->amount ) ?></td>
				<?php
					/**
					 * Add a cell after the donation amount. Any output should be wrapped in <td></td>.
					 *
					 * @since 1.5.0
					 *
					 * @param object $donation The donation as a simple object.
					 */
					do_action( 'charitable_my_donations_table_after_amount', $donation );
				?>
				<td><a href="<?php echo esc_url( charitable_get_permalink( 'donation_receipt_page', array( 'donation_id' => $donation->ID ) ) ) ?>"><?php _e( 'View Receipt', 'charitable' ) ?></a></td>
				<?php
					/**
					 * Add a cell after the link to the receipt. Any output should be wrapped in <td></td>.
					 *
					 * @since 1.5.0
					 *
					 * @param object $donation The donation as a simple object.
					 */
					do_action( 'charitable_my_donations_table_after_receipt', $donation );
				?>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

<?php endif;

/**
 * @hook    charitable_my_donations_after
 */
do_action( 'charitable_my_donations_after', $donations );
