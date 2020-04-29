<?php
/**
 * Output errors when a product is not purchasable
 *
 * @package LifterLMS/Templates/Product
 *
 * @since 3.38.0
 * @version 3.38.0
 *
 * @property LLMS_Product $product Product object of the course or membership.
 */

defined( 'ABSPATH' ) || exit;

if ( 'course' === $product->get( 'type' ) ) :
	$course = new LLMS_Course( $product->post ); ?>

	<?php if ( 'yes' === $course->get( 'enrollment_period' ) ) : ?>

		<?php if ( $course->get( 'enrollment_start_date' ) && ! $course->has_date_passed( 'enrollment_start_date' ) ) : ?>
			<?php llms_print_notice( $course->get( 'enrollment_opens_message' ), 'error' ); ?>
		<?php elseif ( $course->has_date_passed( 'enrollment_end_date' ) ) : ?>
			<?php llms_print_notice( $course->get( 'enrollment_closed_message' ), 'error' ); ?>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( ! $course->has_capacity() ) : ?>
		<?php llms_print_notice( $course->get( 'capacity_message' ), 'error' ); ?>
	<?php endif; ?>

	<?php
endif;
