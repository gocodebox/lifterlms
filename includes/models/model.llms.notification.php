<?php
/**
 * LLMS_Notification class file
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Notification model class.
 *
 * Used for notification CRUD and Display.
 *
 * @since 3.8.0
 */
class LLMS_Notification implements JsonSerializable {

	/**
	 * Notification ID
	 *
	 * @var  int
	 */
	public $id;

	/**********************************************************
	 *
	 * Default Properties
	 **********************************************************/

	/**
	 * Created Date
	 *
	 * @var  string (DATETIME)
	 */
	private $created;

	/**
	 * Updated Date
	 *
	 * @var  string (DATETIME)
	 */
	private $updated;

	/**
	 * Current Status
	 * Options vary based on notification type
	 *
	 * @var  string
	 */
	private $status;

	/**
	 * Type of Notification
	 * basic, email, sms, etc...
	 *
	 * @var  string
	 */
	private $type;

	/**
	 * Subscriber Identifier
	 * WP User ID, email address (for cc,bcc), phone number, etc...
	 *
	 * @var  mixed
	 */
	private $subscriber;

	/**
	 * Trigger ID for the notification
	 * lesson_complete, course_complete, etc...
	 *
	 * @var  string
	 */
	private $trigger_id;

	/**
	 * WP User ID of the user who triggered the notification to be generated
	 * NOT to be confused with $subscriber and can be different than the subscriber
	 *
	 * @var  int
	 */
	private $user_id;

	/**
	 * WP Post ID of the post which triggered the notification to be generated
	 *
	 * @var  int
	 */
	private $post_id;

	/**********************************************************
	 *
	 * View Related Properties
	 **********************************************************/
	/**
	 * Merged HTML for the notification
	 * used for displaying a notification view
	 *
	 * @var string
	 */
	private $html;

	/**
	 * Constructor
	 *
	 * @param    int $notification  Notification ID
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function __construct( $notification = null ) {

		if ( is_numeric( $notification ) ) {
			$this->id = $notification;
		}

	}

	/**
	 * Get notification properties
	 *
	 * @param    string $key  key to retrieve
	 * @return   mixed
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function __get( $key ) {
		return $this->get( $key, false );
	}

	/**
	 * Create a new notification in the database
	 *
	 * @param    array $data  notification data
	 * @return   int|false         new notification id on success, false otherwise
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function create( $data = array() ) {

		$time = current_time( 'mysql' );

		$data = wp_parse_args(
			$data,
			array(

				'created'    => $time,
				'post_id'    => null,
				'status'     => 'new',
				'subscriber' => null,
				'trigger_id' => null,
				'type'       => '',
				'updated'    => $time,
				'user_id'    => null,

			)
		);

		ksort( $data ); // Maintain alpha sort you savages.

		$format = array(
			'%s', // For created.
			'%d', // For post_id.
			'%s', // For status.
			'%s', // For subscriber.
			'%s', // For trigger_id.
			'%s', // For type.
			'%s', // For updated.
			'%d', // For user_id.
		);

		global $wpdb;

		if ( 1 !== $wpdb->insert( $this->get_table(), $data, $format ) ) {
			return false;
		}

		$this->id = $wpdb->insert_id;

		return $this->id;

	}

	/**
	 * Determine if the triggering user is the subscriber
	 *
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function is_subscriber_self() {
		return ( $this->get( 'subscriber' ) == $this->get( 'user_id' ) );
	}

	/**
	 * Get notification properties
	 *
	 * @param    string $key  key to retrieve
	 * @return   mixed
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get( $key, $skip_cache = false ) {

		// Id will always be accessed from the object.
		if ( 'id' === $key ) {
			return $this->id;
		}

		// Return cached values if they exist.
		if ( ! is_null( $this->$key ) && ! $skip_cache ) {
			return $this->$key;
		}

		// get the value from the database.
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_var( $wpdb->prepare( "SELECT {$key} FROM {$this->get_table()} WHERE id = %d", $this->id ) );  // db call ok; no-cache ok.

	}

	/**
	 * Retrieve the HTML for the current notification
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_html() {
		$view = $this->get_view();
		if ( $view ) {
			return $view->get_html();
		}
		return '';
	}

	/**
	 * Get the table name for notification data
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function get_table() {
		global $wpdb;
		return $wpdb->prefix . 'lifterlms_notifications';
	}

	/**
	 * Retrieve an instance of the notification view class for the notification
	 *
	 * @return   LLMS_Abstract_Notification_View|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_view() {
		return llms()->notifications()->get_view( $this );
	}

	/**
	 * Called when converting a notification to JSON
	 *
	 * @since 3.8.0
	 *
	 * @todo The `mixed` return type declared by the parent method, which should be defined here as well,
	 *       is not available until PHP 8.0. Once support is dropped for 7.4 we can add the return type declaration
	 *       and remove the `#[ReturnTypeWillChange]` attribute. This *must* happen before the release of PHP 9.0.
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Load all notification data into the instance.
	 *
	 * @since 3.8.0
	 * @since 7.1.0 Catch possible fatals while generating the notification HTML and log them.
	 *
	 * @return LLMS_Notification
	 */
	public function load() {

		global $wpdb;

		$notification = $wpdb->get_row(
			$wpdb->prepare( "SELECT created, updated, status, type, subscriber, trigger_id, user_id, post_id FROM {$this->get_table()} WHERE id = %d", $this->id ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		); // db call ok; no-cache ok.

		if ( $notification ) {

			foreach ( $notification as $key => $val ) {
				$this->$key = $val;
			}

			try {
				$this->html = $this->get_html();
			} catch ( Error $e ) {
				llms_log( sprintf( 'Error generating the HTML for the notification ID #%d', $this->id ) );
				llms_log( sprintf( 'Error caught %1$s in %2$s on line %3$s', $e->getMessage(), $e->getFile(), $e->getLine() ) );
				$this->set( 'status', 'error' );
			}
		}

		return $this;

	}

	/**
	 * Set object variables
	 *
	 * @since    3.8.0
	 *
	 * @param    string $key  variable name
	 * @param    mixed  $val  data
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
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					return $wpdb->query(
						$wpdb->prepare(
							"UPDATE {$this->get_table()} SET {$key} = %s, updated = %s WHERE id = %d",
							$val,
							current_time( 'mysql' ),
							$this->id
						)
					); // db call ok; no-cache ok.
					// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				}
				return true;
			break;

		}

	}

	/**
	 * Convert the notification to an array
	 * access to all properties and meta items will be made accessible
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function toArray() {
		return get_object_vars( $this->load() );
	}

}
