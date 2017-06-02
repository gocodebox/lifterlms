<?php
/**
 * Tests for LLMS_Abstract_User_Data
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Abstract_User_Data extends LLMS_UnitTestCase {

	/**
	 * Test exists funciton
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function text_exists() {

		$uid = $this->factory->user->create();

		$student = new LLMS_Student( $uid );
		$this->assertTrue( $student->exists() );

		$fake_student = new LLMS_Student( $uid + 1 );
		$this->assertFalse( $fake_student->exists() );

	}

	/**
	 * test get_id method
	 * @return   [type]     [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_id() {

		$uid = $this->factory->user->create();
		$student = new LLMS_Student( $uid );
		$this->assertEquals( $uid, $student->get_id() );

	}

	/**
	 * test get_user method
	 * @return   [type]     [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_user() {

		$uid = $this->factory->user->create();
		$student = new LLMS_Student( $uid );
		$this->assertTrue( is_a( $student->get_user(), 'WP_User' ) );

	}

	/**
	 * Test Student Getters and Setters
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_getters_setters() {

		$uid = $this->factory->user->create( array( 'role' => 'student' ) );
		$user = new WP_User( $uid );
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
