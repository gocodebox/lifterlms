<?php
/**
 * Lesson Time Session model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Lesson_Time_Session model class
 *
 * Manages individual lesson time tracking session records stored in the
 * lifterlms_lesson_time_sessions table.
 *
 * @since [version]
 */
class LLMS_Lesson_Time_Session extends LLMS_Abstract_Database_Store {

	/**
	 * Database table name suffix.
	 *
	 * @var string
	 */
	protected $table = 'lesson_time_sessions';

	/**
	 * Record type for hooks.
	 *
	 * @var string
	 */
	protected $type = 'lesson_time_session';

	/**
	 * Disable automatic created date.
	 *
	 * @var string
	 */
	protected $date_created = '';

	/**
	 * Disable automatic updated date.
	 *
	 * @var string
	 */
	protected $date_updated = '';

	/**
	 * Column definitions.
	 *
	 * @var array
	 */
	protected $columns = array(
		'user_id'             => '%d',
		'lesson_id'           => '%d',
		'session_token'       => '%s',
		'session_start'       => '%s',
		'session_end'         => '%s',
		'last_heartbeat_at'   => '%s',
		'accumulated_seconds' => '%d',
		'heartbeat_count'     => '%d',
		'flagged_gaps'        => '%d',
	);
}
