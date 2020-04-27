<?php
/**
 * Product (Course & Membership) Pricing Table Template
 *
 * @package LifterLMS/Templates/Product
 *
 * @since 3.0.0
 * @version 3.23.0
 *
 * @property LLMS_Product $product      Product object of the course or membership.
 * @property bool         $is_enrolled  Determines if current viewer is enrolled in $product.
 * @property bool         $purchaseable Determines if current product is purchasable.
 * @property bool         $has_free     Determines if any free access plans are available for the product.
 */

defined( 'ABSPATH' ) || exit;

$free_only = ( $has_free && ! $purchaseable );
?>

<?php if ( ! $is_enrolled && ( $purchaseable || $has_free ) ) : ?>

	<?php
		/**
		 * Run prior to output of a course or membership pricing table.
		 *
		 * @since Unknown
		 *
		 * @param int $id WP_Post ID of the course or membership.
		 */
		do_action( 'lifterlms_before_access_plans', $product->get( 'id' ) );
	?>

	<section class="llms-access-plans cols-<?php echo $product->get_pricing_table_columns_count( $free_only ); ?>">

		<?php
			/**
			 * Run prior to listing access plans.
			 *
			 * @since Unknown
			 *
			 * @param int $id WP_Post ID of the course or membership.
			 */
			do_action( 'lifterlms_before_access_plans_loop', $product->get( 'id' ) );
		?>

		<?php foreach ( $product->get_access_plans( $free_only ) as $plan ) : ?>

			<?php
				/**
				 * Outputs a single access plan
				 *
				 * Hooked: llms_template_access_plan - 10
				 *
				 * @since Unknown
				 *
				 * @param LLMS_Access_Plan $plan Access plan object
				 */
				do_action( 'llms_access_plan', $plan );
			?>

		<?php endforeach; ?>

		<?php
			/**
			 * Run prior to listing access plans.
			 *
			 * @since Unknown
			 *
			 * @param int $id WP_Post ID of the course or membership.
			 */
			do_action( 'lifterlms_after_access_plans_loop', $product->get( 'id' ) );
		?>

	</section>

	<?php
		/**
		 * Run after output of a course or membership pricing table.
		 *
		 * @since Unknown
		 *
		 * @param int $id WP_Post ID of the course or membership.
		 */
		do_action( 'lifterlms_after_access_plans', $product->get( 'id' ) );
	?>

<?php elseif ( ! $is_enrolled ) : ?>

	<?php
		/**
		 * Pricing table output when the user is not enrolled but the product is not purchaseable.
		 *
		 * @since Unknown
		 *
		 * @param int $id WP_Post ID of the course or membership.
		 */
		do_action( 'lifterlms_product_not_purchasable', $product->get( 'id' ) );
	?>

	<?php
	if ( 'course' === $product->get( 'type' ) ) :
		$course = new LLMS_Course( $product->post );
		?>
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
