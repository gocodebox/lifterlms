<?php
/**
 * LifterLMS Event management.
 *
 * @package  LifterLMS/Classes
 *
 * @since 3.36.0
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Events class.
 *
 * @since 3.36.0
 * @since 3.36.1 Improve performances when checking if an event is valid in `LLMS_Events->is_event_valid()`.
 *               Remove redundant check on `is_singular()` and `is_post_type_archive()` in `LLMS_Events->should_track_client_events()`.
 */
class LLMS_Events {

	/**
	 * Singleton instance
	 *
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * List of registered event types.
	 *
	 * @var array
	 */
	protected $registered_events = array();

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since 3.36.0
	 *
	 * @return LLMS_Events
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

		add_action( 'init', array( $this, 'register_events' ) );
		add_action( 'init', array( $this, 'store_cookie' ) );

	}

	/**
	 * Retrieves an array of client settings used to initialize the JS Tracking instance on the frontend.
	 *
	 * @since 3.36.0
	 *
	 * @return array
	 */
	public function get_client_settings() {

		$events = ! $this->should_track_client_events() ? array() : array_keys( array_filter( $this->get_registered_events() ) );

		/**
		 * Filter client-side tracking settings
		 *
		 * @since 3.36.0
		 *
		 * @param array $settings {
		 *     Hash of client-side settings.
		 *
		 *     @type string $nonce Nonce used to verify client-side events.
		 *     @type string[] $events Array of events that should be tracked.
		 * }
		 */
		return apply_filters(
			'llms_events_get_client_settings',
			array(
				'nonce'  => wp_create_nonce( 'llms-tracking' ),
				'events' => $events,
			)
		);

	}

	/**
	 * Retrieve an array of valid events.
	 *
	 * @since 3.36.0
	 *
	 * @return array Array key is the event name and array value is used to determine if the key is a client-side event.
	 */
	public function get_registered_events() {
		return $this->registered_events;
	}

	/**
	 * Determine if the event string is registered and valid.
	 *
	 * @since 3.36.0
	 * @since 3.36.1 Use more performant `array_key_exists( $key, $array_assoc )` in place of `in_array( $key, array_keys( $array_assoc ), true )`.
	 *
	 * @param string $event Event string (${event_type}.${event_action}). EG: "account.signon".
	 * @return bool
	 */
	protected function is_event_valid( $event ) {

		return array_key_exists( $event, $this->get_registered_events() );

	}

	/**
	 * Prepares partial events from client-side event data.
	 *
	 * @since 3.36.0
	 *
	 * @param array $raw_event Raw event from client-side data.
	 * @return array
	 */
	public function prepare_event( $raw_event = array() ) {

		if ( ! isset( $raw_event['event'] ) ) {
			// Translators: %s = Event field key.
			return new WP_Error( 'llms_events_missing_event', sprintf( __( 'The event is missing the "%s" field.', 'lifterlms' ), 'event' ) );
		}

		$event    = explode( '.', $raw_event['event'] );
		$prepared = array(
			'actor_id'     => get_current_user_id(),
			'event_type'   => $event[0],
			'event_action' => $event[1],
			'meta'         => isset( $raw_event['meta'] ) ? $raw_event['meta'] : array(),
		);

		// Convert timestamps to MYSQL date.
		if ( isset( $raw_event['time'] ) && is_numeric( $raw_event['time'] ) ) {
			$prepared['date'] = date( 'Y-m-d H:i:s', $raw_event['time'] );
		}

		if ( isset( $raw_event['url'] ) ) {
			$id = url_to_postid( $raw_event['url'] );
			if ( ! $id ) {
				// Translators: %s = URL.
				return new WP_Error( 'llms_events_invalid_url', sprintf( __( 'The URL "%s" cannot be mapped to a valid post object.', 'lifterlms' ), esc_url( $raw_event['url'] ) ) );
			}
			$prepared['object_id']   = $id;
			$prepared['object_type'] = str_replace( 'llms_', '', get_post_type( $id ) );
		} elseif ( isset( $raw_event['object_id'] ) && isset( $raw_event['object_type'] ) ) {
			$prepared['object_id']   = $raw_event['object_id'];
			$prepared['object_type'] = $raw_event['object_type'];
		}

		return $prepared;

	}

	/**
	 * Store an event in the database.
	 *
	 * @since 3.36.0
	 *
	 * @param array $args {
	 *     Event data
	 *
	 *     @type int $actor_id WP_User ID.
	 *     @type string $object_type Type of object being acted upon (post,user,comment,etc...).
	 *     @type int $object_id WP_Post ID, WP_User ID, WP_Comment ID, etc...
	 *     @type string $event_type Type of event (account, page, course, etc...).
	 *     @type string $event_action The event action or verb (signon,viewed,launched,etc...).
	 * }
	 * @return [type]
	 */
	public function record( $args = array() ) {

		$err = new WP_Error();

		foreach ( array( 'actor_id', 'object_type', 'object_id', 'event_type', 'event_action' ) as $key ) {
			if ( ! in_array( $key, array_keys( $args ), true ) ) {
				// Translators: %s = key name of the missing field.
				$err->add( 'llms_event_record_missing_field', sprintf( __( 'Missing required field: "%s".', 'lifterlms' ), $key ) );
			}
		}

		if ( $err->get_error_codes() ) {
			return $err;
		}

		$event = sprintf( '%1$s.%2$s', $args['event_type'], $args['event_action'] );
		if ( ! $this->is_event_valid( $event ) ) {
			// Translators: %s = Submitted event string.
			return new WP_Error( 'llms_event_record_invalid_event', sprintf( __( 'The event "%s" is invalid.', 'lifterlms' ), $event ) );
		}

		$args = $this->sanitize_raw_event( $args );
		$meta = isset( $args['meta'] ) ? $args['meta'] : null;
		unset( $args['meta'] );

		if ( ! in_array( $event, array( 'session.start', 'session.end' ), true ) ) {

			// Start a session if one isn't open.
			$sessions = LLMS_Sessions::instance();
			if ( false === $sessions->get_current() ) {
				$sessions->start();
			}
		}

		$event = new LLMS_Event();
		if ( ! $event->setup( $args )->save() ) {
			$err->add( 'llms_event_recored_unknown_error', __( 'An unknown error occurred during event creation.', 'lifterlms' ) );
			return $err;
		}
		if ( $meta && ! empty( $meta ) ) {
			$event->set_metas( $meta, true );
		}

		// End the current session on signout.
		if ( 'account.signout' === $event ) {
			LLMS_Sessions::instance()->end_current();
		}

		return $event;

	}

	/**
	 * Record multiple events.
	 *
	 * Events are recorded with an SQL transaction. If any errors are encountered the transaction is rolled back (not events are recorded).
	 *
	 * @since 3.36.0
	 *
	 * @param array[] $events Array of event hashes. See LLMS_Events::record() for hash description.
	 * @return LLMS_Event[]|WP_Error Array of recorded events on success or WP_Error on failure.
	 */
	public function record_many( $events = array() ) {

		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );

		$recorded = array();
		$errors   = array();
		foreach ( $events as $event ) {

			$stat = $this->record( $event );
			if ( is_wp_error( $stat ) ) {
				$stat->add_data( $event );
				$errors[] = $stat;
			} else {
				$recorded[] = $stat;
			}
		}

		if ( count( $errors ) ) {
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error( 'llms_events_record_many_errors', __( 'There was one or more errors encountered while recording the events.', 'lifterlms' ), $errors );
		}

		$wpdb->query( 'COMMIT' );

		return $recorded;

	}

	/**
	 * Register event types
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function register_events() {

		$events = array(
			'account.signon'  => false,
			'account.signout' => false,
			'session.start'   => false,
			'session.end'     => false,
			'page.load'       => true,
			'page.exit'       => true,
			'page.focus'      => true,
			'page.blur'       => true,
		);

		/**
		 * Filter the list of registered events.
		 *
		 * Allows 3rd parties to register (or unregister) tracked events.
		 *
		 * @since 3.36.0
		 *
		 * @param array $events Array of events. Array key is the event name and array value is used to determine if the key is a client-side event.
		 */
		$this->registered_events = apply_filters( 'llms_get_registered_events', $events );

	}

	/**
	 * Recursively sanitize event data.
	 *
	 * @since 3.36.0
	 *
	 * @param array $raw Event information array.
	 * @return array
	 */
	protected function sanitize_raw_event( $raw ) {

		$clean = array();

		foreach ( $raw as $key => $val ) {

			// This will recursively handle any metadata submitted.
			if ( is_array( $val ) ) {
				$val = $this->sanitize_raw_event( $val );
			} elseif ( in_array( $key, array( 'actor_id', 'object_id' ), true ) ) {
				// cast id fields to int.
				$val = absint( $val );
			} else {
				// everything else is a text field.
				$val = sanitize_text_field( $val );
			}

			// Sanitize the key. This will ensure no dirty keys are submitted in metadata.
			$key = is_numeric( $key ) ? $key : sanitize_text_field( $key );

			$clean[ $key ] = $val;

		}

		return $clean;

	}

	/**
	 * Determine if client side events from the current page should be tracked.
	 *
	 * @since 3.36.0
	 *
	 * @return boolean
	 */
	protected function should_track_client_events() {

		$ret = false;

		/**
		 * Filter the post types that should be tracked
		 *
		 * @since 3.36.0
		 * @since 3.36.1 Remove redundant check on `is_singular()` and `is_post_type_archive()`.
		 *
		 * @param string[]|string $post_types An array of post type names or a pre-defined setting as a string.
		 *                                    "llms" uses all public LifterLMS and LifterLMS Add-on post types.
		 *                                    "all" tracks everything.
		 */
		$post_types = apply_filters( 'llms_tracking_post_types', 'llms' );

		if ( 'all' === $post_types ) {
			$ret = true;
		} elseif ( 'llms' === $post_types ) {

			// Filter public post types to include LifterLMS public post types.
			$post_types = array_keys( get_post_types( array( 'public' => true ) ) );
			foreach ( $post_types as $key => $type ) {
				if ( ! in_array( $type, array( 'course', 'lesson' ), true ) && 0 !== strpos( $type, 'llms_' ) ) {
					unset( $post_types[ $key ] );
				}
			}
		}

		if ( ! is_array( $post_types ) ) {
			$ret = false;
		} elseif ( is_singular( $post_types ) ) {
			$ret = true;
		} elseif ( is_post_type_archive( $post_types ) ) {
			$ret = true;
		} elseif ( is_llms_account_page() || is_llms_checkout() ) {
			$ret = true;
		}

		/**
		 * Filters whether or not the current page should track client-side events
		 *
		 * @since 3.36.0
		 *
		 * @param bool $ret Whether or not to track the current page.
		 * @param string[] $post_types Array of post types that should be tracked.
		 */
		return apply_filters( 'llms_tracking_should_track_client_events', $ret, $post_types );

	}

	/**
	 * Store event data saved in the tracking cookie.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function store_cookie() {

		$cookie = ! empty( $_COOKIE['llms-tracking'] ) ? json_decode( wp_unslash( $_COOKIE['llms-tracking'] ), true ) : false; // phpcs:ignore: WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via $this->sanitize_raw_event().
		if ( ! $cookie ) {
			return;
		}

		if ( ! empty( $cookie['nonce'] ) && wp_verify_nonce( $cookie['nonce'], 'llms-tracking' ) && get_current_user_id() ) {

			if ( ! empty( $cookie['events'] ) && is_array( $cookie['events'] ) ) {

				foreach ( $cookie['events'] as $event ) {

					$event = $this->prepare_event( $event );
					if ( ! is_wp_error( $event ) ) {
						$this->record( $event );
					}
				}
			}
		}

		setcookie( 'llms-tracking', '', time() - 60, '/' );

	}

}
