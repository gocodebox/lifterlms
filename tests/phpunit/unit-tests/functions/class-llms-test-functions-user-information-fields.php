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

	/**
	 * Test _llms_add_user_info_to_merge_buttons()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function test__llms_add_user_info_to_merge_buttons() {

		$input  = array( '{code}' => 'Desc' );
		$screen = get_current_screen();

		$this->assertEquals( $input, _llms_add_user_info_to_merge_buttons( $input, $screen ) );

		foreach ( array( 'llms_certificate', 'llms_email' ) as $post_type ) {

			llms_tests_mock_current_screen( $post_type );

			$screen = get_current_screen();
			$res    = _llms_add_user_info_to_merge_buttons( $input, $screen );

			$this->assertArrayHasKey( array_keys( $input )[0], $res );

			$this->assertEquals( 'Email Address', $res['[llms-user user_email]'] );
			$this->assertEquals( 'Address Line 2', $res['[llms-user llms_billing_address_2]'] );
			$this->assertEquals( 'Phone Number', $res['[llms-user llms_phone]'] );

			$this->assertFalse( array_key_exists( '[llms-user user_pass]', $res ) );

			llms_tests_reset_current_screen();

		}

	}

}
