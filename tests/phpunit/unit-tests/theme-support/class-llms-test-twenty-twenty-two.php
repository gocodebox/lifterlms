<?php
/**
 * Test LLMS_Twenty_Twenty_Two theme support class
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 * @group twenty_twenty_two
 *
 * @since [version]
 */
class LLMS_Test_Twenty_Twenty_Two extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		update_option( 'template', 'twentytwentytwo' );
		$support = new LLMS_Theme_Support();
		$support->includes();

	}

	/**
	 * Tear down the test case.
	 *
	 * @since [version]
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		update_option( 'template', 'default' );

	}

	/**
	 * Remove all the header actions setup by `handle_page_header_wrappers()`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function remove_header_actions() {

		remove_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap' ), 11 );
		remove_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap_end' ), 99999999 );
		remove_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper' ), -1 );
		remove_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper_end' ), 99999998 );

	}

	/**
	 * Test handle_page_header_wrappers() when the archive title is disabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_page_header_wrappers_no_title() {

		$this->remove_header_actions();
		add_filter( 'lifterlms_show_page_title', '__return_false' );

		LLMS_Twenty_Twenty_Two::handle_page_header_wrappers();

		$this->assertFalse( has_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap_end' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper_end' ) ) );

		remove_filter( 'lifterlms_show_page_title', '__return_false' );

	}

	/**
	 * Test handle_page_header_wrappers() when there's no archive description
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_page_header_wrappers_no_desc() {

		$this->remove_header_actions();

		LLMS_Twenty_Twenty_Two::handle_page_header_wrappers();

		$this->assertEquals( 11, has_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap' ) ) );
		$this->assertEquals( 99999999, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap_end' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper_end' ) ) );

		$this->remove_header_actions();

	}

	/**
	 * Test handle_page_header_wrappers() when there is an archived description
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_page_header_wrappers_title_and_desc() {

		$this->remove_header_actions();

		// Output a description.
		$handler = function( $desc ) {
			return 'Archive description';
		};
		add_filter( 'llms_archive_description', $handler );

		LLMS_Twenty_Twenty_Two::handle_page_header_wrappers();

		$this->assertEquals( 11, has_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap' ) ) );
		$this->assertEquals( 99999999, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'page_header_wrap_end' ) ) );
		$this->assertEquals( -1, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper' ) ) );
		$this->assertEquals( 99999998, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_Two', 'output_archive_description_wrapper_end' ) ) );

		$this->remove_header_actions();

		remove_filter( 'llms_archive_description', $handler );

	}

	/**
	 * Test modify_columns_count()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_modify_columns_count() {

		$this->assertEquals( 1, LLMS_Twenty_Twenty_Two::modify_columns_count( 1 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty_Two::modify_columns_count( 2 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty_Two::modify_columns_count( 3 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty_Two::modify_columns_count( 999 ) );

	}

}
