<?php
/**
 * Single Access Plan Restrictions
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php if ( $plan->has_availability_restrictions() ) : ?>
	<div class="llms-access-plan-restrictions">
		<em class="stamp"><?php _e( 'MEMBER PRICING', 'lifterlms' ); ?></em>
		<ul>
			<?php foreach ( $plan->get_array( 'availability_restrictions' ) as $mid ) : ?>
				<li><a href="<?php echo get_permalink( $mid ); ?>"><?php echo get_the_title( $mid ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
