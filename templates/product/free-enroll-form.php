<?php
/**
 * Template for the free enrollment form
 *
 * Displays to logged in users on pricing tables for free access plans if free checkout is not disabled via filter
 *
 * @package LifterLMS/Templates
 *
 * @since 3.4.0
 * @since 3.30.0 Added redirect field.
 * @since 5.0.0 Use `LLMS_Forms::get_free_enroll_form_html()` in favor of deprecated `LLMS_Person_Handler::get_available_fields()`.
 * @version 5.0.0
 *
 * @property LLMS_Access_Plan $plan Instance of the plan object.
 */

defined( 'ABSPATH' ) || exit;

$uid = get_current_user_id();
if ( ! $uid || empty( $plan ) || ! $plan->has_free_checkout() ) {
	return;
}
?>

<form action="" class="llms-free-enroll-form" method="POST">

	<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo LLMS_Forms::instance()->get_free_enroll_form_html( $plan );
	?>

	<?php wp_nonce_field( 'create_pending_order', '_llms_checkout_nonce' ); ?>

	<input name="action" type="hidden" value="create_pending_order">
	<input name="form" type="hidden" value="free_enroll">
	<input name="llms_agree_to_terms" type="hidden" value="yes">

	<button class="llms-button-action button" type="submit"><?php echo esc_html( $plan->get_enroll_text() ); ?></button>

</form>
