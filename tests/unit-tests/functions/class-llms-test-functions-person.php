<?php
/**
 * Tests for LifterLMS Core Functions
 *
 * @group functions_person
 * @group functions
 * @group LLMS_Student
 *
 * @since 3.8.0
 * @since 3.9.0 Add tests for `llms_get_student()`.
 */
class LLMS_Test_Functions_Person extends LLMS_UnitTestCase {

	/**
	 * Test llms_can_user_bypass_restrictions()
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function test_llms_can_user_bypass_restrictions() {

		// allow admins to bypass
		update_option( 'llms_grant_site_access', array( 'administrator' ) );

		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$student = $this->factory->user->create( array( 'role' => 'student' ) );

		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );
		$this->assertFalse( llms_can_user_bypass_restrictions( $student ) );

		$this->assertFalse( llms_can_user_bypass_restrictions( 'fake' ) );

		// pass in a student
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

		// should still work with two roles
		update_option( 'llms_grant_site_access', array( 'administrator', 'editor' ) );
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

	}

	public function test_llms_get_minimum_password_strength_name() {

		// Default value.
		$this->assertEquals( 'strong', llms_get_minimum_password_strength_name() );

		// Existing options.
		$this->assertEquals( 'strong', llms_get_minimum_password_strength_name( 'strong' ) );
		$this->assertEquals( 'medium', llms_get_minimum_password_strength_name( 'medium' ) );
		$this->assertEquals( 'weak', llms_get_minimum_password_strength_name( 'weak' ) );
		$this->assertEquals( 'very weak', llms_get_minimum_password_strength_name( 'very-weak' ) );

		// Custom option.
		$this->assertEquals( 'fake', llms_get_minimum_password_strength_name( 'fake' ) );

	}

	/**
	 * Test llms_get_student
	 *
	 * @since 3.9.0
	 *
	 * @return void
	 */
	public function test_llms_get_student() {

		$uid = $this->factory->user->create();

		$this->assertTrue( is_a( llms_get_student( $uid ), 'LLMS_Student' ) );
		$this->assertTrue( is_a( llms_get_student( new WP_User( $uid ) ), 'LLMS_Student' ) );
		$this->assertTrue( is_a( llms_get_student( new LLMS_Student( $uid ) ), 'LLMS_Student' ) );

		$this->assertFalse( is_a( llms_get_student( $uid + 1 ), 'LLMS_Student' ) );
		$this->assertFalse( is_a( llms_get_student( 'string' ), 'LLMS_Student' ) );

	}

}
