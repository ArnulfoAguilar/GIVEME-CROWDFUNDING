<?php
/**
 * Campaign ended template.
 *
 * This template displays a message for Charitable campaigns that have finished. It will
 * not be used if Charitable is not active.
 *
 * @package Reach
 */

if ( ! reach_has_charitable() ) :
	return;
endif;

$campaign = charitable_get_current_campaign();

if ( $campaign->has_goal() && $campaign->has_achieved_goal() ) :

	$message = _x( 'This campaign successfully reached its funding goal and ended %s ago', 'campaign successfully reached its funding goal and ended x time ago', 'reach' );

elseif ( $campaign->has_goal() && ! $campaign->has_achieved_goal() ) :

	$message = _x( 'This campaign failed to reach its funding goal %s ago', 'campaign failed to reach its funding goal x time ago', 'reach' );

else :

	$message = _x( 'This campaign ended %s ago', 'campaign end x time ago', 'reach' );

endif;
?>
<h3 class="campaign-ended">
	<?php printf( $message, '<span class="time-ago">' . human_time_diff( $campaign->get_end_time() ) . '</span>' ) ?>
</h3>
