<?php
/**
 * Test LLMS_Twenty_Twenty theme support class
 *
 * @package LifterLMS/Tests
 *
 * @group theme_support
 *
 * @since [version]
 */
class LLMS_Test_Twenty_Twenty_One extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		update_option( 'template', 'twentytwentyone' );
		$support = new LLMS_Theme_Support();
		$support->includes();

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
	 * Remove all the header actions setup by `handle_page_header_wrappers()`.
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_max_width_class() {
		$this->assertEquals( array( 'mock-class', 'default-max-width' ), LLMS_Twenty_Twenty_One::add_max_width_class( array( 'mock-class' ) )  );
	}

	/**
	 * Test add_pagination_classes()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_pagination_classes() {
		$this->assertEquals( array( 'mock-class', 'navigation', 'pagination' ), LLMS_Twenty_Twenty_One::add_pagination_classes( array( 'mock-class' ) )  );
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
