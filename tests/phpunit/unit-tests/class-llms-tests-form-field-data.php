<?php
/**
 * Test form field data
 *
 * @package LifterLMS_Tests/Classes
 *
 * @group form_fields
 * @group form_field_data
 *
 * @since [version]
 */
class LLMS_Test_Form_Field_Data extends LLMS_Unit_Test_Case {

	public function get_main( $id = null, $hydrate = false) {
		return new LLMS_Form_Field_Data( $id, $hydrate );
	}

	public function get_mock_field( $id = 'field-id', $data = array() ) {

		$defaults = array(
			'name'       => 'field_name',
			'field_type' => 'text',
			'store'      => 'usermeta',
			'store_key'  => 'field_name',
			'protected'  => 0,
		);

		$field = $this->get_main( $id )->setup( wp_parse_args( $data, $defaults ) );

		$field->save();

		return $field;

	}

	/**
	 * Test (most?) read/writes functions
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_crud() {

		$data = array(
			'name'       => 'test-name',
			'field_type' => 'text',
			'store'      => 'usermeta',
			'store_key'  => 'test_meta_field',
			'protected'  => 1,
		);

		// Create a new thing.
		$field = $this->get_main( 'test_id' );
		$res = $field->setup( $data )->save();

		$this->assertTrue( $res );

		// Load it and make sure it worked.
		$load = $this->get_main( 'test_id', true );

		$this->assertEquals( 'test_id', $load->get( 'id' ) );

		foreach ( $data as $key => $expect ) {
			$this->assertEquals( $expect, $load->get( $key ) );
		}

		// Update things.
		$data = array(
			'name'       => 'test-name-change',
			'field_type' => 'number',
			'store'      => 'users',
			'store_key'  => 'test_meta_field_change',
			'protected'  => 0,
		);

		foreach ( $data as $key => $value ) {

			$res = $load->set( $key, $value )->save();
			$this->assertTrue( $res );

			// Check the things persisted.
			$check = $this->get_main( 'test_id' );
			$this->assertEquals( $value, $check->get( $key ) );

		}


	}

	public function test_validate_id() {

		$tests = array(
			 // Must start with a letter.
			'123-abc'  => false,
			'-123-abc' => false,
			'_123-abc' => false,

			// Invalid characters.
			'abc!'    => false,
			'abc*'    => false,
			'abc;'    => false,
			'abc+'    => false,
			'abc abc' => false,

			// Okay.
			'abc-abc'               => true,
			'abc-123'               => true,
			'abc-123---'            => true,
			'abc-123---___-_-__abc' => true,
			'abc_123'               => true,
		);

		foreach ( $tests as $input => $expect ) {

			$this->assertEquals( $expect, LLMS_Form_Field_Data::validate( 'id', $input ) );

		}

	}

	public function test_validate_store() {

		$tests = array(
			'users'    => true,
			'usermeta' => true,
			'fake'     => false,
		);
		foreach ( $tests as $input => $expected ) {
			$this->assertEquals( $expected, LLMS_Form_Field_Data::validate( 'store', $input ) );
		}

	}

	public function test_sanitize_text_fields() {

		$tests = array(

			// Invalid characters are stripped.
			'abc!'    => 'abc',
			'abc*'    => 'abc',
			'abc;'    => 'abc',
			'abc+'    => 'abc',
			'abc abc' => 'abcabc',
			'!!!   - abc abc 123' => '-abcabc123',

			// Okay.
			'123-abc'  => '123-abc',
			'-123-abc' => '-123-abc',
			'_123-abc' => '_123-abc',
			'abc-abc'               => 'abc-abc',
			'abc-123'               => 'abc-123',
			'abc-123---'            => 'abc-123---',
			'abc-123---___-_-__abc' => 'abc-123---___-_-__abc',
			'abc_123'               => 'abc_123',
		);

		foreach ( $tests as $input => $expect ) {

			$this->assertEquals( $expect, LLMS_Form_Field_Data::sanitize( 'id', $input ) );
			$this->assertEquals( $expect, LLMS_Form_Field_Data::sanitize( 'name', $input ) );
			$this->assertEquals( $expect, LLMS_Form_Field_Data::sanitize( 'store_key', $input ) );

		}

	}

	public function test_sanitize_protected() {

		$tests = array(
			// Cast truthys to 1.
			array( 1, 1 ),
			array( true, 1 ),
			array( 'yes', 1 ),
			array( 'true', 1 ),
			array( 'on', 1 ),
			array( '1', 1 ),
			// Everything else to 0.
			array( 0, 0 ),
			array( false, 0 ),
			array( 'no', 0 ),
			array( 'false', 0 ),
			array( 'off', 0 ),
			array( '0', 0 ),
			array( 'string', 0 ),
			array( '!', 0 ),
			array( '', 0 ),
		);

		foreach ( $tests as $test ) {

			$this->assertSame( $test[1], LLMS_Form_Field_Data::sanitize( 'protected', $test[0] ), (string) $test[0] );
		}

	}

	public function test_metas() {

		$field = $this->get_mock_field();

		$this->assertTrue( $field->add_meta( 'label', 'Field Label' ) );

		// Look it up.
		$this->assertEquals( 'Field Label', $field->get_meta( 'label' ) );

		// Can't add it again.
		$this->assertFalse( $field->add_meta( 'label', 'Another Field Label' ) );

		// Can set it.
		$this->assertTrue( $field->set_meta( 'label', 'Updated Field Label' ) );

		// Is changed.
		$this->assertEquals( 'Updated Field Label', $field->get_meta( 'label' ) );

		// Add another one.
		$this->assertTrue( $field->add_meta( 'required', true ) );

		// Look it up.
		$this->assertTrue( $field->get_meta( 'required' ) );

		// Get all.
		$metas = $field->get_meta();
		var_dump( $metas );

	}

}
