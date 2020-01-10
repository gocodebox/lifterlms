<?php
/**
 * Test INsturctor model
 *
 * @package  LifterLMS_Tests/Models
 *
 * @group LLMS_Event
 * @group events
 *
 * @since 3.36.0
 * @version 3.36.0
 */
class LLMS_Test_Event extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
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
	 * Test creation.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_create() {

		$expected_time = current_time( 'timestamp' ) - DAY_IN_SECONDS;
		llms_tests_mock_current_time( $expected_time );

		$args = array(
			'actor_id' => 1,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'load',
		);

		$event = new LLMS_Event();
		$event->setUp( $args );
		$this->assertTrue( $event->save() );
		$this->assertTrue( is_numeric( $event->get( 'id' ) ) );

		llms_tests_reset_current_time();

		$event = new LLMS_Event( $event->get( 'id' ) );

		$this->assertEquals( $expected_time, strtotime( $event->get( 'date' ) ) );
		foreach( $args as $key => $expected ) {
			$this->assertEquals( $expected, $event->get( $key ) );
		}

	}

	/**
	 * Test metadata getters, setters, unsetters.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_meta() {

		$args = array(
			'actor_id' => 1,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'load',
		);

		$meta = array(
			'meta_key' => 'meta_val',
			'another' => 1,
		);

		$event = new LLMS_Event();

		// Set multiple metas.
		$event->setUp( $args )->set_metas( $meta );

		// Get all metas.
		$this->assertEquals( $meta, $event->get_meta() );

		// Get individual metas.
		foreach ( $meta as $key => $expect ) {

			$this->assertEquals( $expect, $event->get_meta( $key ) );

		}

		// Update a single meta value.
		$event->set_meta( 'meta_key', 'new_val' );
		$this->assertEquals( 'new_val', $event->get_meta( 'meta_key' ) );

		// Create a new meta item.
		$event->set_meta( 'new_key', true );
		$this->assertTrue( $event->get_meta( 'new_key' ) );

		// Delete a single meta item.
		$event->delete_meta( 'new_key' );
		$this->assertNull( $event->get_meta( 'new_key' ) );

		// Delete all meta items.
		$event->delete_meta();
		$this->assertEquals( array(), $event->get_meta() );

	}

	/**
	 * Test meta getters/setters when the data is saved (ensure db serialization is working properly).
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_meta_store() {

		$args = array(
			'actor_id' => 1,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'load',
		);

		$meta = array(
			'meta_key' => 'meta_val',
			'another' => 1,
		);

		$event = new LLMS_Event();
		$event->setUp( $args )->save();

		$event->set_metas( $meta, true );

		$event = new LLMS_Event( $event->get( 'id' ), true );
		$this->assertEquals( wp_json_encode( $meta ), $event->get( 'meta' ) );
		$this->assertEquals( $meta, $event->get_meta() );

	}

}
