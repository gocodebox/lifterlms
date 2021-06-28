<?php
/**
 * LLMS_Forms_Dynamic_Fields file
 *
 * @package LifterLMS/Classes/Forms
 *
 * @since 5.0.0
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage dynamically generated fields added to the form outside of the block editor
 *
 * @since 5.0.0
 */
class LLMS_Forms_Dynamic_Fields {

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_get_form_blocks', array( $this, 'add_password_strength_meter' ), 10, 2 );

		add_filter( 'llms_get_form_blocks', array( $this, 'modify_account_form' ), 15, 2 );

	}

	/**
	 * Creates a new HTML block with the given settings and inserts it into an existing blocks array at the specified location
	 *
	 * @since 5.0.0
	 *
	 * @param array[] $blocks         Array of WP_Block arrays.
	 * @param array   $block_settings Block attributes used to generate a new custom HTML field block.
	 * @param integer $index          Desired index of the new block.
	 *
	 * @return array[]
	 */
	private function add_block( $blocks, $block_settings, $index ) {

		// Make the new block.
		$add_block = parse_blocks(
			LLMS_Forms::instance()->get_custom_field_block_markup( $block_settings )
		);

		// Add it into the form after the specified index.
		array_splice( $blocks, $index + 1, 0, $add_block );

		return $blocks;

	}

	/**
	 * Adds a password strength meter to a block list
	 *
	 * This function will programmatically add an html block containing the necessary
	 * markup for the password strength meter to function.
	 *
	 * This will locate the user password block and output the meter immediately after
	 * the block. If the password block is within a group it'll output it after the
	 * group block.
	 *
	 * @since 5.0.0
	 * @since 5.0.1 Add `aria-live=polite` to ensure password strength is announced for screen readers.
	 *
	 * @param array[] $blocks WP_Block list.
	 * @return array[]
	 */
	public function add_password_strength_meter( $blocks, $location ) {

		$password = $this->find_block( 'password', $blocks );

		// No password field in the form.
		if ( ! $password ) {
			return $blocks;
		}

		list( $index, $block ) = $password;

		// Meter not enabled.
		if ( empty( $block['attrs']['meter'] ) || ! llms_parse_bool( $block['attrs']['meter'] ) ) {
			return $blocks;
		}

		$meter_settings = array(
			'type'            => 'html',
			'id'              => 'llms-password-strength-meter',
			'classes'         => 'llms-password-strength-meter',
			'description'     => ! empty( $block['attrs']['meter_description'] ) ? $block['attrs']['meter_description'] : '',
			'min_length'      => ! empty( $block['attrs']['html_attrs']['minlength'] ) ? $block['attrs']['html_attrs']['minlength'] : '',
			'min_strength'    => ! empty( $block['attrs']['min_strength'] ) ? $block['attrs']['min_strength'] : '',
			'llms_visibility' => ! empty( $block['attrs']['llms_visibility'] ) ? $block['attrs']['llms_visibility'] : '',
			'attributes'      => array(
				'aria-live' => 'polite',
			),
		);

		if ( 'account' === $location ) {
			$meter_settings['wrapper_classes'] = 'llms-visually-hidden-field';
		}

		/**
		 * Filters the settings used to create the dynamic password strength meter block
		 *
		 * @since 5.0.0
		 *
		 * @param array $meter_settings Array or block attributes/settings.
		 */
		$meter_settings = apply_filters( 'llms_password_strength_meter_field_settings', $meter_settings );

		return $this->add_block( $blocks, $meter_settings, $index );

	}

	/**
	 * Finds a block with the specified ID within a list of blocks
	 *
	 * There's a gotcha with this function... if a user password field is placed within a wp core columns block
	 * the password strength meter will be added outside the column the password is contained within.
	 *
	 * @since 5.0.0
	 *
	 * @param string  $id           ThHe ID of the field to find.
	 * @param array[] $blocks       WP_Block list.
	 * @param integer $parent_index Top level index of the parent block. Used to hold a reference to the current index within the toplevel
	 *                              blocks of the form when looking into the innerBlocks of a block.
	 * @return boolean|array Returns `false` when the block cannot be found in the given list, otherwise returns a numeric array
	 *                       where item `0` is the index of the block within the list (the index of the items parent if it's in a
	 *                       group) and item `1` is the block array.
	 */
	private function find_block( $id, $blocks, $parent_index = null ) {

		foreach ( $blocks as $index => $block ) {

			if ( ! empty( $block['attrs']['id'] ) && $id === $block['attrs']['id'] ) {
				return array( is_null( $parent_index ) ? $index : $parent_index, $block );
			}

			if ( $block['innerBlocks'] ) {
				$inner = $this->find_block( $id, $block['innerBlocks'], is_null( $parent_index ) ? $index : $parent_index );
				if ( false !== $inner ) {
					return $inner;
				}
			}
		}

		return false;

	}

	/**
	 * Retrieve the HTML for a field toggle button link
	 *
	 * @since 5.0.0
	 *
	 * @param string $fields      A comma-separated list of selectors for the controlled fields.
	 * @param string $field_label Label for the original field.
	 * @return string
	 */
	private function get_toggle_button_html( $fields, $field_label ) {

		// Translator: %s = user-selected label for the given field being toggled.
		$change_text = sprintf( esc_attr_x( 'Change %s', 'Toggle button for changing email or password', 'lifterlms' ), $field_label );
		$cancel_text = esc_attr_x( 'Cancel', 'Cancel password or email address change button text', 'lifterlms' );

		return '<a class="llms-toggle-fields" data-fields="' . $fields . '" data-change-text="' . $change_text . '" data-cancel-text="' . $cancel_text . '" href="#">' . $change_text . '</a>';

	}

	/**
	 * Modifies account form to improve the UX of editing the email address and password fields
	 *
	 * Adds a "Current Password" field used to verify the existing user password when changing passwords.
	 *
	 * Forces email & password fields to be required and makes them disabled and visually hidden on page load.
	 *
	 * Adds a toggle button for each set of fields, when the toggle is clicked the fields are revealed and enabled
	 * so they can be used. Ensuring that the fields are only required when they're being explicitly changed.
	 *
	 * @since 5.0.0
	 *
	 * @param [type] $blocks [description]
	 * @param [type] $location [description]
	 *
	 * @return array[]
	 */
	public function modify_account_form( $blocks, $location ) {

		// Only add toggles on the account edit form.
		if ( 'account' !== $location ) {
			return $blocks;
		}

		$blocks = $this->modify_toggle_blocks( $blocks );

		foreach ( array( 'email_address', 'password' ) as $id ) {
			$field  = $this->find_block( $id, $blocks );
			$blocks = $field ? $this->{"toggle_for_$id"}( $field, $blocks ) : $blocks;
		}

		return $blocks;

	}

	/**
	 * Modifies block settings for toggle-controlled fields
	 *
	 * @since 5.0.0
	 *
	 * @param array[] $blocks Array of WP_Block arrays.
	 * @return array[]
	 */
	private function modify_toggle_blocks( $blocks ) {

		// List of toggle fields to modify.
		$fields = array(
			'email_address',
			'email_address_confirm',
			'password',
			'password_confirm',
		);

		foreach ( $blocks as &$block ) {

			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->modify_toggle_blocks( $block['innerBlocks'] );
			} elseif ( ! empty( $block['attrs']['id'] ) && in_array( $block['attrs']['id'], $fields, true ) ) {
				$block['attrs']['wrapper_classes'] = 'llms-visually-hidden-field';
				$block['attrs']['disabled']        = true;
				$block['attrs']['required']        = true;
			}
		}

		return $blocks;
	}

	/**
	 * Adds a toggle link button allowing the user to change their email address
	 *
	 * @since 5.0.0
	 *
	 * @param array   $email  Email field data as located by LLMS_Forms_Dynamic_Fields::find_block().
	 * @param array[] $blocks Array of WP_Block arrays.
	 * @return array[]
	 */
	private function toggle_for_email_address( $email, $blocks ) {

		return $this->add_block(
			$blocks,
			array(
				'type'  => 'html',
				'id'    => 'llms-field-toggle--email',
				'value' => $this->get_toggle_button_html( '#email_address,#email_address_confirm', $email[1]['attrs']['label'] ),
			),
			$email[0]
		);

	}


	/**
	 * Adds a current password field and a toggle link button allowing the user to change their password
	 *
	 * @since 5.0.0
	 *
	 * @param array   $password Password field data as located by LLMS_Forms_Dynamic_Fields::find_block().
	 * @param array[] $blocks   Array of WP_Block arrays.
	 * @return array[]
	 */
	private function toggle_for_password( $password, $blocks ) {

		// Add the toggle button.
		$blocks = $this->add_block(
			$blocks,
			array(
				'type'  => 'html',
				'id'    => 'llms-field-toggle--password',
				'value' => $this->get_toggle_button_html( '#password,#password_confirm,#llms-password-strength-meter,#password_current', $password[1]['attrs']['label'] ),
			),
			$password[1]['attrs']['meter'] ? $password[0] + 1 : $password[0]
		);

		/**
		 * Filters the settings used to create the dynamic password strength meter block
		 *
		 * @since 5.0.0
		 *
		 * @param array $settings Array or block attributes/settings.
		 */
		$current_password = apply_filters(
			'llms_current_password_field_settings',
			array(
				'type'            => 'password',
				'id'              => 'password_current',
				'name'            => 'password_current',
				'label'           => sprintf( __( 'Current %s', 'lifterlms' ), $password[1]['attrs']['label'] ),
				'required'        => true,
				'disabled'        => true,
				'data_store_key'  => false,
				'wrapper_classes' => 'llms-visually-hidden-field',
			)
		);
		return $this->add_block( $blocks, $current_password, $password[0] - 1 );

	}

}

return new LLMS_Forms_Dynamic_Fields();
