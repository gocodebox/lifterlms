<?php
/**
 * LifterLMS Login Form
 * @since    3.0.0
 * @version  3.0.4 - added layout options
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $redirect ) ) {
	$redirect = get_permalink();
}

if ( ! isset( $layout ) ) {
	$layout = apply_filters( 'llms_login_form_layout', 'columns' );
}

if ( is_user_logged_in() ) { return; }
?>
<?php if ( ! empty( $message ) ) : ?>
	<?php llms_print_notice( $message, 'notice' ); ?>
<?php endif; ?>

<?php llms_print_notices(); ?>

<div class="col-1 llms-person-login-form-wrapper">

	<form action="" class="llms-login" method="POST">

		<h4 class="llms-form-heading"><?php _e( 'Login', 'lifterlms' ); ?></h4>

		<div class="llms-form-fields">

			<?php do_action( 'lifterlms_login_form_start' ); ?>

			<?php foreach ( LLMS_Person_Handler::get_login_fields( $layout ) as $field ) : ?>
				<?php llms_form_field( $field ); ?>
			<?php endforeach; ?>

			<?php wp_nonce_field( 'llms_login_user' ); ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
			<input type="hidden" name="action" value="llms_login_user" />

			<?php do_action( 'lifterlms_login_form_end' ); ?>

		</div>

	</form>

</div>
