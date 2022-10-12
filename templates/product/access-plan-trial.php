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

if ( ! $plan->has_trial() ) {
	return;
}

?>
<div class="llms-access-plan-pricing trial">
	<div class="llms-access-plan-price">
		<em class="stamp"><?php _e( 'TRIAL', 'lifterlms' ); ?></em>
		<?php echo $plan->get_price( 'trial_price' ); ?>
	</div>
	<div class="llms-access-plan-trial"><?php echo $plan->get_trial_details(); ?></div>
</div>
