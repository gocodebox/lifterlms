<?php
/**
 * Test the main class / loader.
 *
 * @package LifterLMS_REST/Tests
 *
 * @group main
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_Main extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = LLMS_REST_API();

	}

	/**
	 * Copies the tests MO file to a directory so it can be loaded by `load_textdomain()`.
	 *
	 * @since 1.0.0-beta.17
	 *
	 * @param string $dest Directory to copy the MO file to.
	 * @return string Full path to the created file.
	 */
	protected function copy_mo( $dest ) {

		global $_llms_tests_bootstrap;

		$assets_dir = untrailingslashit( $_llms_tests_bootstrap->assets_dir );
		$name       = '/lifterlms-rest-en_US.mo';
		$orig       = $assets_dir . $name;
		$file       = $dest . $name;

		// Delete the file if it exists so copy doesn't fail later.
		$this->clear_mo( $file );

		// Make sure the destination dir exists.
		if ( ! file_exists( $dest ) ) {
			mkdir( $dest, 0777, true );
		}

		// Copy the mo to the dest directoy.
		copy( $orig, $file );

		return $file;

	}

	/**
	 * Delete an MO file created by `copy_mo()`.
	 *
	 * @since 1.0.0-beta.17
	 *
	 * @param string $file Full path to the MO file to be deleted.
	 * @return void
	 */
	protected function clear_mo( $file ) {

		if ( file_exists( $file ) ) {
			unlink( $file );
		}

	}

	/**
	 * Test class constructor.
	 *
	 * @since 1.0.0-beta.17
	 *
	 * @return void
	 */
	public function test_constructor() {

		remove_action( 'init', array( $this->main, 'load_textdomain' ), 0 );
		LLMS_Unit_Test_Util::call_method( $this->main, '__construct' );

		// When loaded as a library within the LifterLMS core the textdomain is not loaded independently.
		$this->assertFalse( has_action( 'init', array( $this->main, 'load_textdomain' ) ) );

	}

	/**
	 * Test keys() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_keys() {

		$this->assertTrue( is_a( $this->main->keys(), 'LLMS_REST_API_Keys' ) );

	}

	/**
	 * Test load_textdomain().
	 *
	 * @since 1.0.0-beta.17
	 *
	 * @return void
	 */
	public function test_load_textdomain() {

		// Make sure textdomain is not loaded.
		unload_textdomain( 'lifterlms' );

		$dirs = array(
			WP_LANG_DIR . '/lifterlms', // "Safe" directory.
			WP_LANG_DIR . '/plugins', // Default language directory.
			LLMS_REST_API_PLUGIN_DIR . '/i18n', // Plugin language directory.
		);

		foreach ( $dirs as $dir ) {

			// Make sure the initial strings work.
			$this->assertEquals( 'LifterLMS REST API', __( 'LifterLMS REST API', 'lifterlms' ), $dir);
			$this->assertEquals( 'Post title.', __( 'Post title.', 'lifterlms' ), $dir );

			// Load from the "safe" directory.
			$file = $this->copy_mo( $dir );
			$this->main->load_textdomain();

			$this->assertEquals( 'BetterLMS REST API', __( 'LifterLMS REST API', 'lifterlms' ), $dir );
			$this->assertEquals( 'Item title.', __( 'Item title.', 'lifterlms' ), $dir );

			// Clean up.
			$this->clear_mo( $file );
			unload_textdomain( 'lifterlms' );

		}

	}

	/**
	 * Test webhooks() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_webhooks() {

		$this->assertTrue( is_a( $this->main->webhooks(), 'LLMS_REST_Webhooks' ) );

	}

	/**
	 * Test on_user_deletion() method: api keys deletion.
	 *
	 * @since 1.0.0-beta.25
	 *
	 * @return void
	 */
	public function test_on_user_deletion_keys_deletion() {

		$api_keys = $this->main->keys();

		// Use default permissions.
		$data = array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		);
		$ret = $api_keys->create( $data );
		$this->assertTrue( is_a( $ret, 'LLMS_REST_API_Key' ) );
		$this->assertTrue( $ret->exists() );

		// Create user A.
		$user_a = $this->factory->user->create();
		// Create user B.
		$user_b = $this->factory->user->create();

		// Create 2 api keys for user A.
		$data = array(
			'description' => 'Test Key',
			'user_id' => $user_a,
		);
		$keys_a_1= $api_keys->create( $data );
		$this->assertTrue( is_a( $keys_a_1, 'LLMS_REST_API_Key' ) );
		$this->assertTrue( $keys_a_1->exists() );
		$data = array(
			'description' => 'Test Key',
			'user_id' => $user_a,
		);
		$keys_a_2 = $api_keys->create( $data );
		$this->assertTrue( is_a( $keys_a_2, 'LLMS_REST_API_Key' ) );
		$this->assertTrue( $keys_a_2->exists() );

		// Create api keys for user B.
		$data = array(
			'description' => 'Test Key',
			'user_id' => $user_b,
		);
		$keys_b_1= $api_keys->create( $data );
		$this->assertTrue( is_a( $keys_b_1, 'LLMS_REST_API_Key' ) );
		$this->assertTrue( $keys_b_1->exists() );

		// Delete user B.
		wp_delete_user( $user_b );

		// Check user B keys are deleted.
		$this->assertFalse( $keys_b_1->exists() );

		// Check user A keys are still there.
		$this->assertTrue( $keys_a_1->exists() );
		$this->assertTrue( $keys_a_2->exists() );

		// Delete user A.
		wp_delete_user( $user_a );

		// Check user A keys are deleted.
		$this->assertFalse( $keys_a_1->exists() );
		$this->assertFalse( $keys_a_2->exists() );

	}

}
