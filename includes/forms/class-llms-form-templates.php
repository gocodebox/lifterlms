<?php
/**
 * LLMS_Form_Templates class.
 *
 * @package LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 5.1.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage llms_form post type templates
 *
 * Handles creation of reusable blocks for the core/default fields used
 * by the default checkout, registration, and account edit forms.
 *
 * @since 5.0.0
 */
class LLMS_Form_Templates {

	/**
	 * Transform a block definition into a confirm group
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
	 * @since 5.1.1 Run serialized block content through `wp_slash()` to preserve special characters converted to character codes.
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @return int Returns the WP_Post ID of the the wp_block post type or `0` on failure.
	 */
	private static function create_reusable_block( $field_id ) {

		$existing = self::find_reusable_block( $field_id );
		if ( $existing ) {
			return $existing->ID;
		}

		$block_data = self::get_block_data( $field_id );

		$args = array(
			'post_title'   => $block_data['title'],
			'post_content' => wp_slash( serialize_blocks( $block_data['block'] ) ),
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
	 * @since 5.0.0
	 * @since 5.1.0 Method access changed from private to public.
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @return WP_Post|boolean Returns the post object or false if not found.
	 */
	public static function find_reusable_block( $field_id ) {

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
	 * Retrieve a block array for use in a template
	 *
	 * Returns a reusable block when `$reusable` is `true` or returns a regular
	 * block modified by legacy options for the given location when `$reusable` is `false`.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Method access set to public.
	 *
	 * @param string  $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @param string  $location Form location. Accepts "checkout", "registration", or "account".
	 * @param boolean $reusable Whether or not a reusable block should be retrieved.
	 * @return array
	 */
	public static function get_block( $field_id, $location, $reusable ) {

		if ( $reusable ) {
			return self::get_reusable_block( $field_id );
		}

		$legacy_opt = self::get_legacy_option( $field_id, $location );
		// Add a confirm group for email when confirmation is set or for password fields.
		$confirm    = ( ( 'email' === $field_id && 'yes' === $legacy_opt ) || 'password' === $field_id );
		$block_data = self::get_block_data( $field_id, $confirm );
		if ( 'hidden' === $legacy_opt ) {
			return array();
		}

		$block = $block_data['block'][0];

		if ( in_array( $legacy_opt, array( 'required', 'optional' ), true ) ) {
			$block = self::set_required_atts( $block, ( 'required' === $legacy_opt ) );
		}

		return $block;
	}

	/**
	 * Retrieve data for a given field by id
	 *
	 * @since 5.0.0
	 *
	 * @param string  $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @param boolean $confirm  If `true` and the schema includes a confirmation field, will convert the field to a confirm group.
	 * @return array Returns an array containing the block data and title.
	 */
	private static function get_block_data( $field_id, $confirm = true ) {

		$block = self::get_reusable_block_schema( $field_id );
		$title = $block['title'];
		unset( $block['title'] );

		if ( $confirm && ! empty( $block['confirm'] ) ) {
			$block = self::add_confirm_group( $block );
		}

		$block = self::prepare_blocks( array( $block ) );
		return compact( 'title', 'block' );
	}

	/**
	 * Creates a WP_Block array definition for the confirmation (controlled) block in a confirm group
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * Retrieves legacy option's value for a given field and location
	 *
	 * @since 5.0.0
	 *
	 * @param string $field_id The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
	 * @param string $location Form location. Accepts "checkout", "registration", or "account".
	 * @return string
	 */
	private static function get_legacy_option( $field_id, $location ) {

		$name_map = array(
			'address' => 'address',
			'email'   => 'email_confirmation',
			'name'    => 'names',
			'phone'   => 'phone',
		);

		$val = '';

		if ( array_key_exists( $field_id, $name_map ) ) {

			$key = sprintf( 'lifterlms_user_info_field_%1$s_%2$s_visibility', $name_map[ $field_id ], $location );
			$val = get_option( $key );

		}

		return $val;
	}

	/**
	 * Retrieves a core/block WP_Block array for a given default/core field
	 *
	 * This method will attempt to use an existing wp_block for the given field id
	 * if it exists, and when not found creates a new one.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
		 * @since 5.0.0
		 *
		 * @param array  $definition The schema definition.
		 * @param string $field_id   The field's identifier as found in the block schema list returned by LLMS_Form_Templates::get_reusable_block_schema().
		 */
		return apply_filters( 'llms_get_reusable_block_schema', $definition, $field_id );
	}

	/**
	 * Retrieve the block template HTML for a given location.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location. Accepts "checkout", "registration", or "account".
	 * @return string
	 */
	public static function get_template( $location ) {

		/**
		 * Filters whether or not reusable blocks should be used when generating a form template
		 *
		 * By default when migrating from 4.x, non-reusable blocks will be used in order to ensure legacy settings
		 * are transferred during an upgrade to 5.x. However, on a "clean" install of 5.x, reusable blocks will be
		 * used in favor of regular blocks.
		 *
		 * @since 5.0.0
		 *
		 * @param boolean $use_reusable Whether or not to use reusable blocks.
		 */
		$use_reusable = apply_filters( 'llms_blocks_template_use_reusable_blocks', ( 'not-set' === get_option( 'lifterlms_registration_generate_username', 'not-set' ) ) );

		$blocks = self::get_template_blocks( $location, $use_reusable );

		return serialize_blocks( $blocks );
	}

	/**
	 * Retrieve a list of blocks for the given template
	 *
	 * @since 5.0.0
	 *
	 * @param string  $location Form location id.
	 * @param boolean $reusable Whether or not reusable blocks should be used.
	 * @return array[]
	 */
	private static function get_template_blocks( $location, $reusable ) {

		$blocks = array();

		// Email and password are added in different locations depending on the form.
		$base = array(
			self::get_block( 'email', $location, $reusable ),
			self::get_block( 'password', $location, $reusable ),
		);

		// Username only added when option is off on legacy sites.
		if ( 'account' !== $location && ! llms_parse_bool( get_option( 'lifterlms_registration_generate_username', 'yes' ) ) ) {
			array_unshift( $base, self::get_reusable_block( 'username' ) );
		}

		// Email and password go first on checkout/reg forms.
		if ( 'account' !== $location ) {
			$blocks = array_merge( $base, $blocks );
		}

		$blocks[] = self::get_block( 'name', $location, $reusable );

		// Display name on account only, users can add to other forms if desired.
		if ( 'account' === $location ) {
			$blocks[] = self::get_block( 'display_name', $location, $reusable );
		}

		$blocks[] = self::get_block( 'address', $location, $reusable );
		$blocks[] = self::get_block( 'phone', $location, $reusable );

		if ( 'registration' === $location ) {
			$blocks[] = self::get_voucher_block();
		}

		// Email and password go at the end on the account form.
		if ( 'account' === $location ) {
			$blocks = array_merge( $blocks, $base );
		}

		return array_filter( $blocks );
	}

	/**
	 * Retrieve block for the voucher row.
	 *
	 * @since 5.0.0
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
	 * Prepares block attributes for a given reusable block
	 *
	 * This method loads a reusable block from the blocks schema and attempts to locate a user information field
	 * for the given field block from the user information fields schema.
	 *
	 * The field is matched by the block's "id" attribute which should match a user information field's "name" attribute.
	 *
	 * When a match is found, the information field data is merged into the block data and the settings are converted from field settings
	 * to block attributes.
	 *
	 * @since 5.0.0
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

		}

		return $block;
	}

	/**
	 * Recursively prepare a list of blocks to ensure it can be passed into serialize_blocks() without error
	 *
	 * @since 5.0.0
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

	/**
	 * Modifies the `required` block attribute
	 *
	 * @since 5.0.0
	 *
	 * @param array   $block    A WP_Block definition array.
	 * @param boolean $required Desired value of the required attribute.
	 */
	private static function set_required_atts( $block, $required ) {

		if ( isset( $block['attrs']['required'] ) && 'llms/form-field-user-address-street-secondary' !== $block['blockName'] ) {
			$block['attrs']['required'] = $required;
		}

		foreach ( $block['innerBlocks'] as &$inner_block ) {
			$inner_block = self::set_required_atts( $inner_block, $required );
		}

		return $block;
	}
}
