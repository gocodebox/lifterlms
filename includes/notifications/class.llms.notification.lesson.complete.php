<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Lesson_Complete extends LLMS_Notification {

	public $id = 'lesson_complete';

	protected $action = 'lifterlms_lesson_completed';
	protected $accepted_args = 2;
	protected $priority = 10;

	public function callback( $student_id = null, $lesson_id = null ) {

		$this->student = new LLMS_Student( $student_id );
		$this->lesson = llms_get_post( $lesson_id );


		// add a basic subscription for the student
		$this->add_subscription( $student_id, 'basic' );

		$this->handle();

	}

	protected function set_merge_codes() {
		return array(
			'STUDENT_NAME',
			'LESSON_TITLE',
		);
	}

	private function is_subscriber_self( $subscriber_id ) {
		return $subscriber_id === $this->student->get_id();
	}

	protected function merge_code( $code, $subscriber_id = null ) {

		switch ( $code ) {

			case 'LESSON_TITLE':
				return $this->lesson->get( 'title' );
			break;

			case 'STUDENT_NAME':
				if ( $this->is_subscriber_self( $subscriber_id ) ) {
					return __( 'you', 'lifterlms' );
				}
				return $this->student->get_name();
			break;
		}

		return $code;

	}

	protected function set_title( $subscriber_id = null, $type = null ) {

		if ( 'email' === $type ) {
			return sprintf( __( 'Congratulations, %1$s Completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{LESSON_TITLE}}' );
		}

		return __( 'Lesson Completed', 'lifterlms' );

	}

	protected function set_body( $subscriber_id = null, $type = null ) {

		return sprintf( __( 'wooh00t %s', 'lifterlms' ), '{{STUDENT_NAME}}' );

	}

	protected function set_icon( $subscriber_id = null, $type = null ) {

		$img = $this->lesson->get_image( $this->get_icon_dimensions() );
		if ( ! $img ) {
			$course = $this->lesson->get_course();
			$img = $course->get_image( $this->get_icon_dimensions() );
		}

		return $img;

	}

	protected function set_handlers() {
		return array(
			'basic',
			'email',
		);
	}

}

return new LLMS_Notification_Lesson_Complete();
