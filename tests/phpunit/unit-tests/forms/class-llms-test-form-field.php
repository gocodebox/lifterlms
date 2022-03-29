<?php
/**
 * Test LLMS_Form_Field class
 *
 * @package LifterLMS/Tests
 *
 * @group form_field
 *
 * @since 5.0.0
 * @since 5.10.0 Update tests on password strength meter enqueueing.
 * @version 5.10.0
 */
class LLMS_Test_Form_Field extends LLMS_Unit_Test_Case {

	/**
	 * Returns an array of attributes for a 'llms/form-field-checkboxes' block to be serialized into
	 * a form's `post_content`.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_checkboxes_attributes() {

		$checkboxes = array(
			'datastore' => 'user_meta',
			'type'      => 'checkbox',
			'id'        => 'matrix-choices-1',
			'name'      => 'matrix_choices_1',
			'label'     => 'Matrix Choices',
			'options'   => array(
				array(
					'key'  => 'blue_pill',
					'text' => 'Believe whatever you want to believe.',
				),
				array(
					'key'  => 'red_pill',
					'text' => 'I show you how deep the rabbit hole goes',
				),
			),
		);

		return $checkboxes;
	}

	/**
	 * Retrieve a new user with specified user meta data.
	 *
	 * @since 5.0.0
	 *
	 * @param string $meta_key Meta key name.
	 * @param string $meta_val Meta value (optional).
	 * @return int WP_User ID.
	 */
	private function get_user_with_meta( $meta_key, $meta_val = '' ) {

		$uid = $this->factory->user->create();
		update_user_meta( $uid, $meta_key, $meta_val );

		wp_set_current_user( $uid );

		return $uid;

	}

	/**
	 * teardown the test case.
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		wp_set_current_user( null );

	}

	/**
	 * Test the 'explode_options_to_fields()' method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_explode_options_to_fields() {

		$checkboxes = $this->get_checkboxes_attributes();

		// The user has checked the 2nd option.
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );
		add_user_meta( $user_id, $checkboxes['name'], array( $checkboxes['options'][1]['key'] ) );

		$field_with_options = new LLMS_Form_Field( $checkboxes );

		// Test on a form that is not hidden.
		$fields = $field_with_options->explode_options_to_fields( false );
		$this->assertCount( count( $checkboxes['options'] ), $fields );

		foreach ( $fields as $index => $field ) {
			$expected_key = $checkboxes['options'][ $index ]['key'];
			$settings     = $field->get_settings();

			$this->assertEquals( ( 1 === $index ), $settings['checked'] );
			$this->assertFalse( $settings['data_store'] );

			$equals = array(
				"{$checkboxes['id']}--{$expected_key}"   => $settings['id'],
				"{$checkboxes['name']}[]"                => $settings['name'],
				$checkboxes['options'][ $index ]['text'] => $settings['label'],
				'checkbox'                               => $settings['type'],
				$expected_key                            => $settings['value'],
			);
			$this->assertEquals( array_keys( $equals ), array_values( $equals ) );
		}

		// Test on a form that is hidden.
		$fields = $field_with_options->explode_options_to_fields( true );
		$this->assertCount( 1, $fields );

		foreach ( $fields as $field ) {
			$expected_key = $checkboxes['options'][1]['key'];
			$settings     = $field->get_settings();

			$this->assertEquals( true, $settings['checked'] );
			$this->assertFalse( $settings['data_store'] );

			$equals = array(
				"{$checkboxes['id']}--{$expected_key}" => $settings['id'],
				"{$checkboxes['name']}[]"              => $settings['name'],
				$checkboxes['options'][1]['text']      => $settings['label'],
				'hidden'                               => $settings['type'],
				$expected_key                          => $settings['value'],
			);
			$this->assertEquals( array_keys( $equals ), array_values( $equals ) );
		}
	}

	/**
	 * Test output of a hidden input field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_hidden() {

		$this->assertEquals( '<input class="llms-field-input" id="mock-id" name="mock-id" type="hidden" value="1" />', llms_form_field( array( 'type' => 'hidden', 'id' => 'mock-id', 'value' => '1' ), false ) );

	}

	/**
	 * Test output of a select field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_select() {

		$opts = array(
			'type' => 'select',
			'options' => array(
				'mock' => 'MOCK',
				'fake' => 'FAKE',
			),
		);

		$html = llms_form_field( $opts, false );

		$this->assertStringContains( '<select class="llms-field-select', $html );
		$this->assertStringContains( '<option value="mock">MOCK</option>', $html );
		$this->assertStringContains( '<option value="fake">FAKE</option>', $html );

		// With selected value.
		$opts['selected'] = 'fake';
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<option value="fake" selected="selected">FAKE</option>', $html );

		unset( $opts['selected'] );

		// With default value.
		$opts['default'] = 'fake';
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<option value="fake" selected="selected">FAKE</option>', $html );

	}

	/**
	 * Test select field with user data.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_select_with_user_data() {

		$opts = array(
			'type'           => 'select',
			'data_store_key' => 'select_data',
			'selected'       => 'mock',
			'options'        => array(
				'mock' => 'MOCK',
				'fake' => 'FAKE',
			),
		);

		// Uses default value.
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<option value="mock" selected="selected">MOCK</option>', $html );
		$this->assertStringNotContains( '<option value="fake" selected="selected">FAKE</option>', $html );

		// No meta saved for user, uses default.
		$this->get_user_with_meta( 'other', '' );
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<option value="mock" selected="selected">MOCK</option>', $html );
		$this->assertStringNotContains( '<option value="fake" selected="selected">FAKE</option>', $html );

		// Use user's value.
		$this->get_user_with_meta( 'select_data', 'fake' );
		$html = llms_form_field( $opts, false );
		$this->assertStringNotContains( '<option value="mock" selected="selected">MOCK</option>', $html );
		$this->assertStringContains( '<option value="fake" selected="selected">FAKE</option>', $html );

	}

	/**
	 * Test select field with an option group.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_select_opt_group() {

		$opts = array(
			'type'           => 'select',
			'data_store_key' => 'select_data',
			'options'        => array(
				array(
					'label' => __( 'Group 1', 'lifterlms' ),
					'options' => array(
						'opt1' => __( 'Option 1', 'lifterlms' ),
						'opt2' => __( 'Option 2', 'lifterlms' ),
					),
				),
				array(
					'label' => __( 'Group 2', 'lifterlms' ),
					'options' => array(
						'opt3' => __( 'Option 3', 'lifterlms' ),
						'opt4' => __( 'Option 4', 'lifterlms' ),
					),
				),
			),
		);

		$html = llms_form_field( $opts, false );

		$this->assertStringContains( '<optgroup label="Group 1" data-key="0">', $html );
		$this->assertStringContains( '<optgroup label="Group 2" data-key="1">', $html );

		for ( $i = 1; $i <= 4; $i++ ) {
			$this->assertStringContains( sprintf( '<option value="opt%1$d">Option %1$d</option>', $i ), $html );
		}

	}

	/**
	 * Test radio field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_radio() {

		$opts = array(
			'type'  => 'radio',
			'value' => 'mock_val',
		);

		$html = llms_form_field( $opts, false );

		$this->assertStringContains( '<div class="llms-form-field type-radio', $html );
		$this->assertStringContains( '<input class="llms-field-radio"', $html );
		$this->assertStringContains( 'type="radio"', $html );
		$this->assertStringContains( 'value="mock_val"', $html );
		$this->assertStringNotContains( 'checked="checked"', $html );

		// checked.
		$opts['checked'] = true;
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( 'checked="checked"', $html );

	}

	/**
	 * Test radio field with a user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_radio_with_user() {

		$opts = array(
			'id'    => 'radio_store',
			'type'  => 'radio',
			'value' => 'mock_val',
		);

		// User doesn't have value stored.
		$this->get_user_with_meta( 'radio_store' );
		$html = llms_form_field( $opts, false );
		$this->assertStringNotContains( 'checked="checked"', $html );

		$this->get_user_with_meta( 'radio_store', 'mock_val' );
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( 'checked="checked"', $html );

	}

	/**
	 * Test a radio group field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_radio_group() {

		$opts = array(
			'id'      => 'radio-id',
			'label'   => 'Radio Label',
			'type'    => 'radio',
			'options' => array(
				'opt1' => 'Option1',
				'opt2' => 'Option2',
			),
		);

		$html = llms_form_field( $opts, false );

		$this->assertStringContains( '<div class="llms-form-field type-radio is-group', $html );
		$this->assertStringContains( '<label for="radio-id">Radio Label</label><div class="llms-field-radio llms-input-group"', $html );
		$this->assertStringContains( '<div class="llms-form-field type-radio llms-cols-12 llms-cols-last"><input class="llms-field-radio" id="radio-id--opt1" name="radio-id" type="radio" value="opt1" /><label for="radio-id--opt1">Option1</label></div>', $html );
		$this->assertStringContains( '<div class="llms-form-field type-radio llms-cols-12 llms-cols-last"><input class="llms-field-radio" id="radio-id--opt2" name="radio-id" type="radio" value="opt2" /><label for="radio-id--opt2">Option2</label></div>', $html );

		// default value.
		$opts['default'] = 'opt1';
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<input checked="checked" class="llms-field-radio" id="radio-id--opt1" name="radio-id" type="radio" value="opt1" /><label for="radio-id--opt1">Option1</label>', $html );

		// user has saved data.
		$this->get_user_with_meta( 'radio-id', 'opt2' );
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<input checked="checked" class="llms-field-radio" id="radio-id--opt2" name="radio-id" type="radio" value="opt2" /><label for="radio-id--opt2">Option2</label>', $html );
		$this->assertStringNotContains( '<input checked="checked" class="llms-field-radio" id="radio-id--opt1" name="radio-id" type="radio" value="opt1" /><label for="radio-id--opt1">Option1</label>', $html );

	}

	/**
	 * Test a checkbox field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_checkbox() {

		$opts = array(
			'type'  => 'checkbox',
			'value' => 'mock_val',
		);

		$html = llms_form_field( $opts, false );

		$this->assertStringContains( '<div class="llms-form-field type-checkbox', $html );
		$this->assertStringContains( '<input class="llms-field-checkbox"', $html );
		$this->assertStringContains( 'type="checkbox"', $html );
		$this->assertStringContains( 'value="mock_val"', $html );
		$this->assertStringNotContains( 'checked="checked"', $html );

		// Checked.
		$opts['checked'] = true;
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( 'checked="checked"', $html );

	}

	/**
	 * Test checkbox with a user.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_checkbox_with_user() {

		$opts = array(
			'id'    => 'checkbox_store',
			'type'  => 'checkbox',
			'value' => 'mock_val',
		);

		// User doesn't have value stored.
		$this->get_user_with_meta( 'checkbox_store' );
		$html = llms_form_field( $opts, false );
		$this->assertStringNotContains( 'checked="checked"', $html );

		$this->get_user_with_meta( 'checkbox_store', 'mock_val' );
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( 'checked="checked"', $html );

	}

	/**
	 * Test checkbox group.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_checkbox_group() {

		$opts = array(
			'id'      => 'checkbox-id',
			'label'   => 'Checkbox Label',
			'type'    => 'checkbox',
			'options' => array(
				'opt1' => 'Option1',
				'opt2' => 'Option2',
			),
		);

		$html = llms_form_field( $opts, false );

		$this->assertStringContains( '<div class="llms-form-field type-checkbox is-group', $html );
		$this->assertStringContains( '<label for="checkbox-id">Checkbox Label</label><div class="llms-field-checkbox llms-input-group"', $html );
		$this->assertStringContains( '<div class="llms-form-field type-checkbox llms-cols-12 llms-cols-last"><input class="llms-field-checkbox" id="checkbox-id--opt1" name="checkbox-id[]" type="checkbox" value="opt1" /><label for="checkbox-id--opt1">Option1</label></div>', $html );
		$this->assertStringContains( '<div class="llms-form-field type-checkbox llms-cols-12 llms-cols-last"><input class="llms-field-checkbox" id="checkbox-id--opt2" name="checkbox-id[]" type="checkbox" value="opt2" /><label for="checkbox-id--opt2">Option2</label></div>', $html );

		// Default value.
		$opts['default'] = 'opt1';
		$html = llms_form_field( $opts, false );
		$this->assertStringContains(
			'<input checked="checked" class="llms-field-checkbox" id="checkbox-id--opt1" name="checkbox-id[]" type="checkbox" value="opt1" /><label for="checkbox-id--opt1">Option1</label>',
			$html
		);
		$this->assertStringNotContains(
			'<input checked="checked" class="llms-field-checkbox" id="checkbox-id--opt2" name="checkbox-id[]" type="checkbox" value="opt2" /><label for="checkbox-id--opt2">Option2</label>',
			$html
		);

		// Test multiple defaults.
		$opts['default'] = array( 'opt1', 'opt2' );
		$html = llms_form_field( $opts, false );
		$this->assertStringContains(
			'<input checked="checked" class="llms-field-checkbox" id="checkbox-id--opt1" name="checkbox-id[]" type="checkbox" value="opt1" /><label for="checkbox-id--opt1">Option1</label>',
			$html
		);
		$this->assertStringContains(
			'<input checked="checked" class="llms-field-checkbox" id="checkbox-id--opt2" name="checkbox-id[]" type="checkbox" value="opt2" /><label for="checkbox-id--opt2">Option2</label>',
			$html
		);

		// User has saved data.
		$this->get_user_with_meta( 'checkbox-id', 'opt2' );
		$html = llms_form_field( $opts, false );
		$this->assertStringContains( '<input checked="checked" class="llms-field-checkbox" id="checkbox-id--opt2" name="checkbox-id[]" type="checkbox" value="opt2" /><label for="checkbox-id--opt2">Option2</label>', $html );
		$this->assertStringNotContains( '<input checked="checked" class="llms-field-checkbox" id="checkbox-id--opt1" name="checkbox-id[]" type="checkbox" value="opt1" /><label for="checkbox-id--opt1">Option1</label>', $html );

	}

	/**
	 * Test button field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_button() {

		$html = llms_form_field( array(
			'type'  => 'button',
			'value' => 'Button Text',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-button', $html );
		$this->assertStringContains( '<button class="llms-field-button"', $html );
		$this->assertStringContains( 'type="button"', $html );
		$this->assertStringContains( '>Button Text</button>', $html );

	}

	/**
	 * Test submit button field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_submit() {

		$html = llms_form_field( array(
			'type'  => 'submit',
			'value' => 'Button Text',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-submit', $html );
		$this->assertStringContains( '<button class="llms-field-button"', $html );
		$this->assertStringContains( 'type="submit"', $html );
		$this->assertStringContains( '>Button Text</button>', $html );

	}

	/**
	 * Test reset button field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_reset() {

		$html = llms_form_field( array(
			'type'  => 'reset',
			'value' => 'Button Text',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-reset', $html );
		$this->assertStringContains( '<button class="llms-field-button"', $html );
		$this->assertStringContains( 'type="reset"', $html );
		$this->assertStringContains( '>Button Text</button>', $html );
	}

	/**
	 * Test output of a text input field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_text() {

		$html = llms_form_field( array(), false );

		$this->assertStringContains( '<div class="llms-form-field type-text', $html );
		$this->assertStringContains( '<input ', $html );
		$this->assertStringContains( 'type="text"', $html );

	}

	/**
	 * Test email field type.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_email() {

		$html = llms_form_field( array(
			'type' => 'email',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-email', $html );
		$this->assertStringContains( '<input ', $html );
		$this->assertStringContains( 'type="email"', $html );

	}

	/**
	 * Test tel field type.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_tel() {

		$html = llms_form_field( array(
			'type' => 'tel',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-tel', $html );
		$this->assertStringContains( '<input ', $html );
		$this->assertStringContains( 'type="tel"', $html );

	}

	/**
	 * Test number field type.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_number() {

		$html = llms_form_field( array(
			'type' => 'number',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-number', $html );
		$this->assertStringContains( '<input ', $html );
		$this->assertStringContains( 'type="number"', $html );

	}

	/**
	 * Test textarea field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_textarea() {

		$html = llms_form_field( array(
			'type' => 'textarea',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-textarea', $html );
		$this->assertStringContains( '<textarea class="llms-field-textarea"', $html );
		$this->assertStringContains( '></textarea>', $html );

	}

	/**
	 * Test textarea field with user data.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_textarea_with_user_data() {

		$this->get_user_with_meta( 'textarea-id', 'Lorem ipsum dolor sit.' );

		$html = llms_form_field( array(
			'id'   => 'textarea-id',
			'type' => 'textarea',
		), false );

		$this->assertStringContains( '>Lorem ipsum dolor sit.</textarea>', $html );

	}

	/**
	 * Test custom html field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_type_html() {

		$html = llms_form_field( array(
			'type' => 'html',
			'value' => '<h2>HTML Content.</h2>',
		), false );

		$this->assertStringContains( '<div class="llms-form-field type-html', $html );
		$this->assertStringContains( '<div class="llms-field-html"', $html );
		$this->assertStringContains( '><h2>HTML Content.</h2></div>', $html );

	}

	/**
	 * Test attributes setting.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_attributes() {

		$this->assertStringContains( 'data-custom="whatever', llms_form_field( array( 'attributes' => array( 'data-custom' => 'whatever' ) ), false ) );

		$multi = llms_form_field( array( 'attributes' => array( 'data-custom' => 'whatever', 'maxlength' => 5 ) ), false );
		$this->assertStringContains( 'maxlength="5"', $multi );
		$this->assertStringContains( 'data-custom="whatever', $multi );

	}

	/**
	 * Test columns setting.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_columns() {

		// Default.
		$this->assertStringContains( 'llms-cols-12 llms-cols-last', llms_form_field( array(), false ) );
		$this->assertStringContains( '<div class="clear"></div>', llms_form_field( array(), false ) );

		// Set cols.
		$this->assertStringContains( 'llms-cols-5 llms-cols-last', llms_form_field( array( 'columns' => 5 ), false ) );
		$this->assertStringContains( 'llms-cols-8 llms-cols-last', llms_form_field( array( 'columns' => 8 ), false ) );

		// Not last.
		$this->assertStringNotContains( 'llms-cols-last', llms_form_field( array( 'last_column' => false ), false ) );
		$this->assertStringNotContains( '<div class="clear"></div>', llms_form_field( array( 'last_column' => false ), false ) );

	}

	/**
	 * Test id setting.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_id() {

		$this->assertStringContains( 'id="', llms_form_field( array(), false ) );
		$this->assertStringContains( 'id="mock"', llms_form_field( array( 'id' => 'mock' ), false ) );

	}

	/**
	 * Test wrapper classes setting.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_wrapper_classes() {

		// Strings.
		$this->assertStringContains( 'mock-wrapper-class">', llms_form_field( array( 'wrapper_classes' => 'mock-wrapper-class' ), false ) );
		$this->assertStringContains( 'mock-wrapper-class alt-class">', llms_form_field( array( 'wrapper_classes' => 'mock-wrapper-class alt-class' ), false ) );

		// Arrays.
		$this->assertStringContains( 'mock-wrapper-class">', llms_form_field( array( 'wrapper_classes' => array( 'mock-wrapper-class' ) ), false ) );
		$this->assertStringContains( 'mock-wrapper-class alt-class">', llms_form_field( array( 'wrapper_classes' => array( 'mock-wrapper-class', 'alt-class' ) ), false ) );

	}

	/**
	 * Test field `value` attribute.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_placeholder() {

		$this->assertStringContains( 'placeholder="test"', llms_form_field( array( 'placeholder' => 'test' ), false ) );

	}

	/**
	 * Test field `style` attribute.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_deprecated_attributes() {

		// No style.
		$this->assertStringNotContains( 'style="', llms_form_field( array(), false ) );

		// Has style.
		$this->assertStringContains( 'style="test"', llms_form_field( array( 'style' => 'test' ), false ) );

		$this->assertStringContains( 'maxlength="1"', llms_form_field( array( 'max_length' => '1' ), false ) );
		$this->assertStringContains( 'minlength="25"', llms_form_field( array( 'min_length' => '25' ), false ) );

	}

	/**
	 * Test field description.
	 *
	 * @since 5.0.0
	 *
	 * @return void
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
	 * @since 5.0.0
	 *
	 * @return void
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
	 * Test field `label` attribute.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_label() {

		$this->assertStringContains( '<label for="fake">mock</label>', llms_form_field( array( 'id' => 'fake', 'label' => 'mock' ), false ) );
		$this->assertStringContains( '<label for="fake">mock<span class="llms-required">*</span></label>', llms_form_field( array( 'id' => 'fake', 'label' => 'mock', 'required' => true ), false ) );

	}

	/**
	 * No label element output when label is empty.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_label_empty() {

		$this->assertStringNotContains( '<label', llms_form_field( array( 'id' => 'fake' ), false ) );

	}

	/**
	 * Output an empty label element if `label_show_empty` is true and `label` is empty.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_label_show_empty() {

		$this->assertStringContains( '<label for="fake"></label>', llms_form_field( array( 'id' => 'fake', 'label_show_empty' => true ), false ) );

	}

	/**
	 * Test field `disabled` attribute.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_disabled() {

		// No disabled.
		$this->assertStringNotContains( 'disabled="disabled"', llms_form_field( array(), false ) );

		// Has disabled.
		$this->assertStringContains( 'disabled="disabled"', llms_form_field( array( 'disabled' => true ), false ) );

	}

	public function test_prepare_value_for_button_and_html() {

		$types =  array(
			// Always have an explicit value.
			'button', 'reset', 'submit', 'html',
			// May or may not have an explicit value.
			'text',
		);

		foreach ( $types as $type ) {

			$args = array(
				'type'  => $type,
				'name'  => 'test_field',
				'value' => 'A Value',
			);

			$field = new LLMS_Form_Field( $args );

			$settings = $field->get_settings();

			$this->assertEquals( $args['value'], $settings['value'] );

		}

	}

	/**
	 * Test prepare_password_strength_meter() with default values.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_password_strength_meter_default_values() {

		$field = new LLMS_Form_Field();

		$handler = function( $args ) {
			$this->assertEquals( array(), $args['blocklist'] );
			$this->assertEquals( 6, $args['min_length'] );
			$this->assertEquals( 'strong', $args['min_strength'] );
			return $args;
		};
		add_filter( 'llms_password_strength_meter_settings', $handler );

		LLMS_Unit_Test_Util::call_method( $field, 'prepare_password_strength_meter', array() );

		remove_filter( 'llms_password_strength_meter_settings', $handler );
	}

	/**
	 * Test prepare_password_strength_meter with custom values
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_password_strength_meter_custom_values() {

		$field = new LLMS_Form_Field( array(
			'min_strength' => 'weak',
			'min_length'   => 10,
		) );

		$handler = function( $args ) {
			$this->assertEquals( 10, $args['min_length'] );
			$this->assertEquals( 'weak', $args['min_strength'] );
			return $args;
		};
		add_filter( 'llms_password_strength_meter_settings', $handler );

		LLMS_Unit_Test_Util::call_method( $field, 'prepare_password_strength_meter', array() );
		$this->assertFalse( isset( $field->get_settings()['min_length'] ) );

		remove_filter( 'llms_password_strength_meter_settings', $handler );
	}

	/**
	 * Test prepare_password_strength_meter() to ensure the minimum accepted value is 6
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_password_strength_meter_min_length() {

		$field = new LLMS_Form_Field( array(
			'min_length' => 2,
		) );

		$handler = function( $args ) {
			$this->assertEquals( 6, $args['min_length'] );
			return $args;
		};
		add_filter( 'llms_password_strength_meter_settings', $handler );

		LLMS_Unit_Test_Util::call_method( $field, 'prepare_password_strength_meter', array() );
		$this->assertFalse( isset( $field->get_settings()['min_length'] ) );

		remove_filter( 'llms_password_strength_meter_settings', $handler );
	}

	/**
	 * Test prepare_password_strength_meter() for script enqueue: not enqueued case.
	 *
	 * @since 5.10.0
	 *
	 * @return void
	 */
	public function test_prepare_password_strength_meter_assets_not_enqueued() {

		$field = new LLMS_Form_Field();

		// Not enqueued.
		LLMS_Unit_Test_Util::call_method( $field, 'prepare_password_strength_meter', array() );
		$this->assertAssetNotEnqueued( 'script', 'password-strength-meter' );
		$this->assertFalse( llms()->assets->is_inline_enqueued( 'llms-pw-strength-settings' ) );

	}

	/**
	 * Test prepare_password_strength_meter() for script enqueue: enqueued deferred on `wp_enqueue_scripts` hook firing.
	 *
	 * @since 5.10.0
	 *
	 * @return void
	 */
	public function test_prepare_password_strength_meter_assets_enqueued_deferred() {

		$field = new LLMS_Form_Field();

		// Enqueued.
		LLMS_Unit_Test_Util::call_method( $field, 'prepare_password_strength_meter', array() );

		do_action( 'wp_enqueue_scripts' );

		$this->assertAssetIsEnqueued( 'script', 'password-strength-meter' );
		$this->assertTrue( llms()->assets->is_inline_enqueued( 'llms-pw-strength-settings' ) );

		// Pretend wp_enqueue_scripts was never called, for further tests.
		global $wp_actions;
		unset( $wp_actions[ 'wp_enqueue_scripts' ] );

	}

	/**
	 * Test prepare_password_strength_meter() for script enqueue: enqueued right away b/c `wp_enqueue_scripts` already fired.
	 *
	 * @since 5.10.0
	 *
	 * @return void
	 */
	public function test_prepare_password_strength_meter_assets_enqueued_right_away() {

		$field = new LLMS_Form_Field();

		do_action( 'wp_enqueue_scripts' );

		// Enqueued.
		LLMS_Unit_Test_Util::call_method( $field, 'prepare_password_strength_meter', array() );

		$this->assertAssetIsEnqueued( 'script', 'password-strength-meter' );
		$this->assertTrue( llms()->assets->is_inline_enqueued( 'llms-pw-strength-settings' ) );

	}

	/**
	 * Test prepare_value() for a password field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_value_for_password() {

		$field = new LLMS_Form_Field( array(
			'type'  => 'password',
			'name'  => 'test_field',
		) );

		$settings = $field->get_settings();

		$this->assertEmpty( $settings['value'] );

	}

	/**
	 * Test prepare_value() with user-posted data
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_prepare_value_with_posted_data() {

		$this->mockPostRequest( array(
			'test_field' => 'submitted value',
		) );

		$field = new LLMS_Form_Field( array(
			'name'  => 'test_field',
		) );

		$settings = $field->get_settings();

		$this->assertEquals( 'submitted value', $settings['value'] );

	}

	/**
	 * Test field html generated on submision when value is an array
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_array_value_only_post_request() {

		$checkbox_options = array(
			'yes_key' => 'Yes',
			'no_key'  => 'No',
		);

		$form_field_conf = array(
			'name'    => 'array_type',
			'type'    => 'checkbox',
			'options' => $checkbox_options,
		);

		// Simulate a field submission where both the checkboxes are checked.
		$this->mockPostRequest(
			array(
				'array_type' => array_keys( $checkbox_options ),
			)
		);

		// Create a form field.
		$form_field = llms_form_field(
			$form_field_conf,
			false
		);

		// Expect the html has 2 "checked" checkboxes.
		$this->assertEquals(
			2,
			substr_count(
				$form_field,
				'"checked"'
			)
		);

		// Simulate a field submission where only one checkbox is checked.
		$this->mockPostRequest(
			array(
				'array_type' => array_keys( $checkbox_options )[1],
			)
		);

		// Create a form field.
		$form_field = llms_form_field(
			$form_field_conf,
			false
		);

		// Expect the html has 1 "checked" checkbox.
		$this->assertEquals(
			1,
			substr_count(
				$form_field,
				'"checked"'
			)
		);

	}

	/**
	 * Test llms_form_field when passing an user
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_with_user_as_data_source() {

		$opts = array(
			'id'         => 'checkbox_store',
			'type'       => 'checkbox',
			'options'    => array(
				'mock_val'  => 'Mock val',
				'mock_val2' => 'Mock val 2',
			),
			'data_store' => 'usermeta',
		);

		// User doesn't have value stored.
		$this->get_user_with_meta( 'checkbox_store' );
		$user_id = get_current_user_id();
		// Log-out.
		wp_set_current_user( null );

		$html = llms_form_field( $opts, false, $user_id );
		$this->assertStringNotContains( 'checked="checked"', $html );

		// User has value stored.
		$this->get_user_with_meta( 'checkbox_store', array( 'mock_val2' ) );
		$user_id = get_current_user_id();
		// Log-out.
		wp_set_current_user( null );

		$html = llms_form_field( $opts, false, $user_id );
		$this->assertStringContains(
			'<input checked="checked" class="llms-field-checkbox" id="checkbox_store--mock_val2" name="checkbox_store[]" type="checkbox" value="mock_val2" />',
			$html
		);

		// Log in the last user.
		wp_set_current_user( $user_id );
		$html = llms_form_field( $opts, false, $user_id );
		$this->assertStringContains(
			'<input checked="checked" class="llms-field-checkbox" id="checkbox_store--mock_val2" name="checkbox_store[]" type="checkbox" value="mock_val2" />',
			$html
		);

		// Log in the last user.
		wp_set_current_user( $user_id );
		// Pass a non existing user.
		$html = llms_form_field( $opts, false, $user_id + 1 );
		$this->assertStringNotContains( 'checked="checked"', $html );

	}

	/**
	 * Test LLMS_Form_Field data_source and data_source_type props setting
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_field_data_source_and_type() {
		$opts = array(
			'type'       => 'button',
			'value'      => 'Button Text',
			'data_store' => 'usermeta',
		);

		// Pass a WP Post in place of a user.
		$post  = $this->factory->post->create_and_get();
		$field = new LLMS_Form_Field( $opts, $post );

		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source' ) );
		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source_type' ) );

		// Pass a WP User.
		$user  = $this->factory->user->create_and_get();
		$field = new LLMS_Form_Field( $opts, $user );

		$this->assertEquals( $user, LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source' ) );
		$this->assertEquals( 'wp_user', LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source_type' ) );

		// Pass a WP User ID.
		$opts['data_store'] = 'users'; // Test it works with the users table as store too.
		$field = new LLMS_Form_Field( $opts, $user->ID );

		$this->assertEquals( $user, LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source' ) );
		$this->assertEquals( 'wp_user', LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source_type' ) );

		// Pass a non existing WP User ID.
		$field = new LLMS_Form_Field( $opts, $user->ID + 1 );

		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source' ) );
		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source_type' ) );

		// Pass an existing WP User ID but change the data_store to something different from 'usermeta' or 'users'.
		$opts['data_store'] = 'whatever';
		$field = new LLMS_Form_Field( $opts, $user->ID );

		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source' ) );
		$this->assertNull( LLMS_Unit_Test_Util::get_private_property_value( $field, 'data_source_type' ) );

	}

}
