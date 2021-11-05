<?php
/**
 * Tests for LifterLMS Custom Post Types
 *
 * @group LLMS_Post_Types
 *
 * @since 3.13.0
 * @since 5.5.0 Addedd tests for deprecated filters of the type "lifterlms_register_post_type_${prefixed_post_type_name}".
 */
class LLMS_Test_Post_Types extends LLMS_UnitTestCase {

	public function test_deregister_sitemap_post_types() {

		$mock = array(
			'post' => true,
			'page' => true,
			'course' => true,
			'lesson' => true,
			'llms_quiz' => true,
			'llms_certificate' => true,
			'llms_my_certificate' => true
		);

		$expect = array(
			'post' => true,
			'page' => true,
			'course' => true,
		);

		$this->assertEquals( $expect, LLMS_Post_Types::deregister_sitemap_post_types( $mock ) );

	}

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

	/**
	 * Test deprecated filters of the type "lifterlms_register_post_type_${prefixed_post_type_name}".
	 *
	 * @expectedDeprecated lifterlms_register_post_type_llms_membership
	 * @expectedDeprecated lifterlms_register_post_type_llms_engagement
	 * @expectedDeprecated lifterlms_register_post_type_llms_order
	 * @expectedDeprecated lifterlms_register_post_type_llms_transaction
	 * @expectedDeprecated lifterlms_register_post_type_llms_achievement
	 * @expectedDeprecated lifterlms_register_post_type_llms_certificate
	 * @expectedDeprecated lifterlms_register_post_type_llms_my_certificate
	 * @expectedDeprecated lifterlms_register_post_type_llms_email
	 * @expectedDeprecated lifterlms_register_post_type_llms_quiz
	 * @expectedDeprecated lifterlms_register_post_type_llms_question
	 * @expectedDeprecated lifterlms_register_post_type_llms_coupon
	 * @expectedDeprecated lifterlms_register_post_type_llms_voucher
	 * @expectedDeprecated lifterlms_register_post_type_llms_review
	 * @expectedDeprecated lifterlms_register_post_type_llms_access_plan
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_deprecated_filters() {

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

		foreach ( $post_types as $post_type ) {

			unregister_post_type( $post_type );
			add_filter( "lifterlms_register_post_type_${post_type}", '__return_empty_array' );
			LLMS_Post_Types::register_post_type( $post_type, array() );
			remove_filter( "lifterlms_register_post_type_${post_type}", '__return_empty_array' );

		}

	}

}
