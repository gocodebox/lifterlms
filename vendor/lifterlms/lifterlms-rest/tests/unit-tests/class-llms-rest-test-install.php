<?php
/**
 * Test Install class.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group install
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Install extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Test LLMS_REST_Install::check_version()
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_check_version() {

		$actions = did_action( 'llms_rest_updated' );

		// Should run if no option found.
		delete_option( 'llms_rest_version' );
		LLMS_REST_Install::check_version();
		$actions++;
		$this->assertEquals( $actions, did_action( 'llms_rest_updated' ) );

		// Should run if version is not equal to current version.
		update_option( 'llms_rest_version', '0.0.1-alpha.1' );
		LLMS_REST_Install::check_version();
		$actions++;
		$this->assertEquals( $actions, did_action( 'llms_rest_updated' ) );

		// Shouldn't run b/c versions are equal
		LLMS_REST_Install::check_version();
		update_option( 'llms_rest_version', LLMS_REST_API()->version );
		$this->assertEquals( $actions, did_action( 'llms_rest_updated' ) );

		// Shouldn't run b/c iframe request.
		define( 'IFRAME_REQUEST', true );
		LLMS_REST_Install::check_version();
		$this->assertEquals( $actions, did_action( 'llms_rest_updated' ) );

	}

	/**
	 * Test the LLMS_REST_Install::get_schema() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_schema() {

		global $wpdb;

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		$default = "CREATE TABLE `{$wpdb->prefix}lifterlms_fake_table` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT
) $collate;";

		$schema = LLMS_REST_Install::get_schema( $default, $collate );
		$this->assertTrue( is_string( $schema ) );
		$this->assertTrue( 0 === strpos( $schema, $default ) );
		$this->assertTrue( false !== strpos( $schema, "CREATE TABLE `{$wpdb->prefix}lifterlms_api_keys`" ) );
		$this->assertTrue( false !== strpos( $schema, "CREATE TABLE `{$wpdb->prefix}lifterlms_webhooks`" ) );

	}

	/**
	 * Test the install method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_install() {

		// clean existing install first.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', true );
			define( 'LLMS_REMOVE_ALL_DATA', true );
		}
		include dirname( dirname( dirname( __FILE__ ) ) ) . '/uninstall.php';

		LLMS_REST_Install::install();
		$this->assertEquals( get_option( 'llms_rest_version' ), LLMS_REST_API()->version );

		global $wpdb;
		$this->assertEquals( $wpdb->prefix . 'lifterlms_api_keys', $wpdb->get_var( "SHOW TABLES LIKE '%lifterlms_api_keys'" ) );
		$this->assertEquals( $wpdb->prefix . 'lifterlms_webhooks', $wpdb->get_var( "SHOW TABLES LIKE '%lifterlms_webhooks'" ) );

	}

	/**
	 * Test the update_version() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_version() {

		LLMS_REST_Install::update_version( '1' );
		$this->assertEquals( '1', get_option( 'llms_rest_version' ) );

		LLMS_REST_Install::update_version();
		$this->assertEquals( LLMS_REST_API()->version, get_option( 'llms_rest_version' ) );

		LLMS_REST_Install::update_version( '1.2.3' );
		$this->assertEquals( '1.2.3', get_option( 'llms_rest_version' ) );

	}

}
