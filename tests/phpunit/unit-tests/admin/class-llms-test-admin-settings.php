<?php
/**
 * Tests for LLMS_Admin_Settings class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_settings
 *
 * @since 7.0.0
 */
class LLMS_Test_Admin_Settings extends LLMS_UnitTestCase {

	/**
	 * Tests set_field_defaults().
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_set_field_defaults() {

		$expected = array(
			'id'           => '',
			'title'        => '',
			'class'        => '',
			'css'          => '',
			'default'      => '',
			'desc'         => '',
			'desc_tooltip' => '',
			'after_html'   => '',
		);

		$this->assertEquals( $expected, LLMS_Admin_Settings::set_field_defaults( array() ) );

		// Test all default values will be set.
		foreach ( array_keys( $expected ) as $key ) {
			$expected[ $key ] = 'abc';
			$this->assertEquals( $expected, LLMS_Admin_Settings::set_field_defaults( array( $key => 'abc' ) ) );
			$expected[ $key ] = '';
		}

		// Title fallback to name.
		$expected['name']  = 'abc';
		$expected['title'] = 'abc';
		$this->assertEquals( $expected, LLMS_Admin_Settings::set_field_defaults( array( 'name' => 'abc' ) ) );

	}

	/**
	 * Test save_fields() with a single checkbox type field.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_fields_checkbox() {

		$id     = 'mock_checkbox_field';
		$fields = array(
			array(
				'type' => 'checkbox',
				'id'   => $id,
			)
		);

		// Previous value should be overwritten.
		update_option( $id, 'previous val' );

		// Post a new value.
		$this->mockPostRequest( array(
			$id => 'doensntmatter',
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals( 'yes', get_option( $id ) );

		// The element wasn't posted.
		$this->mockPostRequest( array(
			'mock' => '1',
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertSame( 'no', get_option( $id ) );

	}

	/**
	 * Test save_fields() with an checkbox group (array) field.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_fields_checkboxes() {

		$id     = 'mock_checkbox_field';
		$fields = array(
			array(
				'type' => 'checkbox',
				'id'   => $id . '[one]',
			),
			array(
				'type' => 'checkbox',
				'id'   => $id . '[two]',
			)
		);

		// Previous value should be overwritten.
		update_option( $id, 'previous val' );

		// Post a new value.
		$this->mockPostRequest( array(
			$id => array(
				'one' => 'doesntmatter',
			),
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals( array( 'one' => 'yes', 'two' => 'no' ), get_option( $id ) );

		// The element wasn't posted.
		$this->mockPostRequest( array(
			'mock' => '1',
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertSame( array( 'one' => 'no', 'two' => 'no' ), get_option( $id ) );

	}

	/**
	 * Tests save_fields() with regular fields.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_fields_basic() {

		$types = array(
			'password',
			'text',
			'email',
			'number',
			'select',
			'single_select_page',
			'single_select_membership',
			'radio',
			'hidden',
			'image',
		);

		foreach ( $types as $type ) {

			$id     = "mock_{$type}_field";
			$val    = (string) time();
			$fields = array(
				array(
					'type' => 'text',
					'id'   => $id,
				)
			);

			// Previous value should be overwritten.
			update_option( $id, 'previous val' );

			// Post a new value.
			$this->mockPostRequest( array(
				$id => $val,
			) );
			$res = LLMS_Admin_Settings::save_fields( $fields );
			$this->assertEquals( $val, get_option( $id ) );

			// The element wasn't posted.
			$this->mockPostRequest( array(
				'mock' => '1',
			) );
			$res = LLMS_Admin_Settings::save_fields( $fields );
			$this->assertSame( '', get_option( $id ) );

		}

	}

	/**
	 * Test save_fields() with array type fields.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_fields_array() {

		$id     = 'mock_text_arr_field';
		$val    = (string) time();
		$fields = array(
			array(
				'type' => 'text',
				'id'   => $id . '[one]',
			),
			array(
				'type' => 'text',
				'id'   => $id . '[two]',
			)
		);

		// Post only one value.
		$this->mockPostRequest( array(
			$id => array(
				'one' => $val
			),
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals(
			array(
				'one' => $val,
				'two' => '',
			),
			get_option( $id )
		);

		// Post only one value.
		$this->mockPostRequest( array(
			$id => array(
				'two' => $val
			),
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals(
			array(
				'one' => '',
				'two' => $val,
			),
			get_option( $id )
		);

		// Post both values.
		$this->mockPostRequest( array(
			$id => array(
				'one' => "{$val}_1",
				'two' => "{$val}_2",
			),
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals(
			array(
				'one' => "{$val}_1",
				'two' => "{$val}_2",
			),
			get_option( $id )
		);

	}

	/**
	 * Test save_fields() with a secure option.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_fields_secure_option() {

		$id        = 'mock_secure_field_' . time();
		$secure_id = strtoupper( $id );
		$val       = (string) time();
		$fields    = array(
			array(
				'type'          => 'text',
				'id'            => $id,
				'secure_option' => $secure_id,
			),
		);

		update_option( $id, 'db-value' );

		// A constant/env var isn't defined so save the value.
		$this->mockPostRequest( array(
			$id => $val,
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals( $val, get_option( $id ) );

		// The secure value is defined so the DB value will be deleted.
		putenv( "{$secure_id}=SECURE-VAL" );
		$this->mockPostRequest( array(
			$id => $val,
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals( 'NOT-FOUND', get_option( $id, 'NOT-FOUND' ) );

	}

	/**
	 * Test save_fields() for a setting field with no ID.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_fields_no_id() {

		$actions = did_action( 'lifterlms_update_option' );

		$fields = array(
			array(
				'type' => 'text',
			),
		);

		$this->mockPostRequest( array(
			'mock' => '1',
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertSame( $actions, did_action( 'lifterlms_update_option' ) );

	}


	/**
	 * Test save_fields() on fields with maxlength attribute.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_save_field_with_maxlength() {

		// Checking on an array of fields.
		$id     = 'mock_text_arr_field';
		$val    = '123456789101112';
		$fields = array(
			array(
				'type' => 'text',
				'id'   => $id . '[one]',
				'custom_attributes' => array(
					'maxlength' => 9,
				)
			),
			array(
				'type' => 'text',
				'id'   => $id . '[two]',
			)
		);

		// Post only one value.
		$this->mockPostRequest( array(
			$id => array(
				'one' => $val
			),
		) );

		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals(
			array(
				'one' => '123456789',
				'two' => '',
			),
			get_option( $id )
		);

		// Post only one value.
		$this->mockPostRequest( array(
			$id => array(
				'two' => $val
			),
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals(
			array(
				'one' => '',
				'two' => $val,
			),
			get_option( $id )
		);

		// Post both values.
		$this->mockPostRequest( array(
			$id => array(
				'one' => $val,
				'two' => $val,
			),
		) );
		$res = LLMS_Admin_Settings::save_fields( $fields );
		$this->assertEquals(
			array(
				'one' => '123456789',
				'two' => $val,
			),
			get_option( $id )
		);
	}

}
