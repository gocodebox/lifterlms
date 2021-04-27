<?php
/**
 * LLMS_Form_Post_Type class
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Forms Post Type
 *
 * Handle post type registration and interactions
 *
 * @since [version]
 */
class LLMS_Form_Post_Type {

	private $forms = null;

	/**
	 * User Capability required to manage forms
	 *
	 * @var string
	 */
	public $capability = 'manage_lifterlms';

	/**
	 * Forms post type name.
	 *
	 * @var string
	 */
	public $post_type = 'llms_form';

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * return void
	 */
	public function __construct( $forms ) {

		$this->forms = $forms;

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );

		// Modify permalink.
		add_filter( 'post_type_link', array( $this, 'modify_permalink' ), 10, 2 );

		// Prevent deletion of core forms.
		add_filter( 'pre_delete_post', array( $this, 'maybe_prevent_deletion' ), 20, 2 );
		add_filter( 'pre_trash_post', array( $this, 'maybe_prevent_deletion' ), 20, 2 );

		/**
		 * Filters the capability required to manage LifterLMS Forms
		 *
		 * @since [version]
		 *
		 * @param string $capability The user capability. Default: "manage_lifterlms".
		 */
		$this->capability = apply_filters( 'llms_forms_managment_capability', $this->capability );

	}


	/**
	 * Retrieve a permalink for a given form post.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $post Form post object.
	 * @return string|false Permalink to the form or `false` if no permalink exists for the given location.
	 */
	private function get_permalink( $post ) {

		$url      = false;
		$location = get_post_meta( $post->ID, '_llms_form_location', true );

		$method = "get_permalink_for_{$location}";
		if ( $this->forms->is_location_valid( $location ) && method_exists( $this, $method ) ) {
			$url = $this->$method();
		}

		/**
		 * Filters the permalink for a LifterLMS form
		 *
		 * @since [version]
		 *
		 * @param string|false $url      The form's URL.
		 * @param string       $location The location ID for the form.
		 * @param WP_Post      $post     The form post object.
		 */
		return apply_filters( 'llms_form_permalink', $url, $location, $post );

	}

	/**
	 * Retrieve permalink for the account edit form
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_permalink_for_account() {
		return llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) );
	}

	/**
	 * Retrieve permalink for the checkout form
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_permalink_for_checkout() {

		$url  = llms_get_page_url( 'checkout' );
		$args = array();

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
			$args = array(
				'plan' => $plans->posts[0]->ID,
			);
		}

		return LLMS_View_Manager::get_url( 'visitor', $url, $args );

	}

	/**
	 * Retrieve permalink for the registration form
	 *
	 * @since [version]
	 *
	 * @return string|false Permalink or `false` when open registration is disabled.
	 */
	private function get_permalink_for_registration() {

		if ( llms_parse_bool( llms_get_open_registration_status() ) ) {
			return LLMS_View_Manager::get_url( 'visitor', llms_get_page_url( 'myaccount' ) );
		}

		return false;

	}

	/**
	 * Maybe prevent a post from being deleted/trashed
	 *
	 * We do not allow the "core" forms to be deleted. This action prevents both
	 * deletion and trash actions when run against one of the core form.
	 *
	 * @since [version]
	 *
	 * @param null|bool $prevent Whether or not the action has been prevented.
	 * @param WP_Post   $post    The form post object.
	 * @return null|false Returns `null` when we don't prevent the action and `false` if we should.
	 */
	public function maybe_prevent_deletion( $prevent, $post ) {

		if ( $post->post_type === $this->post_type && llms_parse_bool( get_post_meta( $post->ID, '_llms_form_is_core', 'yes' ) ) ) {
			$prevent = false;
		}

		return $prevent;
	}

	/**
	 * Modify the permalink of a given form.
	 *
	 * @since [version]
	 *
	 * @param string  $permalink Default permalink.
	 * @param WP_Post $post      Post object.
	 * @return string|false
	 */
	public function modify_permalink( $permalink, $post ) {

		if ( $this->post_type !== $post->post_type ) {
			return $permalink;
		}

		return $this->get_permalink( $post );

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
				'delete_post'            => $this->capability,
				'edit_posts'             => $this->capability,
				'edit_others_posts'      => $this->capability,
				'publish_posts'          => $this->capability,
				'read_private_posts'     => $this->capability,
				'read'                   => 'read',
				'delete_posts'           => $this->capability,
				'delete_private_posts'   => $this->capability,
				'delete_published_posts' => $this->capability,
				'delete_others_posts'    => $this->capability,
				'edit_private_posts'     => $this->capability,
				'edit_published_posts'   => $this->capability,
				'create_posts'           => false,
			),
		);

		LLMS_Post_Types::register_post_type( $this->post_type, $args );

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
	 * Meta field update authorization callback.
	 *
	 * @since [version]
	 *
	 * @param bool   $allowed   Is the update allowed.
	 * @param string $meta_key  Meta keyname.
	 * @param int    $object_id WP Object ID (post,comment,etc)...
	 * @param int    $user_id   WP User ID.
	 * @param string $cap       Requested capability.
	 * @param array  $caps      User capabilities.
	 * @return bool
	 */
	public function meta_auth_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		return user_can( $user_id, $this->capability, $object_id );
	}

}
