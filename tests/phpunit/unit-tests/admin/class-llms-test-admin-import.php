<?php
/**
 * Tests for LLMS_Admin_Review class
 *
 * @package LifterLMS_Tests/Admin
 *
 * @group admin
 * @group admin_import
 *
 * @since 3.35.0
 * @since 3.37.8 Update path to assets directory.
 * @since 4.7.0 Test success message generation.
 * @since 4.8.0 Move includes to `setUpBeforeClass()` method.
 */
class LLMS_Test_Admin_Import extends LLMS_UnitTestCase {

	/**
	 * Setup before class.
	 *
	 * @since 4.8.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.import.php';
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-export-api.php';

	}

	/**
	 * Setup test case.
	 *
	 * @since 3.35.0
	 * @since 4.8.0 Move includes to `set_up_before_class()` method.
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->import = new LLMS_Admin_Import();

	}

	/**
	 * Tear down test case.
	 *
	 * @since 3.35.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		unset( $_FILES['llms_import'] );

	}

	/**
	 * Mock a file upload for some test data.
	 *
	 * @since 3.35.0
	 *
	 * @param int $err Mock a PHP file upload error code, see https://www.php.net/manual/en/features.file-upload.errors.php.
	 * @param string $import Filename to use for the import, see `import-*.json` files in the `tests/assets` directory.
	 * @return void
	 */
	private function mock_file_upload( $err = 0, $import = null ) {

		$file = is_null( $import ) ? LLMS_PLUGIN_DIR . 'sample-data/sample-course.json' : $import;

		$_FILES['llms_import'] = array(
			'name' => basename( $file ),
			'tmp_name' => $file,
			'type' => 'application/json',
			'error' => $err,
			'size' => filesize( $file ),
		);

	}

	/**
	 * Test the add_help_tabs() method.
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_add_help_tabs() {

		// Not on the right screen.
		$this->assertFalse( $this->import->add_help_tabs() );

		// On the right screen.
		llms_tests_mock_current_screen( 'lifterlms_page_llms-import' );

		$screen = $this->import->add_help_tabs();

		// Tab has been added.
		$tab_id = 'llms_import_overview';
		$tab = $screen->get_help_tab( $tab_id );

		$this->assertEquals( $tab_id, $tab['id'] );

		// Has sidebar content.
		$this->assertStringContains( 'Import Documentation', $screen->get_help_sidebar() );

		llms_tests_reset_current_screen();

	}

	/**
	 * Test cloud_import() errors from nonce
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_error_nonce() {

		// No nonce.
		$this->assertFalse( $this->import->cloud_import() );

		// Invalid nonce.
		$this->mockPostRequest( array(
			'llms_cloud_importer_nonce' => 'fake',
		) );
		$this->assertFalse( $this->import->cloud_import() );

	}

	/**
	 * Test cloud_import() user permission errors
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_error_permissions() {

		$this->mockPostRequest( array(
			'llms_cloud_importer_nonce' => wp_create_nonce( 'llms-cloud-importer' ),
		) );
		$this->assertFalse( $this->import->cloud_import() );

	}

	/**
	 * Test cloud_import() missing necessary data
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_error_no_course_id() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'llms_cloud_importer_nonce' => wp_create_nonce( 'llms-cloud-importer' ),
		) );
		$res = $this->import->cloud_import();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms-cloud-import-missing-id', $res );

	}

	/**
	 * Test cloud_import() with an api errors
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_error_api() {

		$handler = function( $preempt ) {
			return new WP_Error( 'mocked', 'Mocked error.' );
		};

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'llms_cloud_import_course_id' => 1,
			'llms_cloud_importer_nonce'   => wp_create_nonce( 'llms-cloud-importer' ),
		) );

		add_filter( 'pre_http_request', $handler );

		$res = $this->import->cloud_import();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'mocked', $res );

		remove_filter( 'pre_http_request', $handler );

	}

	/**
	 * Test cloud_import() with a real API error from submitting invalid ids
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_error_api_real() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'llms_cloud_import_course_id' => 1,
			'llms_cloud_importer_nonce'   => wp_create_nonce( 'llms-cloud-importer' ),
		) );

		$res = $this->import->cloud_import();

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'not-found', $res );

	}

	/**
	 * Test cloud_import() with a generator error
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_error_generator() {

		$handler = function( $preempt ) {
			return array( 'fake api response' );
		};

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'llms_cloud_import_course_id' => 1,
			'llms_cloud_importer_nonce'   => wp_create_nonce( 'llms-cloud-importer' ),
		) );

		add_filter( 'pre_http_request', $handler );

		$res = $this->import->cloud_import();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'missing-generator', $res );

		remove_filter( 'pre_http_request', $handler );

	}

	/**
	 * Test cloud_import() success
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_cloud_import_success() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'llms_cloud_import_course_id' => 33579, // Free Course Lead Magnet Template.
			'llms_cloud_importer_nonce'   => wp_create_nonce( 'llms-cloud-importer' ),
		) );

		$this->assertTrue( $this->import->cloud_import() );

	}

	/**
	 * Test enqueue() method
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_enqueue() {

		$slug = 'llms-admin-importer';

		$this->assertNull( $this->import->enqueue() );
		$this->assertAssetNotRegistered( 'style', $slug );
		$this->assertAssetNotEnqueued( 'style', $slug );

		llms_tests_mock_current_screen( 'lifterlms_page_llms-import' );

		$this->assertTrue( $this->import->enqueue() );

		$this->assertAssetIsRegistered( 'style', $slug );
		$this->assertAssetIsEnqueued( 'style', $slug );

		llms_tests_reset_current_screen();

	}

	/**
	 * Test get_screen()
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_get_screen() {

		// Wrong screen.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->import, 'get_screen' ) );

		llms_tests_mock_current_screen( 'admin.php' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->import, 'get_screen' ) );

		// Right screen.
		llms_tests_mock_current_screen( 'lifterlms_page_llms-import' );
		$screen = LLMS_Unit_Test_Util::call_method( $this->import, 'get_screen' );
		$this->assertTrue( $screen instanceof WP_Screen );
		$this->assertEquals( 'lifterlms_page_llms-import', $screen->id );

	}

	/**
	 * Test get_success_message()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_get_success_message() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$generator = new LLMS_Generator( array() );
		$course = $this->factory->post->create_many( 2, array( 'post_type' => 'course' ) );
		$user = $this->factory->user->create_many( 1 );
		LLMS_Unit_Test_Util::set_private_property( $generator, 'generated', compact( 'course', 'user' ) );

		$res = LLMS_Unit_Test_Util::call_method( $this->import, 'get_success_message', array( $generator ) );

		$this->assertStringContains( 'Import Successful!', $res );

		foreach( $course as $id ) {
			$this->assertStringContains( esc_url( get_edit_post_link( $id ) ), $res );
			$this->assertStringContains( get_the_title( $id ), $res );
		}

		$user = new WP_User( $user[0] );
		$this->assertStringContains( esc_url( get_edit_user_link( $user->ID ) ), $res );
		$this->assertStringContains( $user->display_name, $res );

	}

	/**
	 * Upload form not submitted.
	 *
	 * @since 3.35.0
	 *
	 * @return [type]
	 */
	public function test_import_not_submitted() {

		$this->assertFalse( $this->import->upload_import() );

	}

	/**
	 * Submitted with an invalid nonce.
	 *
	 * @since 3.35.0
	 *
	 * @return void
	 */
	public function test_upload_import_invalid_nonce() {

		$this->mockPostRequest( array(
			'llms_importer_nonce' => 'fake',
		) );
		$this->assertFalse( $this->import->upload_import() );

	}

	/**
	 * Submitted without files.
	 *
	 * @since 3.35.0
	 *
	 * @return void
	 */
	public function test_upload_import_missing_files() {

		$this->mockPostRequest( array(
			'llms_importer_nonce' => wp_create_nonce( 'llms-importer' ),
		) );
		$this->assertFalse( $this->import->upload_import() );

	}

	/**
	 * Submitted by a user without proper permissions.
	 *
	 * @since 3.35.0
	 *
	 * @return void
	 */
	public function test_upload_import_invalid_permissions() {

		$this->mockPostRequest( array(
			'llms_importer_nonce' => wp_create_nonce( 'llms-importer' ),
		) );
		$this->mock_file_upload();
		$this->assertFalse( $this->import->upload_import() );


	}

	/**
	 * File encountered validation errors.
	 *
	 * @since 3.35.0
	 *
	 * @return void
	 */
	public function test_upload_import_validation_issues() {

		wp_set_current_user( $this->factory->student->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'llms_importer_nonce' => wp_create_nonce( 'llms-importer' ),
		) );

		// Test all the possible PHP file errors.
		$errs = array(
			1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
			3 => 'The uploaded file was only partially uploaded.',
			4 => 'No file was uploaded.',
			6 => 'Missing a temporary folder.',
			7 => 'Failed to write file to disk.',
			8 => 'File upload stopped by extension.',
			9 => 'Unknown upload error.',
		);
		foreach ( $errs as $i => $msg ) {

			$this->mock_file_upload( $i );
			$err = $this->import->upload_import();
			$this->assertIsWPError( $err );
			$this->assertWPErrorMessageEquals( $msg, $err );

		}

		// invalid filetype.
		$this->mock_file_upload();
		$_FILES['llms_import']['name'] = 'mock.txt';

		$err = $this->import->upload_import();
		$this->assertIsWPError( $err );
		$this->assertWPErrorMessageEquals( 'Only valid JSON files can be imported.', $err );

	}

	/**
	 * Generator encountered an issues when setting the generator method.
	 *
	 * @since 3.35.0
	 * @since 3.37.8 Update path to assets directory.
	 *
	 * @return void
	 */
	public function test_upload_import_invalid_generator_error() {

		wp_set_current_user( $this->factory->student->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'llms_importer_nonce' => wp_create_nonce( 'llms-importer' ),
		) );

		global $lifterlms_tests;
		$this->mock_file_upload( 0, $lifterlms_tests->assets_dir . 'import-fake-generator.json' );

		$err = $this->import->upload_import();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'invalid-generator', $err );

	}

	/**
	 * Error during generation (missing required data)
	 *
	 * @since 3.35.0
	 * @since 3.37.8 Update path to assets directory.
	 * @since 4.9.0 PHP8 upgrades from notice to warning.
	 *
	 * @return void
	 */
	public function test_upload_import_generation_error() {

		wp_set_current_user( $this->factory->student->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'llms_importer_nonce' => wp_create_nonce( 'llms-importer' ),
		) );

		global $lifterlms_tests;
		$this->mock_file_upload( 0, $lifterlms_tests->assets_dir . 'import-error.json' );

		$err = $this->import->upload_import();
		$this->assertIsWPError( $err );

		$expected_code = 8 === PHP_MAJOR_VERSION ? 'E_WARNING' : 'E_NOTICE';
		$this->assertWPErrorCodeEquals( $expected_code, $err );

	}

	/**
	 * Success.
	 *
	 * @since 3.35.0
	 *
	 * @return void
	 */
	public function test_upload_import_success() {

		wp_set_current_user( $this->factory->student->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'llms_importer_nonce' => wp_create_nonce( 'llms-importer' ),
		) );
		$this->mock_file_upload();

		$this->assertTrue( $this->import->upload_import() );

	}

	/**
	 * Test output() method.
	 *
	 * @since 4.7.0
	 * @since 7.1.0 Mark-up update.
	 *
	 * @return void
	 */
	public function test_output() {

		$this->assertOutputContains( '<div class="wrap lifterlms lifterlms-settings llms-import-export">', array( $this->import, 'output' ) );

	}

}
