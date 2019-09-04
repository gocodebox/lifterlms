<?php
/**
 * Account Edit Template / Form
 *
 * @since    1.0.0
 * @version  3.19.4
 */

defined( 'ABSPATH' ) || exit;

$req_method = ! empty( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : false;
$field_data = 'post' === strtolower( $req_method ) ? $_POST : get_current_user_id(); // phpcs:disable WordPress.Security.NonceVerification.Missing -- Data is sanitized in LLMS_Person_Handler::fill_fields().
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

<?php do_action( 'lifterlms_before_person_edit_account_form' ); ?>

<div class="llms-person-form-wrapper">

	<form method="post" class="llms-person-form edit-account">

		<?php do_action( 'lifterlms_edit_account_start' ); ?>

		<div class="llms-form-fields">

			<?php do_action( 'lifterlms_before_update_fields' ); ?>

			<?php foreach ( LLMS_Person_Handler::get_available_fields( 'account', $field_data ) as $field ) : ?>
				<?php llms_form_field( $field ); ?>
			<?php endforeach; ?>

			<?php do_action( 'lifterlms_after_update_fields' ); ?>

		</div>

		<footer class="llms-form-fields">

			<?php do_action( 'lifterlms_before_update_button' ); ?>

			<?php
			llms_form_field(
				array(
					'columns'     => 3,
					'classes'     => 'llms-button-action',
					'id'          => 'llms_update_person',
					'value'       => apply_filters( 'lifterlms_update_button_text', __( 'Save', 'lifterlms' ) ),
					'last_column' => true,
					'required'    => false,
					'type'        => 'submit',
				)
			);
			?>

			<?php do_action( 'lifterlms_after_update_button' ); ?>

			<?php wp_nonce_field( 'llms_update_person', '_llms_update_person_nonce' ); ?>

			<input name="action" type="hidden" value="llms_update_person">

		</footer>

		<?php do_action( 'lifterlms_edit_account_form_end' ); ?>

	</form>

</div>

<?php
do_action( 'lifterlms_after_person_edit_account_form' );
