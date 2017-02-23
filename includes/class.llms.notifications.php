<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notifications {

	private $notifications = array();
	private $handlers = array();

	protected static $_instance = null;

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

		// data
		require_once LLMS_PLUGIN_DIR . 'includes/notifications/class.llms.notification.data.php';

		// handlers
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.notification.handler.php';
		require_once LLMS_PLUGIN_DIR . 'includes/notifications/class.llms.notification.handler.basic.php';

		$this->handlers['basic'] = new LLMS_Notification_Handler_Basic();

		require_once LLMS_PLUGIN_DIR . 'includes/interfaces/interface.llms.notification.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.notification.php';

		$classes = array(
			'lesson.complete',
		);

		foreach ( $classes as $class ) {

			$file = LLMS_PLUGIN_DIR . 'includes/notifications/class.llms.notification.' . $class . '.php';
			$obj = require_once $file;
			$this->notifications[ $obj->id ] = $obj;

		}

	}

	public function get_handler( $handler ) {
		if ( isset( $this->handlers[ $handler ] ) ) {
			return $this->handlers[ $handler ];
		}
		return false;
	}

}
