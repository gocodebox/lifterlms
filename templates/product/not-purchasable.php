<?php
/**
 * Output errors when a product is not purchasable
 *
 * @package LifterLMS/Templates/Product
 *
 * @since 3.38.0
 * @since [version] Moved course enrollment restriction logic to `LLMS_Course::is_enrollment_restricted()`.
 * @version [version]
 *
 * @var LLMS_Product $product Product object of the course or membership.
 */

defined( 'ABSPATH' ) || exit;

if ( 'course' === $product->get( 'type' ) ) {

	$course             = new LLMS_Course( $product->post );
	$restricted_message = $course->is_enrollment_restricted();

	if ( $restricted_message ) {
		llms_print_notice( $restricted_message, 'error' );
		return;
	}
}
