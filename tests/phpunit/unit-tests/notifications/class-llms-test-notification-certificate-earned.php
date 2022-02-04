<?php
/**
 * LLMS_Notification Certificate Earned
 *
 * @package LifterLMS/Tests/Notifications
 *
 * @group notifications
 *
 * @since [version]
 */
class LLMS_Test_Notification_Certificate_Earned extends LLMS_NotificationTestCase {

	/**
	 * The ID of the tested notification.
	 *
	 * @var string
	 */
	protected $notification_id = 'certificate_earned';

	/**
	 * The name of the controller class for the tested notification.
	 *
	 * @var string
	 */
	protected $controller_class = 'LLMS_Notification_Controller_Certificate_Earned';

	/**
	 * The name of the view class for the tested notification.
	 *
	 * @var string
	 */
	protected $view_class = 'LLMS_Notification_View_Certificate_Earned';

	/**
	 * Function used to setup arguments passed to a notification controller's `action_callback()` function.
	 *
	 * @since [version]
	 *
	 * @return int[]
	 */
	protected function setup_args() {

		$user_id     = $this->factory->student->create();
		$engagement  = $this->create_mock_engagement( 'course_completed', 'certificate' );
		$related_id  = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );
		$template_id = get_post_meta( $engagement->ID, '_llms_engagement', true );

		$attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
		set_post_thumbnail( $template_id, $attachment_id );

		llms_enroll_student( $user_id, $related_id );

		$earned = LLMS_Engagement_Handler::handle_certificate( array( $user_id, $template_id, $related_id, $engagement->ID ) );

		return array(
			$user_id,
			$earned->get( 'id' ),
			$related_id,
		);

	}

	/**
	 * Test set_merge_data()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_merge_data() {

		$view = $this->get_view();

		$user = llms_get_student( $this->last_setup_args[0] );
		$cert = new LLMS_User_Certificate( $this->last_setup_args[1] );

		$expected_content = apply_filters( 'the_content', 'Test Blog, ' . date( get_option( 'date_format' ) ) );

		$tests = array(
			'{{CERTIFICATE_CONTENT}}' => $expected_content,
			'{{CERTIFICATE_TITLE}}'   => $cert->get( 'title' ),
			'{{CERTIFICATE_URL}}'     => get_permalink( $cert->get( 'id' ) ),
			'{{STUDENT_NAME}}'        => 'you',
			'{{FAKE_CODE}}'           => '{{FAKE_CODE}}',
		);

		foreach( $tests as $code => $expected ) {
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $view, 'set_merge_data', array( $code ) ) );
		}

		$mini_cert = LLMS_Unit_Test_Util::call_method( $view, 'set_merge_data', array( '{{MINI_CERTIFICATE}}' ) );
		$this->assertEquals( 0, strpos( '<div class="llms-mini-cert">', $mini_cert ) );
		$this->assertStringContainsString( "<h2 class=\"llms-mini-cert-title\">{$cert->get( 'title' )}</h2>", $mini_cert );

	}

}
