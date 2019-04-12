<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings: Notifications Tab
 * @since    3.8.0
 * @version  3.24.0
 */
class LLMS_Settings_Notifications extends LLMS_Settings_Page {

	/**
	 * Constructor
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	public function __construct() {

		require_once LLMS_PLUGIN_DIR . 'includes/admin/settings/tables/class.llms.table.notification.settings.php';

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
	 * @param    string     $current_title  the title of the current notification
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	private function get_breadcrumbs( $current_title ) {
		return array(
			'id' => 'notification_options_breadcrumbs',
			'type' => 'custom-html',
			'value' => '<a href="' . esc_url( admin_url( 'admin.php?page=llms-settings&tab=notifications' ) ) . '">' . __( 'All Notifications', 'lifterlms' ) . '</a> <small>&gt;</small> <strong>' . $current_title . '</strong>',
		);
	}

	/**
	 * Get settings specific to the current notification type
	 * @param    obj     $controller  instance of an LLMS_Notification_Controller
	 * @return   array
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	private function get_notification_settings( $controller ) {

		$settings = array();

		// setup vars
		$type = sanitize_text_field( $_GET['type'] );
		$types = $controller->get_supported_types();
		$title = $controller->get_title() . ' (' . $types[ $type ] . ')';
		$view = $controller->get_mock_view( $type );

		// so the merge code button can use it i
		$this->view = $view;

		// output the merge code button for the WYSIWYG editor
		add_action( 'media_buttons', array( $this, 'merge_code_button' ) );

		// add a breadcrumb on the top of the page
		$settings[] = $this->get_breadcrumbs( $title );

		// add field options for the view
		$settings = array_merge( $settings, $view->get_field_options( $type ) );

		$subscribers = $controller->get_subscriber_options( $type );

		foreach ( $subscribers as $i => $data ) {

			$sub_settings = array(
				'default' => $data['enabled'],
				'desc' => $data['title'],
				'id' => sprintf( '%1$s[%2$s]', $controller->get_option_name( $type . '_subscribers' ), $data['id'] ),
				'type' => 'checkbox',
			);

			if ( 0 === $i ) {
				$sub_settings['title'] = __( 'Subscribers', 'lifterlms' );
				$sub_settings['checkboxgroup'] = 'start';
			} elseif ( count( $subscribers ) - 1 === $i ) {
				$sub_settings['checkboxgroup'] = 'end';
			} else {
				$sub_settings['checkboxgroup'] = 'middle';
			}

			$settings[] = $sub_settings;

			if ( 'custom' === $data['id'] ) {
				$settings[] = array(
					'desc' => '<br>' . $data['description'],
					'id' => $controller->get_option_name( $type . '_custom_subscribers' ),
					'type' => 'text',
				);
			}
		}

		if ( $controller->is_testable( $type ) ) {
			foreach ( $controller->get_test_settings( $type ) as $setting ) {
				$setting['id'] = 'llms_notification_test_data[' . $setting['id'] . ']';
				$settings[] = $setting;
			}
		}

		return apply_filters( 'llms_notification_settings_' . $controller->id . '_' . $type, $settings, $controller, $view );

	}

	/**
	 * Get settings array
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_settings() {

		$settings = array();

		$settings[] = array(
			'class' => 'top',
			'id' => 'notification_options',
			'type' => 'sectionstart',
		);

		$settings[] = array(
			'title' => __( 'Notification Settings', 'lifterlms' ),
			'type' => 'title',
			'id' => 'notificati_options_title',
		);

		if ( isset( $_GET['notification'] ) ) {

			$controller = LLMS()->notifications()->get_controller( $_GET['notification'] );

			if ( $controller ) {

				$settings = array_merge( $settings, $this->get_notification_settings( $controller ) );

			} else {

				$settings[] = array(
					'id' => 'notification_options_invalid_error',
					'type' => 'custom-html',
					'value' => __( 'Invalid notification', 'lifterlms' ),
				);

			}
		} else {

			$settings[] = array(
				'id' => 'llms_notifications_table',
				'table' => new LLMS_Table_NotificationSettings(),
				'type' => 'table',
			);

		}

		$settings[] = array(
			'id' => 'notification_options',
			'type' => 'sectionend',
		);

		return apply_filters( 'lifterlms_notifications_settings', $settings );

	}

	/**
	 * Disable save button on the main notification tab (list)
	 * @param    bool      $bool  default display value (true)
	 * @return   boolean
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function maybe_disable_save( $bool ) {

		return ( isset( $_GET['notification'] ) );

	}

	/**
	 * Output a merge code button in the WYSIWYG editor
	 * @return   [type]     [description]
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function merge_code_button() {

		llms_merge_code_button( $this->view->get_option_name( 'body' ), true, $this->view->get_merge_codes() );

	}

	/**
	 * Remove test data from $_POST so that it wont be saved to the DB
	 * @return   void
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function before_save() {

		if ( isset( $_POST['llms_notification_test_data'] ) ) {

			$_POST['llms_notification_test_data_temp'] = $_POST['llms_notification_test_data'];
			unset( $_POST['llms_notification_test_data'] );

		}

	}

	/**
	 * Send a test notification after notification data is saved
	 * @return   void
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function after_save() {

		if ( isset( $_GET['notification'] ) && isset( $_GET['type'] ) && isset( $_POST['llms_notification_test_data_temp'] ) ) {

			if ( ! empty( $_POST['llms_notification_test_data_temp'] ) ) {

				$controller = LLMS()->notifications()->get_controller( $_GET['notification'] );
				$controller->send_test( sanitize_text_field( $_GET['type'] ), $_POST['llms_notification_test_data_temp'] );

			}
		}

	}

}

return new LLMS_Settings_Notifications();
