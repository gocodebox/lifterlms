<?php
/**
 * Register and manage LifterLMS user forms.
 *
 * @package LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 7.1.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Forms class
 *
 * @since 5.0.0
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 */
class LLMS_Forms {

	use LLMS_Trait_Singleton;

	/**
	 * Minimum Supported WP Version required to manage forms with the block editor UI.
	 */
	const MIN_WP_VERSION = '5.7.0';

	/**
	 * Provide access to the post type manager class
	 *
	 * @var LLMS_Forms_Post_Type
	 */
	public $post_type_manager = null;

	/**
	 * Private Constructor
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	private function __construct() {

		$this->post_type_manager = new LLMS_Form_Post_Type( $this );

		add_filter( 'render_block', array( $this, 'render_field_block' ), 10, 2 );
		add_filter( 'llms_get_form_post', array( $this, 'maybe_load_preview' ) );
	}

	/**
	 * Determines if the WP core requirements are met
	 *
	 * This is used to determine if the block editor can be used to manage forms and fields,
	 * all frontend and server-side handling works on all core supported WP versions.
	 *
	 * @since 5.0.0
	 *
	 * @return boolean
	 */
	public function are_requirements_met() {
		global $wp_version;
		return version_compare( $wp_version, self::MIN_WP_VERSION, '>=' ) || is_plugin_active( 'gutenberg/gutenberg.php' );
	}

	/**
	 * Determine if usernames are enabled on the site.
	 *
	 * This method is used to determine if a username can be used to login / reset a user's password.
	 *
	 * A reference to every form with a username block is stored in an option. The option is an array
	 * of integers, the WP_Post IDs of all the form posts containing a username block.
	 *
	 * If the array is empty, there are no forms with username blocks and, therefore, usernames are disabled.
	 * If the array contains at least one item that means there is a form with a username block in it and,
	 * we therefore consider usernames to be enabled for the site.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public function are_usernames_enabled() {

		$locations = get_option( 'llms_forms_username_locations', array() );

		/**
		 * Use this to explicitly enable of disable username fields.
		 *
		 * Note that usage of this filter will not actually disable the llms/form-field-username block.
		 * It's possible to create a confusing user experience by explicitly disabling usernames and
		 * leaving username field blocks on one or more forms. If you decide to explicitly disable via
		 * this filter you should also remove all the username blocks from all of your forms.
		 *
		 * @since 5.0.0
		 *
		 * @param boolean $enabled Whether or not usernames are enabled.
		 */
		return apply_filters( 'llms_are_usernames_enabled', ! empty( $locations ) );
	}

	/**
	 * Converts a block to settings understandable by `llms_form_field()`
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Added logic to remove invisible fields.
	 *              Added `$block_list` param.
	 *
	 * @param array   $block      A WP Block array.
	 * @param array[] $block_list Optional. The list of WP Block array `$block` comes from. Default is empty array.
	 * @return array
	 */
	private function block_to_field_settings( $block, $block_list = array() ) {

		$is_visible = $this->is_block_visible_in_list( $block, $block_list );

		/**
		 * Filters whether or not invisible fields should be included
		 *
		 * If the block is not visible (according to LLMS block-level visibility settings)
		 * it will return an empty array (signaling the field to be removed).
		 *
		 * @since 5.1.0
		 *
		 * @param boolean $filter     Whether or not invisible fields should be included. Default is `false`.
		 * @param array   $block      A WP Block array.
		 * @param array[] $block_list The list of WP Block array `$block` comes from.
		 */
		if ( ! $is_visible && apply_filters( 'llms_forms_remove_invisible_field', false, $block, $block_list ) ) {
			return array();
		}

		$attrs = $this->convert_settings_format( $block['attrs'], 'block' );

		// If the field is required and hidden it's impossible for the user to fill it out so it gets marked as optional at runtime.
		if ( ! empty( $attrs['required'] ) && ! $is_visible ) {
			$attrs['required'] = false;
		}

		/**
		 * Filter an LLMS_Form_Field settings array after conversion from a field block
		 *
		 * @since 5.0.0
		 * @since 5.1.0 Added `$block_list` param.
		 *
		 * @param array   $attrs      An array of LLMS_Form_Field settings.
		 * @param array   $block      A WP Block array.
		 * @param array[] $block_list The list of WP Block array `$block` comes from.
		 */
		return apply_filters( 'llms_forms_block_to_field_settings', $attrs, $block, $block_list );
	}

	/**
	 * Cascade all llms_visibility attributes down into inner blocks.
	 *
	 * If a parent block has a visibility setting this will apply that visibility to a chlid block *if*
	 * the child block does not have a visibility setting of its own.
	 *
	 * Ultimately this ensures that a field block that's not visible can be marked as "optional" so that
	 * form validation can take place.
	 *
	 * For example, if a columns block is displayed only to logged out users and it's child fields are marked
	 * as required that means that it's required only to logged out users and the field becomes "optional"
	 * (for validation purposes) to logged in users.
	 *
	 * @since 5.0.0
	 *
	 * @param array[]     $blocks     Array of parsed block arrays.
	 * @param string|null $visibility The llms_visibility attribute of the parent block which is applied to all innerBlocks
	 *                                if the innerBlock does not already have it's own visibility attribute.
	 * @return array[]
	 */
	private function cascade_visibility_attrs( $blocks, $visibility = null ) {

		foreach ( $blocks as &$block ) {

			// If a visibility setting has been passed from the parent and the block does not have visibility setting of it's own.
			if ( $visibility && ( empty( $block['attrs']['llms_visibility'] ) || 'off' === $block['attrs']['llms_visibility'] ) ) {
				$block['attrs']['llms_visibility'] = $visibility;
			}

			// This block has a visibility attribute and it should be applied it to all the innerBlocks.
			if ( ! empty( $block['attrs']['llms_visibility'] ) && ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->cascade_visibility_attrs( $block['innerBlocks'], $block['attrs']['llms_visibility'] );
			}
		}

		return $blocks;
	}

	/**
	 * Converts field settings formats
	 *
	 * There are small differences between the LLMS_Form_Fields settings array
	 * and the WP_Block settings array.
	 *
	 * This method accepts an associative array
	 * in one format or the other and converts it from the original format to the opposite format.
	 *
	 * @since 5.0.0
	 *
	 * @param array  $map            Associative array of settings.
	 * @param string $orignal_format The original format of the submitted `$map`. Either "field" for
	 *                               an array of LLMS_Form_Field settings or `block` for an array
	 *                               of WP_Block attributes.
	 * @return [type] [description]
	 */
	private function convert_settings_format( $map, $orignal_format ) {

		// Block attributes to LLMS_Form_Field settings.
		$keys = array(
			'field'      => 'type',
			'className'  => 'classes',
			'html_attrs' => 'attributes',
		);

		// LLMS_Form_Field settings to block attributes.
		if ( 'field' === $orignal_format ) {
			$keys = array_flip( $keys );
		}

		// Loop through the original map and rename the necessary keys.
		foreach ( $keys as $orig_key => $new_key ) {
			if ( isset( $map[ $orig_key ] ) ) {
				$map[ $new_key ] = $map[ $orig_key ];
				unset( $map[ $orig_key ] );
			}
		}

		return $map;
	}

	/**
	 * Converts an array of LLMS_Form_Field settings to a block attributes array
	 *
	 * @since 5.0.0
	 *
	 * @param array $settings An array of LLMS_Form_Field settings.
	 * @return array An array of WP_Block attributes.
	 */
	public function convert_settings_to_block_attrs( $settings ) {
		return $this->convert_settings_format( $settings, 'field' );
	}

	/**
	 * Create a form for a given location with the provided data.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location_id Location id.
	 * @param bool   $recreate    If `true` and the form already exists, will recreate the existing form using the existing form's id.
	 * @return int|false Returns the created/update form post ID on success.
	 *                   If the location doesn't exist, returns `false`.
	 *                   If the form already exists and `$recreate` is `false` will return `false`.
	 */
	public function create( $location_id, $recreate = false ) {

		if ( ! $this->is_location_valid( $location_id ) ) {
			return false;
		}

		$locs = $this->get_locations();
		$data = $locs[ $location_id ];

		$existing = $this->get_form_post( $location_id );

		// Form already exists and we haven't requested an update.
		if ( false !== $existing && ! $recreate ) {
			return false;
		}

		$args = array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_content' => LLMS_Form_Templates::get_template( $location_id ),
			'post_status'  => 'publish',
			'post_title'   => $data['title'],
			'post_type'    => $this->get_post_type(),
			'meta_input'   => $data['meta'],
			'post_author'  => $existing ? $existing->post_author : LLMS_Install::get_can_install_user_id(),
		);

		/**
		 * Filter arguments used to install a new form.
		 *
		 * @since 5.0.0
		 *
		 * @param array  $args        Array of arguments to be passed to wp_insert_post
		 * @param string $location_id Location ID/name.
		 * @param array  $data        Array of location information from LLMS_Forms::get_locations().
		 */
		$args = apply_filters( 'llms_forms_install_post_args', $args, $location_id, $data );

		return wp_insert_post( $args );
	}

	/**
	 * Retrieve the form management user capability.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public function get_capability() {
		return $this->post_type_manager->capability;
	}

	/**
	 * Pull LifterLMS Form Field blocks from an array of parsed WP Blocks.
	 *
	 * Searches innerBlocks arrays recursively.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 First check block's innerBlock attribute exists when checking for inner blocks.
	 *              Also made the access visibility public.
	 * @since 5.9.0 Pass an empty string to `strpos()` instead of `null`.
	 *
	 * @param array $blocks Array of WP Block arrays from `parse_blocks()`.
	 * @return array
	 */
	public function get_field_blocks( $blocks ) {

		$fields = array();

		foreach ( $blocks as $block ) {

			if ( ! empty( $block['innerBlocks'] ) ) {
				$fields = array_merge( $fields, $this->get_field_blocks( $block['innerBlocks'] ) );
			} elseif ( false !== strpos( $block['blockName'] ?? '', 'llms/form-field-' ) ) {
				$fields[] = $block;
			} elseif ( 'core/html' === $block['blockName'] && ! empty( $block['attrs']['type'] ) ) {
				$fields[] = $block;
			}
		}

		return $fields;
	}

	/**
	 * Returns a list of field names used by LifterLMS forms
	 *
	 * Used to validate uniqueness of custom field data.
	 *
	 * @since 5.0.0
	 *
	 * @return string[]
	 */
	public function get_field_names() {

		$names = array(
			'user_login',
			'user_login_confirm',
			'email_address',
			'email_address_confirm',
			'password',
			'password_confirm',
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

		/**
		 * Filters the list of field names used by LifterLMS forms
		 *
		 * @since 5.0.0
		 *
		 * @param string[] $names List of registered field names.
		 */
		return apply_filters( 'llms_forms_field_names', $names );
	}

	/**
	 * Retrieve an array of parsed blocks for the form at a given location.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args     Additional arguments passed to the short-circuit filter.
	 * @return array|false
	 */
	public function get_form_blocks( $location, $args = array() ) {

		$post = $this->get_form_post( $location, $args );
		if ( ! $post ) {
			return false;
		}

		$content  = $post->post_content;
		$content .= $this->get_additional_fields_html( $location, $args );

		$blocks = $this->parse_blocks( $content );

		/**
		 * Filters the parsed block list for a given LifterLMS form
		 *
		 * This hook can be used to programmatically modify, insert, or remove
		 * blocks (fields) from a form.
		 *
		 * @since 5.0.0
		 *
		 * @param array[] $blocks   Array of parsed WP_Block arrays.
		 * @param string  $location The request form location ID.
		 * @param array   $args     Additional arguments passed to the short-circuit filter.
		 */
		return apply_filters( 'llms_get_form_blocks', $blocks, $location, $args );
	}

	/**
	 * Retrieve an array of LLMS_Form_Fields settings arrays for the form at a given location.
	 *
	 * This method is used by the LLMS_Form_Handler to perform validations on user-submitted data.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args     Additional arguments passed to the short-circuit filter in `get_form_post()`.
	 * @return false|array
	 */
	public function get_form_fields( $location, $args = array() ) {

		$blocks = $this->get_form_blocks( $location, $args );

		if ( false === $blocks ) {
			return false;
		}

		$fields = $this->get_fields_settings_from_blocks( $blocks );

		/**
		 * Modify the parsed array of LifterLMS Form Fields
		 *
		 * @since 5.0.0
		 *
		 * @param array[] $fields   Array of LifterLMS Form Field settings data.
		 * @param string  $location Form location, one of: "checkout", "registration", or "account".
		 * @param array   $args     Additional arguments passed to the short-circuit filter in `get_form_post()`.
		 */
		return apply_filters( 'llms_get_form_fields', $fields, $location, $args );
	}

	/**
	 * Retrieve an array of LLMS_Form_Field settings from an array of blocks.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Pass the whole list of blocks to the `$this->block_to_field_settings()` method
	 *              to better check whether a block is visible.
	 * @since 6.2.0 Exploded hidden checkbox fields.
	 *
	 * @param array $blocks Array of WP Block arrays from `parse_blocks()`.
	 * @return array
	 */
	public function get_fields_settings_from_blocks( $blocks ) {

		$fields = array();
		$blocks = $this->get_field_blocks( $blocks );

		foreach ( $blocks as $block ) {
			$settings = $this->block_to_field_settings( $block, $blocks );

			if ( empty( $settings ) ) {
				continue;
			}
			if (
				'hidden' === ( $settings['type'] ?? null ) &&
				isset( $block['attrs']['field'] ) && 'checkbox' === $block['attrs']['field']
			) {
				// Convert hidden checkbox settings into multiple "checked" hidden fields.
				$settings['type'] = $block['attrs']['field'];
				$field            = new LLMS_Form_Field( $settings );
				$form_fields      = $field->explode_options_to_fields( true );
				foreach ( $form_fields as $form_field ) {
					$fields[] = $form_field->get_settings();
				}
			} else {
				$field    = new LLMS_Form_Field( $settings );
				$fields[] = $field->get_settings();
			}
		}

		return $fields;
	}

	/**
	 * Retrieve a field item from a list of fields by a key/value pair.
	 *
	 * @since 5.0.0
	 *
	 * @param array[] $fields List of LifterLMS Form Fields.
	 * @param string  $key    Setting key to search for.
	 * @param mixed   $val    Setting valued to search for.
	 * @param string  $return Determine the return value. Use "field" to return the field settings
	 *                        array. Use "index" to return the index of the field in the $fields array.
	 * @return array|int|false `false` when the field isn't found in $fields, otherwise returns the field settings
	 *                          as an array when `$return` is "field". Otherwise returns the field's index as an int.
	 */
	public function get_field_by( $fields, $key, $val, $return = 'field' ) {

		foreach ( $fields as $index => $field ) {
			if ( isset( $field[ $key ] ) && $val === $field[ $key ] ) {
				return 'field' === $return ? $field : $index;
			}
		}

		return false;
	}

	/**
	 * Retrieve the rendered HTML for the form at a given location.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args     Additional arguments passed to the short-circuit filter in `get_form_post()`.
	 * @return string
	 */
	public function get_form_html( $location, $args = array() ) {

		$blocks = $this->get_form_blocks( $location, $args );
		if ( ! $blocks ) {
			return '';
		}

		$disable_visibility = ( 'checkout' !== $location );

		// Force fields to display regardless of visibility settings when viewing account/registration forms.
		if ( $disable_visibility ) {
			add_filter( 'llms_blocks_visibility_should_filter_block', '__return_false', 999 );
		}

		$html = '';
		foreach ( $blocks as $block ) {
			$html .= render_block( $block );
		}

		if ( $disable_visibility ) {
			remove_filter( 'llms_blocks_visibility_should_filter_block', '__return_false', 999 );
		}

		/**
		 * Modify the parsed array of LifterLMS Form Fields.
		 *
		 * @since 5.0.0
		 *
		 * @param string $html     Form fields HTML.
		 * @param string $location Form location, one of: "checkout", "registration", or "account".
		 * @param array  $args     Additional arguments passed to the short-circuit filter in `get_form_post()`.
		 */
		return apply_filters( 'llms_get_form_html', $html, $location, $args );
	}

	/**
	 * Retrieve the WP Post for the form at a given location.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args     Additional arguments passed to the short-circuit filter.
	 * @return WP_Post|false
	 */
	public function get_form_post( $location, $args = array() ) {

		// @todo Add caching. This runs twice on some page loads.

		/**
		 * Skip core lookup of the form for the request location and return a custom form post.
		 *
		 * @since 5.0.0
		 *
		 * @param null|WP_Post $post     Return a WP_Post object to short-circuit default lookup query.
		 * @param string       $location Form location. Either "checkout", "registration", or "account".
		 * @param array        $args     Additional custom arguments.
		 */
		$post = apply_filters( 'llms_get_form_post_pre_query', null, $location, $args );
		if ( is_a( $post, 'WP_Post' ) ) {
			return $post;
		}

		$query = new WP_Query(
			array(
				'post_type'      => $this->get_post_type(),
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				// Only show published forms to end users but allow admins to "preview" drafts.
				'post_status'    => current_user_can( $this->get_capability() ) ? array( 'publish', 'draft' ) : 'publish',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => '_llms_form_location',
						'value' => $location,
					),
					array(
						'key'   => '_llms_form_is_core',
						'value' => 'yes',
					),
				),
			)
		);

		$post = $query->have_posts() ? $query->posts[0] : false;

		/**
		 * Filters the returned `llms_form` post object
		 *
		 * @since 5.0.0
		 *
		 * @param WP_Post|boolean $post     The post object of the form or `false` if no form could be located.
		 * @param string       $location Form location. Either "checkout", "registration", or "account".
		 * @param array        $args     Additional custom arguments.
		 */
		return apply_filters( 'llms_get_form_post', $post, $location, $args );
	}

	/**
	 * Check whether a given form is a core form.
	 *
	 * When there are multiple forms for a location, the core form is identified as the one with the lowest ID.
	 *
	 * @since 6.4.0
	 *
	 * @param WP_Post|int $form Form's WP_Post instance, or its ID.
	 * @return boolean
	 */
	public function is_a_core_form( $form ) {

		$form_id = $form instanceof WP_Post ? $form->ID : $form;

		if ( ! $form_id ) {
			return false;
		}

		return in_array( $form_id, $this->get_core_forms( 'ids' ), true );
	}

	/**
	 * Retrieves only core forms.
	 *
	 * When there are multiple forms for a location, the core form is identified as the one with the lowest ID.
	 *
	 * @since 6.4.0
	 *
	 * @param string $return What to return: 'posts', for an array of WP_Post; 'ids' for an array of WP_Post ids.
	 * @return WP_Post[]|int[]
	 */
	private function get_core_forms( $return = 'posts', $use_cache = true ) {

		global $wpdb;

		$forms_cache_key = 'posts' === $return ? 'llms_core_forms' : 'llms_core_form_ids';
		$forms           = $use_cache ? wp_cache_get( $forms_cache_key ) : false;

		if ( false !== $forms ) {
			return $forms;
		}

		$locations              = array_keys( $this->get_locations() );
		$locations_placeholders = implode( ',', array_fill( 0, count( $locations ), '%s' ) );
		$prepare_values         = array_merge( array( $this->get_post_type() ), $locations );

		$query = "
SELECT MIN({$wpdb->posts}.ID) AS ID
FROM $wpdb->posts
INNER JOIN {$wpdb->postmeta} AS locations ON {$wpdb->posts}.ID = locations.post_id AND locations.meta_key='_llms_form_location'
INNER JOIN {$wpdb->postmeta} AS is_cores ON {$wpdb->posts}.ID = is_cores.post_id AND is_cores.meta_key='_llms_form_is_core'
WHERE {$wpdb->posts}.post_type = %s
AND locations.meta_value IN ({$locations_placeholders})
AND is_cores.meta_value = 'yes'
GROUP BY locations.meta_value";

		$form_ids = $wpdb->get_col(
			$wpdb->prepare(
				$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- It is prepared.
				$prepare_values
			)
		);

		$form_ids = array_map( 'absint', $form_ids );
		$forms    = 'post' === $return ? array_map( 'get_post', $form_ids ) : $form_ids;

		wp_cache_set( $forms_cache_key, $forms );

		return $forms;
	}


	/**
	 * Retrieve additional fields added to the form programmatically.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args     Additional arguments passed to the short-circuit filter.
	 * @return array[]
	 */
	private function get_additional_fields( $location, $args = array() ) {

		/**
		 * Filter to add custom fields to a form programmatically.
		 *
		 * @since 3.0.0
		 * @since 5.0.0 Moved from deprecated function `LLMS_Person_Handler::get_available_fields()`.
		 *
		 * @param array[] $fields   Array of field array suitable to pass to `llms_form_field()`.
		 * @param string  $location Form location, one of: "checkout", "registration", or "account".
		 * @param array   $args     Additional arguments passed to the short-circuit filter.
		 */
		return apply_filters( 'lifterlms_get_person_fields', array(), $location, $args );
	}

	/**
	 * Retrieve HTML for the form's additional programmatically-added fields.
	 *
	 * Gets the HTML for each field from `llms_form_field()` and wraps it as a `wp/html` block.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args     Additional arguments passed to the short-circuit filter.
	 * @return string
	 */
	private function get_additional_fields_html( $location, $args = array() ) {

		$html   = '';
		$fields = $this->get_additional_fields( $location, $args );

		foreach ( $fields as $field ) {
			$html .= "\r" . $this->get_custom_field_block_markup( $field );
		}

		return $html;
	}

	/**
	 * Retrieve the HTML markup for a custom form field block
	 *
	 * Retrieves an array of `LLMS_Form_Field` settings, generates the HTML
	 * for the field, and wraps it in a `wp:html` block.
	 *
	 * @since 5.0.0
	 *
	 * @param array $settings Form field settings (passed to `llms_form_field()`).
	 * @return string
	 */
	public function get_custom_field_block_markup( $settings ) {
		return sprintf( '<!-- wp:html %1$s -->%2$s%3$s%2$s<!-- /wp:html -->', wp_json_encode( $settings ), "\r", llms_form_field( $settings, false ) );
	}

	/**
	 * Retrieve an array of form fields used for the "free enrollment" form
	 *
	 * This is the "one-click" enrollment form used when a logged-in user clicks the "checkout" button
	 * from an access plan.
	 *
	 * This function converts the checkout form to hidden fields, the result is that users with all required fields
	 * will be enrolled into the course with a single click (no need to head to the checkout page) and users
	 * who are missing required information will be directed to the checkout page.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Specifiy to pass the new 3rd param to the `llms_forms_block_to_field_settings` filter callback.
	 * @since 5.9.0 Fix php 8.1 deprecation warnings when `get_form_fields()` returns `false`.
	 * @since 7.0.0 Retrieve and use the free checkout redirect URL as not encoded.
	 *
	 * @param LLMS_Access_Plan $plan Access plan being used for enrollment.
	 * @return array[] List of LLMS_Form_Field settings arrays.
	 */
	public function get_free_enroll_form_fields( $plan ) {

		// Convert all fields to hidden fields and remove any fields hidden by LLMS block-level visibility settings.
		add_filter( 'llms_forms_block_to_field_settings', array( $this, 'prepare_field_for_free_enroll_form' ), 999, 3 );
		$fields = $this->get_form_fields( 'checkout', compact( 'plan' ) );
		remove_filter( 'llms_forms_block_to_field_settings', array( $this, 'prepare_field_for_free_enroll_form' ), 999, 3 );

		// If no fields are found, ensure we add to an array instead of casting false to an array (causing a PHP 8.1 deprecation warning).
		$fields = ! is_array( $fields ) ? array() : $fields;

		// Add additional fields required for form processing.
		$fields[] = array(
			'name'           => 'free_checkout_redirect',
			'type'           => 'hidden',
			'value'          => $plan->get_redirection_url( false ),
			'data_store_key' => false,
		);

		$fields[] = array(
			'id'             => 'llms-plan-id',
			'name'           => 'llms_plan_id',
			'type'           => 'hidden',
			'value'          => $plan->get( 'id' ),
			'data_store_key' => false,
		);

		/**
		 * Filter the list of LLMS_Form_Fields used to generate the "free enrollment" form
		 *
		 * @since 5.0.0
		 *
		 * @param array[]          $fields List of LLMS_Form_Field settings arrays.
		 * @param LLMS_Access_Plan $plan   Access plan being used for enrollment.
		 */
		return apply_filters( 'llms_forms_get_free_enroll_form_fields', $fields, $plan );
	}

	/**
	 * Retrieve the HTML of form fields used for the "free enrollment" form
	 *
	 * @since 5.0.0
	 *
	 * @see LLMS_Forms::get_free_enroll_form_fields()
	 *
	 * @param LLMS_Access_Plan $plan Access plan being used for enrollment.
	 * @return string
	 */
	public function get_free_enroll_form_html( $plan ) {

		$html = '';
		foreach ( $this->get_free_enroll_form_fields( $plan ) as $field ) {
			$html .= llms_form_field( $field, false );
		}

		return $html;
	}

	/**
	 * Retrieve information on all the available form locations.
	 *
	 * @since 5.0.0
	 *
	 * @return array[] {
	 *     An associative array. The array key is the location ID and each array is a location definition array.
	 *
	 *     @type string  $name        The human-readable location name (as displayed on the admin panel).
	 *     @type string  $description A description of the form (as displayed on the admin panel).
	 *     @type string  $title       The form's post title. This is displayed to the end user when the "Show Form Title" option is enabled.
	 *     @type array   $meta        An associative array of postmeta information for the form. The array key is the meta key and the value is the meta value.
	 *     @type string  $template    A string used to generate the post content of the form post, usually retrieve from `LLMS_Form_Templates`.
	 *     @type array   $meta        Array of meta data used when generating the form. The array key is the meta key and array value is the meta value.
	 *     @type array[] $required    Array of arrays defining required fields for each form.
	 * }
	 */
	public function get_locations() {

		$locations = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-form-locations.php';

		/**
		 * Filter the available form locations.
		 *
		 * NOTE: Removing core forms (as well as modifying the ids / keys) may cause areas of LifterLMS to stop working.
		 *
		 * @since 5.0.0
		 *
		 * @param  array[] $locations Associative array of form location information.
		 */
		return apply_filters( 'llms_forms_get_locations', $locations );
	}

	/**
	 * Retrieve the forms post type name.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type_manager->post_type;
	}

	/**
	 * Determine if a block is visible based on LifterLMS Visibility Settings.
	 *
	 * @since 5.0.0
	 * @since 7.1.4 Fixed an issue running unit tests on PHP 7.4 and WordPress 6.2
	 *              expecting `render_block()` returning a string while we were applying a filter
	 *              that returned the boolean `true`.
	 *
	 * @param array $block Parsed block array.
	 * @return bool
	 */
	private function is_block_visible( $block ) {

		// Make the block return a non empty string if it's visible, it will already automatically return an empty string if it's invisible.
		add_filter( 'render_block', array( __CLASS__, '__return_string' ), 5 );

		// Don't run this class render function on the block during this test.
		remove_filter( 'render_block', array( $this, 'render_field_block' ), 10, 2 );

		// Render the block.
		$render = render_block( $block );

		// Cleanup / reapply filters.
		add_filter( 'render_block', array( $this, 'render_field_block' ), 10, 2 );
		remove_filter( 'render_block', array( __CLASS__, '__return_string' ), 5 );

		/**
		 * Filter whether or not the block is visible.
		 *
		 * @since 5.0.0
		 *
		 * @param bool  $visible Whether or not the block is visible.
		 * @param array $block   Parsed block array.
		 */
		return apply_filters( 'llms_forms_is_block_visible', llms_parse_bool( $render ), $block );
	}

	/**
	 * Determine if a block is visible in the list it's contained based on LifterLMS Visibility Settings
	 *
	 * Fall back on `$this->is_block_visible()` if empty `$block_list` is provided.
	 *
	 * @since 5.1.0
	 *
	 * @param array   $block      Parsed block array.
	 * @param array[] $block_list The list of WP Block array `$block` comes from.
	 * @return bool Returns `true` if `$block` (and all its parents) are visible. Returns `false` when `$block`
	 *              or any of its parents are hidden or when `$block` is not found within `$block_list`.
	 */
	public function is_block_visible_in_list( $block, $block_list ) {

		if ( empty( $block_list ) ) {
			return $this->is_block_visible( $block );
		}

		$path       = $this->get_block_path( $block, $block_list );
		$is_visible = ! empty( $path ); // Assume the block is visible until proven hidden, except when path is empty.
		foreach ( $path as $block ) {
			if ( ! $this->is_block_visible( $block ) ) {
				$is_visible = false;
				break;
			}
		}

		/**
		 * Filter whether or not the block is visible in the list of blocks it's contained.
		 *
		 * @since 5.1.0
		 *
		 * @param bool    $is_visible Whether or not the block is visible.
		 * @param array   $block      Parsed block array.
		 * @param array[] $block_list The list of WP Block array `$block` comes from.
		 */
		return apply_filters( 'llms_forms_is_block_visible', $is_visible, $block, $block_list );
	}

	/**
	 * Returns a list of block parents plus the block itself in reverse order
	 *
	 * @since 5.1.0
	 *
	 * @param array   $block      Parsed block array.
	 * @param array[] $block_list The list of WP Block array `$block` comes from.
	 * @param int     $iterations Stores the number of iterations.
	 * @return array[] List of WP_Block arrays or an empty array if `$block` cannot be found within `$block_list`.
	 */
	private function get_block_path( $block, $block_list, $iterations = 0 ) {

		foreach ( $block_list as $_block ) {

			// Found the block.
			if ( $block === $_block ) {
				return array( $block );
			}

			// No innerblocks, proceed to the next block.
			if ( empty( $_block['innerBlocks'] ) ) {
				continue;
			}

			// Look in innerblocks for the block.
			foreach ( $_block['innerBlocks'] as $inner_block ) {

				// The inner block needs to be merged to the path.
				$to_merge = array( $inner_block );

				if ( $block === $inner_block ) { // Inner block is the one we're looking for.
					$path     = array( $block );
					$to_merge = array(); // Inner block equals the path, no need to merge it.
				} else {
					$path = $this->get_block_path( $block, array( $inner_block ), $iterations + 1 );
				}

				if ( $path ) {

					// First iteration, append first block too.
					if ( ! $iterations ) {
						$to_merge[] = $_block;
					}

					// Merge.
					return array_merge( $path, $to_merge );

				}
			}
		}

		// Block not found in the list.
		return array();
	}

	/**
	 * Returns a filtered version of `$block_list` containing only the passed `$block` and its parents.
	 *
	 * @since 5.1.0
	 *
	 * @param array   $block      Parsed block array.
	 * @param array[] $block_list The list of WP Block array `$block` comes from.
	 * @return array[] Filtered version of `$block_list` containing only the passed `$block` and its parents.
	 *                 Or an empty array if `$block` cannot be found within `$block_list`.
	 */
	private function get_block_tree( $block, $block_list ) {

		foreach ( $block_list as &$_block ) {

			// Found the block.
			if ( $block === $_block ) {
				return array( $block );
			}

			if ( ! empty( $_block['innerBlocks'] ) ) {
				$tree = $this->get_block_tree( $block, $_block['innerBlocks'] );
			}

			if ( ! empty( $tree ) ) { // Break as soon as the desired block is removed from one of the innerBlocks.
				if ( $_block['innerBlocks'] !== $tree ) { // Update innerBlocks/innerContent structure if needed.
					$_block['innerBlocks'] = $tree;
					// Update innerContent to reflect the innerBlocks changes = only 1 innerBlock.
					$inner_block_in_content_index = 0;
					foreach ( $_block['innerContent'] as $index => $chunk ) {
						if ( ! is_string( $chunk ) && $inner_block_in_content_index++ ) {
							unset( $_block['innerContent'][ $index ] );
						}
					}
					// Re-index.
					$_block['innerContent'] = array_values( $_block['innerContent'] );
				}

				return array( $_block );
			}
		}

		return array();
	}

	/**
	 * Installation function to install core forms.
	 *
	 * @since 5.0.0
	 *
	 * @param bool $recreate Whether or not to recreate an existing form. This is passed to `LLMS_Forms::create()`.
	 * @return WP_Post[] Array of created posts. Array key is the location id and array value is the WP_Post object.
	 */
	public function install( $recreate = false ) {

		$installed = array();

		foreach ( array_keys( $this->get_locations() ) as $location ) {
			$installed[ $location ] = $this->create( $location, $recreate );
		}

		return $installed;
	}

	/**
	 * Determines if a location is a valid & registered form location
	 *
	 * @since 5.0.0
	 *
	 * @param string $location The location id.
	 * @return boolean
	 */
	public function is_location_valid( $location ) {
		return in_array( $location, array_keys( $this->get_locations() ), true );
	}

	/**
	 * Loads reusable blocks into a block list.
	 *
	 * A reusable block contains a reference to the block post, e.g. `<!-- wp:block {"ref":2198} /-->`,
	 * which will be loaded during rendering.
	 *
	 * Dereferencing the reusable blocks allows the entire block list to be reviewed and to validate all form fields.
	 * This function will replace each reusable block with the parsed blocks from its reference post.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Access turned to public.
	 *
	 * @param array[] $blocks An array of blocks from `parse_blocks()`,
	 *                        where each block is usually an array cast from `WP_Block_Parser_Block`.
	 *
	 * @return array[]
	 */
	public function load_reusable_blocks( $blocks ) {

		$loaded = array();

		foreach ( $blocks as $block ) {

			// Skip blocks that are not reusable blocks.
			if ( 'core/block' === $block['blockName'] ) {

				// Skip reusable blocks that do not exist or are not published.
				$post = get_post( $block['attrs']['ref'] );
				if ( ! $post || 'publish' !== get_post_status( $post ) ) {
					continue;
				}

				$loaded = array_merge( $loaded, $this->parse_blocks( $post->post_content ) );
				continue;
			}

			// Does this block's inner blocks have references to reusable blocks?
			if ( $block['innerBlocks'] ) {
				$block['innerBlocks'] = $this->load_reusable_blocks( $block['innerBlocks'] );
			}

			$loaded[] = $block;
		}

		return $loaded;
	}

	/**
	 * Load form autosaves when previewing a form
	 *
	 * @since 5.0.0
	 *
	 * @param WP_Post|boolean $post WP_Post object for the llms_form post or `false` if no form found.
	 * @return WP_Post|boolean
	 */
	public function maybe_load_preview( $post ) {

		// No form post found.
		if ( ! is_object( $post ) ) {
			return $post;
		}

		// The `_set_preview()` method is marked as private but has existed since 2.7 and my guess is that we can use this safely.
		if ( ! function_exists( '_set_preview' ) ) {
			return $post;
		}

		$is_preview = ( is_preview() && current_user_can( $this->get_capability(), $post->ID ) );

		return $is_preview ? _set_preview( $post ) : $post;
	}

	/**
	 * Parse the post_content of a form into a list of WP_Block arrays.
	 *
	 * This method parses the blocks, loads block data from any reusable blocks,
	 * and cascades visibility attributes onto a block's innerBlocks.
	 *
	 * @since 5.0.0
	 *
	 * @param string $content Post content HTML.
	 * @return array[] Array of parsed block arrays.
	 */
	public function parse_blocks( $content ) {

		$blocks = parse_blocks( $content );

		$blocks = $this->load_reusable_blocks( $blocks );

		$blocks = $this->cascade_visibility_attrs( $blocks );

		return $blocks;
	}

	/**
	 * Modifies a field for usage in the "free enrollment" checkout form
	 *
	 * If the block is not visible (according to LLMS block-level visibility settings)
	 * it will return an empty array (signaling the field to be removed).
	 *
	 * Otherwise the block will be converted to a hidden field.
	 *
	 * This method is a filter callback and is intended for internal use only.
	 *
	 * Backwards incompatible changes and/or method removal may occur without notice.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Added `$block_list` param.
	 * @access private
	 *
	 * @param array   $attrs      LLMS_Form_Field settings array for the field.
	 * @param array   $block      WP_Block settings array.
	 * @param array[] $block_list The list of WP Block array `$block` comes from.
	 * @return array
	 */
	public function prepare_field_for_free_enroll_form( $attrs, $block, $block_list ) {

		if ( ! $this->is_block_visible_in_list( $block, $block_list ) ) {
			return array();
		}

		$attrs['type'] = 'hidden';
		return $attrs;
	}

	/**
	 * Render form field blocks.
	 *
	 * @since 5.0.0
	 * @since 5.9.0 Pass an empty string to `strpos()` instead of `null`.
	 *
	 * @param string $html  Block HTML.
	 * @param array  $block Array of block information.
	 * @return string
	 */
	public function render_field_block( $html, $block ) {

		// Return HTML for any non llms/form-field blocks.
		if ( false === strpos( $block['blockName'] ?? '', 'llms/form-field-' ) ) {
			return $html;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {

			$inner_blocks = array_map( 'render_block', $block['innerBlocks'] );
			return implode( "\n", $inner_blocks );

		}

		$attrs = $this->block_to_field_settings( $block );

		return llms_form_field( $attrs, false );
	}

	/**
	 * Returns a non-empty string.
	 *
	 * Useful for returning a non empty string to filters easily.
	 *
	 * @since 7.1.4
	 *
	 * @access private
	 *
	 * @return string
	 */
	public static function __return_string(): string {// phpcs:ignore -- PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.MethodDoubleUnderscore.
		return '1';
	}
}

return LLMS_Forms::instance();
