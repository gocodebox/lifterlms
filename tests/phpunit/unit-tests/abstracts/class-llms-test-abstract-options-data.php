<?php
/**
 * Tests for the LLMS_Abstract_Integration class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group options
 * @group settings
 *
 * @since 3.19.0
 * @since 4.21.0 Replaced the `get_stub()` method with `$this->main`, initialized in `set_up()`.
 */
class LLMS_Test_Abstract_Options_Data extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 4.21.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->main = $this->getMockForAbstractClass( 'LLMS_Abstract_Options_Data' );
	}

	/**
	 * Test get_option(): version 1 behavior.
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_get_option() {

		// Default value.
		$this->assertEquals( '', $this->main->get_option( 'mock_option' ) );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'mockvalue' ) );

		update_option( 'llms_mock_option', 'mockvalue' );

		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option' ) );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'anothermockvalue' ) );

	}

	/**
	 * Test get_option() when there's an empty string value explicitly saved in the database
	 *
	 * This test illustrates what's actually a bug but exists as expected behavior. Fixing this bug
	 * might result in unexpected consequences throughout add-ons utilizing the existing behavior as
	 * if it were intended and not a bug.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_option_v1_expected_bug() {

		// An empty string value is expected here but due to the bug the supplied default value is supplied instead.
		update_option( 'llms_mock_option', '' );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'mockvalue' ) );

		// Option Does not exist so we should get the default value either way.
		delete_option( 'llms_mock_option' );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'mockvalue' ) );

	}

	/**
	 * Test get_option(): v2 behavior
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_option_v2_behavior() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'version', 2 );

		// No default passed.
		$this->assertEquals( '', $this->main->get_option( 'mock_option' ) );

		// Default value passed.
		$this->assertEquals( '', $this->main->get_option( 'mock_option', '' ) );
		$this->assertEquals( false, $this->main->get_option( 'mock_option', false ) );
		$this->assertEquals( array(), $this->main->get_option( 'mock_option', array() ) );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'mockvalue' ) );

		update_option( 'llms_mock_option', 'mockvalue' );

		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option' ) );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', '' ) );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'anothermockvalue' ) );

	}

	/**
	 * Run test_get_option_v1_expected_bug() on v2 to see the bug fixed.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_option_v2_expected_bug_fixed() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'version', 2 );

		// This fails on v1, see `test_get_option_v1_expected_bug()`.
		update_option( 'llms_mock_option', '' );
		$this->assertEquals( '', $this->main->get_option( 'mock_option', 'mockvalue' ) );

		// Option Does not exist so we should get the default value.
		delete_option( 'llms_mock_option' );
		$this->assertEquals( 'mockvalue', $this->main->get_option( 'mock_option', 'mockvalue' ) );

	}

	/**
	 * test get_option_name() method
	 *
	 * @since 3.19.0
	 * @since 4.21.0 Use unit test utils to update private property value.
	 *
	 * @return void
	 */
	public function test_get_option_name() {

		$this->assertEquals( 'llms_mock_option', $this->main->get_option_name( 'mock_option' ) );

		// Change the option prefix as an extending class might via overriding the `get_option_prefix()` method
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'option_prefix', 'llms_extended_' );

		$this->assertEquals( 'llms_extended_mock_option', $this->main->get_option_name( 'mock_option' ) );

	}

	/**
	 * test set_option() method
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_set_option() {

		delete_option( 'llms_mock_option' );
		$this->assertEquals( true, $this->main->set_option( 'mock_option', 'mockvalue' ) );
		$this->assertEquals( 'mockvalue', get_option( 'llms_mock_option', 'mockvalue' ) );

	}

}
