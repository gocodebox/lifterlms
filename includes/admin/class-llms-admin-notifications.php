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
		add_action( 'wp_ajax_llms_dismiss_notification', [ $this, 'dismiss_notification' ] );
		add_action( 'wp_ajax_llms_show_notification', [ $this, 'show_notification' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function admin_scripts() {
		$handle = 'llms-admin-notifications';

		llms()->assets->enqueue_script( $handle );

		wp_localize_script(
			$handle,
			'llmsAdminNotifications',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'llms_admin_notification_nonce' ),
				'paused'  => $this->is_paused() ? 'true' : 'false',
			]
		);
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
		if ( ! wp_verify_nonce( llms_filter_input( INPUT_POST, 'nonce' ), 'llms_admin_notification_nonce' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'lifterlms' ) );
		}

		$id = llms_filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid notification ID.', 'lifterlms' ) );
		}

		$current_user = get_current_user_id();

		if ( ! $current_user ) {
			wp_send_json_error( __( 'Invalid user.', 'lifterlms' ) );
		}

		$archived        = $this->get_archived_notifications();
		$archived[ $id ] = date_i18n( 'c' );

		update_user_meta( $current_user, 'llms_archived_notifications', $archived );

		wp_send_json_success();
	}

	/**
	 * AJAX callback to check and display notifications.
	 *
	 * Checks permissions, retrieves the next notification, checks if notifications are paused,
	 * and finally, outputs the next notification if one is available.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function show_notification(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to view notifications.', 'lifterlms' ) );
		}

		$nonce = llms_filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'llms_admin_notification_nonce' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'lifterlms' ) );
		}

		$notification = $this->get_next_notification();

		if ( null === $notification ) {
			wp_send_json_error( __( 'No notifications available.', 'lifterlms' ) );
		}

		$html = $this->display_notification( $notification );

		if ( ! $html ) {
			wp_send_json_error( __( 'An error occurred while displaying the notification.', 'lifterlms' ) );
		} else {

			$html = str_replace(
				array( "\n", "\r", "\t" ),
				array( '', '', '' ),
				$html
			);

			wp_send_json_success( $html );
		}
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
		$debug_id      = $this->get_debug_id();

		if ( ! $notifications || $debug_id ) {
			$notifications = $this->fetch_notifications();

			set_transient( $name, $notifications, DAY_IN_SECONDS );
		}

		foreach ( $notifications as $key => $notification ) {
			if ( $debug_id && $notification->id === $debug_id ) {
				return [ $notification ];
			}

			if ( ! $this->is_applicable( $notification ) ) {
				unset( $notifications[ $key ] );
			}

			$allowed_html = array (
				'a'      => array (
					'class'  => array(),
					'href'   => array(),
					'target' => array(),
					'title'  => array(),
				),
				'button' => array (
					'class'  => array(),
				),
				'div'    => array(
					'class' => array(),
				),
				'p'      => array(
					'class' => array(),
				),
				'b'      => array(
					'class' => array(),
				),
				'em'     => array(
					'class' => array(),
				),
				'br'     => array(),
				'strike' => array(),
				'strong' => array(),
			);

			// Map properties.
			$notification->html        = wp_kses( $notification->content ?? '', $allowed_html );
			$notification->icon        = $notification->icon ?? $notification->dashicon ?? 'lifterlms';
			$notification->dismiss_url = '';

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

		$max_priority = (int) get_option( 'lifterlms_max_notifications_priority', 5 );

		foreach ( $notifications as $key => $notification ) {
			$priority = (int) $notification->priority ?? 5;

			if ( $priority > 5 ) {
				$notification->priority = 5;
			}

			if ( $priority > $max_priority ) {
				unset( $notifications[ $key ] );
			}
		}

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
	 * @return string
	 */
	private function display_notification( object $notification ): string {
		ob_start();

		llms_get_template( 'admin/notices/notice.php', (array) $notification );

		return ob_get_clean();
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
