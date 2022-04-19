<?php
/**
 * Test LifterLMS Shortcodes
 *
 * @package LifterLMS/Tests
 *
 * @group shortcodes
 *
 * @since 3.4.3
 * @since 3.24.1 Unknown.
 * @since 4.0.0 Add tests for `get_course_id()` method.
 * @since 5.0.0 Don't need to test for password strength enqueue anymore.
 */
class LLMS_Test_Shortcodes extends LLMS_UnitTestCase {

	/**
	 * Test the private get_course_id() method used by various legacy shortcodes.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_course_id() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'questions' => 1 ) );

		$course_id = $course->get( 'id' );

		// On course.
		$this->go_to( get_permalink( $course_id ) );
		$this->assertTrue( is_course() );
		$this->assertEquals( $course_id, LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcodes', 'get_course_id' ) );

		// On lesson.
		$lesson = $course->get_lessons()[0];
		$this->go_to( get_permalink( $lesson->get( 'id' ) ) );
		$this->assertTrue( is_lesson() );
		$this->assertEquals( $course_id, LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcodes', 'get_course_id' ) );

		// On quiz.
		$this->go_to( get_permalink( $lesson->get( 'quiz' ) ) );
		$this->assertTrue( is_quiz() );
		$this->assertEquals( $course_id, LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcodes', 'get_course_id' ) );

	}

	/**
	 * Generic tests and a few tests on the abstract
	 * @return   void
	 * @since    3.4.3
	 * @version  3.24.1
	 */
	public function test_shortcodes() {

		$shortcodes = array(
			'LLMS_Shortcode_Course_Author',
			'LLMS_Shortcode_Course_Continue',
			'LLMS_Shortcode_Course_Meta_Info',
			'LLMS_Shortcode_Course_Outline',
			'LLMS_Shortcode_Course_Prerequisites',
			'LLMS_Shortcode_Course_Reviews',
			'LLMS_Shortcode_Course_Syllabus',
			'LLMS_Shortcode_Membership_Link',
			'LLMS_Shortcode_Registration',
		);

		foreach ( $shortcodes as $class ) {

			$obj = $class::instance();
			$this->assertTrue( shortcode_exists( $obj->tag ) );
			$this->assertTrue( is_a( $obj, 'LLMS_Shortcode' ) );
			$this->assertTrue( ! empty( $obj->tag ) );
			$this->assertTrue( is_string( $obj->output() ) );
			$this->assertTrue( is_array( $obj->get_attributes() ) );
			$this->assertTrue( is_string( $obj->get_content() ) );

		}

		$this->assertClassHasStaticAttribute( '_instances', 'LLMS_Shortcode' );

	}

	/**
	 * Test the registration shortcode
	 *
	 * @since 3.4.3
	 * @since 4.4.0 Use `LLMS_Assets::is_inline_enqueued()` in favor of deprecated `LLMS_Frontend_Assets::is_inline_script_enqueued()`.
	 * @since 5.0.0 Don't need to test for password strength enqueue anymore.
	 * @since 5.3.3 Use `assertStringContains()` in favor of `assertContains()`.
	 *
	 * @return void
	 */
	public function test_registration() {

		// our output should enqueue this
		wp_dequeue_script( 'password-strength-meter' );

		$obj = LLMS_Shortcode_Registration::instance();

		// when logged out, there should be html content
		$this->assertStringContains( 'llms-new-person-form-wrapper', $obj->output() );

		// no html when logged in
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$this->assertEmpty( $obj->output() );

	}

	/**
	 * Test lifterlms_membership_link shortcode
	 *
	 * @since 3.4.3
	 * @since 5.3.3 Use `assertStringContains()` in favor of `assertContains()`.
	 *
	 * @return void
	 */
	public function test_membership_link() {

		// create a membership that we can use for linking
		$mid = $this->factory->post->create( array(
			'post_title' => 'Test Membership',
			'post_type' => 'llms_membership',
		) );

		$obj = LLMS_Shortcode_Membership_Link::instance();

		// test default settings
		$this->assertStringContains( get_permalink( $mid ), $obj->output( array( 'id' => $mid ) ) );
		$this->assertStringContains( get_the_title( $mid ), $obj->output( array( 'id' => $mid ) ) );

		$this->assertEquals( $mid, $obj->get_attribute( 'id' ) );

		// check non default content
		$this->assertStringContains( 'Alternate Text', $obj->output( array( 'id' => $mid ), 'Alternate Text' ) );
		$this->assertEquals( 'Alternate Text', $obj->get_content( 'Alternate Text' ) );

	}

	/**
	 * Tests that the shortcodes are initialized before the WordPress 'init' action hook calls any other LifterLMS callbacks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_shortcodes_initialized_early() {

		global $wp_filter;

		$shortcodes_init_is_found = false;
		foreach ( $wp_filter['init'][10] as $idx => $callback ) {

			// $idx is a unique ID that for static methods will be class_name . '::' . method_name.
			if ( 'LLMS_Shortcodes::init' === $idx ) {
				$shortcodes_init_is_found = true;
				continue;
			}

			if ( is_array( $callback['function'] ) ) {
				$class = is_object( $callback['function'][0] ) ? get_class( $callback['function'][0] ) : $callback['function'][0];
				$function = "{$class}::{$callback['function'][1]}";
			} else {
				$function = $callback['function'];
			}

			if ( 0 === strpos( $function, 'LLMS_' ) ) {
				$this->assertTrue(
					$shortcodes_init_is_found,
					"Should find LLMS_Shortcodes::init callback before $function."
				);
			}
		}
	}

}
