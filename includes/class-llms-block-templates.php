<?php
/**
 * LLMS_Block_Templates class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the block templates.
 *
 * @since [version]
 */
class LLMS_Block_Templates {

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
	 * Template titles in a human readable form.
	 *
	 * @var array
	 */
	private $template_titles;

	/**
	 * Init.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function init() {

		add_filter( 'get_block_templates', array( $this, 'add_llms_block_templates' ), 10, 3 );
		add_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );
		add_filter( 'lifterlms_js_l10n_admin', array( $this, 'add_block_templates_admin_strings_js' ) );

	}

	/**
	 * This function checks if there's a blocks template to return to pre_get_posts short-circuiting the query in Gutenberg.
	 *
	 * Ultimately it resolves either a saved blocks template from the
	 * database or a template file in `lifterlms/templates/block-templates/`.
	 * Without this it won't be possible to save llms templates customizations in the DB.
	 *
	 * @since [version]
	 *
	 * @param WP_Block_Template|null $template      Return a block template object to short-circuit the default query,
	 *                                               or null to allow WP to run its normal queries.
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
	 * @since [version]
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
	 * @since [version]
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
		$template_slugs = empty( $slugs ) ? $template_slugs : array_intersect( $slugs, $template_slugs );

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
	 * @since [version]
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
	 * @since [version]
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
					'terms'    => array( get_stylesheet(), get_template(), self::LLMS_BLOCK_TEMPLATES_NAMESPACE ),
				),
			),
		);

		if ( is_array( $slugs ) && count( $slugs ) > 0 ) {
			$query_args['post_name__in'] = $slugs;
		}

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
	 * @since [version]
	 *
	 * @return string[]
	 */
	private function block_templates_paths() {

		$block_template_paths = array();

		/**
		 * Filter the block templates directories.
		 *
		 * @since [version]
		 *
		 * @param string[] Array of directory paths.
		 */
		$block_templates_base_paths = apply_filters(
			'lifterlms_block_templates_directories',
			array(
				llms()->plugin_path() . '/templates/' . self::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME,
			)
		);

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
	 * @since [version]
	 *
	 * @param string $template_file Template file path.
	 * @param string $template_slug Template slug.
	 *
	 * @return WP_Block_Template
	 */
	private function build_template_result_from_file( $template_file, $template_slug = '' ) {

		$template_slug      = empty( $template_slug ) ? $this->generate_template_slug_from_path( $template_file ) : $template_slug;
		$template_file_name = substr( $template_slug, strlen( self::LLMS_BLOCK_TEMPLATES_PREFIX ) );
		$template_file      = llms_template_file_path( self::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME . '/' . $template_file_name . '.html' ); // Can be overridden.

		// Is the template from the theme/child-theme.
		$theme = false !== strpos( $template_file, get_template_directory() ) ? get_template() : get_stylesheet();
		$theme = false !== strpos( $template_file, get_stylesheet_directory() ) ? $theme : false;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template_content         = file_get_contents( $template_file );
		$template                 = new WP_Block_Template();
		$template->id             = $theme ? $theme . '//' . $template_slug : self::LLMS_BLOCK_TEMPLATES_NAMESPACE . '//' . $template_slug;
		$template->theme          = $theme ? $theme : self::LLMS_BLOCK_TEMPLATES_NAMESPACE;
		$template->content        = _inject_theme_attribute_in_block_template_content( $template_content );
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
	 * Build a unified template object based a post Object.
	 *
	 * @since [version]
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
	 * Converts template paths into a slug.
	 *
	 * @since [version]
	 *
	 * @param string $path           The template's path.
	 * @param string $directory_name The template's directory name.
	 * @return string slug
	 */
	private function generate_template_slug_from_path( $path, $directory_name = self::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME ) {
		return self::LLMS_BLOCK_TEMPLATES_PREFIX . substr(
			$path,
			strpos( $path, $directory_name . DIRECTORY_SEPARATOR ) + 1 + strlen( $directory_name ),
			-5 // .html
		);
	}

	/**
	 * Converts template slugs into readable titles.
	 *
	 * @since [version]
	 *
	 * @param string $template_slug The templates slug (e.g. single-product).
	 * @return string Human friendly title converted from the slug.
	 */
	private function convert_slug_to_title( $template_slug ) {

		$template_titles = $this->template_titles();

		$unprefixed_template_slug = substr( $template_slug, strlen( self::LLMS_BLOCK_TEMPLATES_PREFIX ) );

		return array_key_exists( $unprefixed_template_slug, $template_titles ) ?
			$template_titles[ $unprefixed_template_slug ]
			:
			// Replace all hyphens and underscores with spaces.
			ucwords( preg_replace( '/[\-_]/', ' ', $unprefixed_template_slug ) );

	}

	/**
	 * Add lifterlms blocks templates.
	 *
	 * @since [version]
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
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() || 'wp_template' !== $template_type ) {
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
	 * Keys are unprefixed template slugs.
	 * Values are template titles in a human readable form.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function template_titles() {

		$template_titles = $this->template_titles ?? array(
			'archive-course'             => esc_html__( 'Course Catalog', 'lifterlms' ),
			'archive-llms_membership'    => esc_html__( 'Membership Catalog', 'lifterlms' ),
			'single-certificate'         => esc_html__( 'Single Certificate', 'lifterlms' ),
			'single-no-access'           => esc_html__( 'Single Access Restricted', 'lifterlms' ),
			'taxonomy-course_cat'        => esc_html__( 'Taxonomy Course Category', 'lifterlms' ),
			'taxonomy-course_difficulty' => esc_html__( 'Taxonomy Course Difficulty', 'lifterlms' ),
			'taxonomy-course_tag'        => esc_html__( 'Taxonomy Course Tag', 'lifterlms' ),
			'taxonomy-course_track'      => esc_html__( 'Taxonomy Course Track', 'lifterlms' ),
			'taxonomy-memberhsip_cat'    => esc_html__( 'Taxonomy Membership Category', 'lifterlms' ),
			'taxonomy-memberhsip_tag'    => esc_html__( 'Taxonomy Membership Tag', 'lifterlms' ),
		);

		$this->template_titles = $this->template_titles ?? $template_titles;

		/**
		 * Filters the block template titles.
		 *
		 * @since [version]
		 *
		 * @param array $template_titles  {
		 *     Associative array of template titles.
		 *
		 *     @type string $slug  The template slug (unprefixed).
		 *     @type string $title The template readable titles.
		 * }
		 */
		return apply_filters( 'lifterlms_block_templates_titles', $this->template_titles );

	}

	/**
	 * Block Templates admin js strings.
	 *
	 * @since [version]
	 *
	 * @param string[] $strings Localization admin strings.
	 * @return string[]
	 */
	public function add_block_templates_admin_strings_js( $strings ) {

		return array_merge(
			$strings,
			array(
				'LifterLMS Course Catalog Template'               => esc_html__( 'LifterLMS Course Catalog Template', 'lifterlms' ),
				'LifterLMS Membership Catalog Template'           => esc_html__( 'LifterLMS Membership Catalog Template', 'lifterlms' ),
				'LifterLMS Single Template Access Restricted'     => esc_html__( 'LifterLMS Single Template Access Restricted', 'lifterlms' ),
				'LifterLMS Certificate Template'                  => esc_html__( 'LifterLMS Certificate Template', 'lifterlms' ),
				'LifterLMS Course Category Taxonomy Template'     => esc_html__( 'LifterLMS Course Category Taxonomy Template', 'lifterlms' ),
				'LifterLMS Course Difficulty Taxonomy Template'   => esc_html__( 'LifterLMS Course Difficulty Taxonomy Template', 'lifterlms' ),
				'LifterLMS Course Tag Taxonomy Template'          => esc_html__( 'LifterLMS Course Tag Taxonomy Template', 'lifterlms' ),
				'LifterLMS Course Track Taxonomy Template'        => esc_html__( 'LifterLMS Course Track Taxonomy Template', 'lifterlms' ),
				'LifterLMS Membership Category Taxonomy Template' => esc_html__( 'LifterLMS Membership Category Taxonomy Template', 'lifterlms' ),
				'LifterLMS Membership Tag Taxonomy Template'      => esc_html__( 'LifterLMS Membership Tag Taxonomy Template', 'lifterlms' ),
			)
		);

	}

}

$block_templates = new LLMS_Block_Templates();
$block_templates->init();

return $block_templates;
