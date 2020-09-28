<?php
/**
 * LLMS_Generator_Courses Tests
 *
 * @package LifterLMS/Tests
 *
 * @group generator
 * @group generator_courses
 *
 * @since [version]
 */
class LLMS_Test_Generator_Courses extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$gen = new LLMS_Generator( array() );
		$this->main = $gen->get_generator( 'courses' );

	}

	protected function get_raw() {

		global $lifterlms_tests;
		return json_decode( file_get_contents( $lifterlms_tests->assets_dir . 'import-with-quiz.json' ), true );


	}

	/**
	 * Test add_course_terms()
	 *
	 * @since [version]
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
	 * Test clone_lesson()
	 *
	 * @since [version]
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

	}

	public function test_generate_course() {}

	public function test_generate_courses() {}

	/**
	 * Test create_access_plan()
	 *
	 * @since [version]
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
	 * Test create_course()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_course() {

		$course_actions  = did_action( 'llms_generator_new_course' );
		$section_actions = did_action( 'llms_generator_new_section' );
		$lesson_actions  = did_action( 'llms_generator_new_lesson' );
		$quiz_actions    = did_action( 'llms_generator_new_quiz' );

		$raw    = $this->get_raw();
		$id     = LLMS_Unit_Test_Util::call_method( $this->main, 'create_course', array( $raw ) );
		$course = llms_get_post( $id );

		$this->assertTrue( $course instanceof LLMS_Course );

		$this->assertEquals( $raw['title'], $course->get( 'title' ) );
		$this->assertEquals( $raw['id'], $course->get( 'generated_from_id' ) );

		$this->assertEquals( ++$course_actions,  did_action( 'llms_generator_new_course' ) );
		$this->assertEquals( ++$section_actions,  did_action( 'llms_generator_new_section' ) );
		$this->assertEquals( ++$quiz_actions,  did_action( 'llms_generator_new_quiz' ) );

		// Test course structure of generated course is preserved via `handle_prerequisites()`.
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

	public function test_create_lesson() {}

	public function test_create_quiz() {}

	public function test_create_question() {}

	public function test_create_section() {}

	public function test_get_generated_courses() {}

	public function test_handle_prerequisites() {}


	/**
	 * Test maybe_sideload_choice_image() for various conditions where the choice can't be sideloaded.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
