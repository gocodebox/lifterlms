<?php
/**
 * LLMS_Notifications_Query class file
 *
 * @package LifterLMS/Notifications/Classes
 *
 * @since 3.8.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Notifications_Query class.
 *
 * Query LifterLMS Students for a given course/membership.
 *
 * @example
 *   $query = new LLMS_Notifications_Query( array(
 *       'subscriber' => 123, // null
 *       'per_page' => 10,
 *       'statuses' => 'new', // array( 'new', 'read', '...' )
 *       'types' => 'basic', // array( 'basic', 'email', '...' )
 *   ) );
 *
 * @since 3.8.0
 * @since 3.14.0 Unknown.
 */
class LLMS_Notifications_Query extends LLMS_Database_Query {

	/**
	 * Identify the extending query
	 *
	 * @var  string
	 */
	protected $id = 'notifications';

	/**
	 * Get an array of allowed notification statuses.
	 *
	 * @since 3.8.0
	 * @since 7.1.0 Added 'error' among the available statuses.
	 *
	 * @return string[]
	 */
	private function get_available_statuses() {
		return array( 'new', 'sent', 'read', 'unread', 'deleted', 'failed', 'error' );
	}

	/**
	 * Get the available notification types
	 *
	 * @return   string[]
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function get_available_types() {
		return array( 'basic', 'email' );
	}

	/**
	 * Retrieve default arguments for a student query.
	 *
	 * @since 3.8.0
	 * @since 3.11.0 Unknown.
	 * @since 7.1.0 Explicitly exclude 'error' status.
	 *               Drop usage of `this->get_filter( 'default_args' )` in favor of `'llms_notification_query_default_args'`.
	 * @return array
	 */
	protected function get_default_args() {

		$args = array(
			'post_id'    => null,
			'subscriber' => null,
			'sort'       => array(
				'updated' => 'DESC',
				'id'      => 'DESC',
			),
			'statuses'   => array_values( array_diff( $this->get_available_statuses(), array( 'error' ) ) ),
			'triggers'   => array(),
			'types'      => array(),
			'user_id'    => null,
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		/**
		 * Filters the notifications query's default args.
		 *
		 * @since 3.8.0
		 * @since 7.1.0 Added `$notifications_query` parameter.
		 *
		 * @param array                    $args                Array of default arguments to set up the query with.
		 * @param LLMS_Notifications_Query $notifications_query Instance of `LLMS_Notifications_Query`.
		 */
		return apply_filters( 'llms_notifications_query_default_args', $args, $this );
	}

	/**
	 * Convert raw results to notification objects.
	 *
	 * @since 3.8.0
	 * @since 7.1.0 When loading a notification, if errored, exclude it when not explictly requested.
	 *              Drop usage of `this->get_filter( 'default_args' )` in favor of `llms_notifications_query_get_notifications`.
	 *
	 * @return LLMS_Notification[]
	 */
	public function get_notifications() {

		$notifications = array();
		$results       = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$notification = ( new LLMS_Notification( $result->id ) )->load();

				// If the notification status is 'error' and errored notifications were not requested, skip it.
				if ( 'error' === $notification->get( 'status' ) && ! in_array( 'error', $this->arguments['statuses'], true ) ) {
					continue;
				}

				$notifications[] = $notification;

			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $notifications;
		}

		/**
		 * Filters the list of notifications.
		 *
		 * @since 3.8.0
		 *
		 * @param array                    $notifications       Array of {@see LLMS_Notification} instances.
		 * @param LLMS_Notifications_Query $notifications_query Instance of `LLMS_Notifications_Query`.
		 */
		return apply_filters( 'llms_notifications_query_get_notifications', $notifications, $this );
	}

	/**
	 * Parse arguments needed for the query
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function parse_args() {

		$this->parse_statuses();
		$this->parse_types();
	}

	/**
	 * Parse submitted statuses
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function parse_statuses() {

		$statuses = $this->arguments['statuses'];

		// allow strings to be submitted when only requesting one status
		if ( is_string( $statuses ) ) {
			$statuses = array( $statuses );
		}

		// ensure only valid statuses are used
		$statuses = array_intersect( $statuses, $this->get_available_statuses() );

		$this->arguments['statuses'] = $statuses;
	}

	/**
	 * Parse submitted types
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function parse_types() {

		$types = $this->arguments['types'];

		// allow strings to be submitted when only requesting one status
		if ( is_string( $types ) ) {
			$types = array( $types );
		}

		// ensure only valid types are used
		$types                    = array_intersect( $types, $this->get_available_types() );
		$this->arguments['types'] = $types;
	}

	/**
	 * Parse submitted triggers
	 *
	 * @return   void
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	private function parse_triggers() {

		$triggers = $this->arguments['triggers'];

		// allow strings to be submitted when only requesting one status
		if ( is_string( $triggers ) ) {
			$triggers = array( $triggers );
		}

		$this->arguments['triggers'] = $triggers;
	}

	/**
	 * Prepare the SQL for the query.
	 *
	 * @since 3.8.0
	 * @since 3.9.4 Unknown.
	 * @since 6.0.0 Renamed from `preprare_query()`.
	 * @since 7.1.0 Use `$this->sql_select_columns({columns})` to determine the columns to select.
	 *
	 * @return string
	 */
	protected function prepare_query() {

		global $wpdb;

		$vars = array(
			$this->get_skip(),
			$this->get( 'per_page' ),
		);

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- SQL is prepared in other functions.
		$sql = $wpdb->prepare(
			"SELECT {$this->sql_select_columns()}
			FROM {$wpdb->prefix}lifterlms_notifications AS n
			LEFT JOIN {$wpdb->posts} AS p on p.ID = n.post_id
			{$this->sql_where()}
			{$this->sql_orderby()}
			LIMIT %d, %d
			;",
			$vars
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $sql;
	}

	/**
	 * Retrieve the prepared SQL for the ORDER clause.
	 *
	 * Slightly modified from abstract to include the table name to prevent ambiguous errors.
	 *
	 * @since 3.9.2
	 * @since 7.1.0 Drop usage of `$this->get_filter('where')` in favor of `llms_notifications_query_where`.
	 *
	 * @return string
	 */
	protected function sql_orderby() {

		$sql = 'ORDER BY';

		$comma = false;

		foreach ( $this->get( 'sort' ) as $orderby => $order ) {
			$pre   = ( $comma ) ? ', ' : ' ';
			$sql  .= $pre . 'n.' . sanitize_sql_orderby( "{$orderby} {$order}" );
			$comma = true;
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		/**
		 * Filters the query WHERE clause.
		 *
		 * @since 7.1.0
		 *
		 * @param string                   $sql                 The WHERE clause of the query.
		 * @param LLMS_Notifications_Query $notifications_query Instance of LLMS_Events_Query.
		 */
		return apply_filters( 'llms_notifications_query_where', $sql, $this );
	}

	/**
	 * Retrieve the prepared SQL for the WHERE clause
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.14.0
	 */
	private function sql_where() {

		global $wpdb;

		$where = 'WHERE 1';

		$post_statuses = array_merge( array( 'publish' ), array_keys( llms_get_order_statuses() ) );
		$post_statuses = array_map( array( $this, 'escape_and_quote_string' ), $post_statuses );
		$where        .= sprintf( ' AND p.post_status IN ( %s )', implode( ', ', $post_statuses ) );

		// these args are all "whered" in the same way
		$wheres = array(
			'statuses' => 'status',
			'triggers' => 'trigger_id',
			'types'    => 'type',
		);

		// loop through them and build the where clauses based off the submitted data
		foreach ( $wheres as $arg_name => $col_name ) {

			$arg = $this->get( $arg_name );
			if ( $arg ) {
				$prepped = array_map( array( $this, 'escape_and_quote_string' ), $arg );
				$where  .= sprintf( ' AND n.%1$s IN( %2$s )', $col_name, implode( ', ', $prepped ) );
			}
		}

		// add subscriber info if set
		$subscriber = $this->get( 'subscriber' );
		if ( $subscriber ) {
			$where .= $wpdb->prepare( ' AND n.subscriber = %s', $subscriber );
		}

		// add post and user id checks
		foreach ( array( 'post_id', 'user_id' ) as $var ) {
			$arg = $this->get( $var );
			if ( $arg ) {
				$where .= sprintf( ' AND n.%1$s = %2$d', esc_sql( $var ), absint( $arg ) );
			}
		}

		return $where;
	}
}
