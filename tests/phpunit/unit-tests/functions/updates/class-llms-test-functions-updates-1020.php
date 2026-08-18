<?php
/**
 * Test updates functions when updating to 10.2.0.
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_1020
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_1020 extends LLMS_UnitTestCase {

	/**
	 * Setup before class.
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-1020.php';
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Test delete_zero_time_tracking_caches().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_delete_zero_time_tracking_caches() {

		$user_id = $this->factory->user->create();

		// Zero-value cache rows: should be deleted.
		update_user_meta( $user_id, 'llms_lesson_time_123', '0' );
		update_user_meta( $user_id, 'llms_lesson_time_456', 0 );
		update_user_meta( $user_id, 'llms_course_time_789', '0' );

		// Non-zero cache rows: should be kept.
		update_user_meta( $user_id, 'llms_lesson_time_321', 120 );
		update_user_meta( $user_id, 'llms_course_time_987', 360 );

		// Admin override rows (array values) share the meta key prefix: should be kept.
		update_user_meta( $user_id, 'llms_lesson_time_override_123', array( 'admin_id' => 1 ) );

		// Unrelated meta: should be kept.
		update_user_meta( $user_id, 'llms_unrelated_meta', '0' );

		while ( \LLMS\Updates\Version_10_2_0\delete_zero_time_tracking_caches() ) {
			continue;
		}

		$this->assertEquals( '', get_user_meta( $user_id, 'llms_lesson_time_123', true ) );
		$this->assertEquals( '', get_user_meta( $user_id, 'llms_lesson_time_456', true ) );
		$this->assertEquals( '', get_user_meta( $user_id, 'llms_course_time_789', true ) );

		$this->assertEquals( 120, get_user_meta( $user_id, 'llms_lesson_time_321', true ) );
		$this->assertEquals( 360, get_user_meta( $user_id, 'llms_course_time_987', true ) );
		$this->assertEquals( array( 'admin_id' => 1 ), get_user_meta( $user_id, 'llms_lesson_time_override_123', true ) );
		$this->assertEquals( '0', get_user_meta( $user_id, 'llms_unrelated_meta', true ) );
	}

	/**
	 * Test update_db_version().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		delete_option( 'lifterlms_db_version' );

		\LLMS\Updates\Version_10_2_0\update_db_version();

		$this->assertEquals( \LLMS\Updates\Version_10_2_0\_get_db_version(), get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );
	}
}
