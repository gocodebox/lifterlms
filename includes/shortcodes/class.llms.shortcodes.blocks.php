<?php
/**
 * LifterLMS Shortcodes Blocks
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcodes_Blocks class.
 *
 * @since [version]
 */
class LLMS_Shortcodes_Blocks {

	/**
	 * Available shortcode blocks.
	 *
	 * @var array
	 */
	private $shortcodes = array(
		'access-plan-button',
		'checkout',
		'courses',
		'course-author',
		'course-continue',
		'course-meta-info',
		'course-outline',
		'course-prerequisites',
		'course-reviews',
		'course-syllabus',
		'login',
		'memberships',
		'my-account',
		'my-achievements',
		'registration',
	);

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'after_setup_theme', array( $this, 'add_editor_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_styles' ) );
		add_filter( 'llms_hide_registration_form', array( $this, 'show_form_preview' ) );
		add_filter( 'llms_hide_login_form', array( $this, 'show_form_preview' ) );
	}

	/**
	 * Registers shortcode blocks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		foreach ( $this->shortcodes as $shortcode ) {
			$block_dir = LLMS_PLUGIN_DIR . "blocks/$shortcode";

			if ( file_exists( "$block_dir/block.json" ) ) {
				register_block_type( $block_dir, [
					'render_callback' => array( $this, 'render_block' ),
				] );
			}
		}
	}

	/**
	 * Loads front end CSS in the editor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function add_editor_styles(): void {
		add_editor_style( '../../plugins/lifterlms/assets/css/lifterlms.min.css' );
	}

	/**
	 * Enqueues editor styles.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function enqueue_editor_styles(): void {
		if ( ! $this->is_block_editor() ) {
			return;
		}

		$path = '/assets/css/editor.min.css';

		if ( ! file_exists( LLMS()->plugin_path() . $path ) ) {
			return;
		}

		wp_enqueue_style(
			'llms-editor',
			LLMS()->plugin_url() . $path,
			array(),
			filemtime( LLMS()->plugin_path() . $path )
		);
	}

	/**
	 * Shows the registration and login form in editor preview.
	 *
	 * @since [version]
	 *
	 * @param bool $hide Whether to hide the registration form.
	 * @return bool
	 */
	public function show_form_preview( bool $hide ): bool {
		if ( ! defined( 'REST_REQUEST' ) || ! is_user_logged_in() ) {
			return $hide;
		}

		global $wp;

		if ( ! $wp instanceof WP || empty( $wp->query_vars['rest_route'] ) ) {
			return $hide;
		}

		$route = $wp->query_vars['rest_route'];

		if ( false !== strpos( $route, '/block-renderer/' ) ) {
			$hide = false;
		}

		return $hide;
	}

	/**
	 * Checks if the current page is a block editor page.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	private function is_block_editor(): bool {
		if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
			return true;
		}

		$current_screen = get_current_screen();

		if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders a shortcode block.
	 *
	 * @since [version]
	 * @version [version]
	 *
	 * @param array $attributes The block attributes.
	 * @param string $content The block default content.
	 * @param WP_Block $block The block instance.
	 *
	 * @return string
	 */
	public function render_block( array $attributes, string $content, WP_Block $block ): string {
		if ( ! property_exists( $block, 'name' ) ) {
			return '';
		}

		$name = str_replace(
			array( 'llms/', '-' ),
			array( '', '_' ),
			$block->name
		);

		$atts = '';

		foreach ( $attributes as $key => $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			if ( strpos( $key, 'llms_' ) !== false ) {
				continue;
			}

			if ( $key === 'text' ) {
				continue;
			}

			$atts .= " $key=$value";
		}

		$html = "[lifterlms_$name $atts]";

		if ( isset( $attributes['text'] ) && ! empty( $attributes['text'] ) ) {
			$html .= $attributes['text'] . '[/lifterlms_' . $name . ']';
		}

		$shortcode = trim( do_shortcode( $html ) );

		// This allows emptyResponsePlaceholder to be used when no content is returned.
		if ( ! $shortcode ) {
			return '';
		}

		// Use emptyResponsePlaceholder for Courses block instead of shortcode message.
		if ( false !== strpos( $shortcode, __( 'No products were found matching your selection.', 'lifterlms' ) ) ) {
			return '';
		}

		$html = '<div ' . get_block_wrapper_attributes() . '>';
		$html .= $shortcode;
		$html .= '</div>';

		return $html;
	}
}

return new LLMS_Shortcodes_Blocks();
