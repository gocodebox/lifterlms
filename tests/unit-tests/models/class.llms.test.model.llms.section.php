<?php
/**
 * Tests for LifterLMS Course Model
 * @group    LLMS_Section
 * @group    LLMS_Post_Model
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_LLMS_Section extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Section';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'section';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_properties() {
		return array(
			'order' => 'absint',
			'parent_course' => 'absint',
		);
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_data() {
		return array(
			'order' => 1,
			'parent_course' => 12345,
		);
	}

	/**
	 * the the count_elements() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_count_elements() {

		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );
		$this->assertEquals( 0, $section->count_elements() );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 5, 0 )[0] );
		$section = llms_get_post( $course->get_sections( 'ids' )[0] );
		$this->assertEquals( 5, $section->count_elements() );

	}

	/**
	 * the the get_course() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_course() {

		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );
		$this->assertNull( $section->get_course() );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 5, 0 )[0] );
		$section = llms_get_post( $course->get_sections( 'ids' )[0] );
		$this->assertTrue( is_a( $section->get_course(), 'LLMS_Course' ) );

	}

	// public function test_get_next() {

		// $section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );
		// $this->assertNull( $section->get_course() );
		// var_dump( $section->get_next() );

	// }

	/**
	 * the the get_percent_complete() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_percent_complete() {

		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );
		$this->assertEquals( 0, $section->get_percent_complete() );

		$course = llms_get_post( $this->generate_mock_courses( 1, 4, 4, 0, 0 )[0] );
		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		$uid = $student->get( 'id' );

		foreach ( $course->get_sections() as $i => $section )  {

			$perc = ( $i + 1 ) * 25;

			// get student by ID
			$this->assertEquals( 0, $section->get_percent_complete( $uid ) );

			// get from current user
			$this->assertEquals( 0, $section->get_percent_complete() );

			// complete 50% of the lessons in the section
			$this->complete_courses_for_student( $uid, $course->get( 'id' ), ( $perc / 2 ) + ( $i * 12.5 ) );
			$this->assertEquals( 50, $section->get_percent_complete( $uid ) );

			// complete the entire section
			$this->complete_courses_for_student( $uid, $course->get( 'id' ), $perc );
			$this->assertEquals( 100, $section->get_percent_complete( $uid ) );

			// check as the current user
			wp_set_current_user( $uid );
			$this->assertEquals( 100, $section->get_percent_complete() );
			wp_set_current_user( null ); // reset

		}

	}

	// public function test_get_previous() {}

	/**
	 * the the get_lessons() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_lessons() {

		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );
		$lessons = $section->get_lessons( 'ids' );
		$this->assertEquals( 0, count( $lessons ) );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 4, 0, 0 )[0] );
		$section = llms_get_post( $course->get_sections( 'ids' )[0] );

		// get just ids
		$lessons = $section->get_lessons( 'ids' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $id ) {
			$this->assertTrue( is_numeric( $id ) );
		}, $lessons );

		// wp post objects
		$lessons = $section->get_lessons( 'posts' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $post ) {
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
		}, $lessons );

		// lesson objects
		$lessons = $section->get_lessons( 'sections' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $lesson ) {
			$this->assertTrue( is_a( $lesson, 'LLMS_Lesson' ) );
		}, $lessons );

	}

	/**
	 * the the get_children_lessons() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_children_lessons() {

		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );
		$lessons = $section->get_lessons( 'ids' );
		$this->assertEquals( 0, count( $lessons ) );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 4, 0, 0 )[0] );
		$section = llms_get_post( $course->get_sections( 'ids' )[0] );

		// wp post objects
		$lessons = $section->get_lessons( 'posts' );
		$this->assertEquals( 4, count( $lessons ) );
		array_map( function( $post ) {
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
		}, $lessons );

	}

}
