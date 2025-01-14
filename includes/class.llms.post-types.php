<?php
/**
 * Register Post Types, Taxonomies, Statuses.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Types class
 *
 * @since 1.0.0
 * @since 3.30.3 Removed duplicate array keys when registering course_tag taxonomy.
 * @since 3.33.0 `llms_question` post type is not publicly queryable anymore.
 * @since 3.34.1 Add the custom property `show_in_llms_rest` set to true by default, to those taxonomies we want to be shown in LLMS REST api.
 * @since 3.37.12 Added 'revisions' support to course, lesson, and llms_mebership post types.
 */
class LLMS_Post_Types {

	/**
	 * Reference to the block templates list.
	 *
	 * @var array
	 */
	private static $templates = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 3.0.4 Unknown.
	 * @since 4.3.2 Add filter to deregister protected post types.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'add_membership_restriction_support' ) );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_statuses' ), 9 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );

		add_action( 'admin_bar_menu', array( __CLASS__, 'add_launch_course_builder_to_course_post_type_admin_bar' ), 100 );

		add_filter( 'wp_sitemaps_post_types', array( __CLASS__, 'deregister_sitemap_post_types' ) );

		add_action( 'after_setup_theme', array( __CLASS__, 'add_thumbnail_support' ), 777 );
	}

	/**
	 * Add Launch Course Builder top admin bar button for Course posts.
	 *
	 * @since 7.8.0
	 *
	 * @return void
	 */
	public static function add_launch_course_builder_to_course_post_type_admin_bar( $wp_admin_bar ) {
		if ( is_admin() || ! is_singular( 'course' ) ) {
			return;
		}

		$url = add_query_arg(
			array(
				'page'      => 'llms-course-builder',
				'course_id' => get_the_ID(),
			),
			admin_url( 'admin.php' )
		);

		$icon_url = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( LLMS_PLUGIN_DIR . 'assets/images/lifterlms-icon-grey.svg' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		$wp_admin_bar->add_node(
			array(
				'id'    => 'course_launch_course_builder',
				'title' => '<div style="float:left;width:32px;height:32px;background-repeat:no-repeat;background-position:center;background-size:20px auto;background-image:url(\'' . $icon_url . '\')" aria-hidden="true"></div> ' . __( 'Launch Course Builder', 'lifterlms' ),
				'href'  => $url,
				'meta'  => array(
					'class' => 'llms-admin-bar-launch-course-builder',
					'title' => __( 'Launch Course Builder', 'lifterlms' ),
				),
			)
		);
	}

	/**
	 * Add post type support for membership restrictions
	 *
	 * This enables the "Membership Access" metabox to display
	 *
	 * @since 3.0.0
	 * @since 3.0.4 Unknown.
	 *
	 * @return void
	 */
	public static function add_membership_restriction_support() {

		/**
		 * Add llms-membership-restrictions support for the following post types.
		 *
		 * Adding support for a post type enables the display of the Membership Restriction metabox
		 * for each specified post type.
		 *
		 * These post types can then be "restricted" to enrollment in the selected memberships.
		 *
		 * @since Unknown
		 *
		 * @param string[] $post_types Array of post type names.
		 */
		$post_types = apply_filters( 'llms_membership_restricted_post_types', array( 'post', 'page' ) );
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, 'llms-membership-restrictions' );
		}
	}

	/**
	 * Ensure LifterLMS Post Types have thumbnail support
	 *
	 * @since 2.4.1
	 * @since 3.8.0 Unknown.
	 *
	 * @return void
	 */
	public static function add_thumbnail_support() {

		// Ensure theme support exists for LifterLMS post types.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
		}

		$thumbnail_post_types = array(
			'course',
			'lesson',
			'llms_membership',
		);

		foreach ( $thumbnail_post_types as $p ) {

			add_post_type_support( $p, 'thumbnail' );

		}

		add_image_size( 'llms_notification_icon', 64, 64, true );
	}

	/**
	 * De-register protected post types from wp-sitemap.xml
	 *
	 * @since 4.3.2
	 *
	 * @param WP_Post_Type[] $post_types Array of post types.
	 * @return WP_Post_Type[]
	 */
	public static function deregister_sitemap_post_types( $post_types ) {

		unset(
			$post_types['lesson'],
			$post_types['llms_quiz'],
			$post_types['llms_certificate'],
			$post_types['llms_my_certificate']
		);

		return $post_types;
	}

	/**
	 * Retrieve all registered order statuses
	 *
	 * @since 3.19.0
	 *
	 * @return array
	 */
	public static function get_order_statuses() {

		$statuses = array(

			// Single payment only.
			'llms-completed'      => array(
				'label'       => _x( 'Completed', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'lifterlms' ),
			),

			// Recurring only.
			'llms-active'         => array(
				'label'       => _x( 'Active', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'lifterlms' ),
			),
			'llms-expired'        => array(
				'label'       => _x( 'Expired', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'lifterlms' ),
			),
			'llms-on-hold'        => array(
				'label'       => _x( 'On Hold', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'lifterlms' ),
			),
			'llms-pending-cancel' => array(
				'label'       => _x( 'Pending Cancellation', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Pending Cancellation <span class="count">(%s)</span>', 'Pending Cancellation <span class="count">(%s)</span>', 'lifterlms' ),
			),

			// Shared.
			'llms-pending'        => array(
				'label'       => _x( 'Pending Payment', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'lifterlms' ),
			),
			'llms-cancelled'      => array(
				'label'       => _x( 'Cancelled', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'lifterlms' ),
			),
			'llms-refunded'       => array(
				'label'       => _x( 'Refunded', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'lifterlms' ),
			),
			'llms-failed'         => array(
				'label'       => _x( 'Failed', 'Order status', 'lifterlms' ),
				'label_count' => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'lifterlms' ),
			),

		);

		$defaults = array(
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		);

		foreach ( $statuses as &$status ) {
			$status = array_merge( $status, $defaults );
		}

		/**
		 * Filter the list of order statuses that will be registered with WordPress.
		 *
		 * @since 3.19.0
		 *
		 * @param array[] $statuses Array of post status arrays.
		 */
		return apply_filters( 'lifterlms_register_order_post_statuses', $statuses );
	}

	/**
	 * Get an array of capabilities for a custom post type
	 *
	 * Due to core bug does not allow us to use capability_type in post type registration.
	 * See https://core.trac.wordpress.org/ticket/30991.
	 *
	 * @since 3.13.0
	 * @since 6.0.0 Add specific case for `llms_my_achievement`, `llms_my_certificate` post types.
	 *
	 * @param string $post_type Post type name.
	 * @return array
	 */
	public static function get_post_type_caps( $post_type ) {

		if ( ! is_array( $post_type ) ) {
			$singular = $post_type;
			$plural   = $post_type . 's';
		} else {
			$singular = $post_type[0];
			$plural   = $post_type[1];
		}

		if ( in_array( $singular, array( 'my_achievement', 'my_certificate' ), true ) ) {
			$caps = self::get_earned_engagements_post_type_caps();
		} else {
			$caps = array(

				'read_post'              => sprintf( 'read_%s', $singular ),
				'read_private_posts'     => sprintf( 'read_private_%s', $plural ),

				'edit_post'              => sprintf( 'edit_%s', $singular ),
				'edit_posts'             => sprintf( 'edit_%s', $plural ),
				'edit_others_posts'      => sprintf( 'edit_others_%s', $plural ),
				'edit_private_posts'     => sprintf( 'edit_private_%s', $plural ),
				'edit_published_posts'   => sprintf( 'edit_published_%s', $plural ),

				'publish_posts'          => sprintf( 'publish_%s', $plural ),

				'delete_post'            => sprintf( 'delete_%s', $singular ),
				'delete_posts'           => sprintf( 'delete_%s', $plural ), // This is the core bug issue here.
				'delete_private_posts'   => sprintf( 'delete_private_%s', $plural ),
				'delete_published_posts' => sprintf( 'delete_published_%s', $plural ),
				'delete_others_posts'    => sprintf( 'delete_others_%s', $plural ),

				'create_posts'           => sprintf( 'create_%s', $plural ),

			);
		}

		/**
		 * Filter the list of post type capabilities for the given post type.
		 *
		 * The dynamic portion of this hook, `$singular` refers to the post type's
		 * name, for example "course" or "llms_membership".
		 *
		 * @since 3.13.0
		 *
		 * @param array $caps Array of capabilities.
		 */
		return apply_filters(
			"llms_get_{$singular}_post_type_caps",
			$caps
		);
	}

	/**
	 * Get an array of capabilities for earned engagements post types.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public static function get_earned_engagements_post_type_caps() {

		return array(

			'read_post'              => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'read_private_posts'     => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,

			'edit_post'              => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'edit_posts'             => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'edit_others_posts'      => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'edit_private_posts'     => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'edit_published_posts'   => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,

			'publish_posts'          => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,

			'delete_post'            => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'delete_posts'           => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'delete_private_posts'   => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'delete_published_posts' => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,
			'delete_others_posts'    => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,

			'create_posts'           => LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP,

		);
	}

	/**
	 * Retrieve taxonomy capabilities for custom taxonomies
	 *
	 * @since 3.13.0
	 *
	 * @param string|array $tax Taxonomy name/names (pass array of singular, plural to customize plural spelling).
	 * @return array
	 */
	public static function get_tax_caps( $tax ) {

		if ( ! is_array( $tax ) ) {
			$singular = $tax;
			$plural   = $tax . 's';
		} else {
			$singular = $tax[0];
			$plural   = $tax[1];
		}

		/**
		 * Customize the taxonomy capabilities for the given taxonomy.
		 *
		 * The dynamic portion of this hook, `$singular` refers to the taxonomy's
		 * registered name.
		 *
		 * @since 3.13.0
		 *
		 * @param array $caps Array of capabilities.
		 */
		return apply_filters(
			"llms_get_{$singular}_tax_caps",
			array(
				'manage_terms' => sprintf( 'manage_%s', $plural ),
				'edit_terms'   => sprintf( 'edit_%s', $plural ),
				'delete_terms' => sprintf( 'delete_%s', $plural ),
				'assign_terms' => sprintf( 'assign_%s', $plural ),
			)
		);
	}

	/**
	 * Retrieves the block template for use in post type registration.
	 *
	 * @since 6.0.0
	 *
	 * @param string $post_type The post type.
	 * @return array|null Returns the block template array or null if no template is defined for the post type.
	 */
	private static function get_template( $post_type ) {

		if ( empty( self::$templates ) ) {
			self::$templates = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-block-templates.php';
		}

		return self::$templates[ $post_type ] ?? null;
	}

	/**
	 * Register a custom post type.
	 *
	 * Automatically checks for duplicates and filters data.
	 *
	 * @since 3.13.0
	 * @since 5.5.0 Added `lifterlms_register_post_type_{$name}` filters deprecation
	 *              where `$name` is the the post type name, if the unprefixed name (removing 'llms_')
	 *              is different from `$name`. E.g. it'll be triggered when registering when using
	 *              `lifterlms_register_post_type_llms_engagement` but not when using `lifterlms_register_post_type_course`,
	 *              for the latter, both the name and the unprefixed name are the same.
	 * @since 6.0.0 Automatically load templates from the `llms-block-templates` schema.
	 *              Added return value.
	 *
	 * @param string $name Post type name.
	 * @param array  $data Post type data.
	 * @return WP_Post_Type|WP_Error
	 */
	public static function register_post_type( $name, $data ) {

		if ( ! post_type_exists( $name ) ) {

			$unprefixed_name = str_replace( 'llms_', '', $name );

			if ( $unprefixed_name !== $name ) {
				$data = apply_filters_deprecated(
					"lifterlms_register_post_type_{$name}",
					array( $data ),
					'5.5.0',
					"lifterlms_register_post_type_{$unprefixed_name}"
				);
			}

			if ( empty( $data['template'] ) ) {
				$data['template'] = self::get_template( $name );
			}

			/**
			 * Modify post type registration arguments of a LifterLMS custom post type.
			 *
			 * The dynamic portion of this hook refers to the post type's name with the `llms_` prefix
			 * removed (if it exist). For example, to modify the arguments for the membership post type
			 * (`llms_membership`) the full hook would be "lifterlms_register_post_type_membership".
			 *
			 * @since 3.13.0
			 *
			 * @param array $data Post type registration arguments passed to `register_post_type()`.
			 */
			$data = apply_filters( "lifterlms_register_post_type_{$unprefixed_name}", $data );
			return register_post_type( $name, $data );

		}

		return get_post_type_object( $name );
	}

	/**
	 * Register Post Types.
	 *
	 * @since 1.0.0
	 * @since 3.0.4 Made 'llms_access_plan' post type hierarchical to prevent a conflict with the Redirection plugin.
	 * @since 3.33.0 `llms_question` post type is not publicly queryable anymore.
	 * @since 3.37.12 Added 'revisions' support to course, lesson, and llms_mebership post types.
	 * @since 4.5.1 Removed "excerpt" support for the course post type.
	 * @since 4.17.0 Add "llms-sales-page" feature to course and membership post types.
	 * @since 5.5.0 Register all the post types using `self::register_post_type()`.
	 * @since 5.8.0 Remove all post type descriptions.
	 * @since 6.0.0 Show `llms_my_certificate` ui (edit) only to who can `manage_lifterlms`.
	 *             Register `llms_my_achievement` post type.
	 *             Add thumbnail support for achievement and certificates (earned and template)
	 *             Renames `llms_certificate` slug from `certificate` to `certificate-template`.
	 *             Rename `llms_my_certificate` slug from `my_certificate` to `certificate`.
	 *             Replaced the use of the deprecated `get_page() function with `get_post()`.
	 *
	 * @return void
	 */
	public static function register_post_types() {
		$permalinks = llms_get_permalink_structure();

		// Course.
		$catalog_id = llms_get_page_id( 'shop' );
		self::register_post_type(
			'course',
			array(
				'labels'              => array(
					'name'               => __( 'Courses', 'lifterlms' ),
					'singular_name'      => __( 'Course', 'lifterlms' ),
					'menu_name'          => _x( 'Courses', 'Admin menu name', 'lifterlms' ),
					'add_new'            => __( 'Add Course', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Course', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Course', 'lifterlms' ),
					'new_item'           => __( 'New Course', 'lifterlms' ),
					'view'               => __( 'View Course', 'lifterlms' ),
					'view_item'          => __( 'View Course', 'lifterlms' ),
					'search_items'       => __( 'Search Courses', 'lifterlms' ),
					'not_found'          => __( 'No Courses found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Courses found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Course', 'lifterlms' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'menu_icon'           => 'dashicons-welcome-learn-more',
				'capabilities'        => self::get_post_type_caps( 'course' ),
				'map_meta_cap'        => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => array(
					'slug'       => $permalinks['course_base'],
					'with_front' => false,
					'feeds'      => true,
				),
				'query_var'           => true,
				'supports'            => array( 'title', 'author', 'editor', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'revisions', 'llms-clone-post', 'llms-export-post', 'llms-sales-page' ),
				'has_archive'         => ( $catalog_id && get_post( $catalog_id ) ) ? get_page_uri( $catalog_id ) : $permalinks['courses_base'],
				'show_in_nav_menus'   => true,
				'menu_position'       => 52,
			)
		);

		// Section.
		self::register_post_type(
			'section',
			array(
				'labels'              => array(
					'name'               => __( 'Sections', 'lifterlms' ),
					'singular_name'      => __( 'Section', 'lifterlms' ),
					'add_new'            => __( 'Add Section', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Section', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Section', 'lifterlms' ),
					'new_item'           => __( 'New Section', 'lifterlms' ),
					'view'               => __( 'View Section', 'lifterlms' ),
					'view_item'          => __( 'View Section', 'lifterlms' ),
					'search_items'       => __( 'Search Sections', 'lifterlms' ),
					'not_found'          => __( 'No Sections found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Sections found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Sections', 'lifterlms' ),
					'menu_name'          => _x( 'Sections', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
			)
		);

		// Lesson.
		self::register_post_type(
			'lesson',
			array(
				'labels'              => array(
					'name'               => __( 'Lessons', 'lifterlms' ),
					'singular_name'      => __( 'Lesson', 'lifterlms' ),
					'add_new'            => __( 'Add Lesson', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Lesson', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Lesson', 'lifterlms' ),
					'new_item'           => __( 'New Lesson', 'lifterlms' ),
					'view'               => __( 'View Lesson', 'lifterlms' ),
					'view_item'          => __( 'View Lesson', 'lifterlms' ),
					'search_items'       => __( 'Search Lessons', 'lifterlms' ),
					'not_found'          => __( 'No Lessons found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Lessons found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Lessons', 'lifterlms' ),
					'menu_name'          => _x( 'Lessons', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'capabilities'        => self::get_post_type_caps( 'lesson' ),
				'map_meta_cap'        => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=course',
				'hierarchical'        => false,
				'rewrite'             => array(
					'slug'       => $permalinks['lesson_base'],
					'with_front' => false,
					'feeds'      => true,
				),
				'show_in_nav_menus'   => false,
				'query_var'           => true,
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'revisions', 'author', 'llms-clone-post' ),
			)
		);

		// Quiz.
		self::register_post_type(
			'llms_quiz',
			array(
				'labels'              => array(
					'name'               => __( 'Quizzes', 'lifterlms' ),
					'singular_name'      => __( 'Quiz', 'lifterlms' ),
					'add_new'            => __( 'Add Quiz', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Quiz', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Quiz', 'lifterlms' ),
					'new_item'           => __( 'New Quiz', 'lifterlms' ),
					'view'               => __( 'View Quiz', 'lifterlms' ),
					'view_item'          => __( 'View Quiz', 'lifterlms' ),
					'search_items'       => __( 'Search Quiz', 'lifterlms' ),
					'not_found'          => __( 'No Quizzes found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Quizzes found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Quizzes', 'lifterlms' ),
					'menu_name'          => _x( 'Quizzes', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => true,
				'show_ui'             => false,
				'map_meta_cap'        => true,
				'capabilities'        => self::get_post_type_caps( array( 'quiz', 'quizzes' ) ),
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=course',
				'hierarchical'        => false,
				'rewrite'             => array(
					'slug'       => $permalinks['quiz_base'],
					'with_front' => false,
					'feeds'      => true,
				),
				'show_in_nav_menus'   => false,
				'query_var'           => true,
				'supports'            => array( 'title', 'editor', 'author', 'custom-fields' ),
			)
		);

		// Quiz Question.
		self::register_post_type(
			'llms_question',
			array(
				'labels'              => array(
					'name'               => __( 'Questions', 'lifterlms' ),
					'singular_name'      => __( 'Question', 'lifterlms' ),
					'add_new'            => __( 'Add Question', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Question', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Question', 'lifterlms' ),
					'new_item'           => __( 'New Question', 'lifterlms' ),
					'view'               => __( 'View Question', 'lifterlms' ),
					'view_item'          => __( 'View Question', 'lifterlms' ),
					'search_items'       => __( 'Search Questions', 'lifterlms' ),
					'not_found'          => __( 'No Questions found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Questions found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Questions', 'lifterlms' ),
					'menu_name'          => _x( 'Quiz Questions', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'map_meta_cap'        => true,
				'capabilities'        => self::get_post_type_caps( 'question' ),
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=course',
				'hierarchical'        => false,
				'rewrite'             => false,
				'show_in_nav_menus'   => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'editor' ),
			)
		);

		// Membership.
		$membership_page_id = llms_get_page_id( 'memberships' );
		self::register_post_type(
			'llms_membership',
			array(
				'labels'              => array(
					'name'               => __( 'Memberships', 'lifterlms' ),
					'singular_name'      => __( 'Membership', 'lifterlms' ),
					'menu_name'          => _x( 'Memberships', 'Admin menu name', 'lifterlms' ),
					'add_new'            => __( 'Add Membership', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Membership', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Membership', 'lifterlms' ),
					'new_item'           => __( 'New Membership', 'lifterlms' ),
					'view'               => __( 'View Membership', 'lifterlms' ),
					'view_item'          => __( 'View Membership', 'lifterlms' ),
					'search_items'       => __( 'Search Memberships', 'lifterlms' ),
					'not_found'          => __( 'No Memberships found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Memberships found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Membership', 'lifterlms' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'capabilities'        => self::get_post_type_caps( 'membership' ),
				'map_meta_cap'        => true,
				'menu_icon'           => 'dashicons-groups',
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_in_menu'        => true,
				'hierarchical'        => false,
				'rewrite'             => array(
					'slug'       => _x( 'membership', 'membership url slug', 'lifterlms' ),
					'with_front' => false,
					'feeds'      => true,
				),
				'query_var'           => true,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', 'revisions', 'llms-sales-page' ),
				'has_archive'         => ( $membership_page_id && get_post( $membership_page_id ) ) ? get_page_uri( $membership_page_id ) : $permalinks['memberships_base'],
				'show_in_nav_menus'   => true,
				'menu_position'       => 52,
			)
		);

		// Engagement.
		self::register_post_type(
			'llms_engagement',
			array(
				'labels'              => array(
					'name'               => __( 'Engagements', 'lifterlms' ),
					'singular_name'      => __( 'Engagement', 'lifterlms' ),
					'add_new'            => __( 'Add Engagement', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Engagement', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Engagement', 'lifterlms' ),
					'new_item'           => __( 'New Engagement', 'lifterlms' ),
					'view'               => __( 'View Engagement', 'lifterlms' ),
					'view_item'          => __( 'View Engagement', 'lifterlms' ),
					'search_items'       => __( 'Search Engagement', 'lifterlms' ),
					'not_found'          => __( 'No Engagement found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Engagement found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Engagement', 'lifterlms' ),
					'menu_name'          => _x( 'Engagements', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_engagements_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'menu_position'       => 52,
				'menu_icon'           => 'dashicons-awards',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
			)
		);

		// Order.
		self::register_post_type(
			'llms_order',
			array(
				'labels'              => array(
					'name'               => __( 'Orders', 'lifterlms' ),
					'singular_name'      => __( 'Order', 'lifterlms' ),
					'add_new'            => __( 'Add Order', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Order', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Order', 'lifterlms' ),
					'new_item'           => __( 'New Order', 'lifterlms' ),
					'view'               => __( 'View Order', 'lifterlms' ),
					'view_item'          => __( 'View Order', 'lifterlms' ),
					'search_items'       => __( 'Search Orders', 'lifterlms' ),
					'not_found'          => __( 'No Orders found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Orders found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Orders', 'lifterlms' ),
					'menu_name'          => _x( 'Orders', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'menu_icon'           => 'dashicons-cart',
				'menu_position'       => 52,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'comments', 'custom-fields' ),
				'has_archive'         => false,
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
			)
		);

		// Transaction.
		self::register_post_type(
			'llms_transaction',
			array(
				'labels'              => array(
					'name'               => __( 'Transactions', 'lifterlms' ),
					'singular_name'      => __( 'Transaction', 'lifterlms' ),
					'add_new'            => __( 'Add Transaction', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Transaction', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Transaction', 'lifterlms' ),
					'new_item'           => __( 'New Transaction', 'lifterlms' ),
					'view'               => __( 'View Transaction', 'lifterlms' ),
					'view_item'          => __( 'View Transaction', 'lifterlms' ),
					'search_items'       => __( 'Search Transactions', 'lifterlms' ),
					'not_found'          => __( 'No Transactions found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Transactions found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Transactions', 'lifterlms' ),
					'menu_name'          => _x( 'Orders', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => false,
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( '' ),
				'has_archive'         => false,
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
			)
		);

		// Achievement.
		self::register_post_type(
			'llms_achievement',
			array(
				'labels'              => array(
					'name'                  => __( 'Achievement Templates', 'lifterlms' ),
					'singular_name'         => __( 'Achievement Template', 'lifterlms' ),
					'add_new'               => __( 'Add Achievement Template', 'lifterlms' ),
					'add_new_item'          => __( 'Add New Achievement Template', 'lifterlms' ),
					'edit'                  => __( 'Edit', 'lifterlms' ),
					'edit_item'             => __( 'Edit Achievement Template', 'lifterlms' ),
					'new_item'              => __( 'New Achievement Template', 'lifterlms' ),
					'view'                  => __( 'View Achievement Template', 'lifterlms' ),
					'view_item'             => __( 'View Achievement Template', 'lifterlms' ),
					'search_items'          => __( 'Search Achievement Templates', 'lifterlms' ),
					'not_found'             => __( 'No Achievement Templates found', 'lifterlms' ),
					'not_found_in_trash'    => __( 'No Achievement Templates found in trash', 'lifterlms' ),
					'parent'                => __( 'Parent Achievement Template', 'lifterlms' ),
					'menu_name'             => _x( 'Achievements', 'Admin menu name', 'lifterlms' ),
					'featured_image'        => __( 'Achievement Image', 'lifterlms' ),
					'set_featured_image'    => __( 'Set achievement  image', 'lifterlms' ),
					'remove_featured_image' => __( 'Remove achievement image', 'lifterlms' ),
					'use_featured_image'    => __( 'Use achievement image', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_achievements_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=llms_engagement',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'thumbnail' ),
				'has_archive'         => false,
			)
		);

		// Earned achievements.
		self::register_post_type(
			'llms_my_achievement',
			array(
				'labels'              => array(
					'name'                  => __( 'Awarded Achievements', 'lifterlms' ),
					'singular_name'         => __( 'Awarded Achievement', 'lifterlms' ),
					'add_new'               => __( 'Award Achievement', 'lifterlms' ),
					'add_new_item'          => __( 'Award New Achievement', 'lifterlms' ),
					'edit'                  => __( 'Edit', 'lifterlms' ),
					'edit_item'             => __( 'Edit Awarded Achievement', 'lifterlms' ),
					'new_item'              => __( 'New Awarded Achievement', 'lifterlms' ),
					'view'                  => __( 'View Awarded Achievement', 'lifterlms' ),
					'view_item'             => __( 'View Awarded Achievement', 'lifterlms' ),
					'search_items'          => __( 'Search Awarded Achievements', 'lifterlms' ),
					'not_found'             => __( 'No Awarded Achievements found', 'lifterlms' ),
					'not_found_in_trash'    => __( 'No Awarded Achievements found in trash', 'lifterlms' ),
					'parent'                => __( 'Parent Awarded Achievements', 'lifterlms' ),
					'menu_name'             => _x( 'Awarded Achievements', 'Admin menu name', 'lifterlms' ),
					'featured_image'        => __( 'Achievement Image', 'lifterlms' ),
					'set_featured_image'    => __( 'Set awarded achievement image', 'lifterlms' ),
					'remove_featured_image' => __( 'Remove awarded achievement image', 'lifterlms' ),
					'use_featured_image'    => __( 'Use awarded achievement image', 'lifterlms' ),
				),
				'description'         => __( 'This is where you can view all of the awarded achievements.', 'lifterlms' ),
				'public'              => false,
				/**
				 * Filters the needed capability to generate and allow a UI for managing `llms_my_achievement` post type in the admin.
				 *
				 * @since 6.0.0
				 *
				 * @param bool $show_ui The needed capability to generate and allow a UI for managing `llms_my_achievement` post type in the admin.
				 *                      Default is `manage_earned_engagements`.
				 */
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_my_achievements_access', LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP ) ) ) ? true : false,
				'capabilities'        => self::get_post_type_caps( 'my_achievement' ),
				'map_meta_cap'        => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				/** This filter is documented above. */
				'show_in_menu'        => ( current_user_can( apply_filters( 'lifterlms_admin_my_achievements_access', LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP ) ) ) ? 'edit.php?post_type=llms_engagement' : false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'show_in_nav_menus'   => false,
				'has_archive'         => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'thumbnail' ),
			)
		);

		// Certificate.
		self::register_certificate_post_type(
			'llms_certificate',
			array(
				'name'               => __( 'Certificate Templates', 'lifterlms' ),
				'singular_name'      => __( 'Certificate Template', 'lifterlms' ),
				'add_new'            => __( 'Add Certificate Template', 'lifterlms' ),
				'add_new_item'       => __( 'Add New Certificate Template', 'lifterlms' ),
				'edit_item'          => __( 'Edit Certificate Template', 'lifterlms' ),
				'new_item'           => __( 'New Certificate Template', 'lifterlms' ),
				'view'               => __( 'View Certificate Template', 'lifterlms' ),
				'view_item'          => __( 'View Certificate Template', 'lifterlms' ),
				'search_items'       => __( 'Search Certificate Templates', 'lifterlms' ),
				'not_found'          => __( 'No Certificate Templates found', 'lifterlms' ),
				'not_found_in_trash' => __( 'No Certificate Templates found in trash', 'lifterlms' ),
				'parent'             => __( 'Parent Certificate Templates', 'lifterlms' ),
				'menu_name'          => _x( 'Certificates', 'Admin menu name', 'lifterlms' ),
			),
			array(
				'map_meta_cap' => true,
			),
			$permalinks['certificate_template_base'],
			/**
			 * Filters the WordPress user capability required for a user to manage certificate templates on the admin panel.
			 *
			 * @since Unknown
			 *
			 * @param string $capability User capability. Default: `manage_lifterlms`.
			 */
			apply_filters( 'lifterlms_admin_certificates_access', 'manage_lifterlms' )
		);

		// Earned certificate.
		self::register_certificate_post_type(
			'llms_my_certificate',
			array(
				'name'               => __( 'Awarded Certificates', 'lifterlms' ),
				'singular_name'      => __( 'Awarded Certificate', 'lifterlms' ),
				'add_new'            => __( 'Award Certificate', 'lifterlms' ),
				'add_new_item'       => __( 'Award New Certificate', 'lifterlms' ),
				'edit_item'          => __( 'Edit Awarded Certificate', 'lifterlms' ),
				'new_item'           => __( 'New Awarded Certificate', 'lifterlms' ),
				'view'               => __( 'View Awarded Certificate', 'lifterlms' ),
				'view_item'          => __( 'View Awarded Certificate', 'lifterlms' ),
				'search_items'       => __( 'Search Awarded Certificates', 'lifterlms' ),
				'not_found'          => __( 'No Awarded Certificates found', 'lifterlms' ),
				'not_found_in_trash' => __( 'No Awarded Certificates found in trash', 'lifterlms' ),
				'parent'             => __( 'Parent Awarded Certificates', 'lifterlms' ),
				'menu_name'          => _x( 'Awarded Certificates', 'Admin menu name', 'lifterlms' ),
			),
			array(
				'capabilities' => self::get_post_type_caps( 'my_certificate' ),
				'map_meta_cap' => false,
			),
			$permalinks['certificate_base'],
			/**
			 * Filters the needed capability to generate and allow a UI for managing `llms_my_certificate` post type in the admin.
			 *
			 * @since 6.0.0
			 *
			 * @param bool $show_ui The needed capability to generate and allow a UI for managing `llms_my_certificate` post type in the admin.
			 *                      Default is `manage_earned_engagements`.
			 */
			apply_filters( 'lifterlms_admin_my_certificates_access', LLMS_Roles::MANAGE_EARNED_ENGAGEMENT_CAP )
		);

		// Email.
		self::register_post_type(
			'llms_email',
			array(
				'labels'              => array(
					'name'               => __( 'Email Templates', 'lifterlms' ),
					'singular_name'      => __( 'Email Template', 'lifterlms' ),
					'add_new'            => __( 'Add Email Template', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Email Template', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Email Template', 'lifterlms' ),
					'new_item'           => __( 'New Email Template', 'lifterlms' ),
					'view'               => __( 'View Email Template', 'lifterlms' ),
					'view_item'          => __( 'View Email Template', 'lifterlms' ),
					'search_items'       => __( 'Search Email Templates', 'lifterlms' ),
					'not_found'          => __( 'No Emails found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Emails found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Email Templates', 'lifterlms' ),
					'menu_name'          => _x( 'Emails', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_emails_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=llms_engagement',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'editor' ),
				'has_archive'         => false,
			)
		);

		// Coupon.
		self::register_post_type(
			'llms_coupon',
			array(
				'labels'              => array(
					'name'               => __( 'Coupons', 'lifterlms' ),
					'singular_name'      => __( 'Coupon', 'lifterlms' ),
					'add_new'            => __( 'Add Coupon', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Coupon', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Coupon', 'lifterlms' ),
					'new_item'           => __( 'New Coupon', 'lifterlms' ),
					'view'               => __( 'View Coupon', 'lifterlms' ),
					'view_item'          => __( 'View Coupon', 'lifterlms' ),
					'search_items'       => __( 'Search Coupon', 'lifterlms' ),
					'not_found'          => __( 'No Coupon found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Coupon found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Coupon', 'lifterlms' ),
					'menu_name'          => _x( 'Coupons', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_coupons_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=llms_order',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
			)
		);

		// Voucher.
		self::register_post_type(
			'llms_voucher',
			array(
				'labels'              => array(
					'name'               => __( 'Vouchers', 'lifterlms' ),
					'singular_name'      => __( 'Voucher', 'lifterlms' ),
					'add_new'            => __( 'Add Voucher', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Voucher', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Voucher', 'lifterlms' ),
					'new_item'           => __( 'New Voucher', 'lifterlms' ),
					'view'               => __( 'View Voucher', 'lifterlms' ),
					'view_item'          => __( 'View Voucher', 'lifterlms' ),
					'search_items'       => __( 'Search Voucher', 'lifterlms' ),
					'not_found'          => __( 'No Voucher found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Voucher found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Voucher', 'lifterlms' ),
					'menu_name'          => _x( 'Vouchers', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_vouchers_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=llms_order',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
			)
		);

		// Review.
		self::register_post_type(
			'llms_review',
			array(
				'labels'              => array(
					'name'               => __( 'Reviews', 'lifterlms' ),
					'singular_name'      => __( 'Review', 'lifterlms' ),
					'menu_name'          => _x( 'Reviews', 'Admin menu name', 'lifterlms' ),
					'add_new'            => __( 'Add Review', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Review', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Review', 'lifterlms' ),
					'new_item'           => __( 'New Review', 'lifterlms' ),
					'view'               => __( 'View Review', 'lifterlms' ),
					'view_item'          => __( 'View Review', 'lifterlms' ),
					'search_items'       => __( 'Search Reviews', 'lifterlms' ),
					'not_found'          => __( 'No Reviews found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Reviews found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Review', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => ( current_user_can( apply_filters( 'lifterlms_admin_reviews_access', 'manage_lifterlms' ) ) ) ? true : false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => 'edit.php?post_type=course',
				'hierarchical'        => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'page-attributes' ),
			)
		);

		// Access Plan.
		self::register_post_type(
			'llms_access_plan',
			array(
				'labels'              => array(
					'name'               => __( 'Access Plans', 'lifterlms' ),
					'singular_name'      => __( 'Access Plan', 'lifterlms' ),
					'add_new'            => __( 'Add Access Plan', 'lifterlms' ),
					'add_new_item'       => __( 'Add New Access Plan', 'lifterlms' ),
					'edit'               => __( 'Edit', 'lifterlms' ),
					'edit_item'          => __( 'Edit Access Plan', 'lifterlms' ),
					'new_item'           => __( 'New Access Plan', 'lifterlms' ),
					'view'               => __( 'View Access Plan', 'lifterlms' ),
					'view_item'          => __( 'View Access Plan', 'lifterlms' ),
					'search_items'       => __( 'Search Access Plans', 'lifterlms' ),
					'not_found'          => __( 'No Access Plans found', 'lifterlms' ),
					'not_found_in_trash' => __( 'No Access Plans found in trash', 'lifterlms' ),
					'parent'             => __( 'Parent Access Plans', 'lifterlms' ),
					'menu_name'          => _x( 'Access Plans', 'Admin menu name', 'lifterlms' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'map_meta_cap'        => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				/**
				 * Making this post type hierarchical prevents a conflict
				 * with the Redirection plugin (https://wordpress.org/plugins/redirection/)
				 * When 301 monitoring is turned on, Redirection creates access plans
				 * for each access plan that redirect the course or membership
				 * to the site's home page.
				 */
				'hierarchical'        => true,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
			)
		);
	}

	/**
	 * Registers awarded and template certificate post types.
	 *
	 * @since 6.0.0
	 *
	 * @param string $post_type    Post type name.
	 * @param array  $labels       Array of post type labels.
	 * @param array  $args         Array of post type args.
	 * @param string $rewrite_slug Post type rewrite slug.
	 * @param string $admin_cap    User capability required to manage the post type on the admin panel.
	 * @return void
	 */
	private static function register_certificate_post_type( $post_type, $labels, $args, $rewrite_slug, $admin_cap ) {

		$user_can = current_user_can( $admin_cap );
		$supports = array( 'title', 'editor', 'thumbnail' );

		if ( 'llms_my_certificate' === $post_type ) {
			$supports[] = 'author';
		}

		$base_labels = array(
			'edit'                  => __( 'Edit', 'lifterlms' ),
			'featured_image'        => __( 'Background Image', 'lifterlms' ),
			'set_featured_image'    => __( 'Set background image', 'lifterlms' ),
			'remove_featured_image' => __( 'Remove background image', 'lifterlms' ),
			'use_featured_image'    => __( 'Use background image', 'lifterlms' ),
		);

		$base_args = array(
			'labels'              => wp_parse_args( $labels, $base_labels ),
			'show_ui'             => $user_can,
			'publicly_queryable'  => 'llms_certificate' === $post_type ? $user_can : true,
			'show_in_rest'        => llms_is_block_editor_supported_for_certificates() && $user_can,
			'public'              => true,
			'hierarchical'        => false,
			'exclude_from_search' => true,
			'show_in_menu'        => 'edit.php?post_type=llms_engagement',
			'show_in_nav_menus'   => false,
			'query_var'           => true,
			'supports'            => $supports,
			'rewrite'             => array(
				'slug'       => $rewrite_slug,
				'with_front' => false,
				'feeds'      => true,
			),
		);

		self::register_post_type( $post_type, wp_parse_args( $args, $base_args ) );
	}

	/**
	 * Register post statuses
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknwn.
	 *
	 * @return void
	 */
	public static function register_post_statuses() {

		$order_statuses = self::get_order_statuses();

		$txn_statuses = apply_filters(
			'lifterlms_register_transaction_post_statuses',
			array(
				'llms-txn-failed'    => array(
					'label'                     => _x( 'Failed', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-txn-pending'   => array(
					'label'                     => _x( 'Pending', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-txn-refunded'  => array(
					'label'                     => _x( 'Refunded', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'lifterlms' ),
				),
				'llms-txn-succeeded' => array(
					'label'                     => _x( 'Succeeded', 'Transaction status', 'lifterlms' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Succeeded <span class="count">(%s)</span>', 'Succeeded <span class="count">(%s)</span>', 'lifterlms' ),
				),
			)
		);

		foreach ( array_merge( $order_statuses, $txn_statuses ) as $status => $values ) {

			register_post_status( $status, $values );

		}
	}

	/**
	 * Register a custom post type taxonomy
	 * Automatically checks for duplicates and filters data
	 *
	 * @since 3.13.0
	 *
	 * @param string       $name   Taxonomy name.
	 * @param string|array $object Post type object(s) to associate the taxonomy with.
	 * @param array        $data   Taxonomy data.
	 * @return void
	 */
	public static function register_taxonomy( $name, $object, $data ) {

		if ( ! taxonomy_exists( $name ) ) {
			$filter_name = str_replace( 'llms_', '', $name );
			register_taxonomy(
				$name,
				apply_filters( 'lifterlms_register_taxonomy_objects_' . $filter_name, $object ),
				apply_filters( 'lifterlms_register_taxonomy_args_' . $filter_name, $data )
			);
		}
	}

	/**
	 * Register Taxonomies
	 *
	 * @since 1.0.0
	 * @since 3.30.3 Removed duplicate array keys when registering course_tag taxonomy.
	 * @since 3.34.1 Add custom property `show_in_llms_rest` set to true by default to those taxonomies we want to show in LLMS REST api.
	 *
	 * @return void
	 */
	public static function register_taxonomies() {

		$permalinks = llms_get_permalink_structure();

		// Course cat.
		self::register_taxonomy(
			'course_cat',
			array( 'course' ),
			array(
				'label'             => __( 'Course Categories', 'lifterlms' ),
				'labels'            => array(
					'name'              => __( 'Course Categories', 'lifterlms' ),
					'singular_name'     => __( 'Course Category', 'lifterlms' ),
					'menu_name'         => _x( 'Categories', 'Admin menu name', 'lifterlms' ),
					'search_items'      => __( 'Search Course Categories', 'lifterlms' ),
					'all_items'         => __( 'All Course Categories', 'lifterlms' ),
					'parent_item'       => __( 'Parent Course Category', 'lifterlms' ),
					'parent_item_colon' => __( 'Parent Course Category:', 'lifterlms' ),
					'edit_item'         => __( 'Edit Course Category', 'lifterlms' ),
					'update_item'       => __( 'Update Course Category', 'lifterlms' ),
					'add_new_item'      => __( 'Add New Course Category', 'lifterlms' ),
					'new_item_name'     => __( 'New Course Category Name', 'lifterlms' ),
				),
				'capabilities'      => self::get_tax_caps( 'course_cat' ),
				'hierarchical'      => true,
				'query_var'         => true,
				'show_admin_column' => true,
				'show_ui'           => true,
				'rewrite'           => array(
					'slug'         => $permalinks['course_category_base'],
					'with_front'   => false,
					'hierarchical' => true,
				),
				'show_in_llms_rest' => true,
			)
		);

		// Course difficulty.
		self::register_taxonomy(
			'course_difficulty',
			array( 'course' ),
			array(
				'label'             => __( 'Course Difficulties', 'lifterlms' ),
				'labels'            => array(
					'name'              => __( 'Course Difficulties', 'lifterlms' ),
					'singular_name'     => __( 'Course Difficulty', 'lifterlms' ),
					'menu_name'         => _x( 'Difficulties', 'Admin menu name', 'lifterlms' ),
					'search_items'      => __( 'Search Course Difficulties', 'lifterlms' ),
					'all_items'         => __( 'All Course Difficulties', 'lifterlms' ),
					'parent_item'       => __( 'Parent Course Difficulty', 'lifterlms' ),
					'parent_item_colon' => __( 'Parent Course Difficulty:', 'lifterlms' ),
					'edit_item'         => __( 'Edit Course Difficulty', 'lifterlms' ),
					'update_item'       => __( 'Update Course Difficulty', 'lifterlms' ),
					'add_new_item'      => __( 'Add New Course Difficulty', 'lifterlms' ),
					'new_item_name'     => __( 'New Course Difficulty Name', 'lifterlms' ),
				),
				'capabilities'      => self::get_tax_caps( array( 'course_difficulty', 'course_difficulties' ) ),
				'hierarchical'      => false,
				'query_var'         => true,
				'show_admin_column' => true,
				'show_ui'           => true,
				'rewrite'           => array(
					'slug'       => $permalinks['course_difficulty_base'],
					'with_front' => false,
				),
				'show_in_llms_rest' => true,
			)
		);

		// Course tag.
		self::register_taxonomy(
			'course_tag',
			array( 'course' ),
			array(
				'label'             => __( 'Course Tags', 'lifterlms' ),
				'labels'            => array(
					'name'              => __( 'Course Tags', 'lifterlms' ),
					'singular_name'     => __( 'Course Tag', 'lifterlms' ),
					'menu_name'         => _x( 'Tags', 'Admin menu name', 'lifterlms' ),
					'search_items'      => __( 'Search Course Tags', 'lifterlms' ),
					'all_items'         => __( 'All Course Tags', 'lifterlms' ),
					'parent_item'       => __( 'Parent Course Tag', 'lifterlms' ),
					'parent_item_colon' => __( 'Parent Course Tag:', 'lifterlms' ),
					'edit_item'         => __( 'Edit Course Tag', 'lifterlms' ),
					'update_item'       => __( 'Update Course Tag', 'lifterlms' ),
					'add_new_item'      => __( 'Add New Course Tag', 'lifterlms' ),
					'new_item_name'     => __( 'New Course Tag Name', 'lifterlms' ),
				),
				'capabilities'      => self::get_tax_caps( 'course_tag' ),
				'hierarchical'      => false,
				'query_var'         => true,
				'show_admin_column' => true,
				'show_ui'           => true,
				'rewrite'           => array(
					'slug'       => $permalinks['course_tag_base'],
					'with_front' => false,
				),
				'show_in_llms_rest' => true,
			)
		);

		// Course track.
		self::register_taxonomy(
			'course_track',
			array( 'course' ),
			array(
				'label'             => __( 'Course Track', 'lifterlms' ),
				'labels'            => array(
					'name'              => __( 'Course Tracks', 'lifterlms' ),
					'singular_name'     => __( 'Course Track', 'lifterlms' ),
					'menu_name'         => _x( 'Tracks', 'Admin menu name', 'lifterlms' ),
					'search_items'      => __( 'Search Course Tracks', 'lifterlms' ),
					'all_items'         => __( 'All Course Tracks', 'lifterlms' ),
					'parent_item'       => __( 'Parent Course Track', 'lifterlms' ),
					'parent_item_colon' => __( 'Parent Course Track:', 'lifterlms' ),
					'edit_item'         => __( 'Edit Course Track', 'lifterlms' ),
					'update_item'       => __( 'Update Course Track', 'lifterlms' ),
					'add_new_item'      => __( 'Add New Course Track', 'lifterlms' ),
					'new_item_name'     => __( 'New Course Track Name', 'lifterlms' ),
				),
				'capabilities'      => self::get_tax_caps( 'course_track' ),
				'hierarchical'      => true,
				'query_var'         => true,
				'show_admin_column' => true,
				'show_ui'           => true,
				'rewrite'           => array(
					'slug'         => $permalinks['course_track_base'],
					'with_front'   => false,
					'hierarchical' => true,
				),
				'show_in_llms_rest' => true,
			)
		);

		// Membership cat.
		self::register_taxonomy(
			'membership_cat',
			array( 'llms_membership' ),
			array(
				'hierarchical'      => true,
				'label'             => __( 'Membership Categories', 'lifterlms' ),
				'labels'            => array(
					'name'              => __( 'Membership Categories', 'lifterlms' ),
					'singular_name'     => __( 'Membership Category', 'lifterlms' ),
					'menu_name'         => _x( 'Categories', 'Admin menu name', 'lifterlms' ),
					'search_items'      => __( 'Search Membership Categories', 'lifterlms' ),
					'all_items'         => __( 'All Membership Categories', 'lifterlms' ),
					'parent_item'       => __( 'Parent Membership Category', 'lifterlms' ),
					'parent_item_colon' => __( 'Parent Membership Category:', 'lifterlms' ),
					'edit_item'         => __( 'Edit Membership Category', 'lifterlms' ),
					'update_item'       => __( 'Update Membership Category', 'lifterlms' ),
					'add_new_item'      => __( 'Add New Membership Category', 'lifterlms' ),
					'new_item_name'     => __( 'New Membership Category Name', 'lifterlms' ),
				),
				'capabilities'      => self::get_tax_caps( 'membership_cat' ),
				'show_ui'           => true,
				'show_in_menu'      => true,
				'query_var'         => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'         => $permalinks['membership_category_base'],
					'with_front'   => false,
					'hierarchical' => true,
				),
				'show_in_llms_rest' => true,
			)
		);

		// Membership tag.
		self::register_taxonomy(
			'membership_tag',
			array( 'llms_membership' ),
			array(
				'hierarchical'      => false,
				'label'             => __( 'Membership Tags', 'lifterlms' ),
				'labels'            => array(
					'name'              => __( 'Membership Tags', 'lifterlms' ),
					'singular_name'     => __( 'Membership Tag', 'lifterlms' ),
					'menu_name'         => _x( 'Tags', 'Admin menu name', 'lifterlms' ),
					'search_items'      => __( 'Search Membership Tags', 'lifterlms' ),
					'all_items'         => __( 'All Membership Tags', 'lifterlms' ),
					'parent_item'       => __( 'Parent Membership Tag', 'lifterlms' ),
					'parent_item_colon' => __( 'Parent Membership Tag:', 'lifterlms' ),
					'edit_item'         => __( 'Edit Membership Tag', 'lifterlms' ),
					'update_item'       => __( 'Update Membership Tag', 'lifterlms' ),
					'add_new_item'      => __( 'Add New Membership Tag', 'lifterlms' ),
					'new_item_name'     => __( 'New Membership Tag Name', 'lifterlms' ),
				),
				'capabilities'      => self::get_tax_caps( 'membership_tag' ),
				'show_ui'           => true,
				'show_in_menu'      => 'lifterlms',
				'query_var'         => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => $permalinks['membership_tag_base'],
					'with_front' => false,
				),
				'show_in_llms_rest' => true,
			)
		);

		// Course/membership visibility.
		self::register_taxonomy(
			'llms_product_visibility',
			array( 'course', 'llms_membership' ),
			array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false,
			)
		);

		// Access plan visibility.
		self::register_taxonomy(
			'llms_access_plan_visibility',
			array( 'llms_access_plan' ),
			array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'query_var'         => is_admin(),
				'rewrite'           => false,
				'public'            => false,
			)
		);
	}
}

LLMS_Post_Types::init();
