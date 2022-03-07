<?php
/**
 * LLMS_Notification Achievement Earned
 *
 * @package LifterLMS/Tests/Notifications
 *
 * @group notifications
 *
 * @since 3.8.0
 */

class LLMS_Test_Notification_Achievement_Earned extends LLMS_NotificationTestCase {

	/**
	 * The ID of the tested notification.
	 *
	 * @var string
	 */
	protected $notification_id = 'achievement_earned';

	/**
	 * The name of the controller class for the tested notification.
	 *
	 * @var string
	 */
	protected $controller_class = 'LLMS_Notification_Controller_Achievement_Earned';

	/**
	 * The name of the view class for the tested notification.
	 *
	 * @var string
	 */
	protected $view_class = 'LLMS_Notification_View_Achievement_Earned';

	/**
	 * Function used to setup arguments passed to a notification controller's `action_callback()` function.
	 *
	 * @since 6.0.0
	 *
	 * @return int[]
	 */
	protected function setup_args() {

		$user_id     = $this->factory->student->create();
		$engagement  = $this->create_mock_engagement( 'course_completed', 'achievement' );
		$related_id  = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );
		$template_id = get_post_meta( $engagement->ID, '_llms_engagement', true );

		$attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
		set_post_thumbnail( $template_id, $attachment_id );

		llms_enroll_student( $user_id, $related_id );

		$earned = LLMS_Engagement_Handler::handle_achievement( array( $user_id, $template_id, $related_id, $engagement->ID ) );

		return array(
			$user_id,
			$earned->get( 'id' ),
			$related_id,
		);

	}

	/**
	 * Test set_merge_data()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_set_merge_data() {

		$view = $this->get_view();

		$user       = llms_get_student( $this->last_setup_args[0] );
		$achievement = new LLMS_User_Achievement( $this->last_setup_args[1] );

		// $img_url = get_the_post_thumbnail_url( $this->last_setup_args[1] );

		$tests = array(
			'{{ACHIEVEMENT_CONTENT}}'   => $achievement->get( 'content' ),
			// '{{ACHIEVEMENT_IMAGE}}'     => '',
			// '{{ACHIEVEMENT_IMAGE_URL}}' => '',
			'{{ACHIEVEMENT_TITLE}}'     => $achievement->get( 'title' ),
			'{{STUDENT_NAME}}'          => 'you',
		);

		foreach( $tests as $code => $expected ) {
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $view, 'set_merge_data', array( $code ) ) );
		}

		$this->markTestIncomplete( 'Need to add tests for achievement image' );

	}

}
