<?php
/**
 * Test form-related functions
 *
 * @package LifterLMS/Tests
 *
 * @group form_functions
 * @group forms
 * @group functions
 *
 * @since 5.0.0
 * @version 5.0.0
 */
class LLMS_Test_Functions_Forms extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_form() function.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_form() {

		$this->assertFalse( llms_get_form( 'fake' ) );
		$this->assertFalse( llms_get_form( 'checkout' ) );

		LLMS_Forms::instance()->create( 'checkout' );
		$this->assertTrue( is_a( llms_get_form( 'checkout' ), 'WP_Post' ) );

	}

	/**
	 * Test llms_get_form_html() function.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_form_html() {

		$this->assertEquals( '', llms_get_form_html( 'fake' ) );
		$this->assertEquals( '', llms_get_form_html( 'checkout' ) );

		LLMS_Forms::instance()->create( 'checkout' );
		$this->assertTrue( '' !== llms_get_form_html( 'checkout' ) );

	}

	/**
	 * test llms_get_form_title() method.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_form_title() {

		$this->assertEquals( '', llms_get_form_title( 'fake' ) );
		$this->assertEquals( '', llms_get_form_title( 'checkout' ) );

		// Title enabled.
		LLMS_Forms::instance()->create( 'checkout' );
		$this->assertEquals( 'Billing Information', llms_get_form_title( 'checkout' ) );

		// Title disabled.
		LLMS_Forms::instance()->create( 'account' );
		$this->assertEquals( '', llms_get_form_title( 'account' ) );

	}

	/**
	 * Test llms_get_login_form() for a logged out user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_login_form_logged_out_user() {

		$res = $this->get_output( 'llms_get_login_form' );
		$this->assertStringContains( '<div class="llms-person-login-form-wrapper">', $res );
		$this->assertStringContains( '<form action="" class="llms-login" method="POST">', $res );

	}

	/**
	 * Test llms_get_login_form() for a logged in user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_login_form_logged_in_user() {

		wp_set_current_user( $this->factory->user->create() );
		$this->assertOutputEmpty( 'llms_get_login_form' );

	}

}
