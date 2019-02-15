<?php
/**
 * Unit Test Case with tests and utilities specific to testing LifterLMS Notification Classes
 * @since    3.8.0
 * @version  3.8.0
 */

require_once 'class-llms-unit-test-case.php';

class LLMS_NotificationTestCase extends LLMS_UnitTestCase {

	public function test_is_registered() {

		$main = LLMS()->notifications();

		$controller = $main->get_controller( $this->notification_id );
		$this->assertTrue( is_a( $controller, 'LLMS_Abstract_Notification_Controller' ) );
		$this->assertEquals( $this->notification_id, $controller->id );

		// $view = $main->get_view( $this->notification_id );
		// $this->assertTrue( is_a( $view, 'LLMS_Abstract_Notification_View' ) );
		// $this->assertEquals( $this->notification_id, $view->trigger_id );

	}

}
