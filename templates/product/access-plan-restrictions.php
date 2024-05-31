<?php
/**
 * Single Access Plan Restrictions
 *
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 *
 * @property  LLMS_Access_Plan $plan Instance of the plan object.
 *
 * @since     3.23.0
 * @since     3.30.0 Added redirect parameter to `$membership_link`
 * @version   3.30.0
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( $plan->has_availability_restrictions() ) : ?>
	<div class="llms-access-plan-restrictions">
		<em class="stamp"><?php _e( 'MEMBER PRICING', 'lifterlms' ); ?></em>
		<ul>
			<?php
			foreach ( $plan->get_array( 'availability_restrictions' ) as $mid ) :
				$membership_link = get_permalink( $mid );
				$redirection     = $plan->get_redirection_url();
				if ( ! empty( $redirection ) ) {
					$membership_link = add_query_arg(
						array(
							'redirect' => $redirection,
						),
						$membership_link
					);
				}
				?>
				<li><a href="<?php echo $membership_link; ?>"><?php echo get_the_title( $mid ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
