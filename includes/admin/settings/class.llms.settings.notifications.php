<?php
/**
 * Admin Settings: Notifications Tab
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 3.8.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Settings_Notifications class
 *
 * Admin Settings: Notifications Tab.
 *
 * @since 3.8.0
 * @since 3.30.3 Explicitly define class properties; fix typo in title element id.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Settings_Notifications extends LLMS_Settings_Page {

	/**
	 * @var LLMS_Abstract_Notification_View
	 *
	 * @since 3.8.0
	 */
	public $view;

	/**
	 * Constructor.
	 *
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->id    = 'notifications';
		$this->label = __( 'Notifications', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'before_save' ), 5 );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'after_save' ), 15 );
		add_filter( 'llms_settings_' . $this->id . '_has_save_button', array( $this, 'maybe_disable_save' ) );

	}

	/**
	 * Get a breadcrumb custom html for use on notification settings screens (not on the table)
	 *
	 * @since 3.8.0
	 *
	 * @param string $current_title The title of the current notification.
	 * @return array
	 */
	private function get_breadcrumbs( $current_title ) {
		return array(
			'id'    => 'notification_options_breadcrumbs',
			'type'  => 'custom-html',
			'value' => '<a href="' . esc_url( admin_url( 'admin.php?page=llms-settings&tab=notifications' ) ) . '">' . __( 'All Notifications', 'lifterlms' ) . '</a> <small>&gt;</small> <strong>' . $current_title . '</strong>',
		);
	}

	/**
	 * Get settings specific to the current notification type
	 *
	 * @since 3.8.0
	 * @since 3.24.0 Unknown.
	 * @since 5.2.0 Merge controller additional options.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param LLMS_Abstract_Notification_Controller $controller Instance of an LLMS_Abstract_Notification_Controller extending class.
	 * @return array
	 */
	private function get_notification_settings( $controller ) {

		$settings = array();

		// Setup vars.
		$type  = llms_filter_input_sanitize_string( INPUT_GET, 'type' );
		$types = $controller->get_supported_types();
		$title = $controller->get_title() . ' (' . $types[ $type ] . ')';
		$view  = $controller->get_mock_view( $type );

		// So the merge code button can use it.
		$this->view = $view;

		// Output the merge code button for the WYSIWYG editor.
		add_action( 'media_buttons', array( $this, 'merge_code_button' ) );

		// Add a breadcrumb on the top of the page.
		$settings[] = $this->get_breadcrumbs( $title );

		// Add field options for the view.
		$settings = array_merge( $settings, $view->get_field_options( $type ) );

		$subscribers = $controller->get_subscriber_options( $type );

		foreach ( $subscribers as $i => $data ) {

			$sub_settings = array(
				'default' => $data['enabled'],
				'desc'    => $data['title'],
				'id'      => sprintf( '%1$s[%2$s]', $controller->get_option_name( $type . '_subscribers' ), $data['id'] ),
				'type'    => 'checkbox',
			);

			if ( 0 === $i ) {
				$sub_settings['title']         = __( 'Subscribers', 'lifterlms' );
				$sub_settings['checkboxgroup'] = 'start';
			} elseif ( count( $subscribers ) - 1 === $i ) {
				$sub_settings['checkboxgroup'] = 'end';
			} else {
				$sub_settings['checkboxgroup'] = 'middle';
			}

			$settings[] = $sub_settings;

			if ( 'custom' === $data['id'] ) {
				$settings[] = array(
					'desc' => $data['description'],
					'id'   => $controller->get_option_name( $type . '_custom_subscribers' ),
					'type' => 'text',
				);
			}
		}

		// Add additional controller options.
		$settings = array_merge( $settings, $controller->get_additional_options( $type ) );

		if ( $controller->is_testable( $type ) ) {
			foreach ( $controller->get_test_settings( $type ) as $setting ) {
				$setting['id'] = 'llms_notification_test_data[' . $setting['id'] . ']';
				$settings[]    = $setting;
			}
		}

		return apply_filters( 'llms_notification_settings_' . $controller->id . '_' . $type, $settings, $controller, $view );

	}

	/**
	 * Get settings array
	 *
	 * @since 3.8.0
	 * @since 3.30.3 Fixed typo in title id.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array();

		$settings[] = array(
			'class' => 'top',
			'id'    => 'notification_options',
			'type'  => 'sectionstart',
		);

		$settings[] = array(
			'title' => __( 'Notification Settings', 'lifterlms' ),
			'type'  => 'title',
			'id'    => 'notification_options_title',
		);

		if ( isset( $_GET['notification'] ) ) {

			$controller = llms()->notifications()->get_controller( llms_filter_input_sanitize_string( INPUT_GET, 'notification' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $controller ) {

				$settings = array_merge( $settings, $this->get_notification_settings( $controller ) );

			} else {

				$settings[] = array(
					'id'    => 'notification_options_invalid_error',
					'type'  => 'custom-html',
					'value' => __( 'Invalid notification', 'lifterlms' ),
				);

			}
		} else {

			$settings[] = array(
				'id'    => 'llms_notifications_table',
				'table' => new LLMS_Table_NotificationSettings(),
				'type'  => 'table',
			);

		}

		$settings[] = array(
			'id'   => 'notification_options',
			'type' => 'sectionend',
		);

		return apply_filters( 'lifterlms_notifications_settings', $settings );

	}

	/**
	 * Disable save button on the main notification tab (list)
	 *
	 * @since 3.24.0
	 *
	 * @param bool $bool Default display value (true).
	 * @return boolean
	 */
	public function maybe_disable_save( $bool ) {

		return ( isset( $_GET['notification'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	}

	/**
	 * Output a merge code button in the WYSIWYG editor
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function merge_code_button() {

		llms_merge_code_button( $this->view->get_option_name( 'body' ), true, $this->view->get_merge_codes() );

	}

	/**
	 * Remove test data from $_POST so that it wont be saved to the DB
	 *
	 * @since 3.24.0
	 * @since 3.35.0 Verify nonce & Sanitize input data.
	 *
	 * @return void
	 */
	public function before_save() {

		if ( ! llms_verify_nonce( '_wpnonce', 'lifterlms-settings' ) ) {
			return;
		}

		if ( isset( $_POST['llms_notification_test_data'] ) ) {

			$_POST['llms_notification_test_data_temp'] = wp_unslash( $_POST['llms_notification_test_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			unset( $_POST['llms_notification_test_data'] );

		}

	}

	/**
	 * Send a test notification after notification data is saved
	 *
	 * @since 3.24.0
	 * @since 3.35.0 Verify nonce & Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public function after_save() {

		if ( ! llms_verify_nonce( '_wpnonce', 'lifterlms-settings' ) ) {
			return;
		}

		if ( isset( $_GET['notification'] ) && isset( $_GET['type'] ) && isset( $_POST['llms_notification_test_data_temp'] ) ) {

			if ( ! empty( $_POST['llms_notification_test_data_temp'] ) ) {

				$controller = llms()->notifications()->get_controller( llms_filter_input_sanitize_string( INPUT_GET, 'notification' ) );

				$controller->send_test(
					llms_filter_input_sanitize_string( INPUT_GET, 'type' ),
					wp_unslash( $_POST['llms_notification_test_data_temp'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

			}
		}

	}

}

return new LLMS_Settings_Notifications();
