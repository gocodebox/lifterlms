<?php
/**
 * Tests for the LLMS_Install Class
 * @since    3.3.1
 * @version  [version]
 */
class LLMS_Test_Install extends LLMS_UnitTestCase {

	/**
	 * Tests for check_version()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_check_version() {

		// ensure the database update runs
		update_option( 'lifterlms_current_version', (float) LLMS()->version - 1 );
		update_option( 'lifterlms_db_version', LLMS()->version );
		LLMS_Install::check_version();
		$this->assertTrue( did_action( 'lifterlms_updated' ) === 1 );

		// ensure that if both are equal the database doesn't run again
		update_option( 'lifterlms_current_version', LLMS()->version );
		update_option( 'lifterlms_db_version', LLMS()->version );
		LLMS_Install::check_version();
		$this->assertTrue( did_action( 'lifterlms_updated' ) === 1 );

	}

	/**
	 * Tests for create_cron_jobs()
	 * @return   void
	 * @since    3.3.1
	 * @version  [version]
	 */
	public function test_create_cron_jobs() {

		// clear crons
		wp_clear_scheduled_hook( 'llms_cleanup_tmp' );
		wp_clear_scheduled_hook( 'llms_send_tracking_data' );

		LLMS_Install::create_cron_jobs();
		$this->assertTrue( is_numeric( wp_next_scheduled( 'llms_cleanup_tmp' ) ) );
		$this->assertTrue( is_numeric( wp_next_scheduled( 'llms_send_tracking_data' ) ) );

	}

	/**
	 * Tests for create_difficulties() & remove_difficulties()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_create_difficulties_crud() {

		// terms may or may not exist and should exist after creation
		LLMS_Install::create_difficulties();
		foreach( LLMS_Install::get_difficulties() as $name ) {
			$this->assertInstanceOf( 'WP_Term', get_term_by( 'name', $name, 'course_difficulty' ) );
		}

		// terms should not exist after deleting terms
		LLMS_Install::remove_difficulties();
		foreach( LLMS_Install::get_difficulties() as $name ) {
			$this->assertFalse( get_term_by( 'name', $name, 'course_difficulty' ) );
		}

		// terms should exist after creating difficulties
		LLMS_Install::create_difficulties();
		foreach( LLMS_Install::get_difficulties() as $name ) {
			$this->assertInstanceOf( 'WP_Term', get_term_by( 'name', $name, 'course_difficulty' ) );
		}

	}

	/**
	 * Test create_files()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
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
	 * @return   void
	 * @since    3.3.1
	 * @version  3.5.1
	 */
	public function test_create_options() {

		// clear options
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'lifterlms\_%';" );

		// install options
		LLMS_Install::create_options();

		// check they exist
		$settings = LLMS_Admin_Settings::get_settings_tabs();

		foreach ( $settings as $section ) {
			// skip general settings since this screen doesn't actually have any settings on it
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
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_create_pages() {

		// clear options
		delete_option( 'lifterlms_shop_page_id' );
		delete_option( 'lifterlms_memberships_page_id' );
		delete_option( 'lifterlms_checkout_page_id' );
		delete_option( 'lifterlms_myaccount_page_id' );

		LLMS_Install::create_pages();

		$this->assertGreaterThan( 0, get_option( 'lifterlms_shop_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_memberships_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_checkout_page_id' ) );
		$this->assertGreaterThan( 0, get_option( 'lifterlms_myaccount_page_id' ) );

		// Delete pages
		wp_delete_post( get_option( 'lifterlms_shop_page_id' ), true );
		wp_delete_post( get_option( 'lifterlms_memberships_page_id' ), true );
		wp_delete_post( get_option( 'lifterlms_checkout_page_id' ), true );
		wp_delete_post( get_option( 'lifterlms_myaccount_page_id' ), true );

		// Clear options
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
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_create_tables() {

		global $wpdb;

		// clear tables
		$wpdb->query(
			"DROP TABLE IF EXISTS
				{$wpdb->prefix}lifterlms_user_postmeta,
				{$wpdb->prefix}lifterlms_product_to_voucher,
				{$wpdb->prefix}lifterlms_voucher_code_redemptions,
				{$wpdb->prefix}lifterlms_vouchers_codes
			;"
		);

		// install tables
		LLMS_Install::create_tables();

		// ensure they exist
		$this->assertEquals( "{$wpdb->prefix}lifterlms_user_postmeta", $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}lifterlms_user_postmeta'" ) );
		$this->assertEquals( "{$wpdb->prefix}lifterlms_product_to_voucher", $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}lifterlms_product_to_voucher'" ) );
		$this->assertEquals( "{$wpdb->prefix}lifterlms_voucher_code_redemptions", $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}lifterlms_voucher_code_redemptions'" ) );
		$this->assertEquals( "{$wpdb->prefix}lifterlms_vouchers_codes", $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}lifterlms_vouchers_codes'" ) );

	}

	/**
	 * Test create_visibilities()
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function test_create_visibilities() {

		// terms may or may not exist and should exist after creation
		LLMS_Install::create_visibilities();
		foreach( array_keys( llms_get_product_visibility_options() ) as $name ) {
			$this->assertInstanceOf( 'WP_Term', get_term_by( 'name', $name, 'llms_product_visibility' ) );
		}

	}

	/**
	 * Test get_difficulties()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_get_difficulties() {

		$this->assertTrue( ! empty( LLMS_Install::get_difficulties() ) );
		$this->assertTrue( is_array( LLMS_Install::get_difficulties() ) );

	}

	/**
	 * Test update_db_version()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_update_db_version() {

		LLMS_Install::update_db_version( '1' );
		$this->assertEquals( '1', get_option( 'lifterlms_db_version' ) );

		LLMS_Install::update_db_version();
		$this->assertEquals( LLMS()->version, get_option( 'lifterlms_db_version' ) );

		LLMS_Install::update_db_version( '1.2.3' );
		$this->assertEquals( '1.2.3', get_option( 'lifterlms_db_version' ) );

	}

	/**
	 * Test update_llms_version()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_update_llms_version() {

		LLMS_Install::update_llms_version( '1' );
		$this->assertEquals( '1', get_option( 'lifterlms_current_version' ) );

		LLMS_Install::update_llms_version();
		$this->assertEquals( LLMS()->version, get_option( 'lifterlms_current_version' ) );

		LLMS_Install::update_llms_version( '1.2.3' );
		$this->assertEquals( '1.2.3', get_option( 'lifterlms_current_version' ) );

	}

	/**
	 * Tests for install() function
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_install() {

		// clean existing install first
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', true );
			define( 'LLMS_REMOVE_ALL_DATA', true );
		}

		include( dirname( dirname( dirname( __FILE__ ) ) ) . '/uninstall.php' );

		LLMS_Install::install();
		$this->assertTrue( get_option( 'lifterlms_current_version' ) === LLMS()->version );

	}

}
