<?php
/**
 * LifterLMS Login Form
 *
 * @package LifterLMS/Templates
 *
 * @since 3.0.0
 * @since 5.0.0 Moved setup logic for passed arguments to the function llms_get_login_form().
 * @version 5.0.0
 *
 * @param string $message (Optional) Messages to display before login form.
 * @param string $redirect (Optional) URL to redirect to after login.
 * @param string $layout (Optional) Form layout [columns|stacked].
 */

defined( 'ABSPATH' ) || exit;
?>

<?php llms_print_notices(); ?>

<?php
	/**
	 * Fire an action prior to the output of the login form.
	 *
	 * @since Unknown
	 */
	do_action( 'llms_before_person_login_form' );
?>

<div class="llms-person-login-form-wrapper">

	<form action="" class="llms-login" method="POST">

		<h4 class="llms-form-heading"><?php esc_html_e( 'Login', 'lifterlms' ); ?></h4>

		<div class="llms-form-fields">

			<?php
				/**
				 * Fire an action prior to the output of the login form fields.
				 *
				 * @since Unknown
				 */
				do_action( 'lifterlms_login_form_start' );
			?>

			<?php foreach ( LLMS_Person_Handler::get_login_fields( $layout ) as $field ) : ?>
				<?php llms_form_field( $field ); ?>
			<?php endforeach; ?>

			<?php wp_nonce_field( 'llms_login_user', '_llms_login_user_nonce' ); ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />
			<input type="hidden" name="action" value="llms_login_user" />

			<?php
				/**
				 * Fire an action after the output of the login form fields.
				 *
				 * @since Unknown
				 */
				do_action( 'lifterlms_login_form_end' );
			?>

		</div>

	</form>

</div>

<?php
	/**
	 * Fire an action after the output of the login form.
	 *
	 * @since Unknown
	 */
	do_action( 'llms_after_person_login_form' );
?>
