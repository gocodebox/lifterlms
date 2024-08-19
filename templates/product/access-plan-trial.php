<?php
/**
 * Single Access Plan Trial
 *
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;

// If the plan has no trial, don't display anything.
if ( ! $plan->has_trial() ) {
	return;
}

?>
<div class="llms-access-plan-pricing trial">
	<?php if ( $plan->has_trial() ) : ?>
		<div class="llms-access-plan-price">
			<em class="stamp"><?php esc_html_e( 'TRIAL', 'lifterlms' ); ?></em>
			<?php echo wp_kses( $plan->get_price( 'trial_price' ), LLMS_ALLOWED_HTML_PRICES ); ?>
		</div>
		<div class="llms-access-plan-trial"><?php echo esc_html( $plan->get_trial_details() ); ?></div>
	<?php else : ?>
		&nbsp;
	<?php endif; ?>
</div>
