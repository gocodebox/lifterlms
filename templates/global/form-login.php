<?php
/**
 * LifterLMS Login Form
 * @since    3.0.0
 * @version  3.21.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! isset( $redirect ) ) {
	$redirect = get_permalink();
}

if ( ! isset( $layout ) ) {
	$layout = apply_filters( 'llms_login_form_layout', 'columns' );
}

if ( is_user_logged_in() ) {
	return;
}

if ( ! empty( $message ) ) {
	llms_print_notice( $message, 'notice' );
}
?>

<?php llms_print_notices(); ?>

<?php do_action( 'llms_before_person_login_form' ); ?>

<div class="col-1 llms-person-login-form-wrapper">

	<form action="" class="llms-login" method="POST">

		<h4 class="llms-form-heading"><?php _e( 'Login', 'lifterlms' ); ?></h4>

		<div class="llms-form-fields">

			<?php do_action( 'lifterlms_login_form_start' ); ?>

			<?php foreach ( LLMS_Person_Handler::get_login_fields( $layout ) as $field ) : ?>
				<?php llms_form_field( $field ); ?>
			<?php endforeach; ?>

			<?php wp_nonce_field( 'llms_login_user', '_llms_login_user_nonce' ); ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
			<input type="hidden" name="action" value="llms_login_user" />

			<?php do_action( 'lifterlms_login_form_end' ); ?>

		</div>

	</form>

</div>

<?php do_action( 'llms_after_person_login_form' ); ?>
