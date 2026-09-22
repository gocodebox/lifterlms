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

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

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
	 * Test that a lesson cannot be moved into a course the builder is not authorized to edit.
	 *
	 * @since 10.0.4
	 *
	 * @return void
	 */
	public function test_update_lessons_cannot_move_to_another_course() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Course the builder is editing, with a section + lesson.
		$course_a  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		) );
		$section_a = $course_a->get_sections()[0];
		$lesson_a  = $course_a->get_lessons()[0];

		// A different course with its own section.
		$course_b  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );
		$section_b = $course_b->get_sections()[0];

		// Craft builder data attempting to move the lesson into course B / section B.
		$lessons_data = array(
			array(
				'id'             => $lesson_a->get( 'id' ),
				'parent_course'  => $course_b->get( 'id' ),
				'parent_section' => $section_b->get( 'id' ),
			),
		);

		LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_lessons',
			array( $lessons_data, $section_a, $course_a->get( 'id' ) )
		);

		// The lesson must remain in the authorized course/section.
		$lesson_a = llms_get_post( $lesson_a->get( 'id' ) );
		$this->assertEquals( $course_a->get( 'id' ), $lesson_a->get( 'parent_course' ) );
		$this->assertEquals( $section_a->get( 'id' ), $lesson_a->get_parent_section() );
	}

	/**
	 * Test that a newly created lesson cannot be injected into another course via the builder.
	 *
	 * @since 10.0.4
	 *
	 * @return void
	 */
	public function test_update_lessons_new_lesson_cannot_inject_into_another_course() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Course being edited, with a section.
		$course_a  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );
		$section_a = $course_a->get_sections()[0];

		// A different course with its own section.
		$course_b  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );
		$section_b = $course_b->get_sections()[0];

		$lessons_data = array(
			array(
				'id'             => 'temp_1',
				'title'          => 'New lesson',
				'parent_course'  => $course_b->get( 'id' ),
				'parent_section' => $section_b->get( 'id' ),
			),
		);

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_lessons',
			array( $lessons_data, $section_a, $course_a->get( 'id' ) )
		);

		$new_lesson = llms_get_post( $res[0]['id'] );

		// The new lesson must belong to the authorized course/section, not course B.
		$this->assertEquals( $course_a->get( 'id' ), $new_lesson->get( 'parent_course' ) );
		$this->assertEquals( $section_a->get( 'id' ), $new_lesson->get_parent_section() );
		$this->assertEmpty( $course_b->get_lessons() );
	}

	/**
	 * Test that a quiz's lesson_id is forced to the authorized lesson and cannot be pointed elsewhere.
	 *
	 * @since 10.0.4
	 *
	 * @return void
	 */
	public function test_update_quiz_forces_lesson_id_to_authorized_lesson() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$course = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		) );
		$lesson = $course->get_lessons()[0];

		// A lesson outside the builder context that the quiz must not be pointed at.
		$other_lesson_id = $this->factory->post->create( array( 'post_type' => 'lesson' ) );

		$quiz_data = array(
			'id'        => 'temp_1',
			'title'     => 'Quiz',
			'lesson_id' => $other_lesson_id,
		);

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_quiz',
			array( $quiz_data, $lesson, $course->get( 'id' ) )
		);

		$quiz = llms_get_post( $res['id'] );
		$this->assertEquals( $lesson->get( 'id' ), $quiz->get( 'lesson_id' ) );
	}

	/**
	 * Test that a user who can only edit one course cannot use a builder heartbeat to move or
	 * inject lessons into a different course they are not allowed to edit.
	 *
	 * @since 10.0.4
	 *
	 * @return void
	 */
	public function test_heartbeat_cannot_move_or_inject_lessons_into_unauthorized_course() {

		$user_with_access    = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$user_without_access = $this->factory->user->create( array( 'role' => 'instructor' ) );

		// Course B is owned by a different user.
		wp_set_current_user( $user_without_access );
		$course_b  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );
		$section_b = $course_b->get_sections()[0];

		// Course A is owned by the user performing the save.
		wp_set_current_user( $user_with_access );
		$course_a  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		) );
		$section_a = $course_a->get_sections()[0];
		$lesson_a  = $course_a->get_lessons()[0];

		// The privilege boundary this test depends on.
		$this->assertTrue( current_user_can( 'edit_course', $course_a->get( 'id' ) ) );
		$this->assertFalse( current_user_can( 'edit_course', $course_b->get( 'id' ) ) );

		// Heartbeat for course A that attempts to move the existing lesson and inject a new one into course B.
		$builder_data = array(
			'id'      => $course_a->get( 'id' ),
			'updates' => array(
				'id'       => $course_a->get( 'id' ),
				'sections' => array(
					array(
						'id'      => $section_a->get( 'id' ),
						'lessons' => array(
							array(
								'id'             => $lesson_a->get( 'id' ),
								'parent_course'  => $course_b->get( 'id' ),
								'parent_section' => $section_b->get( 'id' ),
							),
							array(
								'id'             => 'temp_1',
								'title'          => 'New lesson',
								'parent_course'  => $course_b->get( 'id' ),
								'parent_section' => $section_b->get( 'id' ),
							),
						),
					),
				),
			),
		);

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'heartbeat_received',
			array( array(), array( 'llms_builder' => wp_json_encode( $builder_data ) ) )
		);

		$this->assertEquals( 'success', $res['llms_builder']['status'] );

		// The existing lesson stays in course A.
		$lesson_a = llms_get_post( $lesson_a->get( 'id' ) );
		$this->assertEquals( $course_a->get( 'id' ), $lesson_a->get( 'parent_course' ) );
		$this->assertEquals( $section_a->get( 'id' ), $lesson_a->get_parent_section() );

		// Course B gains no lessons from the crafted request.
		$this->assertEmpty( $course_b->get_lessons() );
	}

	/**
	 * Test that update_section refuses to write into a course the current user cannot edit.
	 *
	 * @since 10.0.4
	 *
	 * @return void
	 */
	public function test_update_section_requires_edit_course_capability() {

		$owner = $this->factory->user->create( array( 'role' => 'instructor' ) );
		wp_set_current_user( $owner );
		$course = $this->factory->course->create_and_get( array(
			'sections' => 0,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );

		// A different user without access to the course attempts the save.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'instructor' ) ) );

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_section',
			array( array( 'id' => 'temp_1', 'title' => 'New section' ), $course->get( 'id' ) )
		);

		$this->assertArrayHasKey( 'error', $res );
		$this->assertEmpty( $course->get_sections() );
	}

	/**
	 * Test that update_quiz refuses to write into a course the current user cannot edit.
	 *
	 * @since 10.0.4
	 *
	 * @return void
	 */
	public function test_update_quiz_requires_edit_course_capability() {

		$owner = $this->factory->user->create( array( 'role' => 'instructor' ) );
		wp_set_current_user( $owner );
		$course = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 0,
		) );
		$lesson = $course->get_lessons()[0];

		// A different user without access to the course attempts the save.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'instructor' ) ) );

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_quiz',
			array( array( 'id' => 'temp_1', 'title' => 'New quiz' ), $lesson, $course->get( 'id' ) )
		);

		$this->assertArrayHasKey( 'error', $res );
		$this->assertFalse( $lesson->is_quiz_enabled() );
	}

	/**
	 * Test that an existing question's parent_id is forced to the authorized quiz and cannot be re-parented.
	 *
	 * @since 10.0.6
	 *
	 * @return void
	 */
	public function test_update_questions_forces_parent_id_to_authorized_quiz() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Course A with a quiz + question (the builder context).
		$course_a   = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 1,
		) );
		$quiz_a     = $course_a->get_lessons()[0]->get_quiz();
		$question_a = $quiz_a->get_questions()[0];

		// Course B with its own quiz (the victim).
		$course_b           = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 1,
		) );
		$quiz_b             = $course_b->get_lessons()[0]->get_quiz();
		$quiz_b_count_start = count( $quiz_b->get_questions() );

		// Craft question data attempting to move the question into quiz B.
		$questions_data = array(
			array(
				'id'        => $question_a->get( 'id' ),
				'parent_id' => $quiz_b->get( 'id' ),
				'title'     => 'Injected question',
			),
		);

		LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_questions',
			array( $questions_data, $quiz_a, $course_a->get( 'id' ) )
		);

		// The question stays attached to quiz A.
		$question_a = llms_get_post( $question_a->get( 'id' ) );
		$this->assertEquals( $quiz_a->get( 'id' ), $question_a->get( 'parent_id' ) );

		// Quiz B gains no questions from the crafted request.
		$this->assertEquals( $quiz_b_count_start, count( $quiz_b->get_questions() ) );
	}

	/**
	 * Test that a user who can edit one course cannot use a builder heartbeat to move one of its
	 * questions into a quiz belonging to a course they are not allowed to edit.
	 *
	 * @since 10.0.6
	 *
	 * @return void
	 */
	public function test_heartbeat_cannot_move_question_into_unauthorized_quiz() {

		$user_with_access    = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$user_without_access = $this->factory->user->create( array( 'role' => 'instructor' ) );

		// Course B (victim) is owned by a different user.
		wp_set_current_user( $user_without_access );
		$course_b           = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 1,
		) );
		$quiz_b             = $course_b->get_lessons()[0]->get_quiz();
		$quiz_b_count_start = count( $quiz_b->get_questions() );

		// Course A is owned by the user performing the save.
		wp_set_current_user( $user_with_access );
		$course_a   = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 1,
			'quizzes'  => 1,
		) );
		$section_a  = $course_a->get_sections()[0];
		$lesson_a   = $course_a->get_lessons()[0];
		$quiz_a     = $lesson_a->get_quiz();
		$question_a = $quiz_a->get_questions()[0];

		// The privilege boundary this test depends on.
		$this->assertTrue( current_user_can( 'edit_course', $course_a->get( 'id' ) ) );
		$this->assertFalse( current_user_can( 'edit_course', $course_b->get( 'id' ) ) );

		// Heartbeat for course A that attempts to re-parent the question into quiz B.
		$builder_data = array(
			'id'      => $course_a->get( 'id' ),
			'updates' => array(
				'id'       => $course_a->get( 'id' ),
				'sections' => array(
					array(
						'id'      => $section_a->get( 'id' ),
						'lessons' => array(
							array(
								'id'   => $lesson_a->get( 'id' ),
								'quiz' => array(
									'id'        => $quiz_a->get( 'id' ),
									'lesson_id' => $lesson_a->get( 'id' ),
									'questions' => array(
										array(
											'id'        => $question_a->get( 'id' ),
											'parent_id' => $quiz_b->get( 'id' ),
											'title'     => 'Injected question',
										),
									),
								),
							),
						),
					),
				),
			),
		);

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'heartbeat_received',
			array( array(), array( 'llms_builder' => wp_json_encode( $builder_data ) ) )
		);

		$this->assertEquals( 'success', $res['llms_builder']['status'] );

		// The question stays attached to quiz A.
		$question_a = llms_get_post( $question_a->get( 'id' ) );
		$this->assertEquals( $quiz_a->get( 'id' ), $question_a->get( 'parent_id' ) );

		// Quiz B gains no questions from the crafted request.
		$this->assertEquals( $quiz_b_count_start, count( $quiz_b->get_questions() ) );
	}

	/**
	 * Test attaching an orphan lesson via update_lessons while also updating title and slug.
	 *
	 * @since 10.1.1
	 *
	 * @return void
	 */
	public function test_update_lessons_can_attach_orphan_with_title_and_name() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$course  = $this->factory->course->create_and_get( array(
			'sections' => 1,
			'lessons'  => 0,
			'quizzes'  => 0,
		) );
		$section = $course->get_sections()[0];

		// Orphan lesson (no parent course/section) with a quiz, matching the attach path.
		$orphan_id = $this->factory->post->create( array(
			'post_type'  => 'lesson',
			'post_title' => 'Orphan Lesson',
			'post_name'  => 'orphan-lesson',
		) );
		$orphan    = llms_get_post( $orphan_id );
		$this->assertTrue( $orphan->is_orphan() );

		$quiz = new LLMS_Quiz( 'new', array( 'post_title' => 'Orphan Quiz' ) );
		$orphan->set( 'quiz', $quiz->get( 'id' ) );
		$orphan->set( 'quiz_enabled', 'yes' );
		$quiz->set( 'lesson_id', $orphan->get( 'id' ) );

		$lessons_data = array(
			array(
				'id'             => $orphan->get( 'id' ),
				'title'          => 'Attached Lesson Title',
				'name'           => 'attached-lesson-slug',
				'parent_course'  => $course->get( 'id' ),
				'parent_section' => $section->get( 'id' ),
				// Intentionally omit `order` — partial syncs after attach can leave it out,
				// and the builder's section lesson query requires `_llms_order`.
			),
		);

		$res = LLMS_Unit_Test_Util::call_method(
			$this->main,
			'update_lessons',
			array( $lessons_data, $section, $course->get( 'id' ) )
		);

		$this->assertArrayNotHasKey( 'error', $res[0] );

		$orphan = llms_get_post( $orphan->get( 'id' ) );
		$this->assertEquals( $course->get( 'id' ), $orphan->get( 'parent_course' ) );
		$this->assertEquals( $section->get( 'id' ), $orphan->get( 'parent_section' ) );
		$this->assertEquals( 'Attached Lesson Title', $orphan->get( 'title', true ) );
		$this->assertEquals( 'attached-lesson-slug', $orphan->get( 'name' ) );
		$this->assertNotEmpty( $orphan->get( 'order' ) );
		$this->assertFalse( $orphan->is_orphan() );

		// Fresh section instance — builder reload queries lessons by `_llms_parent_section` + `_llms_order`.
		$section         = llms_get_post( $section->get( 'id' ) );
		$section_lessons = $section->get_lessons( 'ids' );
		$this->assertContains( $orphan->get( 'id' ), $section_lessons );
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
