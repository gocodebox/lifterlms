<?php
/**
 * Test LLMS_Twenty_Twenty theme support class
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 *
 * @since 3.37.0
 */
class LLMS_Test_Twenty_Twenty extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.0
	 * @since 4.3.0 Update theme support class instantiation.
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		update_option( 'template', 'twentytwenty' );
		$support = new LLMS_Theme_Support();
		$support->includes();

	}

	/**
	 * Tear down the test case.
	 *
	 * @since 3.37.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		update_option( 'template', 'default' );

	}

	/**
	 * Test the hide_meta_output() method.
	 *
	 * @since 3.37.0
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
	 * @since 3.37.0
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

	/**
	 * Default values for column counts will return 1 (default "thin" template)
	 *
	 * @since 3.37.0
	 *
	 * @return [type]
	 */
	public function test_modify_columns_count_defaults() {

		$this->assertEquals( 1, LLMS_Twenty_Twenty::modify_columns_count( 1 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty::modify_columns_count( 2 ) );

	}

	/**
	 * Modify columns on catalogs. Returns 1 for default template and default column values for full width templates.
	 *
	 * @since 3.37.0
	 *
	 * @return [type]
	 */
	public function test_modify_columns_count() {

		LLMS_Install::create_pages();
		LLMS_Install::create_visibilities();

		foreach ( array( 'courses', 'memberships', 'checkout' ) as $page ) {

			$page_id = llms_get_page_id( $page );
			$url = get_permalink( $page_id );

			$this->go_to( $url );
			$this->assertEquals( 1, LLMS_Twenty_Twenty::modify_columns_count( 2 ) );

			update_post_meta( $page_id, '_wp_page_template', 'templates/template-full-width.php' );
			$this->assertEquals( 2, LLMS_Twenty_Twenty::modify_columns_count( 2 ) );

		}

	}

}
