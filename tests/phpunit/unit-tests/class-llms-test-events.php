<?php
/**
 * Test events
 *
 * @package LifterLMS/Tests
 *
 * @group events
 *
 * @since 3.36.0
 * @version 3.36.0
 */
class LLMS_Test_Events extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->events = LLMS()->events();
	}

	/**
	 * Teardown the test case.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events" );
	}

	/**
	 * Test something
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_record_missing_fields() {

		$event = array();

		$ret = $this->events->record( $event );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_event_record_missing_field', $ret );
		$this->assertEquals( 5, count( $ret->get_error_messages( 'llms_event_record_missing_field' ) ) );

		$event['actor_id'] = 1;
		$ret = $this->events->record( $event );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_event_record_missing_field', $ret );
		$this->assertEquals( 4, count( $ret->get_error_messages( 'llms_event_record_missing_field' ) ) );

		$event['object_type'] = 'user';
		$ret = $this->events->record( $event );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_event_record_missing_field', $ret );
		$this->assertEquals( 3, count( $ret->get_error_messages( 'llms_event_record_missing_field' ) ) );

		$event['object_id'] = 1;
		$ret = $this->events->record( $event );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_event_record_missing_field', $ret );
		$this->assertEquals( 2, count( $ret->get_error_messages( 'llms_event_record_missing_field' ) ) );

		$event['event_type'] = 'account';
		$ret = $this->events->record( $event );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_event_record_missing_field', $ret );
		$this->assertEquals( 1, count( $ret->get_error_messages( 'llms_event_record_missing_field' ) ) );

	}

	public function test_record_invalid_event() {

		$args = array(
			'actor_id' => 1,
			'object_type' => 'user',
			'object_id' => 1,
			'event_type' => 'fake',
			'event_action' => 'mock',
		);
		$ret = $this->events->record( $args );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_event_record_invalid_event', $ret );

	}

	public function test_record_success() {

		$args = array(
			'actor_id' => 1,
			'object_type' => 'user',
			'object_id' => 1,
			'event_type' => 'account',
			'event_action' => 'signon',
		);
		$ret = $this->events->record( $args );

		$this->assertTrue( is_a( $ret, 'LLMS_Event' ) );
		foreach ( $args as $key => $expect ) {
			$this->assertEquals( $expect, $ret->get( $key ) );
		}

	}

	public function test_record_success_with_metas() {

		$args = array(
			'actor_id' => 1,
			'object_type' => 'user',
			'object_id' => 1,
			'event_type' => 'account',
			'event_action' => 'signon',
			'meta' => array(
				'meta_key' => 'meta_val',
			),
		);
		$ret = $this->events->record( $args );

		$this->assertTrue( is_a( $ret, 'LLMS_Event' ) );
		foreach ( $args as $key => $expect ) {

			if ( 'meta' === $key ) {
				$this->assertEquals( $expect, $ret->get_meta() );
			} else {
				$this->assertEquals( $expect, $ret->get( $key ) );
			}

		}

	}

	public function test_record_many_with_errors() {

		// All errors.
		$events = array(
			array(),
			array(),
		);
		$ret = $this->events->record_many( $events );

		$this->assertIsWPError( $ret );
		$errors = $ret->get_error_data( 'llms_events_record_many_errors' );
		$this->assertEquals( 2, count( $errors ) );
		foreach ( $errors as $stat ) {
			$this->assertIsWPError( $stat );
		}

		$events = array(
			array(
				'actor_id' => 1,
				'object_type' => 'user',
				'object_id' => 1,
				'event_type' => 'account',
				'event_action' => 'signon',
			),
			array(),
		);

		// One error with one success.
		$ret = $this->events->record_many( $events );
		$this->assertIsWPError( $ret );
		$errors = $ret->get_error_data( 'llms_events_record_many_errors' );
		$this->assertEquals( 1, count( $errors ) );
		foreach ( $errors as $stat ) {
			$this->assertIsWPError( $stat );
		}

		// Query rolled back.
		global $wpdb;
		$this->assertEquals( 0, $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_events" ) );

	}

	public function test_record_many_success() {

		$events = array(
			array(
				'actor_id' => 1,
				'object_type' => 'user',
				'object_id' => 1,
				'event_type' => 'account',
				'event_action' => 'signon',
			),
			array(
				'actor_id' => 1,
				'object_type' => 'user',
				'object_id' => 1,
				'event_type' => 'account',
				'event_action' => 'signon',
			),
		);

		// One error with one success.
		$ret = $this->events->record_many( $events );

		foreach ( $ret as $event ) {
			$this->assertTrue( is_a( $event, 'LLMS_Event' ) );
		}

		// Query committed.
		global $wpdb;
		$this->assertEquals( 2, $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_events" ) );

	}

}
