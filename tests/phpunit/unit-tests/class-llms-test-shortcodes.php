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
	 * Test access plan button output allows safe HTML in custom label text.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_access_plan_button_allows_safe_html() {

		$plan = $this->get_mock_plan();
		$html = LLMS_Shortcodes::access_plan_button(
			array( 'id' => $plan->get( 'id' ) ),
			'Custom <b>Label</b>'
		);

		$this->assertStringContains( '<b>Label</b>', $html );

	}

	/**
	 * Test access plan button output strips disallowed markup from custom label text.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_access_plan_button_strips_unsafe_html() {

		$plan = $this->get_mock_plan();
		$html = LLMS_Shortcodes::access_plan_button(
			array( 'id' => $plan->get( 'id' ) ),
			'Buy <script>bad</script><img src="x" onerror="bad">'
		);

		$this->assertStringNotContains( '<script>', $html );
		$this->assertStringNotContains( 'onerror', $html );
		$this->assertStringContains( '<img', $html );

	}

	/**
	 * Test access plan button output sanitizes the plan enroll text fallback.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_access_plan_button_sanitizes_enroll_text() {

		$plan = $this->get_mock_plan();

		$handler = function ( $text ) {
			return 'Enroll <b>Now</b><script>bad</script>';
		};
		add_filter( 'llms_plan_get_enroll_text', $handler );

		$html = LLMS_Shortcodes::access_plan_button( array( 'id' => $plan->get( 'id' ) ) );

		remove_filter( 'llms_plan_get_enroll_text', $handler );

		$this->assertStringContains( '<b>Now</b>', $html );
		$this->assertStringNotContains( '<script>', $html );

	}

	/**
	 * Test the access plan button block sanitizes its text attribute.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_access_plan_button_block_sanitizes_text_attribute() {

		$plan   = $this->get_mock_plan();
		$markup = sprintf(
			'<!-- wp:llms/access-plan-button %s /-->',
			wp_json_encode(
				array(
					'id'   => (string) $plan->get( 'id' ),
					'text' => 'Buy <b>Now</b><script>bad</script>',
				)
			)
		);
		$html = do_blocks( $markup );

		$this->assertStringContains( '<b>Now</b>', $html );
		$this->assertStringNotContains( '<script>', $html );

	}

	/**
	 * Test that the course info shortcode only outputs info the current user can view.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_course_info_checks_access() {

		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$protected_id = $this->factory->course->create( array( 'sections' => 0 ) );
		wp_update_post(
			array(
				'ID'            => $protected_id,
				'post_title'    => 'Protected Course',
				'post_content'  => 'Protected course content',
				'post_password' => 'coursepass123',
				'post_author'   => $admin_id,
			)
		);
		update_post_meta( $protected_id, '_llms_start_date', '2026-11-06' );

		$draft_id = $this->factory->course->create( array( 'sections' => 0 ) );
		wp_update_post(
			array(
				'ID'           => $draft_id,
				'post_content' => 'Draft course content',
				'post_status'  => 'draft',
				'post_author'  => $admin_id,
			)
		);

		$order = $this->get_mock_order();
		$order->set( 'billing_email', 'buyer@example.com' );

		$info = function ( $id, $key ) {
			return do_shortcode( sprintf( '[lifterlms_course_info id=%1$d key=%2$s date_format=Y-m-d]', $id, $key ) );
		};

		// Logged out visitors can view public info of a published course.
		wp_set_current_user( 0 );
		$this->assertSame( '2026-11-06', $info( $protected_id, 'start_date' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'contributor' ) ) );

		$this->assertSame( 'Protected Course', $info( $protected_id, 'title' ) );
		$this->assertSame( '', $info( $protected_id, 'password' ) );
		$this->assertSame( '', $info( $protected_id, 'content' ) );
		$this->assertSame( '', $info( $draft_id, 'content' ) );
		$this->assertSame( '', $info( $order->get( 'id' ), 'billing_email' ) );

		wp_set_current_user( $admin_id );

		$this->assertSame( 'coursepass123', $info( $protected_id, 'password' ) );
		$this->assertStringContains( 'Draft course content', $info( $draft_id, 'content' ) );
		$this->assertSame( '', $info( $order->get( 'id' ), 'billing_email' ) );

	}

	/**
	 * Test that the course info shortcode escapes date output.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_course_info_escapes_date_output() {

		$course_id = $this->factory->course->create( array( 'sections' => 0 ) );
		update_post_meta( $course_id, '_llms_start_date', '2026-11-06' );

		// A date format with escaped characters cannot add markup.
		$this->assertSame(
			'&lt;b&gt;2026',
			do_shortcode( sprintf( '[lifterlms_course_info id=%d key=start_date date_format=\\\\<\\\\b\\\\>Y]', $course_id ) )
		);
		$this->assertSame( '2026-11-06', do_shortcode( sprintf( '[lifterlms_course_info id=%d key=start_date date_format=Y-m-d]', $course_id ) ) );

	}

	/**
	 * Test that course element and outline shortcodes only output courses the current user can view.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_course_element_shortcodes_check_access() {

		$admin_id  = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$author_id = $this->factory->user->create( array( 'display_name' => 'Hidden Author Name' ) );

		$courses = array();
		foreach ( array( 'publish', 'draft' ) as $status ) {
			$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 0 ) );
			$lesson = $course->get_lessons()[0];
			wp_update_post( array( 'ID' => $lesson->get( 'id' ), 'post_title' => 'Hidden Lesson Title', 'post_author' => $author_id ) );
			wp_update_post( array( 'ID' => $course->get( 'id' ), 'post_status' => $status, 'post_author' => $author_id ) );
			$courses[ $status ] = array( $course->get( 'id' ), $lesson->get( 'id' ) );
		}

		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$this->go_to( get_permalink( $page_id ) );

		$render = function ( $id ) {
			return do_shortcode( sprintf( '[lifterlms_course_author course_id=%1$d][lifterlms_course_syllabus course_id=%1$d][lifterlms_course_outline course_id=%1$d]', $id ) );
		};

		foreach ( array( 0, $admin_id ) as $user_id ) {
			wp_set_current_user( $user_id );
			$can_view_draft = (bool) $user_id;
			foreach ( $courses as $status => $ids ) {
				foreach ( $ids as $id ) {
					$output   = $render( $id );
					$expected = 'publish' === $status || $can_view_draft;
					$this->assertSame( $expected, false !== strpos( $output, 'Hidden Author Name' ), "{$status} {$id} user {$user_id} author" );
					$this->assertSame( $expected, false !== strpos( $output, 'Hidden Lesson Title' ), "{$status} {$id} user {$user_id} lessons" );
				}
			}
		}

	}

	/**
	 * Test that the related courses shortcode outputs published courses.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_related_courses() {

		$course_id = $this->factory->course->create( array( 'sections' => 0 ) );

		$this->assertStringContainsString( get_permalink( $course_id ), do_shortcode( '[lifterlms_related_courses]' ) );

	}

	/**
	 * Tests that the shortcodes are initialized before the WordPress 'init' action hook calls any other LifterLMS callbacks.
	 *
	 * @since 6.4.0
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
