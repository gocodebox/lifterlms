<?php
/**
 * Admin Settings: Notifications Tab
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Notifications extends LLMS_Settings_Page {

	public function __construct() {

		require_once LLMS_PLUGIN_DIR . 'includes/admin/settings/tables/class.llms.table.notification.settings.php';

		$this->id    = 'notifications';
		$this->label = __( 'Notifications', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	private function get_option_name( $controller, $option ) {

	}

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

			$back_url = esc_url( admin_url( 'admin.php?page=llms-settings&tab=notifications' ) );

			if ( $controller ) {

				add_action( 'media_buttons', array( $this, 'merge_code_button' ) );

				$type = sanitize_text_field( $_GET['type'] );

				$view = $controller->get_mock_view( $type );

				// so the merge code button can use it
				$this->view = $view;

				$settings[] = array(
					'title' => $controller->get_title() . ' (' . $type . ')',
					'type' => 'subtitle',
					'id' => 'notificatiion_options_subtitle',
				);

				$settings[] = array(
					'after_html' => llms_merge_code_button( '#' . $view->get_option_name( 'title' ), false, $view->get_merge_codes() ),
					'id' => $view->get_option_name( 'title' ),
					'title' => __( 'Title / Subject', 'lifterlms' ),
					'type' => 'text',
					'value' => $view->get_title( false ),
				);

				$settings[] = array(
					'editor_settings' => array(
						'teeny' => true,
					),
					'title' => __( 'Body', 'lifterlms' ),
					'type' => 'wpeditor',
					'id' => $view->get_option_name( 'body' ),
					'value' => $view->get_body( false ),
				);

				if ( 'basic' === $type ) {
					$settings[] = array(
						'title' => __( 'Icon', 'lifterlms' ),
						'type' => 'text',
						'id' => $view->get_option_name( 'icon' ),
						'value' => $view->get_icon(),
					);
				}


			} else {

				$settings[] = array(
					'id' => 'notificatiion_options_invalid_error',
					'type' => 'custom-html',
					'value' => __( 'Invalid notification type', 'lifterlms' ),
				);

			}

			$settings[] = array(
				'id' => 'notificatiion_options_invalid_error',
				'type' => 'custom-html',
				'value' => '<small><a href="' . $back_url . '">' . __( 'Back to all notifications', 'lifterlms' ) . '</a></small>'
			);

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


	public function merge_code_button() {

		llms_merge_code_button( $this->view->get_option_name( 'body' ), true, $this->view->get_merge_codes() );

	}

}

return new LLMS_Settings_Notifications();
