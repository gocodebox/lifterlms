<?php
/**
 * Output errors when a product is not purchasable
 *
 * @package LifterLMS/Templates/Product
 *
 * @since 3.38.0
 * @since [version] Moved verification logic and notice printing to the LLMS_Shortcode_Checkout class.
 * @version [version]
 *
 * @var LLMS_Product $product Product object of the course or membership.
 */

defined( 'ABSPATH' ) || exit;

if ( 'course' === $product->get( 'type' ) ) {

	$course = new LLMS_Course( $product->post );

	if ( LLMS_Shortcode_Checkout::verify_course_enrollment_has_started( $course ) ) {
		LLMS_Shortcode_Checkout::verify_course_enrollment_has_not_ended( $course );
	}
	LLMS_Shortcode_Checkout::verify_course_enrollment_has_capacity( $course );
}
