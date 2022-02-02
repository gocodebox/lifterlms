<?php
/**
 * LLMS_Processor_Achievement_Sync class
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processor: Sync awarded achievements to their achievement template.
 *
 * @since [version]
 */
class LLMS_Processor_Achievement_Sync extends LLMS_Abstract_Processor_User_Engagement_Sync {

	/**
	 * The type of the user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'achievement';

	/**
	 * Unique identifier for the processor.
	 *
	 * @var string
	 */
	protected $id = 'awarded_achievements_bulk_sync';

	/**
	 * WP Cron Hook for scheduling the background process.
	 *
	 * @var string
	 */
	protected $schedule_hook = 'llms_awarded_achievements_bulk_sync';
}

return new LLMS_Processor_Achievement_Sync();
