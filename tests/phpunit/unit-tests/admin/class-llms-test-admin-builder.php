<?php
/**
 * Test Admin Builder API
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group builder
 *
 * @since 3.37.12
 * @since 4.14.0 Added tests on the autosave option.
 * @since 4.16.0 Added tests on 'the_title' and 'the_content' filters not affecting the save.
 * @since 5.1.3 Added tests on lesson moved into a brand new section.
 */
class LLMS_Test_Admin_Builder extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case
	 *
	 * @since 3.37.12
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->main = 'LLMS_Admin_Builder';
	}

	/**
	 * Test get_autosave_states()
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function test_get_autosave_status() {

		// Defaults to yes.
		$this->assertEquals( 'no', LLMS_Unit_Test_Util::call_method( $this->main, 'get_autosave_status' ) );

		// User has no value set.
		$user = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user );
		$this->assertEquals( 'no', LLMS_Unit_Test_Util::call_method( $this->main, 'get_autosave_status' ) );

		// Explicit yes.
		update_user_meta( $user, 'llms_builder_autosave','yes' );
		$this->assertEquals( 'yes', LLMS_Unit_Test_Util::call_method( $this->main, 'get_autosave_status' ) );

		// Explicit no.
		update_user_meta( $user, 'llms_builder_autosave','no' );
		$this->assertEquals( 'no', LLMS_Unit_Test_Util::call_method( $this->main, 'get_autosave_status' ) );

	}

	/**
	 * Test LLMS_Admin_Builder::get_existing_posts() with a lesson created by users of different roles.
	 *
	 * @since 5.8.0
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1849
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_get_existing_lesson_by_role() {

		$all_lesson_ids        = array();
		$instructor_lesson_ids = array();
		$users                 = array();
		$roles                 = array(
			'administrator',
			'lms_manager',
			'instructor',
			'instructors_assistant',
			'student',
		);

		// Create multiple users for each role.
		foreach ( $roles as $role ) {

			for ( $user_counter = 0; $user_counter < 2; $user_counter ++ ) {

				$user               = $this->factory->user->create_and_get( array( 'role' => $role ) );
				$users[ $user->ID ] = $user;

				// Create multiple courses that are authored by this instructor.
				if ( 'instructor' === $role ) {
					wp_set_current_user( $user->ID );

					if ( ! isset( $instructor_lesson_ids[ $user->ID ] ) ) {
						$instructor_lesson_ids[ $user->ID ] = array();
					}

					for ( $course_counter = 0; $course_counter < 2; $course_counter ++ ) {

						$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 2 ) );
						foreach ( $course->get_lessons( 'ids' ) as $lesson_id ) {
							$all_lesson_ids[]                     = $lesson_id;
							$instructor_lesson_ids[ $user->ID ][] = $lesson_id;
						}
					}

					// Create an instructor assistant for this instructor.
					$assistant = $this->factory->instructor->create_and_get( array( 'role' => 'instructors_assistant' ) );
					$assistant->add_parent( $user->ID );
					$users[ $assistant->get_id() ] = $assistant->get_user();
				}
			}
		}

		// Test each user's capability to build courses with lessons.
		foreach ( $users as $user_id => $user ) {

			wp_set_current_user( $user_id );
			$role = reset( $user->roles ); // We created users with only one role.

			// Get lessons that the user can access.
			$lesson_search    = LLMS_Unit_Test_Util::call_method( $this->main, 'get_existing_posts', array( 'lesson' ) );
			$found_lesson_ids = array();
			foreach ( $lesson_search['results'] as $result ) {
				$found_lesson_ids[] = $result['id'];
			}

			switch ( $role ) {
				case 'administrator':
				case 'lms_manager':
					$message = "$role can build courses with all lessons.";
					$this->assertEqualSets( $all_lesson_ids, $found_lesson_ids, $message );
					break;
				case 'instructor':
					$message = 'Instructors can build courses with lessons that they have authored.';
					$this->assertEqualSets( $instructor_lesson_ids[ $user_id ], $found_lesson_ids, $message );
					break;
				case 'instructors_assistant':
					$assistant           = llms_get_instructor( $user_id );
					$instructor_ids      = (array) $assistant->get( 'parent_instructors' );
					$expected_lesson_ids = $instructor_lesson_ids[ reset( $instructor_ids ) ] ?? array();
					$message             = 'Instructor\'s assistants can build courses with lessons that their ' .
						'parent instructors have authored.';
					$this->assertEqualSets( $expected_lesson_ids, $found_lesson_ids, $message );
					break;
				case 'student':
					$this->assertEmpty( $found_lesson_ids, 'Students can not build courses with any lessons.' );
					break;
			}
		}
	}

	/**
	 * Filter callback for `llms_builder_trash_custom_item` used to mock a custom item deletion.
	 *
	 * @since  3.37.12
	 *
	 * @param null|array $trash_response Denotes the trash response. See description above for details.
	 * @param array      $res            The initial default error response which can be modified for your needs and then returned.
	 * @param mixed      $id             The ID of the course element. Usually a WP_Post id.
	 * @return array
	 */
	public function filter_llms_builder_trash_custom_item( $ret, $res, $id ) {
		return compact( 'id' );
	}

	/**
	 * Test process_trash() for an invalid post id (one that doesn't exist).
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_invalid_post_id() {

		$data = array(
			'trash' => array( $this->factory->post->create() + 1 ),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

		$this->assertEquals( $data['trash'][0], $res[0]['id'] );
		$this->assertStringContains( 'Invalid ID.', $res[0]['error'] );

	}

	/**
	 * Test process_trash() for a custom / 3rd party item.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_custom_item() {

		add_filter( 'llms_builder_trash_custom_item', array( $this, 'filter_llms_builder_trash_custom_item' ), 10, 3 );

		$data = array(
			'trash' => array( $this->factory->post->create() + 1 ),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

		$this->assertEquals( array( 'id' => $data['trash'][0] ), $res[0] );

		remove_filter( 'llms_builder_trash_custom_item', array( $this, 'filter_llms_builder_trash_custom_item' ));

	}

	/**
	 * Test process_trash() for an invalid post type.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_invalid_post_type() {

		$data = array(
			'trash' => array( $this->factory->post->create() ),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

		$this->assertEquals( $data['trash'][0], $res[0]['id'] );
		$this->assertEquals( 'Posts cannot be deleted via the Course Builder.', $res[0]['error'] );

	}

	/**
	 * Test process_trash() for success when the post is force-deleted.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_force_delete_success() {

		$types = array( 'section', 'llms_question', 'llms_quiz' );
		foreach ( $types as $type ) {

			$post_id = $this->factory->post->create( array( 'post_type' => $type ) );

			$data = array(
				'trash' => array( $post_id ),
			);

			$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

			// Proper return.
			$this->assertEquals( array( 'id' => $post_id ), $res[0] );

			// Post has been force deleted.
			$this->assertNull( get_post( $post_id ) );

		}

	}

	/**
	 * Test process_trash() when an error is encountered deleting the post.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_deletion_error() {

		// Mock the return of `wp_delete_post()` to simulate an error.
		add_filter( 'pre_delete_post', '__return_false' );

		$post_id = $this->factory->post->create( array( 'post_type' => 'section' ) );

		$data = array(
			'trash' => array( $post_id ),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

		$this->assertEquals( $post_id, $res[0]['id'] );
		$this->assertStringContains( 'Error deleting the Section', $res[0]['error'] );

		remove_filter( 'pre_delete_post', '__return_false' );

	}

	/**
	 * Test process_trash() success when moving an item to the trash.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_move_to_trash() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'lesson' ) );

		$data = array(
			'trash' => array( $post_id ),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

		// Proper return.
		$this->assertEquals( array( 'id' => $post_id ), $res[0] );

		// Post has been trashed
		$this->assertEquals( 'trash', get_post_status( $post_id ) );

	}

	/**
	 * Test process_trash() when deleting a question choice.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_process_trash_question_choice() {

		$course    = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 1 ) );
		$quiz      = $course->get_lessons()[0]->get_quiz();
		$question  = $quiz->get_questions()[0];
		$choice    = $question->get_choices()[0];
		$choice_id = $choice->get( 'id' );

		$id = sprintf( '%1$d:%2$s', $question->get( 'id' ), $choice_id );

		$data = array(
			'trash' => array( $id ),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'process_trash', array( $data ) );

		// Proper return.
		$this->assertEquals( array( 'id' => $id ), $res[0] );

		// Choice has been deleted.
		$this->assertFalse( $question->get_choice( $choice_id ) );

	}

	/**
	 * Test the ajax save an possible filters applied to the title and the content
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_ajax_save_unfiltered_title_content() {

		// Handle wp die ajax and simulate ajax call.
		add_filter( 'wp_die_ajax_handler', array( $this, '_wp_die_handler' ), 1 );
		add_filter( 'wp_doing_ajax', '__return_true' );

		$user = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user );

		// Add title and content filters.
		foreach ( array( 'the_title', 'the_content' ) as $filter_hook ) {
			add_filter( $filter_hook, array( $this, '__return_filtered' ), 999999 );
		}
		// Create a valid course.
		$course = $this->factory->course->create( array( 0,0,0,0 ) );

		$request = array(
			'action_type'  => 'ajax_save',
			'course_id'    => $course,
			'llms_builder' => array(
			),
		);

		$to_save = array(
			'updates' => array(
				'id'       => $course,
				'sections' => array(
					array(
						'id'            => 'temp_28',
						'parent_course' => $course,
						'title'         => 'New Section',
						'type'          => 'section',
						'lessons'       => array(
							array(
								'id'             => 'temp_40',
								'title'          => 'New Lesson',
								'content'        => '<p>Content</p>',
								'video_embed'    => 'https://somevideo',
								'parent_course'  => $course,
								'parent_section' => 'temp_28',
								'type'           => 'lesson',
								'quiz'           => array(
									'id'        => 'temp_123',
									'title'     => 'New Quiz',
									'type'      => 'llms_quiz',
									'lesson_id' => 'temp_40',
									'content'   => '<p>Quiz description</p>',
									'questions' => array(
										array(
											'id'            => 'temp_155',
											'content'       => '<p>Question description 1</p>',
											'title'         => 'Question title 1',
											'parent_id'     => 'temp_123',
											'type'          => 'llms_question',
											'question_type' => 'choice',
										),
										array(
											'id'            => 'temp_156',
											'content'       => '<p>Question description 2</p>',
											'title'         => 'Question title 2',
											'parent_id'     => 'temp_123',
											'type'          => 'llms_question',
											'question_type' => 'choice',
										),
									),
								),
							),
						),
					),
				),
			),
			'id'      => $course,
		);

		$request['llms_builder'] = wp_json_encode( $to_save );

		// Simulate the ajax save request.
		ob_start();
		try {
			LLMS_Unit_Test_Util::call_method( $this->main, 'handle_ajax', array( $request ) );
		} catch ( WPAjaxDieContinueException $e ) {}
		$res = json_decode( $this->last_response, true );

		// Check the request went through.
		$this->assertEquals( 'success', $res['llms_builder']['status'] );

		// Check the raw title and content have not been affected by the filters.
		$this->check_title_content_filtering_on_save( $res, $to_save );

		/* Check the raw title and content have not been affected by the filters. */

		// Following the instructions contained in the handle_ajax method that actually perform the update,
		// but without removing any filters on the_title, the_content.
		$req = $request;
		$req['llms_builder'] = stripslashes( $request['llms_builder'] );
		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'heartbeat_received',
			array(
				array(),
				$req,
			)
		);

		// Check the request went through.
		$this->assertEquals( 'success', $res['llms_builder']['status'] );

		// Check the raw title and content have not been affected by the filters.
		$this->check_title_content_filtering_on_save( $res, $to_save );

		// Reset.
		foreach ( array( 'the_title', 'the_content' ) as $filter_hook ) {
			remove_filter( $filter_hook, array( $this, '__return_filtered' ), 999999 );
		}
		remove_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	/**
	 * Helper that always returns the string '{filtered}'
	 *
	 * @since 4.16.0
	 *
	 * @return string
	 */
	private function __return_filtered() {
		return '{filtered}';
	}

	/**
	 * Helper to check whether the title and content props are filtered on save.
	 *
	 * @since 4.16.0
	 *
	 * @param array $res  Associative array containing the response from the save ajax method.
	 * @param array $sent Associative array containing the data sent for the update.
	 * @return void
	 */
	private function check_title_content_filtering_on_save( $res, $sent ) {

		$li = 0;

		foreach ( $res['llms_builder']['updates']['sections'][0]['lessons'] as $lesson ) {
			$lq = 0;
			foreach ( array( 'title', 'content' ) as $prop ) {
				// Check lesson's title and content.
				$this->assertStringContainsString(
					$sent['updates']['sections'][0]['lessons'][$li][$prop],
					llms_get_post( $lesson['id'] )->get( $prop, true ),
					$prop
				);
				$this->assertStringNotContainsString(
					$this->__return_filtered(),
					llms_get_post( $lesson['id'] )->get( $prop, true ),
					$prop
				);

				// Check quiz title and content.
				$this->assertStringContainsString(
					$sent['updates']['sections'][0]['lessons'][$li]['quiz'][$prop],
					llms_get_post( $lesson['quiz']['id'] )->get( $prop, true ),
					$prop
				);
				$this->assertStringNotContainsString(
					$this->__return_filtered(),
					llms_get_post( $lesson['quiz']['id'] )->get( $prop, true ),
					$prop
				);
			}

			foreach ( $lesson['quiz']['questions'] as $question ) {
				foreach ( array( 'title', 'content' ) as $prop ) {
					// Check question title and content.
					$this->assertStringContainsString(
						$sent['updates']['sections'][0]['lessons'][$li]['quiz']['questions'][$lq][$prop],
						llms_get_post( $question['id'] )->get( $prop, true ),
						$prop
					);
					$this->assertStringNotContainsString(
						$this->__return_filtered(),
						llms_get_post( $question['id'] )->get( $prop, true ),
						$prop
					);
				}
				$lq++;
			}
			$li++;
		}
	}

	/**
	 * Test a lesson is correctly "moved" into a brand new section :)
	 *
	 * @since 5.1.3
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 *              Replaced the call to the deprecated `LLMS_Lesson::set_parent_course()` method with `LLMS_Lesson::set( 'parent_course', $course_id )`.
	 *
	 * @return void
	 */
	public function test_move_lesson_in_a_brand_new_section() {

		// Create a Course with a Lesson.
		$course = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		) );
		$lesson = $course->get_lessons()[0];

		// Create a section.
		$section_id = $this->factory->post->create( array( 'post_type' => 'section' ) );
		$section    = llms_get_post( $section_id );
		// Add the section to the course above.
		$section->set( 'parent_course', $course->get( 'id' ) );

		// Simulate the course lesson moved from its section to the brand new one.
		// Build builder data.
		$lessons_data_from_builder = array(
			array(
            	'parent_section' => 'temp_108', // temp parent section.
				'id'             => $lesson->get( 'id' ),
			),
		);

		LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_lessons',
			array(
				$lessons_data_from_builder,
				$section // The just created section parent.
			)
		);

		// Check lesson parents.
		$this->assertEquals( $course->get( 'id' ), $lesson->get( 'parent_course' ) );
		$this->assertEquals( $section->get( 'id' ), $lesson->get_parent_section() );

	}

	/**
	 * Catch wp_die() called by ajax methods & store the output buffer contents for use later.
	 *
	 * The same method is used in LLMS_Test_AJAX_Handler.
	 * @since 4.16.0
	 *
	 * @param string $msg Die msg.
	 * @return void
	 */
	public function _wp_die_handler( $msg ) {
		$this->last_response = ob_get_clean();
		throw new WPAjaxDieContinueException( $msg );
	}

}
