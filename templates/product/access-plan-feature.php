<?php
/**
 * Single Access Plan Featured Tab
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="llms-access-plan-featured">
	<?php if ( $plan->is_featured() ) : ?>
		<?php echo apply_filters( 'lifterlms_featured_access_plan_text', __( 'FEATURED', 'lifterlms' ), $plan ); ?>
	<?php else : ?>
		&nbsp;
	<?php endif; ?>
</div>
