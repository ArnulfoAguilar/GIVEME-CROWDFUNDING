<?php
/**
 * The template used to display the forgot password form. Provided here primarily as a way to make it easier to override using theme templates.
 *
 * @author  Rafe Colton
 * @package Charitable/Templates/Account
 * @since   1.4.0
 * @version 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$form = $view_args['form'];

?>
<div class="charitable-forgot-password-form">
	<?php
	/**
	* @hook charitable_forgot_password_before
	*/
	do_action( 'charitable_forgot_password_before' );

	?>
	<form id="lostpasswordform" class="charitable-form" action="<?php echo wp_lostpassword_url() ?>" method="post">

		<?php do_action( 'charitable_form_before_fields', $form ) ?>

		<div class="charitable-form-fields cf">
			<?php $form->view()->render() ?>
		</div><!-- .charitable-form-fields -->

		<?php do_action( 'charitable_form_after_fields', $form ); ?>
	
		<div class="charitable-form-field charitable-submit-field">
			<input type="submit" name="submit" class="lostpassword-button" value="<?php esc_attr_e( 'Reset Password', 'charitable' ) ?>" />
		</div>

	</form><!-- #lostpasswordform -->
	<?php
	/**
	* @hook charitable_forgot_password_after
	*/
	do_action( 'charitable_forgot_password_after' );
	?>
</div>
