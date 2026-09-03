<?php
/**
 * Test LLMS_Admin_Catalog_Table
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group catalog_table
 *
 * @since [version]
 */
class LLMS_Test_Admin_Catalog_Table extends LLMS_UnitTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-catalog-table.php';

		set_transient(
			'llms_products_api_result',
			array(
				'categories' => array(
					'e-commerce'      => 'E-Commerce',
					'email-marketing' => 'Email Marketing',
					'tools'           => 'Tools',
					'advanced'        => 'Advanced',
				),
				'items'      => array(
					array(
						'id'            => 'lifterlms-com-convertkit',
						'slug'          => 'lifterlms-convertkit',
						'title'         => 'Kit Extension',
						'description'   => 'Connect to Kit.',
						'documentation' => 'https://lifterlms.com/docs/kit/',
						'permalink'     => 'https://lifterlms.com/product/convertkit/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-convertkit/lifterlms-convertkit.php',
						'categories'    => array( 'email-marketing' => 'Email Marketing' ),
					),
					array(
						'id'            => 'lifterlms-com-stripe-extension',
						'slug'          => 'lifterlms-stripe',
						'title'         => 'LifterLMS Stripe',
						'description'   => 'Accept credit cards.',
						'documentation' => 'https://lifterlms.com/docs/stripe/',
						'permalink'     => 'https://lifterlms.com/product/stripe/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-stripe/lifterlms-stripe.php',
						'categories'    => array( 'e-commerce' => 'E-Commerce' ),
					),
					array(
						'id'            => 'lifterlms-com-lifterlms-pro',
						'slug'          => 'lifterlms-pro',
						'title'         => 'LifterLMS Powerpack',
						'description'   => 'Graphics pack.',
						'documentation' => '',
						'permalink'     => 'https://lifterlms.com/product/lifterlms-pro/',
						'type'          => 'other',
						'update_file'   => '',
						'categories'    => array( 'tools' => 'Tools' ),
					),
					array(
						'id'            => 'lifterlms-com-office-hours',
						'slug'          => '',
						'title'         => 'LifterLMS Office Hours Mastermind',
						'description'   => 'Support.',
						'documentation' => '',
						'permalink'     => 'https://lifterlms.com/product/office-hours/',
						'type'          => '',
						'update_file'   => '',
						'categories'    => array( 'support' => 'Support' ),
					),
					array(
						'id'            => 'lifterlms-com-lifterlms-helper',
						'slug'          => 'lifterlms-helper',
						'title'         => 'LifterLMS Helper',
						'description'   => 'Helper.',
						'documentation' => '',
						'permalink'     => 'https://lifterlms.com/product/helper/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-helper/lifterlms-helper.php',
						'categories'    => array( 'tools' => 'Tools' ),
					),
					array(
						'id'            => 'lifterlms-com-lifterlms-twilio',
						'slug'          => 'lifterlms-twilio',
						'title'         => 'Twilio Integration',
						'description'   => 'SMS notifications.',
						'documentation' => 'https://lifterlms.com/docs/twilio/',
						'permalink'     => 'https://lifterlms.com/product/twilio/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-integration-twilio/lifterlms-integration-twilio.php',
						'categories'    => array( 'tools' => 'Tools' ),
					),
					array(
						'id'            => 'lifterlms-com-lifterlms-name-your-price',
						'slug'          => 'lifterlms-name-your-price',
						'title'         => 'LifterLMS Name Your Price',
						'description'   => 'Let learners choose a price.',
						'documentation' => 'https://lifterlms.com/docs/name-your-price/',
						'permalink'     => 'https://lifterlms.com/product/name-your-price/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-name-your-price/lifterlms-name-your-price.php',
						'categories'    => array(),
					),
					array(
						'id'            => 'lifterlms-com-lifterlms-car',
						'slug'          => 'lifterlms-car',
						'title'         => 'LifterLMS Cart Abandonment Recovery',
						'description'   => 'Recover abandoned checkouts.',
						'documentation' => 'https://lifterlms.com/docs/car/',
						'permalink'     => 'https://lifterlms.com/product/car/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-car/lifterlms-car.php',
						'categories'    => array( 'e-commerce' => 'E-Commerce' ),
					),
					array(
						'id'            => 'lifterlms-com-woocommerce-extension',
						'slug'          => 'lifterlms-integration-woocommerce',
						'title'         => 'LifterLMS WooCommerce Integration',
						'description'   => 'Sell with WooCommerce.',
						'documentation' => 'https://lifterlms.com/docs/woocommerce/',
						'permalink'     => 'https://lifterlms.com/product/woocommerce/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-integration-woocommerce/lifterlms-integration-woocommerce.php',
						'categories'    => array( 'e-commerce' => 'E-Commerce' ),
					),
					array(
						'id'            => 'lifterlms-com-paypal-extension',
						'slug'          => 'lifterlms-gateway-paypal',
						'title'         => 'LifterLMS PayPal Payments',
						'description'   => 'Accept PayPal.',
						'documentation' => 'https://lifterlms.com/docs/paypal/',
						'permalink'     => 'https://lifterlms.com/product/paypal/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-gateway-paypal/lifterlms-gateway-paypal.php',
						'categories'    => array( 'e-commerce' => 'E-Commerce' ),
					),
					array(
						'id'            => 'lifterlms-com-advanced-quizzes',
						'slug'          => 'lifterlms-advanced-quizzes',
						'title'         => 'LifterLMS Advanced Quizzes',
						'description'   => 'Advanced quiz types.',
						'documentation' => 'https://lifterlms.com/docs/advanced-quizzes/',
						'permalink'     => 'https://lifterlms.com/product/advanced-quizzes/',
						'type'          => 'plugin',
						'update_file'   => 'lifterlms-advanced-quizzes/lifterlms-advanced-quizzes.php',
						'categories'    => array( 'advanced' => 'Advanced' ),
					),
					array(
						'id'              => 'lifterlms-com-lifterlms-advanced-videos',
						'slug'            => 'lifterlms-advanced-videos',
						'title'           => 'LifterLMS Advanced Videos',
						'description'     => 'Require video completion.',
						'documentation'   => 'https://lifterlms.com/doc-category/lifterlms-extensions/advanced-videos/',
						'getting_started' => 'https://lifterlms.com/docs/getting-started-with-advanced-videos/',
						'permalink'       => 'https://lifterlms.com/product/advanced-videos/',
						'type'            => 'plugin',
						'update_file'     => 'lifterlms-advanced-videos/lifterlms-advanced-videos.php',
						'categories'      => array( 'advanced' => 'Advanced' ),
					),
				),
			)
		);
	}

	/**
	 * Teardown test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_transient( 'llms_products_api_result' );
		parent::tear_down();
	}

	/**
	 * Integrations catalog includes non-gateway ecommerce and excludes payment gateways.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_integrations_catalog_exclusions() {

		$ids = array();
		foreach ( LLMS_Admin_Catalog_Table::get_catalog_addons( 'integrations' ) as $addon ) {
			$ids[] = $addon->get( 'id' );
		}

		$this->assertContains( 'lifterlms-com-convertkit', $ids );
		$this->assertContains( 'lifterlms-com-lifterlms-twilio', $ids );
		$this->assertContains( 'lifterlms-com-advanced-quizzes', $ids );
		$this->assertContains( 'lifterlms-com-lifterlms-advanced-videos', $ids );
		$this->assertContains( 'lifterlms-com-lifterlms-name-your-price', $ids );
		$this->assertContains( 'lifterlms-com-lifterlms-car', $ids );
		$this->assertContains( 'lifterlms-com-woocommerce-extension', $ids );
		$this->assertNotContains( 'lifterlms-com-stripe-extension', $ids );
		$this->assertNotContains( 'lifterlms-com-paypal-extension', $ids );
		$this->assertNotContains( 'lifterlms-com-lifterlms-pro', $ids );
		$this->assertNotContains( 'lifterlms-com-office-hours', $ids );
		$this->assertNotContains( 'lifterlms-com-lifterlms-helper', $ids );
	}

	/**
	 * Checkout catalog includes payment gateways only.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_checkout_catalog_includes_gateways_only() {

		$ids = array();
		foreach ( LLMS_Admin_Catalog_Table::get_catalog_addons( 'checkout' ) as $addon ) {
			$ids[] = $addon->get( 'id' );
		}

		$this->assertContains( 'lifterlms-com-stripe-extension', $ids );
		$this->assertContains( 'lifterlms-com-paypal-extension', $ids );
		$this->assertNotContains( 'lifterlms-com-convertkit', $ids );
		$this->assertNotContains( 'lifterlms-com-lifterlms-name-your-price', $ids );
		$this->assertNotContains( 'lifterlms-com-lifterlms-car', $ids );
		$this->assertNotContains( 'lifterlms-com-woocommerce-extension', $ids );
	}

	/**
	 * Catalog gateway detection uses the product ID list and gateway-prefixed plugin files.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_catalog_gateway() {

		$stripe = llms_get_add_on(
			array(
				'id'          => 'lifterlms-com-stripe-extension',
				'update_file' => 'lifterlms-stripe/lifterlms-stripe.php',
			)
		);
		$paypal = llms_get_add_on(
			array(
				'id'          => 'lifterlms-com-unlisted-gateway',
				'update_file' => 'lifterlms-gateway-square/lifterlms-gateway-square.php',
			)
		);
		$car    = llms_get_add_on(
			array(
				'id'          => 'lifterlms-com-lifterlms-car',
				'update_file' => 'lifterlms-car/lifterlms-car.php',
			)
		);

		$this->assertTrue( LLMS_Admin_Catalog_Table::is_catalog_gateway( $stripe ) );
		$this->assertTrue( LLMS_Admin_Catalog_Table::is_catalog_gateway( $paypal ) );
		$this->assertFalse( LLMS_Admin_Catalog_Table::is_catalog_gateway( $car ) );
	}

	/**
	 * Display title overrides for core integrations and Twilio.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_display_title_overrides() {

		$this->assertEquals( 'LifterLMS bbPress', LLMS_Admin_Catalog_Table::get_display_title( 'bbPress', 'bbpress' ) );
		$this->assertEquals( 'LifterLMS BuddyPress', LLMS_Admin_Catalog_Table::get_display_title( 'BuddyPress', 'buddypress' ) );
		$this->assertEquals( 'LifterLMS Twilio', LLMS_Admin_Catalog_Table::get_display_title( 'Twilio Integration', 'lifterlms-com-lifterlms-twilio' ) );
		$this->assertEquals( 'Kit Extension', LLMS_Admin_Catalog_Table::get_display_title( 'Kit Extension', 'lifterlms-com-convertkit' ) );
		$this->assertEquals( 'LifterLMS Kit', LLMS_Admin_Catalog_Table::get_display_title( 'LifterLMS Kit', 'convertkit' ) );
	}

	/**
	 * Catalog items match registered gateway/integration ids without duplicating Stripe.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_addon_matches_registered_id() {

		$stripe = llms_get_add_on( 'lifterlms-com-stripe-extension' );
		$kit    = llms_get_add_on( 'lifterlms-com-convertkit' );

		$this->assertTrue( LLMS_Admin_Catalog_Table::addon_matches_registered_id( $stripe, 'stripe' ) );
		$this->assertFalse( LLMS_Admin_Catalog_Table::addon_matches_registered_id( $kit, 'stripe' ) );
		$this->assertTrue( LLMS_Admin_Catalog_Table::addon_matches_registered_id( $kit, 'convertkit' ) );

		$matched = LLMS_Admin_Catalog_Table::get_matched_catalog_ids( array( 'stripe' ), 'checkout' );
		$this->assertContains( 'lifterlms-com-stripe-extension', $matched );

		$av = llms_get_add_on( 'lifterlms-com-lifterlms-advanced-videos' );
		$this->assertTrue( LLMS_Admin_Catalog_Table::addon_matches_plugin_dir( $av, 'lifterlms-advanced-videos' ) );
		$this->assertFalse( LLMS_Admin_Catalog_Table::addon_matches_plugin_dir( $av, 'lifterlms-convertkit' ) );
		$this->assertFalse( LLMS_Admin_Catalog_Table::addon_matches_registered_id( $av, 'av_vimeo' ) );
		$this->assertEquals(
			'https://lifterlms.com/docs/getting-started-with-advanced-videos/',
			LLMS_Admin_Catalog_Table::get_addon_docs_url( $av )
		);
		$this->assertEquals(
			'LifterLMS Advanced Videos: Vimeo',
			LLMS_Admin_Catalog_Table::get_grouped_display_title( 'Videos: Vimeo', 'av_vimeo', $av, 3 )
		);
		$this->assertEquals(
			'Videos: Vimeo',
			LLMS_Admin_Catalog_Table::get_grouped_display_title( 'Videos: Vimeo', 'av_vimeo', $av, 1 )
		);
	}

	/**
	 * Integrations table HTML uses the new columns and core rows.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_integrations_table_html() {

		require_once LLMS_PLUGIN_DIR . 'includes/admin/settings/class.llms.settings.integrations.php';

		$html = LLMS_Unit_Test_Util::call_method( new LLMS_Settings_Integrations(), 'get_table_html' );

		$this->assertStringContainsString( 'Installed', $html );
		$this->assertStringContainsString( 'Activated', $html );
		$this->assertStringContainsString( 'Enabled', $html );
		$this->assertStringContainsString( 'Documentation', $html );
		$this->assertStringContainsString( 'View Docs', $html );
		$this->assertStringContainsString( 'Learn More', $html );
		$this->assertStringNotContainsString( 'Integration ID', $html );
		$this->assertStringContainsString( 'LifterLMS BuddyPress', $html );
		$this->assertStringContainsString( 'LifterLMS bbPress', $html );
		$this->assertStringContainsString( 'Kit Extension', $html );
		$this->assertStringContainsString( 'LifterLMS Twilio', $html );
		$this->assertStringContainsString( 'LifterLMS Advanced Videos', $html );
		$this->assertStringNotContainsString( 'LifterLMS Stripe', $html );
		$this->assertStringNotContainsString( 'Powerpack', $html );
		$this->assertStringNotContainsString( 'Office Hours', $html );
		$this->assertStringNotContainsString( 'LifterLMS Helper', $html );
	}
}
