<?php
/**
 * Processor: Awarded Certificates Bulk Sync.
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Processor_Awarded_Certificates_Bulk_Sync class.
 *
 * @since [version]
 */
class LLMS_Processor_Awarded_Certificates_Bulk_Sync extends LLMS_Abstract_Processor {

	/**
	 * Unique identifier for the processor.
	 *
	 * @var string
	 */
	protected $id = 'awarded_certificates_bulk_sync';

	/**
	 * WP Cron Hook for scheduling the bg process.
	 *
	 * @var string
	 */
	private $schedule_hook = 'llms_awarded_certificates_bulk_sync';

	/**
	 * Action triggered to sync all the awarded certificate sthat need to be updated.
	 *
	 * @since [version]
	 *
	 * @param int certificate_template_id WP Post ID of the certificate template.
	 * @return void
	 */
	public function dispatch_sync( $certificate_template_id ) {

		$this->log(
			sprintf(
				'awarded certificates bulk sync dispatched for the certificate template %1$s (#%2$d)',
				get_the_title( $certificate_template_id ),
				$certificate_template_id
			)
		);

		$args = array(
			'templates' => $certificate_template_id,
			'per_page'  => 20,
			'page'      => 1,
			'status'    => array(
				'publish',
				'future',
			),
			'type'      => 'certificate',
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

			$args['page']++;
		}

		// Save queue and dispatch the process.
		$this->save()->dispatch();

	}

	/**
	 * Initializer.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function init() {

		// For the cron.
		add_action( $this->schedule_hook, array( $this, 'dispatch_sync' ), 10, 1 );

		// For LifterLMS actions which trigger bulk enrollment.
		$this->actions = array(
			'llms_do_awarded_certificates_bulk_sync' => array(
				'arguments' => 1,
				'callback'  => 'schedule_sync',
				'priority'  => 10,
			),
		);

	}

	/**
	 * Schedule sync.
	 *
	 * This will schedule an event that will setup the queue of items for the background process.
	 *
	 * @since [version]
	 *
	 * @param int $certificate_template_id WP Post ID of the certificate template.
	 * @return void
	 */
	public function schedule_sync( $certificate_template_id ) {

		$this->log(
			sprintf(
				'awarded certificates bulk sync for the certificate template %1$s (#%2$d)',
				get_the_title( $certificate_template_id ),
				$certificate_template_id
			)
		);

		$args = array( $certificate_template_id );

		if ( ! wp_next_scheduled( $this->schedule_hook, $args ) ) {

			wp_schedule_single_event( time(), $this->schedule_hook, $args );
			$this->log(
				sprintf(
					'awarded certificates bulk sync scheduled for the certificate template %1$s (#%2$d)',
					get_the_title( $certificate_template_id ),
					$certificate_template_id
				)
			);

			$this->clear_notices( $certificate_template_id );

			LLMS_Admin_Notices::add_notice(
				sprintf( 'awarded-certificates-sync-%1$d-started', $certificate_template_id ),
				sprintf(
					// Translators: %1$s Anchor opening tag linking to the certificate template, %2$s Certificate Template name, %3$d Certificate Template ID, %4s Anchor closing tag.
					__( 'Awarded certificates sync scheduled for the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
					sprintf( '<a href="%1$s" target="_blank">', get_edit_post_link( $certificate_template_id ) ),
					get_the_title( $certificate_template_id ),
					$certificate_template_id,
					'</a>'
				),
				array(
					'dismissible'      => true,
					'dismiss_for_days' => 0,
				)
			);

		}

	}

	/**
	 * Execute sync for each item in the queue until all awarded certificates are synced.
	 *
	 * @since [version]
	 *
	 * @param array $args Array of processing data.
	 * @return boolean `true` to keep the item in the queue and process again.
	 *                 `false` to remove the item from the queue.
	 */
	public function task( $args ) {

		$this->log(
			sprintf(
				'awarded certificates bulk sync task started for the certificate template %1$s (#%2$d) - chunk %3$d',
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
			$this->sync_awarded_certificates( $query->get_awards(), $args['query_args']['templates'] );
		}

		if ( $query->is_last_page() ) {
			$this->process_completed( $args );
		}

		return false;

	}

	/**
	 * Sync awarded certificates.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate[] $certificates Array of awarded certificates to sync.
	 * @param int                     $template_id  Certificate template ID.
	 * @return void
	 */
	private function sync_awarded_certificates( $certificates, $template_id ) {

		foreach ( $certificates as $awarded_certificate ) {
			$sync = $awarded_certificate->sync();
			if ( is_wp_error( $sync ) ) {
				$this->log(
					sprintf(
						'An error occurred while trying to sync awarded certificate %1$s (#%2$d) from template %3$s (#%4$d)',
						$awarded_certificate->get( 'title' ),
						$awarded_certificate->get( 'id' ),
						get_the_title( $template_id ),
						$template_id
					)
				);
			}
		}

	}

	/**
	 * Clear notices.
	 *
	 * @since [version]
	 *
	 * @param int $certificate_template_id WP Post ID of the certificate template.
	 * @return void
	 */
	private function clear_notices( $certificate_template_id ) {

		LLMS_Admin_Notices::delete_notice(
			sprintf( 'awarded-certificates-sync-%1$d-started', $certificate_template_id )
		);
		LLMS_Admin_Notices::delete_notice(
			sprintf( 'awarded-certificates-sync-%1$d-done', $certificate_template_id )
		);

	}

	/**
	 * Perform actions when the process is completed.
	 *
	 * @since [version]
	 *
	 * @param array $args Array of processing data.
	 * @return void
	 */
	private function process_completed( $args ) {

		$this->log(
			sprintf(
				'awarded certificate bulk sync completed for the certificate template %1$s (#%2$d)',
				get_the_title( $args['query_args']['templates'] ),
				$args['query_args']['templates']
			)
		);

		LLMS_Admin_Notices::delete_notice(
			sprintf( 'awarded-certificates-sync-%1$d-started', $args['query_args']['templates'] )
		);

		LLMS_Admin_Notices::add_notice(
			sprintf( 'awarded-certificates-sync-%1$d-done', $args['query_args']['templates'] ),
			sprintf(
				// Translators: %1$s Anchor opening tag linking to the certificate template, %2$s Certificate Template name, %3$d Certificate Template ID, %4s Anchor closing tag.
				__( 'Awarded certificates sync completed for the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
				sprintf( '<a href="%1$s" target="_blank">', get_edit_post_link( $args['query_args']['templates'] ) ),
				get_the_title( $args['query_args']['templates'] ),
				$args['query_args']['templates'],
				'</a>'
			),
			array(
				'dismissible'      => true,
				'dismiss_for_days' => 0,
			)
		);

	}

}

return new LLMS_Processor_Awarded_Certificates_Bulk_Sync();
