<?php
/**
 * Tests for LLMS_Abstract_User_Data
 * 
 * @group LLMS_Student
 * @group abstracts
 * @group abstract_user_data
 * 
 * @since 3.9.0
 */
class LLMS_Test_Abstract_User_Data extends LLMS_UnitTestCase {

	/**
	 * Test constructor and get_user_id() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor() {

		$user = $this->factory->user->create_and_get();

		$tests = array(
			(int) $user->ID,
			(string) $user->ID,
			$user,
			new LLMS_Student( $user ),
		);

		$expected = new LLMS_Student( $user );

		foreach ( $tests as $i => $input ) {
			$student = new LLMS_Student( $input );
			$this->assertEquals( $expected, $student, $i );
			$this->assertEquals( $user->ID, $student->get_id(), $i );
			$this->assertEquals( $user, $student->get_user(), $i );
		}

	}

	/**
	 * Test constructor when loading the current user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor_curr_user() {

		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );

		$student = new LLMS_Student();
		$this->assertEquals( new LLMS_Student( $user->ID ), $student );
		$this->assertEquals( $user->ID, $student->get_id() );
		$this->assertEquals( $user, $student->get_user() );

	}

	/**
	 * Test constructor when current user autoloading is disabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor_no_autoload() {

		$user = $this->factory->user->create_and_get();

		wp_set_current_user( $user->ID );

		$student = new LLMS_Student( null, false );

		$this->assertSame( 0, $student->get_id() );
		$this->assertFalse( $student->exists() );

	}

	/**
	 * Test exists function
	 * 
	 * @since 3.9.0
	 * 
	 * @return void
	 */
	public function test_exists() {

		$uid = $this->factory->user->create();

		$student = new LLMS_Student( $uid );
		$this->assertTrue( $student->exists() );

		$fake_student = new LLMS_Student( $uid + 1 );
		$this->assertFalse( $fake_student->exists() );

	}

	/**
	 * test get_id method
	 * 
	 * @since 3.9.0
	 * 
	 * @return void
	 */
	public function test_get_id() {

		$uid = $this->factory->user->create();
		$student = new LLMS_Student( $uid );
		$this->assertEquals( $uid, $student->get_id() );

	}

	/**
	 * test get_user method
	 * 
	 * @since 3.9.0
	 * 
	 * @return void
	 */
	public function test_get_user() {

		$uid = $this->factory->user->create();
		$student = new LLMS_Student( $uid );
		$this->assertTrue( is_a( $student->get_user(), 'WP_User' ) );

	}

	/**
	 * Test Student Getters and Setters
	 * 
	 * @since 3.9.0
	 * 
	 * @return   void
	 */
	public function test_getters_setters() {

		$uid     = $this->factory->user->create( array( 'role' => 'student' ) );
		$user    = new WP_User( $uid );
		$student =  new LLMS_Student( $uid );

		// test some core prefixed stuff from the usermeta table
		$student->set( 'first_name', 'Student' );
		$student->set( 'last_name', 'McStudentFace' );
		$this->assertEquals( get_user_meta( $uid, 'first_name', true ), $student->get( 'first_name' ) );
		$this->assertEquals( get_user_meta( $uid, 'last_name', true ), $student->get( 'last_name' ) );

		// stuff from the user table
		$this->assertEquals( $user->user_email, $student->get( 'user_email' ) );

		// llms custom user meta
		$student->set( 'billing_address', '123 Student Place' );
		$this->assertEquals( get_user_meta( $uid, 'llms_billing_address', true ), $student->get( 'billing_address' ) );

		// don't prefix
		$student->set( 'this_is_third_party', '123456', false );
		add_filter( 'llms_student_unprefixed_metas', function( $metas ) {
			$metas[] = 'this_is_third_party';
			return $metas;
		} );
		$this->assertEquals( get_user_meta( $uid, 'this_is_third_party', true ), $student->get( 'this_is_third_party' ) );

	}

}
