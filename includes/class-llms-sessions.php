<?php
/**
 * User event session management
 *
 * @package LifterLMS/Classes
 *
 * @since 3.36.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Sessions class.
 *
 * @since 3.36.0
 * @since 3.37.2 Add filter `llms_sessions_end_idle_cron_recurrence` to allow customization of the recurrence of the idle session cleanup cronjob.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Removed the deprecated `LLMS_Sessions::$_instance` property.
 */
class LLMS_Sessions {

	use LLMS_Trait_Singleton;

	/**
	 * Current user id.
	 *
	 * @var null
	 */
	protected $user_id = null;

	/**
	 * Private Constructor.
	 *
	 * @since 3.36.0
	 * @since 3.37.2 Add filter to the cleanup cronjob interval.
	 *
	 * @return void
	 */
	private function __construct() {

		add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );

		if ( ! wp_next_scheduled( 'llms_end_idle_sessions' ) ) {
			/**
			 * Filter the recurrence interval at which LifterLMS closes idle sessions.
			 *
			 * @link https://developer.wordpress.org/reference/functions/wp_get_schedules/
			 *
			 * @since 3.37.2
			 *
			 * @param string $recurrence Cron job recurrence interval. Must be valid interval as retrieved from `wp_get_schedules()`. Default is "every_five_mins".
			 */
			$recurrence = apply_filters( 'llms_sessions_end_idle_cron_recurrence', 'every_five_mins' );
			wp_schedule_event( time(), $recurrence, 'llms_end_idle_sessions' );
		}
		add_action( 'llms_end_idle_sessions', array( $this, 'end_idle_sessions' ) );

	}

	/**
	 * Add cron schedule for session end interval checks.
	 *
	 * @since 3.36.0
	 *
	 * @param array $schedules Array of cron schedules.
	 * @return array
	 */
	public function add_cron_schedule( $schedules ) {

		// Adds every 5 minutes to the existing schedules.
		$schedules['every_five_mins'] = array(
			'interval' => MINUTE_IN_SECONDS * 5,
			'display'  => sprintf( __( 'Every %d Minutes', 'lifterlms' ), 5 ),
		);
		return $schedules;

	}

	/**
	 * End the 50 oldest idle sessions.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function end_idle_sessions() {

		foreach ( $this->get_open_sessions() as $i => $event ) {
			if ( $this->is_session_idle( $event ) ) {
				$this->end( $event );
			}
		}

	}

	/**
	 * End a session.
	 *
	 * @since 3.36.0
	 * @since 4.5.0 Delete open session entry from the `wp_lifterlms_events_open_sessions` table.
	 *
	 * @param LLMS_Event $start Event object for a session start.
	 * @return LLMS_Event|WP_Error
	 */
	protected function end( $start ) {

		$end = llms()->events()->record(
			array(
				'actor_id'     => $start->get( 'actor_id' ),
				'object_type'  => 'session',
				'object_id'    => $start->get( 'object_id' ),
				'event_type'   => 'session',
				'event_action' => 'end',
			)
		);

		if ( ! is_wp_error( $end ) ) {
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM {$wpdb->prefix}lifterlms_events_open_sessions
					WHERE `event_id` = %d
					",
					$start->get( 'id' )
				)
			); // db call ok; no-cache ok.
		}

		return $end;
	}

	/**
	 * Ends the currently active session for the logged in user.
	 *
	 * @since 3.36.0
	 *
	 * @return LLMS_Event|WP_Error|false
	 */
	public function end_current() {

		$current = $this->get_current();
		if ( ! $current ) {
			return false;
		}

		return $this->end( $current );

	}

	/**
	 * Retrieve the current session start event record for a given user.
	 *
	 * @since 3.36.0
	 * @since 4.5.0 Added optional `$user_id` parameter.
	 *
	 * @param int $user_id Optional. WP_User ID of a student. Default `null`
	 *                     If not provided, or a falsy is provided, will fall back on the current user id.
	 * @return LLMS_Event|false
	 */
	public function get_current( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$session = $this->get_last_session( $user_id );
		if ( ! $session ) {
			return false;
		}

		$session = new LLMS_Event( $session->id );

		if ( ! $this->is_session_open( $session ) ) {
			return false;
		}

		return $session;

	}

	/**
	 * Determine if a session is idle.
	 *
	 * A session is considered idle if it's open and no new events have been recorded
	 * in the last 30 minutes.
	 *
	 * @since 3.36.0
	 * @since 4.7.0 When retrieving the last event, instantiate the events query passing `no_found_rows` arg as `true`,
	 *              to improve performance.
	 *
	 * @param LLMS_Event $start Event record for the start of the session.
	 * @return bool
	 */
	public function is_session_idle( $start ) {

		// Session is closed so it can't be idle.
		if ( ! $this->is_session_open( $start ) ) {
			return false;
		}

		$now = llms_current_time( 'timestamp' );

		/**
		 * Filter the time (in minutes) to allow a session to remain open before it's considered an "idle" session.
		 *
		 * @param int $minutes Number of minutes.
		 */
		$timeout = absint( apply_filters( 'llms_idle_session_timeout', 30 ) ) * MINUTE_IN_SECONDS;

		// Session has started within the idle window, so it can't have expired yet.
		if ( ( $now - strtotime( $start->get( 'date' ) ) ) < $timeout ) {
			return false;
		}

		$events = $this->get_session_events(
			$start,
			array(
				'per_page'      => 1,
				'sort'          => array(
					'date' => 'DESC',
				),
				'no_found_rows' => true,
			)
		);

		// No events, the session is idle.
		if ( ! $events ) {
			return true;
		}

		$last_event = array_shift( $events );
		return ( ( $now - strtotime( $last_event->get( 'date' ) ) ) > $timeout );

	}

	/**
	 * Determines if the given session is open (has not ended)
	 *
	 * @since 3.36.0
	 *
	 * @param LLMS_Event Event record for the start of the session.
	 * @return bool
	 */
	public function is_session_open( $start ) {

		return is_null( $this->get_session_end( $start ) );

	}

	/**
	 * Retrieve the last session object for the current user.
	 *
	 * @since 3.36.0
	 * @since 4.5.0 Added optional `$user_id` parameter.
	 *
	 * @param int $user_id Optional. WP_User ID of a student. Default `null`
	 *                     If not provided, or a falsy is provided, will fall back on the current user id.
	 * @return obj|null
	 */
	protected function get_last_session( $user_id = null ) {
		$user_id = $user_id ? $user_id : get_current_user_id();

		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			   FROM {$wpdb->prefix}lifterlms_events
			  WHERE actor_id = %d
			    AND object_type = 'session'
			    AND event_type = 'session'
			    AND event_action = 'start'
		   ORDER BY date DESC
			  LIMIT 1;",
				$user_id
			)
		); // db call ok; no-cache ok.

	}

	/**
	 * Retrieve open sessions.
	 *
	 * @since 3.36.0
	 * @since 4.5.0 Retrieve open sessions from the `wp_lifterlms_events_open_sessions` table.
	 *
	 * @param int $limit Number of sessions to return.
	 * @param int $skip  Number of sessions to skip.
	 * @return LLMS_Event[]
	 */
	protected function get_open_sessions( $limit = 50, $skip = 0 ) {

		global $wpdb;
		$sessions = $wpdb->get_col(
			$wpdb->prepare(
				"
			   SELECT event_id
			   FROM {$wpdb->prefix}lifterlms_events_open_sessions
			   ORDER BY event_id ASC
			   LIMIT %d, %d
		",
				$skip,
				$limit
			)
		); // db call ok; no-cache ok.

		$ret = array();
		if ( count( $sessions ) ) {
			foreach ( $sessions as $id ) {
				$ret[] = new LLMS_Event( $id );
			}
		}

		return $ret;

	}

	/**
	 * Retrieve an array of events which occurred during a session.
	 *
	 * @since 3.36.0
	 *
	 * @param LLMS_Event $start Event record for the session.start event.
	 * @param array      $args  Array of additional arguments to pass to the LLMS_Events_Query.
	 * @return LLMS_Event[]
	 */
	public function get_session_events( $start, $args = array() ) {

		$end = $this->get_session_end( $start );

		$args = wp_parse_args(
			$args,
			array(
				'date_after' => $start->get( 'date' ),
				'exclude'    => array( $start->get( 'id' ) ),
				'actor'      => $start->get( 'actor_id' ),
				'sort'       => array(
					'date' => 'ASC',
				),
				'per_page'   => 10,
			)
		);

		if ( $end ) {
			$args['date_before'] = $end->get( 'date' );
			$args['exclude'][]   = $end->get( 'id' );
		}

		$query = new LLMS_Events_Query( $args );
		return $query->get_events();

	}

	/**
	 * Retrieve session end record for by session id.
	 *
	 * @since 3.36.0
	 *
	 * @param LLMS_Event $start Event record for the session.start event.
	 * @return LLMS_Event|end
	 */
	public function get_session_end( $start ) {

		global $wpdb;
		$end = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id
			   FROM {$wpdb->prefix}lifterlms_events
			  WHERE actor_id = %d
			    AND object_id = %d
			    AND object_type = 'session'
			    AND event_type = 'session'
			    AND event_action = 'end'
		   ORDER BY date DESC
			  LIMIT 1;",
				$start->get( 'actor_id' ),
				$start->get( 'object_id' )
			)
		); // db call ok; no-cache ok.

		if ( ! $end ) {
			return null;
		}

		return new LLMS_Event( $end );

	}

	/**
	 * Retrieve a new session ID.
	 *
	 * @since 3.36.0
	 * @since 4.5.0 Added optional `$user_id` parameter.
	 *
	 * @param int $user_id Optional. WP_User ID of a student. Default `null`
	 *                     If not provided, or a falsy is provided, will fall back on the current user id.
	 * @return int
	 */
	protected function get_new_id( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();

		$last = $this->get_last_session( $user_id );
		if ( ! $last ) {
			return 1;
		}

		return ++$last->object_id;

	}

	/**
	 * Start a new session for the current user.
	 *
	 * @since 3.36.0
	 * @since 4.5.0 Create open session entry in the `wp_lifterlms_events_open_sessions` table.
	 *                  Added optional `$user_id` parameter.
	 *
	 * @param int $user_id Optional. WP_User ID of a student. Default `null`
	 *                     If not provided, or a falsy is provided, will fall back on the current user id.
	 * @return false|LLMS_Event|WP_Error
	 */
	public function start( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$start = llms()->events()->record(
			array(
				'actor_id'     => $user_id,
				'object_type'  => 'session',
				'object_id'    => $this->get_new_id( $user_id ),
				'event_type'   => 'session',
				'event_action' => 'start',
			)
		);

		if ( ! is_wp_error( $start ) ) {
			global $wpdb;
			$wpdb->query( // db call ok; no-cache ok.
				$wpdb->prepare(
					"
					INSERT INTO {$wpdb->prefix}lifterlms_events_open_sessions ( `event_id` ) VALUES ( %d )
					",
					$start->get( 'id' )
				)
			);
		}

		return $start;

	}

}

return LLMS_Sessions::instance();
