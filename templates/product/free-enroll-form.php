<?php
/**
 * Template for the free enrollment form
 * Displays to logged in users on pricing tables for free access plans if free checkout is not disabled via filter
 *
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 *
 * @property  LLMS_Access_Plan $plan Instance of the plan object.
 *
 * @since    3.4.0
 * @since    3.30.0 Added redirect field
 * @version  3.30.0
 */

defined( 'ABSPATH' ) || exit;

$uid = get_current_user_id();
if ( ! $uid || empty( $plan ) || ! $plan->has_free_checkout() ) {
	return;
}

$redirection = $plan->get_redirection_url();
?>

<form action="" class="llms-free-enroll-form" method="POST">
	<?php
	foreach ( LLMS_Person_Handler::get_available_fields( 'checkout', $uid ) as $field ) :
		$field['type'] = 'hidden';
		?>
		<?php llms_form_field( $field ); ?>
	<?php endforeach; ?>
	<?php wp_nonce_field( 'create_pending_order', '_llms_checkout_nonce' ); ?>
	<input name="action" type="hidden" value="create_pending_order">
	<input name="form" type="hidden" value="free_enroll">
	<input name="llms_agree_to_terms" type="hidden" value="yes">
	<input name="free_checkout_redirect" type="hidden" value="<?php echo $redirection; ?>">
	<input id="llms-plan-id" name="llms_plan_id" type="hidden" value="<?php echo $plan->get( 'id' ); ?>">
	<button class="llms-button-action button" type="submit"><?php echo $plan->get_enroll_text(); ?></button>
</form>
