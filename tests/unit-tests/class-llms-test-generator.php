<?php
/**
 * LLMS Generator Tests
 *
 * @group generator
 *
 * @since Unknown
 * @since 3.36.3 Add tests for `is_generator_valid()` and `set_generator()` methods.
 *              Split `is_error()` method tests into multiple tests.
 * @since 3.37.4 Don't test against core metadata.
 */
class LLMS_Test_Generator extends LLMS_UnitTestCase {

	/**
	 * Test generate method.
	 *
	 * @since Unknown.
	 * @since [version] Ignore core custom field data for custom data assertions.
	 *
	 * @return void
	 */
	public function test_generate() {

		$course = $this->get_mock_course_array( 1, 3, 5, 1, 5 );
		$course['author'] = array(
			'email' => 'test@test.tld',
			'id' => 12345,
		);
		$course['categories'] = array( 'cat' );
		$course['tags'] = array( 'tag1', 'tag2' );
		$course['tracks'] = array( 'track' );
		$course['difficulty'] = 'hard';
		$course['access_plans'] = array(
			array(
				'title' => 'plan1'
			),
			array(
				'title' => 'plan2'
			),
		);

		$course['custom'] = array(
			'customdata' => array( 'yes' ),
			'customdata2' => array( 'no', 'yes', 'maybe' ),
			'customdata3' => array( serialize( array( 'no', 'yes', 'maybe' ) ) ),
		);

		$gen = new LLMS_Generator( $course );
		$gen->set_generator( 'LifterLMS/SingleCourseGenerator' );
		$gen->set_default_post_status( 'publish' );
		$gen->generate();

		$this->assertEquals( array(
			'authors' => 1,
			'courses' => 1,
			'sections' => 3,
			'lessons' => 15,
			'quizzes' => 3,
			'questions' => 15,
			'terms' => 5,
			'plans' => 2,
		), $gen->get_results() );

		// ensure custom data is properly added
		$courses = $gen->get_generated_courses();
		$custom = get_post_custom( $courses[0] );
		unset( $custom['_llms_instructors'] ); // Ignore core custom data.
		$this->assertEquals( $course['custom'], $custom );

	}

	/**
	 * Test is_error() method: no generator supplied.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_no_generator() {

		$gen = new LLMS_Generator( array() );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

	}

	/**
	 * Test is_error() method: valid generator but no data to generate.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_no_data() {

		$gen = new LLMS_Generator( array() );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

	}

	/**
	 * Test is_error() method: valid generator but data formatted improperly.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_invalid_data_format() {

		$gen = new LLMS_Generator( array( 'title' => 'course title' ) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

	}

	/**
	 * Test is_error() method: not an error
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_error_not_an_error() {

		$gen = new LLMS_Generator( array( 'title' => 'course title' ) );
		$gen->set_generator( 'LifterLMS/SingleCourseExporter' );
		$gen->generate();
		$this->assertFalse( $gen->is_error() );

	}

	/**
	 * Test is_generator_valid() method: valid generators.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_generator_valid_valid_generators() {

		$gen = new LLMS_Generator( array() );
		$list = array_keys( LLMS_Unit_Test_Util::call_method( $gen, 'get_generators' ) );
		foreach ( $list as $name ) {
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $gen, 'is_generator_valid', array( $name ) ) );
		}

	}

	/**
	 * Test is_generator_valid() method: invalid generators.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_is_generator_valid_invalid() {

		$gen = new LLMS_Generator( array() );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'is_generator_valid', array( 'fake' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $gen, 'is_generator_valid', array( 'LifterLMS/SingleFakeExporter' ) ) );

	}

	/**
	 * Test set_generator(): interpret from raw missing generator.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_interpret_from_raw_missing() {

		$gen = new LLMS_Generator( array() );
		$err = $gen->set_generator();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'missing-generator', $err );

	}

	/**
	 * Test set_generator(): interpret from raw invalid generator.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_interpret_from_raw_invalid() {

		$gen = new LLMS_Generator( array(
			'_generator' => 'Fake/Generator',
		) );
		$err = $gen->set_generator();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'invalid-generator', $err );

	}

	/**
	 * Test set_generator(): interpret from raw success.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_interpret_from_raw_success() {

		$gen = new LLMS_Generator( array(
			'_generator' => 'LifterLMS/SingleCourseExporter',
		) );
		$this->assertEquals( 'LifterLMS/SingleCourseExporter', $gen->set_generator() );

	}

	/**
	 * Test set_generator(): explicitly supplied invalid.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_explicit_invalid() {

		$gen = new LLMS_Generator( array() );
		$err = $gen->set_generator( 'Fake/Generator' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'invalid-generator', $err );

	}

	/**
	 * Test set_generator(): explicitly supplied success.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_set_generator_explicit_success() {

		$gen = new LLMS_Generator( array() );
		$this->assertEquals( 'LifterLMS/SingleCourseExporter', $gen->set_generator( 'LifterLMS/SingleCourseExporter' ) );

	}

}
