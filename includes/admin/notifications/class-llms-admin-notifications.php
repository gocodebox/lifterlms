<?php
/**
 * LLMS_Admin_Notifications class file.
 *
 * @package LifterLMS/Admin/Notifications/Classes
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

			if ( ! $notification->check_conditions() ) {
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

}

LLMS_Admin_Notifications::instance();
