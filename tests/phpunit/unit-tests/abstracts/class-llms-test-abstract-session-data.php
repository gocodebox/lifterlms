<?php
/**
 * Tests for the LLMS_Abstract_Session_Data class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group sessions
 * @group session_data
 *
 * @since 4.0.0
 */
class LLMS_Test_Abstract_Session_Data extends LLMS_UnitTestCase {

	/**
	 * Setup test case
	 *
	 * @since 4.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = $this->getMockForAbstractClass( 'LLMS_Abstract_Session_Data' );

	}

	/**
	 * Test get, set, and magic methods.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_set_isset_unset() {

		$vals = array(
			1, true, 'yes', 'true', 'on',
			false, 0, 'no', 'off',
			array(), array( 'yes' ), array( 'yes' => 'okay' ),
			1234.56, '1234.56',
			25, '20389'
		);

		foreach ( $vals as $val ) {

			$key = sprintf( '%s_%s', uniqid(), microtime() );

			// Var not set.
			$this->assertFalse( isset( $this->main->$key ) );

			// Default value get when var is not set.
			$this->assertEquals( $val, $this->main->get( $key, $val ) );

			// Set.
			$this->assertEquals( $val, $this->main->set( $key, $val ) );
			$this->assertFalse( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'is_clean' ) );

			// Var is set.
			$this->assertTrue( isset( $this->main->$key ) );

			// Reset.
			LLMS_Unit_Test_Util::set_private_property( $this->main, 'is_clean', true );
			unset( $this->main->$key );

			// Magic set.
			$this->assertEquals( $val, $this->main->set( $key, $val ) );
			$this->assertFalse( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'is_clean' ) );

			// Var is set.
			$this->assertTrue( isset( $this->main->$key ) );

			// Get.
			$this->assertEquals( $val, $this->main->get( $key ) );

			// Magic Get.
			$this->assertEquals( $val, $this->main->$key );

			// Reset.
			LLMS_Unit_Test_Util::set_private_property( $this->main, 'is_clean', true );

			// Unset.
			unset( $this->main->$key );
			$this->assertFalse( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'is_clean' ) );

			// Gone, should return the default value.
			$this->assertEquals( 'deleted', $this->main->get( $key, 'deleted' ) );

		}

	}

	// public function

	/**
	 * Test get_id()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_id() {

		// Already set.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'id', 'fakeid' );
		$this->assertEquals( 'fakeid', $this->main->get_id() );

		// Generate a new id.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'id', '' );
		$id = $this->main->get_id();
		$this->assertTrue( is_string( $this->main->get_id() ) );
		$this->assertEquals( 32, strlen( $this->main->get_id() ) );

	}

	/**
	 * Test get_id() for logged in users.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_id_logged_in() {

		$uid = $this->factory->user->create();
		wp_set_current_user( $uid );

		$this->assertEquals( $uid, $this->main->get_id() );

	}

}
