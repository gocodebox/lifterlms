<?php
/**
 * Tests for LifterLMS Custom Post Types
 * @group    LLMS_Post_Types
 * @since   3.13.0
 * @version 3.13.0
 */
class LLMS_Test_Post_Types extends LLMS_UnitTestCase {

	public function test_register_post_taxonomies() {

		LLMS_Post_Types::register_taxonomies();

		$taxonomies = array(
			'course_cat',
			'course_difficulty',
			'course_tag',
			'course_track',
			'membership_cat',
			'membership_tag',
			'llms_product_visibility',
			'llms_access_plan_visibility',
		);

		foreach ( $taxonomies as $name ) {
			// var_dump( sprintf( '%s: %s', $name, taxonomy_exists( $name ) ) );
			$this->assertTrue( taxonomy_exists( $name ) );
		}

	}

	public function test_register_post_types() {

		LLMS_Post_Types::register_post_types();

		$post_types = array(
			'course',
			'section',
			'lesson',
			'llms_membership',
			'llms_engagement',
			'llms_order',
			'llms_transaction',
			'llms_achievement',
			'llms_certificate',
			'llms_my_certificate',
			'llms_email',
			'llms_quiz',
			'llms_question',
			'llms_coupon',
			'llms_voucher',
			'llms_review',
			'llms_access_plan',
		);

		foreach ( $post_types as $name ) {
			$this->assertTrue( post_type_exists( $name ) );
		}

	}

	public function test_register_post_statuses() {

		LLMS_Post_Types::register_post_statuses();

		$statuses = array(
			'llms-completed',
			'llms-active',
			'llms-expired',
			'llms-on-hold',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
			'llms-txn-failed',
			'llms-txn-pending',
			'llms-txn-refunded',
			'llms-txn-succeeded',
		);

		foreach ( $statuses as $name ) {
			$this->assertTrue( ! is_null( get_post_status_object( $name ) ) );
		}

	}

}
