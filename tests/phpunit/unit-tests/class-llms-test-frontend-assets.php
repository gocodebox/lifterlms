<?php
/**
 * LLMS Frontend Assets Tests
 *
 * @package LifterLMS/Tests
 *
 * @group assets
 * @group frontend_assets
 *
 * @since 4.4.0
 * @since [version] Removed testing of removed items.
 *              - `LLMS_Frontend_Assets::enqueue_inline_script()` method
 *              - `LLMS_Frontend_Assets::is_inline_enqueued()` method
 */
class LLMS_Test_Frontend_Assets extends LLMS_UnitTestCase {

	/**
	 * Retrieves a list of enqueued inline scripts from the LLMS_Assets instance.
	 *
	 * @since 5.6.0
	 *
	 * @return array
	 */
	private function get_inline_scripts() {
		return LLMS_Unit_Test_Util::get_private_property_value( llms()->assets, 'inline' );
	}

	/**
	 * Test enqueue_content_protection().
	 *
	 * @since 5.6.0
	 *
	 * @return void
	 */
	public function test_enqueue_content_protection() {

		// Content protection off & user is logged out: no scripts loaded.
		update_option( 'lifterlms_content_protection', 'no' );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertEquals( array(), $this->get_inline_scripts() );

		// Content protection is on and user is logged out: scripts are loaded.
		update_option( 'lifterlms_content_protection', 'yes' );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertArrayHasKey( 'llms-integrity', $this->get_inline_scripts() );

		LLMS_Unit_Test_Util::set_private_property( llms()->assets, 'inline', array() );

		// Admin can bypass restrictions, script is not loaded.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertEquals( array(), $this->get_inline_scripts() );

		LLMS_Unit_Test_Util::set_private_property( llms()->assets, 'inline', array() );

		// Student can't copy content.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );
		LLMS_Frontend_Assets::enqueue_content_protection();
		$this->assertArrayHasKey( 'llms-integrity', $this->get_inline_scripts() );

		LLMS_Unit_Test_Util::set_private_property( llms()->assets, 'inline', array() );

	}

}
