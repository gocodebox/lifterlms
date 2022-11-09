<?php
/**
 * Tests for the LLMS_Install Class
 *
 * @package LifterLMS/Tests
 *
 * @group install
 *
 * @since 3.3.1
 * @since 3.37.8 Fix directory path to uninstall.php.
 * @since 4.0.0 Test creation of all tables; fix caching issue when testing full install; add new cron test.
 * @since 4.5.0 Test log backup cron.
 * @since 5.0.0 Added tests for the get_can_install_user_id() method.
 */
class LLMS_Test_Install extends LLMS_UnitTestCase {

	/**
	 * Setup the test.
	 *
	 * @since [version]
	 */
	public function set_up() {

		parent::set_up();
		// Can be removed after REST is updated to install it's tables via new system.
		remove_filter( 'llms_install_get_schema', array( 'LLMS_REST_Install', 'get_schema' ), 20 );

	}

	/**
	 * Tear down the test.
	 *
	 * @since [version]
	 */
	public function tear_down() {

		parent::tear_down();
		// Can be removed after REST is updated to install it's tables via new system.
		add_filter( 'llms_install_get_schema', array( 'LLMS_REST_Install', 'get_schema' ), 20, 2 );

	}

	/**
	 * Tests for check_version()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_check_version() {

		// Ensure the database update runs.
		update_option( 'lifterlms_current_version', (float) llms()->version - 1 );
		update_option( 'lifterlms_db_version', llms()->version );
		LLMS_Install::check_version();
		$this->assertTrue( did_action( 'lifterlms_updated' ) === 1 );

		// Ensure that if both are equal the database doesn't run again.
		update_option( 'lifterlms_current_version', llms()->version );
		update_option( 'lifterlms_db_version', llms()->version );
		LLMS_Install::check_version();
		$this->assertTrue( did_action( 'lifterlms_updated' ) === 1 );

	}

	/**
	 * Tests for create_cron_jobs()
	 *
	 * @since 3.3.1
	 * @since 3.28.0 Unknown.
	 * @since 4.0.0 Test session cleanup cron.
	 * @since 4.5.0 Test log backup cron.
	 *
	 * @return void
	 */
	public function test_create_cron_jobs() {

		$crons = array(
			'llms_cleanup_tmp',
			'llms_backup_logs',
			'llms_send_tracking_data',
			'llms_delete_expired_session_data',
		);

		// Clear.
		foreach ( $crons as $cron ) {
			wp_clear_scheduled_hook( $cron );
			$this->assertFalse( wp_next_scheduled( $cron ) );
		}

		LLMS_Install::create_cron_jobs();

		// Scheduled.
		foreach ( $crons as $cron ) {
			$this->assertTrue( is_numeric( wp_next_scheduled( $cron ) ) );
		}

	}

	/**
	 * Tests for create_difficulties() & remove_difficulties()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_create_difficulties_crud() {

		// Terms may or may not exist and should exist after creation.
		LLMS_Install::create_difficulties();
		foreach( LLMS_Install::get_difficulties() as $name ) {
			$this->assertInstanceOf( 'WP_Term', get_term_by( 'name', $name, 'course_difficulty' ) );
		}

		// Terms should not exist after deleting terms.
		LLMS_Install::remove_difficulties();
		foreach( LLMS_Install::get_difficulties() as $name ) {
			$this->assertFalse( get_term_by( 'name', $name, 'course_difficulty' ) );
		}

		// Terms should exist after creating difficulties.
		LLMS_Install::create_difficulties();
		foreach( LLMS_Install::get_difficulties() as $name ) {
			$this->assertInstanceOf( 'WP_Term', get_term_by( 'name', $name, 'course_difficulty' ) );
		}

	}

	/**
	 * Test create_files()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_create_files() {

		LLMS_Install::create_files();
		$this->assertTrue( file_exists( LLMS_LOG_DIR ) );
		$this->assertTrue( file_exists( LLMS_LOG_DIR . '.htaccess' ) );
		$this->assertTrue( file_exists( LLMS_LOG_DIR . 'index.html' ) );
		$this->assertFalse( file_exists( LLMS_LOG_DIR . 'fail.txt' ) );

	}

	/**
	 * Tests for create_options()
	 *
	 * @since 3.3.1
	 * @since 3.5.1 Unknown.
	 *
	 * @return void
	 */
	public function test_create_options() {

		// Clear options.
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'lifterlms\_%';" );

		// Install options.
		LLMS_Install::create_options();

		// Check they exist.
		$settings = LLMS_Admin_Settings::get_settings_tabs();

		foreach ( $settings as $section ) {
			// Skip general settings since this screen doesn't actually have any settings on it.
			if ( 'general' === $section->id ) {
				continue;
			}
			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$this->assertEquals( $value['default'], get_option( $value['id'] ) );
				}
			}
		}

	}

	/**
	 * Tests for create_pages()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_create_pages() {

		// Clear options.
		delete_option( 'lifterlms_shop_page_id' );
		delete_option( 'lifterlms_memberships_page_id' );
		delete_option( 'lifterlms_checkout_page_id' );
		delete_option( 'lifterlms_myaccount_page_id' );

		LLMS_Install::create_pages();

		$this->assertGreaterThan( 0, get_option( 'lifterlms_shop_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_memberships_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_checkout_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_myaccount_page_id' ) );

		// Delete pages.
		wp_delete_post( get_option( 'lifterlms_shop_page_id' ), true );
		wp_delete_post( get_option( 'lifterlms_memberships_page_id' ), true );
		wp_delete_post( get_option( 'lifterlms_checkout_page_id' ), true );
		wp_delete_post( get_option( 'lifterlms_myaccount_page_id' ), true );

		// Clear options.
		delete_option( 'lifterlms_shop_page_id' );
		delete_option( 'lifterlms_memberships_page_id' );
		delete_option( 'lifterlms_checkout_page_id' );
		delete_option( 'lifterlms_myaccount_page_id' );

		LLMS_Install::create_pages();

		$this->assertGreaterThan( 0, get_option( 'lifterlms_shop_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_memberships_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_checkout_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_myaccount_page_id' ) );

	}

	/**
	 * Tests for create_tables()
	 *
	 * @since 3.3.1
	 * @since 4.0.0 Add missing tables.
	 * @since [version] Update test to test against real (not temporary) tables.
	 *
	 * @return void
	 */
	public function test_create_tables() {

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		global $wpdb;

		$tables = array_map(
			function( string $table ): string {
				$table = llms()->db()->get_table( $table );
				return $table->get_prefixed_name();
			},
			llms()->db()->get_core_tables()
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE {$table};" );
		}

		// Install tables.
		LLMS_Install::create_tables();

		foreach ( $tables as $table ) {
			$this->assertEquals( $table, $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) );
		}

		// Reinstall, ensures the temporary tables are available for future tests.
		add_filter( 'query', array( $this, '_create_temporary_tables' ) );
		add_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		LLMS_Install::create_tables();

	}

	/**
	 * Tests {@see LLMS_Install::create_tables} backwards compatibility with the
	 * deprecated `llms_install_get_schema` hook.
	 *
	 * @since [version]
	 *
	 * @expectedDeprecated llms_install_get_schema
	 */
	public function test_create_tables_deprecated(): void {

		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		global $wpdb;
		$table = "{$wpdb->prefix}test_custom_table";

		$handler = function( string $schema, string $options ) use ( $table ): string {
			$schema .= "CREATE TABLE `{$table}` (
				`id` int(5),
				PRIMARY KEY (`id`)
			) {$options};";
			return $schema;
		};

		add_filter( 'llms_install_get_schema', $handler, 20, 2 );

		LLMS_Install::create_tables();

		$this->assertEquals(
			$table,
			$wpdb->get_var( "SHOW TABLES LIKE '{$table}';" )
		);

		$wpdb->query( "DROP TABLE {$table};" );

		remove_filter( 'llms_install_get_schema', $handler, 20, 2 );

	}

	/**
	 * Test create_visibilities()
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function test_create_visibilities() {

		// Terms may or may not exist and should exist after creation.
		LLMS_Install::create_visibilities();
		foreach( array_keys( llms_get_product_visibility_options() ) as $name ) {
			$this->assertInstanceOf( 'WP_Term', get_term_by( 'name', $name, 'llms_product_visibility' ) );
		}

	}

	/**
	 * Test get_difficulties()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_get_difficulties() {

		$this->assertTrue( ! empty( LLMS_Install::get_difficulties() ) );
		$this->assertTrue( is_array( LLMS_Install::get_difficulties() ) );

	}

	/**
	 * Test update_db_version()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		LLMS_Install::update_db_version( '1' );
		$this->assertEquals( '1', get_option( 'lifterlms_db_version' ) );

		LLMS_Install::update_db_version();
		$this->assertEquals( llms()->version, get_option( 'lifterlms_db_version' ) );

		LLMS_Install::update_db_version( '1.2.3' );
		$this->assertEquals( '1.2.3', get_option( 'lifterlms_db_version' ) );

	}

	/**
	 * Test update_llms_version()
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_update_llms_version() {

		LLMS_Install::update_llms_version( '1' );
		$this->assertEquals( '1', get_option( 'lifterlms_current_version' ) );

		LLMS_Install::update_llms_version();
		$this->assertEquals( llms()->version, get_option( 'lifterlms_current_version' ) );

		LLMS_Install::update_llms_version( '1.2.3' );
		$this->assertEquals( '1.2.3', get_option( 'lifterlms_current_version' ) );

	}

	/**
	 * Tests for install() function
	 *
	 * @since 3.3.1
	 * @since 3.37.8 Fix directory path to uninstall.php
	 * @since 4.0.0 Flush cache after uninstall is run.
	 *
	 * @return void
	 */
	public function test_install() {

		// Clean existing install first.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', true );
			define( 'LLMS_REMOVE_ALL_DATA', true );
		}

		include( dirname( __FILE__, 4 ) . '/uninstall.php' );

		wp_cache_flush();

		LLMS_Install::install();
		$this->assertEquals( llms()->version, get_option( 'lifterlms_current_version' ) );

	}

	/**
	 * Test get_can_install_user_id() method
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_can_install_user_id() {

		// Clean user* tables.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE $wpdb->users" );
		$wpdb->query( "TRUNCATE TABLE $wpdb->usermeta" );

		// No users, expect 0.
		$this->assertEquals( 0, LLMS_Install::get_can_install_user_id() );

		// Create a subscriber.
		$subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// No admin users, expect 0.
		$this->assertEquals( 0, LLMS_Install::get_can_install_user_id() );

		// Create two admins.
		$admins = $this->factory->user->create_many( 2, array( 'role' => 'administrator' ) );

		// Expect the first admin to be returned.
		$this->assertEquals( $admins[0], LLMS_Install::get_can_install_user_id() );

		// Log in as subscriber.
		wp_set_current_user( $subscriber );

		// Expect the first admin to be returned.
		$this->assertEquals( $admins[0], LLMS_Install::get_can_install_user_id() );

		// Log in as first admin.
		wp_set_current_user( $admins[0] );

		// Expect the first admin to be returned.
		$this->assertEquals( $admins[0], LLMS_Install::get_can_install_user_id() );

		// Log in as second admin.
		wp_set_current_user( $admins[1] );

		// Expect the second admin to be returned.
		$this->assertEquals( $admins[1], LLMS_Install::get_can_install_user_id() );

	}

}
