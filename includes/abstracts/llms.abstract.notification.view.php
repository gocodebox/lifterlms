<?php
/**
 * Notification View Abstract
 *
 * @since 3.8.0
 * @version 3.31.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Notification_View abstract.
 *
 * @since 3.8.0
 * @since 3.30.3 Explicitly define undefined properties.
 * @since 3.31.0 Add filter on `$basic_options` class property.
 */
abstract class LLMS_Abstract_Notification_View extends LLMS_Abstract_Options_Data {

	/**
	 * Settings for basic notifications
	 *
	 * @var  array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it
		 */
		'auto_dismiss' => 0,
		/**
		 * Enables manual dismissal of notifications
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
	 * @var  LLMS_Post_Model
	 */
	protected $post;

	/**
	 * Supported fields for notification types
	 *
	 * @var  array
	 */
	protected $supported_fields = array();

	/**
	 * Notification Trigger ID
	 *
	 * @var  [type]
	 */
	public $trigger_id;

	/**
	 * Instance of the current LLMS_Notification
	 *
	 * @var  LLMS_Notification
	 */
	protected $notification;

	/**
	 * Instance of LLMS_Student for the subscriber
	 *
	 * @var  LLMS_Student
	 */
	protected $subscriber;

	/**
	 * Instance of an LLMS_Student for the triggering user
	 *
	 * @var  LLMS_Student
	 */
	protected $user;

	/**
	 * Replace merge codes with actual values
	 *
	 * @param    string $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_merge_data( $code );

	/**
	 * Setup body content for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_body();

	/**
	 * Setup footer content for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_footer();

	/**
	 * Setup notification icon for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_icon();

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_merge_codes();

	/**
	 * Setup notification subject line for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_subject();

	/**
	 * Setup notification title for output
	 * On an email the title acts as the "heading" element
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function set_title();

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 * @since 3.31.0 Add filter on `$basic_options` class class property.
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
		$this->post       = llms_get_post( $this->notification->get( 'post_id' ), 'post' );

		$this->basic_options = apply_filters( $this->get_filter( 'basic_options' ), $this->basic_options, $this );

	}

	/**
	 * Get the html for a basic notification
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function get_basic_html() {

		// setup html classes
		$classes = array(
			'llms-notification',
		);

		// setup html attributes
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

		// get variables
		$title  = $this->get_title();
		$icon   = ( 'yes' === $this->get_option( 'icon_hide', 'no' ) ) ? '' : $this->get_icon_src();
		$body   = $this->get_body();
		$footer = $this->get_footer();

		ob_start();
		llms_get_template(
			'notifications/basic.php',
			array(
				'atts'        => $atts,
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
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * @param    string $date    created or updated
	 * @param    string $format  valid PHP date format, defaults to WP date format options
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_date( $date = 'created', $format = null ) {

		if ( ! $format ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return date_i18n( $format, strtotime( $this->notification->get( $date ) ) );

	}

	/**
	 * Get relative or absolute date
	 * Returns relative if relative date is less than $max_days
	 * otherwise returns the absolute date
	 *
	 * @param    integer $max_days  max age of notification to display relative date for
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * @param    string $date  created or updated
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_date_relative( $date = 'created' ) {
		return llms_get_date_diff( current_time( 'timestamp' ), $this->get_date( $date, 'U' ), 1 );
	}

	/**
	 * Get the html for an email notification
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.28.2
	 */
	private function get_email_html() {
		return apply_filters( $this->get_filter( 'get_email_html' ), $this->get_body(), $this );
	}

	/**
	 * Get a filter hook string prefixed for the current view
	 *
	 * @param    string $hook   hook name
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_filter( $hook ) {
		return 'llms_notification_view' . $this->trigger_id . '_' . $hook;
	}

	/**
	 * Get an array of field-related options to be add to the notifications view config page on the admin panel
	 *
	 * @param    [type] $type  [description]
	 * @return   [type]            [description]
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_footer() {
		return apply_filters( $this->get_filter( 'get_footer' ), $this->set_footer(), $this );
	}

	/**
	 * Retrieve the full HTML to be output for the notification type
	 *
	 * @return   string|WP_Error        if the notification type is not supported, returns an error
	 * @since    3.8.0
	 * @version  3.8.0
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

			// 3rd party/custom types
			default:
				$html = apply_filters( $this->get_filter( 'get_' . $type . '_html' ), $html, $this );

		}

		return apply_filters( $this->get_filter( 'get_html' ), $html, $this );

	}

	/**
	 * Retrieve the icon id for the notification
	 * Returns an attachment id for the image
	 *
	 * @return   mixed
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_icon() {
		$icon = $this->get_option( 'icon', apply_filters( $this->get_filter( 'set_icon' ), $this->set_icon(), $this ) );
		return apply_filters( $this->get_filter( 'get_icon' ), $icon, $this );
	}

	/**
	 * Retrieve a default icon for the notification based on the notification type
	 *
	 * @param    string $type  type of icon [positive|negative]
	 * @return   string
	 * @since    3.8.0
	 * @version  3.10.0
	 */
	public function get_icon_default( $type ) {
		if ( ! in_array( $type, array( 'negative', 'positive', 'warning' ) ) ) {
			$ret = '';
		} else {
			$ret = LLMS()->plugin_url() . '/assets/images/notifications/icon-' . $type . '.png';
		}
		return apply_filters( 'llms_notification_get_icon_default', $ret, $type, $this );
	}

	/**
	 * Retrieve the icon src for the notification
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * Get available merge codes for the current notification
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.11.0
	 */
	public function get_merge_codes() {
		$codes = array_merge( $this->get_merge_code_defaults(), $this->set_merge_codes() );
		asort( $codes );
		return apply_filters( $this->get_filter( 'get_merge_codes' ), $codes, $this );
	}

	/**
	 * Get default merge codes available to all notifications of a given type
	 *
	 * @return   array
	 * @since    3.11.0
	 * @version  3.11.0
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
	 * Merge a string
	 *
	 * @param    string $string  an unmerged string
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function get_merged_string( $string ) {

		// only merge if there's codes in the string
		if ( false !== strpos( $string, '{{' ) ) {

			foreach ( array_keys( $this->get_merge_codes() ) as $code ) {

				// set defaults
				if ( in_array( $code, array_keys( $this->get_merge_code_defaults() ) ) ) {

					$func = 'set_merge_data_default';

					// set customs with extended class func
				} else {

					$func = 'set_merge_data';

				}

				$string = str_replace( $code, $this->$func( $code ), $string );

			}
		}

		return apply_filters( $this->get_filter( 'get_merged_string' ), $this->sentence_case( $string ), $this );

	}

	/**
	 * Access the protected notification object
	 *
	 * @return   LLMS_Notification
	 * @since    3.18.2
	 * @version  3.18.2
	 */
	public function get_notification() {
		return $this->notification;
	}

	/**
	 * Retrieve a prefix for options related to the notification
	 * This overrides the LLMS_Abstract_Options_Data method
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_option_prefix() {
		return sprintf( '%1$snotification_%2$s_%3$s_', $this->option_prefix, $this->trigger_id, $this->notification->get( 'type' ) );
	}

	/**
	 * Retrieve the subject for the notification (if supported)
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_supported_fields() {
		return apply_filters( $this->get_filter( 'get_supported_fields' ), $this->set_supported_fields(), $this );
	}

	/**
	 * Retrieve the title for the notification
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * @param    string $type   notification type [email|basic]
	 * @param    string $field  field id
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function is_for_self() {
		return ( $this->subscriber->get_id() === $this->user->get_id() );
	}

	/**
	 * Convert a string to sentence case.
	 * Useful for handling lowercased merged data like "you" which may appear at the beginning or middle of a sentence
	 *
	 * @param    string $string  a string
	 * @return   string
	 * @since    3.8.0
	 * @version  3.24.0
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
	 * @param    string $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	protected function set_merge_data_default( $code ) {

		$mailer = LLMS()->mailer();

		switch ( $code ) {

			case '{{DIVIDER}}':
				$code = $mailer->get_divider_html();
				break;

		}

		return $code;

	}

	/**
	 * Define field support for the view
	 * Extending classes can override this
	 * 3rd parties should filter $this->get_supported_fields()
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
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
