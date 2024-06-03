<?php
/**
 * Test LLMS_Forms Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms_data
 *
 * @since 5.0.0
 * @version 5.0.0
 */
class LLMS_Test_Forms_Data extends LLMS_UnitTestCase {

	/**
	 * Setup the test
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main  = new LLMS_Forms_Data();
		$this->forms = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'forms' );

	}

	public function test_constructor() {

		remove_action( 'save_post_llms_form', array( $this->main, 'save_username_locations' ), 10 );

		$this->main = new LLMS_Forms_Data();

		$this->assertEquals( 10, has_action( 'save_post_llms_form', array( $this->main, 'save_username_locations' ) ) );

		$this->assertTrue( is_a( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'forms' ), 'LLMS_Forms' ) );

	}

	public function test_save_username_location_with_username() {

		update_option( 'lifterlms_registration_generate_username', 'no' );
		$reg_form_id = $this->forms->create( 'registration', true );
		$expect      = array( $reg_form_id );

		// Clear data.
		delete_option( 'llms_forms_username_locations' );

		$res = $this->main->save_username_locations( $reg_form_id, get_post( $reg_form_id ) );
		$this->assertEquals( $expect, $res );

		// Add another form.
		$form_id = $this->forms->create( 'checkout', true );
		$expect[] = $form_id;
		$res = $this->main->save_username_locations( $form_id, get_post( $form_id ) );
		$this->assertEquals( $expect, $res );

		// Recreate the form without a username field, this should remove the form id.
		delete_option( 'lifterlms_registration_generate_username', 'no' );
		$form_id = $this->forms->create( 'checkout', true );
		$res = $this->main->save_username_locations( $form_id, get_post( $form_id ) );
		$this->assertEquals( array( $reg_form_id ), $res );

	}

}




