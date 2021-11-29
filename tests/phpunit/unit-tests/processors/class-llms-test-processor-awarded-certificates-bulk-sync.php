<?php
/**
 * Test Awarded Certificates Bulk Sync background processor.
 *
 * @package LifterLMS/Tests
 *
 * @group processors
 * @group processor_awarded_cerfificates_bulk_sync
 *
 * @since [version]
 */
class LLMS_Test_Processor_Awarded_Certificates_Bulk_Sync extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Forces processor debugging on so that we can make assertions against logged data.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		llms_maybe_define_constant( 'LLMS_PROCESSORS_DEBUG', true );

	}

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->main          = llms()->processors()->get( 'awarded_certificates_bulk_sync' );
		$this->schedule_hook = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'cron_hook_identifier' );

	}

	/**
	 * Teardown the test case.
	 *
	 * @since [version].
	 *
	 * @return void
	 */
	public function tear_down() {

		$this->main->cancel_process();
		parent::tear_down();

	}

	/**
	 * Test dispatch_sync() when there are no awarded certificates to sync.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_dispatch_sync_no_awarded_certificates() {

		$certificate_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);

		$this->main->dispatch_sync( $certificate_template );

		$this->assertEmpty( wp_next_scheduled( $this->schedule_hook ) );

	}


	/**
	 * Test dispatch_sync() when there are no publish/future awarded certificates to sync.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_dispatch_sync_awarded_certificates_wrong_post_status() {

		$certificate_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);
		$awarded_certificates = $this->factory->post->create_many(
			3,
			array(
				'post_parent' => $certificate_template,
				'post_type'   => 'llms_my_certificate',
				'post_status' => 'draft'
			)
		);

		$this->main->dispatch_sync( $certificate_template );

		$this->assertEmpty( wp_next_scheduled( $this->schedule_hook ) );

	}

	/**
	 * Test dispatch_sync() when there are awarded certificates to sync.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_dispatch_sync_awarded_certificates_success() {

		$certificate_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);
		$awarded_certificates = $this->factory->post->create_many(
			3,
			array(
				'post_type'   => 'llms_my_certificate',
				'post_parent' => $certificate_template,
			)
		);

		$this->main->dispatch_sync( $certificate_template );

		$this->assertNotEmpty( wp_next_scheduled( $this->schedule_hook ) );

	}

}
