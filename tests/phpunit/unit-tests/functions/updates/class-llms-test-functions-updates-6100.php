<?php
/**
* Test updates functions when updating to 6.10.0.
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_6100
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_6100 extends LLMS_UnitTestCase {

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
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-6100.php';
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Test migrate_spanish_users().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_migrate_spanish_users() {

		// Create one spanish user with a spanish province that should be migrated to a new one.
		$student_spanish_prov_migrate = $this->factory->user->create();
		update_user_meta( $student_spanish_prov_migrate, 'llms_billing_country', 'ES' );
		update_user_meta( $student_spanish_prov_migrate, 'llms_billing_state', 'AS' ); // 'AS' turned into 'O'.

		// Create a spanish user with a spanish province which doesn't exist anymore.
		$student_spanish_prov_migrate_2 = $this->factory->user->create();
		update_user_meta( $student_spanish_prov_migrate_2, 'llms_billing_country', 'ES' );
		update_user_meta( $student_spanish_prov_migrate_2, 'llms_billing_state', 'EX' );

		// Create another two spanish users with a spanish province that should not be migrated to a new one.
		$student_spanish_prov_migrate_not = $this->factory->user->create();
		update_user_meta( $student_spanish_prov_migrate_not, 'llms_billing_country', 'ES' );
		update_user_meta( $student_spanish_prov_migrate_not, 'llms_billing_state', 'CE' ); // Didn't change.

		$student_spanish_prov_migrate_not_2 = $this->factory->user->create();
		update_user_meta( $student_spanish_prov_migrate_not_2, 'llms_billing_country', 'ES' );
		update_user_meta( $student_spanish_prov_migrate_not_2, 'llms_billing_state', 'B' ); // Wasn't there before.

		// Create an US user with a state that looks like a spanish province that should be migrated.
		$student_spanish_prov_migrate_not_3 = $this->factory->user->create();
		update_user_meta( $student_spanish_prov_migrate_not_3, 'llms_billing_country', 'US' );
		update_user_meta( $student_spanish_prov_migrate_not_3, 'llms_billing_state', 'AS' ); // 'AS' turned into 'O'.


		\LLMS\Updates\Version_6_10_0\migrate_spanish_users();

		// Migrated 'AS' to 'O'.
		$this->assertEquals(
			'O',
			get_user_meta( $student_spanish_prov_migrate, 'llms_billing_state', true )
		);
		// Migrated 'S' to ''.
		$this->assertEquals(
			'',
			get_user_meta( $student_spanish_prov_migrate_2, 'llms_billing_state', true )
		);
		// Unmigrated 'CE'.
		$this->assertEquals(
			'CE',
			get_user_meta( $student_spanish_prov_migrate_not, 'llms_billing_state', true )
		);
		// Unmigrated 'AN'.
		$this->assertEquals(
			'B',
			get_user_meta( $student_spanish_prov_migrate_not_2, 'llms_billing_state', true )
		);
		// Unmigrated american user with 'AS' as state/province.
		$this->assertEquals(
			'AS',
			get_user_meta( $student_spanish_prov_migrate_not_3, 'llms_billing_state', true )
		);

	}

	/**
	 * Test update_db_version().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		\LLMS\Updates\Version_6_10_0\update_db_version();

		$this->assertEquals( \LLMS\Updates\Version_6_10_0\_get_db_version(), get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

}
