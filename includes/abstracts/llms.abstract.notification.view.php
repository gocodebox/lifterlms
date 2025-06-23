<?php
/**
 * Notification View Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.8.0
 * @version 6.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Notification_View class.
 *
 * @since 3.8.0
 * @since 3.30.3 Explicitly define undefined properties.
 * @since 3.31.0 Add filter on `$basic_options` class property.
 * @since 3.37.19 Introduced the method `get_object()`. It'll allow extending classes
 *                 defining the way the object associated to the notification should be retrieved.
 *                 Use `in_array` with strict comparison where possible.
 */
abstract class LLMS_Abstract_Notification_View extends LLMS_Abstract_Options_Data {

	/**
	 * Settings for basic notifications
	 *
	 * @var array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it.
		 */
		'auto_dismiss' => 0,
		/**
		 * Enables manual dismissal of notifications.
		 */
		'dismissible'  => false,
	);

	/**
	 * @var string
	 * @since 3.8.0
	 */
	public $id;

	/**
	 * Instance of the LLMS_Post_Model for the triggering post
	 *
	 * @var LLMS_Post_Model
	 */
	protected $post;

	/**
	 * Supported fields for notification types
	 *
	 * @var array
	 */
	protected $supported_fields = array();

	/**
	 * Notification Trigger ID
	 *
	 * @var int
	 */
	public $trigger_id;

	/**
	 * Instance of the current LLMS_Notification
	 *
	 * @var LLMS_Notification
	 */
	protected $notification;

	/**
	 * Instance of LLMS_Student for the subscriber
	 *
	 * @var LLMS_Student
	 */
	protected $subscriber;

	/**
	 * Instance of an LLMS_Student for the triggering user
	 *
	 * @var LLMS_Student
	 */
	protected $user;

	/**
	 * Merge codes.
	 *
	 * @var string[]
	 */
	protected $merge_codes;

	/**
	 * Replace merge codes with actual values
	 *
	 * @since 3.8.0
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	abstract protected function set_merge_data( $code );

	/**
	 * Setup body content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	abstract protected function set_body();

	/**
	 * Setup footer content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	abstract protected function set_footer();

	/**
	 * Setup notification icon for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	abstract protected function set_icon();

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	abstract protected function set_merge_codes();

	/**
	 * Setup notification subject line for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	abstract protected function set_subject();

	/**
	 * Setup notification title for output
	 *
	 * On an email the title acts as the "heading" element.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	abstract protected function set_title();

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 * @since 3.31.0 Add filter on `$basic_options` class class property.
	 * @since 3.37.19 Moved the retrieval of the associated llms post into a protected method.
	 * @since 5.0.0 Force [llms-user] shortocde to the user ID of the user who triggered the notification.
	 *
	 * @param mixed $notification Notification id, instance of LLMS_Notification
	 *                            or an object containing at least an 'id'.
	 * @return void
	 */
	public function __construct( $notification ) {

		if ( is_numeric( $notification ) ) {
			$this->id           = $notification;
			$this->notification = new LLMS_Notification( $notification );
		} elseif ( is_a( $notification, 'LLMS_Notification' ) ) {
			$this->id           = $notification->get( 'id' );
			$this->notification = $notification;
		} elseif ( is_object( $notification ) && isset( $notification->id ) ) {
			$this->id           = $notification->id;
			$this->notification = new LLMS_Notification( $notification->id );
		}

		$this->subscriber = new LLMS_Student( $this->notification->get( 'subscriber' ) );
		$this->user       = new LLMS_Student( $this->notification->get( 'user_id' ) );
		$this->post       = $this->get_object();

		$this->basic_options = apply_filters( $this->get_filter( 'basic_options' ), $this->basic_options, $this );

		add_filter( 'llms_user_info_shortcode_user_id', array( $this, 'set_shortcode_user' ) );
	}

	/**
	 * Destructor
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function __destruct() {
		remove_filter( 'llms_user_info_shortcode_user_id', array( $this, 'set_shortcode_user' ) );
	}

	/**
	 * Set the user ID used by [llms-user] to the user triggering the notification.
	 *
	 * @since 5.0.0
	 *
	 * @param int $uid WP_User ID of the current user.
	 * @return int
	 */
	public function set_shortcode_user( $uid ) {
		return $this->user->get( 'id' );
	}

	/**
	 * Get the object associated to the notification
	 *
	 * @since 3.37.19
	 *
	 * @return object
	 */
	protected function get_object() {
		return llms_get_post( $this->notification->get( 'post_id' ), 'post' );
	}

	/**
	 * Get the html for a basic notification
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	private function get_basic_html() {

		// Setup html classes.
		$classes = array(
			'llms-notification',
		);

		// Setup html attributes.
		$attributes = array(
			'id'      => $this->id,
			'trigger' => $this->trigger_id,
			'type'    => $this->notification->get( 'type' ),
		);

		if ( $this->basic_options['dismissible'] ) {
			$classes[] = 'is-dismissible';
		}
		if ( $this->basic_options['auto_dismiss'] ) {
			$classes[]                  = 'auto-dismiss';
			$attributes['auto-dismiss'] = $this->basic_options['auto_dismiss'];
		}

		$atts = '';
		foreach ( $attributes as $att => $val ) {
			$atts .= sprintf( ' data-%1$s="%2$s"', $att, $val );
		}

		// Get variables.
		$title  = $this->get_title();
		$icon   = ( 'yes' === $this->get_option( 'icon_hide', 'no' ) ) ? '' : $this->get_icon_src();
		$body   = $this->get_body();
		$footer = $this->get_footer();

		ob_start();
		llms_get_template(
			'notifications/basic.php',
			array(
				'atts'        => $atts,
				'attributes'  => $attributes,
				'body'        => $body,
				'classes'     => implode( ' ', $classes ),
				'date'        => $this->get_date_display( 5 ),
				'dismissible' => $this->basic_options['dismissible'],
				'footer'      => $footer,
				'icon'        => $icon,
				'id'          => $this->id,
				'status'      => $this->notification->get( 'status' ),
				'title'       => $title,
			)
		);
		$html = trim( preg_replace( '/\s+/S', ' ', ob_get_clean() ) );

		return apply_filters( $this->get_filter( 'get_basic_html' ), $html, $this );
	}

	/**
	 * Retrieve the body for the notification
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_body( $merge = true ) {
		$body = $this->get_option( 'body', apply_filters( $this->get_filter( 'set_body' ), $this->set_body(), $this ) );
		if ( $merge ) {
			$body = $this->get_merged_string( $body );
		}
		return apply_filters( $this->get_filter( 'get_body' ), wpautop( $body ), $this );
	}

	/**
	 * Retrieve a formatted date
	 *
	 * @since 3.8.0
	 *
	 * @param string $date   Created or updated.
	 * @param string $format Valid PHP date format, defaults to WP date format options.
	 * @return string
	 */
	public function get_date( $date = 'created', $format = null ) {

		if ( ! $format ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return date_i18n( $format, strtotime( $this->notification->get( $date ) ) );
	}

	/**
	 * Get relative or absolute date
	 *
	 * Returns relative if relative date is less than $max_days
	 * otherwise returns the absolute date.
	 *
	 * @since 3.8.0
	 *
	 * @param int $max_days Max age of notification to display relative date for.
	 * @return string
	 */
	public function get_date_display( $max_days = 5 ) {

		$now     = current_time( 'timestamp' );
		$created = $this->get_date( 'created', 'U' );

		if ( ( $now - $created ) <= ( $max_days * DAY_IN_SECONDS ) ) {

			return sprintf( _x( 'About %s ago', 'relative date display', 'lifterlms' ), $this->get_date_relative( 'created' ) );

		}

		return $this->get_date( 'created' );
	}

	/**
	 * Retrieve a date relative to the current time
	 *
	 * @since 3.8.0
	 *
	 * @param string $date Created or updated.
	 * @return string
	 */
	public function get_date_relative( $date = 'created' ) {
		return llms_get_date_diff( current_time( 'timestamp' ), $this->get_date( $date, 'U' ), 1 );
	}

	/**
	 * Get the html for an email notification
	 *
	 * @since 3.28.2 Unknown.
	 *
	 * @return string
	 * @since 3.8.0
	 */
	private function get_email_html() {
		return apply_filters( $this->get_filter( 'get_email_html' ), $this->get_body(), $this );
	}

	/**
	 * Get a filter hook string prefixed for the current view
	 *
	 * @since 3.8.0
	 *
	 * @param string $hook Hook name.
	 * @return string
	 */
	protected function get_filter( $hook ) {
		return 'llms_notification_view' . $this->trigger_id . '_' . $hook;
	}

	/**
	 * Get an array of field-related options to be add to the notifications view config page on the admin panel
	 *
	 * @since 3.8.0
	 *
	 * @param string $type Type of the field.
	 * @return array
	 */
	public function get_field_options( $type ) {

		$options = array();

		if ( $this->has_field_support( $type, 'subject' ) ) {
			$options[] = array(
				'after_html' => llms_merge_code_button( '#' . $this->get_option_name( 'subject' ), false, $this->get_merge_codes() ),
				'id'         => $this->get_option_name( 'subject' ),
				'title'      => __( 'Subject', 'lifterlms' ),
				'type'       => 'text',
				'value'      => $this->get_subject( false ),
			);
		}

		if ( $this->has_field_support( $type, 'title' ) ) {
			$options[] = array(
				'after_html' => llms_merge_code_button( '#' . $this->get_option_name( 'title' ), false, $this->get_merge_codes() ),
				'id'         => $this->get_option_name( 'title' ),
				'title'      => ( 'email' === $type ) ? __( 'Heading', 'lifterlms' ) : __( 'Title', 'lifterlms' ),
				'type'       => 'text',
				'value'      => $this->get_title( false ),
			);
		}

		if ( $this->has_field_support( $type, 'body' ) ) {
			$options[] = array(
				'editor_settings' => array(
					'teeny' => true,
				),
				'id'              => $this->get_option_name( 'body' ),
				'title'           => __( 'Body', 'lifterlms' ),
				'type'            => 'wpeditor',
				'value'           => $this->get_body( false ),
			);
		}

		if ( $this->has_field_support( $type, 'icon' ) ) {
			$options[] = array(
				'id'         => $this->get_option_name( 'icon' ),
				'image_size' => 'llms_notification_icon',
				'title'      => __( 'Icon', 'lifterlms' ),
				'type'       => 'image',
				'value'      => $this->get_icon(),
			);
			$options[] = array(
				'default'     => 'no',
				'description' => __( 'When checked the icon will not be displayed when showing this notification.', 'lifterlms' ),
				'id'          => $this->get_option_name( 'icon_hide' ),
				'title'       => __( 'Disable Icon', 'lifterlms' ),
				'type'        => 'checkbox',
			);
		}

		return apply_filters( $this->get_filter( 'get_field_options' ), $options, $this );
	}

	/**
	 * Retrieve the footer for the notification
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_footer() {
		return apply_filters( $this->get_filter( 'get_footer' ), $this->set_footer(), $this );
	}

	/**
	 * Retrieve the full HTML to be output for the notification type
	 *
	 * @since 3.8.0
	 * @since 4.16.0 Pass `null` to the 3rd-party filter.
	 *
	 * @return string|WP_Error If the notification type is not supported, returns an error.
	 */
	public function get_html() {

		$type = $this->notification->get( 'type' );

		switch ( $type ) {

			case 'email':
				$html = $this->get_email_html();
				break;

			case 'basic':
				$html = $this->get_basic_html();
				break;

			// 3rd party/custom types.
			default:
				$html = apply_filters( $this->get_filter( 'get_' . $type . '_html' ), null, $this );

		}

		return apply_filters( $this->get_filter( 'get_html' ), $html, $this );
	}

	/**
	 * Retrieve the icon id for the notification
	 *
	 * Returns an attachment id for the image.
	 *
	 * @since 3.8.0
	 *
	 * @return mixed
	 */
	public function get_icon() {
		$icon = $this->get_option( 'icon', apply_filters( $this->get_filter( 'set_icon' ), $this->set_icon(), $this ) );
		return apply_filters( $this->get_filter( 'get_icon' ), $icon, $this );
	}

	/**
	 * Retrieve a default icon for the notification based on the notification type
	 *
	 * @since 3.8.0
	 * @since 3.10.0 Unknown.
	 * @since 3.37.19 Use `in_array` with strict comparison.
	 *
	 * @param string $type Type of icon [positive|negative].
	 * @return string
	 */
	public function get_icon_default( $type ) {
		if ( ! in_array( $type, array( 'negative', 'positive', 'warning' ), true ) ) {
			$ret = '';
		} else {
			$ret = llms()->plugin_url() . '/assets/images/notifications/icon-' . $type . '.png';
		}
		return apply_filters( 'llms_notification_get_icon_default', $ret, $type, $this );
	}

	/**
	 * Retrieve the icon src for the notification
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_icon_src() {
		$src = '';
		$val = $this->get_icon();
		if ( is_numeric( $val ) ) {
			$src = wp_get_attachment_image_src( $val, 'llms_notification_icon' );
			if ( is_array( $src ) ) {
				$src = $src[0];
			}
		} else {
			$src = $val;
		}
		return apply_filters( $this->get_filter( 'get_icon_src' ), $src, $this );
	}

	/**
	 * Get available merge codes for the current notification.
	 *
	 * @since 3.8.0
	 * @since 3.11.0 Unknown.
	 * @since 6.4.0 Cache merge codes.
	 *
	 * @return array
	 */
	public function get_merge_codes() {

		if ( ! isset( $this->merge_codes ) ) {
			$codes = array_merge( $this->get_merge_code_defaults(), $this->set_merge_codes() );
			asort( $codes );
			$this->merge_codes = $codes;
		}

		return apply_filters( $this->get_filter( 'get_merge_codes' ), $this->merge_codes, $this );
	}

	/**
	 * Get default merge codes available to all notifications of a given type
	 *
	 * @since 3.11.0
	 *
	 * @return array
	 */
	protected function get_merge_code_defaults() {

		switch ( $this->notification->get( 'type' ) ) {

			case 'email':
				$codes = array(
					'{{DIVIDER}}' => __( 'Divider Line', 'lifterlms' ),
				);
				break;

			default:
				$codes = array();
		}

		return $codes;
	}

	/**
	 * Merge a string.
	 *
	 * @since 3.8.0
	 * @since 3.37.19 Use `in_array` with strict comparison.
	 * @since 6.4.0 Only populate effectively used merged data.
	 *
	 * @param string $string An unmerged string.
	 * @return string
	 */
	private function get_merged_string( $string ) {

		// Only merge if there are codes in the string.
		if ( false !== strpos( $string, '{{' ) ) {

			$merge_code_defaults = $this->get_merge_code_defaults();

			foreach ( $this->get_used_merge_codes( $string ) as $code ) {

				// Set defaults.
				if ( array_key_exists( $code, $merge_code_defaults ) ) {

					$func = 'set_merge_data_default';

					// Set customs with extended class func.
				} else {

					$func = 'set_merge_data';

				}

				$string = str_replace( $code, $this->$func( $code ), $string );

			}
		}

		return apply_filters( $this->get_filter( 'get_merged_string' ), $this->sentence_case( $string ), $this );
	}

	/**
	 * Retrieve merge codes used in a given string.
	 *
	 * @since 6.4.0
	 *
	 * @param string $string Text string whereto look for merge codes.
	 * @return array Returns a list of merge codes actually used in the passed string.
	 */
	private function get_used_merge_codes( $string ) {

		return array_keys(
			array_filter(
				$this->get_merge_codes(),
				function ( $code ) use ( $string ) {
					return false !== strpos( $string, $code );
				},
				ARRAY_FILTER_USE_KEY
			)
		);
	}

	/**
	 * Access the protected notification object
	 *
	 * @since 3.18.2
	 *
	 * @return LLMS_Notification
	 */
	public function get_notification() {
		return $this->notification;
	}

	/**
	 * Retrieve a prefix for options related to the notification
	 *
	 * This overrides the LLMS_Abstract_Options_Data method.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function get_option_prefix() {
		return sprintf( '%1$snotification_%2$s_%3$s_', $this->option_prefix, $this->trigger_id, $this->notification->get( 'type' ) );
	}

	/**
	 * Retrieve the subject for the notification (if supported)
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_subject( $merge = true ) {
		$subject = $this->get_option( 'subject', apply_filters( $this->get_filter( 'set_subject' ), $this->set_subject(), $this ) );
		if ( $merge ) {
			$subject = $this->get_merged_string( $subject );
		}
		return apply_filters( $this->get_filter( 'get_subject' ), $subject, $this );
	}

	/**
	 * Get supported fields and allow filtering for 3rd parties
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public function get_supported_fields() {
		return apply_filters( $this->get_filter( 'get_supported_fields' ), $this->set_supported_fields(), $this );
	}

	/**
	 * Retrieve the title for the notification
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_title( $merge = true ) {
		$title = $this->get_option( 'title', apply_filters( $this->get_filter( 'set_title' ), $this->set_title(), $this ) );
		if ( $merge ) {
			$title = $this->get_merged_string( $title );
		}
		return apply_filters( $this->get_filter( 'get_title' ), $title, $this );
	}

	/**
	 * Determine if the current view supports a field by ID
	 *
	 * @since 3.8.0
	 *
	 * @param string $type  Notification type [email|basic].
	 * @param string $field Field id.
	 * @return bool
	 */
	protected function has_field_support( $type, $field ) {
		$fields = $this->get_supported_fields();
		if ( ! isset( $fields[ $type ] ) ) {
			return false;
		}
		$type_fields = $fields[ $type ];
		if ( ! isset( $type_fields[ $field ] ) ) {
			return false;
		}
		return $type_fields[ $field ];
	}

	/**
	 * Determine if the notification subscriber is the user who triggered the notification
	 *
	 * @since 3.8.0
	 *
	 * @return bool
	 */
	protected function is_for_self() {
		return ( $this->subscriber->get_id() === $this->user->get_id() );
	}

	/**
	 * Convert a string to sentence case
	 *
	 * Useful for handling lowercased merged data like "you" which may appear at the beginning or middle of a sentence.
	 *
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param string $string A string.
	 * @return string
	 */
	private function sentence_case( $string ) {

		$sentences  = preg_split( '/(\.|\?|\!)(\s|$)+/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		$new_string = '';
		foreach ( $sentences as $sentence ) {
			$new_string .= strlen( $sentence ) === 1 ? $sentence . ' ' : ucfirst( trim( $sentence ) );
		}

		return trim( $new_string );
	}

	/**
	 * Replace default merge codes with actual values
	 *
	 * @since 3.11.0
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data_default( $code ) {

		$mailer = llms()->mailer();

		switch ( $code ) {

			case '{{DIVIDER}}':
				$code = $mailer->get_divider_html();
				break;

		}

		return $code;
	}

	/**
	 * Define field support for the view
	 *
	 * Extending classes can override this
	 * 3rd parties should filter $this->get_supported_fields().
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function set_supported_fields() {
		return array(
			'basic' => array(
				'body'  => true,
				'title' => true,
				'icon'  => true,
			),
			'email' => array(
				'body'    => true,
				'subject' => true,
				'title'   => true,
			),
		);
	}
}
