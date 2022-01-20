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

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'get_block_templates', array( $this, 'add_llms_block_templates' ), 10, 3 );

	}

	private function block_templates() {

		$block_template_paths = $this->block_templates_paths();

		$template_objects = array();
		foreach ( $block_template_paths as $template_file ) {
			$template_slug   = $this->generate_template_slug_from_path( $template_file );
			$template_object = $this->create_new_block_template_object( $template_file, $template_slug );
			$templates[]     = $this->build_template_result_from_file( $template_object );
		}

		return $templates;

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

	private function create_new_block_template_object( $template_file, $template_slug ) {

		$new_template_item = array(
			'slug'        => $template_slug,
			'id'          => self::LLMS_BLOCK_TEMPLATES_NAMESPACE . '//' . $template_slug,
			'path'        => $template_file,
			'type'        => 'wp_template',
			'theme'       => self::LLMS_BLOCK_TEMPLATES_NAMESPACE,
			// Plugin was agreed as a valid source value despite existing inline docs at the time of creating: https://github.com/WordPress/gutenberg/issues/36597#issuecomment-976232909.
			'source'      => 'plugin',
			'title'       => $this->convert_slug_to_title( $template_slug ),
			'description' => '',
			'post_types'  => array(), // Don't appear in any Edit Post template selector dropdown.
		);

		return (object) $new_template_item;
	}

	private function build_template_result_from_file( $template_file ) {
		$template_file = (object) $template_file;

		// If the theme has an archive-products.html template but does not have product taxonomy templates
		// then we will load in the archive-product.html template from the theme to use for product taxonomies on the frontend.
		$template_is_from_theme = 'theme' === $template_file->source;
		$theme_name             = wp_get_theme()->get( 'TextDomain' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template_content  = file_get_contents( $template_file->path );
		$template          = new \WP_Block_Template();
		$template->id      = $template_file->id;
		$template->theme   =  'LifterLMS';
		$template->content = _inject_theme_attribute_in_block_template_content( $template_content );
		// Plugin was agreed as a valid source value despite existing inline docs at the time of creating: https://github.com/WordPress/gutenberg/issues/36597#issuecomment-976232909.
		$template->source         = $template_file->source ? $template_file->source : 'plugin';
		$template->slug           = $template_file->slug;
		$template->type           = $template_type;
		$template->title          = ! empty( $template_file->title ) ? $template_file->title : $this->convert_slug_to_title( $template_file->slug );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->origin         = $template_file->source;
		$template->is_custom      = false; // Templates loaded from the filesystem aren't custom, ones that have been edited and loaded from the DB are.
		$template->post_types     = $template_file->post_types; // Don't appear in any Edit Post template selector dropdown.

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
		return substr(
			$path,
			strpos( $path, $directory_name . DIRECTORY_SEPARATOR ) + 1 + strlen( $directory_name ),
			-5
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
				return ucwords( preg_replace( '/[\-_]/', ' ', $template_slug ) );
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
	 *     @type int    $wp_id Post ID of customized template.
	 * }
	 * @param array               $template_type wp_template or wp_template_part.
	 * @return WP_Block_Template[] Templates.
	 */
	public function add_llms_block_templates( $query_result, $query, $template_type ) {

		// Bail it's not a block theme, or is being retrieved a non wp_template type requested.
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() || 'wp_template' !== $template_type ) {
			return $query_result;
		}

		$post_type = $query['post_type'] ?? '';
		$slugs     = $query['slug__in'] ?? array();

		// Retrieve templates.
		$templates = $this->block_templates();

		return array_merge( $query_result, $templates );

	}


}

return new LLMS_Block_Templates();
