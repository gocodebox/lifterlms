<?php
/**
 * LLMS_Abstract_Processor_User_Engagement_Sync class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base processor class for syncing awarded engagements (certificates or achievements) to their engagement template.
 *
 * @since 6.0.0
 */
abstract class LLMS_Abstract_Processor_User_Engagement_Sync extends LLMS_Abstract_Processor {

	use LLMS_Trait_User_Engagement_Type;

	/**
	 * A text type for an admin notice that the sync of awarded engagements to an engagement template is already scheduled.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_NOTICE_ALREADY_SCHEDULED = 0;

	/**
	 * A text type for an admin notice that the sync of awarded engagements to an engagement template is complete.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_NOTICE_AWARDED_ENGAGEMENTS_COMPLETE = 1;

	/**
	 * A text type for an admin notice that there are no awarded engagements to sync the template with.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_NOTICE_NO_AWARDED_ENGAGEMENTS = 2;

	/**
	 * A text type for an admin notice that the sync of awarded engagements to an engagement template is scheduled.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_NOTICE_SCHEDULED = 3;

	/**
	 * Clear notices.
	 *
	 * @since 6.0.0
	 *
	 * @param int $engagement_template_id WP Post ID of the user engagement template.
	 * @return void
	 */
	private function clear_notices( $engagement_template_id ) {
		$notices = array(
			'awarded-%1$ss-sync-%2$d-scheduled',
			'awarded-%1$ss-sync-%2$d-already-scheduled',
			'awarded-%1$ss-sync-%2$d-done',
		);

		foreach ( $notices as $notice ) {
			LLMS_Admin_Notices::delete_notice(
				sprintf( $notice, $this->engagement_type, $engagement_template_id )
			);
		}
	}

	/**
	 * Action triggered to sync all the awarded engagements that need to be updated.
	 *
	 * @since 6.0.0
	 *
	 * @param int $engagement_template_id WP Post ID of the engagement template.
	 * @return void
	 */
	public function dispatch_sync( $engagement_template_id ) {

		$this->log(
			sprintf(
				'awarded %1$ss bulk sync dispatched for the %1$s template %2$s (#%3$d)',
				$this->engagement_type,
				get_the_title( $engagement_template_id ),
				$engagement_template_id
			)
		);

		/**
		 * Filter the query arguments used when retrieving the awarded engagements to sync.
		 *
		 * The dynamic portion of the hook name,
		 * {@see LLMS_Abstract_Processor_User_Engagement_Sync::$engagement_type `$this->engagement_type`},
		 * refers to the engagement type, either 'achievement' or 'certificate'.
		 *
		 * @since 6.0.0
		 *
		 * @param array $args Query arguments passed to LLMS_Awards_Query.
		 */
		$args = apply_filters(
			"llms_processor_sync_awarded_{$this->engagement_type}s_query_args",
			array(
				'templates' => $engagement_template_id,
				'per_page'  => 20,
				'page'      => 1,
				'status'    => array(
					'publish',
					'future',
				),
				'type'      => $this->engagement_type,
			)
		);

		$query = new LLMS_Awards_Query( $args );

		if ( ! $query->get_found_results() ) {
			return;
		}

		while ( $args['page'] <= $query->get_max_pages() ) {
			$this->push_to_queue(
				array(
					'query_args' => $args,
				)
			);

			$args['page'] ++;
		}

		// Save queue and dispatch the process.
		$this->save()->dispatch();
	}

	/**
	 * Returns a translated text of the given type.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Processor_User_Engagement_Sync::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		return __( 'Invalid text type.', 'lifterlms' );
	}

	/**
	 * Initializer.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	protected function init() {

		// For the cron.
		add_action( $this->schedule_hook, array( $this, 'dispatch_sync' ), 10, 1 );

		// For LifterLMS actions which trigger bulk enrollment.
		$this->actions = array(
			"llms_do_awarded_{$this->engagement_type}s_bulk_sync" => array(
				'arguments' => 1,
				/** @see LLMS_Abstract_Processor_User_Engagement_Sync::schedule_sync() */
				'callback'  => 'schedule_sync',
				'priority'  => 10,
			),
		);
	}

	/**
	 * Perform actions when the process is completed.
	 *
	 * @since 6.0.0
	 *
	 * @param array $args Array of processing data.
	 * @return void
	 */
	private function process_completed( $args ) {

		$this->log(
			sprintf(
				'awarded %1$s bulk sync completed for the %1$s template %2$s (#%3$d)',
				$this->engagement_type,
				get_the_title( $args['query_args']['templates'] ),
				$args['query_args']['templates']
			)
		);

		$this->clear_notices( $args['query_args']['templates'] );

		LLMS_Admin_Notices::add_notice(
			sprintf( 'awarded-%1$ss-sync-%2$d-done', $this->engagement_type, $args['query_args']['templates'] ),
			$this->get_text(
				self::TEXT_SYNC_NOTICE_AWARDED_ENGAGEMENTS_COMPLETE,
				array( 'engagement_template_id' => $args['query_args']['templates'] )
			),
			array(
				'dismissible'      => true,
				'dismiss_for_days' => 0,
				'type'             => 'success',
			)
		);
	}

	/**
	 * Schedule sync.
	 *
	 * This will schedule an event that will setup the queue of items for the background process.
	 *
	 * @since 6.0.0
	 *
	 * @param int $engagement_template_id WP Post ID of the user engagement template.
	 * @return void
	 */
	public function schedule_sync( $engagement_template_id ) {

		$this->log(
			sprintf(
				'awarded %1$ss bulk sync for the %1$s template %2$s (#%3$d)',
				$this->engagement_type,
				get_the_title( $engagement_template_id ),
				$engagement_template_id
			)
		);

		$this->clear_notices( $engagement_template_id );

		$args          = array( $engagement_template_id );
		$awarded_count = $this->count_awarded_engagements( $engagement_template_id );

		if ( 0 === $awarded_count ) {

			$log_message    = 'no awarded %1$ss to bulk sync with the %1$s template %2$s (#%3$d)';
			$notice_message = $this->get_text( self::TEXT_SYNC_NOTICE_NO_AWARDED_ENGAGEMENTS, compact( 'engagement_template_id' ) );
			$notice_id      = 'awarded-%1$ss-sync-%2$d-no-awarded';
			$notice_type    = 'error';

		} elseif ( wp_next_scheduled( $this->schedule_hook, $args ) ) {

			$log_message    = 'awarded %1$ss bulk sync already scheduled for the %1$s template %2$s (#%3$d)';
			$notice_message = $this->get_text( self::TEXT_SYNC_NOTICE_ALREADY_SCHEDULED, compact( 'engagement_template_id' ) );
			$notice_id      = 'awarded-%1$ss-sync-%2$d-already-scheduled';
			$notice_type    = 'warning';
		} else {

			wp_schedule_single_event( time(), $this->schedule_hook, $args );
			$log_message    = 'awarded %1$ss bulk sync scheduled for the %1$s template %2$s (#%3$d)';
			$notice_message = $this->get_text( self::TEXT_SYNC_NOTICE_SCHEDULED, compact( 'engagement_template_id' ) );
			$notice_id      = 'awarded-%1$ss-sync-%2$d-scheduled';
			$notice_type    = 'info';
		}

		$this->log(
			sprintf(
				$log_message,
				$this->engagement_type,
				get_the_title( $engagement_template_id ),
				$engagement_template_id
			)
		);

		LLMS_Admin_Notices::add_notice(
			sprintf( $notice_id, $this->engagement_type, $engagement_template_id ),
			$notice_message,
			array(
				'dismissible'      => true,
				'dismiss_for_days' => 0,
				'type'             => $notice_type,
			)
		);
	}

	/**
	 * Sync awarded engagements.
	 *
	 * @since 6.0.0
	 *
	 * @param LLMS_Abstract_User_Engagement[] $engagements Array of awarded engagements to sync.
	 * @param int                             $template_id Engagement template ID.
	 * @return void
	 */
	private function sync_awarded_engagements( $engagements, $template_id ) {

		$success_log_message = 'awarded %1$s %2$s (#%3$d) successfully synced with template %4$s (#%5$d)';
		$error_log_message   = 'an error occurred while trying to sync awarded %1$s %2$s (#%3$d) from template %4$s (#%5$d)';

		foreach ( $engagements as $awarded_engagement ) {
			$this->log(
				sprintf(
					$awarded_engagement->sync() ? $success_log_message : $error_log_message,
					$this->engagement_type,
					$awarded_engagement->get( 'title', true ),
					$awarded_engagement->get( 'id' ),
					get_the_title( $template_id ),
					$template_id
				)
			);
		}
	}

	/**
	 * Execute sync for each item in the queue until all awarded engagements are synced.
	 *
	 * @since 6.0.0
	 *
	 * @param array $args Array of processing data.
	 * @return boolean `true` to keep the item in the queue and process again.
	 *                 `false` to remove the item from the queue.
	 */
	public function task( $args ) {

		$this->log(
			sprintf(
				'awarded %1$ss bulk sync task started for the %1$s template %2$s (#%3$d) - chunk %4$d',
				$this->engagement_type,
				get_the_title( $args['query_args']['templates'] ),
				$args['query_args']['templates'],
				$args['query_args']['page']
			)
		);

		// Ensure the item has all the data we need to process it.
		if ( empty( $args['query_args']['templates'] ) ) {
			return false;
		}

		$query = new LLMS_Awards_Query( $args['query_args'] );

		if ( $query->has_results() ) {
			$this->sync_awarded_engagements( $query->get_awards(), $args['query_args']['templates'] );
		}

		if ( $query->is_last_page() ) {
			$this->process_completed( $args );
		}

		return false;
	}
}
