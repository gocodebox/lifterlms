<?php
/**
 * Tests for LifterLMS Core Functions
 * @since    3.7.0
 * @version  3.7.0
 */
class LLMS_Test_Functions_Person extends LLMS_UnitTestCase {

	/**
	 * Test llms_can_user_bypass_restrictions()
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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
		$admin = new LLMS_Student( $admin );
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

		// should still work with two roles
		update_option( 'llms_grant_site_access', array( 'administrator', 'editor' ) );
		$this->assertTrue( llms_can_user_bypass_restrictions( $admin ) );

	}

}
