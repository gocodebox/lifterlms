<?php
/**
 * Test LLMS_Form_Field class
 *
 * @package LifterLMS/Tests
 *
 * @group form_field
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Form_Field extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Test field `value` attribute.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_field_value() {

		// No specified value.
		$this->assertStringNotContains( 'value="', llms_form_field( array(), false ) );

		// Value is specified.
		$this->assertStringContains( 'value="mock"', llms_form_field( array( 'value' => 'mock' ), false ) );

		// Default value specified.
		$this->assertStringContains( 'value="mock"', llms_form_field( array( 'default' => 'mock' ), false ) );

		// Default value not added if a value is specified.
		$this->assertStringContains( 'value="mock"', llms_form_field( array( 'value' => 'mock', 'default' => 'fake' ), false ) );
		$this->assertStringNotContains( 'value="fake"', llms_form_field( array( 'value' => 'mock', 'default' => 'fake' ), false ) );

	}

	/**
	 * Test field `name` attribute.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_field_name() {

		// No name specified, fallback to the field id.
		$this->assertStringContains( 'name="mock"', llms_form_field( array( 'id' => 'mock' ), false ) );

		// Name specified.
		$this->assertStringContains( 'name="mock"', llms_form_field( array( 'name' => 'mock', 'id' => 'fake' ), false ) );

		// Name explicitly disabled.
		$this->assertStringNotContains( 'name="', llms_form_field( array( 'name' => false ), false ) );

	}

	/**
	 * Test field `placeholder` attribute.
	 *
	 * @since [version]
	 *
	 * @return [type]
	 */
	public function test_field_placeholder() {

		$this->assertStringContains( 'placeholder="test"', llms_form_field( array( 'placeholder' => 'test' ), false ) );

	}

	/**
	 * Test field `style` attribute.
	 *
	 * @since [version]
	 *
	 * @return [type]
	 */
	public function test_field_style() {

		// No style.
		$this->assertStringNotContains( 'style="', llms_form_field( array(), false ) );

		// Has style.
		$this->assertStringContains( 'style="test"', llms_form_field( array( 'style' => 'test' ), false ) );

	}

	/**
	 * Test field description.
	 *
	 * @since [version]
	 *
	 * @return [type]
	 */
	public function test_field_description() {

		// No description.
		$this->assertStringNotContains( '<span class="llms-description">', llms_form_field( array(), false ) );

		// Has Description.
		$this->assertStringContains( '<span class="llms-description">Test Description</span>', llms_form_field( array( 'description' => 'Test Description' ), false ) );

	}

	/**
	 * Test field `required` attribute.
	 *
	 * @since [version]
	 *
	 * @return [type]
	 */
	public function test_field_required() {

		// Not required.
		$this->assertStringNotContains( '<span class="llms-required">*</span>', llms_form_field( array( 'label' => 'mock' ), false ) );
		$this->assertStringNotContains( 'required="required"', llms_form_field( array(), false ) );

		// Is required.
		$this->assertStringContains( '<span class="llms-required">*</span>', llms_form_field( array( 'required' => true, 'label' => 'mock' ), false ) );
		$this->assertStringContains( 'required="required"', llms_form_field( array( 'required' => true ), false ) );

		// Required but no label.
		$this->assertStringNotContains( '<span class="llms-required">*</span>', llms_form_field( array( 'required' => true ), false ) );

	}

	/**
	 * Test field `disabled` attribute.
	 *
	 * @since [version]
	 *
	 * @return [type]
	 */
	public function test_field_disabled() {

		// No disabled.
		$this->assertStringNotContains( 'disabled="disabled"', llms_form_field( array(), false ) );

		// Has disabled.
		$this->assertStringContains( 'disabled="disabled"', llms_form_field( array( 'disabled' => true ), false ) );

	}

}
