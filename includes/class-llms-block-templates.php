<?php
/**
 * Handles the block templates.
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Block_Templates class.
 *
 * @since [version]
 */
class LLMS_Block_Templates {

	const LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME = 'block-templates';

	const LLMS_BLOCK_TEMPLATES_NAMESPACE = 'lifterlms';

	const LLMS_BLOCK_TEMPLATES_PREFIX = 'llms_';

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

	}

	/**
	 * This function checks if there's a blocks template (ultimately it resolves either a saved blocks template from the
	 * database or a template file in `woo-gutenberg-products-block/templates/block-templates/`)
	 * to return to pre_get_posts short-circuiting the query in Gutenberg.
	 *
	 * @param \WP_Block_Template|null $template Return a block template object to short-circuit the default query,
	 *                                               or null to allow WP to run its normal queries.
	 * @param string                  $id Template unique identifier (example: theme_slug//template_slug).
	 * @param array                   $template_type wp_template or wp_template_part.
	 *
	 * @return mixed|\WP_Block_Template|\WP_Error
	 */
	public function maybe_return_blocks_template( $template, $id, $template_type ) {

		// 'get_block_template' was introduced in WP 5.9.
		if ( ! function_exists( 'get_block_template' ) ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( $theme, $slug ) = $template_name_parts;

		// Remove the filter at this point because if we don't then this function will infinite loop.
		remove_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );

		// Check if the theme has a saved version of this template before falling back to the llms one. Please note how
		// the slug has not been modified at this point, we're still using the default one passed to this hook.
		$maybe_template = get_block_template( $id, $template_type );

		if ( null !== $maybe_template ) {
			add_filter( 'pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3 );
			return $maybe_template;
		}

		// Theme-based template didn't exist, try switching the theme to lifterlms and try again. This function has
		// been unhooked so won't run again.
		add_filter( 'get_block_file_template', array( $this, 'get_single_block_template' ), 10, 3 );
		$maybe_template = get_block_template( self::LLMS_BLOCK_TEMPLATES_NAMESPACE . '//' . $slug, $template_type );

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
	 * Runs on the get_block_template hook. If a template is already found and passed to this function, then return it
	 * and don't run.
	 * If a template is *not* passed, try to look for one that matches the ID in the database, if that's not found defer
	 * to Blocks templates files. Priority goes: DB-Theme, DB-Blocks, Filesystem-Theme, Filesystem-Blocks.
	 *
	 * @param \WP_Block_Template $template      The found block template.
	 * @param string             $id            Template unique identifier (example: theme_slug//template_slug).
	 * @param array              $template_type wp_template or wp_template_part.
	 *
	 * @return mixed|null
	 */
	public function get_single_block_template( $template, $id, $template_type ) {

		// The template was already found before the filter runs, just return it immediately.
		if ( null !== $template || 'wp_template' !== $template_type ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}
		list( , $slug ) = $template_name_parts;

		$available_templates = $this->block_templates( array( $slug ), '', true );

		// If this blocks template doesn't exist then we should just skip the function and let Gutenberg handle it.
		if ( ! in_array( $slug, array_column( $available_templates, 'slug' ), true ) ) {
			return $template;
		}

		if ( is_array( $available_templates ) && count( $available_templates ) > 0 ) {
			$template = $available_templates[0];
			// When saving turn the "theme" lowercase.
			$template->theme = 'LifterLMS' === $template->theme ? self::LLMS_BLOCK_TEMPLATES_NAMESPACE : $template->theme;
		}

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

		$block_templates_paths = $this->block_templates_paths();

		$template_slugs = array_map( array( $this, 'generate_template_slug_from_path' ), $block_templates_paths );
		$template_slugs = empty( $slugs ) ? $template_slugs : array_intersect( $slugs, $template_slugs );

		if ( empty( $template_slugs ) ) {
			return array();
		}

		$templates = $fs_only
			?
			$this->block_templates_from_fs( $block_templates_paths, $template_slugs )
			:
			array_merge(
				$this->block_templates_from_db( $template_slugs, $template_slugs ),
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
					'terms'    => array( self::LLMS_BLOCK_TEMPLATES_NAMESPACE ),
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

	private function block_templates_paths() {

		$block_template_paths = array();

		$block_templates_base_paths = apply_filters(
			'lifterlms_block_templates_directories',
			array(
				LLMS()->plugin_path() . '/templates/' . self::LLMS_BLOCK_TEMPLATES_DIRECTORY_NAME,
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

	private function build_template_result_from_file( $template_file, $template_slug ) {

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template_content         = file_get_contents( $template_file );
		$template                 = new \WP_Block_Template();
		$template->id             = self::LLMS_BLOCK_TEMPLATES_NAMESPACE . '//' . $template_slug;
		$template->theme          = 'LifterLMS';
		$template->content        = _inject_theme_attribute_in_block_template_content( $template_content );
		$template->source         = 'plugin'; // Plugin was agreed as a valid source value despite existing inline docs at the time of creating: https://github.com/WordPress/gutenberg/issues/36597#issuecomment-976232909.
		$template->slug           = $template_slug;
		$template->type           = 'wp_template';
		$template->title          = $this->convert_slug_to_title( $template_slug );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->origin         = 'plugin';
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

		$theme          = $terms[0]->name;
		$has_theme_file = true;

		$template                 = new WP_Block_Template();
		$template->wp_id          = $post->ID;
		$template->id             = $theme . '//' . $post->post_name;
		$template->theme          = self::LLMS_BLOCK_TEMPLATES_NAMESPACE === $theme ? 'LifterLMS' : $theme;
		$template->content        = $post->post_content;
		$template->slug           = $post->post_name;
		$template->source         = 'custom';
		$template->type           = $post->post_type;
		$template->description    = $post->post_excerpt;
		$template->title          = $post->post_title;
		$template->status         = $post->post_status;
		$template->has_theme_file = $has_theme_file;
		$template->is_custom      = false;
		$template->post_types     = array(); // Don't appear in any Edit Post template selector dropdown.

		return $template;

	}

	/**
	 * Converts template paths into a slug.
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
	 * @param string $template_slug The templates slug (e.g. single-product).
	 * @return string Human friendly title converted from the slug.
	 */
	public static function convert_slug_to_title( $template_slug ) {
		switch ( $template_slug ) {
			default:
				// Replace all hyphens and underscores with spaces.
				return ucwords(
					substr(
						preg_replace( '/[\-_]/', ' ', $template_slug ),
						strlen( self::LLMS_BLOCK_TEMPLATES_PREFIX )
					)
				);
		}
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

		return array_merge( $query_result, $templates );

	}


}

$block_templates = new LLMS_Block_Templates();
$block_templates->init();

return $block_templates;
