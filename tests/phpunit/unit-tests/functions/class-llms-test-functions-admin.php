<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @group functions
 * @group admin_functions
 * @group admin
 *
 * @since 3.23.0
 */
class LLMS_Test_Functions_Admin extends LLMS_UnitTestCase {

	/**
	 * Test: llms_get_add_ons()
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_llms_get_add_ons() {

		$res = llms_get_add_ons();

		// Return looks right.
		$this->assertEquals( array( 'categories', 'items' ), array_keys( $res ) );

		// Transient set for caching.
		$this->assertEquals( $res, get_transient( 'llms_products_api_result' ) );

	}

	/**
	 * Test llms_get_add_ons() when an error is encountered.
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_llms_get_add_ons_error() {

		$err = new WP_Error( 'mocked-err', 'Mocked Message', array( 'data' => 'mocked' ) );
		$this->mock_http_request( 'https://lifterlms.com/wp-json/llms/v3/products', $err );

		$res = llms_get_add_ons();

		// Expect mocked error message.
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'api_connection', $res );
		$this->assertWPErrorDataEquals( $err, $res );

		// No transient data.
		$this->assertFalse( get_transient( 'llms_products_api_result' ) );

	}

	/**
	 * Test: llms_get_add_ons() caching mechanisms
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_llms_get_add_ons_with_caching() {

		$mock = array( 'mock' );
		set_transient( 'llms_products_api_result', $mock, DAY_IN_SECONDS );
		$this->assertEquals( $mock, llms_get_add_ons() );

		// Skip cache.
		$this->assertNotEquals( $mock, llms_get_add_ons( false ) );

	}

	/**
	 * Test llms_get_add_on()
	 *
	 * @since 4.21.3
	 * @since 5.0.0 Stop testing against Helper_Add_on.
	 *
	 * @return void
	 */
	public function test_llms_get_add_on() {

		// Fake add-on still works.
		$this->assertTrue( llms_get_add_on( array( 'id' => 'test' ) ) instanceof LLMS_Add_On );

		// Lookup a real add-on via a string.
		$res = llms_get_add_on( 'lifterlms-com-lifterlms', 'id' );
		$this->assertEquals( 'lifterlms-com-lifterlms', $res->get( 'id' ) );

		// // Pass in the whole add-on array.
		// $res = llms_get_add_on( LLMS_Unit_Test_Util::get_private_property_value( $res, 'data' ) );
		// $this->assertEquals( 'lifterlms-com-lifterlms', $res->get( 'id' ) );

		// // Should load the Helper's if found subclass.
		// global $lifterlms_tests;
		// require_once $lifterlms_tests->tests_dir . '/mocks/class-llms-helper-add-on.php';

		// $this->assertTrue( llms_get_add_on( array( 'id' => 'test' ) ) instanceof LLMS_Helper_Add_On );

	}

	/**
	 * test the llms_get_sales_page_types() function
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_llms_get_sales_page_types() {

		$this->assertEquals( array(
			'none'    => 'Display default course content',
			'content' => 'Show custom content',
			'page'    => 'Redirect to WordPress Page',
			'url'     => 'Redirect to custom URL',
		), llms_get_sales_page_types() );

	}

	/**
	 * Test lms_merge_code_button() when no merge codes are passed or exist in the current context.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_merge_code_button_no_codes() {

		$ret = llms_merge_code_button( 'content', false );
		$this->assertEquals( '', $ret );

	}

	/**
	 * Test lms_merge_code_button() when custom merge codes are passed.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_merge_code_button_custom_codes() {

		$ret = llms_merge_code_button( 'content', false, array( '{test}' => 'Merge Code' ) );
		$this->assertStringContains( '<div class="llms-merge-code-wrapper">', $ret );
		$this->assertStringContains( '<div class="llms-merge-codes" data-target="content">', $ret );
		$this->assertStringContains( '<li data-code="{test}">Merge Code</li>', $ret );
		$this->assertStringContains( '</div><!-- .llms-merge-code-wrapper -->', $ret );

	}

	/**
	 * Test lms_merge_code_button() on the `llms_certificate` screen.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_merge_code_button_certs() {

		llms_tests_mock_current_screen( 'llms_certificate' );

		$ret = llms_merge_code_button( 'content', false );

		$this->assertStringContains( '<div class="llms-merge-code-wrapper">', $ret );
		$this->assertStringContains( '<div class="llms-merge-codes" data-target="content">', $ret );

		foreach ( llms_get_certificate_merge_codes() as $code => $desc ) {

			$this->assertStringContains( '<li data-code="' . $code . '">' . $desc . '</li>', $ret );

		}
		$this->assertStringContains( '</div><!-- .llms-merge-code-wrapper -->', $ret );

		llms_tests_reset_current_screen();

	}

}
