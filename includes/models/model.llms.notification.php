<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification implements JsonSerializable {

	public $id;

	// default properties
	private $created;
	private $updated;
	private $status;
	private $type;
	private $subscriber_id;
	private $trigger_id;
	private $user_id;
	private $post_id;

	// view related
	private $html;

	/**
	 * Constructor
	 * @param    int     $notification  Notification ID
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct( $notification = null ) {

		if ( is_numeric( $notification ) ) {
			$this->id = $notification;
		}

	}

	/**
	 * Get notification properties
	 * @param    string     $key  key to retrieve
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function __get( $key ) {
		return $this->get( $key, false );
	}

	/**
	 * Create a new notification in the database
	 * @param    array      $data  notification data
	 * @return   int|false         new notification id on success, false otherwise
	 * @since    [version]
	 * @version  [version]
	 */
	public function create( $data = array() ) {

		$time = current_time( 'mysql' );

		$data = wp_parse_args( $data, array(

			'created' => $time,
			'post_id' => null,
			'status' => 'new',
			'subscriber_id' => null,
			'trigger_id' => null,
			'type' => '',
			'updated' => $time,
			'user_id' => null,

		) );

		ksort( $data );
		$format = array(
			'%s',
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
		);

		global $wpdb;
		if ( 1 !== $wpdb->insert( $this->get_table(), $data, $format ) ) {
			return false;
		}

		$this->id = $wpdb->insert_id;

		return $this->id;

	}

	/**
	 * Determine if the triggering user is the subsriber
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function is_subscriber_self() {
		return ( $this->get( 'subscriber_id' ) == $this->get( 'user_id' ) );
	}

	/**
	 * Get notification properties
	 * @param    string     $key  key to retrieve
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get( $key, $skip_cache = false ) {

		// id will always be accessed from the object
		if ( 'id' === $key ) {
			return $this->id;
		}

		// return cached values if they exist
		if ( ! is_null( $this->$key ) && ! $skip_cache ) {
			return $this->$key;
		}

		// get the value from the database
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT {$key} FROM {$this->get_table()} WHERE id = %d", $this->id ) );

	}

	/**
	 * Retrieve the HTML for the current notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_html() {
		$view = $this->get_view();
		if ( $view ) {
			return $view->get_html();
		}
		return '';
	}

	/**
	 * Get the tablename for notification data
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_table() {
		global $wpdb;
		return $wpdb->prefix . 'lifterlms_notifications';
	}

	/**
	 * Retrieve an instance of the notification view class for the notification
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_view() {
		return LLMS()->notifications()->get_view( $this );
	}

	/**
	 * Called when converting a notification to JSON
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Load all notification data into the instance
	 * @return   self
	 * @since    [version]
	 * @version  [version]
	 */
	public function load() {

		global $wpdb;

		$query = $wpdb->prepare( "SELECT created, updated, status, type, subscriber_id, trigger_id, user_id, post_id FROM {$this->get_table()} WHERE id = %d", $this->id );
		$notification = $wpdb->get_row( $query, ARRAY_A );

		if ( $notification ) {

			foreach ( $notification as $key => $val ) {
				$this->$key = $val;
			}

			$this->html = $this->get_html();

		}

		return $this;

	}

	/**
	 * Set object variables
	 * @param    string     $key  variable name
	 * @param    mixed     $val  data
	 * @since    [version]
	 * @version  [version]
	 */
	public function set( $key, $val ) {

		global $wpdb;

		switch ( $key ) {

			case 'created':
			case 'id':
			case 'updated':
				return false;
			break;

			default:
				$this->$key = $val;
				if ( $this->id ) {
					return $wpdb->query( $wpdb->prepare(
						"UPDATE {$this->get_table()} SET {$key} = %s, updated = %s WHERE id = %d", $val, current_time( 'mysql' ),
						$this->id
					) );
				}
				return true;
			break;

		}

	}

	/**
	 * Convert the notification to an array
	 * access to all properties and meta items will be made accessible
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function toArray() {
		return get_object_vars( $this->load() );
	}

}
