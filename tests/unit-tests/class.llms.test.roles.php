<?php
/**
 * Tests for LifterLMS Custom Post Types
 * @group    LLMS_Roles
 * @since   3.13.0
 * @version 3.14.0
 */
class LLMS_Test_Roles extends LLMS_UnitTestCase {

	public function test_get_all_core_caps() {

		$this->assertTrue( is_array( LLMS_Roles::get_all_core_caps() ) );
		$this->assertTrue( ! empty( LLMS_Roles::get_all_core_caps() ) );

	}

	public function test_get_roles() {

		$expect = array(
			'instructor' => __( 'Instructor', 'lifterlms' ),
			'instructors_assistant' => __( 'Instructor\'s Assistant', 'lifterlms' ),
			'lms_manager' => __( 'LMS Manager', 'lifterlms' ),
			'student' => __( 'Student', 'lifterlms' ),
		);
		$this->assertEquals( $expect, LLMS_Roles::get_roles() );

	}

	public function test_install() {

		$wp_roles = wp_roles();

		// remove first
		LLMS_Roles::remove_roles();

		// install them
		LLMS_Roles::install();

		// ensure all the roles were installed
		foreach ( array_keys( LLMS_Roles::get_roles() ) as $role ) {
			$this->assertTrue( $wp_roles->is_role( $role ) );
		}

		// test admin caps were installed
		$admin = $wp_roles->get_role( 'administrator' );
		foreach ( LLMS_Roles::get_all_core_caps() as $cap ) {
			$this->assertTrue( $admin->has_cap( $cap ) );
		}

		// test instructor caps
		$instructor = $wp_roles->get_role( 'instructor' );
		foreach ( LLMS_Roles::get_all_core_caps() as $cap ) {
			$has = $instructor->has_cap( $cap );
			if ( in_array( $cap, array( 'view_lifterlms_reports', 'lifterlms_instructor' ) ) ) {
				$this->assertTrue( $has );
			} else {
				$this->assertFalse( $has );
			}
		}

	}

	public function test_remove_roles() {

		$wp_roles = wp_roles();

		// ensure roles are installed
		LLMS_Roles::install();

		// remove them
		LLMS_Roles::remove_roles();

		// make sure roles are gone
		foreach ( array_keys( LLMS_Roles::get_roles() ) as $role ) {
			$this->assertFalse( $wp_roles->is_role( $role ) );
		}

		// test admin caps were removed
		$admin = $wp_roles->get_role( 'administrator' );
		foreach ( LLMS_Roles::get_all_core_caps() as $cap ) {

			$this->assertFalse( $admin->has_cap( $cap ) );
		}


	}

}
