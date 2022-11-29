<?php
/**
 * LLMS_Generator_Courses Tests
 *
 * @package LifterLMS/Tests
 *
 * @group generator
 * @group generator_courses
 *
 * @since 4.7.0
 */
class LLMS_Test_Generator_Courses extends LLMS_UnitTestCase {

	/**
	 * Load required class
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-generator-courses.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Generator_Courses();

	}

	/**
	 * Get raw data as an array
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	protected function get_raw( $file = 'import-with-quiz.json' ) {

		global $lifterlms_tests;
		return json_decode( file_get_contents( $lifterlms_tests->assets_dir . $file ), true );

	}

	/**
	 * Test add_course_terms()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_add_course_terms() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$raw       = array(
			'categories' => array( 'cat term' ),
			'difficulty' => array( 'difficulty term', '' ),
			'tags' => array( 'tags term', 'another tag' ),
			'tracks' => array( 'tracks term' ),
		);

		$actions = did_action( 'llms_generator_new_term' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'add_course_terms', array( $course_id, $raw ) );

		// 5 terms created.
		$this->assertEquals( 5, did_action( 'llms_generator_new_term' ) );

		// Match.
		$this->assertEqualSets( $raw['categories'], wp_get_post_terms( $course_id, 'course_cat', array( 'fields' => 'names' ) ) );
		$this->assertEqualSets( array( $raw['difficulty'][0] ), wp_get_post_terms( $course_id, 'course_difficulty', array( 'fields' => 'names' ) ) );
		$this->assertEqualSets( $raw['tags'], wp_get_post_terms( $course_id, 'course_tag', array( 'fields' => 'names' ) ) );
		$this->assertEqualSets( $raw['tracks'], wp_get_post_terms( $course_id, 'course_track', array( 'fields' => 'names' ) ) );


	}

	/**
	 * Test clone_course()
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_clone_course() {

		$raw = array(
			'title'   => 'Sample Course',
			'content' => 'Content',
		);

		$id = $this->main->clone_course( $raw );
		$this->assertTrue( is_numeric( $id ) );
		$post = get_post( $id );
		$this->assertEquals( 'course', $post->post_type );
		$this->assertEquals( 'Sample Course (Clone)', $post->post_title );
		$this->assertEquals( 'Content', $post->post_content );
		$this->assertEquals( 'draft', $post->post_status );

	}

	/**
	 * Test clone_lesson()
	 *
	 * @since 4.7.0
	 * @since 4.13.0 Add check against post status.
	 *
	 * @return void
	 */
	public function test_clone_lesson() {

		$raw = array(
			'title'   => 'Sample Lesson',
			'content' => 'Content',
		);

		$id = $this->main->clone_lesson( $raw );
		$this->assertTrue( is_numeric( $id ) );
		$post = get_post( $id );
		$this->assertEquals( 'lesson', $post->post_type );
		$this->assertEquals( 'Sample Lesson (Clone)', $post->post_title );
		$this->assertEquals( 'Content', $post->post_content );
		$this->assertEquals( 'draft', $post->post_status );

	}

	/**
	 * Test generate_course()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_generate_course() {

		$raw = $this->get_raw();
		$res = $this->main->generate_course( $raw );
		$this->assertTrue( is_numeric( $res ) );
		$this->assertEquals( 'course', get_post_type( $res ) );

	}

	/**
	 * Test generate_courses() when with missing raw course data
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_generate_courses_missing_courses() {

		$this->setExpectedException( Exception::class, 'Raw data is missing the required "courses" array.', 2000 );
		$this->main->generate_courses( array() );

	}

	/**
	 * Test generate_courses() when with invalid raw course data
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_generate_courses_invalid_courses() {

		$this->setExpectedException( Exception::class, 'The raw "courses" item must be an array.', 2001 );
		$this->main->generate_courses( array( 'courses' => 'invalid' ) );

	}

	/**
	 * Test generate_courses()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_generate_courses() {

		$res = $this->main->generate_courses( array(
			'courses' => array(
				array(
					'title' => 'Course 0',
				),
				array(
					'title' => 'Course 1',
				),
			),
		) );

		foreach ( $res as $i => $id ) {
			$this->assertEquals( 'course', get_post_type( $id ) );
			$this->assertEquals( sprintf( 'Course %d', $i ), get_the_title( $id ) );
		}

	}

	/**
	 * Test create_access_plan()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_access_plan() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$raw       = array(
			'id'      => 987,
			'author'  => array(
				'id' => $this->factory->user->create(),
			),
			'title'             => 'Generated Access Plan',
			'content'           => 'Content',
			'status'            => 'publish',
			'access_expiration' => 'lifetime',
			'availability'      => 'open',
			'enroll_text'       => 'Join',
			'is_free'           => 'yes',
		);

		$id   = LLMS_Unit_Test_Util::call_method( $this->main, 'create_access_plan', array( $raw, $course_id ) );
		$plan = llms_get_post( $id );

		$this->assertTrue( $plan instanceof LLMS_Access_Plan );
		$this->assertEquals( 'llms_access_plan', get_post_type( $id ) );

		$this->assertEquals( $raw['author']['id'], $plan->get( 'author' ) );
		$this->assertEquals( $raw['title'], $plan->get( 'title' ) );
		$this->assertEquals( $raw['content'], $plan->get( 'content', true ) );
		$this->assertEquals( $raw['status'], $plan->get( 'status' ) );
		$this->assertEquals( $raw['access_expiration'], $plan->get( 'access_expiration' ) );
		$this->assertEquals( $raw['availability'], $plan->get( 'availability' ) );
		$this->assertEquals( $raw['enroll_text'], $plan->get( 'enroll_text' ) );
		$this->assertEquals( $raw['is_free'], $plan->get( 'is_free' ) );
		$this->assertEquals( $raw['id'], $plan->get( 'generated_from_id' ) );

	}

	/**
	 * Test create_course().
	 *
	 * @since 4.7.0
	 * @since 4.12.0 Only test properties that exist on the raw data arrays.
	 * @since [version] Check that properties on the generated course correctly
	 *               refer to the course id rather than the raw data id.
	 *
	 * @return void
	 */
	public function test_create_course() {

		$course_actions   = did_action( 'llms_generator_new_course' );
		$plan_actions     = did_action( 'llms_generator_new_access_plan' );
		$section_actions  = did_action( 'llms_generator_new_section' );
		$lesson_actions   = did_action( 'llms_generator_new_lesson' );
		$quiz_actions     = did_action( 'llms_generator_new_quiz' );
		$question_actions = did_action( 'llms_generator_new_question' );

		$raw    = $this->get_raw();
		$id     = LLMS_Unit_Test_Util::call_method( $this->main, 'create_course', array( $raw ) );
		$course = llms_get_post( $id );

		$this->assertTrue( $course instanceof LLMS_Course );

		// Default post properties.
		$this->assertEquals( $raw['title'], $course->get( 'title' ) );
		$this->assertEquals( $raw['content'], $course->get( 'content', true ) );

		// Store the original ID.
		$this->assertEquals( $raw['id'], $course->get( 'generated_from_id' ) );

		/**
		 * Properties which refer to the new id rather than the
		 * `$generated_from_id`.
		 */
		$replace_id_props = array(
			'course_closed_message',
			'course_opens_message',
			'enrollment_closed_message',
			'enrollment_opens_message',
		);

		$find    = 'id="' . $raw['id'] . '"';
		$replace = 'id="' . $course->get( 'id' ) . '"';

		// Test meta props are set.
		foreach ( array_keys( LLMS_Unit_Test_Util::get_private_property_value( $course, 'properties' ) ) as $prop ) {
			if ( isset( $raw[ $prop ] ) ) {
				if ( in_array( $prop, $replace_id_props, true ) ) {
					$this->assertEquals( str_replace( $find, $replace, $raw[$prop]  ), $course->get( $prop ) );
				} else {
					$this->assertEquals( $raw[ $prop ], $course->get( $prop ) );
				}
			}
		}

		// Test custom values.
		foreach ( $raw['custom'] as $key => $vals ) {
			$this->assertEquals( $vals, get_post_meta( $course->get( 'id' ), $key ) );
		}

		// Check taxonomies.
		$this->assertEquals( $raw['difficulty'], $course->get_difficulty() );
		$this->assertEquals( $raw['categories'], $course->get_categories() );
		$this->assertEquals( $raw['tags'], $course->get_tags() );
		$this->assertEquals( $raw['tracks'], $course->get_tracks() );

		// Calls actions (noting that children have been created).
		$this->assertEquals( ++$course_actions, did_action( 'llms_generator_new_course' ) );
		$this->assertEquals( ++$plan_actions, did_action( 'llms_generator_new_access_plan' ) );
		$this->assertEquals( ++$section_actions, did_action( 'llms_generator_new_section' ) );
		$this->assertEquals( ++$lesson_actions, did_action( 'llms_generator_new_lesson' ) );
		$this->assertEquals( ++$quiz_actions, did_action( 'llms_generator_new_quiz' ) );
		$this->assertEquals( ++$question_actions, did_action( 'llms_generator_new_question' ) );

		// Test course structure of generated course is preserved.
		foreach ( $course->get_sections() as $section ) {

			$this->assertEquals( $id, $section->get( 'parent_course' ) );

			foreach ( $section->get_lessons() as $lesson ) {

				$this->assertEquals( $id, $lesson->get( 'parent_course' ) );
				$this->assertEquals( $section->get( 'id' ), $lesson->get( 'parent_section' ) );

				$quiz = $lesson->get_quiz();
				$this->assertEquals( $lesson->get( 'id' ), $quiz->get( 'lesson_id' ) );

			}
		}

	}

	/**
	 * Test create_course() error.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_course_error() {

		// Force post creation to fail.
		$handler = function( $args ) {
			return array();
		};
		add_filter( 'llms_new_course', $handler );

		$this->setExpectedException( Exception::class, 'Error creating the course post object.', 1000 );
		LLMS_Unit_Test_Util::call_method( $this->main, 'create_course', array( array( 'title' => '' ) ) );

		remove_filter( 'llms_new_course', $handler );

	}

	/**
	 * Test create_lesson()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_lesson() {

		$lesson_actions   = did_action( 'llms_generator_new_lesson' );
		$quiz_actions     = did_action( 'llms_generator_new_quiz' );
		$question_actions = did_action( 'llms_generator_new_question' );

		$raw     = $this->get_raw()['sections'][0]['lessons'][0];
		$order   = 3;
		$course  = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 0 ) );
		$section = $course->get_sections()[0];
		$id      = LLMS_Unit_Test_Util::call_method( $this->main, 'create_lesson', array( $raw, $order, $section->get( 'id' ), $course->get( 'id' ) ) );
		$lesson  = llms_get_post( $id );

		$this->assertTrue( $lesson instanceof LLMS_Lesson );

		// Default post properties.
		$this->assertEquals( $raw['title'], $lesson->get( 'title' ) );
		$this->assertEquals( $raw['content'], $lesson->get( 'content', true ) );

		// Test meta props are set.
		foreach ( array_keys( LLMS_Unit_Test_Util::get_private_property_value( $lesson, 'properties' ) ) as $prop ) {
			// This data is not based off raw.
			if ( in_array( $prop, array( 'order', 'parent_course', 'parent_section', 'quiz' ), true ) ) {
				continue;
			}
			$this->assertEquals( $raw[ $prop ], $lesson->get( $prop ), $prop );
		}

		// Test custom values.
		foreach ( $raw['custom'] as $key => $vals ) {
			$this->assertEquals( $vals, get_post_meta( $lesson->get( 'id' ), $key ) );
		}

		// Order.
		$this->assertEquals( $order, $lesson->get( 'order' ) );

		// Store the original ID.
		$this->assertEquals( $raw['id'], $lesson->get( 'generated_from_id' ) );

		// Calls actions (noting that children have been created).
		$this->assertEquals( ++$lesson_actions, did_action( 'llms_generator_new_lesson' ) );
		$this->assertEquals( ++$quiz_actions, did_action( 'llms_generator_new_quiz' ) );
		$this->assertEquals( ++$question_actions, did_action( 'llms_generator_new_question' ) );

		// Relationships.
		$this->assertEquals( $course->get( 'id' ), $lesson->get( 'parent_course' ) );
		$this->assertEquals( $section->get( 'id' ), $lesson->get( 'parent_section' ) );

		$quiz = $lesson->get_quiz();
		$this->assertEquals( $id, $quiz->get( 'lesson_id' ) );

	}

	public function test_create_quiz() {

		$quiz_actions     = did_action( 'llms_generator_new_quiz' );
		$question_actions = did_action( 'llms_generator_new_question' );

		$raw              = $this->get_raw()['sections'][0]['lessons'][0]['quiz'];
		$lesson_id        = $this->factory->post->create( array( 'post_type' => 'lesson' ) );
		$raw['lesson_id'] = $lesson_id;

		$id   = LLMS_Unit_Test_Util::call_method( $this->main, 'create_quiz', array( $raw ) );
		$quiz = llms_get_post( $id );

		$this->assertTrue( $quiz instanceof LLMS_Quiz );

		// Default post properties.
		$this->assertEquals( $raw['title'], $quiz->get( 'title' ) );
		$this->assertEquals( $raw['content'], $quiz->get( 'content', true ) );

		// Test meta props are set.
		foreach ( array_keys( LLMS_Unit_Test_Util::get_private_property_value( $quiz, 'properties' ) ) as $prop ) {
			// This data is not based off raw.
			if ( in_array( $prop, array( 'order', 'parent_course', 'parent_section', 'quiz' ), true ) ) {
				continue;
			}
			$this->assertEquals( $raw[ $prop ], $quiz->get( $prop ), $prop );
		}

		// Test custom values.
		foreach ( $raw['custom'] as $key => $vals ) {
			$this->assertEquals( $vals, get_post_meta( $quiz->get( 'id' ), $key ) );
		}

		// Store the original ID.
		$this->assertEquals( $raw['id'], $quiz->get( 'generated_from_id' ) );

		// Calls actions (noting that children have been created).
		$this->assertEquals( ++$quiz_actions, did_action( 'llms_generator_new_quiz' ) );
		$this->assertEquals( ++$question_actions, did_action( 'llms_generator_new_question' ) );

		// Relationships.
		$this->assertEquals( $lesson_id, $quiz->get( 'lesson_id' ) );

	}

	/**
	 * Test create_question()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_question() {

		$question_actions = did_action( 'llms_generator_new_question' );

		$raw     = $this->get_raw()['sections'][0]['lessons'][0]['quiz']['questions'][0];
		$quiz_id = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$quiz    = llms_get_post( $quiz_id );

		$id       = LLMS_Unit_Test_Util::call_method( $this->main, 'create_question', array( $raw, $quiz->questions(), $this->factory->user->create() ) );
		$question = llms_get_post( $id );

		$this->assertTrue( $question instanceof LLMS_Question );

		// Default post properties.
		$this->assertEquals( $raw['title'], $question->get( 'title' ) );
		$this->assertEquals( $raw['content'], $question->get( 'content', true ) );

		// Test meta props are set.
		foreach ( array_keys( LLMS_Unit_Test_Util::get_private_property_value( $question, 'properties' ) ) as $prop ) {
			// This data is not based off raw.
			if ( in_array( $prop, array( 'parent_id' ), true ) ) {
				continue;
			}
			$this->assertEquals( $raw[ $prop ], $question->get( $prop ), $prop );
		}

		// Store the original ID.
		$this->assertEquals( $raw['id'], $question->get( 'generated_from_id' ) );

		// Calls actions (noting that children have been created).
		$this->assertEquals( ++$question_actions, did_action( 'llms_generator_new_question' ) );

		// Relationships.
		$this->assertEquals( $quiz_id, $question->get( 'parent_id' ) );

		// Check choices.
		foreach ( $question->get_choices() as $i => $choice ) {

			$this->assertEquals( $id, $choice->get_question_id() );

			$this->assertEquals( $raw['choices'][ $i ]['choice'], $choice->get_choice() );
			$this->assertEquals( $raw['choices'][ $i ]['choice_type'], $choice->get( 'choice_type' ) );
			$this->assertEquals( $raw['choices'][ $i ]['correct'], $choice->get( 'correct' ) );
			$this->assertEquals( $raw['choices'][ $i ]['marker'], $choice->get( 'marker' ) );

		}

	}

	/**
	 * Test create_question() during a post creation error
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_question_error() {

		$quiz_id = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$quiz    = llms_get_post( $quiz_id );

		// Force post creation to fail.
		$handler = function( $args ) {
			return array();
		};
		add_filter( 'llms_new_question', $handler );

		$this->setExpectedException( Exception::class, 'Error creating the question post object.', 1000 );
		LLMS_Unit_Test_Util::call_method( $this->main, 'create_question', array( array( 'title' => '' ), $quiz->questions(), $this->factory->user->create() ) );

		remove_filter( 'llms_new_question', $handler );

	}

	/**
	 * Test create_section()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_create_section() {

		$section_actions  = did_action( 'llms_generator_new_section' );
		$lesson_actions   = did_action( 'llms_generator_new_lesson' );
		$quiz_actions     = did_action( 'llms_generator_new_quiz' );
		$question_actions = did_action( 'llms_generator_new_question' );

		$raw     = $this->get_raw()['sections'][0];
		$order   = 20;
		$course  = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$id      = LLMS_Unit_Test_Util::call_method( $this->main, 'create_section', array( $raw, $order, $course ) );
		$section = llms_get_post( $id );

		$this->assertTrue( $section instanceof LLMS_Section );

		// Default post properties.
		$this->assertEquals( $raw['title'], $section->get( 'title' ) );

		// These are the only important pieces of meta data.
		$this->assertEquals( $course, $section->get( 'parent_course' ) );
		$this->assertEquals( $order, $section->get( 'order' ) );

		// Store the original ID.
		$this->assertEquals( $raw['id'], $section->get( 'generated_from_id' ) );

		// Calls actions (noting that children have been created).
		$this->assertEquals( ++$section_actions, did_action( 'llms_generator_new_section' ) );
		$this->assertEquals( ++$lesson_actions, did_action( 'llms_generator_new_lesson' ) );
		$this->assertEquals( ++$quiz_actions, did_action( 'llms_generator_new_quiz' ) );
		$this->assertEquals( ++$question_actions, did_action( 'llms_generator_new_question' ) );

		// Test course structure of generated course is preserved.
		foreach ( $section->get_lessons() as $lesson ) {

			$this->assertEquals( $course, $lesson->get( 'parent_course' ) );
			$this->assertEquals( $section->get( 'id' ), $lesson->get( 'parent_section' ) );

			$quiz = $lesson->get_quiz();
			$this->assertEquals( $lesson->get( 'id' ), $quiz->get( 'lesson_id' ) );

		}

	}

	/**
	 * Test handle_prerequisites()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_handle_prerequisites() {

		$raw = $this->get_raw( 'import-with-prerequisites.json' );
		$courses = $this->main->generate_courses( $raw );

		$course = llms_get_post( $courses[0] );

		$this->assertTrue( $course->has_prerequisite( 'course' ) );
		$this->assertEquals( $courses[1], $course->get_prerequisite_id( 'course' ) );

		// Tracks aren't preserved.
		$this->assertFalse( $course->has_prerequisite( 'course_track' ) );

		$lessons = $course->get_lessons();
		$this->assertTrue( $lessons[1]->has_prerequisite() );
		$this->assertEquals( $lessons[0]->get( 'id' ), $lessons[1]->get_prerequisite() );

	}


	/**
	 * Test maybe_sideload_choice_image() for various conditions where the choice can't be sideloaded.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_maybe_sideload_choice_image_disabled() {

		$choice = array(
			'id'     => 'mock',
			'choice' => 'string',
		);

		// The 'choice_type' prop is missing.
		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );

		$choice['choice_type'] = 'text';

		// The 'choice_type' prop is not "image".
		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );

		// Sideloading is disabled.
		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );
		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );
		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

	}

	/**
	 * Test maybe_sideload_choice_image()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_maybe_sideload_choice_image() {

		$choice = array(
			'id'          => 'mock',
			'choice_type' => 'image',
			'choice'      => array(
				'id'  => 123,
				'src' => 'https://raw.githubusercontent.com/gocodebox/lifterlms/trunk/tests/assets/christian-fregnan-unsplash.jpg',
			),
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_sideload_choice_image', array( $choice, 123 ) );

		$this->assertTrue( 123 !== $res['choice']['id'] );
		$this->assertTrue( $choice['choice']['src'] !== $res['choice']['src'] );
		$this->assertEquals( wp_get_attachment_url( $res['choice']['id'] ),  $res['choice']['src'] );

	}


	/**
	 * Test maybe_sideload_choice_image() when an error is encountered during sideloading
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_maybe_sideload_choice_image_error() {

		$choice = array(
			'id'          => 'mock',
			'choice_type' => 'image',
			'choice'      => array(
				'id'  => 123,
				'src' => 'fake.jpg',
			),
		);

		$this->assertEquals( $choice, LLMS_Unit_Test_Util::call_method( $this->main, 'maybe_sideload_choice_image', array( $choice, 123 ) ) );

	}

	/**
	 * Test store_temp_id()
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function test_store_temp_id() {

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$course    = llms_get_post( $course_id );

		$raw = array(
			'id' => 128,
		);

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'store_temp_id', array( $raw, $course ) );

		$this->assertEquals( 128, $res );
		$this->assertEquals( 128, $course->get( 'generated_from_id' ) );

		$this->assertEquals( array( 128 => $course_id ), LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'tempids' )['course'] );

	}

}
