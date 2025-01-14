<?php
/**
 * Account Edit Template / Form
 *
 * @since 1.0.0
 * @since 5.0.0 Utilize fields from LLMS_Forms.
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

$form_title  = llms_get_form_title( 'account' );
$form_fields = llms_get_form_html( 'account' );
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

<?php do_action( 'lifterlms_before_person_edit_account_form' ); ?>

<div class="llms-person-form-wrapper">

	<?php if ( $form_title ) : ?>
		<h4 class="llms-form-heading"><?php echo wp_kses_post( $form_title ); ?></h4>
	<?php endif; ?>

	<form method="post" class="llms-person-form edit-account">

		<?php do_action( 'lifterlms_edit_account_start' ); ?>

		<div class="llms-form-fields">

			<?php do_action( 'lifterlms_before_update_fields' ); ?>

			<?php echo wp_kses( $form_fields, LLMS_ALLOWED_HTML_FORM_FIELDS ); ?>

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
