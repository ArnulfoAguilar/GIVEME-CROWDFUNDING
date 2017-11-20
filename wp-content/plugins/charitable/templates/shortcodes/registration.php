<?php
/**
 * The template used to display the registration form.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Account
 * @since   1.0.0
 * @version 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$form = $view_args['form'];

/**
 * @hook    charitable_user_registration_before
 */
do_action( 'charitable_user_registration_before', $form );

?>
<form method="post" id="charitable-registration-form" class="charitable-form">
	<?php
	/**
	 * @hook    charitable_form_before_fields
	 */
	do_action( 'charitable_form_before_fields', $form );

	?>
	<div class="charitable-form-fields cf">
		<?php $form->view()->render() ?>
	</div><!-- .charitable-form-fields -->
	<?php

	/**
	 * @hook    charitable_form_after_fields
	 */
	do_action( 'charitable_form_after_fields', $form );

	?>
	<div class="charitable-form-field charitable-submit-field">
		<button class="button button-primary" type="submit" name="register"><?php esc_attr_e( 'Register', 'charitable' ) ?></button>
	</div>
</form><!-- #charitable-registration-form -->
<?php

/**
 * @hook    charitable_user_registration_after
 */
do_action( 'charitable_user_registration_after', $form );
