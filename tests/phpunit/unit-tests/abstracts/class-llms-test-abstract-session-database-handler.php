<?php
/**
 * Tests for the LLMS_Abstract_Session_Database_Handler class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group sessions
 * @group session_database_handler
 *
 * @since 4.0.0
 */
class LLMS_Test_Abstract_Session_Database_Handler extends LLMS_UnitTestCase {

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

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_sessions" );

		$this->main = $this->getMockForAbstractClass( 'LLMS_Abstract_Session_Database_Handler' );

	}

	/**
	 * Test clean() when deleting only expired sessions.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_clean_expired_only() {

		$prefix = LLMS_Cache_Helper::get_prefix( 'llms_session_id' );

		$active  = $this->create_mock_session_data( 2 );
		$expired = $this->create_mock_session_data( 2, true );

		// Return 2 deletions.
		$this->assertEquals( 2, $this->main->clean() );

		// Active sessions were not removed.
		global $wpdb;
		$remaining = array_map( 'absint', $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}lifterlms_sessions" ) );
		$this->assertEqualSets( $active, $remaining );

		// New prefix because the old one is invalidated.
		$this->assertNotEquals( $prefix, LLMS_Cache_Helper::get_prefix( 'llms_session_id' ) );

	}

	/**
	 * Test clean() when deleting all sessions.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_clean_all() {

		$active  = $this->create_mock_session_data( 2 );
		$expired = $this->create_mock_session_data( 2, true );

		// Return 4 deletions.
		$this->assertEquals( 4, $this->main->clean( false ) );

		// No sessions remain.
		global $wpdb;
		$remaining = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}lifterlms_sessions" );
		$this->assertEquals( array(), $remaining );

	}

	/**
	 * Test delete()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_delete() {

		$id = $this->create_mock_session_data( 1 )[0];

		global $wpdb;
		$session_id = $wpdb->get_col( "SELECT session_key FROM {$wpdb->prefix}lifterlms_sessions WHERE id = {$id};" )[0];

		// Mock cached data data.
		wp_cache_set( LLMS_Cache_Helper::get_prefix( 'llms_session_id' ) . $session_id, 'mock_data', 'llms_session_id' );

		$this->assertTrue( $this->main->delete( $session_id ) );

		$this->assertFalse( wp_cache_get( LLMS_Cache_Helper::get_prefix( 'llms_session_id' ) . $session_id, 'llms_session_id' ) );



	}

	/**
	 * Test save() when there's not data to be saved
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_save_is_clean() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'is_clean', true );
		$this->assertFalse( $this->main->save( time() + HOUR_IN_SECONDS ) );

	}

	/**
	 * Test save()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_save() {

		$this->main->set( 'item', 'yes' );

		// Saved to DB.
		$this->assertTrue( $this->main->save( time() + HOUR_IN_SECONDS ) );

		// Cache set.
		$data = wp_cache_get( LLMS_Cache_Helper::get_prefix( 'llms_session_id' ) . $this->main->get_id(), 'llms_session_id' );
		$this->assertEquals( array( 'item' => 'yes' ), $data );

	}

	/**
	 * Test read() when there's no saved data so it returns a default value
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_read_default() {

		$this->assertEquals( 'defaultvalue', $this->main->read( 'fake', 'defaultvalue' ) );

	}

	/**
	 * Test read() when there's a cache hit
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_read_cache_hit() {

		wp_cache_set( LLMS_Cache_Helper::get_prefix( 'llms_session_id' ) . 'fake_session', 'mock_data', 'llms_session_id' );
		$this->assertEquals( 'mock_data', $this->main->read( 'fake_session' ) );

	}


	/**
	 * Test read() when there's a cache miss
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_read_cache_miss() {

		$this->main->set( 'something', 'is_set' );
		$this->main->save( time() + HOUR_IN_SECONDS );

		LLMS_Cache_Helper::invalidate_group( 'llms_session_id' );

		$this->assertEquals( array( 'something' => 'is_set' ), $this->main->read( $this->main->get_id() ) );

	}

}
