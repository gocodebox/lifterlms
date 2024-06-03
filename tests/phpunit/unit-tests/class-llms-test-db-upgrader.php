<?php
/**
 * Test AJAX Handler
 *
 * @package LifterLMS/Tests
 *
 * @group upgrader
 *
 * @since 5.2.0
 */
class LLMS_Test_DB_Upgrader extends LLMS_UnitTestCase {

	/**
	 * Test can_auto_update()
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_can_auto_update() {

		// All manual.
		$updates = array(
			'1.0.0' => array(
				'type' => 'manual',
			),
			'2.0.0' => array(
				'type' => 'manual',
			),
		);
		$upgrader = new LLMS_DB_Upgrader( '0.0.1', $updates );
		$this->assertFalse( $upgrader->can_auto_update() );

		$upgrader = new LLMS_DB_Upgrader( '1.5.0', $updates );
		$this->assertFalse( $upgrader->can_auto_update() );

		// As one auto but it's still manual.
		$updates['2.0.0']['type'] = 'auto';
		$upgrader = new LLMS_DB_Upgrader( '0.1.0', $updates );
		$this->assertFalse( $upgrader->can_auto_update() );

		// Only auto so it's okay.
		$upgrader = new LLMS_DB_Upgrader( '1.9.999', $updates );
		$this->assertTrue( $upgrader->can_auto_update() );

	}

	/**
	 * Test constructor and get_updates()
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_constructor_and_get_updates() {

		// No update schema passed, use the core included file.
		$upgrader = new LLMS_DB_Upgrader( '1.2.3' );

		$expect = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-db-updates.php';
		$this->assertEquals( $expect, $upgrader->get_updates() );

		// Pass in a schema.
		$schema = array(
			'1.2.3' => array(
				'type' => 'manual',
				'updates' => array(
					'fake_callback',
					'fake_callback_2',
				),
			),
			'2.0.0' => array(
				'type' => 'auto',
				'updates' => array(
					'fake_callback',
				),
			),
		);

		$upgrader = new LLMS_DB_Upgrader( '1.2.3', $schema );
		$this->assertEquals( $schema, $upgrader->get_updates() );

	}

	/**
	 * Test get_callback_prefix()
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function test_get_callback_prefix() {

		$upgrader = new LLMS_DB_Upgrader( '1.2.3' );

		$tests = array(
			array( false, '5.0.0', '', ),
			array( null, '5.0.0', '', ),
			array( true, '5.0.0', 'LLMS\Updates\Version_5_0_0\\', ),
			array( true, '5.0.0-beta.1', 'LLMS\Updates\Version_5_0_0\\', ),
			array( true, '5.0.0-alpha.1', 'LLMS\Updates\Version_5_0_0\\', ),
			array( 'Custom\String\Provided', '1.0.0', 'Custom\String\Provided\Version_1_0_0\\', ),
		);

		foreach ( $tests as $test ) {

			list( $namespace, $version, $expected ) = $test;

			$info = compact( 'namespace' );
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $upgrader, 'get_callback_prefix', array( $info, $version ) ) );

		}

		// When `$namespace` not provided in the $info object.
		$this->assertEquals( '', LLMS_Unit_Test_Util::call_method( $upgrader, 'get_callback_prefix', array( array(), $version ) ) );

	}

	/**
	 * Test enuqeue_updates() when auto updating
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_enqueue_updates_auto() {

		$schema = array(
			'1.5.0' => array(
				'type' => 'auto',
				'updates' => array(
					'update_auto',
				),
			),
		);

		$upgrader = new LLMS_DB_Upgrader( '1.2.3', $schema );
		$upgrader->enqueue_updates();

		$updater = LLMS_Unit_Test_Util::get_private_property_value( $upgrader, 'updater' );
		$batch   = LLMS_Unit_Test_Util::call_method( $updater, 'get_batch' )->data;

		$this->assertEquals( array( 'update_auto' ), $batch );

		// Reinit the updater for future tests.
		LLMS_Install::init_background_updater();

	}

	/**
	 * Test enuqeue_updates() when manual updating is required
	 *
	 * @since 5.2.0
	 * @since 5.6.0 Add tests for automatic namespacing.
	 *
	 * @return void
	 */
	public function test_enqueue_updates_manual() {

		$schema = array(
			'1.5.0' => array(
				'type' => 'manual',
				'updates' => array(
					'update_150_1',
					'update_150_2',
				),
			),
			'2.0.0' => array(
				'type' => 'auto',
				'updates' => array(
					'update_200',
				),
			),
			'3.5.1' => array(
				'type'      => 'manual',
				'namespace' => true,
				'updates' => array(
					'update_something',
				),
			),
			'3.9.9' => array(
				'type'      => 'manual',
				'namespace' => 'Custom\Namespace',
				'updates' => array(
					'update_something',
				),
			),
		);

		$upgrader = new LLMS_DB_Upgrader( '1.2.3', $schema );

		$upgrader->enqueue_updates();

		// Check logs.
		$expected_logs = array(
			'Queuing 1.5.0 - update_150_1',
			'Queuing 1.5.0 - update_150_2',
			'Queuing 2.0.0 - update_200',
			'Queuing 3.5.1 - LLMS\Updates\Version_3_5_1\update_something',
			'Queuing 3.9.9 - Custom\Namespace\Version_3_9_9\update_something',
		);
		$this->assertEquals( $expected_logs, $this->logs->get( 'updater' ) );

		// Callbacks loaded into queue properly.
		$expected_batch = array(
			'update_150_1',
			'update_150_2',
			'update_200',
			'LLMS\Updates\Version_3_5_1\update_something',
			'Custom\Namespace\Version_3_9_9\update_something',
		);

		$updater = LLMS_Unit_Test_Util::get_private_property_value( $upgrader, 'updater' );
		$batch   = LLMS_Unit_Test_Util::call_method( $updater, 'get_batch' )->data;

		// Show completion message.
		$complete = array_pop( $batch );
		$this->assertInstanceOf( 'LLMS_DB_Upgrader', $complete[0] );
		$this->assertEquals( 'show_notice_complete', $complete[1] );

		// Rest of the callbacks.
		$this->assertEquals( $expected_batch, $batch );

		// Reinit the updater for future tests.
		LLMS_Install::init_background_updater();

	}

	/**
	 * Test get_required_updates() and has_required_updates()
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_get_required_updates_and_has_required_updates() {

		// Mock updates.
		$updates = array(
			'1.2.3' => array(),
			'2.0.0' => array(),
			'3.0.5' => array(),
			'4.5.6' => array(),
		);

		foreach ( array( '0.1.1', '1.0.0', '1.2.2' ) as $version ) {
			$upgrader = new LLMS_DB_Upgrader( $version, $updates );
			$this->assertEquals( $updates, $upgrader->get_required_updates() );
			$this->assertTrue( $upgrader->has_required_updates() );
		}

		unset( $updates['1.2.3'] );
		foreach ( array( '1.2.3', '1.5.0', '1.99.999' ) as $version ) {
			$upgrader = new LLMS_DB_Upgrader( $version, $updates );
			$this->assertEquals( $updates, $upgrader->get_required_updates() );
			$this->assertTrue( $upgrader->has_required_updates() );
		}

		unset( $updates['2.0.0'] );
		$upgrader = new LLMS_DB_Upgrader( '2.0.0', $updates );
		$this->assertEquals( $updates, $upgrader->get_required_updates() );
		$this->assertTrue( $upgrader->has_required_updates() );

		unset( $updates['3.0.5'] );
		$upgrader = new LLMS_DB_Upgrader( '4.1.2', $updates );
		$this->assertEquals( $updates, $upgrader->get_required_updates() );
		$this->assertTrue( $upgrader->has_required_updates() );

		// No updates.
		foreach ( array( '4.5.6', '5.0.0', '10.5.9' ) as $version ) {
			$upgrader = new LLMS_DB_Upgrader( $version, $updates );
			$this->assertEquals( array(), $upgrader->get_required_updates() );
			$this->assertFalse( $upgrader->has_required_updates() );
		}

	}

	/**
	 * Test show_notice_complete()
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_show_notice_complete() {

		LLMS_Admin_Notices::add_notice( 'bg-db-update-started', 'notice' );

		$upgrader = new LLMS_DB_Upgrader( '1.2.3' );
		LLMS_Unit_Test_Util::call_method( $upgrader, 'show_notice_complete' );

		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'bg-db-update-started' ) );
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'bg-db-update-complete' ) );

	}

	/**
	 * Test show_notice_pending()
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_show_notice_pending() {

		$upgrader = new LLMS_DB_Upgrader( '1.2.3' );

		// Add a fake notice so we can make sure it's deleted.
		LLMS_Admin_Notices::add_notice( 'bg-db-update', 'deleted' );
		LLMS_Unit_Test_Util::call_method( $upgrader, 'show_notice_pending' );

		// Has notice.
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'bg-db-update' ) );

	}

	/**
	 * Test update() when no updates are required.
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_update_no_required() {

		$upgrader = new LLMS_DB_Upgrader( '5.0.0', array( '1.0.0' => array() ) );
		$this->assertFalse( $upgrader->update() );

	}

	/**
	 * Test update() when updates are required.
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_update_required() {

		LLMS_Admin_Notices::delete_notice( 'bg-db-update' );

		$schema = array(
			'1.5.0' => array(
				'type' => 'manual',
				'updates' => array(
					'update_150_1',
					'update_150_2',
				),
			),
			'2.0.0' => array(
				'type' => 'auto',
				'updates' => array(
					'update_200',
				),
			),
		);

		// Manual update.
		$upgrader = new LLMS_DB_Upgrader( '1.0.0', $schema );
		$this->assertTrue( $upgrader->update() );

		// Notice displayed.
		$this->assertTrue( LLMS_Admin_Notices::has_notice( 'bg-db-update' ) );
		LLMS_Admin_Notices::delete_notice( 'bg-db-update' );


		// Auto update.
		$upgrader = new LLMS_DB_Upgrader( '1.9.1', $schema );
		$this->assertTrue( $upgrader->update() );

		// No notice displayed.
		$this->assertFalse( LLMS_Admin_Notices::has_notice( 'bg-db-update' ) );
		// Updates queued.
		$this->assertEquals( array( 'Queuing 2.0.0 - update_200' ), $this->logs->get( 'updater' ) );

	}

}
