<?php

class LLMS_Test_Install extends LLMS_UnitTestCase {

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

}
