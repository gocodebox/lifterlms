<?php
/**
 * Test session class
 *
 * @package LifterLMS/Tests
 *
 * @group session
 * @group sessions
 *
 * @since 4.0.0
 */
class LLMS_Test_Session extends LLMS_Unit_Test_Case {

	/**
	 * Setup test
	 *
	 * @since 4.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Session();

	}

	/**
	 * Retrieve the name of the cookie
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_cookie_name() {
		return LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'cookie' );
	}

	/**
	 * Retrieve the raw cookie value.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	protected function get_raw_cookie() {
		return $this->cookies->get( $this->get_cookie_name() );
	}

	/**
	 * Test constructor
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_construct_should_init() {

		remove_action( 'llms_delete_expired_session_data', array( $this->main, 'clean' ) );
		remove_action( 'wp_logout', array( $this->main, 'destroy' ) );
		remove_action( 'shutdown', array( $this->main, 'maybe_save_data' ), 20 );

		$this->main = new LLMS_Session();

		$this->assertEquals( 10, has_action( 'llms_delete_expired_session_data', array( $this->main, 'clean' ) ) );
		$this->assertEquals( 10, has_action( 'wp_logout', array( $this->main, 'destroy' ) ) );
		$this->assertEquals( 20, has_action( 'shutdown', array( $this->main, 'maybe_save_data' ) ) );

		$this->assertEquals( sprintf( 'wp_llms_session_%s', COOKIEHASH ), $this->get_cookie_name() );

	}

	/**
	 * Test constructor when we should not initialize.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_construct_should_not_init() {

		remove_action( 'llms_delete_expired_session_data', array( $this->main, 'clean' ) );
		remove_action( 'wp_logout', array( $this->main, 'destroy' ) );
		remove_action( 'shutdown', array( $this->main, 'maybe_save_data' ), 20 );

		add_filter( 'llms_session_should_init', '__return_false' );
		$this->main = new LLMS_Session();
		remove_filter( 'llms_session_should_init', '__return_false' );

		$this->assertEquals( 10, has_action( 'llms_delete_expired_session_data', array( $this->main, 'clean' ) ) );
		$this->assertFalse( has_action( 'wp_logout', array( $this->main, 'destroy' ) ) );
		$this->assertFalse( has_action( 'shutdown', array( $this->main, 'maybe_save_data' ) ) );

		$this->assertEquals( sprintf( 'wp_llms_session_%s', COOKIEHASH ), LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'cookie' ) );

	}

	/**
	 * Test destroy()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_destroy() {

		$this->main->set( 'somedata', 'isset' );
		$this->assertTrue( $this->main->save( time() + HOUR_IN_SECONDS ) );

		// Destroyed.
		$this->assertTrue( $this->main->destroy() );

		// Class properties reset.
		$this->assertEquals( '', LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'id' ) );
		$this->assertEquals( array(), LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'data' ) );
		$this->assertTrue( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'is_clean' ) );

		// Cookie should be emptied and set to expire.
		$cookie = $this->get_raw_cookie();

		$this->assertEquals( '', $cookie['value'] );
		$this->assertTrue( $cookie['expires'] < time() );

	}

	/**
	 * Test get_cookie() when there's no cookie set.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_cookie_not_set() {

		$this->cookies->unset_all();
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' ) );

	}

	/**
	 * Test get_cookie() when it returns something unexpected
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_cookie_not_string() {

		$this->cookies->set( $this->get_cookie_name(), 1234 );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' ) );

	}

	/**
	 * Test get_cookie() when it's missing required parts.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_cookie_missing_parts() {

		$this->cookies->set( $this->get_cookie_name(), 'part1' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' ) );

		$this->cookies->set( $this->get_cookie_name(), 'part1||part2||part3||' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' ) );

	}

	/**
	 * Test get_cookie() when the hash is invalid
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_cookie_invalid() {

		$this->cookies->set( $this->get_cookie_name(), 'part1||part2||part3||part4|1234' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' ) );

	}

	/**
	 * Test get_cookie() for a success return
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_cookie() {

		$parts = LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' );

		$this->assertEquals( $this->main->get_id(), $parts[0] );
		$this->assertEquals( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'expires' ), $parts[1] );
		$this->assertEquals( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'expiring' ), $parts[2] );
		$this->assertTrue( is_string( $parts[3] ) );

	}

	/**
	 * Test init_cookie() when the cookie exists
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_init_cookie_from_existing() {

		$data = $this->main->set( 'something', 123 );
		$this->main->save( time() + HOUR_IN_SECONDS );
		$parts = LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' );

		// Reset everything.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'id', '' );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'expires', 0 );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'expiring', 0 );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'data', array() );

		// Reinit.
		LLMS_Unit_Test_Util::call_method( $this->main, 'init_cookie' );

		$this->assertEquals( $parts, LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' ) );
		$this->assertEquals( $parts[0], $this->main->get_id() );
		$this->assertEquals( $parts[1], LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'expires' ) );
		$this->assertEquals( $parts[2], LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'expiring' ) );
		$this->assertEquals( 123, $this->main->get( 'something' ) );

	}

	/**
	 * Test init_cookie() when the cookie is expiring
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_init_cookie_from_existing_expiring() {

		// Expiring is in the past.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'expiring', 0 );

		// Reinit.
		LLMS_Unit_Test_Util::call_method( $this->main, 'init_cookie' );

		// Expiring reset to the future.
		$this->assertTrue( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'expiring' ) > time() );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' )[2] > time() );

	}

	/**
	 * Test init_cookie() when the user id is to change
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_init_cookie_from_existing_user_logged_in() {

		$id  = $this->main->get_id();
		$uid = $this->factory->user->create();

		wp_set_current_user( $uid );

		// Reinit.
		LLMS_Unit_Test_Util::call_method( $this->main, 'init_cookie' );

		$this->assertEquals( $uid, $this->main->get_id() );
		$this->assertEquals( $uid, LLMS_Unit_Test_Util::call_method( $this->main, 'get_cookie' )[0] );

	}

	/**
	 * Test init_cookie() when a new cookie is created
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_init_cookie_new() {

		$original = $this->get_raw_cookie();
		$this->cookies->unset_all();

		LLMS_Unit_Test_Util::call_method( $this->main, 'init_cookie' );
		$this->assertNotEquals( $original, $this->get_raw_cookie() );


	}

	/**
	 * Test
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_maybe_save_data_is_clean() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'is_clean', true );
		$this->assertFalse( $this->main->maybe_save_data() );

	}

	/**
	 * Test
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_maybe_save_data_is_not_clean() {

		$this->main->set( 'test', 'data' );
		$this->assertTrue( $this->main->maybe_save_data() );

	}

}
