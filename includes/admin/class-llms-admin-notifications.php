<?php
/**
 * LLMS_Admin_Notifications class file.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Admin Notifications.
 *
 * @since [version]
 */
class LLMS_Admin_Notifications {

	use LLMS_REST_Trait_Singleton;

	/**
	 * Notifications URL.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	private string $url;

	/**
	 * Transient name.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	private string $name = 'llms_notificiations';

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		$this->url = $this->get_notifications_url();

		add_action( 'current_screen', array( $this, 'add_notifications' ) );
	}

	/**
	 * Conditionally displays notifications.
	 *
	 * @since [version]
	 *
	 * @param WP_Screen $current_screen WP_Screen instance.
	 * @return void
	 */
	public function add_notifications( WP_Screen $current_screen ): void {

		if ( ! str_contains( $current_screen->base, 'llms' ) && ! str_contains( $current_screen->id, 'llms' ) ) {
			return;
		}

		$notifications = $this->get_notifications( true );

		if ( ! $notifications ) {
			return;
		}

		foreach ( $notifications as $notification ) {

			if ( ! $this->check_conditions( $notification ) ) {
				continue;
			}

			LLMS_Admin_Notices::add_notice(
				$notification->id,
				$notification->content,
				array(
					'dismissible'      => $notification->dismissible,
					'dismiss_for_days' => 7,
					'flash'            => false,
					'html'             => '',
					'remind_in_days'   => 7,
					'remindable'       => false,
					'type'             => $notification->type,
					'icon'             => $notification->icon,
					'template'         => false,
					'template_path'    => '',
					'default_path'     => '',
					'priority'         => $notification->priority,
				)
			);
		}
	}

	/**
	 * Returns the filtered URL to the notifications JSON file.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_notifications_url(): string {

		/**
		 * Filter the notifications URL.
		 *
		 * @since [version]
		 *
		 * @param string $url The URL.
		 */
		$url = apply_filters(
			'llms_notifications_url',
			'https://lifter-telemetry.local/wp-content/cache/llms/notifications/notifications.json'
		);

		return esc_url( $url );
	}

	/**
	 * Returns array of all notifications.
	 *
	 * @since [version]
	 *
	 * @param bool $fetch Force a refresh of the notifications.
	 * @return LLMS_Admin_Notification[] Array of applicable notification objects.
	 */
	private function get_notifications( bool $fetch = false ): array {

		$notifications = get_transient( $this->name );

		if ( ! $notifications || $fetch ) {
			$notifications = $this->fetch_notifications();

			set_transient( $this->name, $notifications, DAY_IN_SECONDS );
		}

		/**
		 * Allows filtering of notifications.
		 *
		 * @since [version]
		 *
		 * @param array $notifications Array of notification objects.
		 */
		$filtered = apply_filters( $this->name, $notifications );

		$notifications = array();

		foreach ( $filtered as $notification ) {
			$notifications[] = new LLMS_Admin_Notification( $notification );
		}

		usort(
			$notifications,
			static function (
				LLMS_Admin_Notification $a,
				LLMS_Admin_Notification $b
			): bool {
				return $a->priority - $b->priority;
			}
		);

		return $notifications;

	}

	/**
	 * Fetches notifications from the server.
	 *
	 * @since [version]
	 *
	 * @return array Array of notification objects.
	 */
	private function fetch_notifications(): array {

		$response = wp_remote_get(
			$this->get_notifications_url(),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, false ) ?? [];

	}

	/**
	 * Checks if a notification should be displayed.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Admin_Notification $notification Notification object.
	 * @return bool
	 */
	private function check_conditions( LLMS_Admin_Notification $notification ): bool {

		if ( ! $this->check_date_range( $notification ) ) {
			return false;
		}

		if ( ! $notification->conditions ) {
			return true;
		}

		foreach ( $notification->conditions as $condition ) {
			if ( ! $this->check_condition( (object) $condition ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if a single condition is met.
	 *
	 * @since [version]
	 *
	 * @param object $condition Condition object.
	 * @return bool
	 */
	private function check_condition( object $condition ): bool {
		$value = explode( ',', $condition->value );

		$callbacks = [
			'plugin_active'     => static fn( $value ) => is_plugin_active( $value ),
			'plugin_inactive'   => static fn( $value ) => ! is_plugin_active( $value ),
			'lifterlms_version' => static fn( $value, $operator ) => version_compare(
				llms()->version,
				$value,
				$operator
			),
			'lifterlms_license' => static fn( $value, $operator ) => version_compare(
				llms()->version,
				$value,
				$operator
			),
		];

		if ( ! isset( $callbacks[ $condition->type ] ) ) {
			return false;
		}

		return $callbacks[ $condition->type ]( $value, $condition->operator );

	}

	/**
	 * Checks if a notification is within date range.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Admin_Notification $notification Notification object.
	 * @return bool
	 */
	private function check_date_range( LLMS_Admin_Notification $notification ): bool {

		if ( ! $notification->start_date && ! $notification->end_date ) {
			return true;
		}

		$start = strtotime( $notification->start_date );
		$end   = strtotime( $notification->end_date );

		if ( ! $start && ! $end ) {
			return true;
		}

		$time = time();

		if ( $time < $start ) {
			return false;
		}

		if ( $time > $end ) {
			return false;
		}

		return true;
	}

}

LLMS_Admin_Notifications::instance();
