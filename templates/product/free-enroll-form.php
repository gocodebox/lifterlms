<?php
/**
 * Template for the free enrollment form
 * Displays to logged in users on pricing tables for free access plans
 * if free checkout is not disabled via filter
 *
 * @since    3.4.0
 * @version  3.7.5
 */
defined( 'ABSPATH' ) || exit;
$uid = get_current_user_id();
if ( ! $uid || empty( $plan ) || ! $plan->has_free_checkout() ) {
	return;
}
?>

<form action="" class="llms-free-enroll-form" method="POST">
	<?php foreach ( LLMS_Person_Handler::get_available_fields( 'checkout', $uid ) as $field ) :
		$field['type'] = 'hidden'; ?>
		<?php llms_form_field( $field ); ?>
	<?php endforeach; ?>
	<?php wp_nonce_field( 'create_pending_order' ); ?>
	<input name="action" type="hidden" value="create_pending_order">
	<input name="form" type="hidden" value="free_enroll">
	<input name="llms_agree_to_terms" type="hidden" value="yes">
	<input id="llms-plan-id" name="llms_plan_id" type="hidden" value="<?php echo $plan->get( 'id' ); ?>">
	<button class="llms-button-action button" type="submit"><?php echo $plan->get_enroll_text(); ?></button>
</form>
