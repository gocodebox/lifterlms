<?php
/**
 * Test Awarded Achievements Bulk Sync background processor.
 *
 * @package LifterLMS/Tests
 *
 * @group processors
 * @group processor_awarded_achievements_bulk_sync
 *
 * @since [version]
 */
class LLMS_Test_Processor_Awarded_Achievements_Bulk_Sync extends LLMS_UnitTestCase {

	/**
	 * @var string
	 */
	private $cron_hook_identifier;

	/**
	 * @var LLMS_Processor_Achievement_Sync
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

		$this->main                 = llms()->processors()->get( 'achievement_sync' );
		$this->cron_hook_identifier = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'cron_hook_identifier' );
		$this->schedule_hook        = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'schedule_hook' );
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
	 * Test dispatch_sync() when there are no awarded achievements to sync.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_dispatch_sync_no_awarded_achievements() {

		$achievement_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_achievement',
			)
		);

		$this->main->dispatch_sync( $achievement_template );

		$this->assertEmpty( wp_next_scheduled( $this->cron_hook_identifier ) );
	}

	/**
	 * Test dispatch_sync() when there are no publish/future awarded achievements to sync.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_dispatch_sync_awarded_achievements_wrong_post_status() {

		$achievement_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_achievement',
			)
		);
		$awarded_achievements = $this->factory->post->create_many(
			3,
			array(
				'post_parent' => $achievement_template,
				'post_type'   => 'llms_my_achievement',
				'post_status' => 'draft'
			)
		);

		$this->main->dispatch_sync( $achievement_template );
		$this->assertEmpty( wp_next_scheduled( $this->cron_hook_identifier ) );
	}

	/**
	 * Test dispatch_sync() when there are awarded achievements to sync.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_dispatch_sync_awarded_achievements_success() {

		$achievement_template = $this->factory->post->create(
			array(
				'post_type' => 'llms_achievement',
			)
		);
		$awarded_achievements = $this->factory->post->create_many(
			20,
			array(
				'post_type'   => 'llms_my_achievement',
				'post_parent' => $achievement_template,
			)
		);

		$handler = function ( $args ) {
			$args['per_page'] = 10;
			return $args;
		};

		add_filter( 'llms_processor_sync_awarded_achievements_query_args', $handler );

		$this->main->dispatch_sync( $achievement_template );

		// Test data is loaded into the queue properly.
		foreach ( LLMS_Unit_Test_Util::call_method( $this->main, 'get_batch' )->data as $i => $args ) {

			$query_args = $args['query_args'];

			$this->assertEquals( $achievement_template, $query_args['templates'] );
			$this->assertEquals( 10, $query_args['per_page'] );
			$this->assertEquals( array( 'publish', 'future' ), $query_args['status'] );
			$this->assertEquals( ++ $i, $query_args['page'] );
		}

		$this->assertEquals( 2, $i ); // Two chunks.
		$this->assertNotEmpty( wp_next_scheduled( $this->cron_hook_identifier ) );

		remove_filter( 'llms_processor_sync_awarded_achievements_query_args', $handler );
	}

	/**
	 * Test schedule_sync() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_schedule_sync() {

		$achievement_template   = $this->factory->post->create(
			array(
				'post_type' => 'llms_achievement',
			)
		);
		$achievement_template_2 = $this->factory->post->create(
			array(
				'post_type' => 'llms_achievement',
			)
		);
		$awarded_achievements   = $this->factory->post->create_many(
			2,
			array(
				'post_type'   => 'llms_my_achievement',
				'post_parent' => $achievement_template,
			)
		);
		$awarded_achievements_2 = $this->factory->post->create_many(
			2,
			array(
				'post_type'   => 'llms_my_achievement',
				'post_parent' => $achievement_template_2,
			)
		);

		$this->logs->clear( 'processors' );

		do_action(
			'llms_do_awarded_achievements_bulk_sync',
			$achievement_template
		);
		$this->assertNotEmpty( wp_next_scheduled( $this->schedule_hook, array( $achievement_template ) ) );

		$this->assertEquals(
			array(
				sprintf(
					'awarded achievements bulk sync for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template ),
					$achievement_template,
				),
				sprintf(
					'awarded achievements bulk sync scheduled for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template ),
					$achievement_template,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );

		// A sync for a different achievement template is scheduled as well.
		do_action(
			'llms_do_awarded_achievements_bulk_sync',
			$achievement_template_2
		);
		$this->assertNotEmpty( wp_next_scheduled( $this->schedule_hook, array( $achievement_template_2 ) ) );

		$this->assertEquals(
			array(
				sprintf(
					'awarded achievements bulk sync for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template_2 ),
					$achievement_template_2,
				),
				sprintf(
					'awarded achievements bulk sync scheduled for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template_2 ),
					$achievement_template_2,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );

		// Already scheduled.
		do_action(
			'llms_do_awarded_achievements_bulk_sync',
			$achievement_template_2
		);
		$this->assertEquals(
			array(
				sprintf(
					'awarded achievements bulk sync for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template_2 ),
					$achievement_template_2,
				),
				sprintf(
					'awarded achievements bulk sync already scheduled for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template_2 ),
					$achievement_template_2,
				),
			),
			$this->logs->get( 'processors' )
		);

		$this->logs->clear( 'processors' );
	}

	/**
	 * Test LLMS_Processor_Achievement_Sync::task() and LLMS_Controller_Achievements::sync_awarded_engagements().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_task() {

		$achievement_template = $this->factory->post->create(
			array(
				'post_type'    => 'llms_achievement',
				'meta_input'   => array(
					'_llms_achievement_title' => 'A achievement title'
				),
				'post_content' => 'Achievement post content',
			)
		);

		$awarded_achievements_ids = $this->factory->post->create_many(
			2,
			array(
				'post_type'   => 'llms_my_achievement',
				'post_parent' => $achievement_template,
			)
		);

		// Manipulate awards results so to make the second sync fail.
		$filter_awards = function ( $awards ) {
			$awards[1]->set( 'parent', 0 ); // Remove the parent template.
			return $awards;
		};

		add_filter( 'llms_awards_query_get_awards', $filter_awards );

		$this->main->task(
			array(
				'query_args' => array(
					'templates' => $achievement_template,
					'per_page'  => 20,
					'page'      => 1,
					'status'    => array(
						'publish',
						'future',
					),
					'type'      => 'achievement',
					'sort'      => array( 'ID', 'ASC' ),
				)
			)
		);

		remove_filter( 'llms_awards_query_get_awards', $filter_awards );

		$awarded_achievements = array();
		foreach ( $awarded_achievements_ids as $awarded_achievements_id ) {
			$awarded_achievements[] = new LLMS_User_Achievement( $awarded_achievements_id );
		}

		$this->assertEquals(
			array(
				sprintf(
					'awarded achievements bulk sync task started for the achievement template %1$s (#%2$d) - chunk 1',
					get_the_title( $achievement_template ),
					$achievement_template,
				),
				sprintf(
					'awarded achievement %1$s (#%2$d) successfully synced with template %3$s (#%4$d)',
					$awarded_achievements[0]->get( 'title' ),
					$awarded_achievements[0]->get( 'id' ),
					get_the_title( $achievement_template ),
					$achievement_template,
				),
				sprintf(
					'an error occurred while trying to sync awarded achievement %1$s (#%2$d) from template %3$s (#%4$d)',
					$awarded_achievements[1]->get( 'title' ),
					$awarded_achievements[1]->get( 'id' ),
					get_the_title( $achievement_template ),
					$achievement_template,
				),
				sprintf(
					'awarded achievement bulk sync completed for the achievement template %1$s (#%2$d)',
					get_the_title( $achievement_template ),
					$achievement_template,
				)
			),
			$this->logs->get( 'processors' )
		);

		// Check title/content sync.
		$this->assertEquals(
			get_post_meta( $achievement_template, '_llms_achievement_title', true ),
			$awarded_achievements[0]->get( 'title', true )
		);
		$this->assertEquals(
			get_post( $achievement_template )->post_content,
			$awarded_achievements[0]->get( 'content', true )
		);
		$this->assertNotEquals(
			get_post_meta( $achievement_template, '_llms_achievement_title', true ),
			$awarded_achievements[1]->get( 'title', true )
		);
		$this->assertNotEquals(
			get_post( $achievement_template )->post_content,
			$awarded_achievements[1]->get( 'content', true )
		);
	}
}
