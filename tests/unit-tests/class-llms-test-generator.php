<?php
/**
 * LLMS Generator Tests
 * @group generator
 */

class LLMS_Test_Generator extends LLMS_UnitTestCase {

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
		$this->assertEquals( $course['custom'], get_post_custom( $courses[0] ) );


	}

	public function test_is_error() {

		// no generator
		$gen = new LLMS_Generator( array() );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

		// generator set but no data
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

		// invalid generator format
		$gen = new LLMS_Generator( array( 'title' => 'course title' ) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->generate();
		$this->assertTrue( $gen->is_error() );

		// good
		$gen = new LLMS_Generator( array( 'title' => 'course title' ) );
		$gen->set_generator( 'LifterLMS/SingleCourseExporter' );
		$gen->generate();
		$this->assertFalse( $gen->is_error() );

	}

}
