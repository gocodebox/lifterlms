<?php
/**
 * Tests for LifterLMS Core Functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_core
 *
 * @since 3.3.1
 * @since 3.35.0 Test ipv6 addresses.
 * @since 3.36.1 Use exception from lifterlms-tests lib.
 * @since 3.37.12 Fix errors thrown due to usage of `llms_section` instead of `section`.
 * @since 3.37.14 When testing `llms_get_post_parent_course()` added tests on other LLMS post types which are not instance of `LLMS_Post_Model`.
 * @since 4.2.0 Add tests for llms_get_completable_post_types() & llms_get_completable_taxonomies().
 * @since 4.4.0 Add tests for `llms_deprecated_function()`.
 * @since 4.4.1 Add tests for `llms_get_enrollable_post_types()` and `llms_get_enrollable_status_check_post_types()`.
 * @since 4.7.0 Add test for `llms_get_dom_document()`.
 * @since 4.10.1 Add test for possible 3rd party cpts conflicts using `llms_get_post()`.
 * @since 4.13.0 Test `llms_get_dom_document()` relying on `mb_convert_encoding()` and not.
 */
class LLMS_Test_Functions_Core extends LLMS_UnitTestCase {

	/**
	 * Test the llms_assoc_array_insert
	 *
	 * @since 3.21.0
	 *
	 * @return void
	 */
	public function test_llms_assoc_array_insert() {

		// base array.
		$array = array(
			'test' => 'asrt',
			'tester' => 'asrtarst',
			'moretest_key' => 'arst',
			'another' => 'arst',
		);

		// after first item.
		$expect = array(
			'test' => 'asrt',
			'new_key' => 'item',
			'tester' => 'asrtarst',
			'moretest_key' => 'arst',
			'another' => 'arst',
		);
		$this->assertEquals( $expect, llms_assoc_array_insert( $array, 'test', 'new_key', 'item' ) );

		// add in the middle.
		$expect = array(
			'test'         => 'asrt',
			'tester'       => 'asrtarst',
			'new_key'      => 'item',
			'moretest_key' => 'arst',
			'another'      => 'arst',
		);
		$this->assertEquals( $expect, llms_assoc_array_insert( $array, 'tester', 'new_key', 'item' ) );

		// requested key doesn't exist so it'll be added to the end.
		$expect = array(
			'test'         => 'asrt',
			'tester'       => 'asrtarst',
			'moretest_key' => 'arst',
			'another'      => 'arst',
			'new_key'      => 'item',
		);
		$this->assertEquals( $expect, llms_assoc_array_insert( $array, 'noexist', 'new_key', 'item' ) );

		// after last item.
		$expect = array(
			'test'         => 'asrt',
			'new_key'      => 'item',
			'tester'       => 'asrtarst',
			'moretest_key' => 'arst',
			'another'      => 'arst',
		);
		$this->assertEquals( $expect, llms_assoc_array_insert( $array, 'another', 'new_key', 'item' ) );

	}

	/**
	 * Test llms_deprecated_function()
	 *
	 * @since 4.4.0
	 *
	 * @expectedDeprecated DEPRECATED
	 *
	 * @return void
	 */
	public function test_llms_deprecated_function() {

		// Add an action where we'll test that all our deprecation data is properly passed.
		add_action( 'deprecated_function_run', array( $this, 'deprecated_function_run_assertions' ), 10, 3 );

		llms_deprecated_function( 'DEPRECATED', '999.999.999', 'REPLACEMENT' );

		remove_action( 'deprecated_function_run', array( $this, 'deprecated_function_run_assertions' ) );

	}

	/**
	 * Callback method used to test `llms_deprecated_function()`.
	 *
	 * @since 4.4.0
	 *
	 * @param string $function    Deprecated function name.
	 * @param string $replacement Deprecated function replacement.
	 * @param string $version     Deprecated version number.
	 * @return void
	 */
	public function deprecated_function_run_assertions( $function, $replacement, $version ) {

		// Our deprecation data should be passed to the core.
		$this->assertEquals( 'DEPRECATED', $function );
		$this->assertEquals( 'REPLACEMENT', $replacement );
		$this->assertEquals( '999.999.999', $version );

	}

	/**
	 * Test llms_get_completable_post_types()
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_llms_get_completable_post_types() {
		$this->assertEquals( array( 'course', 'section', 'lesson' ), llms_get_completable_post_types() );
	}

	/**
	 * Test llms_get_completable_taxonomies()
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function test_llms_get_completable_taxonomies() {
		$this->assertEquals( array( 'course_track' ), llms_get_completable_taxonomies() );
	}


	/**
	 * Test llms_get_core_supported_themes()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_core_supported_themes() {

		$this->assertFalse( empty( llms_get_core_supported_themes() ) );
		$this->assertTrue( is_array( llms_get_core_supported_themes() ) );

	}

	/**
	 * Test llms_get_date_diff()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_date_diff() {

		$this->assertEquals( '18 days', llms_get_date_diff( '2016-05-12', '2016-05-30' ) );
		$this->assertEquals( '1 year, 2 months', llms_get_date_diff( '2016-01-01', '2017-03-25 23:32:32' ) );
		$this->assertEquals( '10 months, 14 days', llms_get_date_diff( '2016-01-01', '2016-11-15' ) );
		$this->assertEquals( '4 years, 24 days', llms_get_date_diff( '2013-03-01', '2017-03-25' ) );
		$this->assertEquals( '3 years, 10 months', llms_get_date_diff( '2013-03-01', '2017-01-25' ) );
		$this->assertEquals( '24 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:01:25' ) );
		$this->assertEquals( '1 second', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:01:02' ) );
		$this->assertEquals( '59 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:02:00' ) );
		$this->assertEquals( '1 minute, 44 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:02:45' ) );
		$this->assertEquals( '1 minute, 14 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:02:15' ) );
		$this->assertEquals( '3 minutes, 59 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:05:00' ) );
		$this->assertEquals( '44 minutes, 33 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:45:34' ) );
		$this->assertEquals( '44 minutes, 33 seconds', llms_get_date_diff( '2016-05-12 01:45:34', '2016-05-12 01:01:01' ) );

	}

	/**
	 * Test llms_get_dom_document()
	 *
	 * @since 4.7.0
	 * @since 4.8.0 Test against HTML strings, HTML documents, strings with character entities, and strings with non-utf8 characters.
	 * @since 4.13.0 Test `llms_get_dom_document()` relying on `mb_convert_encoding()` and not.
	 *               Also, use `$this->assertStringContainsString()` in place of `$this->assertStringContainsString()` to get a better erro message on failures.
	 *
	 * @return void
	 */
	public function test_llms_get_dom_document() {

		/**
		 * Array of test strings
		 *
		 * First value is the input string & the second value is the expected output string.
		 *
		 * @var array[]
		 */
		$tests = array(
			array(
				'simple text string',
				'<p>simple text string</p>',
			),
			array(
				'<h1>html text string</h1><br><div class="test"><em>wow!</em></div>',
				'<h1>html text string</h1><br><div class="test"><em>wow!</em></div>',
			),
			array(
				'Ḷ𝝄𝔯𝚎ɱ ĭ𝓹ᵴǘɱ ժөḻ𝝈ɍ 𝘀𝗂ᴛ.',
				'<p>&#7734;&#120644;&#120111;&#120462;&#625; &#301;&#120057;&#7540;&#472;&#625; &#1386;&#1257;&#7739;&#120648;&#589; &#120320;&#120258;&#7451;.</p>',
			),
			array(
				'Contains &mdash; Char Codes and special – !',
				'<p>Contains &mdash; Char Codes and special &ndash; !</p>',
			),
			array(
				'<!DOCTYPE html><html lang="en-US"><head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width" /></head><body>And &gt;>&gt; a <b>full</b> HTML docum𝞔nt!</body></html>',
				'And &gt;&gt;&gt; a <b>full</b> HTML docum&#120724;nt!',
			),
		);

		// Using `mb_convert_econding()`.
		foreach ( $tests as $test ) {

			$dom = llms_get_dom_document( $test[0] );
			$this->assertTrue( $dom instanceof DOMDocument, $test[1] );
			$this->assertStringContainsString( sprintf( '<body>%s</body></html>', $test[1] ), $dom->saveHTML() );

		}

		// Repeat the same test using "the meta fixer".
		add_filter( 'llms_dom_document_use_mb_convert_encoding', '__return_false' );

		foreach ( $tests as $test ) {

			$dom = llms_get_dom_document( $test[0] );
			$this->assertTrue( $dom instanceof DOMDocument, $test[1] );
			$this->assertStringContainsString( sprintf( '<body>%s</body></html>', $test[1] ), $dom->saveHTML() );

		}

		remove_filter( 'llms_dom_document_use_mb_convert_encoding', '__return_false' );
	}

	/**
	 * Test llms_get_engagement_triggers()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_engagement_triggers() {
		$this->assertFalse( empty( llms_get_engagement_triggers() ) );
		$this->assertTrue( is_array( llms_get_engagement_triggers() ) );
	}

	/**
	 * Test llms_get_engagement_types()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_engagement_types() {
		$this->assertFalse( empty( llms_get_engagement_types() ) );
		$this->assertTrue( is_array( llms_get_engagement_types() ) );
	}

	/**
	 * Test llms_get_enrollable_post_types()
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function test_llms_get_enrollable_post_types() {
		foreach ( llms_get_enrollable_post_types() as $post_type ) {
			$this->assertTrue( is_string( $post_type ) );
			$this->assertTrue( post_type_exists( $post_type ) );
		}
	}

	/**
	 * Test llms_get_enrollable_status_check_post_types()
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function test_llms_get_enrollable_status_check_post_types() {
		foreach ( llms_get_enrollable_status_check_post_types() as $post_type ) {
			$this->assertTrue( is_string( $post_type ) );
			$this->assertTrue( post_type_exists( $post_type ) );
		}
	}

	/**
	 * Test llms_get_open_registration_status()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_open_registration_status() {

		// No value, defaults to no.
		delete_option( 'lifterlms_enable_myaccount_registration' );
		$this->assertEquals( 'no', llms_get_open_registration_status() );

		// Explicitly no.
		update_option( 'lifterlms_enable_myaccount_registration', 'no' );
		$this->assertEquals( 'no', llms_get_open_registration_status() );

		// Explicitly yes.
		update_option( 'lifterlms_enable_myaccount_registration', 'yes' );
		$this->assertEquals( 'yes', llms_get_open_registration_status() );

		// Explicitly yes but filtered off.
		$handler = function( $val ) {
			return 'no';
		};
		add_filter( 'llms_enable_open_registration', $handler );
		$this->assertEquals( 'no', llms_get_open_registration_status() );
		remove_filter( 'llms_enable_open_registration', $handler );

	}

	/**
	 * Test the llms_get_option_page_anchor() function
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_llms_get_option_page_anchor() {

		$id = $this->factory->post->create( array(
			'post_title' => 'The Page Title',
			'post_type'  => 'page',
		) );

		$option_name = 'llms_test_page_anchor';

		// returns empty if option isn't set.
		$this->assertEmpty( llms_get_option_page_anchor( $option_name ) );

		update_option( $option_name, $id );

		// title found in string.
		$this->assertTrue( false !== strpos( llms_get_option_page_anchor( $option_name ), get_the_title( $id ) ) );

		// URL found.
		$this->assertTrue( false !== strpos( llms_get_option_page_anchor( $option_name ), get_the_permalink( $id ) ) );

		// no target found.
		$this->assertTrue( false === strpos( llms_get_option_page_anchor( $option_name, false ), 'target="_blank"' ) );

	}

	/**
	 * Test llms_get_product_visibility_options()
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function test_llms_get_product_visibility_options() {
		$this->assertFalse( empty( llms_get_product_visibility_options() ) );
		$this->assertTrue( is_array( llms_get_product_visibility_options() ) );
	}

	/**
	 * Test llms_filter_input_sanitize_string() when the input var isn't set.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_filter_input_sanitize_string_var_not_set() {

		$this->assertNull( llms_filter_input_sanitize_string( INPUT_POST, uniqid( 'notset_' ) ) );
		$this->assertNull( llms_filter_input_sanitize_string( INPUT_POST, uniqid( 'notset_' ), array( FILTER_REQUIRE_ARRAY ) ) );

	}


	/**
	 * Test llms_filter_input_sanitize_string() when the input var is "empty".
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_filter_input_sanitize_string_var_empty() {

		$tests = array(

			array(
				'',
				'',
			),
			array(
				false,
				false,
			),
			array(
				'0',
				'0',
			),
			array(
				null,
				null,
			),
		);

		foreach ( $tests as $test ) {
			list( $input, $output ) = $test;
			$this->mockPostRequest( compact( 'input' ) );
			$this->assertEquals( $output, llms_filter_input_sanitize_string( INPUT_POST, 'input' ) );
		}

	}

	/**
	 * Test llms_filter_input_sanitize_string().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_filter_input_sanitize_string() {

		$tests = array(
			array(
				'simple text input', // Input.
				'simple text input', // Output with quotes encoded.
				'simple text input', // Output without quotes encoded.
			),
			array(
				'input "with" double quotes.',
				'input &#34;with&#34; double quotes.',
				'input "with" double quotes.',
			),
			array(
				"input 'with' single quotes.",
				"input &#39;with&#39; single quotes.",
				"input 'with' single quotes.",
			),
			array(
				'<a href="#">Solo Tag</a>',
				'Solo Tag',
				'Solo Tag',
			),
			array(
				'Text and <a href="#">a tag</a> and more text',
				'Text and a tag and more text',
				'Text and a tag and more text',
			),
			array(
				'Text and <a href="#">a tag</a> and <b>more tags</b> and "quotes".',
				'Text and a tag and more tags and &#34;quotes&#34;.',
				'Text and a tag and more tags and "quotes".',
			),
			array(
				1,
				'1',
				'1',
			),
			array(
				true,
				'1',
				'1',
			),
			array(
				'234234',
				'234234',
				'234234',
			),
			array(
				'true',
				'true',
				'true',
			),
			array(
				'false',
				'false',
				'false',
			),
			array(
				'null',
				'null',
				'null',
			),
		);

		$types = array(
			INPUT_GET  => 'mockGetRequest',
			INPUT_POST => 'mockPostRequest',
		);
		foreach ( $types as $type => $mock_func ) {

			// Setup FILTER_REQUIRE_ARRAY vars.
			$arr_input            = array();
			$arr_output           = array();
			$arr_output_no_encode = array();

			foreach ( $tests as $test ) {

				list( $input, $output, $output_no_encode ) = $test;
				$this->$mock_func( compact( 'input' ) );

				// Test input with quotes encoded.
				$this->assertEquals( $output, llms_filter_input_sanitize_string( $type, 'input' ), "Input string: {$input}" );

				// Quotes not encoded.
				$this->assertEquals( $output_no_encode, llms_filter_input_sanitize_string( $type, 'input', array( FILTER_FLAG_NO_ENCODE_QUOTES ) ), "Input string: {$input}" );

				// Requesting array when no array submitted results in the filter failing.
				$this->assertFalse( llms_filter_input_sanitize_string( $type, 'input', array( FILTER_REQUIRE_ARRAY ) ), "Input string: {$input}" );

				// Add to FILTER_REQUIRE_ARRAY vars.
				$arr_input[]            = $input;
				$arr_output[]           = $output;
				$arr_output_no_encode[] = $output_no_encode;

			}

			// Test array-related input.
			$this->$mock_func( compact( 'arr_input' ) );

			// Array submitted but FILTER_REQUIRE_ARRAY not passed as an option.
			$this->assertEquals( '', llms_filter_input_sanitize_string( $type, 'arr_input' ) );

			// Array requested.
			$this->assertEquals( $arr_output, llms_filter_input_sanitize_string( $type, 'arr_input', array( FILTER_REQUIRE_ARRAY ) ) );
			$this->assertEquals( $arr_output_no_encode, llms_filter_input_sanitize_string( $type, 'arr_input', array( FILTER_REQUIRE_ARRAY, FILTER_FLAG_NO_ENCODE_QUOTES ) ) );

		}

	}

	/**
	 * Test llms_find_coupon()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_find_coupon() {

		// create a coupon.
		$id = $this->factory->post->create( array(
			'post_title' => 'coopond',
			'post_type'  => 'llms_coupon',
		) );
		$this->assertEquals( $id, llms_find_coupon( 'coopond' ) );

		// create a dup.
		$dup = $this->factory->post->create( array(
			'post_title' => 'coopond',
			'post_type'  => 'llms_coupon',
		) );
		$this->assertEquals( $dup, llms_find_coupon( 'coopond' ) );

		// test dupcheck.
		$this->assertEquals( $id, llms_find_coupon( 'coopond', $dup ) );

		// delete the coupon.
		wp_delete_post( $id );
		wp_delete_post( $dup );
		$this->assertEmpty( llms_find_coupon( 'coopond' ) );

	}

	/**
	 * Test llms_get_enrolled_students()
	 *
	 * @since 3.6.0
	 * @return void
	 */
	function test_llms_get_enrolled_students() {

		$course_id = $this->factory->post->create( array(
			'post_type' => 'course',
		) );

		$students = $this->factory->user->create_many( 25, array( 'role' => 'student' ) );
		$students_copy = $students;
		foreach ( $students as $student_id ) {
			$student = new LLMS_Student( $student_id );
			$student->enroll( $course_id );
		}

		// test basic enrollment query passing in a string.
		$this->assertEquals( $students, llms_get_enrolled_students( $course_id, 'enrolled', 50, 0 ) );
		// test basic enrollment query passing in an array.
		$this->assertEquals( $students, llms_get_enrolled_students( $course_id, array( 'enrolled' ), 50, 0 ) );

		// test pagination.
		$this->assertEquals( array_splice( $students, 0, 10 ), llms_get_enrolled_students( $course_id, 'enrolled', 10, 0 ) );
		$this->assertEquals( array_splice( $students, 0, 10 ), llms_get_enrolled_students( $course_id, 'enrolled', 10, 10 ) );
		$this->assertEquals( $students, llms_get_enrolled_students( $course_id, 'enrolled', 10, 20 ) );

		// should be no one expired.
		$this->assertEquals( array(), llms_get_enrolled_students( $course_id, 'expired', 10, 0 ) );

		// sleeping makes unenrollment tests work.
		sleep( 1 );

		$i = 0;
		$expired = array();
		while ( $i < 5 ) {
			$student = new LLMS_Student( $students_copy[ $i ] );
			$student->unenroll( $course_id, 'any', 'expired' );
			$expired[] = $students_copy[ $i ];
			$i++;
		}

		// test expired alone.
		$this->assertEquals( $expired, llms_get_enrolled_students( $course_id, 'expired', 10, 0 ) );

		// test multiple statuses.
		$this->assertEquals( $students_copy, llms_get_enrolled_students( $course_id, array( 'enrolled', 'expired' ), 50, 0 ) );

	}

	/**
	 * Test llms_get_enrollment_statuses()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_enrollment_statuses() {
		$this->assertFalse( empty( llms_get_enrollment_statuses() ) );
		$this->assertTrue( is_array( llms_get_enrollment_statuses() ) );
	}

	/**
	 * Test llms_get_enrollment_status_name()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_enrollment_status_name() {
		$this->assertNotEquals( 'asrt', llms_get_enrollment_status_name( 'cancelled' ) );
		$this->assertNotEquals( 'cancelled', llms_get_enrollment_status_name( 'Cancelled' ) );
		$this->assertEquals( 'Cancelled', llms_get_enrollment_status_name( 'cancelled' ) );
		$this->assertEquals( 'Cancelled', llms_get_enrollment_status_name( 'Cancelled' ) );
		$this->assertEquals( 'wut', llms_get_enrollment_status_name( 'wut' ) );
	}

	/**
	 * Test llms_get_ip_address()
	 *
	 * @since 3.6.0
	 * @since 3.35.0 Test sanitization and ipv6 addresses.
	 *
	 * @return void
	 */
	public function test_llms_get_ip_address() {

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

		$_SERVER['REMOTE_ADDR'] = '::1';
		$this->assertEquals( '::1', llms_get_ip_address() );
		unset( $_SERVER['REMOTE_ADDR'] );

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, 192.168.1.1, 192.168.1.5';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '::1, ::2';
		$this->assertEquals( '::1', llms_get_ip_address() );
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );

		$_SERVER['HTTP_X_REAL_IP'] = '127.0.0.1';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

		$_SERVER['HTTP_X_REAL_IP'] = '::1';
		$this->assertEquals( '::1', llms_get_ip_address() );
		unset( $_SERVER['HTTP_X_REAL_IP'] );

		$this->assertEquals( '', llms_get_ip_address() );

		$_SERVER['REMOTE_ADDR'] = '127\.0.0.1';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

		$_SERVER['REMOTE_ADDR'] = '127\\/\/\/\.0.0.1';
		$this->assertEquals( '', llms_get_ip_address() );

	}

	/**
	 * Test llms_get_post()
	 *
	 * @since 3.3.1
	 * @since 3.16.11 Unknown.
	 * @since 3.37.12 Fix errors thrown due to usage of `llms_section` instead of `section`.
	 *
	 * @return void
	 */
	public function test_llms_get_post() {

		$types = array(
			'LLMS_Access_Plan' => 'llms_access_plan',
			'LLMS_Coupon'      => 'llms_coupon',
			'LLMS_Course'      => 'course',
			'LLMS_Lesson'      => 'lesson',
			'LLMS_Membership'  => 'llms_membership',
			'LLMS_Order'       => 'llms_order',
			'LLMS_Quiz'        => 'llms_quiz',
			'LLMS_Question'    => 'llms_question',
			'LLMS_Section'     => 'section',
			'LLMS_Transaction' => 'llms_transaction',
		);

		foreach ( $types as $class => $type ) {

			$id = $this->factory->post->create( array(
				'post_type' => $type,
			) );
			$this->assertInstanceOf( $class, llms_get_post( $id ) );

		}

		$this->assertInstanceOf( 'WP_Post', llms_get_post( $this->factory->post->create(), 'post' ) );
		$this->assertNull( llms_get_post( 'fail' ) );
		$this->assertNull( llms_get_post( 0 ) );

	}

	/**
	 * Test llms_get_post() with post types which don't have to be confused with LifterLMS post types
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_llms_get_post_no_conflicts() {

		$types = array(
			'LLMS_Events'      => 'events',
			'LLMS_Certificate' => 'certificate',
			'LLMS_Transaction' => 'transaction',
		);

		foreach ( $types as $class => $type ) {
			register_post_type( $type );
			$id = $this->factory->post->create( array(
				'post_type' => $type,
			) );

			$this->assertNotInstanceOf( $class, llms_get_post( $id ) );
			unregister_post_type( $type );
		}

	}

	/**
	 * Test `llms_get_post_parent_course()`
	 *
	 * @since 3.6.0
	 * @since 3.37.14 Added tests on other LLMS post types which are not instance of `LLMS_Post_Model`.
	 *
	 * @return void
	 */
	public function test_llms_get_post_parent_course() {

		$course = new LLMS_Course( 'new', 'title' );
		$section = new LLMS_Section( 'new', array(
			'post_title' => 'section',
			'meta_input' => array(
				'_llms_parent_course' => $course->get( 'id' )
			),
		) );
		$lesson = new LLMS_Lesson( 'new', array(
			'post_title' => 'lesson',
			'meta_input' => array(
				'_llms_parent_course' => $course->get( 'id' ),
				'_llms_parent_section' => $section->get( 'id' ),
			),
		) );

		foreach ( array( $section, $lesson ) as $obj ) {

			$post = get_post( $obj->get( 'id' ) );

			// pass in post id.
			$this->assertEquals( $course, llms_get_post_parent_course( $post->ID ) );

			// pass in an object.
			$this->assertEquals( $course, llms_get_post_parent_course( $post ) );

		}

		// other non lms post types don't have a parent course.
		$reg_post = $this->factory->post->create();
		$this->assertNull( llms_get_post_parent_course( $reg_post ) );

		// make sure an LLMS post type, which is not an istance of `LLMS_Post_Model` doesn't have a parent course.
		// and no fatals are produced.
		$certificate_post = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);
		$this->assertNull( llms_get_post_parent_course( $certificate_post ) );
	}


	/**
	 * Test llms_get_transaction_statuses()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_get_transaction_statuses() {
		$this->assertFalse( empty( llms_get_transaction_statuses() ) );
		$this->assertTrue( is_array( llms_get_transaction_statuses() ) );
	}

	/**
	 * Test llms_is_site_https()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_is_site_https() {
		update_option( 'home', 'https://is.ssl' );
		$this->assertTrue( llms_is_site_https() );

		update_option( 'home', 'http://is.ssl' );
		$this->assertFalse( llms_is_site_https() );
	}

	/**
	 * Test the llms_parse_bool function
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_llms_parse_bool() {

		$true = array( 'yes', 'on', true, 1, 'true', '1' );

		foreach ( $true as $val ) {
			$this->assertTrue( llms_parse_bool( $val ) );
		}

		$false = array( 'no', 'off', false, 0, 'false', 'something', '', null, '0', array(), array( 'ast' ), array( true ) );

		foreach ( $false as $val ) {
			$this->assertFalse( llms_parse_bool( $val ) );
		}

	}

	/**
	 * Test llms_php_error_constant_to_code()
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function test_llms_php_error_constant_to_code() {

		$errors = array(
			E_ERROR             => 'E_ERROR',
			E_WARNING           => 'E_WARNING',
			E_PARSE             => 'E_PARSE',
			E_NOTICE            => 'E_NOTICE',
			E_CORE_ERROR        => 'E_CORE_ERROR',
			E_CORE_WARNING      => 'E_CORE_WARNING',
			E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
			E_USER_ERROR        => 'E_USER_ERROR',
			E_USER_WARNING      => 'E_USER_WARNING',
			E_USER_NOTICE       => 'E_USER_NOTICE',
			E_STRICT            => 'E_STRICT',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
			E_DEPRECATED        => 'E_DEPRECATED',
			E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
			9999                => 9999,
		);

		foreach ( $errors as $in => $out ) {
			$this->assertEquals( $out, llms_php_error_constant_to_code( $in ) );
		}

	}

	/**
	 * Test llms_redirect_and_exit() func with safe on
	 *
	 * @since 3.19.4
	 * @since 3.34.0 Use exception from lifterlms-tests lib.
	 *
	 * @return void
	 */
	public function test_llms_redirect_and_exit_safe_on() {

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'https://lifterlms.com [302] YES' );
		llms_redirect_and_exit( 'https://lifterlms.com' );

	}

	/**
	 * Test llms_redirect_and_exit() func with safe on
	 *
	 * @since 3.36.1 Use exception from lifterlms-tests lib.
	 *
	 * @return void
	 */
	public function test_llms_redirect_and_exit_safe_off() {

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'https://lifterlms.com [302] NO' );
		llms_redirect_and_exit( 'https://lifterlms.com', array( 'safe' => false ) );

	}

	/**
	 * Test llms_redirect_and_exit() func with safe custom status
	 *
	 * @since 3.36.1 Use exception from lifterlms-tests lib.
	 *
	 * @return void
	 */
	public function test_llms_redirect_and_exit_safe_status() {

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'https://lifterlms.com [301] YES' );
		llms_redirect_and_exit( 'https://lifterlms.com', array( 'status' => 301 ) );

	}

	/**
	 * Test llms_trim_string()
	 *
	 * @since 3.3.1
	 * @since 3.6.0 Unknown.
	 *
	 * @return void
	 */
	public function test_llms_trim_string() {

		$this->assertEquals( 'yasssss', llms_trim_string( 'yasssss' ) );
		$this->assertEquals( 'y...',    llms_trim_string( 'yasssss', 4 ) );
		$this->assertEquals( 'ya.',     llms_trim_string( 'yasssss', 3, '.' ) );
		$this->assertEquals( 'yassss$', llms_trim_string( 'yassss$s', 7, '' ) );

	}

}
