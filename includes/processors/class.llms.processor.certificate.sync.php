<?php
/**
 * LLMS_Processor_Certificate_Sync class
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processor: Sync awarded certificates to their certificate template.
 *
 * @since [version]
 */
class LLMS_Processor_Certificate_Sync extends LLMS_Abstract_Processor_User_Engagement_Sync {

	/**
	 * The type of the user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'certificate';

	/**
	 * Unique identifier for the processor.
	 *
	 * @var string
	 */
	protected $id = 'awarded_certificates_bulk_sync';

	/**
	 * WP Cron Hook for scheduling the background process.
	 *
	 * @var string
	 */
	protected $schedule_hook = 'llms_awarded_certificates_bulk_sync';

	/**
	 * Returns a translated version of this plural user engagement type.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_plural_engagement_type() {

		return __( 'certificates', 'lifterlms' );
	}

	/**
	 * Returns a translated version of this singular user engagement type.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_singular_engagement_type() {

		return __( 'certificate', 'lifterlms' );
	}
}

return new LLMS_Processor_Certificate_Sync();
