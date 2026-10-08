<?php
/**
 * Test LLMS_Helper_Keys class
 *
 * @package LifterLMS_Helper/Tests
 *
 * @group add_on
 *
 * @since 3.2.1
 */
class LLMS_Helper_Test_Add_On extends LLMS_Helper_Unit_Test_Case {

	/**
	 * Test get_channel_subscripiton() and subscribe_to_channel()
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function test_channel_subscription() {

		$addon = llms_get_add_on( 'lifterlms-com-lifterlms' );

		// Stable by default.
		$this->assertEquals( 'stable', $addon->get_channel_subscription() );

		// Explicitly stable.
		$this->assertTrue( $addon->subscribe_to_channel( 'stable' ) );
		$this->assertEquals( 'stable', $addon->get_channel_subscription() );

		// Beta.
		$this->assertTrue( $addon->subscribe_to_channel( 'beta' ) );
		$this->assertEquals( 'beta', $addon->get_channel_subscription() );

	}

	/**
	 * Test find_license() and is_licensed() for add-ons that do not require a license.
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function test_find_license_and_is_licensed_not_required() {

		$addon = llms_get_add_on( 'lifterlms-com-lifterlms' );

		// No license.
		$this->assertFalse( $addon->find_license() );
		$this->assertFalse( $addon->is_licensed() );

		$mock_keys = array(
			'mock-key' => array(
				'product_id' => 'does-not-matter',
			),
		);

		// Mock saved keys.
		llms_helper_options()->set_license_keys( $mock_keys );

		// Returns the first stored key.
		$this->assertEquals( $mock_keys['mock-key'], $addon->find_license() );
		$this->assertTrue( $addon->is_licensed() );

	}

	/**
	 * Test find_license() and is_licensed() for add-ons that require a license
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function test_find_license_and_is_licensed_is_required() {

		$addon = llms_get_add_on( 'lifterlms-com-stripe-extension' );

		// No license.
		$this->assertFalse( $addon->find_license() );
		$this->assertFalse( $addon->is_licensed() );

		// No match.
		$mock_keys = array(
			'mock-key' => array(
				'product_id' => 'no-match',
				'addons'     => array(),
			),
		);
		llms_helper_options()->set_license_keys( $mock_keys );
		$this->assertFalse( $addon->find_license() );
		$this->assertFalse( $addon->is_licensed() );

		// Returns the key by product id.
		$mock_keys['good-key'] = array(
			'product_id' => 'lifterlms-com-stripe-extension',
			'addons'     => array(),
		);
		llms_helper_options()->set_license_keys( $mock_keys );
		$this->assertEquals( $mock_keys['good-key'], $addon->find_license() );
		$this->assertTrue( $addon->is_licensed() );

		// Returns the key by because it's included in the bundle.
		$mock_keys['good-key'] = array(
			'product_id' => 'bundle-product',
			'addons'     => array(
				'lifterlms-com-stripe-extension',
			),
		);
		llms_helper_options()->set_license_keys( $mock_keys );
		$this->assertEquals( $mock_keys['good-key'], $addon->find_license() );
		$this->assertTrue( $addon->is_licensed() );

	}

	/**
	 * Test get_download_info() error for no license found
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function test_get_download_info_err_no_license() {

		$addon = llms_get_add_on( 'lifterlms-com-stripe-extension' );
		$res   = $addon->get_download_info();

		$this->assertIsWpError( $res );
		$this->assertWpErrorCodeEquals( 'no_license', $res );

	}

	/**
	 * Test get_download_info() license errors
	 *
	 * @since 3.2.1
	 *
	 * @apiIntegration
	 *
	 * @return void
	 */
	public function test_get_download_info_err_with_license() {

		// License & Update key are empty.
		$mock_keys['bad-key'] = array(
			'product_id'  => 'lifterlms-com-stripe-extension',
			'license_key' => '',
			'update_key'  => '',
		);
		llms_helper_options()->set_license_keys( $mock_keys );

		$addon = llms_get_add_on( 'lifterlms-com-stripe-extension' );
		$res   = $addon->get_download_info();

		$this->assertIsWpError( $res );
		$this->assertWpErrorCodeEquals( 'error', $res );
		$this->assertWpErrorMessageEquals( 'Invalid credentials provided.', $res );

		// Valid key but fake update key.
		$mock_keys['bad-key'] = array(
			'product_id'  => 'lifterlms-com-stripe-extension',
			'license_key' => $this->get_test_key( 'STRIPE' ),
			'update_key'  => 'fake',
		);
		llms_helper_options()->set_license_keys( $mock_keys );

		$addon = llms_get_add_on( 'lifterlms-com-stripe-extension' );
		$res   = $addon->get_download_info();

		$this->assertIsWpError( $res );
		$this->assertWpErrorCodeEquals( 'error', $res );
		$this->assertWpErrorMessageEquals( 'Invalid credentials provided.', $res );

	}

	/**
	 * Test get_download_info() success for an add-on that requires a license
	 *
	 * @since 3.2.1
	 *
	 * @apiIntegration
	 *
	 * @return void
	 */
	public function test_get_download_info_success_license_required_single() {

		$key = $this->activate_key( 'STRIPE' );

		$addon = llms_get_add_on( 'lifterlms-com-stripe-extension' );
		$res   = $addon->get_download_info();

		$this->assertNotEmpty( $res['data']['url'] );
		$this->assertNotEmpty( $res['data']['file'] );
		$this->assertEquals( $addon->get( 'version' ), $res['data']['version'] );

	}

	/**
	 * Test get_download_info() success for an add-on that requires a license with a bundle key
	 *
	 * @since 3.2.1
	 *
	 * @apiIntegration
	 *
	 * @return void
	 */
	public function test_get_download_info_success_license_required_bundle() {

		$key = $this->activate_key( 'UNIVERSE' );

		$addon = llms_get_add_on( 'lifterlms-com-stripe-extension' );
		$res   = $addon->get_download_info();

		$this->assertNotEmpty( $res['data']['url'] );
		$this->assertNotEmpty( $res['data']['file'] );
		$this->assertEquals( $addon->get( 'version' ), $res['data']['version'] );

	}

	/**
	 * Test get_download_info() success for an add-on that does not require a license
	 *
	 * @since 3.2.1
	 *
	 * @apiIntegration
	 *
	 * @return void
	 */
	public function test_get_download_info_success_license_optional() {

		$addon = llms_get_add_on( 'lifterlms-com-lifterlms' );

		// Without keys.
		$res = $addon->get_download_info();

		$this->assertNotEmpty( $res['data']['url'] );
		$this->assertNotEmpty( $res['data']['file'] );
		$this->assertEquals( $addon->get( 'version' ), $res['data']['version'] );

		// With keys.
		$key = $this->activate_key( 'STRIPE' );
		$res = $addon->get_download_info();

		$this->assertNotEmpty( $res['data']['url'] );
		$this->assertNotEmpty( $res['data']['file'] );
		$this->assertEquals( $addon->get( 'version' ), $res['data']['version'] );

	}

	public function test_requires_license() {

		$add_on = new LLMS_Helper_Add_On( array( 'has_license' => 'yes' ) );
		$this->assertTrue( $add_on->requires_license() );

		$add_on = new LLMS_Helper_Add_On( array( 'has_license' => 'no' ) );
		$this->assertFalse( $add_on->requires_license() );

	}

}
