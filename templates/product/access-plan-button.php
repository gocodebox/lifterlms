<?php
/**
 * Single Access Plan Button
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php if ( get_current_user_id() && $plan->has_free_checkout() && $plan->is_available_to_user() ) : ?>
	<?php llms_get_template( 'product/free-enroll-form.php', compact( 'plan' ) ); ?>
<?php else : ?>
	<a class="llms-button-action button" href="<?php echo $plan->get_checkout_url(); ?>"><?php echo $plan->get_enroll_text(); ?></a>
<?php endif; ?>
