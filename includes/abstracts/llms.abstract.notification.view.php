<?php
/**
 * Notification View Abstract
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Abstract_Notification_View extends LLMS_Abstract_Options_Data {

	/**
	 * Settings for basic notifications
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
		'dismissible' => false,
	);

	/**
	 * Instance of the LLMS_Post_Model for the triggering post
	 * @var  [type]
	 */
	protected $post;

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id;

	/**
	 * Instance of the current LLMS_Notification
	 * @var  obj
	 */
	protected $notification;

	/**
	 * Instance of LLMS_Student for the subscriber
	 * @var  [type]
	 */
	protected $subscriber;

	/**
	 * Instance of an LLMS_Student for the triggering user
	 * @var  [type]
	 */
	protected $user;

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_merge_data( $code );

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_body();

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_footer();

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_icon();

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_merge_codes();

	/**
	 * Setup notification subject line for outpet
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_subject();

	/**
	 * Setup notification title for output
	 * On an email the title acts as the "heading" element
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	abstract protected function set_title();

	/**
	 * Constructor
	 * @param    mixed     $notification  notification id, instance of LLMS_Notification
	 *                                    or an object containing at least an 'id'
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct( $notification ) {

		if ( is_numeric( $notification ) ) {
			$this->id = $notification;
			$this->notification = new LLMS_Notification( $notification );
		} elseif ( is_a( $notification, 'LLMS_Notification' ) ) {
			$this->id = $notification->get( 'id' );
			$this->notification = $notification;
		} elseif ( is_object( $notification ) && isset( $notification->id ) ) {
			$this->id = $notification->id;
			$this->notification = new LLMS_Notification( $notification->id );
		}

		$this->subscriber = new LLMS_Student( $this->notification->get( 'subscriber' ) );
		$this->user = new LLMS_Student( $this->notification->get( 'user_id' ) );
		$this->post = llms_get_post( $this->notification->get( 'post_id' ) );

	}

	/**
	 * Get the html for a basic notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_basic_html() {

		// setup html classes
		$classes = array(
			'llms-notification',
		);

		// setup html attributes
		$attributes = array(
			'id' => $this->id,
			'trigger' => $this->trigger_id,
			'type' => $this->notification->get( 'type' ),
		);

		if ( $this->basic_options['dismissible'] ) {
			$classes[] = 'is-dismissible';
		}
		if ( $this->basic_options['auto_dismiss'] ) {
			$classes[] = 'auto-dismiss';
			$attributes['auto-dismiss'] = $this->basic_options['auto_dismiss'];
		}

		$atts = '';
		foreach ( $attributes as $att => $val ) {
			$atts .= sprintf( ' data-%1$s="%2$s"', $att, $val );
		}

		// get variables
		$title = $this->get_title();
		$icon = $this->get_icon();
		$body = $this->get_body();
		$footer = $this->get_footer();

		ob_start();
		?>
			<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $atts; ?> id="llms-notification-<?php echo $this->id; ?>">

				<?php if ( $this->basic_options['dismissible'] ) : ?>
					<i class="llms-notification-dismiss fa fa-times-circle" aria-hidden="true"></i>
				<?php endif; ?>

				<section class="llms-notification-content">
					<div class="llms-notification-main">
						<h4 class="llms-notification-title"><?php echo $title; ?></h4>
						<div class="llms-notification-body"><?php echo $body; ?></div>
					</div>

					<?php if ( $icon ) : ?>
						<aside class="llms-notification-aside">
							<img class="llms-notification-icon" alt="<?php echo $title; ?>" src="<?php echo $icon; ?>">
						</aside>
					<?php endif; ?>
				</section>

				<?php if ( $footer ) : ?>
					<footer class="llms-notification-footer"><?php echo $footer; ?></footer>
				<?php endif; ?>

			</div>
		<?php

		$html = trim( preg_replace( '/\s+/S', ' ', ob_get_clean() ) );
		return apply_filters( $this->get_filter( 'get_basic_html' ), $html, $this );

	}

	/**
	 * Retrieve the body for the notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_body( $merge = true ) {
		$body = $this->get_option( 'body', $this->set_body() );
		if ( $merge ) {
			$body = $this->get_merged_string( $body );
		}
		return apply_filters( $this->get_filter( 'get_body' ), $body, $this );
	}

	/**
	 * Get the html for an email notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_email_html() {
		return apply_filters( $this->get_filter( 'get_basic_html' ), $this->get_body(), $this );
	}

	/**
	 * Get a filter hook string prefixed for the current view
	 * @param    string   $hook   hook name
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_filter( $hook ) {
		return 'llms_notification_view' . $this->trigger_id . '_' . $hook;
	}

	/**
	 * Get an array of field-related options to be add to the notifications view config page on the admin panel
	 * @param    [type]     $type  [description]
	 * @return   [type]            [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_field_options( $type ) {

		$options = array();

		if ( 'email' === $type ) {
			$options[] = array(
				'after_html' => llms_merge_code_button( '#' . $this->get_option_name( 'subject' ), false, $this->get_merge_codes() ),
				'id' => $this->get_option_name( 'subject' ),
				'title' => __( 'Subject', 'lifterlms' ),
				'type' => 'text',
				'value' => $this->get_subject( false ),
			);
		}

		$options[] = array(
			'after_html' => llms_merge_code_button( '#' . $this->get_option_name( 'title' ), false, $this->get_merge_codes() ),
			'id' => $this->get_option_name( 'title' ),
			'title' => ( 'email' === $type ) ? __( 'Heading', 'lifterlms' ) : __( 'Title', 'lifterlms' ),
			'type' => 'text',
			'value' => $this->get_title( false ),
		);

		$options[] = array(
			'editor_settings' => array(
				'teeny' => true,
			),
			'id' => $this->get_option_name( 'body' ),
			'title' => __( 'Body', 'lifterlms' ),
			'type' => 'wpeditor',
			'value' => $this->get_body( false ),
		);

		if ( 'basic' === $type ) {
			$options[] = array(
				'id' => $this->get_option_name( 'icon' ),
				'title' => __( 'Icon', 'lifterlms' ),
				'type' => 'text',
				'value' => $this->get_icon(),
			);
		}

		return apply_filters( $this->get_filter( 'get_field_options' ), $options, $this );
	}

	/**
	 * Retrieve the footer for the notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_footer() {
		return apply_filters( $this->get_filter( 'get_footer' ), $this->set_footer(), $this );
	}

	/**
	 * Retrieve the full HTML to be output for the notification type
	 * @return   string|WP_Error        if the notification type is not supported, returns an error
	 * @since    [version]
	 * @version  [version]
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
	 * Retrieve the icon for the notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_icon() {
		$icon = $this->get_option( 'icon', $this->set_icon() );
		return apply_filters( $this->get_filter( 'get_icon' ), $icon, $this );
	}

	/**
	 * Get available merge codes for the current notification
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_merge_codes() {
		return apply_filters( $this->get_filter( 'get_merge_codes' ), $this->set_merge_codes(), $this );
	}

	/**
	 * Merge a string
	 * @param    string     $string  an unmerged string
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_merged_string( $string ) {

		// only merge if there's codes in the string
		if ( false !== strpos( $string, '{{' ) ) {

			foreach ( array_keys( $this->get_merge_codes() ) as $code ) {
				$string = str_replace( $code, $this->set_merge_data( $code ), $string );
			}

		}

		return apply_filters( $this->get_filter( 'get_merged_string' ), $this->sentence_case( $string ), $this );

	}

	/**
	 * Retrieve a prefix for options related to the notification
	 * This overrides the LLMS_Abstract_Options_Data method
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_option_prefix() {
		return sprintf( '%1$snotification_%2$s_%3$s_', $this->option_prefix, $this->trigger_id, $this->notification->get( 'type' ) );
	}

	/**
	 * Retrieve the subject for the notification (if supported)
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_subject( $merge = true ) {
		$subject = $this->get_option( 'subject', $this->set_subject() );
		if ( $merge ) {
			$subject = $this->get_merged_string( $subject );
		}
		return apply_filters( $this->get_filter( 'get_subject' ), $subject, $this );
	}

	/**
	 * Retrieve an array of supported types for the notifications
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_supported_types() {
		return apply_filters( $this->get_filter( 'get_supported_types' ), $this->supports, $this );
	}

	/**
	 * Retrieve the title for the notification
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_title( $merge = true ) {
		$title = $this->get_option( 'title', $this->set_title() );
		if ( $merge ) {
			$title = $this->get_merged_string( $title );
		}
		return apply_filters( $this->get_filter( 'get_title' ), $title, $this );
	}

	/**
	 * Determine if the notification subscriber is the user who triggered the notification
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	protected function is_for_self() {
		return ( $this->subscriber->get_id() === $this->user->get_id() );
	}

	/**
	 * Convert a string to sentence case.
	 * Useful for handling lowercased merged data like "you" which may appear at the beginnig or middle of a sentence
	 * @param    string     $string  a string
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function sentence_case( $string ) {

		$sentences = preg_split( '/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		$new_string = '';
		foreach ( $sentences as $key => $sentence ) {

			$new_string .= ( $key & 1 ) == 0 ? ucfirst( trim( $sentence ) ) : $sentence . ' ';

		}

		return trim( $new_string );
	}

}
