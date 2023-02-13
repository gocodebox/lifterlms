<?php
/**
 * LifterLMS Shortcodes Blocks
 *
 * @since   1.0.0
 * @package LifterLMS/Classes/Shortcodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcodes_Blocks
 *
 * @since 5.3.0
 */
class LLMS_Shortcodes_Blocks {

	/**
	 * Available shortcode blocks.
	 */
	private array $shortcodes = [
		'access-plan-button',
		'checkout',
		'courses',
		'course-author',
		'course-continue-button',
		'course-meta-info',
		'course-outline',
		'course-prerequisites',
		'course-reviews',
		'login',
		'memberships',
		'my-account',
		'my-achievements',
		'registration',
	];

	/**
	 * Constructor.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_filter( 'block_categories', [ $this, 'register_block_category' ], 11 );
		add_filter( 'llms_hide_registration_form', [ $this, 'show_form_preview' ] );
		add_filter( 'llms_hide_login_form', [ $this, 'show_form_preview' ] );
	}

	/**
	 * Registers shortcode blocks.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		foreach ( $this->shortcodes as $shortcode ) {
			register_block_type( LLMS_PLUGIN_DIR . "blocks/$shortcode" );
		}
	}

	/**
	 * Loads front end CSS in the editor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		wp_enqueue_style(
			'lifterlms-styles',
			LLMS()->plugin_url() . '/assets/css/lifterlms.min.css',
			[],
			filemtime( LLMS()->plugin_path() . '/assets/css/lifterlms.min.css' )
		);

		wp_add_inline_style( 'lifterlms-styles', $this->get_inline_css() );

		wp_localize_script(
			'llms-blocks-editor',
			'llmsShortcodeBlocks',
			[
				'accessPlans' => $this->get_access_plans(),
			]
		);
	}

	/**
	 * Registers the LifterLMS block category.
	 *
	 * @since 1.0.0
	 *
	 * @param array $categories Array of block categories.
	 *
	 * @return array
	 */
	public function register_block_category( array $categories ): array {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'lifterlms',
					'title' => __( 'LifterLMS', 'lifterlms' ),
				],
			]
		);
	}

	/**
	 * Returns inline CSS for the editor.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_inline_css(): string {
		$css = <<<CSS
.components-form-token-field,
.components-number-control,
.components-range-control {
	width: 100%;
}

.llms-block-description {
	margin: 0 0 8px;
    font-size: 12px;
    color: rgb(117, 117, 117);
}
CSS;

		return $css;
	}

	/**
	 * Returns an array of access plans.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_access_plans(): array {
		$query = new WP_Query(
			[
				'post_type'      => 'llms_access_plan',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			]
		);

		return array_map(
			function ( $post ) {
				return [
					'value' => $post->ID,
					'label' => $post->post_title,
				];
			},
			$query->posts
		);
	}

	/**
	 * Shows the registration form in editor preview.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $hide Whether to hide the registration form.
	 *
	 * @return bool
	 */
	public function show_form_preview( bool $hide ): bool {
		$rest_route = (string) ( $_GET['rest_route'] ?? '' );

		if ( strpos( $rest_route, 'block-renderer' ) !== false ) {
			$hide = false;
		}

		return $hide;
	}
}

return new LLMS_Shortcodes_Blocks();
