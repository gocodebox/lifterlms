<?php
/**
 * LLMS_Forms_Dynamic_Fields file
 *
 * @package LifterLMS/Classes/Forms
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage dynamically generated fields added to the form outside of the block editor
 *
 * @since [version]
 */
class LLMS_Forms_Dynamic_Fields {

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_get_form_blocks', array( $this, 'add_password_strength_meter' ), 10 );

	}

	/**
	 * Creates a new HTML block with the given settings and inserts it into an existing blocks array at the specified location
	 *
	 * @since [version]
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

		// Add it into the form after the password block / group.
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
	 * @since [version]
	 *
	 * @param array[] $blocks WP_Block list.
	 * @return array[]
	 */
	public function add_password_strength_meter( $blocks ) {

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

		/**
		 * Filters the settings used to create the dynamic password strength meter block
		 *
		 * @since [version]
		 *
		 * @param array $settings Array or block attributes/settings.
		 * @param  $[name] [<description>]
		 */
		$meter_block = apply_filters( 'llms_password_strength_meter_block_settings', array(
			'type'            => 'html',
			'id'              => 'llms-password-strength-meter',
			'classes'         => 'llms-password-strength-meter',
			'description'     => ! empty( $block['attrs']['meter_description'] ) ? $block['attrs']['meter_description'] : '',
			'min_length'      => ! empty( $block['attrs']['html_attrs']['minlength'] ) ? $block['attrs']['html_attrs']['minlength'] : '',
			'min_strength'    => ! empty( $block['attrs']['min_strength'] ) ? $block['attrs']['min_strength'] : '',
			'llms_visibility' => ! empty( $block['attrs']['llms_visibility'] ) ? $block['attrs']['llms_visibility'] : '',
		) );

		return $this->add_block( $blocks, $meter_block, $index );

	}

	/**
	 * Finds a block with the specified ID within a list of blocks
	 *
	 * There's a gotcha with this function... if a user password field is placed within a wp core columns block
	 * the password strength meter will be added outside the column the password is contained within.
	 *
	 * @since [version]
	 *
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

}

return new LLMS_Forms_Dynamic_Fields();
