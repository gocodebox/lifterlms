<?php

class LLMS_Notifications {

	protected static $_instance = null;

	protected $controllers = array();
	protected $views = array();

	/**
	 * Main Instance
	 * @return LLMS_Controller_Notifications
	 * @since     ??
	 * @version   ??
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {

		$this->load();

	}

	private function load() {

		$triggers = array(
			'lesson_complete',
		);

		$dir = LLMS_PLUGIN_DIR . 'includes/notifications/';

		foreach ( $triggers as $name ) {

			$filename = str_replace( '_', '.', $name );

			$this->controllers[ $name ] = require_once  $dir . 'class.llms.notification.controller.' . $filename . '.php';

			$this->views[] = $name;
			require_once $dir . 'class.llms.notification.view.' . $filename . '.php';

		}

	}

	public function get_view( $notification ) {

		$trigger = $notification->get( 'trigger_id' );

		if ( in_array( $trigger, $this->views ) ) {
			$class = $this->get_view_classname( $trigger );
			$view = new $class( $notification );
			return $view;
		}

		return false;

	}

	private function get_view_classname( $trigger ) {
		$name = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $trigger ) ) );
		return 'LLMS_Notification_View_' . $name;
	}

	public function get_controller( $controller ) {
		if ( isset( $this->controllers[ $controller ] ) ) {
			return $this->controllers[ $controller ];
		}
		return false;
	}

	public function get_controllers() {
		return $this->controllers;
	}

}
