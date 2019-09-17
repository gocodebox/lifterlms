<?php
/**
 * User event session management.
 *
 * @package  LifterLMS/Classes
 *
 * @since 3.36.0
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Sessions class..
 *
 * @since 3.36.0
 */
class LLMS_Sessions {

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $_instance = null;

	/**
	 * Current user id.
	 *
	 * @var null
	 */
	protected $user_id = null;

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since 3.36.0
	 *
	 * @return LLMS_Sessions
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Private Constructor.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	private function __construct() {

		add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );

		if ( ! wp_next_scheduled( 'llms_end_idle_sessions' ) ) {
			wp_schedule_event( time(), 'every_five_mins', 'llms_end_idle_sessions' );
		}
		add_action( 'llms_end_idle_sessions', array( $this, 'end_idle_sessions' ) );

	}

	/**
	 * Add cron schedule for session end interval checks.
	 *
	 * @since 3.36.0
	 *
	 * @param array $schedules Array of cron schedules
	 * @return  array
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
	 *
	 * @param LLMS_Event $start Event object for a session start.
	 * @return LLMS_EVent
	 */
	protected function end( $start ) {

		return LLMS()->events()->record(
			array(
				'actor_id'     => $start->get( 'actor_id' ),
				'object_type'  => 'session',
				'object_id'    => $start->get( 'object_id' ),
				'event_type'   => 'session',
				'event_action' => 'end',
			)
		);

	}

	/**
	 * Ends the currently active session for the logged in user.
	 *
	 * @since 3.36.0
	 *
	 * @return LLMS_Event|false
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
	 *
	 * @return LLMS_Event|false
	 */
	public function get_current() {

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$session = $this->get_last_session();
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
				'per_page' => 1,
				'sort'     => array(
					'date' => 'DESC',
				),
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
	 * Determines if the given session is open (has not ended).
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
	 *
	 * @return obj|null
	 */
	protected function get_last_session() {

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
				get_current_user_id()
			)
		);

	}

	/**
	 * Retrieve open sessions.
	 *
	 * @since 3.36.0
	 *
	 * @param int $limit Number of sessions to return.
	 * @param int $skip Number of sessions to skip.
	 * @return LLMS_Event[]
	 */
	protected function get_open_sessions( $limit = 50, $skip = 0 ) {

		global $wpdb;
		$sessions = $wpdb->get_col(
			$wpdb->prepare(
				"
			   SELECT e1.id
			     FROM {$wpdb->prefix}lifterlms_events AS e1
			LEFT JOIN {$wpdb->prefix}lifterlms_events AS e2
			       ON e1.object_id = e2.object_id
			      AND e1.actor_id = e2.actor_id
			      AND e2.event_type = 'session'
			      AND e2.event_action = 'end'
			    WHERE e1.event_type = 'session'
			      AND e1.event_action = 'start'
			      AND e2.date IS NULL
			 ORDER BY e1.date ASC
			    LIMIT %d, %d
		",
				$skip,
				$limit
			)
		);

		$ret = array();
		foreach ( $sessions as $id ) {
			$ret[] = new LLMS_Event( $id );
		}

		return $ret;

	}

	/**
	 * Retrieve an array of events which occurred during a session.
	 *
	 * @since 3.36.0
	 *
	 * @param LLMS_Event $start Event record for the session.start event.
	 * @param array      $args Array of additional arguments to pass to the LLMS_Events_Query.
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
		);

		if ( ! $end ) {
			return null;
		}

		return new LLMS_Event( $end );

	}

	/**
	 * Retrieve a new session ID.
	 *
	 * @since 3.36.0
	 *
	 * @return int
	 */
	protected function get_new_id() {

		$last = $this->get_last_session();
		if ( ! $last ) {
			return 1;
		}

		return ++$last->object_id;

	}

	/**
	 * Start a new session for the current user.
	 *
	 * @since 3.36.0
	 *
	 * @return false|LLMS_Event
	 */
	public function start() {

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		return LLMS()->events()->record(
			array(
				'actor_id'     => $user_id,
				'object_type'  => 'session',
				'object_id'    => $this->get_new_id(),
				'event_type'   => 'session',
				'event_action' => 'start',
			)
		);

	}

}

return LLMS_Sessions::instance();
