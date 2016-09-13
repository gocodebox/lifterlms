<?php
/**
 * LifterLMS Login Form
 * @version  3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $redirect ) ) {
	$redirect = get_permalink();
}

if ( is_user_logged_in() ) { return; }
?>
<?php if ( ! empty( $message ) ) : ?>
	<?php llms_print_notice( $message, 'notice' ); ?>
<?php endif; ?>

<form action="" class="llms-login" method="POST">

	<h4 class="llms-form-heading"><?php _e( 'Login', 'lifterlms' ); ?></h4>

	<div class="llms-form-fields">

		<?php do_action( 'lifterlms_login_form_start' ); ?>

		<?php foreach ( LLMS_Person_Handler::get_login_fields() as $field ) : ?>
			<?php llms_form_field( $field ); ?>
		<?php endforeach; ?>

		<?php wp_nonce_field( 'llms_login_user' ); ?>
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		<input type="hidden" name="action" value="llms_login_user" />

		<?php do_action( 'lifterlms_login_form_end' ); ?>

	</div>

</form>
