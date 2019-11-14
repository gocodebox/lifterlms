<?php
/**
 * Register and manage LifterLMS user forms.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Forms class..
 *
 * @since [version]
 */
class LLMS_Forms {

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $instance = null;

	/**
	 * User Capability required to manage forms
	 *
	 * @var string
	 */
	protected $capability = 'manage_lifterlms';

	/**
	 * Forms post type name.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_form';

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Forms
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );

		add_filter( 'post_type_link', array( $this, 'modify_permalink' ), 10, 2 );
		add_filter( 'render_block', array( $this, 'render_field_block' ), 10, 2 );

		/**
		 * Filter the capability required to manage LifterLMS Forms
		 *
		 * @since [version]
		 *
		 * @param string $capability The user capability.
		 */
		$this->capability = apply_filters( 'llms_forms_managment_capability', $this->capability );

	}

	/**
	 * Converts stored block attribute to settings understandable by `llms_form_field()`
	 *
	 * @since [version]
	 *
	 * @param array $attrs Block Attributes.
	 * @return array
	 */
	protected function block_atts_to_field_settings( $attrs ) {

		if ( isset( $attrs['field'] ) ) {
			// Rename "field" to "type".
			$attrs['type'] = $attrs['field'];
			unset( $attrs['field'] );
		}

		// Rename "className" to "classes"
		if ( isset( $attrs['className'] ) ) {
			$attrs['classes'] = $attrs['className'];
			unset( $attrs['className'] );
		}

		return $attrs;

	}

	/**
	 * Create a form for a given location with the provided data.
	 *
	 * @since [version]
	 *
	 * @param string $location_id Location id.
	 * @param bool   $update If `true` and the form already exists, will update the existing form.
	 * @return int|false Returns the created/update form post ID on success.
	 *                   If the location doesn't exist, returns `false`.
	 *                   If the form already exists and `$update` is `false` will return `false`.
	 */
	public function create( $location_id, $update = false ) {

		$locs = $this->get_locations();
		$data = isset( $locs[ $location_id ] ) ? $locs[ $location_id ] : false;

		if ( ! $data ) {
			return false;
		}

		$existing = $this->get_form_post( $location_id );

		// Only create a form for a location if the form doesn't already exist.
		if ( false !== $existing && ! $update ) {
			return false;
		}

		$meta = array(
			'_llms_form_location' => $location_id,
		);
		if ( isset( $data['meta'] ) ) {
			$meta = array_merge( $meta, $data['meta'] );
		}

		$templates = LLMS_Form_Templates::instance();
		$args      = array(
			'post_content' => $templates->get_template( $location_id ),
			'post_status'  => 'publish',
			'post_title'   => $data['title'],
			'post_type'    => $this->post_type,
			'meta_input'   => $meta,
		);

		if ( $existing ) {
			$args['ID'] = $existing->ID;
		}

		/**
		 * Filter arguments used to install a new form.
		 *
		 * @since [version]
		 *
		 * @param array $args Array of arguments to be passed to wp_insert_post
		 * @param $string $location_id Location ID/name.
		 * @param array $data Array of location information from LLMS_Forms::get_locations().
		 */
		$args = apply_filters( 'llms_forms_install_post_args', $args, $location_id, $data );

		return wp_insert_post( $args );

	}

	/**
	 * Retrieve the form management user capability.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_capability() {
		return $this->capability;
	}

	/**
	 * Pull LifterLMS Form Field blocks from an array of parsed WP Blocks.
	 *
	 * Searches innerBlocks arrays recursively.
	 *
	 * @since [version]
	 *
	 * @param  array $blocks Array of WP Block arrays from `parse_blocks()`.
	 * @return array
	 */
	protected function get_field_blocks( $blocks ) {

		$fields = array();

		foreach ( $blocks as $block ) {

			if ( false !== strpos( $block['blockName'], 'llms/form-field-' ) ) {
				$fields[] = $block;
			} elseif ( $block['innerBlocks'] ) {
				$fields = array_merge( $fields, $this->get_field_blocks( $block['innerBlocks'] ) );
			}
		}

		return $fields;

	}

	/**
	 * Retrieve an array of parsed blocks for the form at a given location.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args Additioal arguments passed to the short-circuit filter in `get_form_post()`.
	 * @return array|false
	 */
	public function get_form_blocks( $location, $args = array() ) {

		$post = $this->get_form_post( $location, $args );

		if ( ! $post ) {
			return false;
		}

		$content  = $post->post_content;
		$content .= $this->get_additional_fields_html( $location, $args );

		return parse_blocks( $content );

	}

	/**
	 * Retrieve an array of LLMS_Form_Fields settings arrays for the form at a given location.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args Additioal arguments passed to the short-circuit filter in `get_form_post()`.
	 * @return false|array
	 */
	public function get_form_fields( $location, $args = array() ) {

		$blocks = $this->get_form_blocks( $location, $args );
		if ( false === $blocks ) {
			return false;
		}

		$fields = array();
		foreach ( $this->get_field_blocks( $blocks ) as $block ) {
			$settings = $this->block_atts_to_field_settings( $block['attrs'] );
			$field    = new LLMS_Form_Field( $settings );
			$fields[] = $field->get_settings();
		}

		$fields = array_merge( $fields, $this->get_additional_fields( $location, $args ) );

		/**
		 * Modify the parsed array of LifterLMS Form Fields.
		 *
		 * @since [version]
		 *
		 * @param array[] $fields Array of LifterLMS Form Field settings data.
		 * @param string $location Form location, one of: "checkout", "registration", or "account".
		 * @param array $args Additioal arguments passed to the short-circuit filter in `get_form_post()`.
		 */
		return apply_filters( 'llms_get_form_fields', $fields, $location, $args );

	}

	/**
	 * Retrieve a field item from a list of fields by a key/value pair.
	 *
	 * @since [version]
	 *
	 * @param array[] $fields List of LifterLMS Form Fields.
	 * @param string  $key Setting key to search for.
	 * @param mixed   $val Setting valued to search for.
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
	 * @since [version]
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args Additioal arguments passed to the short-circuit filter in `get_form_post()`.
	 * @return string
	 */
	public function get_form_html( $location, $args = array() ) {

		$blocks = $this->get_form_blocks( $location, $args );
		if ( ! $blocks ) {
			return '';
		}

		$html = '';
		foreach ( $blocks as $block ) {
			$html .= render_block( $block );
		}

		/**
		 * Modify the parsed array of LifterLMS Form Fields.
		 *
		 * @since [version]
		 *
		 * @param string $html Form fields HTML.
		 * @param string $location Form location, one of: "checkout", "registration", or "account".
		 * @param array $args Additioal arguments passed to the short-circuit filter in `get_form_post()`.
		 */
		return apply_filters( 'llms_get_form_html', $html, $location, $args );

	}

	/**
	 * Retrieve the WP Post for the form at a given location.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args Additioal arguments passed to the short-circuit filter.
	 * @return WP_Post|false
	 */
	public function get_form_post( $location, $args = array() ) {

		/**
		 * Skip core lookup of the form for the request location and return a custom form post.
		 *
		 * @since [version]
		 *
		 * @param null|WP_Post $post Return a WP_Post object to short-circuit default lookup query.
		 * @param string $location Form location. Either "checkout", "registration", or "account".
		 * @param array $args Additional custom arguments.
		 */
		$post = apply_filters( 'llms_get_form_post_pre_query', null, $location, $args );
		if ( is_a( $post, 'WP_Post' ) ) {
			return $post;
		}

		$query = new WP_Query(
			array(
				'post_type'      => $this->post_type,
				'posts_per_page' => 1,
				// Only show published forms to end users but allow admins to "preview" drafts.
				'post_status'    => current_user_can( $this->capability ) ? array( 'publish', 'draft' ) : 'publish',
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

		if ( $query->have_posts() ) {
			return $query->posts[0];
		}

		return false;

	}

	/**
	 * Retrieve additional fields added to the form programmatically.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args Additioal arguments passed to the short-circuit filter.
	 * @return array[]
	 */
	protected function get_additional_fields( $location, $args = array() ) {

		/**
		 * Filter to add custom fields to a form programmatically.
		 *
		 * @since 3.0.0
		 * @since [version] Moved from deprecated function `LLMS_Person_Handler::get_available_fields()`.
		 *
		 * @param array[] $fields Array of field array suitable to pass to `llms_form_field()`.
		 * @param string $location Form location, one of: "checkout", "registration", or "account".
		 * @param array $args Additioal arguments passed to the short-circuit filter.
		 */
		return apply_filters( 'lifterlms_get_person_fields', array(), $location, $args );

	}

	/**
	 * Retrieve HTML for the form's additional programmatically-added fields.
	 *
	 * Gets the HTML for each field from `llms_form_field()` and wraps it as a `wp/html` block.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location, one of: "checkout", "registration", or "account".
	 * @param array  $args Additioal arguments passed to the short-circuit filter.
	 * @return string
	 */
	protected function get_additional_fields_html( $location, $args = array() ) {

		$html   = '';
		$fields = $this->get_additional_fields( $location, $args );

		foreach ( $fields as $field ) {
			$html .= sprintf( "\r<!-- wp:html -->\r%s\r<!-- /wp:html -->", llms_form_field( $field, false ) );
		}

		return $html;

	}

	/**
	 * Retrieve information on all the available form locations.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_locations() {

		/**
		 * Filter the available form locations.
		 *
		 * NOTE: Removing core forms (as well as modifying the ids / keys) may cause areas of LifterLMS to stop working.
		 *
		 * @since [version]
		 *
		 * @param  array $locations Associative array of form location information.
		 */
		return apply_filters(
			'llms_forms_get_locations',
			array(
				'checkout'     => array(
					'name'        => __( 'Checkout', 'lifterlms' ),
					'description' => __( 'Handles new user registration and existing user information updates during checkout and enrollment.', 'lifterlms' ),
					'title'       => __( 'Billing Information', 'lifterlms' ),
					'meta'        => array(
						'_llms_form_show_title' => 'yes',
						'_llms_form_is_core'    => 'yes',
					),
				),
				'registration' => array(
					'name'        => __( 'Registration', 'lifterlms' ),
					'description' => __( 'Handles new user registration and existing user information updates for open registration on the student dashboard and wherever the [lifterlms_registration] shortcode is used.', 'lifterlms' ),
					'title'       => __( 'Register', 'lifterlms' ),
					'meta'        => array(
						'_llms_form_show_title' => 'yes',
						'_llms_form_is_core'    => 'yes',
					),
				),
				'account'      => array(
					'name'        => __( 'Account', 'lifterlms' ),
					'description' => __( 'Handles user account information updates on the edit account area of the student dashboard.', 'lifterlms' ),
					'title'       => __( 'Edit Account Information', 'lifterlms' ),
					'meta'        => array(
						'_llms_form_show_title' => 'no',
						'_llms_form_is_core'    => 'yes',
					),
				),
			)
		);

	}

	/**
	 * Retrieve the forms post type name.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Retrieve a permalink for a given form post.
	 *
	 * This is primarily used by the Block Editor to allow quick links to the form from within the editor.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $post Form post object.
	 * @return string|false
	 */
	protected function get_permalink( $post ) {

		$url      = false;
		$location = get_post_meta( $post->ID, '_llms_form_location', true );

		switch ( $location ) {

			case 'account':
				$url = llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) );
				break;

			case 'checkout':
				$url = llms_get_page_url( 'checkout' );

				// Add an access plan to the URL.
				$plans = new WP_Query(
					array(
						'post_type'      => 'llms_access_plan',
						'posts_per_page' => 1,
						'orderby'        => 'ID',
						'order'          => 'ASC',
					)
				);
				if ( $plans->have_posts() ) {
					$url = add_query_arg( 'plan', $plans->posts[0]->ID, $url );
				}
				break;

			case 'registration':
				if ( llms_parse_bool( get_option( 'lifterlms_enable_myaccount_registration', 'no' ) ) ) {
					$url = llms_get_page_url( 'myaccount' );
				}

				break;

		}

		return apply_filters( 'llms_form_get_permalink', $url, $location, $post );

	}

	/**
	 * Installation function to install core forms.
	 *
	 * @since [version]
	 *
	 * @return WP_Post[] Array of created posts. Array key is the location id and array value is the WP_Post object.
	 */
	public function install() {

		$installed = array();

		foreach ( array_keys( $this->get_locations() ) as $location ) {
			$installed[ $location ] = $this->create( $location );
		}

		return $installed;

	}

	/**
	 * Meta field update authorization callback.
	 *
	 * @since [version]
	 *
	 * @param bool   $allowed Is the update allowed.
	 * @param string $meta_key Meta keyname.
	 * @param int    $object_id WP Object ID (post,comment,etc)...
	 * @param int    $user_id WP User ID.
	 * @param string $cap Requested capability.
	 * @param array  $caps User capabilities.
	 * @return bool
	 */
	public function meta_auth_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		return user_can( $user_id, $this->capability, $object_id );
	}

	/**
	 * Modify the permalink of a given form.
	 *
	 * @since [version]
	 *
	 * @param string  $permalink Default permalink.
	 * @param WP_Post $post Post object.
	 * @return string|false
	 */
	public function modify_permalink( $permalink, $post ) {

		if ( $this->post_type !== $post->post_type ) {
			return $permalink;
		}

		return $this->get_permalink( $post );

	}

	/**
	 * Register custom postmeta properties for the forms post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_meta() {

		$props = array(
			'_llms_form_location'   => array(
				'description' => __( 'Determines the front-end location where the form is displayed.', 'lifterlms' ),
			),
			'_llms_form_show_title' => array(
				'description' => __( 'Determines whether or not to display the form\'s title on the front-end.', 'lifterlms' ),
			),
			'_llms_form_is_core'    => array(
				'description' => __( 'Determines if the form is a core form required for basic site functionality.', 'lifterlms' ),
			),
		);

		foreach ( $props as $prop => $settings ) {

			register_meta(
				'post',
				$prop,
				wp_parse_args(
					$settings,
					array(
						'object_subtype'    => $this->post_type,
						'sanitize_callback' => 'sanitize_text_field',
						'auth_callback'     => array( $this, 'meta_auth_callback' ),
						'type'              => 'string',
						'single'            => true,
						'show_in_rest'      => true,
					)
				)
			);

		}

	}

	/**
	 * Register the forms post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_post_type() {

		$args = array(
			'label'               => __( 'LifterLMS Forms', 'lifterlms' ),
			'labels'              => array(
				'name'          => __( 'LifterLMS Forms', 'lifterlms' ),
				'singular_name' => __( 'LifterLMS Form', 'lifterlms' ),
				'search_items'  => __( 'Search Forms', 'lifterlms' ),
				'menu_name'     => __( 'Forms', 'lifterlms' ),
			),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => 'lifterlms',
			'show_in_admin_bar'   => false,
			'supports'            => array( 'title', 'editor', 'custom-fields' ),
			'show_in_rest'        => true,
			'rewrite'             => false,
			'capabilities'        => array(
				'edit_post'              => $this->capability,
				'read_post'              => $this->capability,
				'delete_post'            => false,
				'edit_posts'             => $this->capability,
				'edit_others_posts'      => $this->capability,
				'publish_posts'          => $this->capability,
				'read_private_posts'     => $this->capability,
				'read'                   => 'read',
				'delete_posts'           => false,
				'delete_private_posts'   => false,
				'delete_published_posts' => false,
				'delete_others_posts'    => false,
				'edit_private_posts'     => $this->capability,
				'edit_published_posts'   => $this->capability,
				'create_posts'           => false,
			),
		);

		/**
		 * Filter post type registration arguments for the llms_form post type.
		 *
		 * @since [version]
		 *
		 * @param array $args Post type registration arguments.
		 */
		$args = apply_filters( 'llms_forms_register_post_type', $args );

		register_post_type( $this->post_type, $args );

	}

	/**
	 * Render form field blocks.
	 *
	 * @since [version]
	 *
	 * @param string $html Block HTML.
	 * @param array  $block Array of block information.
	 * @return string
	 */
	public function render_field_block( $html, $block ) {

		// Return HTML for any non llms/form-field blocks.
		if ( false === strpos( $block['blockName'], 'llms/form-field-' ) ) {
			return $html;
		}

		$attrs = $this->block_atts_to_field_settings( $block['attrs'] );

		return llms_form_field( $attrs, false );

	}

}

return LLMS_Forms::instance();
