<?php
/**
 * Tests for {@see LLMS_Trait_Sales_Page}.
 *
 * @group Traits
 * @group LLMS_Post_Model
 *
 * @since 5.3.0
 */
class LLMS_Test_Sales_Page_Trait extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Trait_Sales_Page
	 */
	protected $mock;

	/**
	 * Setup before running each test in this class.
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 */
	public function set_up() {

		parent::set_up();

		$args = array(
			'post_title' => 'Mock Post with the Sales Page Trait',
		);
		$this->mock = new class( 'new', $args ) extends LLMS_Post_Model {

			use LLMS_Trait_Sales_Page;

			protected $db_post_type = 'course';
			protected $model_post_type = 'course';

			public function __construct( $model, $args = array() ) {

				$this->construct_sales_page();
				parent::__construct( $model, $args );
			}
		};
	}

	/**
	 * Test the `construct_sales_page()` method.
	 *
	 * @since 5.3.0
	 */
	public function test_construct_sales_page() {
		/**
		 * {@see set_up()} should have created a mock object that called {@see LLMS_Trait_Sales_Page::construct_sales_page()}.
		 */
		$properties = $this->mock->get_properties();

		$this->assertArrayHasKey( 'sales_page_content_page_id', $properties );
		$this->assertArrayHasKey( 'sales_page_content_type', $properties );
		$this->assertArrayHasKey( 'sales_page_content_url', $properties );
	}

	/**
	 * Test the `get_sales_page_url()` method.
	 *
	 * @since 5.3.0
	 */
	public function test_get_sales_page_url() {

		# Test "Redirect to WordPress Page".
		$page_id  = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		$expected = get_permalink( $page_id );
		$this->mock->set( 'sales_page_content_type', 'page' );
		$this->mock->set( 'sales_page_content_page_id', $page_id );
		$actual = $this->mock->get_sales_page_url();
		$this->assertEquals( $expected, $actual );

		# Test "Redirect to custom URL".
		$expected = 'https://lifterlms.com';
		$this->mock->set( 'sales_page_content_type', 'url' );
		$this->mock->set( 'sales_page_content_url', $expected );
		$actual = $this->mock->get_sales_page_url();
		$this->assertEquals( $expected, $actual );

		# Test "Display default course content".
		$expected = get_permalink( $this->mock->get( 'id' ) );
		$this->mock->set( 'sales_page_content_type', 'none' );
		$actual = $this->mock->get_sales_page_url();
		$this->assertEquals( $expected, $actual );

		# Test "Show custom content".
		$expected = get_permalink( $this->mock->get( 'id' ) );
		$this->mock->set( 'sales_page_content_type', 'content' );
		$this->mock->set( 'excerpt', 'Please enroll in this course.' );
		$actual = $this->mock->get_sales_page_url();
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test the `has_sales_page_redirect()` method.
	 *
	 * @since 5.3.0
	 */
	public function test_has_sales_page_redirect() {

		# Test "Redirect to WordPress Page".
		$page_id  = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		$this->mock->set( 'sales_page_content_type', 'page' );
		$this->mock->set( 'sales_page_content_page_id', $page_id );
		$actual = $this->mock->has_sales_page_redirect();
		$this->assertTrue( $actual );

		# Test "Redirect to custom URL".
		$this->mock->set( 'sales_page_content_type', 'url' );
		$this->mock->set( 'sales_page_content_url', 'https://lifterlms.com' );
		$actual = $this->mock->has_sales_page_redirect();
		$this->assertTrue( $actual );

		# Test "Display default course content".
		$this->mock->set( 'sales_page_content_type', 'none' );
		$actual = $this->mock->has_sales_page_redirect();
		$this->assertFalse( $actual );

		# Test "Show custom content".
		$this->mock->set( 'sales_page_content_type', 'content' );
		$this->mock->set( 'excerpt', 'Please enroll in this course.' );
		$actual = $this->mock->has_sales_page_redirect();
		$this->assertFalse( $actual );
	}
}
