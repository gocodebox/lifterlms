<?php
/**
* Test updates functions when updating to 4.14.0
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_4140
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Functions_Updates_4140 extends LLMS_UnitTestCase {

	private $sessions;

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {
		parent::setupBeforeClass();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-4140.php';
	}

	/**
	 * Teardown the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		// Clean posts and postmeta tables
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->postmeta}" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->posts}" );
		// Delete transients.
		delete_transient( 'llms_update_4140_remove_orphan_access_plans' );
		delete_transient( 'llms_4140_skipper_orphan_access_plans' );
	}

	/**
	 * Test llms_update_4140_remove_orphan_access_plans
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_4140_remove_orphan_access_plans() {

		// Create orphan access plans.
		$access_plan_ids = $this->factory->post->create_many(
			10,
			array(
				'post_type' => 'llms_access_plan',
			)
		);
		foreach ( $access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', 999 );
		}

		$this->assertEquals(
			10,
			count(
				get_posts(
					array(
						'include'   => $access_plan_ids,
						'post_type' => 'llms_access_plan',
					)
				)
			)
		);

		// Fire the update.
		llms_update_4140_remove_orphan_access_plans();

		// Expect no orphan access plans.
		$this->assertEquals(
			0,
			count(
				get_posts(
					array(
						'include'   => $access_plan_ids,
						'post_type' => 'llms_access_plan',
					)
				)
			)
		);

	}

	/**
	 * Test llms_update_4140_remove_orphan_access_plans
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_4140_remove_orphan_access_plans_keep_linked() {

		// Create orphan access plans
		$orphan_access_plan_ids = $this->factory->post->create_many(
			10,
			array(
				'post_type' => 'llms_access_plan',
			)
		);
		foreach ( $orphan_access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', 999 );
		}

		// Create linked access plans.
		$access_plan_ids = $this->factory->post->create_many(
			10,
			array(
				'post_type' => 'llms_access_plan',
			)
		);
		$course = $this->factory->post->create();
		foreach ( $access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', $course );
		}

		llms_update_4140_remove_orphan_access_plans();

		// Expect no orphan access plans.
		$this->assertEquals(
			0,
			count(
				get_posts(
					array(
						'include'   => $orphan_access_plan_ids,
						'post_type' => 'llms_access_plan',
					)
				)
			)
		);

		// Expect linked access plans are still there.
		$this->assertEquals(
			count( $access_plan_ids ),
			count(
				get_posts(
					array(
						'include'   => $access_plan_ids,
						'post_type' => 'llms_access_plan',
					)
				)
			)
		);

	}

	/**
	 * Test llms_update_4140_update_db_version()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		llms_update_4140_update_db_version();

		$this->assertNotEquals( '4.14.0', get_option( 'lifterlms_db_version' ) );

		// Unlock the db version update.
		set_transient( 'llms_update_4140_remove_orphan_access_plans', 'complete', DAY_IN_SECONDS );

		llms_update_4140_update_db_version();

		$this->assertEquals( '4.14.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

}
