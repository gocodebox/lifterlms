<?php
/**
 * Test LLMS_Assets
 *
 * @package LifterLMS/Tests
 *
 * @group assets
 *
 * @since 4.4.0
 * @version 7.2.0
 */
class LLMS_Test_Assets extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 4.4.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = LLMS_Unit_Test_Util::call_method( llms(), 'init_assets' );

	}

	/**
	 * Teardown the test case.
	 *
	 * Dequeue and deregister all assets that may have been registered/enqueued during the test.
	 *
	 * @since 4.4.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		foreach ( array_keys( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'scripts' ) ) as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}

		foreach ( array_keys( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'styles' ) ) as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}

	}

	/**
	 * Test merging of defaults during construction
	 *
	 * @since 4.9.0
	 * @since 5.5.0 Add `asset_file`.
	 *
	 * @return void
	 */
	public function test_default_merge() {

		$defaults = array(
			// Base defaults shared by all asset types.
			'base'   => array(
				'base_file' => 'Some/Custom/Plugin/File.php',
				'base_url'  => 'https://mock.tld/wp-content/plugins/custom-plugin',
				'version'   => '93.29.107',
				'suffix'    => '.custom',
			),
			// Script specific defaults.
			'script' => array(
				'translate' => true, // All scripts in this plugin are translated.
			),
		);

		$expected = array(
			'base' => array(
				'base_file' => 'Some/Custom/Plugin/File.php',
				'base_url' => 'https://mock.tld/wp-content/plugins/custom-plugin',
				'suffix' => '.custom',
				'dependencies' => array(),
				'version' => '93.29.107',
			),
			'script' => array(
				'path'       => 'assets/js',
				'extension'  => '.js',
				'in_footer'  => true,
				'translate'  => true,
				'asset_file' => false,
			),
			'style' => array(
				'path' => 'assets/css',
				'extension' => '.css',
				'media' => 'all',
				'rtl' => true,
			),
		);


		$assets = new LLMS_Assets( 'mock-package-id', $defaults );
		$this->assertEquals( $expected, LLMS_Unit_Test_Util::get_private_property_value( $assets, 'defaults' ) );

	}

	/**
	 * Test define() with script assets.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_define_scripts() {

		$scripts = array(
			'llms' => array( 'src' => 'mock' ), // Overwrite an existing script.
			'mock' => array( 'src' => 'mock' ), // Define a new one.
		);

		$res = $this->main->define( 'scripts', $scripts );

		$this->assertEquals( $scripts['llms'], $res['llms'] );
		$this->assertEquals( $scripts['mock'], $res['mock'] );

	}

	/**
	 * Test define() with style assets.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_define_styles() {

		$styles = array(
			'lifterlms' => array( 'src' => 'mock' ), // Overwrite an existing style.
			'mock' => array( 'src' => 'mock' ),      // Define a new one.
		);

		$res = $this->main->define( 'styles', $styles );

		$this->assertEquals( $styles['lifterlms'], $res['lifterlms'] );
		$this->assertEquals( $styles['mock'], $res['mock'] );

	}

	/**
	 * Test define() with an invalid type.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_define_invalid_type() {

		$this->assertFalse( $this->main->define( 'fake', array() ) );

	}

	/**
	 * Test enqueue_inline()
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_enqueue_inline() {

		$this->assertEquals( 10, $this->main->enqueue_inline( 'mock-foot', 'console.log( 1 );', 'footer' ) );

		// Already enqueued.
		$this->assertEquals( 10, $this->main->enqueue_inline( 'mock-foot', 'console.log( 1 );', 'footer' ) );

		// Priority automatically incremented.
		$this->assertEquals( 10.01, $this->main->enqueue_inline( 'mock-foot-two', 'console.log( 1 );', 'footer' ) );

		// Explicit priority.
		$this->assertEquals( 25, $this->main->enqueue_inline( 'mock-head', 'console.log( 1 );', 'header', 25 ) );

		$inline = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'inline' );
		$this->assertEquals( array( 'mock-foot', 'mock-foot-two', 'mock-head' ), array_keys( $inline ) );

		foreach ( $inline as $def ) {
			$this->assertEquals( array( 'handle', 'asset', 'location', 'priority' ), array_keys( $def ) );
		}

	}

	/**
	 * Test enqueue_script() for a defined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_enqueue_script_defined() {

		$this->assertAssetNotRegistered( 'script', 'llms' );

		// Register and enqueue.
		$this->assertTrue( $this->main->enqueue_script( 'llms' ) );

		// Already registered.
		$this->assertTrue( $this->main->enqueue_script( 'llms' ) );

	}

	/**
	 * Test enqueue_script() for an undefined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_enqueue_script_undefined() {

		$this->assertFalse( $this->main->enqueue_script( 'fake-script' ) );

	}

	/**
	 * Test enqueue_style() for a defined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_enqueue_style_defined() {

		$this->assertAssetNotRegistered( 'style', 'lifterlms-styles' );

		// Register and enqueue.
		$this->assertTrue( $this->main->enqueue_style( 'lifterlms-styles' ) );

		// Already registered.
		$this->assertTrue( $this->main->enqueue_style( 'lifterlms-styles' ) );

	}

	/**
	 * Test enqueue_style() for an undefined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_enqueue_style_undefined() {

		$this->assertFalse( $this->main->enqueue_style( 'fake-style' ) );

	}

	/**
	 * Test get() method.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_get() {

		$asset = LLMS_Unit_Test_Util::call_method( $this->main, 'get', array( 'script', 'llms' ) );

		// Add the handle to the data array.
		$this->assertEquals( 'llms', $asset['handle'] );
		$this->assertArrayHasKey( 'src', $asset );
		$this->assertEquals( 'llms-core', $asset['package_id'] );

	}

	/**
	 * Test get() method for an asset with an asset.php file
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_get_with_asset_file() {

		$definition = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'scripts' )['llms-addons'];
		$asset_file = include LLMS_PLUGIN_DIR . 'assets/js/llms-admin-addons.asset.php';

		$asset = LLMS_Unit_Test_Util::call_method( $this->main, 'get', array( 'script', 'llms-addons' ) );

		$this->assertArrayHasKey( 'src', $asset );

		$this->assertEquals( $asset_file['version'], $asset['version'] );
		$this->assertEqualSets( $asset_file['dependencies'], $asset['dependencies'] );

	}

	/**
	 * Test get() method for an undefined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_get_undefined() {

		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'get', array( 'style', 'undefined-style' ) ) );

	}

	/**
	 * Test get() method for an asset which is defined with an empty array signifying that all asset values should be defaults.
	 *
	 * @since 4.4.1
	 *
	 * @see https://github.com/gocodebox/lifterlms/issues/1313
	 *
	 * @return version
	 */
	public function test_get_all_default_values() {

		add_filter( 'llms_get_style_asset_before_prep', function( $asset, $handle ) {

			if ( 'mock-style-with-all-defaults' === $handle ) {
				$asset = array();
			}

			return $asset;

		}, 10, 2 );

		$asset = LLMS_Unit_Test_Util::call_method( $this->main, 'get', array( 'style', 'mock-style-with-all-defaults' ) );

		$this->assertTrue( is_array( $asset ) );
		$this->assertEquals( 'mock-style-with-all-defaults', $asset['handle'] );

	}

	/**
	 * Test that adding an asset with a custom src will use the custom src instead of a generated one
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_get_custom_src() {

		add_filter( 'llms_get_script_asset_before_prep', function( $asset, $handle ) {

			if ( 'mock-script-custom-src' === $handle ) {
				$asset = array(
					'file_slug' => 'mock',
					'src'       => 'custom-src',
				);
			}

			return $asset;

		}, 10, 2 );

		$asset = LLMS_Unit_Test_Util::call_method( $this->main, 'get', array( 'script', 'mock-script-custom-src' ) );

		$this->assertEquals( 'custom-src', $asset['src'] );

	}

	/**
	 * Test that adding an asset with an empty suffix will not add the default suffix.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_get_no_suffix() {

		add_filter( 'llms_get_script_asset_before_prep', function( $asset, $handle ) {

			if ( 'mock-style-no-suffix' === $handle ) {
				$asset = array(
					'file_slug' => 'mock',
					'suffix'    => '',
				);
			}

			return $asset;

		}, 10, 2 );

		$asset = LLMS_Unit_Test_Util::call_method( $this->main, 'get', array( 'script', 'mock-style-no-suffix' ) );

		$this->assertEquals( '', $asset['suffix'] );



	}

	/**
	 * Test get_scripts()
	 *
	 * @since 4.4.0
	 * @since 5.5.0 Add `asset_file`.
	 * @since 7.2.0 Use `LLMS_ASSETS_VERSION` for asset versions.
	 *
	 * @return void
	 */
	public function test_get_defaults_for_scripts() {

		$expect = array(
			'base_file'    => LLMS_PLUGIN_FILE,
			'base_url'     => LLMS_PLUGIN_URL,
			'suffix'       => LLMS_ASSETS_SUFFIX,
			'dependencies' => array(),
			'version'      => LLMS_ASSETS_VERSION,
			'extension'    => '.js',
			'in_footer'    => true,
			'path'         => 'assets/js',
			'translate'    => false,
			'asset_file'   => false,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_defaults', array( 'script' ) ) );

	}

	/**
	 * Test get_styles()
	 *
	 * @since 4.4.0
	 * @since 7.2.0 Use `LLMS_ASSETS_VERSION` for asset versions.
	 *
	 * @return void
	 */
	public function test_get_defaults_for_styles() {

		$expect = array(
			'base_file'    => LLMS_PLUGIN_FILE,
			'base_url'     => LLMS_PLUGIN_URL,
			'suffix'       => LLMS_ASSETS_SUFFIX,
			'dependencies' => array(),
			'version'      => LLMS_ASSETS_VERSION,
			'extension'    => '.css',
			'media'        => 'all',
			'path'         => 'assets/css',
			'rtl'          => true,
		);
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->main, 'get_defaults', array( 'style' ) ) );

	}

	/**
	 * Test get_definitions()
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_get_definitions() {

		// Definitions returned.
		$this->assertFalse( empty( LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions', array( 'script' ) ) ) );
		$this->assertFalse( empty( LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions', array( 'style' ) ) ) );

		// Not a real asset type.
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions', array( 'fake' ) ) );

	}

	/**
	 * Test get_definitions_inline()
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_get_definitions_inline() {

		// No assets.
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'header' ) ) );
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'footer' ) ) );
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'style' ) ) );

		// Fake.
		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'fake' ) ) );

		$this->main->enqueue_inline( 'in-header', '', 'header' );
		$this->main->enqueue_inline( 'in-footer', '', 'footer' );
		$this->main->enqueue_inline( 'in-style', '', 'style' );

		// Reduces to scripts by location.
		$this->assertEquals( array( 'in-header'), array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'header' ) ) ) );
		$this->assertEquals( array( 'in-footer' ), array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'footer' ) ) ) );
		$this->assertEquals( array( 'in-style' ), array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'style' ) ) ) );

		$this->main->enqueue_inline( 'in-header-first', '', 'header', 5 );

		// Sorted by priority.
		$this->assertEquals( array( 'in-header-first', 'in-header' ), array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_definitions_inline', array( 'header' ) ) ) );

	}

	/**
	 * Test get_inline_priority()
	 *
	 * @since 4.4.0
	 * @since 7.0.0 Round mock priorities to nearest 2 decimals.
	 *
	 * @return void
	 */
	public function test_get_inline_priority() {

		$existing_priorties = array();

		$i = (float) 5;
		while ( $i <= 5.05 ) {

			$this->assertEquals( $i, LLMS_Unit_Test_Util::call_method( $this->main, 'get_inline_priority', array( 5, $existing_priorties ) ) );

			$existing_priorties[] = array( 'priority' => $i );
			$i = round( $i + 0.01, 2 );

		}

	}

	/**
	 * Test is_inline_enqueued()
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_is_inline_enqueued() {

		// Not enqueued.
		$this->assertFalse( $this->main->is_inline_enqueued( 'is-inline-enqueued' ) );

		// Enqueue.
		$this->main->enqueue_inline( 'is-inline-enqueued', 'console.log( 1 );', 'footer' );

		// Is enqueued.
		$this->assertTrue( $this->main->is_inline_enqueued( 'is-inline-enqueued' ) );

	}

	/**
	 * Test merge_asset_file() when `asset_file` is `false`.
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_merge_asset_file_disabled() {

		$asset = array(
			'base_file'    => 'fake.php',
			'asset_file'   => false,
			'dependencies' => array(),
		);

		$this->assertEquals( $asset, LLMS_Unit_Test_Util::call_method( $this->main, 'merge_asset_file', array( $asset ) ) );

	}

	/**
	 * Test output_inline()
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_output_inline() {

		add_filter( 'llms_assets_debug', '__return_false' );
		$this->main = LLMS_Unit_Test_Util::call_method( llms(), 'init_assets' );

		$this->main->enqueue_inline( 'in-header', 'console.log(1);', 'header' );
		$this->main->enqueue_inline( 'in-header-2', 'console.log(2);', 'header' );
		$this->main->enqueue_inline( 'in-footer', 'console.log(1);', 'footer' );
		$this->main->enqueue_inline( 'in-footer-2', 'console.log(2);', 'footer' );
		$this->main->enqueue_inline( 'in-style', 'body{background:red;}', 'style' );
		$this->main->enqueue_inline( 'in-style-2', 'body{color:black;}', 'style' );

		$this->assertOutputEquals( '<script id="llms-inline-header-scripts" type="text/javascript">console.log(1);console.log(2);</script>', array( $this->main, 'output_inline' ), array( 'header' ) );
		$this->assertOutputEquals( '<script id="llms-inline-footer-scripts" type="text/javascript">console.log(1);console.log(2);</script>', array( $this->main, 'output_inline' ), array( 'footer' ) );

		$this->assertOutputEquals( '<style id="llms-inline-styles" type="text/css">body{background:red;}body{color:black;}</style>', array( $this->main, 'output_inline' ), array( 'style' ) );

		remove_filter( 'llms_assets_debug', '__return_false' );

	}

	/**
	 * Test prepare_inline_asset_for_output(): not in debug mode, scripts & styles work the same.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_prepare_inline_asset_for_output() {

		$asset = array(
			'handle' => 'fake-handle',
			'asset'  => 'console.log(1);',
		);

		add_filter( 'llms_assets_debug', '__return_false' );
		$this->main = LLMS_Unit_Test_Util::call_method( llms(), 'init_assets' );

		$this->assertEquals( $asset['asset'], LLMS_Unit_Test_Util::call_method( $this->main, 'prepare_inline_asset_for_output', array( $asset, 'header' ) ) );

		remove_filter( 'llms_assets_debug', '__return_false' );

	}

	/**
	 * Test prepare_inline_asset_for_output(): for scripts.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_prepare_inline_asset_for_output_scripts_debug_on() {

		$asset = array(
			'handle' => 'fake-handle',
			'asset'  => 'console.log(1);',
		);

		add_filter( 'llms_assets_debug', '__return_true' );
		$this->main = LLMS_Unit_Test_Util::call_method( llms(), 'init_assets' );

		$this->assertEquals( "// fake-handle.\nconsole.log(1);\n", LLMS_Unit_Test_Util::call_method( $this->main, 'prepare_inline_asset_for_output', array( $asset, 'header' ) ) );

		remove_filter( 'llms_assets_debug', '__return_true' );

	}

	/**
	 * Test prepare_inline_asset_for_output(): for styles.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_prepare_inline_asset_for_output_styles_debug_on() {

		$asset = array(
			'handle' => 'fake-handle',
			'asset'  => 'body{background:red;}',
		);

		add_filter( 'llms_assets_debug', '__return_true' );
		$this->main = LLMS_Unit_Test_Util::call_method( llms(), 'init_assets' );

		$this->assertEquals( "/* fake-handle. */\nbody{background:red;}\n", LLMS_Unit_Test_Util::call_method( $this->main, 'prepare_inline_asset_for_output', array( $asset, 'style' ) ) );

		remove_filter( 'llms_assets_debug', '__return_true' );

	}

	/**
	 * Test register_script() for a custom asset (added via a filter)
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_register_script_custom() {

		add_filter( 'llms_get_script_asset_definitions', function( $defs ) {
			$defs['mock-script'] = array(
				'file_slug' => 'mock-script',
			);
			return $defs;
		} );

		$this->assertTrue( $this->main->register_script( 'mock-script' ) );
		$this->assertAssetIsRegistered( 'script', 'mock-script' );

	}

	/**
	 * Test register_script() for a defined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_register_script_defined() {

		$this->assertTrue( $this->main->register_script( 'llms' ) );
		$this->assertAssetIsRegistered( 'script', 'llms' );

	}

	/**
	 * Test register_script() for a defined asset with defined dependencies.
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_register_script_defined_with_deps() {

		// Dependency is not registered.
		$this->assertAssetNotRegistered( 'script', 'llms' );

		$this->assertTrue( $this->main->register_script( 'llms-quiz' ) );
		$this->assertAssetIsRegistered( 'script', 'llms-quiz' );

		// Dependency was automatically registered.
		$this->assertAssetIsRegistered( 'script', 'llms' );

	}

	/**
	 * Test register_script() for an undefined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_register_script_undefined() {

		$this->assertFalse( $this->main->register_script( 'fake-script' ) );
		$this->assertAssetNotRegistered( 'script', 'fake-script' );

	}

	/**
	 * Test register_script() with translations
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_register_script_with_translations() {

		// LLMS_PLUGIN_URL gets messed up in the testing environment.
		$handler = function( $defaults ) {
			$defaults['base_url'] = plugins_url() . '/lifterlms';
			return $defaults;
		};
		add_filter( 'llms_get_script_asset_defaults', $handler );


		$handle = 'llms-test-messages';
		$file   = 'assets/js/llms-test-messages.js';
		$md5    = md5( $file );
		$json   = file_get_contents( LLMS_Unit_Test_Files::get_asset_path( sprintf( 'lifterlms-en_US-%s.json', $md5 ) ) );

		$scripts = array( $handle => array( 'translate' => true ) );
		$this->main->define( 'scripts', $scripts );

		$dirs = array(
			WP_LANG_DIR . '/lifterlms', // "Safe" directory.
			WP_LANG_DIR . '/plugins', // Default language directory.
			plugin_dir_path( LLMS_PLUGIN_FILE ) . 'languages', // Plugin language directory.
		);

		foreach ( $dirs as $dir ) {

			// Load a language file.
			$file = LLMS_Unit_Test_Files::copy_asset( sprintf( 'lifterlms-en_US-%s.json', $md5 ), $dir );
			$this->main->register_script( $handle );

			// The script's translation path should be the intended directory.
			$this->assertEquals( $dir, wp_scripts()->registered[ $handle ]->translations_path, $dir );

			// If we load the script's textdomain we'll see JSON matching the mock file.
			$this->assertEquals( $json, load_script_textdomain( $handle, 'lifterlms', $dir ), $dir );

			// Clean up.
			LLMS_Unit_Test_Files::remove( $file );
			wp_deregister_script( $handle );

		}

		// No files found.
		$this->main->register_script( $handle );
		$this->assertNull( wp_scripts()->registered[ $handle ]->translations_path );
		$this->assertFalse( load_script_textdomain( $handle, 'lifterlms' ) );

		remove_filter( 'llms_get_script_asset_defaults', $handler );

	}

	/**
	 * Test register_style() for a custom asset (added via a filter)
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_register_style_custom() {

		add_filter( 'llms_get_style_asset_definitions', function( $defs ) {
			$defs['mock-style'] = array(
				'file_slug' => 'mock-style',
				'rtl'       => false,
			);
			return $defs;
		} );

		$this->assertTrue( $this->main->register_style( 'mock-style' ) );
		$this->assertAssetIsRegistered( 'style', 'mock-style' );

		// No RTL is added.
		global $wp_styles;
		$this->assertEquals( array(), $wp_styles->registered['mock-style']->extra );

	}


	/**
	 * Test register_style() for a defined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_register_style_defined() {

		$this->assertTrue( $this->main->register_style( 'lifterlms-styles' ) );
		$this->assertAssetIsRegistered( 'style', 'lifterlms-styles' );

		// Ensure RTL is added.
		global $wp_styles;
		$expect = array(
			'rtl'    => 'replace',
			'suffix' => LLMS_ASSETS_SUFFIX,
		);
		$this->assertEquals( $expect, $wp_styles->registered['lifterlms-styles']->extra );

	}

	/**
	 * Test register_style() for an asset with defined dependencies
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_register_style_with_deps() {

		$this->markTestIncomplete( 'Need to rework this test when a qualifying asset is defined.' );

		// Deps are not registered.
		$deps = array( 'llms-datetimepicker', 'llms-quill-bubble', 'webui-popover' );
		foreach ( $deps as $dep ) {
			$this->assertAssetNotRegistered( 'style', $dep );
		}

		$this->assertTrue( $this->main->register_style( 'llms-builder-styles' ) );
		$this->assertAssetIsRegistered( 'style', 'llms-builder-styles' );

		// Deps are registered.
		foreach ( $deps as $dep ) {
			$this->assertAssetIsRegistered( 'style', $dep );
		}

	}

	/**
	 * Test register_style() for an undefined asset.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_register_style_undefined() {

		$this->assertFalse( $this->main->register_style( 'fake-style' ) );
		$this->assertAssetNotRegistered( 'style', 'fake-style' );

	}

}
