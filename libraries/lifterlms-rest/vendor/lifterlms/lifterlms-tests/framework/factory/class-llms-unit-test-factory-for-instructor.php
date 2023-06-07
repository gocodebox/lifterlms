<?php

/**
 * Unit test factory for instructors.
 *
 * Note: The below `@method` notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method LLMS_Instructor create_and_get( $args = array(), $generation_definitions = null )
 */
class LLMS_Unit_Test_Factory_For_Instructor extends WP_UnitTest_Factory_For_User {

	/**
	 * Create a new Instructor Factory.
	 *
	 * This is essentially the WP Core User Factory except it creates users with the "Instructor" role
	 * and Returns an LLMS_Student object with gets
	 *
	 * @param object $factory Global Factory.
	 */
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'user_login' => new WP_UnitTest_Generator_Sequence( 'Instructor %s' ),
			'user_pass'  => 'password',
			'user_email' => new WP_UnitTest_Generator_Sequence( 'instructor_%s@example.org' ),
			'role' => 'instructor',
		);
	}

	/**
	 * Retrieve student by ID
	 *
	 * @param   int   $user_id WP User ID.
	 * @return  LLMS_Instructor
	 */
	public function get_object_by_id( $user_id ) {
		return new LLMS_Instructor( $user_id );
	}

}
