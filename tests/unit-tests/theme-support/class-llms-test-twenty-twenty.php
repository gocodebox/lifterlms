<?php
/**
 * Test LLMS_Twenty_Twenty theme support class
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Twenty_Twenty extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		update_option( 'template', 'twentytwenty' );
		new LLMS_Theme_Support();

	}

	/**
	 * Tear down the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		update_option( 'template', 'default' );

	}

	/**
	 * Test the hide_meta_output() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_hide_meta_output() {

		$this->assertEquals( array( 'course', 'llms_membership', 'lesson', 'llms_quiz' ), LLMS_Twenty_Twenty::hide_meta_output( array() ) );
		$this->assertEquals( array( 'existing', 'course', 'llms_membership', 'lesson', 'llms_quiz' ), LLMS_Twenty_Twenty::hide_meta_output( array( 'existing' ) ) );

	}

	/**
	 * Test is_page_full_width() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_page_full_width() {

		// Not Set.
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( 'LLMS_Twenty_Twenty', 'is_page_full_width', array( $page_id ) ) );

		// Not full width.
		update_post_meta( $page_id, '_wp_page_template', 'default' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( 'LLMS_Twenty_Twenty', 'is_page_full_width', array( $page_id ) ) );

		// Is full width.
		update_post_meta( $page_id, '_wp_page_template', 'templates/template-full-width.php' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( 'LLMS_Twenty_Twenty', 'is_page_full_width', array( $page_id ) ) );

	}

	public function test_modify_columns_count_defaults() {

		$this->assertEquals( 1, LLMS_Twenty_Twenty::modify_columns_count( 1 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty::modify_columns_count( 2 ) );

	}

	public function test_modify_columns_count_catalogs() {

		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		// LLMS_Install::create_pages();

		global $wp_query;
		$wp_query->queried_object_id = $page_id;

		update_post_meta( $page_id, '_wp_page_template', 'templates/template-full-width.php' );

	}

}
