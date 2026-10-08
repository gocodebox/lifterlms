<?php
/**
 * Lesson Time Session model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 10.1.0
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Lesson_Time_Session model class
 *
 * Manages individual lesson time tracking session records stored in the
 * lifterlms_lesson_time_sessions table.
 *
 * @since 10.1.0
 */
class LLMS_Lesson_Time_Session extends LLMS_Abstract_Database_Store {

	/**
	 * Constructor.
	 *
	 * @since 10.1.0
	 *
	 * @param int|null $id Record ID.
	 */
	public function __construct( $id = null ) {
		$this->id = $id;
		parent::__construct();
	}

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
