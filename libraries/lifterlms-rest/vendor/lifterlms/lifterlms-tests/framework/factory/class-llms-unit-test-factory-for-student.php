<?php

/**
 * Unit test factory for students.
 *
 * Note: The below `@method` notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method LLMS_Student create_and_get( $args = array(), $generation_definitions = null )
 */
class LLMS_Unit_Test_Factory_For_Student extends WP_UnitTest_Factory_For_User {

	/**
	 * Create a new Student Factory.
	 *
	 * This is essentially the WP Core User Factory except it creates users with the "Student" role
	 * and Returns an LLMS_Student object with gets
	 *
	 * @param object $factory Global Factory.
	 */
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'user_login' => new WP_UnitTest_Generator_Sequence( 'Student %s' ),
			'user_pass'  => 'password',
			'user_email' => new WP_UnitTest_Generator_Sequence( 'student_%s@example.org' ),
			'role' => 'student',
		);
	}

	/**
	 * Create a student and enroll it in a list of courses and/or memberships.
	 *
	 * @param   mixed $post_id WP_Post ID of course or membership.
	 * @param   array $args     user generation arguments.
	 * @return  int
	 */
	public function create_and_enroll( $post_id, $args = array() ) {

		$student = $this->create_and_get( $args );
		$student->enroll( $post_id );
		return $student->get_id();

	}

	/**
	 * Create many students and enroll them into a list of courses and/or memberships.
	 *
	 * @param   int    $count    Number of students to create.
	 * @param   mixed $post_id WP_Post ID of course or membership.
	 * @param   array     $args     User generation args.
	 * @return  array
	 */
	public function create_and_enroll_many( $count, $post_id, $args = array() ) {

		$results = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$results[] = $this->create_and_enroll( $post_id, $args );
		}
		return $results;

	}

	/**
	 * Retrieve student by ID
	 *
	 * @param   int   $user_id WP User ID.
	 * @return  LLMS_Student
	 */
	public function get_object_by_id( $user_id ) {
		return new LLMS_Student( $user_id );
	}

}
