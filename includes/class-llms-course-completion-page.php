<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Course_Completion_Page {

	use LLMS_Trait_Singleton;

	public function __construct() {
		$this->init();
	}

	protected function init() {

		// Add it as the last action to ensure other handlers of course completion happen first.
		add_action( 'lifterlms_course_completed', array( $this, 'maybe_redirect_to_course_completion_page' ), 9999, 2 );
	}

	public function maybe_redirect_to_course_completion_page( $student_id, $related_post_id ) {
		if ( is_admin() || $student_id !== get_current_user_id() ) {
			return;
		}

		$course = new LLMS_Course( $related_post_id );

		if ( ! $course ) {
			return;
		}

		if ( $course->get( 'completion_page_id' ) ) {
			wp_safe_redirect( get_permalink( $course->get( 'completion_page_id' ) ) );
			exit();
		}

		if ( get_option( 'lifterlms_course_completion_page_id', '' ) ) {
			wp_safe_redirect( get_permalink( get_option( 'lifterlms_course_completion_page_id' ) ) );
			exit();
		}
	}
}

return new LLMS_Course_Completion_Page();
