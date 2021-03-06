<?php
/**
 * Renders the donation details meta box for the Donation post type.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.5.0
 */

global $post;

$logs             = charitable_get_donation( $post->ID )->get_merged_logs();
$date_time_format = get_option( 'date_format' ) . ' - ' . get_option( 'time_format' );

?>
<div id="charitable-donation-log-metabox" class="charitable-metabox">
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Date &amp; Time', 'charitable' ) ?></th>
				<th><?php _e( 'Log', 'charitable' ) ?></th>
			</th>
		</thead>
		<?php foreach ( $logs as $log ) : ?>
		<tr>
			<td><?php echo get_date_from_gmt( date( 'Y-m-d H:i:s', $log['time'] ), $date_time_format ) ?></td>
			<td><?php echo $log['message'] ?></td>
		</tr>
		<?php endforeach ?>
	</table>
</div>
