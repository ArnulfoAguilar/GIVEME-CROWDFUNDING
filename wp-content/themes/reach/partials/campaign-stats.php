<?php
/**
 * Campaign stats.
 *
 * This template will not be used if Charitable is not active.
 *
 * @package Reach
 */

if ( ! reach_has_charitable() ) :
	return;
endif;

$campaign = charitable_get_current_campaign();
?>
<ul class="campaign-stats">
	<li class="campaign-raised">
		<span><?php echo charitable_format_money( $campaign->get_donated_amount() ) ?></span>
		<?php _e( 'Pledged', 'reach' ) ?>		
	</li>
	<li class="campaign-goal">
		<span><?php echo charitable_format_money( $campaign->get_goal() ) ?></span>
		<?php _e( 'Goal', 'reach' ) ?>				
	</li>
	<li class="campaign-backers">
		<span><?php echo $campaign->get_donor_count() ?></span>
		<?php _e( 'Donors', 'reach' ) ?>
	</li>				
</ul>
