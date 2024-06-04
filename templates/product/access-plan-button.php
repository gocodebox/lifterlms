<?php
/**
 * Single Access Plan Button.
 *
 * @property LLMS_Access_Plan $plan Instance of the LLMS_Access_Plan.
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since 3.23.0
 * @since 4.2.0 Added `llms_display_free_enroll_form` filter hook.
 * @version 4.2.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
/**
 * Filter the displaying of the free enroll form.
 *
 * @since 4.2.0
 *
 * @param boolean          $display Whether or not displaying the free enroll form.
 * @param LLMS_Access_Plan $plan    Instance of the LLMS_Access_Plan.
 */
if ( apply_filters( 'llms_display_free_enroll_form', get_current_user_id() && $plan->has_free_checkout() && $plan->is_available_to_user(), $plan ) ) :
	?>
	<?php llms_get_template( 'product/free-enroll-form.php', compact( 'plan' ) ); ?>
<?php else : ?>
	<a class="llms-button-action button" href="<?php echo esc_url( $plan->get_checkout_url() ); ?>"><?php echo esc_html( $plan->get_enroll_text() ); ?></a>
<?php endif; ?>
