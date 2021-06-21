<?php
/**
 * Test user information field functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group user_info_fields
 *
 * @since 5.0.0
 */
class LLMS_Test_Functions_User_Info_fields extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_user_information_field()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_user_information_field() {

		// Does not exist.
		$this->assertFalse( llms_get_user_information_field( 'fake' ) );

		// Does exist.
		$field = llms_get_user_information_field( 'email_address' );

		$this->assertEquals( array( 'id', 'name', 'type', 'label', 'data_store', 'data_store_key' ), array_keys( $field ) );

		$this->assertEquals( 'user_email', $field['data_store_key'] );
		$this->assertEquals( 'users', $field['data_store'] );
		$this->assertEquals( 'email_address', $field['name'] );
		$this->assertEquals( 'email_address', $field['id'] );

	}

	/**
	 * Test llms_get_user_information_fields()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_user_information_fields() {

		$list = llms_get_user_information_fields();

		$ids = array();

		foreach ( $list as $field ) {

			$this->assertArrayHasKey( 'id', $field );
			$this->assertArrayHasKey( 'data_store', $field );
			$this->assertArrayHasKey( 'data_store_key', $field );

			$ids[] = $field['id'];

		}

		$expected_ids = array(
			'user_login',
			'email_address',
			'password',
			'first_name',
			'last_name',
			'display_name',
			'llms_billing_address_1',
			'llms_billing_address_2',
			'llms_billing_city',
			'llms_billing_country',
			'llms_billing_state',
			'llms_billing_zip',
			'llms_phone',
		);
		$this->assertEquals( $expected_ids, $ids );
	}

}
