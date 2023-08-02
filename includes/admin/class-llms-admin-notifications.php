<?php
/**
 * LLMS_Admin_Notifications class file.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Notifications.
 *
 * @since [version]
 */
class LLMS_Admin_Notifications {

	/**
	 * Notifications constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'current_screen', [ $this, 'show_notifications' ] );
		add_action( 'current_screen', [ $this, 'dismiss_notification' ], 11 );
	}

	/**
	 * AJAX callback to check and display notifications.
	 *
	 * Checks permissions, retrieves the next notification, checks if notifications are paused,
	 * and finally, outputs the next notification if one is available.
	 *
	 * @since [version]
	 *
	 * @param WP_Screen $current_screen WP_Screen instance.
	 * @return void
	 */
	public function show_notifications( WP_Screen $current_screen ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! str_contains( $current_screen->base, 'llms' ) && ! str_contains( $current_screen->id, 'llms' ) ) {
			return;
		}

		$notification = $this->get_next_notification();

		if ( null === $notification || $this->is_paused() ) {
			return;
		}

		$this->display_notification( $notification );
	}

	/**
	 * Handle dismissing notifications.
	 *
	 * Handles the request to archive a notification, updating the
	 * user meta with the new list of archived notifications.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function dismiss_notification(): void {
		if ( ! wp_verify_nonce( llms_filter_input( INPUT_GET, 'llms_admin_notification_nonce' ), 'llms_admin_notification_nonce' ) ) {
			return;
		}

		$id = llms_filter_input( INPUT_GET, 'llms_admin_notification_pause', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $id ) {
			return;
		}

		$current_user    = get_current_user_id();
		$archived        = $this->get_archived_notifications();
		$archived[ $id ] = date_i18n( 'c' );

		update_user_meta( $current_user, 'llms_archived_notifications', $archived );
	}

	/**
	 * Get the next applicable notification.
	 *
	 * @since [version]
	 *
	 * @return stdClass|null The next notification object or null if none found.
	 */
	private function get_next_notification(): ?stdClass {
		$notifications = $this->get_notifications();

		if ( empty( $notifications ) ) {
			return null;
		}

		$notifications = $this->filter_and_sort_notifications( $notifications );
		$debug_id      = $this->get_debug_id();

		if ( $debug_id ) {
			foreach ( $notifications as $notification ) {
				if ( $notification->id === $debug_id ) {
					return $notification;
				}
			}
		}

		return reset( $notifications ) ?: null;
	}

	/**
	 * Get all applicable notifications.
	 *
	 * @since [version]
	 *
	 * @return array Array of applicable notification objects.
	 */
	private function get_notifications(): array {
		$name          = 'llms_notificiations_' . llms()->version;
		$notifications = get_transient( $name );

		if ( ! $notifications || $this->get_debug_id() ) {
			$notifications = $this->fetch_notifications();
			set_transient( $name, $notifications, DAY_IN_SECONDS );
		}

		foreach ( $notifications as $key => $notification ) {
			if ( ! $this->is_applicable( $notification ) ) {
				unset( $notifications[ $key ] );
			}

			// Map properties.
			$notification->html        = wp_kses_post( $notification->content ?? '' );
			$notification->icon        = $notification->icon ?? $notification->dashicon ?? 'lifterlms';
			$notification->dismiss_url = wp_nonce_url(
				add_query_arg( 'llms_admin_notification_pause', $notification->id ),
				'llms_admin_notification_nonce',
				'llms_admin_notification_nonce'
			);

			if ( ( $notification->type ?? '' ) === 'general' ) {
				$notification->type = 'info';
			}

			$notifications[ $key ] = $notification;
		}

		return $notifications;
	}

	/**
	 * Fetch notifications from the server.
	 *
	 * @since [version]
	 *
	 * @return array Array of notification objects.
	 */
	private function fetch_notifications(): array {
		$url = 'https://notifications.paidmembershipspro.com/v2/notifications.json';
		$url = defined( 'LLMS_ADMIN_NOTIFICATIONS_URL' ) ? LLMS_ADMIN_NOTIFICATIONS_URL : $url;
		$url = apply_filters( 'llms_admin_notifications_url', $url );

		$response = wp_remote_get( esc_url( $url ) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [];
		}

		$body          = wp_remote_retrieve_body( $response );
		$notifications = json_decode( $body, false );

		return is_array( $notifications ) ? $notifications : [];
	}

	/**
	 * Filter out archived notifications and sort by priority.
	 *
	 * @since [version]
	 *
	 * @param array $notifications Array of notification objects.
	 * @return array Filtered and sorted array of notification objects.
	 */
	private function filter_and_sort_notifications( array $notifications ): array {
		if ( $this->get_debug_id() ) {
			return $notifications;
		}

		$archived = $this->get_archived_notifications();

		$notifications = array_filter(
			$notifications,
			static fn( stdClass $notification ): bool => ! isset( $archived[ $notification->id ] )
		);

		usort(
			$notifications,
			static fn( stdClass $a, stdClass $b ): bool => $a->priority - $b->priority
		);

		return $notifications;
	}

	/**
	 * Display the notification HTML.
	 *
	 * @since [version]
	 *
	 * @param object $notification The notification object.
	 * @return void
	 */
	private function display_notification( object $notification ): void {
		llms_get_template( 'admin/notices/notice.php', (array) $notification );
	}

	/**
	 * Check if notifications should be paused.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	private function is_paused(): bool {
		if ( $this->get_debug_id() ) {
			return false;
		}

		$archived       = $this->get_archived_notifications();
		$archived_count = count( $archived );

		if ( 0 === $archived_count ) {
			return false;
		}

		$last_notification_date = end( $archived );
		$now                    = time();
		$delay                  = defined( 'LLMS_ADMIN_NOTIFICATIONS_DELAY' ) ? LLMS_ADMIN_NOTIFICATIONS_DELAY : 12 * HOUR_IN_SECONDS;
		$delay                  = apply_filters( 'llms_admin_notifications_url', $delay );

		// Delay notifications for 12 hours after the last notification.
		if ( strtotime( $last_notification_date ) > ( $now - $delay ) ) {
			return true;
		}

		if ( $archived_count >= 3 && strtotime( $last_notification_date ) > ( $now - 7 * DAY_IN_SECONDS ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get the archived notifications for the current user.
	 *
	 * @since [version]
	 *
	 * @return array Array of archived notification IDs.
	 */
	private function get_archived_notifications(): array {
		$current_user_id = get_current_user_id();
		$archived        = get_user_meta( $current_user_id, 'llms_archived_notifications', true );

		return is_array( $archived ) ? $archived : [];
	}

	/**
	 * Check rules for a notification.
	 *
	 * @since [version]
	 *
	 * @param object $notification The notification object.
	 * @returns bool true if notification should be shown, false if not.
	 */
	private function is_applicable( object $notification ): bool {

		// If one is specified by URL parameter, it's allowed.
		if ( $this->get_debug_id() ) {
			return true;
		}

		// Hide if today's date is before notification start date.
		if ( date( 'Y-m-d', current_time( 'timestamp' ) ) < $notification->starts ) {
			return false;
		}

		// Hide if today's date is after end date.
		if ( date( 'Y-m-d', current_time( 'timestamp' ) ) > $notification->ends ) {
			return false;
		}

		// Check priority, e.g. if only security notifications should be shown.
		if ( $notification->priority > 5 ) {
			return false;
		}

		// Check show rules.
		if ( ! $this->should_show( $notification ) ) {
			return false;
		}

		// Check hide rules.
		if ( $this->should_hide( $notification ) ) {
			return false;
		}

		// If we get here, show it.
		return true;
	}

	/**
	 * Get the debug ID from the URL if set.
	 *
	 * @since [version]
	 *
	 * @return ?int The debug ID or false if not set.
	 */
	private function get_debug_id(): ?int {
		return (int) llms_filter_input( INPUT_GET, 'notification_id', FILTER_SANITIZE_NUMBER_INT ) ?? null;
	}

	/**
	 * Check a notification to see if we should show it
	 * based on the rules set.
	 * Shows if ALL rules are true. (AND)
	 *
	 * @since [version]
	 *
	 * @param object $notification The notification object.
	 * @return bool Whether the notification should be shown.
	 */
	private function should_show( object $notification ): bool {
		$show = true;

		if ( empty( $notification->show_if ) ) {
			return true;
		}

		foreach ( $notification->show_if as $callback => $args ) {
			if ( method_exists( $this, $callback ) ) {
				if ( ! $this->$callback( ...$args ) ) {
					$show = false;
					break;
				}
			}
		}

		return $show;
	}

	/**
	 * Check a notification to see if we should hide it
	 * based on the rules set.
	 *
	 * Hides if ANY rule is true (OR).
	 *
	 * @since [version]
	 *
	 * @param object $notification The notification object.
	 * @return bool Whether the notification should be hidden.
	 */
	private function should_hide( object $notification ): bool {
		$hide = false;

		if ( empty( $notification->hide_if ) ) {
			return false;
		}

		foreach ( $notification->hide_if as $callback => $args ) {
			if ( method_exists( $this, $callback ) ) {
				if ( $this->$callback( ...$args ) ) {
					$hide = true;
					break;
				}
			}
		}

		return $hide;
	}

	/**
	 * Checks if all plugins in array are active.
	 *
	 * First checks for short slug match, e.g. "lifterlms" before
	 * checking full plugin basename "lifterlms/lifterlms.php".
	 *
	 * @since [version]
	 *
	 * @param ...string $values Array of plugin slugs.
	 * @return bool
	 */
	private function plugins_active( ...$values ): bool {

		$plugins_active = false;

		foreach ( $values as $value ) {
			if ( $this->plugin_active( $value ) ) {
				$plugins_active = true;
			}
		}

		return $plugins_active;

	}

	/**
	 * Checks if a single plugin is active.
	 *
	 * @since [version]
	 *
	 * @param string $value Plugin slug.
	 * @return bool
	 */
	private function plugin_active( string $value ): bool {

		$active_plugins = get_option( 'active_plugins', array() );

		if ( in_array( $value, $active_plugins, true ) ) {
			return true;
		}

		foreach ( $active_plugins as $active_plugin ) {
			$without_php      = basename( $active_plugin, '.php' );
			$plugin_parts     = explode( '/', $active_plugin );
			$folder_name      = $plugin_parts[0];
			$file_name        = $plugin_parts[1];
			$file_without_php = basename( $file_name, '.php' );

			if ( $value === $without_php || $value === $folder_name || $value === $file_name || $value === $file_without_php ) {
				return true;
			}

		}

		return false;

	}

	/**
	 * Checks if all plugins in array are inactive.
	 *
	 * @since [version]
	 *
	 * @param string[] ...$values Array of plugin slugs.
	 * @return bool
	 */
	private function plugins_inactive( array ...$values ): bool {

		$plugins_inactive = false;

		foreach ( $values as $value ) {
			if ( $this->plugin_inactive( $value ) ) {
				$plugins_inactive = true;
			}
		}

		return $plugins_inactive;

	}

	/**
	 * Checks if a single plugin is inactive.
	 *
	 * @since [version]
	 *
	 * @param string $value Plugin slug.
	 * @return bool
	 */
	private function plugin_inactive( string $value ): bool {
		return ! $this->plugin_active( $value );
	}

	/**
	 * Checks LifterLMS version.
	 *
	 * @since [version]
	 *
	 * @param string[] ...$values Array of versions.
	 * @return bool
	 */
	private function llms_version( array ...$values ): bool {
		$operator = $values[0] ?? '===';
		$version  = $values[1] ?? false;

		return version_compare( llms()->version, $version, $operator );
	}
}

return new LLMS_Admin_Notifications();
