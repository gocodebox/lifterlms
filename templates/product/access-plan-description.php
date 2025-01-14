<?php
/**
 * Single Access Plan Description
 *
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;

// If the plan has no content, don't display anything.
if ( ! $plan->get( 'content' ) ) {
	return;
}

?>
<div class="llms-access-plan-description"><?php echo wp_kses_post( $plan->get( 'content' ) ); ?></div>
