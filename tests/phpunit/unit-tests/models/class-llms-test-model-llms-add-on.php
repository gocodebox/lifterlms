<?php
/**
 * Test Add On model
 *
 * @package LifterLMS_Tests/Models
 *
 * @group LLMS_Add_On
 * @group add_ons
 *
 * @since 4.21.3
 */
class LLMS_Test_Add_On extends LLMS_Unit_Test_Case {

	/**
	 * Retrieve a mock plugin add-on for testing
	 *
	 * @since 5.1.1
	 *
	 * @param boolean $install  If true, calls `install_mock_addon()` to physically install the mock plugin.
	 * @param boolean $activate If true and `$install` is also true, activates the mock plugin following installation.
	 * @return LLMS_Add_On
	 */
	private function get_mock_addon( $install = false, $activate = false ) {

		$asset = 'lifterlms-mock-addon.php';
		$dir   = 'lifterlms-mock-addon/';
		$file  = $dir . $asset;
		if ( $install ) {
			LLMS_Unit_Test_Files::copy_asset( $asset, trailingslashit( WP_PLUGIN_DIR  ). $dir );
			if ( $activate ) {
				activate_plugin( $file );
			}
		}

		return new LLMS_Add_On( array(
			'title'       => 'LLMS Mock Add-on',
			'update_file' => $file,
			'id'          => 'lifterlms-com-mock-addon',
			'type'        => 'plugin',
		) );

	}

	/**
	 * Retrieves the first `twenty*` theme that's installed on the site.
	 *
	 * We used to hardcode the theme but with WP 5.9 the core included themes list (in the test environment) includes
	 * only twentytwenty and later. Doing it this way is safer because we don't actually care what theme were using,
	 * we just need one that *is installed*.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function get_wp_included_default_theme() {

		foreach ( array_keys( wp_get_themes() ) as $slug ) {

			if ( 0 === strpos( $slug, 'twenty' ) ) {
				return $slug;
			}

		}

		return 'default';

	}

	/**
	 * Test constructor with an addon array passed in.
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_constructor_with_addon() {

		$mock = array(
			'id'  => 'test',
			'key' => 'val',
		);
		$addon = new LLMS_Add_On( $mock );

		$this->assertEquals( $mock, LLMS_Unit_Test_Util::get_private_property_value( $addon, 'data' ) );
		$this->assertEquals( 'test', LLMS_Unit_Test_Util::get_private_property_value( $addon, 'id' ) );

	}

	/**
	 * Test constructor with a lookup
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_constructor_with_lookup() {

		$addon = new LLMS_Add_On( 'lifterlms-com-lifterlms', 'id' );

		$this->assertEquals( 'lifterlms-com-lifterlms', $addon->get( 'id' ) );
		$this->assertEquals( 'lifterlms-com-lifterlms', LLMS_Unit_Test_Util::get_private_property_value( $addon, 'id' ) );

	}

	public function test_get() {

		$addon = new LLMS_Add_On( 'lifterlms-com-lifterlms', 'id' );

		// Non-existent prop.
		$this->assertSame( '', $addon->get( 'fake' ) );

		// Real prop.
		$this->assertSame( 'LifterLMS', $addon->get( 'title' ) );

	}

	/**
	 * Test plugin activation and deactivation
	 *
	 * Also tests the `is_active()` and partially the `get_status()` methods.
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_activate_deactivate_plugin() {

		$addon    = new LLMS_Add_On( array( 'title' => 'Akismet', 'type' => 'plugin', 'update_file' => 'akismet/akismet.php' ) );
		$activate = $addon->activate();
		$this->assertEquals( $activate, 'Akismet was successfully activated.' );

		$this->assertTrue( $addon->is_active() );
		$this->assertEquals( 'active', $addon->get_status() );
		$this->assertEquals( 'Active', $addon->get_status( true ) );

		$deactivate = $addon->deactivate();
		$this->assertEquals( $deactivate, 'Akismet was successfully deactivated.' );

		$this->assertFalse( $addon->is_active() );
		$this->assertEquals( 'inactive', $addon->get_status() );
		$this->assertEquals( 'Inactive', $addon->get_status( true ) );

	}

	/**
	 * Test theme activation
	 *
	 * Also tests the `is_active()` and partially the `get_status()` methods.
	 *
	 * @since 4.21.3
	 * @since [version] Make sure the theme is installed before testing that it's inactivate.
	 *
	 * @return void
	 */
	public function test_activate_theme_success() {

		$addon = new LLMS_Add_On( array( 'title' => 'Default Theme', 'type' => 'theme', 'update_file' => $this->get_wp_included_default_theme() ) );

		$this->assertFalse( $addon->is_active() );
		$this->assertEquals( 'inactive', $addon->get_status() );
		$this->assertEquals( 'Inactive', $addon->get_status( true ) );

		$res = $addon->activate();
		$this->assertEquals( $res, 'Default Theme was successfully activated.' );

		$this->assertTrue( $addon->is_active() );
		$this->assertEquals( 'active', $addon->get_status() );
		$this->assertEquals( 'Active', $addon->get_status( true ) );

	}

	/**
	 * Test activate() error for a plugin
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_activate_error() {

		$addon    = new LLMS_Add_On( array( 'title' => 'fake', 'type' => 'plugin' ) );
		$activate = $addon->activate();

		$this->assertIsWPError( $activate);
		$this->assertWPErrorCodeEquals( 'activation', $activate );

	}

	/**
	 * Test deactivate() error for a plugin
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_deactivate_error() {

		$addon      = new LLMS_Add_On( array( 'title' => 'fake' ) );
		$deactivate = $addon->deactivate();
		$this->assertIsWPError( $deactivate );
		$this->assertWPErrorCodeEquals( 'deactivation', $deactivate );

	}

	/**
	 * Test get_channel_subscription()
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_get_channel_subscription() {

		$addon = new LLMS_Add_On();
		$this->assertEquals( 'stable', $addon->get_channel_subscription() );

	}

	/**
	 * Test get_type()
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_get_type() {

		$tests = array(
			'theme'    => array( 'type' => 'theme' ),
			'plugin'   => array( 'type' => 'plugin' ),
			'fake'     => array( 'type' => 'fake' ),
			'bundle'   => array( 'categories' => array( 'bundles' => 'Bundles' ) ),
			'external' => array( 'categories' => array( 'third-party' => 'Third Party' ) ),
			'support'  => array( 'categories' => array() ),
		);

		foreach ( $tests as $expected => $data ) {
			$addon = new LLMS_Add_On( $data );
			$this->assertEquals( $expected, $addon->get_type(), $expected );
		}

	}

	/**
	 * Test get_permalink()
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_get_permalink() {

		$addon = new LLMS_Add_On( 'lifterlms-com-lifterlms', 'id' );
		$expect = 'https://lifterlms.com/product/lifterlms/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Add-Ons%20Screen&utm_content=LifterLMS%20Ad%20' . llms()->version;
		$this->assertEquals( $expect, $addon->get_permalink() );

	}

	/**
	 * Test get_install_status() and is_installed()
	 *
	 * @since 4.21.3
	 * @since [version] Make sure the theme is installed before checking it's install status.
	 *
	 * @return void
	 */
	public function test_install_status() {

		// Invalid.
		$addon = new LLMS_Add_On();
		$this->assertEquals( 'none', $addon->get_install_status() );
		$this->assertEquals( 'N/A', $addon->get_install_status( true ) );

		// Plugin installed.
		$addon = new LLMS_Add_On( array( 'type' => 'plugin', 'update_file' => 'akismet/akismet.php' ) );
		$this->assertEquals( 'installed', $addon->get_install_status() );
		$this->assertEquals( 'Installed', $addon->get_install_status( true ) );

		// Plugin not installed.
		$addon = new LLMS_Add_On( array( 'type' => 'plugin', 'update_file' => 'mock/mock.php' ) );
		$this->assertEquals( 'uninstalled', $addon->get_install_status() );
		$this->assertEquals( 'Not Installed', $addon->get_install_status( true ) );

		// Theme installed.
		$addon = new LLMS_Add_On( array( 'type' => 'theme', 'update_file' => $this->get_wp_included_default_theme() ) );
		$this->assertEquals( 'installed', $addon->get_install_status() );
		$this->assertEquals( 'Installed', $addon->get_install_status( true ) );

		// Theme not installed.
		$addon = new LLMS_Add_On( array( 'type' => 'theme', 'update_file' => 'fake' ) );
		$this->assertEquals( 'uninstalled', $addon->get_install_status() );
		$this->assertEquals( 'Not Installed', $addon->get_install_status( true ) );

	}

	/**
	 * Test lookup_add_on() when errors are encountered.
	 *
	 * @since 4.21.3
	 *
	 * @return void
	 */
	public function test_lookup_errors() {

		$addon = new LLMS_Add_On();

		// Mock the HTTP request to find addons for an error.
		$err = new WP_Error( 'mocked-err', 'Mocked Message', array( 'data' => 'mocked' ) );
		$this->mock_http_request( 'https://lifterlms.com/wp-json/llms/v3/products', $err );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $addon, 'lookup_add_on', array( 'mock', 'mock' ) ) );

		// Mock the HTTP request to return an empty array for some reason..
		$ret = array( 'items' => array() );
		$this->mock_http_request( 'https://lifterlms.com/wp-json/llms/v3/products', $ret );

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $addon, 'lookup_add_on', array( 'mock', 'mock' ) ) );

	}

	/**
	 * Test uninstall() for an add-on that isn't installed
	 *
	 * @since 5.1.1
	 *
	 * @return void
	 */
	public function test_uninstall_error_addon_not_installed() {

		$addon = llms_get_add_on( 'lifterlms-groups', 'slug' );
		$res   = $addon->uninstall();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'not-installed', $res );

	}

	/**
	 * Test uninstall() error for an active add-on.
	 *
	 * @since 5.1.1
	 *
	 * @return void
	 */
	public function test_uninstall_error_is_activate() {

		$addon = $this->get_mock_addon( true, true );
		$res   = $addon->uninstall();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'uninstall-active', $res );

	}

	/**
	 * Test uninstall() error for an invalid add-on type.
	 *
	 * @since 5.1.1
	 *
	 * @return void
	 */
	public function test_uninstall_real_error_invalid_type() {

		$addon = new LLMS_Add_On( array( 'type' => 'fake' ) );
		$res = LLMS_Unit_Test_Util::call_method( $addon, 'uninstall_real' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'uninstall-invalid-type', $res );

	}

	/**
	 * Test uninstall() success for a plugin add-on
	 *
	 * @since 5.1.1
	 *
	 * @return void
	 */
	public function test_uninstall_plugin_real_success() {

		$addon = $this->get_mock_addon( true, false );
		$res   = LLMS_Unit_Test_Util::call_method( $addon, 'uninstall_real' );
		$this->assertEquals( 'LLMS Mock Add-on was successfully uninstalled.', $res );

	}
}
