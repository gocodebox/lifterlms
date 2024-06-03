<?php
/**
 * LLMS_Block_Templates class file
 *
 * @package LifterLMS/Classes
 *
 * @since 5.8.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the block templates.
 *
 * @since 5.8.0
 */
class LLMS_Block_Templates {

	use LLMS_Trait_Singleton;

	/**
	 * Directory name of the block templates.
	 *
	 * @var string
	 */
	const LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME = 'block-templates';

	/**
	 * Block Template namespace.
	 *
	 * This is used to save templates to the DB which are stored against this value in the wp_terms table.
	 *
	 * @var string
	 */
	const LLMS_BLOCK_TEMPLATES_NAMESPACE = 'lifterlms/lifterlms';

	/**
	 * Block Template slug prefix.
	 *
	 * @var string
	 */
	const LLMS_BLOCK_TEMPLATES_PREFIX = 'llms_';

	/**
	 * Block templates configuration.
	 *
	 * @var array
	 */
	private $block_templates_config;

	/**
	 * Private Constructor.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	private function __construct() {

		$this->configure_block_templates();

		add_filter( 'get_block_templates', array( $this, 'add_llms_block_templates' ), 10, 3 );
		add_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_blocks' ), 9999 );
	}

	/**
	 * Configure block templates.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public function configure_block_templates() {

		$block_templates_config = array(
			llms()->plugin_path() . '/templates/' . self::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME => array(
				'slug_prefix'       => self::LLMS_BLOCK_TEMPLATES_PREFIX,
				'namespace'         => self::LLMS_BLOCK_TEMPLATES_NAMESPACE,
				'blocks_dir'        => self::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME, // Relative to the plugin's templates directory.
				'admin_blocks_l10n' => $this->block_editor_l10n(),
				'template_titles'   => $this->template_titles(),
			),
		);

		/**
		 * Filters the block templates configuration.
		 *
		 * @since 5.8.0
		 *
		 * @param array $block_templates_config Block templates configuration array.
		 */
		$this->block_templates_config = apply_filters( 'llms_block_templates_config', $block_templates_config );

	}

	/**
	 * This function checks if there's a blocks template to return to pre_get_posts short-circuiting the query in Gutenberg.
	 *
	 * Ultimately it resolves either a saved blocks template from the
	 * database or a template file in `lifterlms/templates/block-templates/`.
	 * Without this it won't be possible to save llms templates customizations in the DB.
	 *
	 * @since 5.8.0
	 *
	 * @param WP_Block_Template|null $template      Return a block template object to short-circuit the default query,
	 *                                              or null to allow WP to run its normal queries.
	 * @param string                 $id            Template unique identifier (example: theme_slug//template_slug).
	 * @param array                  $template_type wp_template or wp_template_part.
	 * @return mixed|WP_Block_Template|WP_Error
	 */
	public function maybe_return_blocks_template( $template, $id, $template_type ) {

		// Bail if 'get_block_template' (introduced in WP 5.9.) doesn't exist, or the requested template is not a 'wp_template' type.
		if ( ! function_exists( 'get_block_template' ) || 'wp_template' !== $template_type ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( , $slug ) = $template_name_parts;

		// Remove the filter at this point because if we don't then this function will infinite loop.
		remove_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );

		// Check if the theme has a saved version of this template before falling back to the llms one.
		$maybe_template = get_block_template( $id, $template_type );

		if ( null !== $maybe_template ) {
			add_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );
			return $maybe_template;
		}

		// Theme-based template didn't exist, try switching the theme to lifterlms and try again. This function has
		// been unhooked so won't run again.
		add_filter( 'get_block_file_template', array( $this, 'get_single_block_template' ), 10, 3 );
		$maybe_template = get_block_template( $id, $template_type );

		// Re-hook this function, it was only unhooked to stop recursion.
		add_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );
		remove_filter( 'get_block_file_template', array( $this, 'get_single_block_template' ), 10, 3 );
		if ( null !== $maybe_template ) {
			return $maybe_template;
		}

		// At this point we haven't had any luck finding a template. Give up and let Gutenberg take control again.
		return $template;

	}


	/**
	 * Runs on the get_block_template hook.
	 *
	 * If a template is already found and passed to this function, then return it and don't run.
	 * If a template is *not* passed, try to look for one that matches the ID in the database, if that's not found defer
	 * to Blocks templates files. Priority goes: DB-Theme, DB-Blocks, Filesystem-Theme, Filesystem-Blocks.
	 *
	 * @since 5.8.0
	 *
	 * @param WP_Block_Template $template      The found block template.
	 * @param string            $id            Template unique identifier (example: theme_slug//template_slug).
	 * @param array             $template_type wp_template or wp_template_part.
	 *
	 * @return mixed|null
	 */
	public function get_single_block_template( $template, $id, $template_type ) {

		// The template was already found before the filter runs, or the requested template is not a 'wp_template' type, just return it immediately.
		if ( null !== $template || 'wp_template' !== $template_type ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}
		list( , $slug ) = $template_name_parts;

		// Get available llms templates from the filesystem.
		$available_templates = $this->block_templates( array( $slug ), '', true );

		// If this blocks template doesn't exist then we should just skip the function and let Gutenberg handle it.
		if ( ! in_array( $slug, wp_list_pluck( $available_templates, 'slug' ), true ) ) {
			return $template;
		}

		$template = ( is_array( $available_templates ) && count( $available_templates ) > 0 ) ?
			$available_templates[0] : $template;

		return $template;

	}

	/**
	 * Gets the templates.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Filter template slugs array before checking if it's empty.
	 *
	 * @param array  $slugs     An array of slugs to retrieve templates for.
	 * @param string $post_type Post Type.
	 * @param bool   $fs_only   Retrieve templates from the filesystem ony.
	 * @return WP_Block_Template[] Templates.
	 */
	private function block_templates( $slugs = array(), $post_type = '', $fs_only = false ) {

		// Get paths where to look for block templates.
		$block_templates_paths = $this->block_templates_paths();

		// Get all the slugs.
		$template_slugs = array_map( array( $this, 'generate_template_slug_from_path' ), $block_templates_paths );
		// If specific slugs are required, filter them only.
		$template_slugs = empty( array_filter( $slugs ) ) ? $template_slugs : array_intersect( $slugs, $template_slugs );

		if ( empty( $template_slugs ) ) {
			return array();
		}

		$templates = $fs_only
			?
			$this->block_templates_from_fs( $block_templates_paths, $template_slugs )
			:
			array_merge(
				$this->block_templates_from_db( $template_slugs ),
				$this->block_templates_from_fs( $block_templates_paths, $template_slugs )
			);

		// DB wins over fs, exclude not allowed post types.
		$templates = array_values(
			array_filter(
				$templates,
				function( $template, $key ) use ( $templates, $post_type ) {
					return ( ! ( $post_type && isset( $template->post_types ) && ! in_array( $post_type, $template->post_types, true ) ) ) &&
						array_search( $template->slug, array_unique( wp_list_pluck( $templates, 'slug' ) ), true ) === $key;
				},
				ARRAY_FILTER_USE_BOTH
			)
		);

		return $templates;

	}

	/**
	 * Get block templates from the file system.
	 *
	 * @since 5.8.0
	 *
	 * @param string[] $block_templates_paths Array of block templates paths to look for templates.
	 * @param string[] $slugs                 Arrray of template slugs to be retrieved.
	 * @return void
	 */
	private function block_templates_from_fs( $block_templates_paths, $slugs = array() ) {

		$templates = array();

		foreach ( $block_templates_paths as $template_file ) {
			$template_slug = $this->generate_template_slug_from_path( $template_file );
			if ( ! empty( $slugs ) && ! in_array( $template_slug, $slugs, true ) ) {
				continue;
			}
			$templates[] = $this->build_template_result_from_file( $template_file, $template_slug );
		}

		return $templates;

	}

	/**
	 * Gets the templates saved in the database.
	 *
	 * @since 5.8.0
	 *
	 * @param array $slugs An array of slugs to retrieve templates for.
	 * @return int[]|WP_Post[] An array of found templates.
	 */
	private function block_templates_from_db( $slugs = array() ) {

		$query_args = array(
			'post_status'    => array( 'auto-draft', 'draft', 'publish' ),
			'post_type'      => 'wp_template',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => array_merge(
						array( get_stylesheet(), get_template() ),
						array_column( $this->block_templates_config, 'namespace' )
					),
				),
			),
		);

		if ( is_array( $slugs ) && count( $slugs ) > 0 ) {
			$query_args['post_name__in'] = $slugs;
		}

		/**
		 * Filters the query arguments to retrieve the templates saved in the db.
		 *
		 * @since 5.8.0
		 *
		 * @param array $query_args WQ_Query argiments to retrieve the templates saved in the db.
		 */
		$query_args = apply_filters( 'llms_block_templates_from_db_query_args', $query_args );

		$templates = ( new WP_Query( $query_args ) )->posts;

		return array_map(
			function( $template ) {
				return $this->build_template_result_from_post( $template );
			},
			$templates
		);

	}

	/**
	 * Retrieve the block templates directory paths.
	 *
	 * @since 5.8.0
	 *
	 * @return string[]
	 */
	private function block_templates_paths() {

		$block_template_paths = array();

		$block_templates_base_paths = array_keys( $this->block_templates_config );

		foreach ( $block_templates_base_paths as $block_template_base_path ) {
			$block_template_paths = array_merge(
				_get_block_templates_paths( $block_template_base_path ),
				$block_template_paths
			);
		}

		return $block_template_paths;

	}

	/**
	 * Build a wp template from file.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Allow template directory override when the block template comes from an add-on.
	 * @since 7.5.0 Use `traverse_and_serialize_blocks` in place of deprecated (since wp 6.4.0) `_inject_theme_attribute_in_block_template_content`
	 *
	 * @param string $template_file Template file path.
	 * @param string $template_slug Template slug.
	 * @return WP_Block_Template
	 */
	private function build_template_result_from_file( $template_file, $template_slug = '' ) {

		$template_slug = empty( $template_slug ) ? $this->generate_template_slug_from_path( $template_file ) : $template_slug;
		$namespace     = $this->generate_template_namespace_from_path( $template_file );  // Looks like 'lifterlms/lifterlms' or 'lifterlms-groups/lifterlms-groups', etc.
		$template_file = $this->get_maybe_overridden_block_template_file_path( $template_file );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template_content = file_get_contents( $template_file );

		// Is the template from the theme/child-theme.
		$theme = false !== strpos( $template_file, get_template_directory() ) ? get_template() : get_stylesheet();
		$theme = false !== strpos( $template_file, get_stylesheet_directory() ) ? $theme : false;

		$template                 = new WP_Block_Template();
		$template->id             = $theme ? $theme . '//' . $template_slug : $namespace . '//' . $template_slug;
		$template->theme          = $theme ? $theme : $namespace;
		$template->content        = function_exists( 'traverse_and_serialize_blocks' ) ?
			traverse_and_serialize_blocks( parse_blocks( $template_content ), '_inject_theme_attribute_in_template_part_block' ) :
			_inject_theme_attribute_in_block_template_content( $template_content );
		$template->source         = $theme ? 'theme' : 'plugin'; // Plugin was agreed as a valid source value despite existing inline docs at the time of creating: https://github.com/WordPress/gutenberg/issues/36597#issuecomment-976232909.
		$template->slug           = $template_slug;
		$template->type           = 'wp_template';
		$template->title          = $this->convert_slug_to_title( $template_slug );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->origin         = $theme ? 'theme' : 'plugin';
		$template->is_custom      = false; // Templates loaded from the filesystem aren't custom, ones that have been edited and loaded from the DB are.
		$template->post_types     = array(); // Don't appear in any Edit Post template selector dropdown.

		return $template;
	}

	/**
	 * Build a unified template object based on a WP_Post object.
	 *
	 * @since 5.8.0
	 *
	 * @param WP_Post $post Template post.
	 * @return WP_Block_Template|WP_Error Template.
	 */
	private function build_template_result_from_post( $post ) {

		$terms = get_the_terms( $post, 'wp_theme' );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( ! $terms ) {
			return new \WP_Error( 'template_missing_theme', __( 'No theme is defined for this template.', 'lifterlms' ) );
		}

		$theme = $terms[0]->name;

		$template                 = new WP_Block_Template();
		$template->wp_id          = $post->ID;
		$template->id             = $theme . '//' . $post->post_name;
		$template->theme          = $theme;
		$template->content        = $post->post_content;
		$template->slug           = $post->post_name;
		$template->source         = 'custom';
		$template->type           = $post->post_type;
		$template->description    = $post->post_excerpt;
		$template->title          = $post->post_title;
		$template->status         = $post->post_status;
		$template->has_theme_file = true;
		$template->is_custom      = false;
		$template->post_types     = array(); // Don't appear in any Edit Post template selector dropdown.

		/**
		 * Set the 'plugin' origin
		 * if it doesn't come from from the current theme (or its parent).
		 */
		if ( ! in_array( $theme, array( get_template(), get_stylesheet() ), true ) ) {
			$template->origin = 'plugin';
		}

		return $template;

	}

	/**
	 * Retrieve the actual template file path, maybe overridden in the theme.
	 *
	 * @since 5.9.0
	 *
	 * @param string $template_file The template's path.
	 * @return string
	 */
	private function get_maybe_overridden_block_template_file_path( $template_file ) {

		$template_path_info  = pathinfo( $template_file );
		$template_file_name  = $template_path_info['filename'];
		$template_blocks_dir = untrailingslashit( $this->generate_template_blocks_dir_from_path( $template_file ) ); // Looks like 'block-templates'.

		/**
		 * Does this come from LifterLMS or from an add-on? In the latter case use the absolute path.
		 *
		 * $template_path_info['dirname'] looks like 'ABSPATH/wp-content/plugins/lifterlms/templates/block-templates' or
		 * 'ABSPATH/wp-content/plugins/lifterlms-groups/templates/block-templates' for an add-on.
		 */
		return false !== strpos( $template_path_info['dirname'], trailingslashit( llms()->plugin_path() ) )
			?
			llms_template_file_path(
				$template_blocks_dir . '/' . $template_file_name . '.html'
			)
			:
			llms_template_file_path(
				$template_blocks_dir . '/' . $template_file_name . '.html', // Looks like 'block-templates/single-llms_group.html'.
				substr( $template_path_info['dirname'], 0, -1 * strlen( $template_blocks_dir ) ), // Looks like 'ABSPATH/wp-content/plugins/lifterlms-groups/templates/'.
				true
			);

	}

	/**
	 * Convert the template paths into a slug.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Return empty string if the passed path is not in the configuration.
	 * @since 5.10.0 Use '/' in favor of DIRECTORY_SEPARATOR to avoid issues on Windows.
	 * @since 7.2.0 Retrieve the slug by using `basename()` which also fixes issues on Windows filesystems.
	 *
	 * @param string $path The template's path.
	 * @return string
	 */
	private function generate_template_slug_from_path( $path ) {

		$prefix  = $this->block_template_config_property_from_path( $path, 'slug_prefix' );

		return $prefix . basename( $path, '.html' );

	}

	/**
	 * Generate the template namespace from the template path.
	 *
	 * @since 5.8.0
	 *
	 * @param string $path The template's path.
	 * @return string
	 */
	private function generate_template_namespace_from_path( $path ) {

		return $this->block_template_config_property_from_path( $path, 'namespace' );

	}

	/**
	 * Generate the template slug prefix from the template path.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Fix property name.
	 *
	 * @param string $path The template's path.
	 * @return string
	 */
	private function generate_template_prefix_from_path( $path ) {

		return $this->block_template_config_property_from_path( $path, 'slug_prefix' );

	}

	/**
	 * Generate the block template directory (relative to the templates direcotry) from the template path.
	 *
	 * @since 5.9.0
	 *
	 * @param string $path The template's path.
	 * @return string
	 */
	private function generate_template_blocks_dir_from_path( $path ) {

		return $this->block_template_config_property_from_path( $path, 'blocks_dir' );

	}

	/**
	 * Retrieve a template config property from path.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Return an empty string if requesting a non existing property.
	 *               Also removed unused var `$dirname`.
	 *
	 * @param string $path     The template's path.
	 * @param string $property The template's config property to retrieve.
	 * @return string
	 */
	private function block_template_config_property_from_path( $path, $property ) {

		$prop_value = '';
		foreach ( $this->block_templates_config as $block_templates_base_path => $config ) {
			if ( false !== strpos( $path, $block_templates_base_path ) ) {
				$prop_value = $config[ $property ] ?? $prop_value;
				break;
			}
		}
		return $prop_value;

	}

	/**
	 * Converts template slugs into readable titles.
	 *
	 * @since 5.8.0
	 *
	 * @param string $template_slug The templates slug (e.g. single-product).
	 * @return string Human friendly title converted from the slug.
	 */
	private function convert_slug_to_title( $template_slug ) {

		$template_titles = array_merge( ...array_column( $this->block_templates_config, 'template_titles' ) );

		return array_key_exists( $template_slug, $template_titles ) ?
			$template_titles[ $template_slug ]
			:
			// Replace all hyphens and underscores with spaces.
			ucwords( preg_replace( '/[\-_]/', ' ', $template_slug ) );

	}

	/**
	 * Add lifterlms blocks templates.
	 *
	 * @since 5.8.0
	 * @since 6.0.0 Use `llms_is_block_theme()` in favor of `wp_is_block_theme()`.
	 *
	 * @param WP_Block_Template[] $query_result Array of found block templates.
	 * @param array               $query        {
	 *     Optional. Arguments to retrieve templates.
	 *
	 *     @type array  $slug__in List of slugs to include.
	 *     @type int    $wp_id    Post ID of customized template.
	 * }
	 * @param array               $template_type wp_template or wp_template_part.
	 * @return WP_Block_Template[] Templates.
	 */
	public function add_llms_block_templates( $query_result, $query, $template_type = 'wp_template' ) {

		// Bail it's not a block theme, or is being retrieved a non wp_template type requested.
		if ( ! llms_is_block_theme() || 'wp_template' !== $template_type ) {
			return $query_result;
		}

		$post_type = $query['post_type'] ?? '';
		$slugs     = $query['slug__in'] ?? array();

		// Retrieve templates.
		$templates = $this->block_templates( $slugs, $post_type );

		/**
		 * Remove theme override templates who have a customization in the db from $query_result:
		 * those template blocks will be already retrieved by our LLMS_Block_Templates::block_templates_from_db().
		 */
		$query_result = array_values(
			array_filter(
				$query_result,
				function( $template ) use ( $templates ) {
					$slugs = wp_list_pluck( $templates, 'slug' );
					return ( ! in_array( $template->slug, $slugs, true ) );
				}
			)
		);

		return array_merge( $query_result, $templates );

	}

	/**
	 * Returns an associative array of template titles.
	 *
	 * Keys are template slugs.
	 * Values are template titles in a human readable form.
	 *
	 * @since 5.8.0
	 *
	 * @return array
	 */
	private function template_titles() {

		$template_titles = array(
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'archive-course'             => esc_html__( 'Course Catalog', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'archive-llms_membership'    => esc_html__( 'Membership Catalog', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'single-certificate'         => esc_html__( 'Single Certificate', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'single-no-access'           => esc_html__( 'Single Access Restricted', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-course_cat'        => esc_html__( 'Taxonomy Course Category', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-course_difficulty' => esc_html__( 'Taxonomy Course Difficulty', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-course_tag'        => esc_html__( 'Taxonomy Course Tag', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-course_track'      => esc_html__( 'Taxonomy Course Track', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-membership_cat'    => esc_html__( 'Taxonomy Membership Category', 'lifterlms' ),
			self::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-membership_tag'    => esc_html__( 'Taxonomy Membership Tag', 'lifterlms' ),
		);

		/**
		 * Filters the block template titles.
		 *
		 * @since 5.8.0
		 *
		 * @param array $template_titles  {
		 *     Associative array of template titles.
		 *
		 *     @type string $slug  The template slug.
		 *     @type string $title The template readable titles.
		 * }
		 */
		return apply_filters( 'lifterlms_block_templates_titles', $template_titles );

	}

	/**
	 * Block Templates admin js strings.
	 *
	 * @since 5.8.0
	 *
	 * @return string[]
	 */
	private function block_editor_l10n() {

		return array(
			'archive-course'             => esc_html__( 'LifterLMS Course Catalog Template', 'lifterlms' ),
			'archive-llms_membership'    => esc_html__( 'LifterLMS Membership Catalog Template', 'lifterlms' ),
			'single-certificate'         => esc_html__( 'LifterLMS Certificate Template', 'lifterlms' ),
			'single-no-access'           => esc_html__( 'LifterLMS Single Template Access Restricted', 'lifterlms' ),
			'taxonomy-course_cat'        => esc_html__( 'LifterLMS Course Category Taxonomy Template', 'lifterlms' ),
			'taxonomy-course_difficulty' => esc_html__( 'LifterLMS Course Difficulty Taxonomy Template', 'lifterlms' ),
			'taxonomy-course_tag'        => esc_html__( 'LifterLMS Course Tag Taxonomy Template', 'lifterlms' ),
			'taxonomy-course_track'      => esc_html__( 'LifterLMS Course Track Taxonomy Template', 'lifterlms' ),
			'taxonomy-membership_cat'    => esc_html__( 'LifterLMS Membership Tag Taxonomy Template', 'lifterlms' ),
			'taxonomy-membership_tag'    => esc_html__( 'LifterLMS Membership Tag Taxonomy Template', 'lifterlms' ),
		);

	}

	/**
	 * Localize block templates.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Retuns the `wp_localize_script()` return value.
	 *
	 * @return bool
	 */
	public function localize_blocks() {
		return wp_localize_script(
			'llms-blocks-editor',
			'llmsBlockTemplatesL10n',
			array_merge( ...array_column( $this->block_templates_config, 'admin_blocks_l10n' ) )
		);
	}

}
