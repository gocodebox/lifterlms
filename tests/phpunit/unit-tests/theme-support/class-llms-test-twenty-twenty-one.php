<?php
/**
 * Test LLMS_Twenty_Twenty theme support class
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 *
 * @since 4.10.0
 */
class LLMS_Test_Twenty_Twenty_One extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 4.10.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		update_option( 'template', 'twentytwentyone' );
		$support = new LLMS_Theme_Support();
		$support->includes();

	}

	/**
	 * Tear down the test case.
	 *
	 * @since 4.10.0
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
	 * @since 4.10.0
	 *
	 * @return void
	 */
	protected function remove_header_actions() {

		remove_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap' ), 11 );
		remove_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap_end' ), 99999999 );
		remove_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper' ), -1 );
		remove_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper_end' ), 99999998 );

	}

	/**
	 * Test add_max_width_class()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_add_max_width_class() {
		$this->assertEquals( array( 'mock-class', 'default-max-width' ), LLMS_Twenty_Twenty_One::add_max_width_class( array( 'mock-class' ) )  );
	}

	/**
	 * Test add_pagination_classes()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_add_pagination_classes() {
		$this->assertEquals( array( 'mock-class', 'navigation', 'pagination' ), LLMS_Twenty_Twenty_One::add_pagination_classes( array( 'mock-class' ) )  );
	}

	/**
	 * Test summary
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_handle_certificate_title_not_a_cert() {

		global $post;

		$types = array(
			'post' => array( 10, 'Untitled' ),
			'llms_certificate' => array( false, '' ),
			'llms_my_certificate' => array( false, '' ),
		);

		// Mock the filter callback function.
		function twenty_twenty_one_post_title( $title ) {
			return 'Untitled';
		}

		foreach ( $types as $post_type => $data ) {

			add_filter( 'the_title', 'twenty_twenty_one_post_title' );

			list( $expect_priority, $expected_title ) = $data;

			$post = $this->factory->post->create_and_get( array( 'post_type' => $post_type, 'post_title' => '' ) );

			LLMS_Twenty_Twenty_One::handle_certificate_title();

			$this->assertEquals( $expect_priority, has_filter( 'the_title', 'twenty_twenty_one_post_title' ) );
			$this->assertEquals( $expected_title, get_the_title() );

		}

		$post = null;

	}

	/**
	 * Test handle_page_header_wrappers() when the archive title is disabled.
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_handle_page_header_wrappers_no_title() {

		$this->remove_header_actions();
		add_filter( 'lifterlms_show_page_title', '__return_false' );

		LLMS_Twenty_Twenty_One::handle_page_header_wrappers();

		$this->assertFalse( has_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap_end' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper_end' ) ) );

		remove_filter( 'lifterlms_show_page_title', '__return_false' );

	}

	/**
	 * Test handle_page_header_wrappers() when there's no archive description
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_handle_page_header_wrappers_no_desc() {

		$this->remove_header_actions();

		LLMS_Twenty_Twenty_One::handle_page_header_wrappers();

		$this->assertEquals( 11, has_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap' ) ) );
		$this->assertEquals( 99999999, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap_end' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper' ) ) );
		$this->assertFalse( has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper_end' ) ) );

		$this->remove_header_actions();

	}

	/**
	 * Test handle_page_header_wrappers() when there is an archived description
	 *
	 * @since 4.10.0
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

		LLMS_Twenty_Twenty_One::handle_page_header_wrappers();

		$this->assertEquals( 11, has_action( 'lifterlms_before_main_content', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap' ) ) );
		$this->assertEquals( 99999999, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'page_header_wrap_end' ) ) );
		$this->assertEquals( -1, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper' ) ) );
		$this->assertEquals( 99999998, has_action( 'lifterlms_archive_description', array( 'LLMS_Twenty_Twenty_One', 'output_archive_description_wrapper_end' ) ) );

		$this->remove_header_actions();

		remove_filter( 'llms_archive_description', $handler );

	}

	/**
	 * Test modify_columns_count()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_modify_columns_count() {

		$this->assertEquals( 1, LLMS_Twenty_Twenty_One::modify_columns_count( 1 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty_One::modify_columns_count( 2 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty_One::modify_columns_count( 3 ) );
		$this->assertEquals( 1, LLMS_Twenty_Twenty_One::modify_columns_count( 999 ) );

	}

	/**
	 * Test maybe_disable_post_navigation()
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_maybe_disable_post_navigation() {

		global $post;
		$temp = $post;

		$tests = array(
			'post'            => 'default html',
			'course'          => '',
			'llms_membership' => '',
			'lesson'          => '',
			'llms_quiz'       => '',
		);

		foreach ( $tests as $post_type => $expected ) {

			$post = $this->factory->post->create( compact( 'post_type' ) );
			$this->assertEquals( $expected, LLMS_Twenty_Twenty_One::maybe_disable_post_navigation( 'default html' ) );

		}

		$post = $temp;

	}

}
