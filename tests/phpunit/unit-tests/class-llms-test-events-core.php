<?php
/**
 * Test core events
 *
 * @package LifterLMS_Tests/Classes
 *
 * @group events
 * @group events_core
 *
 * @since 3.36.0
 * @version 3.36.0
 */
class LLMS_Test_Events_Core extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->events = new LLMS_Events_Core();
	}

	/**
	 * Test on_signon() method
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_on_signon() {

		$user = $this->factory->user->create_and_get();

		$event = $this->events->on_signon( $user->user_login, $user );

		$this->assertTrue( is_a( $event, 'LLMS_Event' ) );
		$this->assertEquals( $user->ID, $event->get( 'actor_id' ) );
		$this->assertEquals( $user->ID, $event->get( 'object_id' ) );

		$this->assertEquals( 'user', $event->get( 'object_type' ) );
		$this->assertEquals( 'account', $event->get( 'event_type' ) );
		$this->assertEquals( 'signon', $event->get( 'event_action' ) );

	}

	/**
	 * Test on_signon() method
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_on_signout() {

		$user = $this->factory->user->create();
		wp_set_current_user( $user );

		$event = $this->events->on_signout();

		$this->assertTrue( is_a( $event, 'LLMS_Event' ) );
		$this->assertEquals( $user, $event->get( 'actor_id' ) );
		$this->assertEquals( $user, $event->get( 'object_id' ) );

		$this->assertEquals( 'user', $event->get( 'object_type' ) );
		$this->assertEquals( 'account', $event->get( 'event_type' ) );
		$this->assertEquals( 'signout', $event->get( 'event_action' ) );

	}

}
