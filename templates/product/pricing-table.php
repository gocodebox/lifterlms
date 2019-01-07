<?php
/**
 * Product (Course & Membership) Pricing Table Template
 * @property  obj   $product       WP_Product object
 * @property  bool  $is_enrolled   determines if current viewer is enrolled in $product
 * @property  bool  $purchaseable  determines if current product is purchaseable
 * @property  bool  $has_free      determines if any free access plans are avialable for the product
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.0.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
$free_only = ( $has_free && ! $purchaseable );
?>

<?php if ( ! apply_filters( 'llms_product_pricing_table_enrollment_status', $is_enrolled ) && ( $purchaseable || $has_free ) ) : ?>

	<?php do_action( 'lifterlms_before_access_plans', $product->get( 'id' ) ); ?>

	<section class="llms-access-plans cols-<?php echo $product->get_pricing_table_columns_count( $free_only ) ?>">

		<?php do_action( 'lifterlms_before_access_plans_loop', $product->get( 'id' ) ); ?>

		<?php foreach ( $product->get_access_plans( $free_only ) as $plan ) : ?>

			<?php
				/**
				 * llms_access_plan
				 * @hooked llms_template_access_plan - 10
				 */
				do_action( 'llms_access_plan', $plan );
			?>

		<?php endforeach; ?>

		<?php do_action( 'lifterlms_after_access_plans_loop', $product->get( 'id' ) ); ?>

	</section>

	<?php do_action( 'lifterlms_after_access_plans', $product->get( 'id' ) ); ?>

<?php elseif ( ! $is_enrolled ) : ?>

	<?php do_action( 'lifterlms_product_not_purchasable', $product->get( 'id' ) ); ?>

	<?php if ( 'course' === $product->get( 'type' ) ) :
		$course = new LLMS_Course( $product->post ); ?>
		<?php if ( 'yes' === $course->get( 'enrollment_period' ) ) : ?>
			<?php if ( ! $course->has_date_passed( 'enrollment_start_date' ) ) : ?>
				<?php llms_print_notice( $course->get( 'enrollment_opens_message' ), 'notice' ); ?>
			<?php elseif ( $course->has_date_passed( 'enrollment_end_date' ) ) : ?>
				<?php llms_print_notice( $course->get( 'enrollment_closed_message' ), 'error' ); ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( ! $course->has_capacity() ) : ?>
			<?php llms_print_notice( $course->get( 'capacity_message' ), 'error' ); ?>
		<?php endif; ?>
	<?php endif; ?>

<?php endif; ?>
