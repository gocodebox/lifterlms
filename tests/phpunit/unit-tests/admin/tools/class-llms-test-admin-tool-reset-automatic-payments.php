<?php
/**
 * Tests for the LLMS_Admin_Tool_Reset_Automatic_Payments class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 * @group reset_payments
 *
 * @since 4.13.0
 * @since 5.3.0 Use `LLMS_Admin_Tool_Test_Case` and remove redundant methods/tests.
 */
class LLMS_Test_Admin_Tool_Reset_Automatic_Payments extends LLMS_Admin_Tool_Test_Case {

	/**
	 * Name of the class being tested.
	 *
	 * @var sting
	 */
	const CLASS_NAME = 'LLMS_Admin_Tool_Reset_Automatic_Payments';

	/**
	 * Test handle()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_handle() {

		$actions = did_action( 'llms_site_clone_detected' );

		// Get the original values of options to be cleared.
		$orig_url    = get_option( 'llms_site_url' );
		$orig_ignore = get_option( 'llms_site_url_ignore' );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', admin_url( 'admin.php?page=llms-status&tab=tools') ) );

		try {
			LLMS_Unit_Test_Util::call_method( $this->main, 'handle' );
		} catch( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$this->assertEquals( '', get_option( 'llms_site_url' ) );
			$this->assertEquals( 'no', get_option( 'llms_site_url_ignore' ) );
			$this->assertEquals( ++$actions, did_action( 'llms_site_clone_detected' ) );

			// Reset to the orig values.
			update_option( 'llms_site_url', $orig_url );
			update_option( 'llms_site_url_ignore', $orig_ignore );

			throw $exception;

		}

	}

	/**
	 * Test should_load() with no constants set.
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_should_load() {
		$this->assertTrue( true, LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );
	}

	/**
	 * Test should_load() with LLMS_SITE_IS_CLONE constant set.
	 *
	 * @since 4.13.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_should_load_with_site_clone_constant_set() {
		define( 'LLMS_SITE_IS_CLONE', false );
		$this->assertTrue( true, LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );
	}

	/**
	 * Test should_load() with LLMS_SITE_FEATURE_RECURRING_PAYMENTS constant set.
	 *
	 * @since 4.13.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_should_load_with_recurring_payments_constant_set() {
		define( 'LLMS_SITE_FEATURE_RECURRING_PAYMENTS', false );
		$this->assertTrue( true, LLMS_Unit_Test_Util::call_method( $this->main, 'should_load' ) );
	}

}
