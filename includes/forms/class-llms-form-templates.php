<?php
/**
 * LLMS_Form_Templates class.
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage llms_form post type templates
 *
 * Handles creation of reusable blocks for the core/default fields used
 * by the default checkout, registration, and account edit forms.
 *
 * @since [version]
 */
class LLMS_Form_Templates {

	/**
	 * Transform a block definition into a confirm group
	 *
	 * @since [version]
	 *
	 * @param array $block A WP_Block definition array.
	 * @return array
	 */
	private static function add_confirm_group( $block ) {

		$inner = array(
			self::get_confirm_group_controller( $block ),
			self::get_confirm_group_controlled( $block ),
		);

		if ( is_rtl() ) {
			$inner = array_reverse( $inner );
		}

		$attrs = array(
			'fieldLayout' => 'columns',
		);

		if ( ! empty( $block['attrs']['llms_visibility'] ) ) {
			$attrs['llms_visibility'] = $block['attrs']['llms_visibility'];
		}

		return array(
			'blockName'   => 'llms/form-field-confirm-group',
			'innerBlocks' => $inner,
			'attrs'       => $attrs,
		);

	}

	/**
	 * Create a wp_block (resuable block) post for a given field id
	 *
	 * This method will attempt to use an existing reusable block field
	 * if it already exists and will only create it if one isn't found.
	 *
	 * @since [version]
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @return int Returns the WP_Post ID of the the wp_block post type or `0` on failure.
	 */
	private static function create_reusable_block( $field_id ) {

		$existing = self::find_reusable_block( $field_id );
		if ( $existing ) {
			return $existing->ID;
		}

		$block      = self::get_reusable_block_schema( $field_id );
		$post_title = $block['title'];

		unset( $block['title'] );

		if ( ! empty( $block['confirm'] ) ) {
			$block = self::add_confirm_group( $block );
		}

		$args = array(
			'post_title'   => $post_title,
			'post_content' => serialize_blocks( self::prepare_blocks( array( $block ) ) ),
			'post_status'  => 'publish',
			'post_type'    => 'wp_block',
			'meta_input'   => array(
				'_is_llms_field' => 'yes',
				'_llms_field_id' => $field_id,
			),
		);

		return wp_insert_post( $args );

	}

	/**
	 * Locates an existing wp_block post by field id
	 *
	 * @since [version]
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @return WP_Post|boolean Returns the post object or false if not found.
	 */
	private static function find_reusable_block( $field_id ) {

		$query = new WP_Query(
			array(
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'post_type'      => 'wp_block',
				'meta_key'       => '_llms_field_id',
				'meta_value'     => $field_id,
			)
		);

		return $query->posts ? $query->posts[0] : false;

	}

	/**
	 * Creates a WP_Block array definition for the confirmation (controlled) block in a confirm group
	 *
	 * @since [version]
	 *
	 * @param array $block A WP_Block definition array for the primary/default block in the group.
	 * @return array A new WP_Block definition array for the controlled block.
	 */
	private static function get_confirm_group_controlled( $block ) {

		$block['blockName'] = 'llms/form-field-text';

		$block['attrs'] = wp_parse_args(
			array(
				'field'               => $block['confirm'],
				'id'                  => $block['attrs']['id'] . '_confirm',
				'name'                => $block['attrs']['id'] . '_confirm',
				'label'               => sprintf( __( 'Confirm %s', 'lifterlms' ), $block['attrs']['label'] ),
				'columns'             => 6,
				'last_column'         => is_rtl() ? false : true,
				'isConfirmationField' => true,
				'llms_visibility'     => 'off',
				'match'               => $block['attrs']['id'],
				'data_store'          => false,
				'data_store_key'      => false,
			),
			$block['attrs']
		);

		return $block;

	}

	/**
	 * Creates a WP_Block array definition for the primary (controller) block in a confirm group
	 *
	 * @since [version]
	 *
	 * @param array $block A WP_Block definition array for the primary/default block in the group.
	 * @return array A new WP_Block definition array for the controller block.
	 */
	private static function get_confirm_group_controller( $block ) {

		$block['attrs'] = wp_parse_args(
			array(
				'columns'                    => 6,
				'last_column'                => is_rtl() ? true : false,
				'isConfirmationControlField' => true,
				'llms_visibility'            => 'off',
				'match'                      => $block['attrs']['id'] . '_confirm',
			),
			$block['attrs']
		);

		return $block;

	}

	/**
	 * Retrieves a core/block WP_Block array for a given default/core field
	 *
	 * This method will attempt to use an existing wp_block for the given field id
	 * if it exists, and when not found creates a new one.
	 *
	 * @since [version]
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @return array A WP_Block definition array.
	 */
	private static function get_reusable_block( $field_id ) {

		$ref = self::create_reusable_block( $field_id );

		return array(
			'blockName'    => 'core/block',
			'attrs'        => compact( 'ref' ),
			'innerContent' => array(),
		);
	}

	/**
	 * Retrieves the schema definition for a default/core reusable block
	 *
	 * @since [version]
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @return array The block definition schema. This is a WP_Block array definition but missing some data that is automatically populated before serialization.
	 */
	private static function get_reusable_block_schema( $field_id ) {

		$list = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-reusable-blocks.php';

		$definition = empty( $list[ $field_id ] ) ? array() : self::prepare_block_attrs( $list[ $field_id ] );

		/**
		 * Filters the result of a schema definition.
		 *
		 * This hook can be used to add definitions for custom (non-core) fields or to modify a core definition.
		 *
		 * @since [version]
		 *
		 * @param array  $definition The schema definition.
		 * @param string $field_id   The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
		 */
		return apply_filters( 'llms_get_reusable_block_schema', $definition, $field_id );

	}

	/**
	 * Prepares block attributes for a given reusable block
	 *
	 * This method loads a reusable block from the blocks schema and attempts to locate a user information field
	 * for the given field block from the user information fields schema.
	 *
	 * The field is matched by the block's "id" attribute which should match a user information field's "id" attribute.
	 *
	 * When a match is found, the information field data is merged into the block data and the settings are converted from field settings
	 * to block attributes.
	 *
	 * @since [version]
	 *
	 * @param array $block A partial WP_Block array used to create a reusable block.
	 * @return array
	 */
	private static function prepare_block_attrs( $block ) {

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as &$inner_block ) {
				$inner_block = self::prepare_block_attrs( $inner_block );
			}
		} elseif ( ! empty( $block['attrs']['id'] ) ) {

			// If we find a field, merge the block into the field and convert it to block attributes.
			$field          = llms_get_user_information_field( $block['attrs']['id'] );
			$block['attrs'] = $field ? LLMS_Forms::instance()->convert_settings_to_block_attrs( wp_parse_args( $field, $block['attrs'] ) ) : $block['attrs'];

			// Property used for internal purposes.
			unset( $block['attrs']['group'] );

		}

		return $block;

	}

	/**
	 * Retrieve the block template HTML for a given location.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location. Accepts "checkout", "registration", or "account".
	 * @return string
	 */
	public static function get_template( $location ) {

		$blocks = array();

		// Email and password are added in different locations depending on the form.
		$base = array(
			self::get_reusable_block( 'email' ),
			self::get_reusable_block( 'password' ),
		);

		// Username only added when option is off on legacy sites.
		if ( 'account' !== $location && ! llms_parse_bool( get_option( 'lifterlms_registration_generate_username', 'yes' ) ) ) {
			array_unshift( $base, self::get_reusable_block( 'username' ) );
		}

		// Email and password go first on checkout/reg forms.
		if ( 'account' !== $location ) {
			$blocks = array_merge( $base, $blocks );
		}

		$blocks[] = self::get_reusable_block( 'name' );

		// Display name on account only, users can add to other forms if desired.
		if ( 'account' === $location ) {
			$blocks[] = self::get_reusable_block( 'display_name' );
		}

		$blocks[] = self::get_reusable_block( 'address' );
		$blocks[] = self::get_reusable_block( 'phone' );

		if ( 'registration' === $location ) {
			$blocks[] = self::get_voucher_block();
		}

		// Email and password go at the end on the account form.
		if ( 'account' === $location ) {
			$blocks = array_merge( $blocks, $base );
		}

		return serialize_blocks( array_filter( $blocks ) );

	}

	/**
	 * Retrieve block for the voucher row.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private static function get_voucher_block() {

		// Don't include voucher if legacy option has vouchers hidden.
		$option = get_option( 'lifterlms_voucher_field_registration_visibility', 'optional' );
		if ( 'hidden' === $option ) {
			return array();
		}

		return array(
			'blockName'    => 'llms/form-field-redeem-voucher',
			'attrs'        => array(
				'id'             => 'llms_voucher',
				'label'          => __( 'Have a voucher?', 'lifterlms' ),
				'placeholder'    => __( 'Voucher Code', 'lifterlms' ),
				'required'       => ( 'required' === $option ),
				'toggleable'     => true,
				'data_store'     => false,
				'data_store_key' => false,
			),
			'innerContent' => array(),
		);

	}

	/**
	 * Recursively prepare a list of blocks to ensure it can be passed into serialize_blocks() without error
	 *
	 * @since [version]
	 *
	 * @param array[] $blocks Array of WP_Block definition arrays.
	 * @return array[]
	 */
	private static function prepare_blocks( $blocks ) {

		foreach ( $blocks as &$block ) {

			$block = wp_parse_args(
				$block,
				array(
					'attrs'       => array(),
					'innerBlocks' => array(),
				)
			);

			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::prepare_blocks( $block['innerBlocks'] );
			}

			// WP core serialize_block() doesn't work unless this...
			$block['innerContent'] = array_fill( 0, count( $block['innerBlocks'] ), null );

		}

		return $blocks;

	}

}
