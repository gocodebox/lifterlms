<?php
/**
 * Test Awarded Certificates Bulk Sync background processor.
 *
 * @package LifterLMS/Tests
 *
 * @group processors
 * @group processor_awarded_certificates_bulk_sync
 *
 * @since 6.0.0
 */
class LLMS_Test_Processor_Awarded_Certificates_Bulk_Sync extends LLMS_UnitTestCase {

	/**
	 * @var string
	 */
	private $cron_hook_identifier;

	/**
	 * @var LLMS_Processor_Certificate_Sync
	 */
	private $main;

	/**
	 * @var string
	 */
	private $schedule_hook;

	/**
	 * Setup before class
	 *
	 * Forces processor debugging on so that we can make assertions against logged data.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->main                 = llms()->processors()->get( 'certificate_sync' );
		$this->cron_hook_identifier = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'cron_hook_identifier' );
		$this->schedule_hook        = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'schedule_hook' );
	}

	/**
	 * Teardown the test case.
	 *
	 * @since 6.0.0.
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
	 * @since 6.0.0
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

		$this->assertEmpty( wp_next_scheduled( $this->cron_hook_identifier ) );
	}

	/**
	 * Test dispatch_sync() when there are no publish/future awarded certificates to sync.
	 *
	 * @since 6.0.0
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
		$this->assertEmpty( wp_next_scheduled( $this->cron_hook_identifier ) );
	}

	/**
	 * Test dispatch_sync() when there are awarded certificates to sync.
	 *
	 * @since 6.0.0
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
			20,
			array(
				'post_type'   => 'llms_my_certificate',
				'post_parent' => $certificate_template,
			)
		);

		$handler = function ( $args ) {
			$args['per_page'] = 10;
			return $args;
		};

		add_filter( 'llms_processor_sync_awarded_certificates_query_args', $handler );

		$this->main->dispatch_sync( $certificate_template );

		// Test data is loaded into the queue properly.
		foreach ( LLMS_Unit_Test_Util::call_method( $this->main, 'get_batch' )->data as $i => $args ) {

			$query_args = $args['query_args'];

			$this->assertEquals( $certificate_template, $query_args['templates'] );
			$this->assertEquals( 10, $query_args['per_page'] );
			$this->assertEquals( array( 'publish', 'future' ), $query_args['status'] );
			$this->assertEquals( ++ $i, $query_args['page'] );
		}

		$this->assertEquals( 2, $i ); // Two chunks.
		$this->assertNotEmpty( wp_next_scheduled( $this->cron_hook_identifier ) );

		remove_filter( 'llms_processor_sync_awarded_certificates_query_args', $handler );
	}

	/**
	 * Test schedule_sync() method.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_schedule_sync() {

		$certificate_template   = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);
		$certificate_template_2 = $this->factory->post->create(
			array(
				'post_type' => 'llms_certificate',
			)
		);

		// The template has no awarded engagements to schedule a sync with.
		do_action(
			'llms_do_awarded_certificates_bulk_sync',
			$certificate_template
		);
		$this->assertEquals(
			array(
				sprintf(
					'awarded certificates bulk sync for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template ),
					$certificate_template,
				),
				sprintf(
					'no awarded certificates to bulk sync with the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template ),
					$certificate_template,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );

		$awarded_certificates   = $this->factory->post->create_many(
			2,
			array(
				'post_type'   => 'llms_my_certificate',
				'post_parent' => $certificate_template,
			)
		);
		$awarded_certificates_2 = $this->factory->post->create_many(
			2,
			array(
				'post_type'   => 'llms_my_certificate',
				'post_parent' => $certificate_template_2,
			)
		);

		$this->logs->clear( 'processors' );

		do_action(
			'llms_do_awarded_certificates_bulk_sync',
			$certificate_template
		);
		$this->assertNotEmpty( wp_next_scheduled( $this->schedule_hook, array( $certificate_template ) ) );

		$this->assertEquals(
			array(
				sprintf(
					'awarded certificates bulk sync for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template ),
					$certificate_template,
				),
				sprintf(
					'awarded certificates bulk sync scheduled for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template ),
					$certificate_template,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );

		// A sync for a different certificate template is scheduled as well.
		do_action(
			'llms_do_awarded_certificates_bulk_sync',
			$certificate_template_2
		);
		$this->assertNotEmpty( wp_next_scheduled( $this->schedule_hook, array( $certificate_template_2 ) ) );

		$this->assertEquals(
			array(
				sprintf(
					'awarded certificates bulk sync for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template_2 ),
					$certificate_template_2,
				),
				sprintf(
					'awarded certificates bulk sync scheduled for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template_2 ),
					$certificate_template_2,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );

		// Already scheduled.
		do_action(
			'llms_do_awarded_certificates_bulk_sync',
			$certificate_template_2
		);
		$this->assertEquals(
			array(
				sprintf(
					'awarded certificates bulk sync for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template_2 ),
					$certificate_template_2,
				),
				sprintf(
					'awarded certificates bulk sync already scheduled for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template_2 ),
					$certificate_template_2,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );
	}

	/**
	 * Test LLMS_Processor_Certificate_Sync::task() and LLMS_Controller_Certificates::sync_awarded_engagements().
	 *
	 * @since 6.0.0
	 * @since 7.1.0 Test logs as equal sets.
	 *
	 * @return void
	 */
	public function test_task() {

		$certificate_template = $this->factory->post->create(
			array(
				'post_type'    => 'llms_certificate',
				'meta_input'   => array(
					'_llms_certificate_title' => 'A certificate title'
				),
				'post_content' => 'Certificate post content',
			)
		);

		$awarded_certificates_ids = $this->factory->post->create_many(
			2,
			array(
				'post_type'   => 'llms_my_certificate',
				'post_parent' => $certificate_template,
			)
		);

		// Manipulate awards results so to make the second sync fail.
		$filter_awards = function ( $awards ) {
			$awards[1]->set('parent', 0 ); // Remove the parent template.
			return $awards;
		};

		add_filter( 'llms_awards_query_get_awards', $filter_awards );

		$this->main->task(
			array(
				'query_args' => array(
					'templates' => $certificate_template,
					'per_page'  => 20,
					'page'      => 1,
					'status'    => array(
						'publish',
						'future',
					),
					'type'      => 'certificate',
					'sort'      => array( 'ID', 'ASC' ),
				)
			)
		);

		remove_filter( 'llms_awards_query_get_awards', $filter_awards );

		$awarded_certificates = array_map(
			'llms_get_certificate',
			$awarded_certificates_ids
		);

		$this->assertEqualSets(
			array(
				sprintf(
					'awarded certificates bulk sync task started for the certificate template %1$s (#%2$d) - chunk 1',
					get_the_title( $certificate_template ),
					$certificate_template,
				),
				sprintf(
					'awarded certificate %1$s (#%2$d) successfully synced with template %3$s (#%4$d)',
					$awarded_certificates[0]->get( 'title' ),
					$awarded_certificates[0]->get( 'id' ),
					get_the_title( $certificate_template ),
					$certificate_template,
				),
				sprintf(
					'an error occurred while trying to sync awarded certificate %1$s (#%2$d) from template %3$s (#%4$d)',
					$awarded_certificates[1]->get( 'title' ),
					$awarded_certificates[1]->get( 'id' ),
					get_the_title( $certificate_template ),
					$certificate_template,
				),
				sprintf(
					'awarded certificate bulk sync completed for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template ),
					$certificate_template,
				)
			),
			$this->logs->get( 'processors' )
		);

		// Check title/content sync.
		$this->assertEquals(
			get_post_meta( $certificate_template, '_llms_certificate_title', true ),
			$awarded_certificates[0]->get( 'title', true )
		);
		$this->assertEquals(
			get_post( $certificate_template )->post_content,
			$awarded_certificates[0]->get( 'content', true )
		);
		$this->assertNotEquals(
			get_post_meta( $certificate_template, '_llms_certificate_title', true ),
			$awarded_certificates[1]->get( 'title', true )
		);
		$this->assertNotEquals(
			get_post( $certificate_template )->post_content,
			$awarded_certificates[1]->get( 'content', true )
		);
	}
}
