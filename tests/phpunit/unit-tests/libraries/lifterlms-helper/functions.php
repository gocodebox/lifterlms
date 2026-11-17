<?php
/**
 * Test helper functions
 *
 * @package LifterLMS_Helper/Tests
 *
 * @group functions
 *
 * @since 3.2.1
 */
class LLMS_Helper_Test_Functions extends LLMS_Helper_Unit_Test_Case {

	/**
	 * Test llms_helper_options() function
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function test_llms_helper_options() {

		$instance = llms_helper_options();
		$this->assertTrue( $instance instanceof LLMS_Helper_Options );

	}

	/**
	 * Test llms_helper_flush_cache()
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function test_llms_helper_flush_cache() {

		// Prime the caches.
		set_transient( 'llms_products_api_result', 'fake-data', HOUR_IN_SECONDS );
		set_site_transient( 'update_themes', 'fake-data', HOUR_IN_SECONDS );
		set_site_transient( 'update_plugins', 'fake-data', HOUR_IN_SECONDS );

		llms_helper_flush_cache();

		$this->assertEmpty( get_transient( 'llms_products_api_result' ) );
		$this->assertEmpty( get_site_transient( 'update_themes' ) );
		$this->assertEmpty( get_site_transient( 'update_plugins' ) );

	}

}
