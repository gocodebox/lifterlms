<?php
/**
* Test updates functions when updating to 4.15.0
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_4150
 *
 * @since 4.15.0
 * @version 4.15.0
 */
class LLMS_Test_Functions_Updates_4150 extends LLMS_UnitTestCase {

	private $sessions;

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since 4.15.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes and move teardown functions into here.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-4150.php';

		// Clean posts and postmeta tables.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->postmeta}" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->posts}" );
		// Delete transients.
		delete_transient( 'llms_update_4150_remove_orphan_access_plans' );

	}

	/**
	 * Test llms_update_4150_remove_orphan_access_plans
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	public function test_update_4150_remove_orphan_access_plans() {

		// Create orphan access plans.
		$access_plan_ids = $this->factory->post->create_many(
			10,
			array(
				'post_type' => 'llms_access_plan',
			)
		);
		foreach ( $access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', end( $access_plan_ids ) + 1 );
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
		llms_update_4150_remove_orphan_access_plans();

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
	 * Test llms_update_4150_remove_orphan_access_plans
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	public function test_update_4150_remove_orphan_access_plans_keep_linked() {

		// Create linked access plans.
		$access_plan_ids = $this->factory->post->create_many(
			11,
			array(
				'post_type' => 'llms_access_plan',
			)
		);

		$course = $this->factory->post->create();
		foreach ( $access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', $course );
		}

		// Create orphan access plans.
		$orphan_access_plan_ids = $this->factory->post->create_many(
			10,
			array(
				'post_type' => 'llms_access_plan',
			)
		);

		foreach ( $orphan_access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', end( $orphan_access_plan_ids ) + 1 );
		}

		llms_update_4150_remove_orphan_access_plans();

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
	 * Test "pagination" in llms_update_4150_remove_orphan_access_plans()
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	public function test_update_4150_remove_orphan_access_plans_pagination() {

		// Create orphan access plans.
		$orphan_access_plan_ids = $this->factory->post->create_many(
			110, // Each page is of 50 orphan access plans.
			array(
				'post_type' => 'llms_access_plan',
			)
		);

		foreach ( $orphan_access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', end( $orphan_access_plan_ids ) + 1 );
		}

		$loops = 0;
		// Check how many times the update function needs to run.
		// Internally we fetch 50 orphan access plans at time, we expect it to run the following number of times:
		$expected_loops = 3;
		while ( llms_update_4150_remove_orphan_access_plans() ) {
			$loops++;
		}

		$this->assertEquals( $expected_loops, $loops );
		$this->assertEquals( get_transient( 'llms_update_4150_remove_orphan_access_plans' ), 'complete' );

		// Expect no orphan access plans.
		$this->assertEquals(
			0,
			count(
				get_posts(
					array(
						'include'     => $orphan_access_plan_ids,
						'post_type'   => 'llms_access_plan',
						'numberposts' => 200
					)
				)
			)
		);

	}


	/**
	 * Test llms_update_4150_update_db_version()
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		llms_update_4150_update_db_version();

		$this->assertNotEquals( '4.15.0', get_option( 'lifterlms_db_version' ) );

		// Unlock the db version update.
		set_transient( 'llms_update_4150_remove_orphan_access_plans', 'complete', DAY_IN_SECONDS );

		llms_update_4150_update_db_version();

		$this->assertEquals( '4.15.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

}
