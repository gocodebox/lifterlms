<?php
/**
 * LLMS_Forms_Dynamic_Fields file
 *
 * @package LifterLMS/Classes/Forms
 *
 * @since 5.0.0
 * @version 5.1.0
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
	 * @since 5.1.0 Added logic to make sure forms have all the required fields.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_get_form_blocks', array( $this, 'add_password_strength_meter' ), 10, 2 );
		add_filter( 'llms_get_form_blocks', array( $this, 'maybe_add_required_block_fields' ), 10, 3 );
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
	 * @param string  $id           The ID of the field to find.
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
	 * Retrieve the fields required for a given location based on user state
	 *
	 * @since 5.1.0
	 *
	 * @param string $location The request form location ID.
	 * @param array  $args     Additional arguments passed to the short-circuit filter.
	 * @return array[] Array of field_id => block_name required or an empty array if no fields required.
	 */
	private function get_required_fields_for_location( $location, $args ) {

		$fields = array();

		if (
			( ! is_user_logged_in() && in_array( $location, array( 'checkout', 'registration' ), true ) ) ||
				( is_user_logged_in() && 'account' === $location ) ) {
			$fields = array(
				// Field ID => block name.
				'email_address' => 'email',
				'password'      => 'password',
			);
		}

		/**
		 * Filters the required block fields to add to the form
		 *
		 * @since 5.1.0
		 *
		 * @param array[] $fields   Array of field_id => block_name required.
		 * @param string  $location The request form location ID.
		 * @param array   $args     Additional arguments passed to the short-circuit filter.
		 */
		return apply_filters( 'llms_forms_required_block_fields', $fields, $location, $args );

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
	 * @param array[] $blocks   Array of parsed WP_Block arrays.
	 * @param string  $location The form location ID.
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
	 * Maybe add the required email and password block to a form.
	 *
	 * @since 5.1.0
	 * @since 5.4.1 Make sure added reusable blocks contain the actual required field,
	 *              otherwise fall back on the dynamically generated ones.
	 *
	 * @param array[] $blocks   Array of parsed WP_Block arrays.
	 * @param string  $location The request form location ID.
	 * @param array   $args     Additional arguments passed to the short-circuit filter.
	 * @return array[]
	 */
	public function maybe_add_required_block_fields( $blocks, $location, $args ) {

		$fields_to_require = $this->get_required_fields_for_location( $location, $args );
		if ( empty( $fields_to_require ) ) {
			return $blocks;
		}

		foreach ( $fields_to_require as $field_id => $field_block_name ) {

			$block = $this->find_block( $field_id, $blocks );

			if ( ! empty( $block ) ) {
				// Fields in non checkout forms are always visible - see LLMS_Forms::get_form_html().
				$blocks = 'checkout' === $location ? $this->make_block_visible( $block[1], $blocks, $block[0] ) : $blocks;
				unset( $fields_to_require[ $field_id ] );
				if ( empty( $fields_to_require ) ) { // All the required blocks are present.
					return $blocks;
				}
			}
		}

		return $this->add_required_block_fields( $fields_to_require, $blocks, $location );

	}

	/**
	 * Add required block fields.
	 *
	 * @since 5.4.1
	 *
	 * @param string[] $fields_to_require Array of field ids to require.
	 * @param array[]  $blocks            Array of parsed WP_Block arrays to add required fields to.
	 * @param string   $location          The request form location ID.
	 * @return array[]
	 */
	private function add_required_block_fields( $fields_to_require, $blocks, $location ) {

		$blocks_to_add = array();
		foreach ( $fields_to_require as $field_id => $block_to_add ) {

			// If a reusable block exists for the field, use it. Otherwise use a dynamically generated block from the template schema.
			$use_reusable = LLMS_Form_Templates::find_reusable_block( $block_to_add );
			$block        = LLMS_Form_Templates::get_block( $block_to_add, $location, $use_reusable );

			if ( $use_reusable ) {
				// Load reusable block.
				$_blocks = LLMS_Forms::instance()->load_reusable_blocks( array( $block ) );
				// The reusable block doesn't contain the needed block, use a dynamically generated block from the template schema.
				if ( empty( $_blocks ) || ! $this->find_block( $field_id, $_blocks ) ) {
					$_blocks = array( LLMS_Form_Templates::get_block( $block_to_add, $location, false ) );
				}
				$block = $_blocks[0];
			}

			$blocks_to_add[] = $block;
		}

		// Make blocks to add visible.
		$blocks_to_add = 'checkout' === $location ? array_map( array( $this, 'make_all_visible' ), $blocks_to_add ) : $blocks_to_add;

		return array_merge(
			$blocks,
			$blocks_to_add
		);

	}

	/**
	 * Make a block visible within its list of blocks
	 *
	 * @since 5.1.0
	 *
	 * @param array   $block       Parsed WP_Block array.
	 * @param array[] $blocks      Array of parsed WP_Block arrays.
	 * @param int     $block_index Index of the block within the `$blocks` list.
	 *                             If the block is in a group, this is the the index of the item's parent.
	 * @return array[]
	 */
	private function make_block_visible( $block, $blocks, $block_index ) {

		if ( LLMS_Forms::instance()->is_block_visible_in_list( $block, array( $blocks[ $block_index ] ) ) ) {
			return $blocks;
		}

		// If the block has a confirm group, use that.
		$confirm = $this->get_confirm_group( $block['attrs']['id'], array( $blocks[ $block_index ] ) );

		$block_to_add = empty( $confirm ) ? $block : $confirm;

		$replace = true;
		// Insert the visible block before the invisible one if the block is in a group,
		// so to avoid the replacement of the whole group which might contain other required fields.
		// But replace the invisible with the visible if otherwise.
		if ( $block_to_add !== $blocks[ $block_index ] ) {
			$replace = false;
			$this->remove_block( $block_to_add, $blocks );
		}

		// Make the block to add and its children visible.
		$block_to_add = $this->make_all_visible( $block_to_add );

		array_splice( $blocks, $block_index, (int) ( ! empty( $replace ) ), array( $block_to_add ) );

		return $blocks;

	}

	/**
	 * Remove block from the list which contains it.
	 *
	 * @since 5.1.0
	 *
	 * @param array   $block  Parsed WP_Block array.
	 * @param array[] $blocks Array of parsed WP_Block arrays (passed by reference).
	 * @param array   $parent Optional. Parsed WP_Block array representing the parent block of the `$blocks`, in case this is a list of inner blocks. Default null.
	 *                        Passed by reference.
	 * @return bool
	 */
	private function remove_block( $block, &$blocks, &$parent = null ) {

		foreach ( $blocks as $index => &$_block ) {

			if ( $_block === $block ) {
				array_splice( $blocks, $index, 1 ); // Remove and re-index.
				// If we're removing an innerBlock we need to update the innerContent too, to avoid wp calling the render method on nulls.
				if ( ! is_null( $parent ) ) {
					$this->remove_inner_block_from_inner_content( $index, $parent );
				}
				return true;
			}

			if ( ! empty( $_block['innerBlocks'] ) ) {
				$removed = $this->remove_block( $block, $_block['innerBlocks'], $_block );
			}
			if ( ! empty( $removed ) ) { // Break as soon as the desired block is removed from one of the innerBlocks.
				return true;
			}
		}

		return false;

	}

	/**
	 * Remove inner block reference from inner content
	 *
	 * See WP_Block::inner_content documentation.
	 *
	 * The inner_content block's property is an array of string fragments and null markers where inner blocks were found.
	 * So here we cycle over the block's parent innerContent field looking for references to innerBlocks (null).
	 * When we found a positional correspondance between the removed innerBlock and its refernce in innerContent we remove the latter too.
	 *
	 * @since 5.1.0
	 *
	 * @param int   $inner_block_index The index of the inner block in the block's innerBlocks list.
	 * @param array $parent            Parsed WP_Block array representing the inner blocks parent. Passed by reference.
	 */
	private function remove_inner_block_from_inner_content( $inner_block_index, &$parent ) {

		$inner_block_in_content_index = 0;
		foreach ( $parent['innerContent'] as $chunk_index => $chunk ) {
			if ( ! is_string( $chunk ) && $inner_block_index === $inner_block_in_content_index++ ) {
				array_splice( $parent['innerContent'], $chunk_index, 1 ); // Remove and re-index.
				break;
			}
		}

	}

	/**
	 * Make the block and its children visible
	 *
	 * @since 5.1.0
	 *
	 * @param array $block A parsed WP_Block.
	 * @return array
	 */
	private function make_all_visible( $block ) {

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $index => $inner_block ) {
				$block['innerBlocks'][ $index ] = $this->make_all_visible( $inner_block );
			}
		}
		$block['attrs']['llms_visibility'] = '';

		return $block;

	}

	/**
	 * Get confirm group in a list of blocks for a given block id
	 *
	 * @since 5.1.0
	 *
	 * @param string  $id     The ID of the field to find the confirm group for.
	 * @param array[] $blocks WP_Block list.
	 * @return array
	 */
	private function get_confirm_group( $id, $blocks ) {

		foreach ( $blocks as $index => $block ) {

			if ( $block['innerBlocks'] ) {
				if ( ( 'llms/form-field-confirm-group' === $block['blockName'] ) &&
						$this->find_block( $id, $block['innerBlocks'] ) ) {
					return $block;
				}
				$inner = $this->get_confirm_group( $id, $block['innerBlocks'] );
				if ( false !== $inner ) {
					return $inner;
				}
			}
		}

		return false;
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
